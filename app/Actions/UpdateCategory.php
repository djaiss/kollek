<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\UserActionEnum;
use App\Helpers\TextSanitizer;
use App\Jobs\LogUserAction;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

/**
 * Update a category's name and parent. Only owners and editors of its
 * collection's account may do so.
 */
class UpdateCategory
{
    public function __construct(
        private readonly User $user,
        private readonly Category $category,
        private string $name,
        private ?int $parentId = null,
        private ?string $description = null,
    ) {}

    public function execute(): Category
    {
        $this->validate();
        $this->sanitize();
        $this->update();
        $this->log();

        return $this->category;
    }

    private function validate(): void
    {
        if (! $this->category->catalog->account->allowsManagementBy($this->user)) {
            throw new ModelNotFoundException('Category not found');
        }

        if ($this->parentId === null) {
            return;
        }

        if ($this->parentId === $this->category->id) {
            throw ValidationException::withMessages(['parent_id' => 'A category cannot be its own parent']);
        }

        $parent = $this->category->catalog->categories()->find($this->parentId);

        if ($parent === null) {
            throw ValidationException::withMessages(['parent_id' => 'Invalid parent category']);
        }

        if ($this->isDescendantOf($parent)) {
            throw ValidationException::withMessages(['parent_id' => 'A category cannot be nested under one of its own descendants']);
        }
    }

    private function isDescendantOf(Category $candidate): bool
    {
        $current = $candidate;

        while ($current !== null) {
            if ($current->id === $this->category->id) {
                return true;
            }

            $current = $current->parent;
        }

        return false;
    }

    private function sanitize(): void
    {
        $this->name = TextSanitizer::plainText($this->name);

        if ($this->description !== null) {
            $this->description = TextSanitizer::plainText($this->description);
        }
    }

    private function update(): void
    {
        $this->category->name = $this->name;
        $this->category->description = $this->description;
        $this->category->parent_id = $this->parentId;
        $this->category->updated_by_id = $this->user->id;
        $this->category->updated_by_name = $this->user->getFullName();
        $this->category->save();
    }

    private function log(): void
    {
        LogUserAction::dispatch(
            user: $this->user,
            action: UserActionEnum::CategoryUpdate,
            parameters: ['name' => $this->category->name],
        )->onQueue('low');
    }
}
