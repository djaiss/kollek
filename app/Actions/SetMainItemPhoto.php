<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ItemActionEnum;
use App\Enums\UserActionEnum;
use App\Jobs\LogItemAction;
use App\Jobs\LogUserAction;
use App\Models\ItemPhoto;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

/**
 * Designate a photo as the main visual of its item. An item has exactly one, so
 * the previous main is unset in the process. Only owners and editors of the
 * item's account may do so.
 */
class SetMainItemPhoto
{
    public function __construct(
        private readonly User $user,
        private readonly ItemPhoto $itemPhoto,
    ) {}

    public function execute(): ItemPhoto
    {
        $this->validate();
        $this->setAsMain();
        $this->log();

        return $this->itemPhoto;
    }

    private function validate(): void
    {
        if (! $this->itemPhoto->item->catalog->account->allowsManagementBy($this->user)) {
            throw new ModelNotFoundException('Account not found');
        }
    }

    private function setAsMain(): void
    {
        DB::transaction(function (): void {
            $this->itemPhoto->item->photos()
                ->where('is_main', true)
                ->whereKeyNot($this->itemPhoto->id)
                ->update(['is_main' => false]);

            $this->itemPhoto->is_main = true;
            $this->itemPhoto->save();
        });
    }

    private function log(): void
    {
        LogUserAction::dispatch(
            user: $this->user,
            action: UserActionEnum::ItemPhotoUpdate,
            parameters: ['name' => $this->itemPhoto->item->name],
        )->onQueue('low');

        LogItemAction::dispatch(
            item: $this->itemPhoto->item,
            user: $this->user,
            action: ItemActionEnum::PhotoMainSet,
            parameters: ['file' => $this->itemPhoto->filename],
        )->onQueue('low');
    }
}
