<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ExportPayload;
use App\Contracts\ExportWriter;
use App\Enums\ExportSection;
use App\Helpers\Money;
use App\Models\Document;
use App\Models\ItemPhoto;
use App\ValueObjects\ExportSelection;
use BackedEnum;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Exceptions\ImageException;
use Intervention\Image\ImageManager;

/**
 * Writes an export as a laid out document.
 *
 * dompdf renders the markup itself rather than through a browser, which is what
 * lets this run anywhere the app runs, and is also the constraint the templates
 * are written against: no flexbox, no grid, no modern colour functions. Layout is
 * tables and the charts are drawn as bars, which is enough for what the summary
 * has to say.
 *
 * The item sheets arrive as a generator and are rendered one at a time into
 * strings, and the appendices are gathered from the same pass. Walking the
 * generator twice is not possible, and building the whole document in one Blade
 * loop would mean holding every item's models at once, so the assembly happens
 * here and the templates stay free of state.
 *
 * A photo cannot be fetched over its own url, which needs a session dompdf does
 * not have, so every image is read off the disk, scaled down and inlined.
 */
class ExportPdfWriter implements ExportWriter
{
    private const int THUMBNAIL_WIDTH = 150;

    private const int PLATE_WIDTH = 520;

    /** @var array<int, ?string> */
    private array $images = [];

    public function __construct(
        private readonly ExportBlueprint $blueprint,
    ) {}

    public function write(ExportPayload $payload): string
    {
        // A few hundred items with their photos outlast the default request
        // budget, and this is the one place that knows the work is this big.
        set_time_limit(600);
        ini_set('memory_limit', '1024M');

        $selection = $payload->selection();
        $sheets = [];
        $appendices = ['photos' => [], 'timeline' => [], 'valuations' => [], 'transactions' => [], 'insurance' => []];

        foreach ($payload->itemSheets() ?? [] as $item) {
            $sheets[] = view('exports.pdf.item-sheet', [
                'item' => $item,
                'payload' => $payload,
                'selection' => $selection,
                'writer' => $this,
                'blueprint' => $this->blueprint,
            ])->render();

            $this->gather($appendices, $item, $selection);
        }

        $html = view('exports.pdf.document', [
            'payload' => $payload,
            'selection' => $selection,
            'writer' => $this,
            'blueprint' => $this->blueprint,
            'meta' => $payload->meta(),
            'cover' => $payload->cover(),
            'summary' => $payload->summary(),
            'inventory' => $payload->inventory(),
            'documents' => $payload->documents(),
            'appendices' => $payload->appendices(),
            'appendixRows' => $appendices,
            'sheets' => $sheets,
        ])->render();

        $path = (string) tempnam(sys_get_temp_dir(), 'kollek-export-');

        file_put_contents($path, Pdf::loadHTML($html)
            ->setPaper('a4')
            ->setOption(['isRemoteEnabled' => false, 'defaultFont' => 'DejaVu Sans'])
            ->output());

        return $path;
    }

    /**
     * A photo as a data uri, scaled to the box it is drawn in.
     *
     * The same photo can be asked for by the inventory and again by the item
     * sheet, so the encoded string is kept. A photo whose file has gone missing
     * reads as null and the template simply leaves a gap, because half an export
     * is better than none.
     */
    public function image(?ItemPhoto $photo, int $width = self::THUMBNAIL_WIDTH): ?string
    {
        if (! $photo instanceof ItemPhoto) {
            return null;
        }

        $key = $photo->id * 10000 + $width;

        if (array_key_exists($key, $this->images)) {
            return $this->images[$key];
        }

        return $this->images[$key] = $this->encode($photo, $width);
    }

    /**
     * A value as the document should read it.
     *
     * The payload hands over cents, Carbon dates and enums so that the workbook
     * can keep them as numbers, which leaves this the one place that turns them
     * into words. An empty value reads as a dash rather than as nothing, so a
     * table keeps its shape.
     */
    public function text(string $key, mixed $value, ?string $currency = null): string
    {
        if (in_array($value, [null, '', []], true)) {
            return '—';
        }

        if ($this->blueprint->isMoney($key) && is_int($value)) {
            return Money::format($value, $currency);
        }

        if ($key === 'documents.size' && is_int($value)) {
            return $this->humanSize($value);
        }

        // A mime type reads as the format a person recognises, the same way a
        // photo's does on screen.
        if ($key === 'documents.format' && is_string($value)) {
            $separator = mb_strpos($value, '/');

            return mb_strtoupper($separator === false ? $value : mb_substr($value, $separator + 1));
        }

        if ($value instanceof Carbon) {
            return $value->isoFormat('LL');
        }

        if ($value instanceof ItemPhoto) {
            return (string) $value->filename;
        }

        if ($value instanceof BackedEnum) {
            return method_exists($value, 'label') ? $value->label() : (string) $value->value;
        }

        if (is_bool($value)) {
            return $value ? __('Yes') : __('No');
        }

        if (is_array($value)) {
            $parts = [];

            foreach ($value as $name => $entry) {
                if (is_array($entry)) {
                    continue;
                }
                if (is_object($entry)) {
                    continue;
                }
                $parts[] = is_string($name) ? $name.': '.$entry : (string) $entry;
            }

            return $parts === [] ? '—' : implode(', ', $parts);
        }

        return (string) $value;
    }

    private function humanSize(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes.' B';
        }

        if ($bytes < 1024 * 1024) {
            return number_format($bytes / 1024, 1).' KB';
        }

        return number_format($bytes / (1024 * 1024), 1).' MB';
    }

    /**
     * An amount in cents, in the currency of what is being exported.
     */
    public function money(?int $cents, ?string $currency): string
    {
        return $cents === null ? '—' : Money::format($cents, $currency);
    }

    /**
     * Break a wide table into slices narrow enough for the page.
     *
     * The inventory can carry twenty three columns, and an A4 portrait page
     * holds about eight of them before the text stops being readable and dompdf
     * simply runs the rest off the edge. So the table is drawn more than once,
     * each pass carrying the next columns along with the anchors that say which
     * row is which. Nothing is dropped, which is the point: a column the user
     * ticked has to appear somewhere.
     *
     * @param  list<array{key: string, label: string}>  $columns
     * @param  list<string>  $anchors  Keys repeated on every slice, if selected.
     * @return list<list<array{key: string, label: string}>>
     */
    public function columnSlices(array $columns, array $anchors = [], int $perSlice = 8): array
    {
        $pinned = array_values(array_filter($columns, fn (array $column): bool => in_array($column['key'], $anchors, true)));
        $rest = array_values(array_filter($columns, fn (array $column): bool => ! in_array($column['key'], $anchors, true)));

        if ($rest === []) {
            return $pinned === [] ? [] : [$pinned];
        }

        // The first slice already shows the anchors, so it only needs the
        // remaining room; later slices repeat them and give up the same.
        $room = max(1, $perSlice - count($pinned));
        $slices = [];

        foreach (array_chunk($rest, $room) as $chunk) {
            $slices[] = [...$pinned, ...$chunk];
        }

        return $slices;
    }

    /**
     * A line chart as an svg data uri.
     *
     * dompdf will not draw an inline `<svg>` element, but it does render svg
     * handed to it as an image, so the drawing is built here and passed in as
     * one. The alternative was to reduce the only line chart in the document to
     * more bars, which would lose the shape of the year.
     *
     * @param  list<array{label: string, value: int}>  $series
     */
    public function lineChart(array $series, int $width = 500, int $height = 126): ?string
    {
        $values = array_column($series, 'value');

        if (count($values) < 2) {
            return null;
        }

        $ceiling = max($values) > 0 ? max($values) * 1.08 : 1;
        $points = [];

        foreach ($values as $index => $value) {
            $x = ($index / (count($values) - 1)) * ($width - 4) + 2;
            $y = $height - 6 - ($value / $ceiling) * ($height - 16);
            $points[] = round($x, 1).','.round($y, 1);
        }

        $line = implode(' ', $points);
        $area = $line.' '.($width - 2).','.($height - 6).' 2,'.($height - 6);

        $svg = <<<SVG
            <svg xmlns="http://www.w3.org/2000/svg" width="{$width}" height="{$height}" viewBox="0 0 {$width} {$height}">
                <polygon points="{$area}" fill="#ededed" />
                <polyline points="{$line}" fill="none" stroke="#2b2b2b" stroke-width="1.6" />
            </svg>
            SVG;

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }

    /**
     * The share of a total, as a width a bar can be drawn at. dompdf has no
     * flexbox, so every chart here is a table cell sized in percent.
     */
    public function share(int|float|null $value, int|float|null $peak): float
    {
        if ($value === null || $peak === null || $peak <= 0) {
            return 0.0;
        }

        return round(min(100, max(0, ($value / $peak) * 100)), 2);
    }

    public function plateWidth(): int
    {
        return self::PLATE_WIDTH;
    }

    /**
     * A document's file as a data uri, for the appendix that embeds them.
     *
     * Only images are embedded. dompdf cannot place another PDF inside the one it
     * is building, and a spreadsheet or a word file has no visual form at all, so
     * everything else stays listed in the register with its metadata alone.
     */
    public function documentImage(Document $document): ?string
    {
        if ($document->path === null || ! str_starts_with((string) $document->mime_type, 'image/')) {
            return null;
        }

        $contents = $this->contents($document->path);

        if ($contents === null) {
            return null;
        }

        return $this->scale($contents, self::PLATE_WIDTH);
    }

    /**
     * The appendices repeat what the sheets already showed, gathered collection
     * wide. They are filled from the same pass over the items, so nothing is
     * queried a second time.
     *
     * @param  array<string, list<array<string, mixed>>>  $appendices
     * @param  array<string, mixed>  $item
     */
    private function gather(array &$appendices, array $item, ExportSelection $selection): void
    {
        if (! $selection->hasSection(ExportSection::Appendices)) {
            return;
        }

        if ($selection->has('appendices.photos')) {
            $photos = array_filter([
                $item['item']['sheet.item.main_photo'] ?? null,
                ...($item['item']['sheet.item.other_photos'] ?? []),
            ]);

            foreach ($photos as $photo) {
                $appendices['photos'][] = ['item' => $item['name'], 'photo' => $photo];
            }
        }

        foreach ($item['copies'] as $copy) {
            $identity = ['item' => $item['name'], 'copy' => $copy['identifier']];

            foreach (['valuations' => 'appendices.valuations', 'transactions' => 'appendices.transactions', 'insurance' => 'appendices.insurance'] as $block => $field) {
                if (! $selection->has($field)) {
                    continue;
                }

                foreach ($copy[$block] as $row) {
                    $appendices[$block][] = [...$identity, 'row' => $row];
                }
            }

            if ($selection->has('appendices.timeline')) {
                foreach ($copy['histories']['sheet.history.timeline'] ?? [] as $entry) {
                    $appendices['timeline'][] = [...$identity, 'entry' => $entry];
                }
            }
        }
    }

    private function encode(ItemPhoto $photo, int $width): ?string
    {
        $contents = $this->contents($photo->path);

        return $contents === null ? null : $this->scale($contents, $width);
    }

    private function scale(string $contents, int $width): ?string
    {
        try {
            // scaleDown keeps the ratio and leaves an already smaller image
            // alone, so a thumbnail is never blown up to fill its box.
            $image = new ImageManager(new Driver)->decodeBinary($contents)->scaleDown(width: $width);

            return 'data:image/jpeg;base64,'.base64_encode((string) $image->encode(new JpegEncoder(quality: 70)));
        } catch (ImageException) {
            // A file that is not an image, or one GD cannot read, must not take
            // the whole export down with it. Only Intervention's own failures are
            // swallowed: catching everything here once hid a coding error in this
            // very method, and every photo silently vanished from the document.
            return null;
        }
    }

    private function contents(?string $path): ?string
    {
        if ($path === null || ! $this->disk()->exists($path)) {
            return null;
        }

        return $this->disk()->get($path);
    }

    private function disk(): Filesystem
    {
        return Storage::disk((string) config('filesystems.default'));
    }
}
