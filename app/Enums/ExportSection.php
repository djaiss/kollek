<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * The parts an export is built out of, in the order they appear in the file.
 *
 * A section is a block of the document the user may leave out whole, and each
 * one owns a set of fields that may be left out one at a time. What those fields
 * are lives in the blueprint rather than here, because the answer depends on
 * whether a whole collection or a single item is being exported.
 */
enum ExportSection: string
{
    case Cover = 'cover';
    case Summary = 'summary';
    case Inventory = 'inventory';
    case ItemSheets = 'item_sheets';
    case Documents = 'documents';
    case Appendices = 'appendices';

    public function label(): string
    {
        return match ($this) {
            self::Cover => __('Cover page'),
            self::Summary => __('Collection summary'),
            self::Inventory => __('Inventory table'),
            self::ItemSheets => __('Detailed sheet per item'),
            self::Documents => __('Document register'),
            self::Appendices => __('Appendices'),
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Cover => __('Identity of the collection and the date it was generated.'),
            self::Summary => __('Statistics Kollek already works out.'),
            self::Inventory => __('One row per copy, with everything recorded about it.'),
            self::ItemSheets => __('Everything known about each item, one sheet at a time.'),
            self::Documents => __('The documents on file, listed with their metadata.'),
            self::Appendices => __('The full histories, gathered at the end.'),
        };
    }

    /**
     * The name of the sheet this section becomes in a workbook. Sections that
     * spread over several sheets, the item sheets in particular, name only the
     * first of them; the rest are named by the writer.
     */
    public function sheetName(): string
    {
        return match ($this) {
            self::Cover => __('Cover'),
            self::Summary => __('Summary'),
            self::Inventory => __('Inventory'),
            self::ItemSheets => __('Items'),
            self::Documents => __('Documents'),
            self::Appendices => __('Appendices'),
        };
    }
}
