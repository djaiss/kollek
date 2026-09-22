<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\UserActionEnum;
use App\Jobs\LogUserAction;
use App\Models\CustomFieldGroup;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

/**
 * Delete a group of custom fields. The fields it held are not deleted with it:
 * they drop back to ungrouped, so no value recorded against them is lost. Only
 * owners and editors of the type's account may do so.
 */
class DestroyCustomFieldGroup
{
    public function __construct(
        private readonly User $user,
        private readonly CustomFieldGroup $customFieldGroup,
    ) {}

    public function execute(): void
    {
        $this->validate();
        $this->log();
        $this->destroy();
    }

    private function destroy(): void
    {
        DB::transaction(function (): void {
            $this->customFieldGroup->customFields()->update(['group_id' => null]);
            $this->customFieldGroup->delete();
        });
    }

    private function validate(): void
    {
        if (! $this->customFieldGroup->catalogType->account->allowsManagementBy($this->user)) {
            throw new ModelNotFoundException('Account not found');
        }
    }

    private function log(): void
    {
        LogUserAction::dispatch(
            user: $this->user,
            action: UserActionEnum::CustomFieldGroupDeletion,
            parameters: ['name' => $this->customFieldGroup->name],
        )->onQueue('low');
    }
}
