<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\LocationHistory;
use App\Models\User;
use App\Traits\RecordsCopyMoves;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Delete a past location record. Only owners and editors of its account may do
 * so.
 */
class DestroyLocationHistory
{
    use RecordsCopyMoves;

    public function __construct(
        private readonly User $user,
        private readonly LocationHistory $record,
    ) {}

    public function execute(): void
    {
        $this->validate();

        $copy = $this->record->copy;
        $this->record->delete();

        $this->syncCurrentLocationFromOpenRecord($copy);
    }

    private function validate(): void
    {
        $account = $this->record->copy->item->catalog->account;

        if (! $account->allowsManagementBy($this->user)) {
            throw new ModelNotFoundException('Account not found');
        }
    }
}
