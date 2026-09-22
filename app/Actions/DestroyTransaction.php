<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ItemActionEnum;
use App\Enums\UserActionEnum;
use App\Jobs\LogItemAction;
use App\Jobs\LogUserAction;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Delete a transaction. Only owners and editors of its account may do so.
 */
class DestroyTransaction
{
    public function __construct(
        private readonly User $user,
        private readonly Transaction $transaction,
    ) {}

    public function execute(): void
    {
        $this->validate();
        $this->unlinkProvenance();
        $this->log();
        $this->transaction->delete();
    }

    private function validate(): void
    {
        $account = $this->transaction->copy->item->catalog->account;

        if (! $account->allowsManagementBy($this->user)) {
            throw new ModelNotFoundException('Account not found');
        }
    }

    private function unlinkProvenance(): void
    {
        $this->transaction->provenanceEvent()->update(['transaction_id' => null]);
    }

    private function log(): void
    {
        $item = $this->transaction->copy->item;

        LogUserAction::dispatch(
            user: $this->user,
            action: UserActionEnum::TransactionDeletion,
            parameters: ['name' => $item->name],
        )->onQueue('low');

        LogItemAction::dispatch(
            item: $item,
            user: $this->user,
            action: ItemActionEnum::TransactionDeletion,
            parameters: ['label' => $this->transaction->type->label()],
        )->onQueue('low');
    }
}
