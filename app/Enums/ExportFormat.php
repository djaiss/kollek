<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * The file an export comes out as.
 *
 * The two read the same data and differ only in what they are good for: the PDF
 * is a laid out document to print or hand to an insurer, the workbook is one
 * sheet per section with columns a spreadsheet can filter and sort.
 */
enum ExportFormat: string
{
    case Pdf = 'pdf';
    case Excel = 'xlsx';

    public function label(): string
    {
        return match ($this) {
            self::Pdf => __('PDF document'),
            self::Excel => __('Excel workbook'),
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Pdf => __('A layout ready to print or hand to an insurer.'),
            self::Excel => __('One sheet per section, with filterable and sortable columns.'),
        };
    }

    public function extension(): string
    {
        return match ($this) {
            self::Pdf => 'pdf',
            self::Excel => 'xlsx',
        };
    }

    public function mimeType(): string
    {
        return match ($this) {
            self::Pdf => 'application/pdf',
            self::Excel => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        };
    }

    /**
     * The short word drawn on the format card, which is the file extension
     * rather than the name of the format.
     */
    public function badge(): string
    {
        return mb_strtoupper($this->extension());
    }
}
