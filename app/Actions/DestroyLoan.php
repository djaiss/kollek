<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ItemActionEnum;
use App\Enums\UserActionEnum;
use App\Jobs\LogItemAction;
use App\Jobs\LogUserAction;
use App\Models\Loan;
use App\Models\User;
use App\Traits\SyncsLoanState;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Delete a loan recorded against a copy. Only owners and editors of its account
 * may do so.
 */
class DestroyLoan
{
    use SyncsLoanState;

    public function __construct(
        private readonly User $user,
        private readonly Loan $loan,
    ) {}

    public function execute(): void
    {
        $this->validate();
        $this->log();

        $copy = $this->loan->copy;

        $this->loan->loanProvenanceEvent?->delete();
        $this->loan->returnProvenanceEvent?->delete();
        $this->loan->delete();

        $this->syncCopyStatus($copy);
    }

    private function validate(): void
    {
        $account = $this->loan->copy->item->catalog->account;

        if (! $account->allowsManagementBy($this->user)) {
            throw new ModelNotFoundException('Account not found');
        }
    }

    private function log(): void
    {
        $item = $this->loan->copy->item;

        LogUserAction::dispatch(
            user: $this->user,
            action: UserActionEnum::LoanDeletion,
            parameters: ['name' => $item->name],
        )->onQueue('low');

        LogItemAction::dispatch(
            item: $item,
            user: $this->user,
            action: ItemActionEnum::LoanDeletion,
            parameters: ['label' => $this->loan->direction->label()],
        )->onQueue('low');
    }
}
