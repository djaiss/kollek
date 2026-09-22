<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ItemActionEnum;
use App\Enums\UserActionEnum;
use App\Enums\ValuationConfidence;
use App\Enums\ValuationType;
use App\Helpers\Money;
use App\Jobs\LogItemAction;
use App\Jobs\LogUserAction;
use App\Models\User;
use App\Models\Valuation;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Update a valuation. Only owners and editors of its account may do so.
 */
class UpdateValuation
{
    /**
     * The values that moved, captured before the valuation is written so the
     * activity tab can show what they moved from.
     *
     * @var list<array{label: string, from: string|null, to: string|null}>
     */
    private array $changes = [];

    public function __construct(
        private readonly User $user,
        private readonly Valuation $valuation,
        private readonly ValuationType $type,
        private readonly int $amount,
        private readonly string $valuedAt,
        private readonly ?string $currencyCode = null,
        private readonly ValuationConfidence $confidence = ValuationConfidence::Unknown,
        private readonly ?string $valuer = null,
        private readonly ?string $method = null,
        private readonly ?string $sourceUrl = null,
        private readonly ?string $referenceNumber = null,
        private readonly ?string $note = null,
    ) {}

    public function execute(): Valuation
    {
        $this->validate();
        $this->captureChanges();
        $this->update();
        $this->log();

        return $this->valuation;
    }

    private function validate(): void
    {
        $account = $this->valuation->copy->item->catalog->account;

        if (! $account->allowsManagementBy($this->user)) {
            throw new ModelNotFoundException('Account not found');
        }
    }

    private function captureChanges(): void
    {
        $currency = $this->valuation->currency_code;

        $this->changes = array_values(array_filter([
            $this->change('Type', $this->valuation->type->label(), $this->type->label()),
            $this->change('Amount', Money::format($this->valuation->amount, $currency), Money::format($this->amount, $this->currencyCode ?? $currency)),
            $this->change('Valued', $this->valuation->valued_at->toDateString(), $this->valuedAt),
            $this->change('Confidence', $this->valuation->confidence->label(), $this->confidence->label()),
            $this->change('Valuer', $this->valuation->valuer, $this->valuer),
            $this->change('Method', $this->valuation->method, $this->method),
            $this->change('Reference', $this->valuation->reference_number, $this->referenceNumber),
            $this->change('Note', $this->valuation->note, $this->note),
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
        $this->valuation->fill([
            'type' => $this->type,
            'amount' => $this->amount,
            'currency_code' => $this->currencyCode ?? $this->valuation->currency_code,
            'valued_at' => $this->valuedAt,
            'confidence' => $this->confidence,
            'valuer' => $this->valuer,
            'method' => $this->method,
            'source_url' => $this->sourceUrl,
            'reference_number' => $this->referenceNumber,
            'note' => $this->note,
        ]);
        $this->valuation->updated_by_id = $this->user->id;
        $this->valuation->updated_by_name = $this->user->getFullName();
        $this->valuation->save();
    }

    private function log(): void
    {
        $item = $this->valuation->copy->item;

        LogUserAction::dispatch(
            user: $this->user,
            action: UserActionEnum::ValuationUpdate,
            parameters: ['name' => $item->name],
        )->onQueue('low');

        LogItemAction::dispatch(
            item: $item,
            user: $this->user,
            action: ItemActionEnum::ValuationUpdate,
            parameters: $this->changes === [] ? null : ['changes' => $this->changes],
        )->onQueue('low');
    }
}
