<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Account;
use App\Models\ItemPhoto;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Delete several photos of an account at once, as the photos screen lets a user
 * do with a selection. Only owners and editors of the account may do so.
 */
class DestroyItemPhotos
{
    /**
     * @param  list<int>  $photoIds
     */
    public function __construct(
        private readonly User $user,
        private readonly Account $account,
        private readonly array $photoIds,
    ) {}

    /**
     * The number of photos deleted.
     */
    public function execute(): int
    {
        $this->validate();

        return $this->destroy();
    }

    private function validate(): void
    {
        if (! $this->account->allowsManagementBy($this->user)) {
            throw new ModelNotFoundException('Account not found');
        }
    }

    private function destroy(): int
    {
        $photos = ItemPhoto::query()
            ->ofAccount($this->account)
            ->whereKey($this->photoIds)
            ->get();

        if ($photos->count() !== count(array_unique($this->photoIds))) {
            throw new ModelNotFoundException('Photo not found');
        }

        $photos->each(fn (ItemPhoto $photo) => new DestroyItemPhoto(
            user: $this->user,
            itemPhoto: $photo,
        )->execute());

        return $photos->count();
    }
}
