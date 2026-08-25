<?php

declare(strict_types=1);

namespace App\Mcp\Servers;

use App\Mcp\Tools\Blog\ArchiveBlogPost;
use App\Mcp\Tools\Blog\CopyBlogPostSourceTranslation;
use App\Mcp\Tools\Blog\CreateBlogPost;
use App\Mcp\Tools\Blog\ListBlogPosts;
use App\Mcp\Tools\Blog\PublishBlogPost;
use App\Mcp\Tools\Blog\PublishBlogPostTranslation;
use App\Mcp\Tools\Blog\ShowBlogPost;
use App\Mcp\Tools\Blog\ShowBlogPostTranslation;
use App\Mcp\Tools\Blog\UpdateBlogPost;
use App\Mcp\Tools\Blog\WithdrawBlogPostTranslation;
use App\Mcp\Tools\Blog\WriteBlogPostTranslation;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;
use Laravel\Mcp\Server\Tool;

/**
 * The instance administration, driven by an assistant rather than by a person.
 *
 * The blog is the first thing it can run. Everything else the panel does is
 * still done by hand, so the tool list grows one screen at a time.
 *
 * Deleting is deliberately absent: an entry can be archived, which takes it out
 * of the catalogue while its URL keeps answering, and wiping one for good stays
 * a decision somebody makes in the panel.
 */
#[Name('Kollek instance administration')]
#[Version('1.0.0')]
#[Instructions(<<<'MARKDOWN'
    This server runs the instance administration of a Kollek instance. Right now
    it covers the blog on the marketing site.

    A blog entry is one thing written in several languages. English is the
    source: it always exists, it is what every other language is written from,
    and it is what a reader is served when their own language is not ready. The
    other languages are translations, and a translation is only shown to readers
    once it has been marked proofread.

    An entry is a draft until it is published, and archiving takes it out of the
    catalogue without breaking the links pointing at it.

    Work in this order: create the entry with its English text, write the other
    languages, then publish. The write tools change only the fields they are
    given, but they replace those fields outright, so read a language with
    `show-blog-post-translation` before rewriting part of it.
    MARKDOWN)]
class InstanceServer extends Server
{
    /**
     * @var array<int, class-string<Tool>>
     */
    protected array $tools = [
        ListBlogPosts::class,
        ShowBlogPost::class,
        CreateBlogPost::class,
        UpdateBlogPost::class,
        PublishBlogPost::class,
        ArchiveBlogPost::class,
        ShowBlogPostTranslation::class,
        WriteBlogPostTranslation::class,
        CopyBlogPostSourceTranslation::class,
        PublishBlogPostTranslation::class,
        WithdrawBlogPostTranslation::class,
    ];
}
