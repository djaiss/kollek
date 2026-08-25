<?php

declare(strict_types=1);

use App\Enums\ExportFormat;

it('has a label and a description for every format', function (): void {
    foreach (ExportFormat::cases() as $format) {
        expect($format->label())->not->toBeEmpty()
            ->and($format->description())->not->toBeEmpty();
    }
});

it('knows its extension and mime type', function (): void {
    expect(ExportFormat::Pdf->extension())->toBe('pdf')
        ->and(ExportFormat::Pdf->mimeType())->toBe('application/pdf')
        ->and(ExportFormat::Excel->extension())->toBe('xlsx')
        ->and(ExportFormat::Excel->mimeType())->toBe('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
});

it('badges a format with its extension in capitals', function (): void {
    expect(ExportFormat::Pdf->badge())->toBe('PDF')
        ->and(ExportFormat::Excel->badge())->toBe('XLSX');
});
