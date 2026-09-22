<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\DatePrecision;
use App\Enums\ItemActionEnum;
use App\Enums\ProvenanceEventType;
use App\Enums\UserActionEnum;
use App\Helpers\ImpreciseDate;
use App\Jobs\LogItemAction;
use App\Jobs\LogUserAction;
use App\Models\ProvenanceEvent;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Update a provenance event. Only owners and editors of its account may do so.
 */
class UpdateProvenanceEvent
{
    /**
     * The values that moved, captured before the event is written so the
     * activity tab can show what they moved from.
     *
     * @var list<array{label: string, from: string|null, to: string|null}>
     */
    private array $changes = [];

    public function __construct(
        private readonly User $user,
        private readonly ProvenanceEvent $event,
        private readonly ProvenanceEventType $type,
        private readonly string $title,
        private readonly DatePrecision $occurredAtPrecision = DatePrecision::Exact,
        private readonly ?string $occurredAt = null,
        private readonly ?string $description = null,
        private readonly ?string $location = null,
        private readonly ?string $fromParty = null,
        private readonly ?string $toParty = null,
        private readonly ?string $referenceNumber = null,
        private readonly ?string $sourceUrl = null,
        private readonly bool $isVerified = false,
        private readonly ?string $verificationNote = null,
        private readonly ?Transaction $transaction = null,
    ) {}

    public function execute(): ProvenanceEvent
    {
        $this->validate();
        $this->captureChanges();
        $this->update();
        $this->log();

        return $this->event;
    }

    private function validate(): void
    {
        $account = $this->event->copy->item->catalog->account;

        if (! $account->allowsManagementBy($this->user)) {
            throw new ModelNotFoundException('Account not found');
        }

        if (! $this->transaction instanceof Transaction) {
            return;
        }

        if ($this->transaction->copy_id !== $this->event->copy_id) {
            throw new ModelNotFoundException('Transaction not found');
        }

        // Relinking to the transaction this event already carries is not a
        // clash with itself, so only another event's claim counts.
        $claimed = $this->transaction->provenanceEvent()->whereKeyNot($this->event->id)->exists();

        if ($claimed) {
            throw new ModelNotFoundException('Transaction not found');
        }
    }

    private function captureChanges(): void
    {
        $this->changes = array_values(array_filter([
            $this->change('Type', $this->event->type->label(), $this->type->label()),
            $this->change('Title', $this->event->title, $this->title),
            $this->change('Description', $this->event->description, $this->description),
            $this->change('Occurred', $this->event->formattedDate(), $this->formattedNewDate()),
            $this->change('Location', $this->event->location, $this->location),
            $this->change('From', $this->event->from_party, $this->fromParty),
            $this->change('To', $this->event->to_party, $this->toParty),
            $this->change('Reference', $this->event->reference_number, $this->referenceNumber),
            $this->change('Verified', $this->event->is_verified ? __('Yes') : __('No'), $this->isVerified ? __('Yes') : __('No')),
        ]));
    }

    private function formattedNewDate(): string
    {
        return ImpreciseDate::format(
            $this->occurredAtPrecision->carriesDate() && $this->occurredAt !== null
                ? Carbon::parse($this->occurredAt)
                : null,
            $this->occurredAtPrecision,
        );
    }

    /**
     * @return array{label: string, from: string|null, to: string|null}|null
     */
    private function change(string $label, ?string $from, ?string $to): ?array
    {
        if ($from === $to) {
            return null;
        }

        return ['label' => $label, 'from' => $from, 'to' => $to];
    }

    private function update(): void
    {
        $this->event->fill([
            'transaction_id' => $this->transaction?->id,
            'type' => $this->type,
            'title' => $this->title,
            'description' => $this->description,
            'occurred_at' => $this->occurredAtPrecision->carriesDate() ? $this->occurredAt : null,
            'occurred_at_precision' => $this->occurredAtPrecision,
            'location' => $this->location,
            'from_party' => $this->fromParty,
            'to_party' => $this->toParty,
            'reference_number' => $this->referenceNumber,
            'source_url' => $this->sourceUrl,
            'is_verified' => $this->isVerified,
            'verification_note' => $this->isVerified ? $this->verificationNote : null,
        ]);
        $this->event->updated_by_id = $this->user->id;
        $this->event->updated_by_name = $this->user->getFullName();
        $this->event->save();
    }

    private function log(): void
    {
        $item = $this->event->copy->item;

        LogUserAction::dispatch(
            user: $this->user,
            action: UserActionEnum::ProvenanceEventUpdate,
            parameters: ['name' => $item->name],
        )->onQueue('low');

        LogItemAction::dispatch(
            item: $item,
            user: $this->user,
            action: ItemActionEnum::ProvenanceEventUpdate,
            parameters: $this->changes === [] ? null : ['changes' => $this->changes],
        )->onQueue('low');
    }
}
