<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ItemActionEnum;
use App\Enums\UserActionEnum;
use App\Jobs\LogItemAction;
use App\Jobs\LogUserAction;
use App\Models\Document;
use App\Models\User;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;

/**
 * Remove a document, deleting its stored file from the disk along with the row.
 * An external document has no file to remove. Only owners and editors of the
 * document's account may do so.
 */
class DestroyDocument
{
    public function __construct(
        private readonly User $user,
        private readonly Document $document,
    ) {}

    public function execute(): void
    {
        $this->validate();
        $this->log();
        $this->destroy();
    }

    private function validate(): void
    {
        if (! $this->document->account->allowsManagementBy($this->user)) {
            throw new ModelNotFoundException('Account not found');
        }
    }

    private function destroy(): void
    {
        $path = $this->document->path;

        $this->document->delete();

        if ($path === null) {
            return;
        }

        $this->disk()->delete($path);
    }

    private function disk(): Filesystem
    {
        return Storage::disk((string) config('filesystems.default'));
    }

    private function log(): void
    {
        $item = $this->document->item();

        LogUserAction::dispatch(
            user: $this->user,
            action: UserActionEnum::DocumentDeletion,
            parameters: ['name' => $item->name],
        )->onQueue('low');

        LogItemAction::dispatch(
            item: $item,
            user: $this->user,
            action: ItemActionEnum::DocumentDeletion,
            parameters: ['label' => $this->document->name],
        )->onQueue('low');
    }
}
