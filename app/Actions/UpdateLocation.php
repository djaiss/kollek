<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\UserActionEnum;
use App\Helpers\TextSanitizer;
use App\Jobs\LogUserAction;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

/**
 * Update a location's name and parent. Only owners and editors of its
 * account may do so.
 */
class UpdateLocation
{
    public function __construct(
        private readonly User $user,
        private readonly Location $location,
        private string $name,
        private ?int $parentId = null,
        private ?string $emoji = null,
    ) {}

    public function execute(): Location
    {
        $this->validate();
        $this->sanitize();
        $this->update();
        $this->log();

        return $this->location;
    }

    private function validate(): void
    {
        if (! $this->location->account->allowsManagementBy($this->user)) {
            throw new ModelNotFoundException('Account not found');
        }

        if ($this->parentId === null) {
            return;
        }

        if ($this->parentId === $this->location->id) {
            throw ValidationException::withMessages(['parent_id' => 'A location cannot be its own parent']);
        }

        $parent = $this->location->account->locations()->find($this->parentId);

        if ($parent === null) {
            throw ValidationException::withMessages(['parent_id' => 'Invalid parent location']);
        }

        if ($this->isDescendantOf($parent)) {
            throw ValidationException::withMessages(['parent_id' => 'A location cannot be nested under one of its own descendants']);
        }
    }

    private function isDescendantOf(Location $candidate): bool
    {
        $current = $candidate;

        while ($current !== null) {
            if ($current->id === $this->location->id) {
                return true;
            }

            $current = $current->parent;
        }

        return false;
    }

    private function sanitize(): void
    {
        $this->name = TextSanitizer::plainText($this->name);
        $this->emoji = TextSanitizer::nullablePlainText($this->emoji);
    }

    private function update(): void
    {
        $this->location->name = $this->name;
        $this->location->parent_id = $this->parentId;
        $this->location->emoji = $this->emoji;
        $this->location->updated_by_id = $this->user->id;
        $this->location->updated_by_name = $this->user->getFullName();
        $this->location->save();
    }

    private function log(): void
    {
        LogUserAction::dispatch(
            user: $this->user,
            action: UserActionEnum::LocationUpdate,
            parameters: ['name' => $this->location->name],
        )->onQueue('low');
    }
}
