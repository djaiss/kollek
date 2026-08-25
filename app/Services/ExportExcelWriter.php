<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ExportPayload;
use App\Contracts\ExportWriter;
use App\Enums\ExportSection;
use App\Models\ItemPhoto;
use App\ValueObjects\ExportSelection;
use App\ValueObjects\TimelineEntry;
use BackedEnum;
use Carbon\Carbon;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\AutoFilter;
use OpenSpout\Writer\Common\Entity\Sheet;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Writer\XLSX\Properties;
use OpenSpout\Writer\XLSX\Writer;

/**
 * Writes an export as a workbook, one sheet per section.
 *
 * The point of this format is that a spreadsheet can do the rest: every sheet
 * gets a frozen bold header and a filter over its columns, and every value is
 * written as the type it is rather than as a sentence. Money lands as a number in
 * the major unit with its currency in its own column, and dates land as ISO
 * strings, so both sort the way a reader expects instead of alphabetically.
 *
 * The sheets are all created up front and written to in one pass, because the
 * item sheets arrive as a generator that can only be walked once and every
 * history sheet is filled from it. Switching the current sheet per row is what
 * openspout is for, and costs nothing.
 */
class ExportExcelWriter implements ExportWriter
{
    public function __construct(
        private readonly ExportBlueprint $blueprint,
    ) {}

    public function write(ExportPayload $payload): string
    {
        $payload->selection();
        $path = (string) tempnam(sys_get_temp_dir(), 'kollek-export-');

        $meta = $payload->meta();

        // XLSX carries its authorship in the workbook properties rather than
        // through setCreator, which the writer refuses for this format.
        $writer = new Writer(new Options(properties: new Properties(
            title: $meta['title'],
            application: 'Kollek',
            creator: 'Kollek',
            lastModifiedBy: 'Kollek',
        )));

        $writer->openToFile($path);

        $header = new Style()->withFontBold(true);
        $first = true;

        /** @var array<string, array{sheet: Sheet, columns: list<array{key: string, label: string}>, rows: int}> $sheets */
        $sheets = [];

        /** @var list<string> $named */
        $named = [];

        // Every sheet is opened before anything is written, so the pass over the
        // item generator below can put each row wherever it belongs.
        foreach ($this->plan($payload) as $name => $columns) {
            $sheet = $first ? $writer->getCurrentSheet() : $writer->addNewSheetAndMakeItCurrent();
            $first = false;

            $assigned = $this->sheetName($name, $named);
            $named[] = $assigned;
            $sheet->setName($assigned);
            $writer->addRow(Row::fromValuesWithStyle(array_column($columns, 'label'), $header));

            $sheets[$name] = ['sheet' => $sheet, 'columns' => $columns, 'rows' => 1];
        }

        if ($sheets === []) {
            $writer->close();

            return $path;
        }

        $this->fill($writer, $sheets, $payload);

        // The filter and the frozen header need the final row count, so they are
        // applied once everything has been written rather than as each sheet opens.
        foreach ($sheets as $entry) {
            $columns = count($entry['columns']);

            if ($columns === 0) {
                continue;
            }

            $entry['sheet']->setColumnWidthForRange(22, 1, $columns);

            if ($entry['rows'] > 1) {
                $entry['sheet']->setAutoFilter(new AutoFilter(0, 1, $columns - 1, $entry['rows']));
            }
        }

        $writer->close();

        return $path;
    }

    /**
     * Which sheets the workbook holds, and the columns of each. A section that
     * was unticked contributes nothing and its sheet never appears.
     *
     * @return array<string, list<array{key: string, label: string}>>
     */
    private function plan(ExportPayload $payload): array
    {
        $selection = $payload->selection();
        $plan = [];

        if ($selection->hasSection(ExportSection::Cover) || $selection->hasSection(ExportSection::Summary)) {
            $plan[__('Summary')] = [
                ['key' => 'label', 'label' => __('Measure')],
                ['key' => 'value', 'label' => __('Value')],
            ];
        }

        $inventory = $this->blueprint->columns($selection, ExportSection::Inventory, 'inventory');

        if ($inventory !== []) {
            $plan[__('Inventory')] = $inventory;
        }

        foreach ($this->sheetBlocks($selection) as $name => $columns) {
            $plan[$name] = $columns;
        }

        $documents = $this->blueprint->columns($selection, ExportSection::Documents, 'documents');

        if ($documents !== []) {
            $plan[__('Documents')] = $documents;
        }

        return $plan;
    }

    /**
     * The sheets that come out of the item detail, each prefixed with the item
     * and copy it belongs to so a row can be traced back without the sheet it
     * came from.
     *
     * @return array<string, list<array{key: string, label: string}>>
     */
    private function sheetBlocks(ExportSelection $selection): array
    {
        if (! $selection->hasSection(ExportSection::ItemSheets)) {
            return [];
        }

        $identity = [
            ['key' => '_item', 'label' => __('Item')],
            ['key' => '_copy', 'label' => __('Copy')],
        ];

        $blocks = [
            __('Items') => 'identification',
            __('Copies') => 'copies',
            __('Transactions') => 'transactions',
            __('Valuations') => 'valuations',
            __('Insurance') => 'insurance',
            __('Provenance') => 'provenance',
        ];

        $plan = [];

        foreach ($blocks as $name => $group) {
            $columns = $this->blueprint->columns($selection, ExportSection::ItemSheets, $group);

            if ($columns === []) {
                continue;
            }

            // The items sheet is one row per item, so it names the item alone.
            $plan[$name] = $group === 'identification'
                ? [$identity[0], ...$columns]
                : [...$identity, ...$columns];
        }

        foreach ($this->historyBlocks() as $field => $definition) {
            if (! $selection->has($field)) {
                continue;
            }

            $plan[$definition['name']] = [...$identity, ...$definition['columns']];
        }

        return $plan;
    }

    /**
     * The histories are lists of their own shape rather than rows of blueprint
     * fields, so their columns are named here.
     *
     * @return array<string, array{name: string, key: string, columns: list<array{key: string, label: string}>}>
     */
    private function historyBlocks(): array
    {
        return [
            'sheet.history.locations' => [
                'name' => __('Locations'),
                'key' => 'locations',
                'columns' => [
                    ['key' => 'location', 'label' => __('Location')],
                    ['key' => 'moved_at', 'label' => __('Moved in')],
                    ['key' => 'moved_out_at', 'label' => __('Moved out')],
                    ['key' => 'reason', 'label' => __('Reason')],
                    ['key' => 'note', 'label' => __('Note')],
                ],
            ],
            'sheet.history.loans' => [
                'name' => __('Loans'),
                'key' => 'loans',
                'columns' => [
                    ['key' => 'direction', 'label' => __('Direction')],
                    ['key' => 'status', 'label' => __('Status')],
                    ['key' => 'party', 'label' => __('Party')],
                    ['key' => 'purpose', 'label' => __('Purpose')],
                    ['key' => 'loaned_at', 'label' => __('Loaned on')],
                    ['key' => 'due_at', 'label' => __('Due')],
                    ['key' => 'returned_at', 'label' => __('Returned')],
                ],
            ],
            'sheet.history.loan_conditions' => [
                'name' => __('Loan conditions'),
                'key' => 'loan_conditions',
                'columns' => [
                    ['key' => 'party', 'label' => __('Party')],
                    ['key' => 'before', 'label' => __('Condition out')],
                    ['key' => 'after', 'label' => __('Condition in')],
                ],
            ],
            'sheet.history.loan_deposits' => [
                'name' => __('Loan deposits'),
                'key' => 'loan_deposits',
                'columns' => [
                    ['key' => 'party', 'label' => __('Party')],
                    ['key' => 'amount', 'label' => __('Deposit')],
                    ['key' => 'currency', 'label' => __('Currency')],
                    ['key' => 'returned_at', 'label' => __('Returned')],
                ],
            ],
            'sheet.history.maintenance' => [
                'name' => __('Maintenance'),
                'key' => 'maintenance',
                'columns' => [
                    ['key' => 'type', 'label' => __('Type')],
                    ['key' => 'title', 'label' => __('Title')],
                    ['key' => 'description', 'label' => __('Description')],
                    ['key' => 'performed_by', 'label' => __('Performed by')],
                    ['key' => 'performed_at', 'label' => __('Performed on')],
                    ['key' => 'cost', 'label' => __('Cost')],
                    ['key' => 'currency', 'label' => __('Currency')],
                    ['key' => 'before', 'label' => __('Condition before')],
                    ['key' => 'after', 'label' => __('Condition after')],
                    ['key' => 'next_due_at', 'label' => __('Next due')],
                ],
            ],
            'sheet.history.timeline' => [
                'name' => __('Timeline'),
                'key' => 'timeline',
                'columns' => [
                    ['key' => 'date', 'label' => __('Date')],
                    ['key' => 'source', 'label' => __('Source')],
                    ['key' => 'title', 'label' => __('Entry')],
                    ['key' => 'summary', 'label' => __('Detail')],
                    ['key' => 'amount', 'label' => __('Amount')],
                    ['key' => 'currency', 'label' => __('Currency')],
                ],
            ],
        ];
    }

    /**
     * @param  array<string, array{sheet: Sheet, columns: list<array{key: string, label: string}>, rows: int}>  $sheets
     */
    private function fill(Writer $writer, array &$sheets, ExportPayload $payload): void
    {
        $summary = $this->summaryRows($payload);

        foreach ($summary as $row) {
            $this->append($writer, $sheets, __('Summary'), $row);
        }

        $inventory = $payload->inventory();

        if ($inventory !== null) {
            foreach ($inventory['rows'] as $row) {
                $this->append($writer, $sheets, __('Inventory'), $row);
            }
        }

        $sheetsOfItems = $payload->itemSheets();

        if ($sheetsOfItems === null) {
            $this->appendDocuments($writer, $sheets, $payload);

            return;
        }

        $histories = $this->historyBlocks();

        foreach ($sheetsOfItems as $item) {
            $this->append($writer, $sheets, __('Items'), ['_item' => $item['name'], ...$item['item']]);

            foreach ($item['copies'] as $copy) {
                $identity = ['_item' => $item['name'], '_copy' => $copy['identifier']];

                $this->append($writer, $sheets, __('Copies'), [...$identity, ...$copy['fields']]);

                foreach ([__('Transactions') => 'transactions', __('Valuations') => 'valuations', __('Insurance') => 'insurance', __('Provenance') => 'provenance'] as $name => $block) {
                    foreach ($copy[$block] as $row) {
                        $this->append($writer, $sheets, $name, [...$identity, ...$row]);
                    }
                }

                foreach ($histories as $field => $definition) {
                    foreach ($copy['histories'][$field] ?? [] as $entry) {
                        $this->append($writer, $sheets, $definition['name'], [
                            ...$identity,
                            ...($definition['key'] === 'timeline' ? $this->timelineRow($entry) : $entry),
                        ]);
                    }
                }
            }
        }

        $this->appendDocuments($writer, $sheets, $payload);
    }

    /**
     * @param  array<string, array{sheet: Sheet, columns: list<array{key: string, label: string}>, rows: int}>  $sheets
     */
    private function appendDocuments(Writer $writer, array &$sheets, ExportPayload $payload): void
    {
        $documents = $payload->documents();

        if ($documents === null) {
            return;
        }

        foreach ($documents['rows'] as $row) {
            $this->append($writer, $sheets, __('Documents'), $row);
        }
    }

    /**
     * The cover and the summary both read as measures, so they share one sheet
     * rather than each getting a two-row one of its own.
     *
     * @return list<array<string, mixed>>
     */
    private function summaryRows(ExportPayload $payload): array
    {
        $rows = [];

        foreach ([$payload->cover(), $payload->summary()] as $block) {
            foreach ($block ?? [] as $key => $value) {
                $rows[] = ['label' => $this->blueprint->label($key), 'value' => $this->flatten($key, $value)];
            }
        }

        return $rows;
    }

    /**
     * @param  array<string, array{sheet: Sheet, columns: list<array{key: string, label: string}>, rows: int}>  $sheets
     * @param  array<string, mixed>  $values
     */
    private function append(Writer $writer, array &$sheets, string $name, array $values): void
    {
        if (! isset($sheets[$name])) {
            return;
        }

        $writer->setCurrentSheet($sheets[$name]['sheet']);

        $cells = [];

        foreach ($sheets[$name]['columns'] as $column) {
            $cells[] = $this->flatten($column['key'], $values[$column['key']] ?? null);
        }

        $writer->addRow(Row::fromValues($cells));
        $sheets[$name]['rows']++;
    }

    /**
     * A timeline entry is a value object rather than a row of blueprint fields,
     * so its columns are read off the object here.
     *
     * @return array<string, mixed>
     */
    private function timelineRow(TimelineEntry $entry): array
    {
        return [
            'date' => $entry->date,
            'source' => $entry->source,
            'title' => $entry->title,
            'summary' => $entry->summary,
            'amount' => $entry->amountCents === null ? null : $entry->amountCents / 100,
            'currency' => $entry->currencyCode,
        ];
    }

    /**
     * Reduce a value to something a cell can hold, keeping numbers as numbers so
     * the column stays sortable.
     */
    private function flatten(string $key, mixed $value): float|int|string|null
    {
        if ($value === null || $value === []) {
            return null;
        }

        if ($this->blueprint->isMoney($key) && is_int($value)) {
            return $value / 100;
        }

        if ($value instanceof Carbon) {
            return $value->format('Y-m-d');
        }

        if ($value instanceof ItemPhoto) {
            return $value->filename;
        }

        if ($value instanceof BackedEnum) {
            return method_exists($value, 'label') ? $value->label() : (string) $value->value;
        }

        if (is_bool($value)) {
            return $value ? __('Yes') : __('No');
        }

        if (is_array($value)) {
            return $this->flattenList($value);
        }

        if (is_int($value) || is_float($value)) {
            return $value;
        }

        return (string) $value;
    }

    /**
     * A list becomes a comma separated cell, and a map becomes "name: value"
     * pairs, which is how the custom fields and the tags read.
     *
     * @param  array<array-key, mixed>  $value
     */
    private function flattenList(array $value): string
    {
        $parts = [];

        foreach ($value as $name => $entry) {
            if (is_array($entry)) {
                continue;
            }
            if (is_object($entry)) {
                continue;
            }
            $parts[] = is_string($name) ? $name.': '.$entry : (string) $entry;
        }

        return implode(', ', $parts);
    }

    /**
     * A worksheet name is capped at 31 characters and has to be unique, and the
     * names here are translated, so neither can be taken for granted.
     *
     * @param  list<string>  $taken
     */
    private function sheetName(string $name, array $taken): string
    {
        $clean = trim((string) preg_replace('/[\\\\\\/\\?\\*\\[\\]:]/', ' ', $name));
        $clean = mb_substr($clean === '' ? 'Sheet' : $clean, 0, 31);
        $candidate = $clean;
        $suffix = 2;

        while (in_array($candidate, $taken, true)) {
            $candidate = mb_substr($clean, 0, 28).'-'.$suffix;
            $suffix++;
        }

        return $candidate;
    }
}
