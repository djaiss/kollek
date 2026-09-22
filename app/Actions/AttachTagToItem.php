<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ItemActionEnum;
use App\Enums\UserActionEnum;
use App\Helpers\TextSanitizer;
use App\Jobs\LogItemAction;
use App\Jobs\LogUserAction;
use App\Models\Item;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

/**
 * Put a tag on an item, naming it. A name the account already knows reuses that
 * tag, and any other name creates one. Only owners and editors of the item's
 * account may do so.
 */
class AttachTagToItem
{
    private Tag $tag;

    private bool $attached = false;

    public function __construct(
        private readonly User $user,
        private readonly Item $item,
        private string $name,
    ) {}

    public function execute(): Tag
    {
        $this->validate();
        $this->sanitize();
        $this->resolve();
        $this->attach();
        $this->reindexSearch();
        $this->log();

        return $this->tag;
    }

    private function validate(): void
    {
        if (! $this->item->catalog->account->allowsManagementBy($this->user)) {
            throw new ModelNotFoundException('Account not found');
        }
    }

    private function sanitize(): void
    {
        $this->name = TextSanitizer::plainText($this->name);

        if ($this->name === '') {
            throw ValidationException::withMessages(['name' => __('The name is required.')]);
        }
    }

    private function resolve(): void
    {
        $account = $this->item->catalog->account;

        $existing = $account->tags()->get()->first(
            fn (Tag $tag): bool => mb_strtolower($tag->name) === mb_strtolower($this->name),
        );

        if ($existing instanceof Tag) {
            $this->tag = $existing;

            return;
        }

        $this->tag = new CreateTag(
            user: $this->user,
            account: $account,
            name: $this->name,
        )->execute();
    }

    private function attach(): void
    {
        $changes = $this->item->tags()->syncWithoutDetaching([$this->tag->id]);

        $this->attached = $changes['attached'] !== [];
    }

    private function reindexSearch(): void
    {
        if (! $this->attached) {
            return;
        }

        $this->item->load(['catalog', 'category', 'set', 'series', 'catalogType', 'tags', 'customFieldValues']);

        new IndexSearchable(searchable: $this->item)->execute();
    }

    private function log(): void
    {
        if (! $this->attached) {
            return;
        }

        LogUserAction::dispatch(
            user: $this->user,
            action: UserActionEnum::ItemTagAttached,
            parameters: ['tag' => $this->tag->name, 'name' => $this->item->name],
        )->onQueue('low');

        LogItemAction::dispatch(
            item: $this->item,
            user: $this->user,
            action: ItemActionEnum::TagAttached,
            parameters: ['label' => $this->tag->name],
        )->onQueue('low');
    }
}
