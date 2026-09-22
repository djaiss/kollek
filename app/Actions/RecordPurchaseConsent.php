<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PurchaseConsentChoice;
use App\Enums\UserActionEnum;
use App\Jobs\LogUserAction;
use App\Models\Account;
use App\Models\PurchaseConsent;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

/**
 * Record what somebody confirmed before being sent to a payment processor. Only
 * an owner of the account may do so.
 */
class RecordPurchaseConsent
{
    public function __construct(
        private readonly User $user,
        private readonly Account $account,
        private readonly ?string $ipAddress = null,
    ) {}

    /**
     * @return Collection<int, PurchaseConsent>
     */
    public function execute(): Collection
    {
        $this->validate();

        $consents = $this->record();

        $this->log();

        return $consents;
    }

    private function validate(): void
    {
        if (! $this->account->isOwnedBy($this->user)) {
            throw new ModelNotFoundException('Account not found');
        }
    }

    /**
     * @return Collection<int, PurchaseConsent>
     */
    private function record(): Collection
    {
        $acceptedAt = now();
        $consents = new Collection;

        DB::transaction(function () use ($acceptedAt, $consents): void {
            foreach (PurchaseConsentChoice::cases() as $choice) {
                $consents->push(PurchaseConsent::query()->create([
                    'account_id' => $this->account->id,
                    'user_id' => $this->user->id,
                    'user_name' => $this->user->getFullName(),
                    'choice' => $choice,
                    'ip_address' => $this->ipAddress,
                    'accepted_at' => $acceptedAt,
                ]));
            }
        });

        return $consents;
    }

    private function log(): void
    {
        LogUserAction::dispatch(
            user: $this->user,
            action: UserActionEnum::PurchaseConsentRecorded,
        )->onQueue('low');
    }
}
