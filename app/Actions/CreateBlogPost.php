<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\BlogPostStatus;
use App\Enums\BlogShelf;
use App\Enums\BlogTranslationState;
use App\Enums\UserActionEnum;
use App\Jobs\LogUserAction;
use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;

/**
 * Start a blog entry, as a draft, with its English text.
 *
 * English is the source every other language is written from, so an entry is
 * never created without it. The catalogue reference is assigned here and never
 * again: it is one past the highest ever issued, so a deleted entry does not
 * hand its number to the next one.
 *
 * Only an instance administrator may write for the blog. The panel is gated on
 * the instance flag, and the action checks it too so the rule lives in one place.
 */
class CreateBlogPost
{
    private BlogPost $blogPost;

    public function __construct(
        private readonly User $user,
        private readonly string $title,
        private readonly string $excerpt,
        private readonly string $body,
        private readonly BlogShelf $shelf,
        private readonly ?string $slug = null,
    ) {}

    public function execute(): BlogPost
    {
        $this->validate();
        $this->create();
        $this->addSourceTranslation();
        $this->log();

        return $this->blogPost->refresh();
    }

    private function validate(): void
    {
        if (! $this->user->isInstanceAdministrator()) {
            throw new ModelNotFoundException('Blog post not found');
        }
    }

    private function create(): void
    {
        $this->blogPost = BlogPost::query()->create([
            'reference' => (int) BlogPost::query()->max('reference') + 1,
            'shelf' => $this->shelf,
            'status' => BlogPostStatus::Draft,
            'published_at' => null,
            'is_featured' => false,
            'robots' => 'index,follow',
            'author_id' => $this->user->id,
            'author_name' => $this->user->getFullName(),
        ]);
    }

    private function addSourceTranslation(): void
    {
        $this->blogPost->translations()->create([
            'locale' => config('docs.default_locale'),
            'slug' => $this->slug !== null && $this->slug !== '' ? Str::slug($this->slug) : Str::slug($this->title),
            'title' => $this->title,
            'excerpt' => $this->excerpt,
            'body' => $this->body,
            'state' => BlogTranslationState::Source,
        ]);
    }

    private function log(): void
    {
        LogUserAction::dispatch(
            user: $this->user,
            action: UserActionEnum::BlogPostCreation,
            parameters: ['reference' => $this->blogPost->reference()],
        )->onQueue('low');
    }
}
