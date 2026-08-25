<?php

declare(strict_types=1);

use App\Enums\ExportFormat;
use App\Models\Catalog;
use App\Services\ExportBlueprint;
use App\ViewModels\CollectionExport;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function exportViewModel(?Catalog $catalog = null, int $items = 412, int $copies = 1138, int $documents = 264): CollectionExport
{
    return new CollectionExport(
        catalog: $catalog ?? Catalog::factory()->create(['name' => 'Dunder Mifflin Memorabilia']),
        blueprint: ExportBlueprint::forCollection(),
        itemCount: $items,
        copyCount: $copies,
        documentCount: $documents,
    );
}

it('offers both formats', function (): void {
    expect(array_column(exportViewModel()->formats(), 'value'))->toBe(['pdf', 'xlsx']);
});

it('numbers the sections in the order they appear in the document', function (): void {
    $sections = exportViewModel()->sections();

    expect($sections[0]['key'])->toBe('cover')
        ->and($sections[0]['number'])->toBe(1)
        ->and($sections[0]['count'])->toBe(6)
        ->and($sections[3]['key'])->toBe('item_sheets')
        ->and($sections[3]['number'])->toBe(4);
});

it('groups the fields of the item sheets under their own headings', function (): void {
    $sections = collect(exportViewModel()->sections())->firstWhere('key', 'item_sheets');

    expect(array_column($sections['groups'], 'key'))
        ->toBe(['identification', 'copies', 'transactions', 'valuations', 'insurance', 'provenance', 'histories']);
});

it('reports the whole catalogue as the total to tick against', function (): void {
    expect(exportViewModel()->fieldCount())->toBe(127)
        ->and(exportViewModel()->sectionCount())->toBe(6)
        ->and(exportViewModel()->fieldKeys())->toHaveCount(127);
});

it('reports the counts it was handed', function (): void {
    expect(exportViewModel()->counts())->toBe(['items' => 412, 'copies' => 1138, 'documents' => 264]);
});

it('estimates more pages for a bigger collection', function (): void {
    expect(exportViewModel(items: 412, copies: 1138)->estimatedPages())
        ->toBeGreaterThan(exportViewModel(items: 4, copies: 11)->estimatedPages());
});

it('estimates at least one page for an empty collection', function (): void {
    expect(exportViewModel(items: 0, copies: 0, documents: 0)->estimatedPages())->toBeGreaterThanOrEqual(1);
});

it('estimates a couple of pages for each item sheet', function (): void {
    $one = exportViewModel(items: 1, copies: 1, documents: 0)->estimatedPages();
    $ten = exportViewModel(items: 10, copies: 10, documents: 0)->estimatedPages();

    expect($ten - $one)->toBeGreaterThan(10);
});

it('offers the three switches, with the embed one off', function (): void {
    $options = collect(exportViewModel()->options())->keyBy('key');

    expect($options->keys()->all())->toBe(['thumbnails', 'embed_documents', 'page_per_item'])
        ->and($options['thumbnails']['default'])->toBeTrue()
        ->and($options['embed_documents']['default'])->toBeFalse()
        ->and($options['embed_documents']['note'])->not->toBeNull();
});

it('names the file after the collection, the day and the format', function (): void {
    $model = exportViewModel();

    expect($model->fileName(ExportFormat::Pdf))->toBe('dunder-mifflin-memorabilia-'.now()->format('Y-m-d').'.pdf')
        ->and($model->fileName(ExportFormat::Excel))->toBe('dunder-mifflin-memorabilia-'.now()->format('Y-m-d').'.xlsx')
        ->and($model->fileNames())->toHaveKeys(['pdf', 'xlsx']);
});

it('names the file usably when the collection name has no letters', function (): void {
    $model = exportViewModel(Catalog::factory()->create(['name' => '📦']));

    expect($model->fileName(ExportFormat::Pdf))->toStartWith('collection-');
});
