<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\TestimonialStatus;
use App\Enums\UserActionEnum;
use App\Jobs\LogUserAction;
use App\Models\Testimonial;
use App\Models\User;

/**
 * Submit, or resubmit, a member's testimonial for the marketing site. Each user
 * has at most one testimonial, so this creates it or overwrites the existing one
 * and puts it back in review. A rejected testimonial revised here goes back to
 * in review, giving the member a way to try again.
 */
class SubmitTestimonial
{
    private Testimonial $testimonial;

    public function __construct(
        private readonly User $user,
        private readonly string $name,
        private readonly ?string $link,
        private readonly string $body,
    ) {}

    public function execute(): Testimonial
    {
        $this->submit();
        $this->log();

        return $this->testimonial;
    }

    private function submit(): void
    {
        $this->testimonial = Testimonial::query()->updateOrCreate(
            ['user_id' => $this->user->id],
            [
                'name' => $this->name,
                'link' => $this->normalizedLink(),
                'body' => $this->body,
                'status' => TestimonialStatus::InReview,
                'submitted_at' => now(),
                // A resubmission starts its review over, so any earlier
                // publication no longer holds.
                'published_at' => null,
            ],
        );
    }

    private function normalizedLink(): ?string
    {
        $link = $this->link === null ? null : trim($this->link);

        return $link === '' ? null : $link;
    }

    private function log(): void
    {
        LogUserAction::dispatch(
            user: $this->user,
            action: UserActionEnum::TestimonialSubmitted,
        )->onQueue('low');
    }
}
