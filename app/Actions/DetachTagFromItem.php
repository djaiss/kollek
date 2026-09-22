<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ItemActionEnum;
use App\Enums\UserActionEnum;
use App\Jobs\LogItemAction;
use App\Jobs\LogUserAction;
use App\Models\Item;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Take a tag off an item. The tag itself stays on the account, ready to be used
 * again. Only owners and editors of the item's account may do so.
 */
class DetachTagFromItem
{
    public function __construct(
        private readonly User $user,
        private readonly Item $item,
        private readonly Tag $tag,
    ) {}

    public function execute(): Item
    {
        $this->validate();
        $this->detach();
        $this->reindexSearch();
        $this->log();

        return $this->item;
    }

    private function validate(): void
    {
        $account = $this->item->catalog->account;

        if (! $account->allowsManagementBy($this->user)) {
            throw new ModelNotFoundException('Account not found');
        }

        if ($this->tag->account_id !== $account->id) {
            throw new ModelNotFoundException('Tag not found');
        }
    }

    private function detach(): void
    {
        $this->item->tags()->detach($this->tag->id);
    }

    private function reindexSearch(): void
    {
        $this->item->load(['catalog', 'category', 'set', 'series', 'catalogType', 'tags', 'customFieldValues']);

        new IndexSearchable(searchable: $this->item)->execute();
    }

    private function log(): void
    {
        LogUserAction::dispatch(
            user: $this->user,
            action: UserActionEnum::ItemTagDetached,
            parameters: ['tag' => $this->tag->name, 'name' => $this->item->name],
        )->onQueue('low');

        LogItemAction::dispatch(
            item: $this->item,
            user: $this->user,
            action: ItemActionEnum::TagDetached,
            parameters: ['label' => $this->tag->name],
        )->onQueue('low');
    }
}
