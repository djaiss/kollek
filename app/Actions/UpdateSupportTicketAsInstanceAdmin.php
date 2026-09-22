<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\SupportTicketCloser;
use App\Enums\SupportTicketStatus;
use App\Enums\UserActionEnum;
use App\Jobs\LogUserAction;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Close or reopen a support conversation from the instance panel. Only an
 * instance administrator may do this, and a closure made here is a team closure.
 */
class UpdateSupportTicketAsInstanceAdmin
{
    public function __construct(
        private readonly User $user,
        private readonly SupportTicket $ticket,
        private readonly SupportTicketStatus $status,
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
        if (! $this->user->isInstanceAdministrator()) {
            throw new ModelNotFoundException('Support conversation not found');
        }
    }

    private function update(): void
    {
        $this->ticket->status = $this->status;
        $this->recordClosure();
        $this->ticket->save();
    }

    private function recordClosure(): void
    {
        if ($this->status === SupportTicketStatus::Closed) {
            $this->ticket->closed_by = SupportTicketCloser::Team;
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
