<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ItemActionEnum;
use App\Enums\UserActionEnum;
use App\Jobs\LogItemAction;
use App\Jobs\LogUserAction;
use App\Models\User;
use App\Models\Valuation;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Delete a valuation. Only owners and editors of its account may do so.
 */
class DestroyValuation
{
    public function __construct(
        private readonly User $user,
        private readonly Valuation $valuation,
    ) {}

    public function execute(): void
    {
        $this->validate();
        $this->log();
        $this->valuation->delete();
    }

    private function validate(): void
    {
        $account = $this->valuation->copy->item->catalog->account;

        if (! $account->allowsManagementBy($this->user)) {
            throw new ModelNotFoundException('Account not found');
        }
    }

    private function log(): void
    {
        $item = $this->valuation->copy->item;

        LogUserAction::dispatch(
            user: $this->user,
            action: UserActionEnum::ValuationDeletion,
            parameters: ['name' => $item->name],
        )->onQueue('low');

        LogItemAction::dispatch(
            item: $item,
            user: $this->user,
            action: ItemActionEnum::ValuationDeletion,
            parameters: ['label' => $this->valuation->type->label()],
        )->onQueue('low');
    }
}
