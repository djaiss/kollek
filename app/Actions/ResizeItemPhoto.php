<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\ItemPhoto;
use App\Models\User;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use InvalidArgumentException;

/**
 * Resize a stored item photo to fit within a requested box and store that
 * version next to the original. The ratio is preserved, so the result fits
 * inside the box rather than filling it, and a photo is never enlarged beyond
 * its own size. Only owners and editors of the item's account may do so.
 */
class ResizeItemPhoto
{
    private string $path;

    public function __construct(
        private readonly User $user,
        private readonly ItemPhoto $itemPhoto,
        private readonly int $width,
        private readonly int $height,
    ) {}

    public function execute(): string
    {
        $this->validate();
        $this->resize();

        return $this->path;
    }

    private function validate(): void
    {
        if (! $this->itemPhoto->item->catalog->account->allowsManagementBy($this->user)) {
            throw new ModelNotFoundException('Account not found');
        }

        if ($this->width < 1 || $this->height < 1) {
            throw new InvalidArgumentException('The width and height must be positive');
        }
    }

    private function resize(): void
    {
        $image = new ImageManager(new Driver)
            ->decodeBinary($this->disk()->get($this->itemPhoto->path));

        // scaleDown fits the image inside the box while keeping its ratio, and
        // leaves a smaller image untouched rather than enlarging it.
        $image->scaleDown($this->width, $this->height);

        $this->path = $this->variantPath();

        $this->disk()->put(
            $this->path,
            (string) $image->encodeUsingMediaType($this->itemPhoto->mime_type),
        );
    }

    private function variantPath(): string
    {
        $extension = pathinfo($this->itemPhoto->path, PATHINFO_EXTENSION);
        $stem = substr($this->itemPhoto->path, 0, -(strlen($extension) + 1));

        return $stem.'_'.$this->width.'x'.$this->height.'.'.$extension;
    }

    private function disk(): Filesystem
    {
        return Storage::disk((string) config('filesystems.default'));
    }
}
