<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\UserActionEnum;
use App\Jobs\LogUserAction;
use App\Models\SiteOption;
use App\Models\User;
use App\Services\CloudflareCache;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Update the instance wide site options. Only an instance administrator may do
 * this.
 */
class UpdateSiteOptions
{
    private SiteOption $siteOption;

    /**
     * @param  array<string, array<string, string|null>>  $bannerContent  the sentence and link label, keyed by locale
     */
    public function __construct(
        private readonly User $user,
        private readonly bool $bannerEnabled,
        private readonly ?string $bannerVersion,
        private readonly ?string $bannerUrl,
        private readonly array $bannerContent,
    ) {}

    public function execute(): SiteOption
    {
        $this->validate();
        $this->update();
        $this->flushMarketingCache();
        $this->log();

        return $this->siteOption;
    }

    private function validate(): void
    {
        if (! $this->user->isInstanceAdministrator()) {
            throw new ModelNotFoundException('Site options not found');
        }
    }

    private function update(): void
    {
        $this->siteOption = SiteOption::query()->firstOrNew();

        $this->siteOption->banner_enabled = $this->bannerEnabled;
        $this->siteOption->banner_version = $this->bannerVersion;
        $this->siteOption->banner_url = $this->bannerUrl;
        $this->siteOption->banner_content = $this->bannerContent;
        $this->siteOption->save();
    }

    private function flushMarketingCache(): void
    {
        CloudflareCache::purgeEverything();
    }

    private function log(): void
    {
        LogUserAction::dispatch(
            user: $this->user,
            action: UserActionEnum::SiteOptionsUpdate,
        )->onQueue('low');
    }
}
