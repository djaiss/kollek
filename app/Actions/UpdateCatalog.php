<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PermissionEnum;
use App\Enums\UserActionEnum;
use App\Enums\VisibilityEnum;
use App\Helpers\TextSanitizer;
use App\Jobs\LogUserAction;
use App\Models\Catalog;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

/**
 * Update a collection. Only owners and editors of its account may do so.
 */
class UpdateCatalog
{
    /**
     * @param  array<string, mixed>|null  $settings
     * @param  array<int, int>|null  $catalogTypeIds  Null leaves the linked types alone.
     */
    public function __construct(
        private readonly User $user,
        private readonly Catalog $catalog,
        private string $name,
        private ?string $description = null,
        private ?string $emoji = null,
        private string $visibility = VisibilityEnum::Private->value,
        private ?string $currency = null,
        private ?array $settings = null,
        private ?array $catalogTypeIds = null,
    ) {}

    public function execute(): Catalog
    {
        $this->validate();
        $this->sanitize();
        $this->update();
        $this->syncCatalogTypes();
        $this->log();

        return $this->catalog;
    }

    private function validate(): void
    {
        if (! $this->userCanManageCatalogs()) {
            throw new ModelNotFoundException('Account not found');
        }

        if (VisibilityEnum::tryFrom($this->visibility) === null) {
            throw ValidationException::withMessages(['visibility' => 'Invalid visibility']);
        }
    }

    private function userCanManageCatalogs(): bool
    {
        return in_array(
            $this->catalog->account->roleFor($this->user),
            [PermissionEnum::Owner->value, PermissionEnum::Editor->value],
            true,
        );
    }

    private function sanitize(): void
    {
        $this->name = TextSanitizer::plainText($this->name);
        $this->description = TextSanitizer::nullablePlainText($this->description);
        $this->emoji = TextSanitizer::nullablePlainText($this->emoji);
    }

    private function update(): void
    {
        $this->catalog->name = $this->name;
        $this->catalog->description = $this->description;
        $this->catalog->emoji = $this->emoji;
        $this->catalog->visibility = VisibilityEnum::from($this->visibility);
        $this->catalog->currency = $this->currency;
        $this->catalog->settings = $this->settings;
        $this->catalog->updated_by_id = $this->user->id;
        $this->catalog->updated_by_name = $this->user->getFullName();
        $this->catalog->save();
    }

    private function syncCatalogTypes(): void
    {
        if ($this->catalogTypeIds === null) {
            return;
        }

        $ids = $this->catalog->account->catalogTypes()
            ->whereIn('id', $this->catalogTypeIds)
            ->pluck('id')
            ->all();

        $this->catalog->catalogTypes()->sync($ids);
    }

    private function log(): void
    {
        LogUserAction::dispatch(
            user: $this->user,
            action: UserActionEnum::CatalogUpdate,
            parameters: ['name' => $this->catalog->name],
        )->onQueue('low');
    }
}
