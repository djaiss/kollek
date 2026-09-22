<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\UserActionEnum;
use App\Jobs\LogUserAction;
use App\Models\User;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use InvalidArgumentException;

/**
 * Set the avatar of a user. The original is kept, and a square version is
 * written next to it for every size the app displays an avatar at, along with a
 * version at twice that size for dense screens. A user only ever has one
 * avatar, so an earlier one is removed once the new one is in place.
 */
class UpdateUserAvatar
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

    private ?string $previousPath;

    private string $path;

    public function __construct(
        private readonly User $user,
        private readonly UploadedFile $file,
    ) {}

    public function execute(): User
    {
        $this->validate();
        $this->store();
        $this->resize();
        $this->save();
        $this->removePrevious();
        $this->log();

        return $this->user;
    }

    private function validate(): void
    {
        if (! in_array($this->file->getMimeType(), self::ALLOWED_MIME_TYPES, true)) {
            throw new InvalidArgumentException('The file must be a jpeg, png or webp image');
        }

        if ($this->file->getSize() > self::MAX_SIZE_IN_BYTES) {
            throw new InvalidArgumentException('The file must not be larger than 5 MB');
        }
    }

    private function store(): void
    {
        $this->previousPath = $this->user->avatar_path;

        $name = Str::uuid()->toString().'.'.$this->file->extension();

        $this->path = (string) $this->disk()->putFileAs('avatars/'.$this->user->id, $this->file, $name);
    }

    private function resize(): void
    {
        $original = $this->disk()->get($this->path);

        foreach (User::avatarPixelSizes() as $pixels) {
            $image = new ImageManager(new Driver)->decodeBinary($original);

            $image->cover($pixels, $pixels);

            $this->disk()->put(
                User::avatarVariantPathFor($this->path, $pixels),
                (string) $image->encodeUsingMediaType((string) $this->file->getMimeType()),
            );
        }
    }

    private function save(): void
    {
        $this->user->avatar_path = $this->path;
        $this->user->save();
    }

    private function removePrevious(): void
    {
        if ($this->previousPath === null) {
            return;
        }

        new DestroyUserAvatarFiles(path: $this->previousPath)->execute();
    }

    private function disk(): Filesystem
    {
        return Storage::disk((string) config('filesystems.default'));
    }

    private function log(): void
    {
        LogUserAction::dispatch(
            user: $this->user,
            action: UserActionEnum::AvatarUpdate,
        )->onQueue('low');
    }
}
