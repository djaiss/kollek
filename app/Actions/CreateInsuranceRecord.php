<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\InsuranceStatus;
use App\Enums\ItemActionEnum;
use App\Enums\UserActionEnum;
use App\Jobs\LogItemAction;
use App\Jobs\LogUserAction;
use App\Models\Copy;
use App\Models\InsuranceRecord;
use App\Models\User;
use App\Traits\GuardsActiveInsuranceRecord;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Record a piece of insurance coverage against a copy. Only owners and editors
 * of the copy's account may do so.
 */
class CreateInsuranceRecord
{
    use GuardsActiveInsuranceRecord;

    private InsuranceRecord $record;

    public function __construct(
        private readonly User $user,
        private readonly Copy $copy,
        private readonly string $provider,
        private readonly int $insuredValue,
        private readonly InsuranceStatus $status = InsuranceStatus::Active,
        private readonly ?string $currencyCode = null,
        private readonly ?string $policyNumber = null,
        private readonly ?string $coverageType = null,
        private readonly ?int $deductibleAmount = null,
        private readonly ?string $deductibleCurrencyCode = null,
        private readonly ?string $startsAt = null,
        private readonly ?string $endsAt = null,
        private readonly bool $isScheduledItem = false,
        private readonly ?string $contactName = null,
        private readonly ?string $contactEmail = null,
        private readonly ?string $contactPhone = null,
        private readonly ?string $note = null,
    ) {}

    public function execute(): InsuranceRecord
    {
        $this->validate();
        $this->create();
        $this->stampAuthor();
        $this->log();

        return $this->record;
    }

    private function validate(): void
    {
        $account = $this->copy->item->catalog->account;

        if (! $account->allowsManagementBy($this->user)) {
            throw new ModelNotFoundException('Account not found');
        }

        $this->guardAgainstSecondActiveRecord($this->copy, $this->status, $this->policyNumber);
    }

    private function create(): void
    {
        $this->record = InsuranceRecord::query()->create([
            'copy_id' => $this->copy->id,
            'provider' => $this->provider,
            'policy_number' => $this->policyNumber,
            'coverage_type' => $this->coverageType,
            'insured_value' => $this->insuredValue,
            // The collection's currency is the sensible default when the caller
            // does not say what the amounts are in.
            'currency_code' => $this->currencyCode ?? $this->copy->item->catalog->currency,
            'deductible_amount' => $this->deductibleAmount,
            'deductible_currency_code' => $this->deductibleAmount === null
                ? null
                : ($this->deductibleCurrencyCode ?? $this->currencyCode ?? $this->copy->item->catalog->currency),
            'starts_at' => $this->startsAt,
            'ends_at' => $this->endsAt,
            'status' => $this->status,
            'is_scheduled_item' => $this->isScheduledItem,
            'contact_name' => $this->contactName,
            'contact_email' => $this->contactEmail,
            'contact_phone' => $this->contactPhone,
            'note' => $this->note,
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

    private function log(): void
    {
        $item = $this->copy->item;

        LogUserAction::dispatch(
            user: $this->user,
            action: UserActionEnum::InsuranceRecordCreation,
            parameters: ['name' => $item->name],
        )->onQueue('low');

        LogItemAction::dispatch(
            item: $item,
            user: $this->user,
            action: ItemActionEnum::InsuranceRecordCreation,
            parameters: ['label' => $this->provider],
        )->onQueue('low');
    }
}
