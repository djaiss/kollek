<?php

declare(strict_types=1);

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BlogImageController extends Controller
{
    /**
     * A picture shown inside the text of a blog entry.
     *
     * Public and unauthenticated, for the same reason the social card is: a
     * reader with no session has to be able to fetch it. It is streamed from a
     * route rather than served off a public disk so the file itself stays
     * private, which is how every other file in this application is handled.
     *
     * The URL holds no locale and no slug, unlike the social card's. A rendered
     * page builds the card's URL again every time, but this one is written into
     * the Markdown of a body and stored as text, so it has to keep answering
     * after the entry is renamed, and it has to be the same URL for all seven
     * languages. The entry id never changes, so the URL is built from that.
     *
     * Holding the id is also what lets the path be derived rather than looked
     * up: no table maps a name back to an entry.
     */
    public function show(BlogPost $blogPost, string $image): StreamedResponse
    {
        $path = 'blog/'.$blogPost->id.'/body/'.$image;
        $disk = Storage::disk((string) config('filesystems.default'));

        if (! $disk->exists($path)) {
            throw new NotFoundHttpException;
        }

        return $disk->response($path, headers: [
            'Content-Type' => (string) $disk->mimeType($path),
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
