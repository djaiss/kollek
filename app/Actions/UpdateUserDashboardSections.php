<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\DashboardSectionEnum;
use App\Models\User;
use Illuminate\Validation\ValidationException;

/**
 * Remember which blocks of the dashboard a user has hidden. The preference
 * belongs to the user rather than to the account.
 */
class UpdateUserDashboardSections
{
    /**
     * @param  list<string>  $hidden
     */
    public function __construct(
        private readonly User $user,
        private array $hidden,
    ) {}

    public function execute(): User
    {
        $this->validate();

        return $this->update();
    }

    private function validate(): void
    {
        foreach ($this->hidden as $section) {
            if (DashboardSectionEnum::tryFrom($section) === null) {
                throw ValidationException::withMessages(['sections' => 'Invalid section']);
            }
        }
    }

    private function update(): User
    {
        $this->user->hidden_dashboard_sections = array_values(array_unique($this->hidden));
        $this->user->save();

        return $this->user;
    }
}
