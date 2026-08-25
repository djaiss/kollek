<?php

declare(strict_types=1);

use App\Enums\ExportFormat;
use App\Enums\ExportSection;
use App\Services\ExportBlueprint;
use App\ValueObjects\ExportSelection;

it('knows which sections and fields are on', function (): void {
    $selection = new ExportSelection(
        format: ExportFormat::Pdf,
        sections: ['cover'],
        fields: ['cover.name'],
    );

    expect($selection->hasSection(ExportSection::Cover))->toBeTrue()
        ->and($selection->hasSection(ExportSection::Summary))->toBeFalse()
        ->and($selection->has('cover.name'))->toBeTrue()
        ->and($selection->has('cover.currency'))->toBeFalse();
});

it('wants a field only when its section is on too', function (): void {
    $selection = new ExportSelection(
        format: ExportFormat::Pdf,
        sections: [],
        fields: ['cover.name'],
    );

    expect($selection->has('cover.name'))->toBeTrue()
        ->and($selection->wants(ExportSection::Cover, 'cover.name'))->toBeFalse();
});

it('answers whether any of several fields survived', function (): void {
    $selection = new ExportSelection(
        format: ExportFormat::Pdf,
        sections: ['summary'],
        fields: ['summary.items'],
    );

    expect($selection->wantsAny(ExportSection::Summary, 'summary.copies', 'summary.items'))->toBeTrue()
        ->and($selection->wantsAny(ExportSection::Summary, 'summary.copies', 'summary.value'))->toBeFalse()
        ->and($selection->wantsAny(ExportSection::Cover, 'summary.items'))->toBeFalse();
});

it('reads the options, and treats a missing one as off', function (): void {
    $selection = new ExportSelection(
        format: ExportFormat::Pdf,
        sections: [],
        fields: [],
        options: ['thumbnails' => true],
    );

    expect($selection->option('thumbnails'))->toBeTrue()
        ->and($selection->option('embed_documents'))->toBeFalse();
});

it('builds itself from a validated payload', function (): void {
    $selection = ExportSelection::fromRequest([
        'format' => 'xlsx',
        'sections' => ['cover', 'summary'],
        'fields' => ['cover.name', 'summary.items'],
        'options' => ['thumbnails' => '1', 'embed_documents' => '0'],
    ], ExportBlueprint::forCollection());

    expect($selection->format)->toBe(ExportFormat::Excel)
        ->and($selection->sections())->toBe(['cover', 'summary'])
        ->and($selection->fields())->toBe(['cover.name', 'summary.items'])
        ->and($selection->option('thumbnails'))->toBeTrue()
        ->and($selection->option('embed_documents'))->toBeFalse();
});

it('drops a section or a field the blueprint does not know', function (): void {
    $selection = ExportSelection::fromRequest([
        'format' => 'pdf',
        'sections' => ['cover', 'dundies'],
        'fields' => ['cover.name', 'cover.salary'],
    ], ExportBlueprint::forCollection());

    expect($selection->sections())->toBe(['cover'])
        ->and($selection->fields())->toBe(['cover.name']);
});

it('reads an empty payload as nothing selected', function (): void {
    $selection = ExportSelection::fromRequest(['format' => 'pdf'], ExportBlueprint::forCollection());

    expect($selection->sections())->toBe([])
        ->and($selection->fields())->toBe([])
        ->and($selection->wants(ExportSection::Cover, 'cover.name'))->toBeFalse();
});

it('builds a selection of everything the blueprint offers', function (): void {
    $blueprint = ExportBlueprint::forCollection();
    $selection = ExportSelection::everything($blueprint, ExportFormat::Excel);

    expect($selection->format)->toBe(ExportFormat::Excel)
        ->and($selection->fields())->toHaveCount($blueprint->countFields())
        ->and($selection->sections())->toHaveCount(6)
        ->and($selection->option('thumbnails'))->toBeTrue()
        ->and($selection->option('embed_documents'))->toBeFalse();
});
