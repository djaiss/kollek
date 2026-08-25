<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ExportSection;
use App\ValueObjects\ExportSelection;

/**
 * The catalogue of what an export may contain: every section, and inside each
 * one every field the user may tick or untick.
 *
 * This is the one place the list lives. The export screen draws its checkboxes
 * from it, the controller validates the posted keys against it, and the writers
 * ask it nothing but read the same keys back out of the selection. Keeping it
 * here is what stops the screen and the writers from drifting apart, which is
 * the failure mode of a list this long.
 *
 * A field key is prefixed by where it belongs rather than by its section, so the
 * keys of the item sheets read as `sheet.copy.status` and stay legible in a form
 * payload and in a test.
 *
 * Only whole collections are described for now. A single item drops the cover,
 * the summary and the inventory and keeps the rest, which is why the shape is
 * built behind a named constructor rather than hard coded into the class.
 */
class ExportBlueprint
{
    /** @var list<string> */
    private const array MONEY_FIELDS = [
        'cover.value',
        'summary.value',
        'summary.average',
        'summary.value_added_this_month',
        'inventory.price_paid',
        'inventory.estimated_value',
        'inventory.insured_value',
        'sheet.transaction.amount',
        'sheet.transaction.tax',
        'sheet.transaction.fees',
        'sheet.transaction.shipping',
        'sheet.transaction.total',
        'sheet.valuation.amount',
        'sheet.insurance.insured_value',
        'sheet.insurance.deductible',
    ];

    /**
     * @param  list<array{section: ExportSection, groups: list<array{key: string, label: ?string, fields: list<array{key: string, label: string}>}>}>  $sections
     */
    private function __construct(
        private readonly array $sections,
    ) {}

    public static function forCollection(): self
    {
        return new self([
            [
                'section' => ExportSection::Cover,
                'groups' => [
                    ['key' => 'cover', 'label' => null, 'fields' => self::coverFields()],
                ],
            ],
            [
                'section' => ExportSection::Summary,
                'groups' => [
                    ['key' => 'summary', 'label' => null, 'fields' => self::summaryFields()],
                ],
            ],
            [
                'section' => ExportSection::Inventory,
                'groups' => [
                    ['key' => 'inventory', 'label' => null, 'fields' => self::inventoryFields()],
                ],
            ],
            [
                'section' => ExportSection::ItemSheets,
                'groups' => self::sheetGroups(),
            ],
            [
                'section' => ExportSection::Documents,
                'groups' => [
                    ['key' => 'documents', 'label' => null, 'fields' => self::documentFields()],
                ],
            ],
            [
                'section' => ExportSection::Appendices,
                'groups' => [
                    ['key' => 'appendices', 'label' => null, 'fields' => self::appendixFields()],
                ],
            ],
        ]);
    }

    /**
     * @return list<array{section: ExportSection, groups: list<array{key: string, label: ?string, fields: list<array{key: string, label: string}>}>}>
     */
    public function sections(): array
    {
        return $this->sections;
    }

    /**
     * @return list<ExportSection>
     */
    public function sectionCases(): array
    {
        return array_map(fn (array $section): ExportSection => $section['section'], $this->sections);
    }

    /**
     * @return list<string>
     */
    public function sectionKeys(): array
    {
        return array_map(fn (ExportSection $section): string => $section->value, $this->sectionCases());
    }

    /**
     * Every field key in the blueprint, which is what the posted selection is
     * validated against.
     *
     * @return list<string>
     */
    public function fieldKeys(): array
    {
        $keys = [];

        foreach ($this->sections as $section) {
            foreach ($section['groups'] as $group) {
                foreach ($group['fields'] as $field) {
                    $keys[] = $field['key'];
                }
            }
        }

        return $keys;
    }

    /**
     * @return list<string>
     */
    public function fieldKeysFor(ExportSection $section): array
    {
        foreach ($this->sections as $candidate) {
            if ($candidate['section'] !== $section) {
                continue;
            }

            $keys = [];

            foreach ($candidate['groups'] as $group) {
                foreach ($group['fields'] as $field) {
                    $keys[] = $field['key'];
                }
            }

            return $keys;
        }

        return [];
    }

    /**
     * Whether a field holds an amount in cents.
     *
     * Both writers need this and neither should ask the other: the workbook
     * divides these into the major unit so the column stays sortable, and the
     * document renders them through the money helper. Kept as a list rather than
     * guessed from the name, so a new field is on it deliberately or not at all.
     */
    /**
     * The label a field is drawn under. Falls back to the key, so a field that
     * has been removed from the catalogue still reads as something rather than
     * as an empty cell.
     */
    public function label(string $key): string
    {
        foreach ($this->sections as $section) {
            foreach ($section['groups'] as $group) {
                foreach ($group['fields'] as $field) {
                    if ($field['key'] === $key) {
                        return $field['label'];
                    }
                }
            }
        }

        return $key;
    }

    public function isMoney(string $key): bool
    {
        return in_array($key, self::MONEY_FIELDS, true);
    }

    public function countFields(): int
    {
        return count($this->fieldKeys());
    }

    /**
     * The fields of one group, in blueprint order, narrowed to those the
     * selection kept. This is how a writer works out the columns of a block
     * without knowing which fields exist, and why the order of a table is the
     * order of the catalogue rather than the order the user happened to tick.
     *
     * @return list<array{key: string, label: string}>
     */
    public function columns(ExportSelection $selection, ExportSection $section, string $group): array
    {
        if (! $selection->hasSection($section)) {
            return [];
        }

        $columns = [];

        foreach ($this->group($section, $group) as $field) {
            if ($selection->has($field['key'])) {
                $columns[] = $field;
            }
        }

        return $columns;
    }

    /**
     * @return list<array{key: string, label: string}>
     */
    public function group(ExportSection $section, string $group): array
    {
        foreach ($this->sections as $candidate) {
            if ($candidate['section'] !== $section) {
                continue;
            }

            foreach ($candidate['groups'] as $definition) {
                if ($definition['key'] === $group) {
                    return $definition['fields'];
                }
            }
        }

        return [];
    }

    /**
     * @return list<array{key: string, label: string}>
     */
    private static function coverFields(): array
    {
        return [
            ['key' => 'cover.name', 'label' => __('Collection name')],
            ['key' => 'cover.generated_at', 'label' => __('Generation date')],
            ['key' => 'cover.currency', 'label' => __('Collection currency')],
            ['key' => 'cover.items', 'label' => __('Number of items')],
            ['key' => 'cover.copies', 'label' => __('Total number of copies')],
            ['key' => 'cover.value', 'label' => __('Current total estimated value')],
        ];
    }

    /**
     * @return list<array{key: string, label: string}>
     */
    private static function summaryFields(): array
    {
        return [
            ['key' => 'summary.items', 'label' => __('number of items')],
            ['key' => 'summary.copies', 'label' => __('number of copies')],
            ['key' => 'summary.value', 'label' => __('total estimated value')],
            ['key' => 'summary.average', 'label' => __('average value per item')],
            ['key' => 'summary.value_added_this_month', 'label' => __('value added during the month')],
            ['key' => 'summary.items_added_this_month', 'label' => __('items added during the month')],
            ['key' => 'summary.value_over_time', 'label' => __('value over the last twelve months')],
            ['key' => 'summary.acquisitions_per_month', 'label' => __('acquisitions per month')],
            ['key' => 'summary.by_category', 'label' => __('items by category')],
            ['key' => 'summary.by_condition', 'label' => __('copies by condition')],
            ['key' => 'summary.value_by_location', 'label' => __('value by location')],
            ['key' => 'summary.top_items', 'label' => __('most valuable items')],
            ['key' => 'summary.sets_completion', 'label' => __('progress of sets with a target count')],
        ];
    }

    /**
     * @return list<array{key: string, label: string}>
     */
    private static function inventoryFields(): array
    {
        return [
            ['key' => 'inventory.photo', 'label' => __('Main photo')],
            ['key' => 'inventory.item_name', 'label' => __('Item name')],
            ['key' => 'inventory.description', 'label' => __('Description')],
            ['key' => 'inventory.collection', 'label' => __('Collection')],
            ['key' => 'inventory.type', 'label' => __('Type')],
            ['key' => 'inventory.category', 'label' => __('Category')],
            ['key' => 'inventory.set', 'label' => __('Set')],
            ['key' => 'inventory.series', 'label' => __('Series')],
            ['key' => 'inventory.copy_identifier', 'label' => __('Copy identifier')],
            ['key' => 'inventory.condition', 'label' => __('Condition')],
            ['key' => 'inventory.status', 'label' => __('Status')],
            ['key' => 'inventory.quantity', 'label' => __('Quantity')],
            ['key' => 'inventory.location', 'label' => __('Current location')],
            ['key' => 'inventory.acquired_at', 'label' => __('Acquisition date')],
            ['key' => 'inventory.price_paid', 'label' => __('Total price paid')],
            ['key' => 'inventory.estimated_value', 'label' => __('Current estimated value')],
            ['key' => 'inventory.valued_at', 'label' => __('Date of the last valuation')],
            ['key' => 'inventory.insured_value', 'label' => __('Active insured value')],
            ['key' => 'inventory.insurance_status', 'label' => __('Insurance status')],
            ['key' => 'inventory.disposed_at', 'label' => __('Disposal date')],
            ['key' => 'inventory.note', 'label' => __('Notes')],
            ['key' => 'inventory.custom_fields', 'label' => __('Custom fields of the item')],
            ['key' => 'inventory.tags', 'label' => __('Tags')],
        ];
    }

    /**
     * @return list<array{key: string, label: ?string, fields: list<array{key: string, label: string}>}>
     */
    private static function sheetGroups(): array
    {
        return [
            [
                'key' => 'identification',
                'label' => __('Identification'),
                'fields' => [
                    ['key' => 'sheet.item.name', 'label' => __('Name')],
                    ['key' => 'sheet.item.description', 'label' => __('Description')],
                    ['key' => 'sheet.item.main_photo', 'label' => __('Main photo')],
                    ['key' => 'sheet.item.other_photos', 'label' => __('Other photos')],
                    ['key' => 'sheet.item.type', 'label' => __('Type')],
                    ['key' => 'sheet.item.category', 'label' => __('Category')],
                    ['key' => 'sheet.item.set', 'label' => __('Set')],
                    ['key' => 'sheet.item.series', 'label' => __('Series')],
                    ['key' => 'sheet.item.custom_fields', 'label' => __('Custom fields')],
                    ['key' => 'sheet.item.tags', 'label' => __('Tags')],
                ],
            ],
            [
                'key' => 'copies',
                'label' => __('Copies'),
                'fields' => [
                    ['key' => 'sheet.copy.identifier', 'label' => __('Identifier')],
                    ['key' => 'sheet.copy.quantity', 'label' => __('Quantity')],
                    ['key' => 'sheet.copy.condition', 'label' => __('Condition')],
                    ['key' => 'sheet.copy.status', 'label' => __('Status')],
                    ['key' => 'sheet.copy.location', 'label' => __('Location')],
                    ['key' => 'sheet.copy.disposed_at', 'label' => __('Disposal date')],
                    ['key' => 'sheet.copy.note', 'label' => __('Note')],
                ],
            ],
            [
                'key' => 'transactions',
                'label' => __('Acquisition and transactions'),
                'fields' => [
                    ['key' => 'sheet.transaction.type', 'label' => __('Transaction type')],
                    ['key' => 'sheet.transaction.occurred_at', 'label' => __('Date')],
                    ['key' => 'sheet.transaction.counterparty', 'label' => __('Counterparty')],
                    ['key' => 'sheet.transaction.amount', 'label' => __('Amount')],
                    ['key' => 'sheet.transaction.tax', 'label' => __('Taxes')],
                    ['key' => 'sheet.transaction.fees', 'label' => __('Fees')],
                    ['key' => 'sheet.transaction.shipping', 'label' => __('Shipping')],
                    ['key' => 'sheet.transaction.total', 'label' => __('Total')],
                    ['key' => 'sheet.transaction.currency', 'label' => __('Currency')],
                    ['key' => 'sheet.transaction.reference_number', 'label' => __('Reference number')],
                    ['key' => 'sheet.transaction.source_url', 'label' => __('Source URL')],
                    ['key' => 'sheet.transaction.note', 'label' => __('Note')],
                ],
            ],
            [
                'key' => 'valuations',
                'label' => __('Valuation history'),
                'fields' => [
                    ['key' => 'sheet.valuation.amount', 'label' => __('Amount')],
                    ['key' => 'sheet.valuation.currency', 'label' => __('Currency')],
                    ['key' => 'sheet.valuation.valued_at', 'label' => __('Date')],
                    ['key' => 'sheet.valuation.type', 'label' => __('Valuation type')],
                    ['key' => 'sheet.valuation.valuer', 'label' => __('Valuer')],
                    ['key' => 'sheet.valuation.method', 'label' => __('Method')],
                    ['key' => 'sheet.valuation.confidence', 'label' => __('Confidence level')],
                    ['key' => 'sheet.valuation.source_url', 'label' => __('Source URL')],
                    ['key' => 'sheet.valuation.reference_number', 'label' => __('Reference number')],
                    ['key' => 'sheet.valuation.note', 'label' => __('Note')],
                ],
            ],
            [
                'key' => 'insurance',
                'label' => __('Insurance'),
                'fields' => [
                    ['key' => 'sheet.insurance.provider', 'label' => __('Insurer')],
                    ['key' => 'sheet.insurance.policy_number', 'label' => __('Policy number')],
                    ['key' => 'sheet.insurance.coverage_type', 'label' => __('Coverage type')],
                    ['key' => 'sheet.insurance.insured_value', 'label' => __('Insured value')],
                    ['key' => 'sheet.insurance.currency', 'label' => __('Currency')],
                    ['key' => 'sheet.insurance.deductible', 'label' => __('Deductible')],
                    ['key' => 'sheet.insurance.starts_at', 'label' => __('Start date')],
                    ['key' => 'sheet.insurance.ends_at', 'label' => __('End date')],
                    ['key' => 'sheet.insurance.status', 'label' => __('Status')],
                    ['key' => 'sheet.insurance.is_scheduled_item', 'label' => __('Specifically scheduled item')],
                    ['key' => 'sheet.insurance.contact_name', 'label' => __('Contact name')],
                    ['key' => 'sheet.insurance.contact_email', 'label' => __('Contact email')],
                    ['key' => 'sheet.insurance.contact_phone', 'label' => __('Contact phone')],
                    ['key' => 'sheet.insurance.note', 'label' => __('Note')],
                ],
            ],
            [
                'key' => 'provenance',
                'label' => __('Provenance'),
                'fields' => [
                    ['key' => 'sheet.provenance.type', 'label' => __('Event type')],
                    ['key' => 'sheet.provenance.title', 'label' => __('Title')],
                    ['key' => 'sheet.provenance.description', 'label' => __('Description')],
                    ['key' => 'sheet.provenance.occurred_at', 'label' => __('Date')],
                    ['key' => 'sheet.provenance.precision', 'label' => __('Date precision')],
                    ['key' => 'sheet.provenance.location', 'label' => __('Location')],
                    ['key' => 'sheet.provenance.from_party', 'label' => __('From')],
                    ['key' => 'sheet.provenance.to_party', 'label' => __('To')],
                    ['key' => 'sheet.provenance.reference_number', 'label' => __('Reference number')],
                    ['key' => 'sheet.provenance.source_url', 'label' => __('Source URL')],
                    ['key' => 'sheet.provenance.is_verified', 'label' => __('Verified or not')],
                    ['key' => 'sheet.provenance.verification_note', 'label' => __('Verification note')],
                ],
            ],
            [
                'key' => 'histories',
                'label' => __('Other histories'),
                'fields' => [
                    ['key' => 'sheet.history.locations', 'label' => __('Location history')],
                    ['key' => 'sheet.history.loans', 'label' => __('Loans lent out and borrowed in')],
                    ['key' => 'sheet.history.loan_conditions', 'label' => __('Condition before and after a loan')],
                    ['key' => 'sheet.history.loan_deposits', 'label' => __('Deposits held against a loan')],
                    ['key' => 'sheet.history.maintenance', 'label' => __('Maintenance and restoration')],
                    ['key' => 'sheet.history.timeline', 'label' => __('Unified timeline of the copy')],
                ],
            ],
        ];
    }

    /**
     * @return list<array{key: string, label: string}>
     */
    private static function documentFields(): array
    {
        return [
            ['key' => 'documents.name', 'label' => __('Document name')],
            ['key' => 'documents.type', 'label' => __('Type')],
            ['key' => 'documents.issued_at', 'label' => __('Issue date')],
            ['key' => 'documents.reference_number', 'label' => __('Reference number')],
            ['key' => 'documents.description', 'label' => __('Description')],
            ['key' => 'documents.format', 'label' => __('Format')],
            ['key' => 'documents.size', 'label' => __('Size')],
            ['key' => 'documents.record', 'label' => __('Record it is attached to')],
        ];
    }

    /**
     * @return list<array{key: string, label: string}>
     */
    private static function appendixFields(): array
    {
        return [
            ['key' => 'appendices.photos', 'label' => __('Every selected photo')],
            ['key' => 'appendices.timeline', 'label' => __('Full timeline')],
            ['key' => 'appendices.valuations', 'label' => __('Full valuation history')],
            ['key' => 'appendices.transactions', 'label' => __('Full transaction history')],
            ['key' => 'appendices.insurance', 'label' => __('Full insurance history')],
            ['key' => 'appendices.documents', 'label' => __('List of supporting documents')],
        ];
    }
}
