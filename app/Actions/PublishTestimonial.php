<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\EmailType;
use App\Enums\TestimonialStatus;
use App\Enums\UserActionEnum;
use App\Jobs\LogUserAction;
use App\Jobs\SendEmail;
use App\Mail\TestimonialPublished as TestimonialPublishedMail;
use App\Models\Testimonial;
use App\Models\User;
use App\Services\CloudflareCache;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Publish a testimonial so it appears on the marketing site, and email the
 * author to thank them. Only an instance administrator may do this.
 */
class PublishTestimonial
{
    public function __construct(
        private readonly User $user,
        private readonly Testimonial $testimonial,
    ) {}

    public function execute(): Testimonial
    {
        $this->validate();
        $this->publish();
        $this->notifyAuthor();
        $this->flushMarketingCache();
        $this->log();

        return $this->testimonial;
    }

    private function validate(): void
    {
        if (! $this->user->isInstanceAdministrator()) {
            throw new ModelNotFoundException('Testimonial not found');
        }
    }

    private function publish(): void
    {
        $this->testimonial->update([
            'status' => TestimonialStatus::Published,
            'published_at' => now(),
        ]);
    }

    private function notifyAuthor(): void
    {
        SendEmail::dispatch(
            new TestimonialPublishedMail($this->testimonial),
            $this->testimonial->user,
            EmailType::TestimonialPublished,
        )->onQueue('low');
    }

    private function flushMarketingCache(): void
    {
        CloudflareCache::purgeEverything();
    }

    private function log(): void
    {
        LogUserAction::dispatch(
            user: $this->user,
            action: UserActionEnum::TestimonialPublished,
            parameters: ['name' => $this->testimonial->name],
        )->onQueue('low');
    }
}
