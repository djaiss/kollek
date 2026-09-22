<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\EmailSent;
use App\Models\User;
use Stevebauman\Purify\Facades\Purify;

/**
 * Create an Email Sent object.
 * This lets the user know exactly which emails have been sent to him and the
 * content of the emails.
 * Links are purged in each email, since they could contain links that
 * are not valid anymore.
 */
class CreateEmailSent
{
    private EmailSent $emailSent;

    private string $updatedBody = '';

    public function __construct(
        private readonly User $user,
        private readonly ?string $uuid,
        private readonly string $emailType,
        private readonly string $emailAddress,
        private readonly string $subject,
        private readonly string $body,
    ) {}

    public function execute(): EmailSent
    {
        $this->sanitize();
        $this->create();

        return $this->emailSent;
    }

    private function sanitize(): void
    {
        $config = ['HTML.ForbiddenElements' => 'a'];
        $this->updatedBody = Purify::config($config)->clean($this->body);
    }

    private function create(): void
    {
        $this->emailSent = EmailSent::query()->create([
            'user_id' => $this->user->id,
            'uuid' => $this->uuid,
            'email_type' => $this->emailType,
            'email_address' => $this->emailAddress,
            'subject' => $this->subject,
            'body' => $this->updatedBody,
            'sent_at' => now(),
        ]);
    }
}
