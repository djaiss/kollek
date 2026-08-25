<?php

declare(strict_types=1);

use App\Enums\ExportFormat;
use App\Enums\ExportSection;
use App\Services\ExportBlueprint;
use App\ValueObjects\ExportSelection;

it('describes every section of a collection export', function (): void {
    $blueprint = ExportBlueprint::forCollection();

    expect($blueprint->sectionCases())->toBe([
        ExportSection::Cover,
        ExportSection::Summary,
        ExportSection::Inventory,
        ExportSection::ItemSheets,
        ExportSection::Documents,
        ExportSection::Appendices,
    ]);
});

it('gives every field a unique key', function (): void {
    $keys = ExportBlueprint::forCollection()->fieldKeys();

    expect($keys)->toHaveCount(count(array_unique($keys)));
});

it('counts the fields of a section', function (): void {
    $blueprint = ExportBlueprint::forCollection();

    expect($blueprint->fieldKeysFor(ExportSection::Cover))->toHaveCount(6)
        ->and($blueprint->fieldKeysFor(ExportSection::Summary))->toHaveCount(13)
        ->and($blueprint->fieldKeysFor(ExportSection::Inventory))->toHaveCount(23)
        ->and($blueprint->countFields())->toBe(127);
});

it('groups the fields of the item sheets', function (): void {
    $blueprint = ExportBlueprint::forCollection();

    expect($blueprint->group(ExportSection::ItemSheets, 'insurance'))->toHaveCount(14)
        ->and($blueprint->group(ExportSection::ItemSheets, 'histories'))->toHaveCount(6);
});

it('returns nothing for a group it does not have', function (): void {
    expect(ExportBlueprint::forCollection()->group(ExportSection::Cover, 'dundies'))->toBe([]);
});

it('narrows the columns of a group to what was selected', function (): void {
    $blueprint = ExportBlueprint::forCollection();

    $selection = new ExportSelection(
        format: ExportFormat::Pdf,
        sections: ['inventory'],
        fields: ['inventory.item_name', 'inventory.quantity'],
    );

    expect(array_column($blueprint->columns($selection, ExportSection::Inventory, 'inventory'), 'key'))
        ->toBe(['inventory.item_name', 'inventory.quantity']);
});

it('gives no columns for a section that was left out', function (): void {
    $blueprint = ExportBlueprint::forCollection();

    $selection = new ExportSelection(
        format: ExportFormat::Pdf,
        sections: [],
        fields: ['inventory.item_name'],
    );

    expect($blueprint->columns($selection, ExportSection::Inventory, 'inventory'))->toBe([]);
});

it('orders the columns the way the catalogue does rather than the way they were ticked', function (): void {
    $blueprint = ExportBlueprint::forCollection();

    $selection = new ExportSelection(
        format: ExportFormat::Pdf,
        sections: ['inventory'],
        fields: ['inventory.quantity', 'inventory.item_name'],
    );

    expect(array_column($blueprint->columns($selection, ExportSection::Inventory, 'inventory'), 'key'))
        ->toBe(['inventory.item_name', 'inventory.quantity']);
});

it('knows which fields hold money', function (): void {
    $blueprint = ExportBlueprint::forCollection();

    expect($blueprint->isMoney('inventory.estimated_value'))->toBeTrue()
        ->and($blueprint->isMoney('sheet.transaction.total'))->toBeTrue()
        ->and($blueprint->isMoney('inventory.quantity'))->toBeFalse();
});

it('labels a field, and falls back to its key', function (): void {
    $blueprint = ExportBlueprint::forCollection();

    expect($blueprint->label('inventory.item_name'))->toBe('Item name')
        ->and($blueprint->label('inventory.dundies'))->toBe('inventory.dundies');
});
