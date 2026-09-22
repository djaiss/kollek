<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ItemActionEnum;
use App\Enums\UserActionEnum;
use App\Jobs\LogItemAction;
use App\Jobs\LogUserAction;
use App\Models\Copy;
use App\Models\Location;
use App\Models\User;
use App\Traits\RecordsCopyMoves;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Move a copy to a location. Only owners and editors of its account may do so.
 */
class MoveCopy
{
    use RecordsCopyMoves;

    private ?string $fromLocationName = null;

    public function __construct(
        private readonly User $user,
        private readonly Copy $copy,
        private readonly ?Location $location = null,
        private readonly ?string $movedAt = null,
        private readonly ?string $reason = null,
        private readonly ?string $note = null,
    ) {}

    public function execute(): Copy
    {
        $this->validate();
        $this->fromLocationName = $this->copy->currentLocation?->name;
        $this->recordCopyMove($this->copy, $this->location?->id, $this->user, $this->movedAt, $this->reason, $this->note);
        $this->log();

        return $this->copy;
    }

    private function validate(): void
    {
        $account = $this->copy->item->catalog->account;

        if (! $account->allowsManagementBy($this->user)) {
            throw new ModelNotFoundException('Account not found');
        }

        if ($this->location instanceof Location && $this->location->account_id !== $account->id) {
            throw new ModelNotFoundException('Location not found');
        }
    }

    private function log(): void
    {
        $item = $this->copy->item;

        LogUserAction::dispatch(
            user: $this->user,
            action: UserActionEnum::CopyMoved,
            parameters: ['name' => $item->name],
        )->onQueue('low');

        $changes = [[
            'label' => 'Location',
            'from' => $this->fromLocationName,
            'to' => $this->location?->name,
        ]];

        LogItemAction::dispatch(
            item: $item,
            user: $this->user,
            action: ItemActionEnum::CopyMoved,
            parameters: ['changes' => $changes],
        )->onQueue('low');
    }
}
