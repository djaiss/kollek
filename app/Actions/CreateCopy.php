<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\CopyStatus;
use App\Enums\ItemActionEnum;
use App\Enums\UserActionEnum;
use App\Enums\ValuationConfidence;
use App\Enums\ValuationType;
use App\Jobs\LogItemAction;
use App\Jobs\LogUserAction;
use App\Models\Copy;
use App\Models\Item;
use App\Models\ItemCondition;
use App\Models\Location;
use App\Models\User;
use App\Models\Valuation;
use App\Traits\RecordsCopyMoves;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Create a copy of an item. Only owners and editors of the item's account may
 * do so.
 */
class CreateCopy
{
    use RecordsCopyMoves;

    private Copy $copy;

    public function __construct(
        private readonly User $user,
        private readonly Item $item,
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
        $this->create();
        $this->stampAuthor();
        $this->move();
        $this->value();
        $this->log();

        return $this->copy;
    }

    private function validate(): void
    {
        $account = $this->item->catalog->account;

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

    private function create(): void
    {
        $this->copy = Copy::query()->create([
            'item_id' => $this->item->id,
            'identifier' => $this->identifier,
            'item_condition_id' => $this->itemCondition?->id,
            'status' => $this->status,
            'quantity' => $this->quantity,
            'disposed_at' => $this->disposedAt,
            'note' => $this->note,
        ]);
    }

    private function move(): void
    {
        $this->recordCopyMove($this->copy, $this->location?->id, $this->user);
    }

    private function stampAuthor(): void
    {
        $this->copy->created_by_id = $this->user->id;
        $this->copy->created_by_name = $this->user->getFullName();
        $this->copy->updated_by_id = $this->user->id;
        $this->copy->updated_by_name = $this->user->getFullName();
        $this->copy->save();
    }

    private function value(): void
    {
        if ($this->estimatedValue === null) {
            return;
        }

        $valuation = new Valuation([
            'copy_id' => $this->copy->id,
            'type' => ValuationType::UserEstimate,
            'amount' => $this->estimatedValue,
            'currency_code' => $this->item->catalog->currency,
            'valued_at' => now()->toDateString(),
            'confidence' => ValuationConfidence::Unknown,
        ]);

        $valuation->created_by_id = $this->user->id;
        $valuation->created_by_name = $this->user->getFullName();
        $valuation->updated_by_id = $this->user->id;
        $valuation->updated_by_name = $this->user->getFullName();
        $valuation->save();
    }

    private function log(): void
    {
        LogUserAction::dispatch(
            user: $this->user,
            action: UserActionEnum::CopyCreation,
            parameters: ['name' => $this->item->name],
        )->onQueue('low');

        LogItemAction::dispatch(
            item: $this->item,
            user: $this->user,
            action: ItemActionEnum::CopyCreation,
            parameters: $this->itemCondition instanceof ItemCondition ? ['label' => $this->itemCondition->name] : null,
        )->onQueue('low');
    }
}
