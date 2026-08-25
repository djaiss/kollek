<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\BlogPostRedirectFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class BlogPostRedirect
 *
 * A slug a published entry used to answer on, kept so that renaming a live post
 * does not break every link anyone has made to it. The public site answers a
 * request for one of these with a permanent redirect to the current slug.
 *
 * @property int $id
 * @property int $blog_post_id
 * @property string $locale
 * @property string $slug
 * @property Carbon $created_at
 * @property Carbon|null $updated_at
 * @property-read BlogPost $blogPost
 */
class BlogPostRedirect extends Model
{
    /** @use HasFactory<BlogPostRedirectFactory> */
    use HasFactory;

    protected $table = 'blog_post_redirects';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'blog_post_id',
        'locale',
        'slug',
    ];

    /**
     * Get the entry the old slug now points at.
     *
     * @return BelongsTo<BlogPost, $this>
     */
    public function blogPost(): BelongsTo
    {
        return $this->belongsTo(BlogPost::class);
    }
}
