<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BlogTranslationState;
use App\Services\BlogPostRenderer;
use Carbon\Carbon;
use Database\Factories\BlogPostTranslationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class BlogPostTranslation
 *
 * One blog entry as written in one language: its headline, its standfirst, its
 * Markdown body and everything the URL and the tags in the head are built from.
 *
 * The slug is per language, so a French reader gets a French URL rather than an
 * English one behind a French prefix. It is therefore unique per locale rather
 * than globally.
 *
 * @property int $id
 * @property int $blog_post_id
 * @property string $locale
 * @property string $slug
 * @property string $title
 * @property string $excerpt
 * @property string $body
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string|null $focus_keyword
 * @property string|null $og_image_path
 * @property BlogTranslationState $state
 * @property Carbon $created_at
 * @property Carbon|null $updated_at
 * @property-read BlogPost $blogPost
 */
class BlogPostTranslation extends Model
{
    /** @use HasFactory<BlogPostTranslationFactory> */
    use HasFactory;

    protected $table = 'blog_post_translations';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'blog_post_id',
        'locale',
        'slug',
        'title',
        'excerpt',
        'body',
        'meta_title',
        'meta_description',
        'focus_keyword',
        'og_image_path',
        'state',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'state' => BlogTranslationState::class,
        ];
    }

    /**
     * Get the entry this is a language of.
     *
     * @return BelongsTo<BlogPost, $this>
     */
    public function blogPost(): BelongsTo
    {
        return $this->belongsTo(BlogPost::class);
    }

    /**
     * Whether readers are served this language, or whether the locale falls
     * back to the English source instead.
     */
    public function isPublic(): bool
    {
        return $this->state->isPublic();
    }

    /**
     * The body as HTML, with an id on every heading so the contents list has
     * something to anchor to.
     *
     * @return array{html: string, toc: array<int, array{id: string, text: string, level: int}>}
     */
    public function rendered(): array
    {
        return app(BlogPostRenderer::class)->render($this);
    }

    /**
     * The title tag, which the writer may override and which otherwise reads as
     * the headline itself.
     */
    public function metaTitle(): string
    {
        return $this->meta_title ?: $this->title;
    }

    /**
     * The meta description, falling back to the standfirst, which is already
     * written to be the one sentence that sells the entry.
     */
    public function metaDescription(): string
    {
        return $this->meta_description ?: $this->excerpt;
    }
}
