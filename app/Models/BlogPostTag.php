<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\BlogPostTagFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class BlogPostTag
 *
 * What a blog entry is filed under, beyond the one shelf it sits on. These
 * belong to the entry rather than to a shared vocabulary, which is why they are
 * not the Tag model: that one is scoped to an account and describes the objects
 * a member owns.
 *
 * @property int $id
 * @property int $blog_post_id
 * @property string $name
 * @property Carbon $created_at
 * @property Carbon|null $updated_at
 * @property-read BlogPost $blogPost
 */
class BlogPostTag extends Model
{
    /** @use HasFactory<BlogPostTagFactory> */
    use HasFactory;

    protected $table = 'blog_post_tags';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'blog_post_id',
        'name',
    ];

    /**
     * Get the entry filed under this tag.
     *
     * @return BelongsTo<BlogPost, $this>
     */
    public function blogPost(): BelongsTo
    {
        return $this->belongsTo(BlogPost::class);
    }
}
