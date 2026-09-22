<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\CopyStatus;
use App\Enums\ItemActionEnum;
use App\Enums\UserActionEnum;
use App\Enums\ValuationConfidence;
use App\Enums\ValuationType;
use App\Helpers\Money;
use App\Jobs\LogItemAction;
use App\Jobs\LogUserAction;
use App\Models\Copy;
use App\Models\ItemCondition;
use App\Models\Location;
use App\Models\User;
use App\Models\Valuation;
use App\Traits\RecordsCopyMoves;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Update a copy of an item. Only owners and editors of its account may do so.
 */
class UpdateCopy
{
    use RecordsCopyMoves;

    /**
     * The values that moved, captured before the copy is written so the
     * activity tab can show what they moved from.
     *
     * @var list<array{label: string, from: string|null, to: string|null}>
     */
    private array $changes = [];

    public function __construct(
        private readonly User $user,
        private readonly Copy $copy,
        private readonly ?ItemCondition $itemCondition = null,
        private readonly ?Location $location = null,
        private readonly ?string $identifier = null,
        private readonly CopyStatus $status = CopyStatus::Owned,
        private readonly int $quantity = 1,
        private readonly ?string $disposedAt = null,
        private readonly ?string $note = null,
        private readonly ?int $estimatedValue = null,
    ) {}

    public function execute(): Copy
    {
        $this->validate();
        $this->captureChanges();
        $this->update();
        $this->move();
        $this->value();
        $this->log();

        return $this->copy;
    }

    private function validate(): void
    {
        $account = $this->copy->item->catalog->account;

        if (! $account->allowsManagementBy($this->user)) {
            throw new ModelNotFoundException('Account not found');
        }

        if ($this->itemCondition instanceof ItemCondition && $this->itemCondition->account_id !== $account->id) {
            throw new ModelNotFoundException('Condition not found');
        }

        if ($this->location instanceof Location && $this->location->account_id !== $account->id) {
            throw new ModelNotFoundException('Location not found');
        }
    }

    private function captureChanges(): void
    {
        $currency = $this->copy->item->catalog->currency;

        $this->changes = array_values(array_filter([
            $this->change('Identifier', $this->copy->identifier, $this->identifier),
            $this->change('Condition', $this->copy->itemCondition?->name, $this->itemCondition?->name),
            $this->change('Location', $this->copy->currentLocation?->name, $this->location?->name),
            $this->change('Status', $this->copy->status->label(), $this->status->label()),
            $this->change('Quantity', (string) $this->copy->quantity, (string) $this->quantity),
            $this->change('Disposed', $this->copy->disposed_at?->toDateString(), $this->disposedAt),
            $this->change('Note', $this->copy->note, $this->note),
            $this->change('Estimated value', $this->amount($this->copy->estimatedValue(), $currency), $this->amount($this->estimatedValue, $currency)),
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

    private function amount(?int $cents, ?string $currency): ?string
    {
        return $cents === null ? null : Money::format($cents, $currency);
    }

    private function update(): void
    {
        $this->copy->fill([
            'identifier' => $this->identifier,
            'item_condition_id' => $this->itemCondition?->id,
            'status' => $this->status,
            'quantity' => $this->quantity,
            'disposed_at' => $this->disposedAt,
            'note' => $this->note,
        ]);
        $this->copy->updated_by_id = $this->user->id;
        $this->copy->updated_by_name = $this->user->getFullName();
        $this->copy->save();
    }

    private function move(): void
    {
        $this->recordCopyMove($this->copy, $this->location?->id, $this->user);
    }

    private function value(): void
    {
        if ($this->estimatedValue === null || $this->estimatedValue === $this->copy->estimatedValue()) {
            return;
        }

        $valuation = new Valuation([
            'copy_id' => $this->copy->id,
            'type' => ValuationType::UserEstimate,
            'amount' => $this->estimatedValue,
            'currency_code' => $this->copy->item->catalog->currency,
            'valued_at' => now()->toDateString(),
            'confidence' => ValuationConfidence::Unknown,
        ]);

        $valuation->created_by_id = $this->user->id;
        $valuation->created_by_name = $this->user->getFullName();
        $valuation->updated_by_id = $this->user->id;
        $valuation->updated_by_name = $this->user->getFullName();
        $valuation->save();

        $this->copy->unsetRelation('latestValuation')->unsetRelation('valuations');
    }

    private function log(): void
    {
        LogUserAction::dispatch(
            user: $this->user,
            action: UserActionEnum::CopyUpdate,
            parameters: ['name' => $this->copy->item->name],
        )->onQueue('low');

        LogItemAction::dispatch(
            item: $this->copy->item,
            user: $this->user,
            action: ItemActionEnum::CopyUpdate,
            parameters: $this->changes === [] ? null : ['changes' => $this->changes],
        )->onQueue('low');
    }
}
