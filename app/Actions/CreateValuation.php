<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ItemActionEnum;
use App\Enums\UserActionEnum;
use App\Enums\ValuationConfidence;
use App\Enums\ValuationType;
use App\Jobs\LogItemAction;
use App\Jobs\LogUserAction;
use App\Models\Copy;
use App\Models\User;
use App\Models\Valuation;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Record a valuation against a copy. Only owners and editors of the copy's
 * account may do so.
 */
class CreateValuation
{
    private Valuation $valuation;

    public function __construct(
        private readonly User $user,
        private readonly Copy $copy,
        private readonly ValuationType $type,
        private readonly int $amount,
        private readonly string $valuedAt,
        private readonly ?string $currencyCode = null,
        private readonly ValuationConfidence $confidence = ValuationConfidence::Unknown,
        private readonly ?string $valuer = null,
        private readonly ?string $method = null,
        private readonly ?string $sourceUrl = null,
        private readonly ?string $referenceNumber = null,
        private readonly ?string $note = null,
    ) {}

    public function execute(): Valuation
    {
        $this->validate();
        $this->create();
        $this->stampAuthor();
        $this->log();

        return $this->valuation;
    }

    private function validate(): void
    {
        $account = $this->copy->item->catalog->account;

        if (! $account->allowsManagementBy($this->user)) {
            throw new ModelNotFoundException('Account not found');
        }
    }

    private function create(): void
    {
        $this->valuation = Valuation::query()->create([
            'copy_id' => $this->copy->id,
            'type' => $this->type,
            'amount' => $this->amount,
            // The collection's currency is the sensible default when the caller
            // does not say what the amount is in.
            'currency_code' => $this->currencyCode ?? $this->copy->item->catalog->currency,
            'valued_at' => $this->valuedAt,
            'confidence' => $this->confidence,
            'valuer' => $this->valuer,
            'method' => $this->method,
            'source_url' => $this->sourceUrl,
            'reference_number' => $this->referenceNumber,
            'note' => $this->note,
        ]);
    }

    private function stampAuthor(): void
    {
        $this->valuation->created_by_id = $this->user->id;
        $this->valuation->created_by_name = $this->user->getFullName();
        $this->valuation->updated_by_id = $this->user->id;
        $this->valuation->updated_by_name = $this->user->getFullName();
        $this->valuation->save();
    }

    private function log(): void
    {
        $item = $this->copy->item;

        LogUserAction::dispatch(
            user: $this->user,
            action: UserActionEnum::ValuationCreation,
            parameters: ['name' => $item->name],
        )->onQueue('low');

        LogItemAction::dispatch(
            item: $item,
            user: $this->user,
            action: ItemActionEnum::ValuationCreation,
            parameters: ['label' => $this->type->label()],
        )->onQueue('low');
    }
}
