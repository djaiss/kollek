<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\FieldTypeEnum;
use App\Enums\UserActionEnum;
use App\Helpers\TextSanitizer;
use App\Jobs\LogUserAction;
use App\Models\CatalogType;
use App\Models\CustomField;
use App\Models\CustomFieldGroup;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

/**
 * Create a custom field on a type, either inside a group or outside of any.
 * New fields are appended after the existing ones. Only owners and editors of
 * the type's account may do so.
 */
class CreateCustomField
{
    private CustomField $customField;

    /**
     * @param  array<int, mixed>|null  $options
     */
    public function __construct(
        private readonly User $user,
        private readonly CatalogType $catalogType,
        private string $name,
        private string $fieldType = FieldTypeEnum::Text->value,
        private ?array $options = null,
        private readonly ?CustomFieldGroup $group = null,
    ) {}

    public function execute(): CustomField
    {
        $this->validate();
        $this->sanitize();
        $this->create();
        $this->stampAuthor();
        $this->log();

        return $this->customField;
    }

    private function validate(): void
    {
        if (! $this->catalogType->account->allowsManagementBy($this->user)) {
            throw new ModelNotFoundException('Account not found');
        }

        if (FieldTypeEnum::tryFrom($this->fieldType) === null) {
            throw ValidationException::withMessages(['field_type' => 'Invalid field type']);
        }

        if ($this->group instanceof CustomFieldGroup && $this->group->type_id !== $this->catalogType->id) {
            throw ValidationException::withMessages(['group_id' => 'The group belongs to another type']);
        }
    }

    private function sanitize(): void
    {
        $this->name = TextSanitizer::plainText($this->name);
    }

    private function create(): void
    {
        $this->customField = CustomField::query()->create([
            'type_id' => $this->catalogType->id,
            'group_id' => $this->group?->id,
            'name' => $this->name,
            'field_type' => $this->fieldType,
            'options' => $this->options,
            'position' => $this->nextPosition(),
        ]);
    }

    private function nextPosition(): int
    {
        return (int) $this->catalogType->customFields()
            ->where('group_id', $this->group?->id)
            ->max('position') + 1;
    }

    private function stampAuthor(): void
    {
        $this->customField->created_by_id = $this->user->id;
        $this->customField->created_by_name = $this->user->getFullName();
        $this->customField->updated_by_id = $this->user->id;
        $this->customField->updated_by_name = $this->user->getFullName();
        $this->customField->save();
    }

    private function log(): void
    {
        LogUserAction::dispatch(
            user: $this->user,
            action: UserActionEnum::CustomFieldCreation,
            parameters: ['name' => $this->customField->name],
        )->onQueue('low');
    }
}
