<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ItemActionEnum;
use App\Enums\UserActionEnum;
use App\Jobs\LogItemAction;
use App\Jobs\LogUserAction;
use App\Models\ProvenanceEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Delete a provenance event. Only owners and editors of its account may do so.
 */
class DestroyProvenanceEvent
{
    public function __construct(
        private readonly User $user,
        private readonly ProvenanceEvent $event,
    ) {}

    public function execute(): void
    {
        $this->validate();
        $this->log();
        $this->event->delete();
    }

    private function validate(): void
    {
        $account = $this->event->copy->item->catalog->account;

        if (! $account->allowsManagementBy($this->user)) {
            throw new ModelNotFoundException('Account not found');
        }
    }

    private function log(): void
    {
        $item = $this->event->copy->item;

        LogUserAction::dispatch(
            user: $this->user,
            action: UserActionEnum::ProvenanceEventDeletion,
            parameters: ['name' => $item->name],
        )->onQueue('low');

        LogItemAction::dispatch(
            item: $item,
            user: $this->user,
            action: ItemActionEnum::ProvenanceEventDeletion,
            parameters: ['label' => $this->event->type->label()],
        )->onQueue('low');
    }
}
