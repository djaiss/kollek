<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ItemActionEnum;
use App\Enums\UserActionEnum;
use App\Jobs\LogItemAction;
use App\Jobs\LogUserAction;
use App\Models\ItemPhoto;
use App\Models\User;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Delete a photo of an item, removing both the row and the file from the disk.
 * Deleting the main visual promotes the next remaining photo, so an item with
 * photos always has exactly one. Only owners and editors of the item's account
 * may do so.
 */
class DestroyItemPhoto
{
    public function __construct(
        private readonly User $user,
        private readonly ItemPhoto $itemPhoto,
    ) {}

    public function execute(): void
    {
        $this->validate();
        $this->log();
        $this->destroy();
    }

    private function validate(): void
    {
        if (! $this->itemPhoto->item->catalog->account->allowsManagementBy($this->user)) {
            throw new ModelNotFoundException('Account not found');
        }
    }

    private function destroy(): void
    {
        $path = $this->itemPhoto->path;

        DB::transaction(function (): void {
            $wasMain = $this->itemPhoto->is_main;

            // The foreign key cascades on MySQL and Postgres, but SQLite only
            // enforces one when the connection asks it to, and SQLite is a
            // supported way to run this app. Clearing the hashes here means the
            // index cannot outlive the photo whatever the database is.
            $this->itemPhoto->photoSearchTokens()->delete();

            $this->itemPhoto->delete();

            if (! $wasMain) {
                return;
            }

            $this->promoteNextPhoto();
        });

        $this->disk()->delete($path);
    }

    private function promoteNextPhoto(): void
    {
        $next = $this->itemPhoto->item->photos()->first();

        if (! $next instanceof ItemPhoto) {
            return;
        }

        $next->is_main = true;
        $next->save();
    }

    private function disk(): Filesystem
    {
        return Storage::disk((string) config('filesystems.default'));
    }

    private function log(): void
    {
        LogUserAction::dispatch(
            user: $this->user,
            action: UserActionEnum::ItemPhotoDeletion,
            parameters: ['name' => $this->itemPhoto->item->name],
        )->onQueue('low');

        LogItemAction::dispatch(
            item: $this->itemPhoto->item,
            user: $this->user,
            action: ItemActionEnum::PhotoDeleted,
            parameters: ['file' => $this->itemPhoto->filename],
        )->onQueue('low');
    }
}
