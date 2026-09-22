<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ItemActionEnum;
use App\Enums\TransactionType;
use App\Enums\UserActionEnum;
use App\Helpers\Money;
use App\Jobs\LogItemAction;
use App\Jobs\LogUserAction;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Update a transaction. Only owners and editors of its account may do so.
 */
class UpdateTransaction
{
    /**
     * The values that moved, captured before the transaction is written so the
     * activity tab can show what they moved from.
     *
     * @var list<array{label: string, from: string|null, to: string|null}>
     */
    private array $changes = [];

    public function __construct(
        private readonly User $user,
        private readonly Transaction $transaction,
        private readonly TransactionType $type,
        private readonly string $occurredAt,
        private readonly ?string $counterparty = null,
        private readonly ?int $amount = null,
        private readonly ?string $currencyCode = null,
        private readonly ?int $taxAmount = null,
        private readonly ?int $feeAmount = null,
        private readonly ?int $shippingAmount = null,
        private readonly ?int $totalAmount = null,
        private readonly ?string $referenceNumber = null,
        private readonly ?string $sourceUrl = null,
        private readonly ?string $note = null,
    ) {}

    public function execute(): Transaction
    {
        $this->validate();
        $this->captureChanges();
        $this->update();
        $this->log();

        return $this->transaction;
    }

    private function validate(): void
    {
        $account = $this->transaction->copy->item->catalog->account;

        if (! $account->allowsManagementBy($this->user)) {
            throw new ModelNotFoundException('Account not found');
        }
    }

    private function captureChanges(): void
    {
        $currency = $this->transaction->currency_code;

        $this->changes = array_values(array_filter([
            $this->change('Type', $this->transaction->type->label(), $this->type->label()),
            $this->change('Counterparty', $this->transaction->counterparty, $this->counterparty),
            $this->change('Amount', $this->amountLabel($this->transaction->amount, $currency), $this->amountLabel($this->amount, $this->currencyCode ?? $currency)),
            $this->change('Tax', $this->amountLabel($this->transaction->tax_amount, $currency), $this->amountLabel($this->taxAmount, $this->currencyCode ?? $currency)),
            $this->change('Fees', $this->amountLabel($this->transaction->fee_amount, $currency), $this->amountLabel($this->feeAmount, $this->currencyCode ?? $currency)),
            $this->change('Shipping', $this->amountLabel($this->transaction->shipping_amount, $currency), $this->amountLabel($this->shippingAmount, $this->currencyCode ?? $currency)),
            $this->change('Total', $this->amountLabel($this->transaction->total_amount, $currency), $this->amountLabel($this->totalAmount, $this->currencyCode ?? $currency)),
            $this->change('Occurred', $this->transaction->occurred_at->toDateString(), $this->occurredAt),
            $this->change('Reference', $this->transaction->reference_number, $this->referenceNumber),
            $this->change('Note', $this->transaction->note, $this->note),
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

    private function amountLabel(?int $cents, ?string $currency): ?string
    {
        return $cents === null ? null : Money::format($cents, $currency);
    }

    private function update(): void
    {
        $this->transaction->fill([
            'type' => $this->type,
            'counterparty' => $this->counterparty,
            'amount' => $this->amount,
            'currency_code' => $this->currencyCode ?? $this->transaction->currency_code,
            'tax_amount' => $this->taxAmount,
            'fee_amount' => $this->feeAmount,
            'shipping_amount' => $this->shippingAmount,
            'total_amount' => $this->totalAmount,
            'occurred_at' => $this->occurredAt,
            'reference_number' => $this->referenceNumber,
            'source_url' => $this->sourceUrl,
            'note' => $this->note,
        ]);
        $this->transaction->updated_by_id = $this->user->id;
        $this->transaction->updated_by_name = $this->user->getFullName();
        $this->transaction->save();
    }

    private function log(): void
    {
        $item = $this->transaction->copy->item;

        LogUserAction::dispatch(
            user: $this->user,
            action: UserActionEnum::TransactionUpdate,
            parameters: ['name' => $item->name],
        )->onQueue('low');

        LogItemAction::dispatch(
            item: $item,
            user: $this->user,
            action: ItemActionEnum::TransactionUpdate,
            parameters: $this->changes === [] ? null : ['changes' => $this->changes],
        )->onQueue('low');
    }
}
