<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\DatePrecision;
use App\Enums\ItemActionEnum;
use App\Enums\MaintenanceType;
use App\Enums\ProvenanceEventType;
use App\Enums\UserActionEnum;
use App\Jobs\LogItemAction;
use App\Jobs\LogUserAction;
use App\Models\Copy;
use App\Models\MaintenanceRecord;
use App\Models\User;
use App\Traits\GuardsMaintenanceConditions;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Log a piece of work performed on a copy. Only owners and editors of the copy's
 * account may do so.
 */
class CreateMaintenanceRecord
{
    use GuardsMaintenanceConditions;

    private MaintenanceRecord $record;

    public function __construct(
        private readonly User $user,
        private readonly Copy $copy,
        private readonly MaintenanceType $type,
        private readonly string $title,
        private readonly ?string $description = null,
        private readonly ?string $performedBy = null,
        private readonly ?string $performedAt = null,
        private readonly ?int $costAmount = null,
        private readonly ?string $costCurrencyCode = null,
        private readonly ?int $itemConditionBeforeId = null,
        private readonly ?int $itemConditionAfterId = null,
        private readonly ?string $nextDueAt = null,
        private readonly bool $includeInProvenance = false,
    ) {}

    public function execute(): MaintenanceRecord
    {
        $this->validate();
        $this->create();
        $this->stampAuthor();
        $this->syncCopyCondition();
        $this->handleProvenance();
        $this->log();

        return $this->record;
    }

    private function validate(): void
    {
        $account = $this->copy->item->catalog->account;

        if (! $account->allowsManagementBy($this->user)) {
            throw new ModelNotFoundException('Account not found');
        }

        $this->guardConditionsBelongToAccount($account, $this->itemConditionBeforeId, $this->itemConditionAfterId);
    }

    private function create(): void
    {
        $this->record = MaintenanceRecord::query()->create([
            'copy_id' => $this->copy->id,
            'type' => $this->type,
            'title' => $this->title,
            'description' => $this->description,
            'performed_by' => $this->performedBy,
            'performed_at' => $this->performedAt,
            'cost_amount' => $this->costAmount,
            // The collection's currency is the sensible default when the caller
            // does not say what the cost is in.
            'cost_currency_code' => $this->costAmount === null
                ? null
                : ($this->costCurrencyCode ?? $this->copy->item->catalog->currency),
            'item_condition_before_id' => $this->itemConditionBeforeId,
            'item_condition_after_id' => $this->itemConditionAfterId,
            'next_due_at' => $this->nextDueAt,
            'include_in_provenance' => $this->includeInProvenance,
        ]);
    }

    private function stampAuthor(): void
    {
        $this->record->created_by_id = $this->user->id;
        $this->record->created_by_name = $this->user->getFullName();
        $this->record->updated_by_id = $this->user->id;
        $this->record->updated_by_name = $this->user->getFullName();
        $this->record->save();
    }

    private function syncCopyCondition(): void
    {
        if ($this->itemConditionAfterId === null) {
            return;
        }

        $this->copy->item_condition_id = $this->itemConditionAfterId;
        $this->copy->save();
    }

    private function handleProvenance(): void
    {
        if (! $this->includeInProvenance) {
            return;
        }

        $event = new CreateProvenanceEvent(
            user: $this->user,
            copy: $this->copy,
            type: ProvenanceEventType::SignificantRestoration,
            title: $this->title,
            occurredAtPrecision: $this->performedAt === null ? DatePrecision::Unknown : DatePrecision::Exact,
            occurredAt: $this->performedAt,
            description: $this->description,
        )->execute();

        $this->record->provenance_event_id = $event->id;
        $this->record->save();
    }

    private function log(): void
    {
        $item = $this->copy->item;

        LogUserAction::dispatch(
            user: $this->user,
            action: UserActionEnum::MaintenanceRecordCreation,
            parameters: ['name' => $item->name],
        )->onQueue('low');

        LogItemAction::dispatch(
            item: $item,
            user: $this->user,
            action: ItemActionEnum::MaintenanceRecordCreation,
            parameters: ['label' => $this->type->label()],
        )->onQueue('low');
    }
}
