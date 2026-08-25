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
 * Remove the social card of one language of a blog entry, which puts it back to
 * sharing under the site wide card.
 */
class DestroyBlogPostOgImage
{
    public function __construct(
        private readonly User $user,
        private readonly BlogPost $blogPost,
        private readonly BlogPostTranslation $translation,
    ) {}

    public function execute(): BlogPostTranslation
    {
        $this->validate();
        $this->deleteFile();
        $this->save();
        $this->flushMarketingCache();
        $this->log();

        return $this->translation;
    }

    private function validate(): void
    {
        if (! $this->user->isInstanceAdministrator()) {
            throw new ModelNotFoundException('Blog post not found');
        }

        if ($this->translation->blog_post_id !== $this->blogPost->id) {
            throw new ModelNotFoundException('Blog post not found');
        }
    }

    private function deleteFile(): void
    {
        if ($this->translation->og_image_path === null) {
            return;
        }

        $this->disk()->delete($this->translation->og_image_path);
    }

    private function save(): void
    {
        $this->translation->update([
            'og_image_path' => null,
        ]);
    }

    private function flushMarketingCache(): void
    {
        if (! $this->blogPost->status->isReadable()) {
            return;
        }

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
            action: UserActionEnum::BlogPostOgImageDeletion,
            parameters: ['reference' => $this->blogPost->reference(), 'locale' => $this->translation->locale],
        )->onQueue('low');
    }
}
