<?php

declare(strict_types=1);

use App\Enums\ExportFormat;
use App\Models\Catalog;
use App\Models\Copy;
use App\Models\Item;
use App\Models\Valuation;
use App\Services\CollectionExportPayload;
use App\Services\ExportBlueprint;
use App\Services\ExportExcelWriter;
use App\ValueObjects\ExportSelection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use OpenSpout\Reader\XLSX\Reader;

uses(RefreshDatabase::class);

/**
 * Read a written workbook back as a map of sheet name to its rows.
 */
function readWorkbook(string $path): array
{
    $reader = new Reader;
    $reader->open($path);

    $sheets = [];

    foreach ($reader->getSheetIterator() as $sheet) {
        $rows = [];

        foreach ($sheet->getRowIterator() as $row) {
            $rows[] = $row->toArray();
        }

        $sheets[$sheet->getName()] = $rows;
    }

    $reader->close();

    return $sheets;
}

function writeWorkbook(Catalog $catalog, ?ExportSelection $selection = null): string
{
    $blueprint = ExportBlueprint::forCollection();
    $selection ??= ExportSelection::everything($blueprint, ExportFormat::Excel);

    return new ExportExcelWriter(blueprint: $blueprint)->write(new CollectionExportPayload(
        catalog: $catalog,
        selection: $selection,
        blueprint: $blueprint,
    ));
}

function memorabiliaWithOneCopy(): Catalog
{
    $catalog = Catalog::factory()->create(['name' => 'Dunder Mifflin Memorabilia', 'currency' => 'USD']);
    $item = Item::factory()->create(['catalog_id' => $catalog->id, 'name' => 'Dundie Award']);
    $copy = Copy::factory()->create(['item_id' => $item->id, 'identifier' => 'DUNDIE-1', 'quantity' => 2]);
    Valuation::factory()->create(['copy_id' => $copy->id, 'amount' => 12545, 'currency_code' => 'USD']);

    return $catalog;
}

it('writes one sheet per section', function (): void {
    $sheets = readWorkbook(writeWorkbook(memorabiliaWithOneCopy()));

    expect(array_keys($sheets))->toContain('Summary', 'Inventory', 'Items', 'Copies', 'Transactions', 'Valuations', 'Insurance', 'Provenance', 'Documents');
});

it('heads every sheet with the labels of its columns', function (): void {
    $sheets = readWorkbook(writeWorkbook(memorabiliaWithOneCopy()));

    expect($sheets['Inventory'][0])->toContain('Item name', 'Copy identifier', 'Quantity')
        ->and($sheets['Summary'][0])->toBe(['Measure', 'Value']);
});

it('writes a row per copy under the header', function (): void {
    $sheets = readWorkbook(writeWorkbook(memorabiliaWithOneCopy()));

    expect($sheets['Inventory'])->toHaveCount(2)
        ->and($sheets['Inventory'][1])->toContain('Dundie Award', 'DUNDIE-1');
});

it('writes money as a number in the major unit so the column stays sortable', function (): void {
    $sheets = readWorkbook(writeWorkbook(memorabiliaWithOneCopy()));

    $columns = array_flip($sheets['Inventory'][0]);

    expect($sheets['Inventory'][1][$columns['Current estimated value']])->toBe(125.45);
});

it('writes a quantity as a number rather than as text', function (): void {
    $sheets = readWorkbook(writeWorkbook(memorabiliaWithOneCopy()));

    $columns = array_flip($sheets['Inventory'][0]);

    expect($sheets['Inventory'][1][$columns['Quantity']])->toBe(2);
});

it('names the item and the copy on every history sheet', function (): void {
    $sheets = readWorkbook(writeWorkbook(memorabiliaWithOneCopy()));

    expect($sheets['Valuations'][0][0])->toBe('Item')
        ->and($sheets['Valuations'][0][1])->toBe('Copy')
        ->and($sheets['Valuations'][1][0])->toBe('Dundie Award')
        ->and($sheets['Valuations'][1][1])->toBe('DUNDIE-1');
});

it('leaves out the sheet of a section that was not ticked', function (): void {
    $blueprint = ExportBlueprint::forCollection();

    $sheets = readWorkbook(writeWorkbook(memorabiliaWithOneCopy(), new ExportSelection(
        format: ExportFormat::Excel,
        sections: ['inventory'],
        fields: $blueprint->fieldKeys(),
    )));

    expect(array_keys($sheets))->toBe(['Inventory']);
});

it('leaves out a column that was not ticked', function (): void {
    $sheets = readWorkbook(writeWorkbook(memorabiliaWithOneCopy(), new ExportSelection(
        format: ExportFormat::Excel,
        sections: ['inventory'],
        fields: ['inventory.item_name', 'inventory.quantity'],
    )));

    expect($sheets['Inventory'][0])->toBe(['Item name', 'Quantity']);
});

it('writes a workbook even when nothing was ticked', function (): void {
    $path = writeWorkbook(memorabiliaWithOneCopy(), new ExportSelection(
        format: ExportFormat::Excel,
        sections: [],
        fields: [],
    ));

    expect(file_exists($path))->toBeTrue()
        ->and(filesize($path))->toBeGreaterThan(0);
});
