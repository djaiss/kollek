<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Location;
use App\Models\LocationHistory;
use App\Models\User;
use App\Traits\RecordsCopyMoves;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Correct a past location record. Only owners and editors of its account may do
 * so.
 */
class UpdateLocationHistory
{
    use RecordsCopyMoves;

    public function __construct(
        private readonly User $user,
        private readonly LocationHistory $record,
        private readonly ?Location $location,
        private readonly string $movedAt,
        private readonly ?string $movedOutAt = null,
        private readonly ?string $reason = null,
        private readonly ?string $note = null,
    ) {}

    public function execute(): LocationHistory
    {
        $this->validate();
        $this->update();
        $this->syncCurrentLocationFromOpenRecord($this->record->copy);

        return $this->record;
    }

    private function validate(): void
    {
        $account = $this->record->copy->item->catalog->account;

        if (! $account->allowsManagementBy($this->user)) {
            throw new ModelNotFoundException('Account not found');
        }

        if ($this->location instanceof Location && $this->location->account_id !== $account->id) {
            throw new ModelNotFoundException('Location not found');
        }
    }

    private function update(): void
    {
        $this->record->fill([
            'location_id' => $this->location?->id,
            'moved_at' => $this->movedAt,
            'moved_out_at' => $this->movedOutAt,
            'reason' => $this->reason,
            'note' => $this->note,
        ]);
        $this->record->updated_by_id = $this->user->id;
        $this->record->updated_by_name = $this->user->getFullName();
        $this->record->save();
    }
}
