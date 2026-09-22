<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\TestimonialStatus;
use App\Enums\UserActionEnum;
use App\Jobs\LogUserAction;
use App\Models\Testimonial;
use App\Models\User;
use App\Services\CloudflareCache;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Withdraw a member's own testimonial, deleting it outright. A member owns their
 * testimonial and can take it back at any time, whether it is still in review or
 * already live on the marketing site. Removing a published one has to drop it
 * from the cached public pages, so Cloudflare is purged in that case.
 */
class WithdrawTestimonial
{
    public function __construct(
        private readonly User $user,
        private readonly Testimonial $testimonial,
    ) {}

    public function execute(): void
    {
        $this->validate();

        $wasPublished = $this->testimonial->status === TestimonialStatus::Published;

        $this->log();
        $this->testimonial->delete();

        if ($wasPublished) {
            CloudflareCache::purgeEverything();
        }
    }

    private function validate(): void
    {
        if ($this->testimonial->user_id !== $this->user->id) {
            throw new ModelNotFoundException('Testimonial not found');
        }
    }

    private function log(): void
    {
        LogUserAction::dispatch(
            user: $this->user,
            action: UserActionEnum::TestimonialWithdrawn,
        )->onQueue('low');
    }
}
