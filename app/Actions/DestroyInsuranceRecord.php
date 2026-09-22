<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ItemActionEnum;
use App\Enums\UserActionEnum;
use App\Jobs\LogItemAction;
use App\Jobs\LogUserAction;
use App\Models\InsuranceRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Delete a piece of insurance coverage. Only owners and editors of its account
 * may do so.
 */
class DestroyInsuranceRecord
{
    public function __construct(
        private readonly User $user,
        private readonly InsuranceRecord $record,
    ) {}

    public function execute(): void
    {
        $this->validate();
        $this->log();
        $this->record->delete();
    }

    private function validate(): void
    {
        $account = $this->record->copy->item->catalog->account;

        if (! $account->allowsManagementBy($this->user)) {
            throw new ModelNotFoundException('Account not found');
        }
    }

    private function log(): void
    {
        $item = $this->record->copy->item;

        LogUserAction::dispatch(
            user: $this->user,
            action: UserActionEnum::InsuranceRecordDeletion,
            parameters: ['name' => $item->name],
        )->onQueue('low');

        LogItemAction::dispatch(
            item: $item,
            user: $this->user,
            action: ItemActionEnum::InsuranceRecordDeletion,
            parameters: ['label' => $this->record->provider],
        )->onQueue('low');
    }
}
