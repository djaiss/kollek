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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use InvalidArgumentException;

/**
 * Set the social card one language of a blog entry shares as.
 *
 * The card is written at exactly 1200x630, the size every social platform reads,
 * by covering the box rather than fitting inside it: a letterboxed card with
 * bars down the side looks broken in a timeline.
 *
 * The earlier card is only removed once the new one is saved, so a failure
 * halfway through leaves the entry with a working card rather than none.
 */
class UpdateBlogPostOgImage
{
    /**
     * The mime types we accept. Anything else is rejected, whatever the
     * extension of the uploaded file claims.
     */
    private const array ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    private const int MAX_SIZE_IN_BYTES = 5 * 1024 * 1024;

    private const int WIDTH = 1200;

    private const int HEIGHT = 630;

    private ?string $previousPath = null;

    private string $path;

    public function __construct(
        private readonly User $user,
        private readonly BlogPost $blogPost,
        private readonly BlogPostTranslation $translation,
        private readonly UploadedFile $file,
    ) {}

    public function execute(): BlogPostTranslation
    {
        $this->validate();
        $this->store();
        $this->resize();
        $this->save();
        $this->deletePreviousCard();
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

        if (! in_array($this->file->getMimeType(), self::ALLOWED_MIME_TYPES, true)) {
            throw new InvalidArgumentException('The file must be a jpeg, png or webp image');
        }

        if ($this->file->getSize() > self::MAX_SIZE_IN_BYTES) {
            throw new InvalidArgumentException('The file must not be larger than 5 MB');
        }
    }

    /**
     * The name the uploader gave the file never reaches the disk: we generate a
     * random one instead.
     */
    private function store(): void
    {
        $this->previousPath = $this->translation->og_image_path;

        $name = Str::uuid()->toString().'.'.$this->file->extension();

        $this->path = (string) $this->disk()->putFileAs('blog/'.$this->blogPost->id, $this->file, $name);
    }

    private function resize(): void
    {
        $image = new ImageManager(new Driver)->decodeBinary((string) $this->disk()->get($this->path));

        $this->disk()->put(
            $this->path,
            (string) $image->cover(self::WIDTH, self::HEIGHT)->encodeUsingMediaType((string) $this->file->getMimeType()),
        );
    }

    private function save(): void
    {
        $this->translation->update([
            'og_image_path' => $this->path,
        ]);
    }

    private function deletePreviousCard(): void
    {
        if ($this->previousPath === null) {
            return;
        }

        $this->disk()->delete($this->previousPath);
    }

    private function flushMarketingCache(): void
    {
        if (! $this->blogPost->status->isReadable()) {
            return;
        }

        CloudflareCache::purgeEverything();
    }

    /**
     * The disk lives here alone so it can be swapped in one place.
     */
    private function disk(): Filesystem
    {
        return Storage::disk((string) config('filesystems.default'));
    }

    private function log(): void
    {
        LogUserAction::dispatch(
            user: $this->user,
            action: UserActionEnum::BlogPostOgImageUpdate,
            parameters: ['reference' => $this->blogPost->reference(), 'locale' => $this->translation->locale],
        )->onQueue('low');
    }
}
