<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ItemActionEnum;
use App\Enums\LoanDirection;
use App\Enums\LoanStatus;
use App\Enums\UserActionEnum;
use App\Jobs\LogItemAction;
use App\Jobs\LogUserAction;
use App\Models\Copy;
use App\Models\Loan;
use App\Models\User;
use App\Traits\GuardsLoanConditions;
use App\Traits\GuardsOverlappingLoans;
use App\Traits\SyncsLoanState;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Record a loan against a copy: a piece lent out, or a piece borrowed in. Only
 * owners and editors of the copy's account may do so.
 */
class CreateLoan
{
    use GuardsLoanConditions;
    use GuardsOverlappingLoans;
    use SyncsLoanState;

    private Loan $loan;

    public function __construct(
        private readonly User $user,
        private readonly Copy $copy,
        private readonly LoanDirection $direction,
        private readonly string $party,
        private readonly string $loanedAt,
        private readonly LoanStatus $status = LoanStatus::Active,
        private readonly ?string $purpose = null,
        private readonly ?string $dueAt = null,
        private readonly ?string $returnedAt = null,
        private readonly ?int $itemConditionOutId = null,
        private readonly ?int $itemConditionInId = null,
        private readonly ?int $depositAmount = null,
        private readonly ?string $depositCurrencyCode = null,
        private readonly bool $includeInProvenance = false,
    ) {}

    public function execute(): Loan
    {
        $this->validate();
        $this->create();
        $this->stampAuthor();
        $this->syncCopyStatus($this->copy);
        $this->handleProvenance();
        $this->log();

        return $this->loan;
    }

    private function validate(): void
    {
        $account = $this->copy->item->catalog->account;

        if (! $account->allowsManagementBy($this->user)) {
            throw new ModelNotFoundException('Account not found');
        }

        $this->guardConditionsBelongToAccount($account, $this->itemConditionOutId, $this->itemConditionInId);
        $this->guardAgainstOverlappingLoan($this->copy, $this->direction, $this->status);
    }

    private function create(): void
    {
        $this->loan = Loan::query()->create([
            'copy_id' => $this->copy->id,
            'direction' => $this->direction,
            'status' => $this->status,
            'party' => $this->party,
            'purpose' => $this->purpose,
            'loaned_at' => $this->loanedAt,
            'due_at' => $this->dueAt,
            'returned_at' => $this->returnedAt,
            'item_condition_out_id' => $this->itemConditionOutId,
            'item_condition_in_id' => $this->itemConditionInId,
            'deposit_amount' => $this->depositAmount,
            // The collection's currency is the sensible default when the caller
            // holds a deposit but does not say what it is in.
            'deposit_currency_code' => $this->depositAmount === null
                ? null
                : ($this->depositCurrencyCode ?? $this->copy->item->catalog->currency),
            'include_in_provenance' => $this->includeInProvenance,
        ]);
    }

    private function stampAuthor(): void
    {
        $this->loan->created_by_id = $this->user->id;
        $this->loan->created_by_name = $this->user->getFullName();
        $this->loan->updated_by_id = $this->user->id;
        $this->loan->updated_by_name = $this->user->getFullName();
        $this->loan->save();
    }

    private function handleProvenance(): void
    {
        if (! $this->includeInProvenance) {
            return;
        }

        $this->createLoanProvenanceEvent($this->loan);
        $this->createReturnProvenanceEvent($this->loan);
    }

    private function log(): void
    {
        $item = $this->copy->item;

        LogUserAction::dispatch(
            user: $this->user,
            action: UserActionEnum::LoanCreation,
            parameters: ['name' => $item->name],
        )->onQueue('low');

        LogItemAction::dispatch(
            item: $item,
            user: $this->user,
            action: ItemActionEnum::LoanCreation,
            parameters: ['label' => $this->direction->label()],
        )->onQueue('low');
    }
}
