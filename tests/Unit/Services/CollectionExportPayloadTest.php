<?php

declare(strict_types=1);

use App\Enums\ExportFormat;
use App\Models\Catalog;
use App\Models\Copy;
use App\Models\Document;
use App\Models\Item;
use App\Models\Transaction;
use App\Models\Valuation;
use App\Services\CollectionExportPayload;
use App\Services\ExportBlueprint;
use App\ValueObjects\ExportSelection;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function payloadFor(Catalog $catalog, ?ExportSelection $selection = null): CollectionExportPayload
{
    $blueprint = ExportBlueprint::forCollection();

    return new CollectionExportPayload(
        catalog: $catalog,
        selection: $selection ?? ExportSelection::everything($blueprint),
        blueprint: $blueprint,
    );
}

function scrantonBranch(): Catalog
{
    return Catalog::factory()->create(['name' => 'Dunder Mifflin Memorabilia', 'currency' => 'USD']);
}

it('describes what is being exported', function (): void {
    $catalog = scrantonBranch();

    $meta = payloadFor($catalog)->meta();

    expect($meta['title'])->toBe('Dunder Mifflin Memorabilia')
        ->and($meta['currency'])->toBe('USD')
        ->and($meta['fileSlug'])->toBe('dunder-mifflin-memorabilia')
        ->and($meta['generatedAt'])->toBeInstanceOf(Carbon::class);
});

it('falls back to a usable slug when the name has no letters', function (): void {
    $catalog = Catalog::factory()->create(['name' => '📦']);

    expect(payloadFor($catalog)->meta()['fileSlug'])->toBe('collection');
});

it('builds the cover out of the collection and its totals', function (): void {
    $catalog = scrantonBranch();
    $item = Item::factory()->create(['catalog_id' => $catalog->id, 'name' => 'Dundie Award']);
    Copy::factory()->create(['item_id' => $item->id]);

    $cover = payloadFor($catalog)->cover();

    expect($cover['cover.name'])->toBe('Dunder Mifflin Memorabilia')
        ->and($cover['cover.currency'])->toBe('USD')
        ->and($cover['cover.items'])->toBe(1)
        ->and($cover['cover.copies'])->toBe(1);
});

it('leaves out a section that was not ticked', function (): void {
    $catalog = scrantonBranch();
    $blueprint = ExportBlueprint::forCollection();

    $payload = payloadFor($catalog, new ExportSelection(
        format: ExportFormat::Pdf,
        sections: ['summary'],
        fields: $blueprint->fieldKeys(),
    ));

    expect($payload->cover())->toBeNull()
        ->and($payload->inventory())->toBeNull()
        ->and($payload->itemSheets())->toBeNull()
        ->and($payload->documents())->toBeNull()
        ->and($payload->appendices())->toBeNull()
        ->and($payload->summary())->not->toBeNull();
});

it('keeps only the ticked fields of a section it does render', function (): void {
    $catalog = scrantonBranch();

    $payload = payloadFor($catalog, new ExportSelection(
        format: ExportFormat::Pdf,
        sections: ['cover'],
        fields: ['cover.name', 'cover.currency'],
    ));

    expect(array_keys($payload->cover()))->toBe(['cover.name', 'cover.currency']);
});

it('builds one inventory row per copy', function (): void {
    $catalog = scrantonBranch();
    $item = Item::factory()->create(['catalog_id' => $catalog->id, 'name' => 'Dundie Award']);
    Copy::factory()->count(3)->create(['item_id' => $item->id]);

    $inventory = payloadFor($catalog)->inventory();

    expect($inventory['rows'])->toHaveCount(3)
        ->and($inventory['columns'])->toHaveCount(23)
        ->and($inventory['rows'][0]['inventory.item_name'])->toBe('Dundie Award')
        ->and($inventory['rows'][0]['inventory.collection'])->toBe('Dunder Mifflin Memorabilia');
});

it('carries the money as cents and the dates as carbon, for the writers to format', function (): void {
    $catalog = scrantonBranch();
    $item = Item::factory()->create(['catalog_id' => $catalog->id, 'name' => 'Dundie Award']);
    $copy = Copy::factory()->create(['item_id' => $item->id]);
    Valuation::factory()->create(['copy_id' => $copy->id, 'amount' => 12500, 'valued_at' => '2025-03-04']);

    $row = payloadFor($catalog)->inventory()['rows'][0];

    expect($row['inventory.estimated_value'])->toBe(12500)
        ->and($row['inventory.valued_at'])->toBeInstanceOf(Carbon::class);
});

it('builds one sheet per item, with its copies', function (): void {
    $catalog = scrantonBranch();
    $item = Item::factory()->create(['catalog_id' => $catalog->id, 'name' => 'Jim\'s stapler in jello']);
    Copy::factory()->count(2)->create(['item_id' => $item->id]);
    Item::factory()->create(['catalog_id' => $catalog->id, 'name' => 'Beet farm ledger']);

    $sheets = iterator_to_array(payloadFor($catalog)->itemSheets());

    expect($sheets)->toHaveCount(2)
        ->and($sheets[0]['name'])->toBe('Jim\'s stapler in jello')
        ->and($sheets[0]['copies'])->toHaveCount(2)
        ->and($sheets[0]['item']['sheet.item.name'])->toBe('Jim\'s stapler in jello');
});

it('carries the transactions of a copy onto its sheet', function (): void {
    $catalog = scrantonBranch();
    $item = Item::factory()->create(['catalog_id' => $catalog->id, 'name' => 'Dundie Award']);
    $copy = Copy::factory()->create(['item_id' => $item->id]);
    Transaction::factory()->create(['copy_id' => $copy->id, 'counterparty' => 'Chili\'s', 'amount' => 4200]);

    $sheets = iterator_to_array(payloadFor($catalog)->itemSheets());

    expect($sheets[0]['copies'][0]['transactions'])->toHaveCount(1)
        ->and($sheets[0]['copies'][0]['transactions'][0]['sheet.transaction.counterparty'])->toBe('Chili\'s')
        ->and($sheets[0]['copies'][0]['transactions'][0]['sheet.transaction.amount'])->toBe(4200);
});

it('lists the documents filed against the collection', function (): void {
    $catalog = scrantonBranch();
    $item = Item::factory()->create(['catalog_id' => $catalog->id]);
    $copy = Copy::factory()->create(['item_id' => $item->id]);

    Document::factory()->create([
        'account_id' => $catalog->account_id,
        'documentable_type' => 'copy',
        'documentable_id' => $copy->id,
        'name' => 'Dundie certificate',
    ]);

    $payload = payloadFor($catalog);

    expect($payload->documentCount())->toBe(1)
        ->and($payload->documents()['rows'][0]['documents.name'])->toBe('Dundie certificate')
        ->and($payload->documents()['rows'][0]['documents.record'])->toBe('Copy');
});

it('does not count the documents of another collection', function (): void {
    $catalog = scrantonBranch();
    $other = Catalog::factory()->create(['account_id' => $catalog->account_id, 'name' => 'Vance Refrigeration']);
    $copy = Copy::factory()->create(['item_id' => Item::factory()->create(['catalog_id' => $other->id])->id]);

    Document::factory()->create([
        'account_id' => $catalog->account_id,
        'documentable_type' => 'copy',
        'documentable_id' => $copy->id,
    ]);

    expect(payloadFor($catalog)->documentCount())->toBe(0);
});

it('carries the document model only when the files are to be embedded', function (): void {
    $catalog = scrantonBranch();
    $copy = Copy::factory()->create(['item_id' => Item::factory()->create(['catalog_id' => $catalog->id])->id]);
    Document::factory()->create(['account_id' => $catalog->account_id, 'documentable_type' => 'copy', 'documentable_id' => $copy->id]);

    $blueprint = ExportBlueprint::forCollection();

    $without = payloadFor($catalog)->documents()['rows'][0];

    $with = payloadFor($catalog, new ExportSelection(
        format: ExportFormat::Pdf,
        sections: $blueprint->sectionKeys(),
        fields: $blueprint->fieldKeys(),
        options: ['embed_documents' => true],
    ))->documents()['rows'][0];

    expect($without)->not->toHaveKey('_document')
        ->and($with['_document'])->toBeInstanceOf(Document::class);
});
