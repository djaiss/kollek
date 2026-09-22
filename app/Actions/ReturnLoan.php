<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ItemActionEnum;
use App\Enums\LoanStatus;
use App\Enums\UserActionEnum;
use App\Jobs\LogItemAction;
use App\Jobs\LogUserAction;
use App\Models\Loan;
use App\Models\User;
use App\Traits\GuardsLoanConditions;
use App\Traits\SyncsLoanState;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Mark a loan as returned: close it with the date it came back and the condition
 * it came back in, in one step. Only owners and editors of its account may do so.
 */
class ReturnLoan
{
    use GuardsLoanConditions;
    use SyncsLoanState;

    public function __construct(
        private readonly User $user,
        private readonly Loan $loan,
        private readonly string $returnedAt,
        private readonly ?int $itemConditionInId = null,
    ) {}

    public function execute(): Loan
    {
        $this->validate();
        $this->close();
        $this->syncCopyCondition();
        $this->syncCopyStatus($this->loan->copy);
        $this->handleProvenance();
        $this->log();

        return $this->loan;
    }

    private function validate(): void
    {
        $account = $this->loan->copy->item->catalog->account;

        if (! $account->allowsManagementBy($this->user)) {
            throw new ModelNotFoundException('Account not found');
        }

        // A loan that is already closed has nothing to return. It looks not found
        // rather than refused, the same way a cross tenant loan would.
        if (! $this->loan->status->isOpen()) {
            throw new ModelNotFoundException('Loan not found');
        }

        $this->guardConditionsBelongToAccount($account, null, $this->itemConditionInId);
    }

    private function close(): void
    {
        $this->loan->fill([
            'status' => LoanStatus::Returned,
            'returned_at' => $this->returnedAt,
            'item_condition_in_id' => $this->itemConditionInId,
        ]);
        $this->loan->updated_by_id = $this->user->id;
        $this->loan->updated_by_name = $this->user->getFullName();
        $this->loan->save();
    }

    private function syncCopyCondition(): void
    {
        if ($this->itemConditionInId === null) {
            return;
        }

        $copy = $this->loan->copy;
        $copy->item_condition_id = $this->itemConditionInId;
        $copy->save();
    }

    private function handleProvenance(): void
    {
        if (! $this->loan->include_in_provenance) {
            return;
        }

        if ($this->loan->return_provenance_event_id !== null) {
            return;
        }

        $this->createReturnProvenanceEvent($this->loan);
    }

    private function log(): void
    {
        $item = $this->loan->copy->item;

        LogUserAction::dispatch(
            user: $this->user,
            action: UserActionEnum::LoanReturn,
            parameters: ['name' => $item->name],
        )->onQueue('low');

        LogItemAction::dispatch(
            item: $item,
            user: $this->user,
            action: ItemActionEnum::LoanReturn,
            parameters: ['label' => $this->loan->party],
        )->onQueue('low');
    }
}
