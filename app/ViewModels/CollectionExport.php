<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Enums\ExportFormat;
use App\Models\Catalog;
use App\Services\ExportBlueprint;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;

/**
 * What the export screen shows.
 *
 * The section and field lists come out of the blueprint, so the checkboxes are
 * the same catalogue the writers read and the two cannot drift apart. Nothing
 * here goes to the database: the counts the summary card reports are handed in by
 * the controller, which is the one place allowed to ask for them.
 *
 * The page estimate is a guess and is presented as one. It exists because
 * choosing what to include is easier when the size of the result is visible, and
 * it is recomputed in the browser as boxes are ticked; this only provides the
 * numbers it starts from.
 */
class CollectionExport
{
    /**
     * Roughly how much of a page each kind of thing takes, which is what turns a
     * selection into an estimate. These are averages measured against real
     * exports rather than a calculation, so they are named as what they are.
     *
     * An item sheet runs to a couple of pages because it carries a table for each
     * of the transactions, the valuations, the coverage, the provenance and the
     * histories. How many depends entirely on what has been recorded, so the
     * figure is an average and the screen presents it as an estimate.
     */
    private const float PAGES_PER_ITEM_SHEET = 2.2;

    private const float ROWS_PER_INVENTORY_PAGE = 26.0;

    public function __construct(
        private readonly Catalog $catalog,
        private readonly ExportBlueprint $blueprint,
        private readonly int $itemCount,
        private readonly int $copyCount,
        private readonly int $documentCount,
    ) {}

    /**
     * @return list<array{value: string, label: string, description: string, badge: string}>
     */
    public function formats(): array
    {
        return array_map(fn (ExportFormat $format): array => [
            'value' => $format->value,
            'label' => $format->label(),
            'description' => $format->description(),
            'badge' => $format->badge(),
        ], ExportFormat::cases());
    }

    /**
     * The sections, numbered as they appear in the document, each with the field
     * groups the user ticks inside it.
     *
     * @return list<array{key: string, number: int, label: string, description: string, groups: list<array{key: string, label: ?string, fields: list<array{key: string, label: string}>}>, count: int}>
     */
    public function sections(): array
    {
        $sections = [];

        foreach ($this->blueprint->sections() as $number => $section) {
            $sections[] = [
                'key' => $section['section']->value,
                'number' => $number + 1,
                'label' => $section['section']->label(),
                'description' => $section['section']->description(),
                'groups' => $section['groups'],
                'count' => count($this->blueprint->fieldKeysFor($section['section'])),
            ];
        }

        return $sections;
    }

    /**
     * @return list<string>
     */
    public function fieldKeys(): array
    {
        return $this->blueprint->fieldKeys();
    }

    public function fieldCount(): int
    {
        return $this->blueprint->countFields();
    }

    public function sectionCount(): int
    {
        return count($this->blueprint->sections());
    }

    /**
     * The three switches that change how the document is built rather than what
     * it contains.
     *
     * @return list<array{key: string, label: string, note: ?string, default: bool, formats: list<string>}>
     */
    public function options(): array
    {
        return [
            [
                'key' => 'thumbnails',
                'label' => __('Include photo thumbnails'),
                'note' => null,
                'default' => true,
                'formats' => [ExportFormat::Pdf->value],
            ],
            [
                'key' => 'embed_documents',
                'label' => __('Embed the document files'),
                'note' => __('Only images can be reproduced. Every other file stays in Kollek.'),
                'default' => false,
                'formats' => [ExportFormat::Pdf->value],
            ],
            [
                'key' => 'page_per_item',
                'label' => __('A new page per item'),
                'note' => null,
                'default' => true,
                'formats' => [ExportFormat::Pdf->value],
            ],
        ];
    }

    /**
     * The figures the summary card reports, which are what the selection is
     * measured against.
     *
     * @return array{items: int, copies: int, documents: int}
     */
    public function counts(): array
    {
        return [
            'items' => $this->itemCount,
            'copies' => $this->copyCount,
            'documents' => $this->documentCount,
        ];
    }

    /**
     * The page count a whole export comes to, which is where the estimate in the
     * browser starts from before anything is unticked.
     */
    public function estimatedPages(): int
    {
        return max(1, (int) round(
            2
            + ceil($this->copyCount / self::ROWS_PER_INVENTORY_PAGE)
            + $this->itemCount * self::PAGES_PER_ITEM_SHEET
            + ceil($this->documentCount / self::ROWS_PER_INVENTORY_PAGE)
        ));
    }

    /**
     * @return array{items: float, copies: float, documents: float, sheets: float}
     */
    public function pageWeights(): array
    {
        return [
            'items' => self::PAGES_PER_ITEM_SHEET,
            'copies' => 1 / self::ROWS_PER_INVENTORY_PAGE,
            'documents' => 1 / self::ROWS_PER_INVENTORY_PAGE,
            'sheets' => self::PAGES_PER_ITEM_SHEET,
        ];
    }

    /**
     * The name the file downloads as. The collection name is free text, so it can
     * slug down to nothing and still has to produce something usable.
     */
    public function fileName(ExportFormat $format): string
    {
        $slug = Str::slug($this->catalog->name);

        return ($slug !== '' ? $slug : 'collection').'-'.Date::now()->format('Y-m-d').'.'.$format->extension();
    }

    /**
     * @return array<string, string>
     */
    public function fileNames(): array
    {
        $names = [];

        foreach (ExportFormat::cases() as $format) {
            $names[$format->value] = $this->fileName($format);
        }

        return $names;
    }
}
