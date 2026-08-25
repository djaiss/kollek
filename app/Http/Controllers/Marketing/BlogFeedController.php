<?php

declare(strict_types=1);

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Services\BlogFeed;
use Illuminate\Http\Response;

class BlogFeedController extends Controller
{
    public function __construct(
        private BlogFeed $feed,
    ) {}

    /**
     * The blog as RSS, one feed per language. The {locale} URL prefix is
     * consumed by the marketing.locale middleware, so the feed is built for
     * whichever locale the reader subscribed under.
     *
     * The feed carries each entry in full rather than a teaser: a reader who
     * chose a feed reader has said where they want to read, and sending them
     * back to the site for the rest of the sentence ignores that.
     */
    public function index(): Response
    {
        return response($this->feed->forLocale(app()->getLocale()), 200, [
            'Content-Type' => 'application/rss+xml; charset=UTF-8',
        ]);
    }
}
