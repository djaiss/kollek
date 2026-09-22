<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\SupportCategory;
use App\Enums\SupportTicketCloser;
use App\Enums\SupportTicketStatus;
use App\Enums\UserActionEnum;
use App\Jobs\LogUserAction;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Update a support conversation, such as closing it or changing what it is about.
 * Only the user who owns the conversation may change it.
 */
class UpdateSupportTicket
{
    public function __construct(
        private readonly User $user,
        private readonly SupportTicket $ticket,
        private readonly ?SupportTicketStatus $status = null,
        private readonly ?SupportCategory $category = null,
        private readonly SupportTicketCloser $closedBy = SupportTicketCloser::User,
    ) {}

    public function execute(): SupportTicket
    {
        $this->validate();
        $this->update();
        $this->log();

        return $this->ticket;
    }

    private function validate(): void
    {
        if ($this->ticket->user_id !== $this->user->id) {
            throw new ModelNotFoundException('Support conversation not found');
        }
    }

    private function update(): void
    {
        if ($this->status !== null) {
            $this->ticket->status = $this->status;
            $this->recordClosure();
        }

        if ($this->category !== null) {
            $this->ticket->category = $this->category;
        }

        $this->ticket->save();
    }

    private function recordClosure(): void
    {
        if ($this->status === SupportTicketStatus::Closed) {
            $this->ticket->closed_by = $this->closedBy;
            $this->ticket->closed_at = now();

            return;
        }

        $this->ticket->closed_by = null;
        $this->ticket->closed_at = null;
    }

    private function log(): void
    {
        LogUserAction::dispatch(
            user: $this->user,
            action: UserActionEnum::SupportTicketUpdate,
        )->onQueue('low');
    }
}
