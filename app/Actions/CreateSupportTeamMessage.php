<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\EmailType;
use App\Enums\SupportTicketStatus;
use App\Enums\UserActionEnum;
use App\Helpers\TextSanitizer;
use App\Jobs\LogUserAction;
use App\Jobs\SendEmail;
use App\Mail\SupportTeamReply;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Reply to a support conversation as the instance team. Only an instance
 * administrator may do this: the reply is flagged as coming from the team, moves
 * the conversation to answered, and emails the person who opened it.
 */
class CreateSupportTeamMessage
{
    private SupportMessage $message;

    public function __construct(
        private readonly User $user,
        private readonly SupportTicket $ticket,
        private string $body,
    ) {}

    public function execute(): SupportMessage
    {
        $this->validate();
        $this->sanitize();
        $this->create();
        $this->markAnswered();
        $this->notify();
        $this->log();

        return $this->message;
    }

    private function validate(): void
    {
        if (! $this->user->isInstanceAdministrator()) {
            throw new ModelNotFoundException('Support conversation not found');
        }
    }

    private function sanitize(): void
    {
        $this->body = TextSanitizer::plainText($this->body);
    }

    private function create(): void
    {
        $this->message = SupportMessage::query()->create([
            'support_ticket_id' => $this->ticket->id,
            'user_id' => $this->user->id,
            'body' => $this->body,
            'is_from_team' => true,
        ]);
    }

    private function markAnswered(): void
    {
        $this->ticket->status = SupportTicketStatus::Answered;
        $this->ticket->closed_by = null;
        $this->ticket->closed_at = null;
        $this->ticket->save();
    }

    private function notify(): void
    {
        SendEmail::dispatch(
            mailable: new SupportTeamReply(
                ticket: $this->ticket,
                reply: $this->body,
            ),
            user: $this->ticket->user,
            emailType: EmailType::SupportTeamReply,
            // Low: a support reply is welcome but nobody is blocked on it, unlike
            // the sign in and security emails that make up the rest of high.
        )->onQueue('low');
    }

    private function log(): void
    {
        LogUserAction::dispatch(
            user: $this->user,
            action: UserActionEnum::SupportMessageCreation,
        )->onQueue('low');
    }
}
