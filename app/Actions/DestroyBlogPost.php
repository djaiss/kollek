<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\UserActionEnum;
use App\Jobs\LogUserAction;
use App\Models\BlogPost;
use App\Models\BlogPostTranslation;
use App\Models\User;
use App\Services\CloudflareCache;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;

/**
 * Delete a blog entry outright, with every language of it and every old slug
 * that pointed at it.
 *
 * This is the destructive option, and rarely the right one: archiving keeps the
 * URL answering for the links already out in the world. Deleting is for an
 * entry that should never have existed. Its reference is not reissued.
 */
class DestroyBlogPost
{
    private string $reference;

    public function __construct(
        private readonly User $user,
        private readonly BlogPost $blogPost,
    ) {}

    public function execute(): void
    {
        $this->validate();
        $this->deleteSocialCards();
        $this->delete();
        $this->flushMarketingCache();
        $this->log();
    }

    private function validate(): void
    {
        if (! $this->user->isInstanceAdministrator()) {
            throw new ModelNotFoundException('Blog post not found');
        }

        $this->reference = $this->blogPost->reference();
    }

    /**
     * The rows cascade, but the files behind them do not, so they go first.
     */
    private function deleteSocialCards(): void
    {
        $this->blogPost->translations
            ->filter(fn (BlogPostTranslation $translation): bool => $translation->og_image_path !== null)
            ->each(fn (BlogPostTranslation $translation) => $this->disk()->delete((string) $translation->og_image_path));
    }

    private function delete(): void
    {
        $this->blogPost->delete();
    }

    private function flushMarketingCache(): void
    {
        CloudflareCache::purgeEverything();
    }

    private function disk(): Filesystem
    {
        return Storage::disk((string) config('filesystems.default'));
    }

    private function log(): void
    {
        LogUserAction::dispatch(
            user: $this->user,
            action: UserActionEnum::BlogPostDeletion,
            parameters: ['reference' => $this->reference],
        )->onQueue('low');
    }
}
