<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

/**
 * Remove an avatar original and every resized version of it from the disk.
 * Replacing an avatar and deleting one both need this, so it lives on its own.
 */
class DestroyUserAvatarFiles
{
    public function __construct(
        private readonly string $path,
    ) {}

    public function execute(): void
    {
        $this->disk()->delete($this->path);

        foreach (User::avatarPixelSizes() as $pixels) {
            $this->disk()->delete(User::avatarVariantPathFor($this->path, $pixels));
        }
    }

    private function disk(): Filesystem
    {
        return Storage::disk((string) config('filesystems.default'));
    }
}
