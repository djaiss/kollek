<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BlogPostStatus;
use App\Enums\BlogShelf;
use Carbon\Carbon;
use Database\Factories\BlogPostFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class BlogPost
 *
 * One entry in the public blog, independent of the language it is read in. What
 * it says lives in its translations; English is the source and always exists.
 *
 * The reference is the entry's permanent name. It is assigned once, never
 * reused and never renumbered, so an entry can be corrected, retitled or moved
 * to another shelf without breaking the number anybody has cited.
 *
 * @property int $id
 * @property int $reference
 * @property BlogShelf $shelf
 * @property BlogPostStatus $status
 * @property Carbon|null $published_at
 * @property bool $is_featured
 * @property string $robots
 * @property int|null $author_id
 * @property string $author_name
 * @property Carbon $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, BlogPostTranslation> $translations
 * @property-read Collection<int, BlogPostRedirect> $redirects
 * @property-read Collection<int, BlogPostTag> $tags
 * @property-read User|null $author
 */
class BlogPost extends Model
{
    /** @use HasFactory<BlogPostFactory> */
    use HasFactory;

    protected $table = 'blog_posts';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'reference',
        'shelf',
        'status',
        'published_at',
        'is_featured',
        'robots',
        'author_id',
        'author_name',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'reference' => 'integer',
            'shelf' => BlogShelf::class,
            'status' => BlogPostStatus::class,
            'published_at' => 'datetime',
            'is_featured' => 'boolean',
        ];
    }

    /**
     * Get every language this entry has been written in.
     *
     * @return HasMany<BlogPostTranslation, $this>
     */
    public function translations(): HasMany
    {
        return $this->hasMany(BlogPostTranslation::class);
    }

    /**
     * Get the slugs this entry used to answer on.
     *
     * @return HasMany<BlogPostRedirect, $this>
     */
    public function redirects(): HasMany
    {
        return $this->hasMany(BlogPostRedirect::class);
    }

    /**
     * Get what this entry is filed under.
     *
     * @return HasMany<BlogPostTag, $this>
     */
    public function tags(): HasMany
    {
        return $this->hasMany(BlogPostTag::class);
    }

    /**
     * Get the user who wrote it, if they still have an account.
     *
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * The entries the public index and the feed list, newest first.
     *
     * An archived entry is deliberately absent: its URL keeps answering so old
     * links do not break, but it is no longer part of the catalogue.
     *
     * @param  Builder<BlogPost>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('status', BlogPostStatus::Published)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderByDesc('published_at');
    }

    /**
     * The entries that answer on a URL at all, which includes the archived ones.
     *
     * @param  Builder<BlogPost>  $query
     */
    public function scopeReadable(Builder $query): void
    {
        $query->whereIn('status', [BlogPostStatus::Published, BlogPostStatus::Archived])
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Whether this entry answers on a URL at all: the same rule scopeReadable
     * applies in SQL, asked of one entry already in hand.
     */
    public function isReadable(): bool
    {
        return $this->status->isReadable()
            && $this->published_at !== null
            && $this->published_at->lessThanOrEqualTo(now());
    }

    /**
     * The catalogue number as it is printed and cited: KLK-0031.
     */
    public function reference(): string
    {
        return sprintf('%s-%04d', config('marketing.blog.reference_prefix'), $this->reference);
    }

    /**
     * The English translation, which is the source every other language is
     * written from and the fallback for every locale that has none of its own.
     */
    public function source(): ?BlogPostTranslation
    {
        return $this->translations->firstWhere('locale', config('docs.default_locale'));
    }

    /**
     * The translation a reader in this locale should be served: their own
     * language when it is live, and the English source otherwise.
     */
    public function translation(string $locale): ?BlogPostTranslation
    {
        $translation = $this->translations->firstWhere('locale', $locale);

        if ($translation !== null && $translation->isPublic()) {
            return $translation;
        }

        return $this->source();
    }

    /**
     * The translation for this locale whether or not it is live, for the
     * instance administration, which has to show what is not yet published.
     */
    public function translationFor(string $locale): ?BlogPostTranslation
    {
        return $this->translations->firstWhere('locale', $locale);
    }

    /**
     * The locales a reader can actually read this entry in.
     *
     * @return array<int, string>
     */
    public function liveLocales(): array
    {
        return $this->translations
            ->filter(fn (BlogPostTranslation $translation): bool => $translation->isPublic())
            ->pluck('locale')
            ->values()
            ->all();
    }
}
