<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\UserActionEnum;
use App\Jobs\LogUserAction;
use App\Models\BlogPost;
use App\Models\User;
use App\Services\CloudflareCache;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Exceptions\DecoderException;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;
use InvalidArgumentException;

/**
 * Put a picture on the disk for a blog entry to show inside its text. The image
 * arrives base64 encoded.
 */
class AddBlogPostImage
{
    /**
     * Keyed by mime type, valued by the extension written on disk, so the name
     * can never take an extension from the caller.
     */
    private const array ALLOWED_MIME_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    private const int MAX_SIZE_IN_BYTES = 5 * 1024 * 1024;

    private const int MAX_EDGE = 1600;

    private string $mimeType;

    private string $path;

    private ImageInterface $image;

    public function __construct(
        private readonly User $user,
        private readonly BlogPost $blogPost,
        private readonly string $content,
        private readonly ?string $sha256 = null,
    ) {}

    public function execute(): string
    {
        $this->validate();
        $this->store();
        $this->flushMarketingCache();
        $this->log();

        return $this->path;
    }

    private function validate(): void
    {
        if (! $this->user->isInstanceAdministrator()) {
            throw new ModelNotFoundException('Blog post not found');
        }

        $bytes = base64_decode($this->content, true);

        if ($bytes === false || $bytes === '') {
            throw new InvalidArgumentException('The image must be base64 encoded');
        }

        if ($this->sha256 !== null && ! hash_equals(strtolower($this->sha256), hash('sha256', $bytes))) {
            throw new InvalidArgumentException(
                'The image does not match the checksum given for it, so it arrived altered or incomplete. '
                .'Send the whole base64 string again in one piece.'
            );
        }

        if (strlen($bytes) > self::MAX_SIZE_IN_BYTES) {
            throw new InvalidArgumentException('The image must not be larger than 5 MB');
        }

        // The type is read out of the bytes rather than taken from the caller,
        // so an argument claiming to be a png cannot smuggle something else on.
        $size = @getimagesizefromstring($bytes);

        if ($size === false) {
            throw new InvalidArgumentException('The image could not be read');
        }

        $mimeType = image_type_to_mime_type($size[2]);

        if (! array_key_exists($mimeType, self::ALLOWED_MIME_TYPES)) {
            throw new InvalidArgumentException('The image must be a jpeg, png or webp');
        }

        // getimagesizefromstring only reads the header, so a picture whose data is
        // cut short passes every check above. Decoding it here is what proves the
        // rest of it arrived, and it is the copy store() goes on to write.
        try {
            $this->image = new ImageManager(new Driver)->decodeBinary($bytes);
        } catch (DecoderException) {
            throw new InvalidArgumentException(
                'The image header was read but the rest of it could not be, so it arrived incomplete or '
                .'corrupted. Send the whole base64 string again in one piece.'
            );
        }

        $this->mimeType = $mimeType;
    }

    private function store(): void
    {
        $this->path = 'blog/'.$this->blogPost->id.'/body/'
            .Str::uuid()->toString().'.'.self::ALLOWED_MIME_TYPES[$this->mimeType];

        // Scaled down and never up, so a small picture keeps its own size.
        $this->disk()->put(
            $this->path,
            (string) $this->image->scaleDown(self::MAX_EDGE, self::MAX_EDGE)->encodeUsingMediaType($this->mimeType),
        );
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
            action: UserActionEnum::BlogPostImageUpload,
            parameters: ['reference' => $this->blogPost->reference()],
        )->onQueue('low');
    }
}
