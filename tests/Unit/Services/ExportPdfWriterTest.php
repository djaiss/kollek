<?php

declare(strict_types=1);

use App\Enums\ExportFormat;
use App\Models\Catalog;
use App\Models\Copy;
use App\Models\Document;
use App\Models\Item;
use App\Models\ItemPhoto;
use App\Models\Valuation;
use App\Services\CollectionExportPayload;
use App\Services\ExportBlueprint;
use App\Services\ExportPdfWriter;
use App\ValueObjects\ExportSelection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

/**
 * A real jpeg of the given size, so the image tests exercise the encoder rather
 * than a stand-in for it.
 */
function jpegFixture(int $width, int $height): string
{
    $canvas = imagecreatetruecolor($width, $height);
    imagefilledrectangle($canvas, 0, 0, $width, $height, imagecolorallocate($canvas, 200, 120, 60));

    ob_start();
    imagejpeg($canvas);
    $binary = (string) ob_get_clean();
    imagedestroy($canvas);

    return $binary;
}

function writeDocument(Catalog $catalog, ?ExportSelection $selection = null): string
{
    $blueprint = ExportBlueprint::forCollection();
    $selection ??= ExportSelection::everything($blueprint);

    return new ExportPdfWriter(blueprint: $blueprint)->write(new CollectionExportPayload(
        catalog: $catalog,
        selection: $selection,
        blueprint: $blueprint,
    ));
}

function warehouseCollection(): Catalog
{
    $catalog = Catalog::factory()->create(['name' => 'Dunder Mifflin Memorabilia', 'currency' => 'USD']);
    $item = Item::factory()->create(['catalog_id' => $catalog->id, 'name' => 'Dundie Award']);
    $copy = Copy::factory()->create(['item_id' => $item->id, 'identifier' => 'DUNDIE-1']);
    Valuation::factory()->create(['copy_id' => $copy->id, 'amount' => 12500, 'currency_code' => 'USD']);

    return $catalog;
}

it('writes a pdf file', function (): void {
    $path = writeDocument(warehouseCollection());

    expect(file_exists($path))->toBeTrue()
        ->and(file_get_contents($path))->toStartWith('%PDF-');
});

it('writes a pdf when nothing was ticked', function (): void {
    $path = writeDocument(warehouseCollection(), new ExportSelection(
        format: ExportFormat::Pdf,
        sections: [],
        fields: [],
    ));

    expect(file_get_contents($path))->toStartWith('%PDF-');
});

it('writes a smaller file when the item sheets are left out', function (): void {
    $catalog = warehouseCollection();
    $blueprint = ExportBlueprint::forCollection();

    $whole = filesize(writeDocument($catalog));

    $without = filesize(writeDocument($catalog, new ExportSelection(
        format: ExportFormat::Pdf,
        sections: ['cover', 'summary', 'inventory', 'documents'],
        fields: $blueprint->fieldKeys(),
    )));

    expect($without)->toBeLessThan($whole);
});

it('slices a wide table into passes that fit the page', function (): void {
    $writer = new ExportPdfWriter(blueprint: ExportBlueprint::forCollection());

    $columns = array_map(fn (int $index): array => ['key' => 'c'.$index, 'label' => 'C'.$index], range(1, 20));

    $slices = $writer->columnSlices($columns, ['c1'], 8);

    expect($slices)->toHaveCount(3)
        ->and($slices[0])->toHaveCount(8)
        ->and(array_column($slices[1], 'key'))->toContain('c1')
        ->and(collect($slices)->flatten(1)->pluck('key')->unique())->toHaveCount(20);
});

it('keeps a narrow table in a single pass', function (): void {
    $writer = new ExportPdfWriter(blueprint: ExportBlueprint::forCollection());

    $columns = [['key' => 'a', 'label' => 'A'], ['key' => 'b', 'label' => 'B']];

    expect($writer->columnSlices($columns))->toHaveCount(1);
});

it('has no slices when there are no columns', function (): void {
    $writer = new ExportPdfWriter(blueprint: ExportBlueprint::forCollection());

    expect($writer->columnSlices([]))->toBe([]);
});

it('formats a value the way the document should read it', function (): void {
    $writer = new ExportPdfWriter(blueprint: ExportBlueprint::forCollection());

    expect($writer->text('inventory.estimated_value', 12500, 'USD'))->toBe('$125')
        ->and($writer->text('inventory.quantity', 3))->toBe('3')
        ->and($writer->text('inventory.item_name', null))->toBe('—')
        ->and($writer->text('inventory.tags', ['Sales', 'Awards']))->toBe('Sales, Awards')
        ->and($writer->text('inventory.custom_fields', ['Grade' => 'A']))->toBe('Grade: A')
        ->and($writer->text('sheet.insurance.is_scheduled_item', true))->toBe('Yes')
        ->and($writer->text('documents.size', 2048))->toBe('2.0 KB')
        ->and($writer->text('documents.format', 'image/jpeg'))->toBe('JPEG');
});

it('draws a line chart only when there is a line to draw', function (): void {
    $writer = new ExportPdfWriter(blueprint: ExportBlueprint::forCollection());

    $series = [['label' => 'Jan', 'value' => 100], ['label' => 'Feb', 'value' => 300]];

    expect($writer->lineChart($series))->toStartWith('data:image/svg+xml;base64,')
        ->and($writer->lineChart([['label' => 'Jan', 'value' => 100]]))->toBeNull()
        ->and($writer->lineChart([]))->toBeNull();
});

it('reads a share as a percentage of its peak, and guards a zero peak', function (): void {
    $writer = new ExportPdfWriter(blueprint: ExportBlueprint::forCollection());

    expect($writer->share(5, 10))->toBe(50.0)
        ->and($writer->share(20, 10))->toBe(100.0)
        ->and($writer->share(5, 0))->toBe(0.0)
        ->and($writer->share(null, 10))->toBe(0.0);
});

it('has no image for a photo that is not there', function (): void {
    $writer = new ExportPdfWriter(blueprint: ExportBlueprint::forCollection());

    expect($writer->image(null))->toBeNull();
});

it('inlines a photo as a data uri, scaled to its box', function (): void {
    Storage::fake(config('filesystems.default'));

    $photo = ItemPhoto::factory()->create(['path' => 'items/1/dundie.jpg']);
    Storage::disk((string) config('filesystems.default'))->put($photo->path, jpegFixture(240, 160));

    $writer = new ExportPdfWriter(blueprint: ExportBlueprint::forCollection());
    $encoded = $writer->image($photo, 90);

    expect($encoded)->toStartWith('data:image/jpeg;base64,');

    $binary = base64_decode(substr((string) $encoded, strlen('data:image/jpeg;base64,')), true);
    $size = getimagesizefromstring((string) $binary);

    expect($size[0])->toBe(90)
        ->and($size[2])->toBe(IMAGETYPE_JPEG);
});

it('reads the same photo once however often it is drawn', function (): void {
    Storage::fake(config('filesystems.default'));

    $photo = ItemPhoto::factory()->create(['path' => 'items/1/dundie.jpg']);
    $disk = Storage::disk((string) config('filesystems.default'));
    $disk->put($photo->path, jpegFixture(240, 160));

    $writer = new ExportPdfWriter(blueprint: ExportBlueprint::forCollection());
    $first = $writer->image($photo, 90);

    // The file is gone, so anything but a cached answer would come back null.
    $disk->delete($photo->path);

    expect($writer->image($photo, 90))->toBe($first);
});

it('has no image for a photo whose file is missing', function (): void {
    Storage::fake(config('filesystems.default'));

    $photo = ItemPhoto::factory()->create(['path' => 'items/1/gone.jpg']);

    expect(new ExportPdfWriter(blueprint: ExportBlueprint::forCollection())->image($photo))->toBeNull();
});

it('has no image for a file that is not one', function (): void {
    Storage::fake(config('filesystems.default'));

    $photo = ItemPhoto::factory()->create(['path' => 'items/1/notes.txt']);
    Storage::disk((string) config('filesystems.default'))->put($photo->path, 'That\'s what she said.');

    expect(new ExportPdfWriter(blueprint: ExportBlueprint::forCollection())->image($photo))->toBeNull();
});

it('embeds an image document but not any other kind of file', function (): void {
    Storage::fake(config('filesystems.default'));
    $disk = Storage::disk((string) config('filesystems.default'));

    $image = Document::factory()->create(['path' => 'documents/certificate.jpg', 'mime_type' => 'image/jpeg']);
    $disk->put($image->path, jpegFixture(200, 120));

    $pdf = Document::factory()->create(['path' => 'documents/receipt.pdf', 'mime_type' => 'application/pdf']);
    $disk->put($pdf->path, '%PDF-1.7 not really');

    $external = Document::factory()->create(['path' => null, 'external_url' => 'https://example.com/x.jpg', 'mime_type' => 'image/jpeg']);

    $writer = new ExportPdfWriter(blueprint: ExportBlueprint::forCollection());

    expect($writer->documentImage($image))->toStartWith('data:image/jpeg;base64,')
        ->and($writer->documentImage($pdf))->toBeNull()
        ->and($writer->documentImage($external))->toBeNull();
});
