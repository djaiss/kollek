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
        $this->deleteImages();
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

    private function deleteSocialCards(): void
    {
        $this->blogPost->translations
            ->filter(fn (BlogPostTranslation $translation): bool => $translation->og_image_path !== null)
            ->each(fn (BlogPostTranslation $translation) => $this->disk()->delete((string) $translation->og_image_path));
    }

    private function deleteImages(): void
    {
        $this->disk()->deleteDirectory('blog/'.$this->blogPost->id.'/body');
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
