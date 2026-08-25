<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ExportPayload;
use App\Enums\ExportSection;
use App\Models\Catalog;
use App\Models\Copy;
use App\Models\Document;
use App\Models\InsuranceRecord;
use App\Models\Item;
use App\Models\ItemPhoto;
use App\Models\Loan;
use App\Models\LocationHistory;
use App\Models\MaintenanceRecord;
use App\Models\ProvenanceEvent;
use App\Models\Transaction;
use App\Models\Valuation;
use App\ValueObjects\ExportSelection;
use Carbon\Carbon;
use Closure;
use Generator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;

/**
 * Everything a writer needs to export one whole collection.
 *
 * Nothing here formats anything. Amounts stay in cents, dates stay as Carbon and
 * enums stay as enums, because the PDF wants "$1,200" while the workbook wants a
 * number a spreadsheet can sort. Turning a value into something readable is the
 * writer's job, and doing it here would force one of the two formats to undo it.
 *
 * Rows are keyed by the blueprint's field keys, so a writer pairs them with the
 * columns the blueprint hands it and never needs to know which fields exist.
 *
 * The item sheets are a generator. A collection of a few hundred items with their
 * photos, transactions and histories does not belong in memory all at once, so
 * items are walked in chunks and each sheet is handed over and forgotten. That is
 * also why the sheets can only be read once.
 */
class CollectionExportPayload implements ExportPayload
{
    private ?CatalogStatistics $statistics = null;

    public function __construct(
        private readonly Catalog $catalog,
        private readonly ExportSelection $selection,
        private readonly ExportBlueprint $blueprint,
    ) {}

    /**
     * @return array{title: string, subtitle: ?string, currency: ?string, generatedAt: Carbon, fileSlug: string}
     */
    public function meta(): array
    {
        // The name is free text, so it can slug down to nothing: a collection
        // named with an emoji alone still has to produce a usable file name.
        $slug = Str::slug($this->catalog->name);

        return [
            'title' => $this->catalog->name,
            'subtitle' => $this->catalog->description,
            'currency' => $this->catalog->currency,
            'generatedAt' => Date::now(),
            'fileSlug' => $slug !== '' ? $slug : 'collection',
        ];
    }

    public function selection(): ExportSelection
    {
        return $this->selection;
    }

    /**
     * @return ?array<string, mixed>
     */
    public function cover(): ?array
    {
        if (! $this->selection->hasSection(ExportSection::Cover)) {
            return null;
        }

        $totals = $this->statistics()->totals();

        return $this->keep(ExportSection::Cover, [
            'cover.name' => $this->catalog->name,
            'cover.generated_at' => Date::now(),
            'cover.currency' => $this->catalog->currency,
            'cover.items' => $totals['items'],
            'cover.copies' => $totals['copies'],
            'cover.value' => $totals['value'],
        ]);
    }

    /**
     * The statistics the summary draws on, each read only if it was asked for so
     * an export of the cover alone runs none of these queries.
     *
     * @return ?array<string, mixed>
     */
    public function summary(): ?array
    {
        if (! $this->selection->hasSection(ExportSection::Summary)) {
            return null;
        }

        $statistics = $this->statistics();
        $needsTotals = $this->selection->wantsAny(
            ExportSection::Summary,
            'summary.items',
            'summary.copies',
            'summary.value',
            'summary.average',
            'summary.value_added_this_month',
            'summary.items_added_this_month',
        );

        $totals = $needsTotals ? $statistics->totals() : [];

        return $this->keep(ExportSection::Summary, [
            'summary.items' => $totals['items'] ?? null,
            'summary.copies' => $totals['copies'] ?? null,
            'summary.value' => $totals['value'] ?? null,
            'summary.average' => $totals['average'] ?? null,
            'summary.value_added_this_month' => $totals['valueAddedThisMonth'] ?? null,
            'summary.items_added_this_month' => $totals['itemsAddedThisMonth'] ?? null,
            'summary.value_over_time' => $statistics->valueOverTime(...),
            'summary.acquisitions_per_month' => $statistics->acquisitionsPerMonth(...),
            'summary.by_category' => $statistics->byCategory(...),
            'summary.by_condition' => $statistics->byCondition(...),
            'summary.value_by_location' => $statistics->valueByLocation(...),
            'summary.top_items' => $statistics->topItems(...),
            'summary.sets_completion' => $statistics->setsCompletion(...),
        ]);
    }

    /**
     * @return ?array{columns: list<array{key: string, label: string}>, rows: list<array<string, mixed>>}
     */
    public function inventory(): ?array
    {
        $columns = $this->blueprint->columns($this->selection, ExportSection::Inventory, 'inventory');

        if ($columns === []) {
            return null;
        }

        $rows = [];

        foreach ($this->items(['copies.itemCondition', 'copies.currentLocation', 'copies.latestValuation', 'copies.acquiringTransaction', 'copies.activeInsuranceRecord']) as $item) {
            foreach ($item->copies as $copy) {
                $rows[] = $this->inventoryRow($item, $copy);
            }
        }

        return ['columns' => $columns, 'rows' => $rows];
    }

    /**
     * @return ?Generator<int, array<string, mixed>>
     */
    public function itemSheets(): ?iterable
    {
        if (! $this->selection->hasSection(ExportSection::ItemSheets)) {
            return null;
        }

        return $this->sheets();
    }

    /**
     * @return ?array{columns: list<array{key: string, label: string}>, rows: list<array<string, mixed>>}
     */
    public function documents(): ?array
    {
        $columns = $this->blueprint->columns($this->selection, ExportSection::Documents, 'documents');

        if ($columns === []) {
            return null;
        }

        $rows = [];

        // The model is carried alongside its row only when the export is going
        // to embed the file, because that is the one case a writer needs more
        // than the metadata. A writer reads its rows by column, so the extra key
        // is invisible to the ones that do not want it.
        $embedding = $this->selection->option('embed_documents');

        foreach ($this->documentQuery()->cursor() as $document) {
            $row = $this->keep(ExportSection::Documents, [
                'documents.name' => $document->name,
                'documents.type' => $document->type,
                'documents.issued_at' => $document->issued_at,
                'documents.reference_number' => $document->reference_number,
                'documents.description' => $document->description,
                'documents.format' => $document->mime_type,
                'documents.size' => $document->size,
                'documents.record' => $this->recordLabel($document->documentable_type),
            ]);

            if ($embedding) {
                $row['_document'] = $document;
            }

            $rows[] = $row;
        }

        return ['columns' => $columns, 'rows' => $rows];
    }

    /**
     * The appendices are the same histories the item sheets carry, gathered
     * collection wide instead of item by item. Which of them are drawn is the
     * only thing decided here; the rows themselves are read by the writer off
     * the sheets it has already walked, so nothing is queried twice.
     *
     * @return ?array<string, mixed>
     */
    public function appendices(): ?array
    {
        if (! $this->selection->hasSection(ExportSection::Appendices)) {
            return null;
        }

        return $this->keep(ExportSection::Appendices, [
            'appendices.photos' => true,
            'appendices.timeline' => true,
            'appendices.valuations' => true,
            'appendices.transactions' => true,
            'appendices.insurance' => true,
            'appendices.documents' => true,
        ]);
    }

    /**
     * How many documents the collection has on file, which the export screen
     * reports before anything is generated.
     */
    public function documentCount(): int
    {
        return $this->documentQuery()->count();
    }

    /**
     * @return Generator<int, array<string, mixed>>
     */
    private function sheets(): Generator
    {
        $relations = [
            'photos',
            'copies.itemCondition',
            'copies.currentLocation',
            'copies.transactions',
            'copies.valuations',
            'copies.insuranceRecords',
            'copies.provenanceEvents',
            'copies.locationHistory.location',
            'copies.loans.itemConditionOut',
            'copies.loans.itemConditionIn',
            'copies.maintenanceRecords.itemConditionBefore',
            'copies.maintenanceRecords.itemConditionAfter',
        ];

        foreach ($this->items($relations) as $item) {
            yield [
                'id' => $item->id,
                'name' => $item->name,
                'item' => $this->identification($item),
                'copies' => $item->copies->map(fn (Copy $copy): array => $this->copySheet($copy))->values()->all(),
            ];
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function identification(Item $item): array
    {
        return $this->keep(ExportSection::ItemSheets, [
            'sheet.item.name' => $item->name,
            'sheet.item.description' => $item->description,
            'sheet.item.main_photo' => fn (): ?ItemPhoto => $item->photos->firstWhere('is_main', true) ?? $item->photos->first(),
            'sheet.item.other_photos' => fn (): array => $item->photos->where('is_main', false)->values()->all(),
            'sheet.item.type' => $item->catalogType?->name,
            'sheet.item.category' => $item->category?->name,
            'sheet.item.set' => $item->set?->name,
            'sheet.item.series' => $item->series?->name,
            'sheet.item.custom_fields' => fn (): array => $this->customFields($item),
            'sheet.item.tags' => fn (): array => $item->tags->pluck('name')->all(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function copySheet(Copy $copy): array
    {
        return [
            'id' => $copy->id,
            'identifier' => $copy->identifier,
            'fields' => $this->keep(ExportSection::ItemSheets, [
                'sheet.copy.identifier' => $copy->identifier,
                'sheet.copy.quantity' => $copy->quantity,
                'sheet.copy.condition' => $copy->itemCondition?->name,
                'sheet.copy.status' => $copy->status,
                'sheet.copy.location' => $copy->currentLocation?->name,
                'sheet.copy.disposed_at' => $copy->disposed_at,
                'sheet.copy.note' => $copy->note,
            ]),
            'transactions' => $copy->transactions->map(fn (Transaction $transaction): array => $this->keep(ExportSection::ItemSheets, [
                'sheet.transaction.type' => $transaction->type,
                'sheet.transaction.occurred_at' => $transaction->occurred_at,
                'sheet.transaction.counterparty' => $transaction->counterparty,
                'sheet.transaction.amount' => $transaction->amount,
                'sheet.transaction.tax' => $transaction->tax_amount,
                'sheet.transaction.fees' => $transaction->fee_amount,
                'sheet.transaction.shipping' => $transaction->shipping_amount,
                'sheet.transaction.total' => $transaction->total(),
                'sheet.transaction.currency' => $transaction->currency_code,
                'sheet.transaction.reference_number' => $transaction->reference_number,
                'sheet.transaction.source_url' => $transaction->source_url,
                'sheet.transaction.note' => $transaction->note,
            ]))->values()->all(),
            'valuations' => $copy->valuations->map(fn (Valuation $valuation): array => $this->keep(ExportSection::ItemSheets, [
                'sheet.valuation.amount' => $valuation->amount,
                'sheet.valuation.currency' => $valuation->currency_code,
                'sheet.valuation.valued_at' => $valuation->valued_at,
                'sheet.valuation.type' => $valuation->type,
                'sheet.valuation.valuer' => $valuation->valuer,
                'sheet.valuation.method' => $valuation->method,
                'sheet.valuation.confidence' => $valuation->confidence,
                'sheet.valuation.source_url' => $valuation->source_url,
                'sheet.valuation.reference_number' => $valuation->reference_number,
                'sheet.valuation.note' => $valuation->note,
            ]))->values()->all(),
            'insurance' => $copy->insuranceRecords->map(fn (InsuranceRecord $record): array => $this->keep(ExportSection::ItemSheets, [
                'sheet.insurance.provider' => $record->provider,
                'sheet.insurance.policy_number' => $record->policy_number,
                'sheet.insurance.coverage_type' => $record->coverage_type,
                'sheet.insurance.insured_value' => $record->insured_value,
                'sheet.insurance.currency' => $record->currency_code,
                'sheet.insurance.deductible' => $record->deductible_amount,
                'sheet.insurance.starts_at' => $record->starts_at,
                'sheet.insurance.ends_at' => $record->ends_at,
                'sheet.insurance.status' => $record->status,
                'sheet.insurance.is_scheduled_item' => $record->is_scheduled_item,
                'sheet.insurance.contact_name' => $record->contact_name,
                'sheet.insurance.contact_email' => $record->contact_email,
                'sheet.insurance.contact_phone' => $record->contact_phone,
                'sheet.insurance.note' => $record->note,
            ]))->values()->all(),
            'provenance' => $copy->provenanceEvents->map(fn (ProvenanceEvent $event): array => $this->keep(ExportSection::ItemSheets, [
                'sheet.provenance.type' => $event->type,
                'sheet.provenance.title' => $event->title,
                'sheet.provenance.description' => $event->description,
                'sheet.provenance.occurred_at' => $event->occurred_at,
                'sheet.provenance.precision' => $event->occurred_at_precision,
                'sheet.provenance.location' => $event->location,
                'sheet.provenance.from_party' => $event->from_party,
                'sheet.provenance.to_party' => $event->to_party,
                'sheet.provenance.reference_number' => $event->reference_number,
                'sheet.provenance.source_url' => $event->source_url,
                'sheet.provenance.is_verified' => $event->is_verified,
                'sheet.provenance.verification_note' => $event->verification_note,
            ]))->values()->all(),
            'histories' => $this->histories($copy),
        ];
    }

    /**
     * The remaining histories, each an independent list rather than a row of
     * fields, because a location move and a loan have nothing in common beyond
     * both being something that happened.
     *
     * @return array<string, mixed>
     */
    private function histories(Copy $copy): array
    {
        return $this->keep(ExportSection::ItemSheets, [
            'sheet.history.locations' => fn (): array => $copy->locationHistory
                ->map(fn (LocationHistory $entry): array => [
                    'location' => $entry->location?->name,
                    'moved_at' => $entry->moved_at,
                    'moved_out_at' => $entry->moved_out_at,
                    'reason' => $entry->reason,
                    'note' => $entry->note,
                ])->values()->all(),
            'sheet.history.loans' => fn (): array => $copy->loans
                ->map(fn (Loan $loan): array => [
                    'direction' => $loan->direction,
                    'status' => $loan->status,
                    'party' => $loan->party,
                    'purpose' => $loan->purpose,
                    'loaned_at' => $loan->loaned_at,
                    'due_at' => $loan->due_at,
                    'returned_at' => $loan->returned_at,
                ])->values()->all(),
            'sheet.history.loan_conditions' => fn (): array => $copy->loans
                ->map(fn (Loan $loan): array => [
                    'party' => $loan->party,
                    'before' => $loan->itemConditionOut?->name,
                    'after' => $loan->itemConditionIn?->name,
                ])->values()->all(),
            'sheet.history.loan_deposits' => fn (): array => $copy->loans
                ->filter(fn (Loan $loan): bool => $loan->deposit_amount !== null)
                ->map(fn (Loan $loan): array => [
                    'party' => $loan->party,
                    'amount' => $loan->deposit_amount,
                    'currency' => $loan->deposit_currency_code,
                    'returned_at' => $loan->returned_at,
                ])->values()->all(),
            'sheet.history.maintenance' => fn (): array => $copy->maintenanceRecords
                ->map(fn (MaintenanceRecord $record): array => [
                    'type' => $record->type,
                    'title' => $record->title,
                    'description' => $record->description,
                    'performed_by' => $record->performed_by,
                    'performed_at' => $record->performed_at,
                    'cost' => $record->cost_amount,
                    'currency' => $record->cost_currency_code,
                    'before' => $record->itemConditionBefore?->name,
                    'after' => $record->itemConditionAfter?->name,
                    'next_due_at' => $record->next_due_at,
                ])->values()->all(),
            // The relations the timeline reads are already loaded, so this
            // assembles from memory rather than going back to the database.
            'sheet.history.timeline' => fn (): array => new BuildCopyHistory(copy: $copy)->entries(meaningfulOnly: false),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function inventoryRow(Item $item, Copy $copy): array
    {
        $insurance = $copy->activeInsuranceRecord;

        return $this->keep(ExportSection::Inventory, [
            'inventory.photo' => fn (): ?ItemPhoto => $item->mainPhoto,
            'inventory.item_name' => $item->name,
            'inventory.description' => $item->description,
            'inventory.collection' => $this->catalog->name,
            'inventory.type' => $item->catalogType?->name,
            'inventory.category' => $item->category?->name,
            'inventory.set' => $item->set?->name,
            'inventory.series' => $item->series?->name,
            'inventory.copy_identifier' => $copy->identifier,
            'inventory.condition' => $copy->itemCondition?->name,
            'inventory.status' => $copy->status,
            'inventory.quantity' => $copy->quantity,
            'inventory.location' => $copy->currentLocation?->name,
            'inventory.acquired_at' => $copy->acquiredAt(),
            'inventory.price_paid' => $copy->pricePaid(),
            'inventory.estimated_value' => $copy->estimatedValue(),
            'inventory.valued_at' => $copy->latestValuation?->valued_at,
            'inventory.insured_value' => $insurance?->insured_value,
            'inventory.insurance_status' => $insurance?->status,
            'inventory.disposed_at' => $copy->disposed_at,
            'inventory.note' => $copy->note,
            'inventory.custom_fields' => fn (): array => $this->customFields($item),
            'inventory.tags' => fn (): array => $item->tags->pluck('name')->all(),
        ]);
    }

    /**
     * What a document is attached to, read as words rather than as the morph
     * alias the column holds. The alias is stable and part of the api, so it is
     * translated here rather than renamed.
     */
    private function recordLabel(string $alias): string
    {
        return match ($alias) {
            'copy' => __('Copy'),
            'transaction' => __('Transaction'),
            'valuation' => __('Valuation'),
            'insurance_record' => __('Insurance record'),
            'maintenance_record' => __('Maintenance record'),
            'provenance_event' => __('Provenance event'),
            'loan' => __('Loan'),
            default => $alias,
        };
    }

    /**
     * @return array<string, string>
     */
    private function customFields(Item $item): array
    {
        $values = [];

        foreach ($item->customFieldValues as $value) {
            if ($value->customField === null) {
                continue;
            }
            if ($value->value === null) {
                continue;
            }
            if ($value->value === '') {
                continue;
            }
            $values[$value->customField->name] = $value->value;
        }

        return $values;
    }

    /**
     * Drop whatever the selection left out, and only then work out what is kept.
     *
     * The values that cost a query or a decryption pass are handed in as
     * closures, so an unticked field is never paid for. Everything cheap is
     * passed by value, because wrapping an integer in a closure to save nothing
     * would only make the callers harder to read.
     *
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    private function keep(ExportSection $section, array $values): array
    {
        $kept = [];

        foreach ($values as $key => $value) {
            if (! $this->selection->wants($section, $key)) {
                continue;
            }

            $kept[$key] = $value instanceof Closure ? $value() : $value;
        }

        return $kept;
    }

    /**
     * The items of the collection, walked in chunks so a large collection never
     * sits in memory whole. Names are encrypted, so the order is the one the
     * database can actually give: by id.
     *
     * @param  list<string>  $relations
     * @return Generator<int, Item>
     */
    private function items(array $relations): Generator
    {
        $base = ['catalogType', 'category', 'set', 'series', 'tags', 'customFieldValues.customField', 'mainPhoto'];

        foreach ($this->catalog->items()->with([...$base, ...$relations])->orderBy('id')->lazyById(50) as $item) {
            yield $item;
        }
    }

    /**
     * Every document filed against this collection.
     *
     * A document hangs off a copy or off one of the records hanging off a copy,
     * polymorphically, so there is no single relation to follow. The ids of each
     * kind are gathered from the collection's copies and the documents are read
     * in one query against them, which is both fewer queries than walking the
     * records and the only way to count them without building every sheet.
     *
     * @return Builder<Document>
     */
    private function documentQuery(): Builder
    {
        $copyIds = Copy::query()
            ->whereIn('item_id', $this->catalog->items()->select('id'))
            ->pluck('id');

        $owners = [
            'copy' => $copyIds,
            'transaction' => Transaction::query()->whereIn('copy_id', $copyIds)->pluck('id'),
            'valuation' => Valuation::query()->whereIn('copy_id', $copyIds)->pluck('id'),
            'insurance_record' => InsuranceRecord::query()->whereIn('copy_id', $copyIds)->pluck('id'),
            'maintenance_record' => MaintenanceRecord::query()->whereIn('copy_id', $copyIds)->pluck('id'),
            'provenance_event' => ProvenanceEvent::query()->whereIn('copy_id', $copyIds)->pluck('id'),
            'loan' => Loan::query()->whereIn('copy_id', $copyIds)->pluck('id'),
        ];

        return Document::query()
            ->where('account_id', $this->catalog->account_id)
            ->where(function ($query) use ($owners): void {
                foreach ($owners as $type => $ids) {
                    $query->orWhere(fn ($owner) => $owner
                        ->where('documentable_type', $type)
                        ->whereIn('documentable_id', $ids));
                }
            })
            ->latest('issued_at')
            ->orderByDesc('id');
    }

    private function statistics(): CatalogStatistics
    {
        return $this->statistics ??= new CatalogStatistics(catalog: $this->catalog);
    }
}
