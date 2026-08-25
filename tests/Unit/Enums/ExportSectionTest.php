<?php

declare(strict_types=1);

use App\Enums\ExportSection;

it('has a label, a description and a sheet name for every section', function (): void {
    foreach (ExportSection::cases() as $section) {
        expect($section->label())->not->toBeEmpty()
            ->and($section->description())->not->toBeEmpty()
            ->and($section->sheetName())->not->toBeEmpty();
    }
});

it('keeps its sheet name short enough for a worksheet', function (): void {
    foreach (ExportSection::cases() as $section) {
        expect(mb_strlen($section->sheetName()))->toBeLessThanOrEqual(31);
    }
});
