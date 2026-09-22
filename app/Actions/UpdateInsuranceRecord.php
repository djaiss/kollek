<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\InsuranceStatus;
use App\Enums\ItemActionEnum;
use App\Enums\UserActionEnum;
use App\Helpers\Money;
use App\Jobs\LogItemAction;
use App\Jobs\LogUserAction;
use App\Models\InsuranceRecord;
use App\Models\User;
use App\Traits\GuardsActiveInsuranceRecord;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Update a piece of insurance coverage. Only owners and editors of its account
 * may do so, and reviving it to active is refused while another active record on
 * the copy carries the same policy number.
 */
class UpdateInsuranceRecord
{
    use GuardsActiveInsuranceRecord;

    /**
     * The values that moved, captured before the record is written so the
     * activity tab can show what they moved from.
     *
     * @var list<array{label: string, from: string|null, to: string|null}>
     */
    private array $changes = [];

    public function __construct(
        private readonly User $user,
        private readonly InsuranceRecord $record,
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
        $this->captureChanges();
        $this->update();
        $this->log();

        return $this->record;
    }

    private function validate(): void
    {
        $account = $this->record->copy->item->catalog->account;

        if (! $account->allowsManagementBy($this->user)) {
            throw new ModelNotFoundException('Account not found');
        }

        $this->guardAgainstSecondActiveRecord($this->record->copy, $this->status, $this->policyNumber, $this->record->id);
    }

    private function captureChanges(): void
    {
        $currency = $this->record->currency_code;

        $this->changes = array_values(array_filter([
            $this->change('Provider', $this->record->provider, $this->provider),
            $this->change('Policy', $this->record->policy_number, $this->policyNumber),
            $this->change('Insured value', Money::format($this->record->insured_value, $currency), Money::format($this->insuredValue, $this->currencyCode ?? $currency)),
            $this->change('Status', $this->record->status->label(), $this->status->label()),
            $this->change('Starts', $this->record->starts_at?->toDateString(), $this->startsAt),
            $this->change('Ends', $this->record->ends_at?->toDateString(), $this->endsAt),
            $this->change('Note', $this->record->note, $this->note),
        ]));
    }

    /**
     * @return array{label: string, from: string|null, to: string|null}|null
     */
    private function change(string $label, ?string $from, ?string $to): ?array
    {
        if ($from === $to) {
            return null;
        }

        return ['label' => $label, 'from' => $from, 'to' => $to];
    }

    private function update(): void
    {
        $this->record->fill([
            'provider' => $this->provider,
            'policy_number' => $this->policyNumber,
            'coverage_type' => $this->coverageType,
            'insured_value' => $this->insuredValue,
            'currency_code' => $this->currencyCode ?? $this->record->currency_code,
            'deductible_amount' => $this->deductibleAmount,
            'deductible_currency_code' => $this->deductibleAmount === null
                ? null
                : ($this->deductibleCurrencyCode ?? $this->currencyCode ?? $this->record->currency_code),
            'starts_at' => $this->startsAt,
            'ends_at' => $this->endsAt,
            'status' => $this->status,
            'is_scheduled_item' => $this->isScheduledItem,
            'contact_name' => $this->contactName,
            'contact_email' => $this->contactEmail,
            'contact_phone' => $this->contactPhone,
            'note' => $this->note,
        ]);
        $this->record->updated_by_id = $this->user->id;
        $this->record->updated_by_name = $this->user->getFullName();
        $this->record->save();
    }

    private function log(): void
    {
        $item = $this->record->copy->item;

        LogUserAction::dispatch(
            user: $this->user,
            action: UserActionEnum::InsuranceRecordUpdate,
            parameters: ['name' => $item->name],
        )->onQueue('low');

        LogItemAction::dispatch(
            item: $item,
            user: $this->user,
            action: ItemActionEnum::InsuranceRecordUpdate,
            parameters: $this->changes === [] ? null : ['changes' => $this->changes],
        )->onQueue('low');
    }
}
