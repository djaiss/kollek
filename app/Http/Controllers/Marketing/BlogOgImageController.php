<?php

declare(strict_types=1);

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\BlogPostTranslation;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BlogOgImageController extends Controller
{
    /**
     * The social card of one entry, in one language.
     *
     * Unlike every other file this application stores, this one is deliberately
     * public and unauthenticated: the whole point of a social card is that a
     * crawler with no session can fetch it. It is served from a route rather
     * than a public disk so the file itself stays private, and so the URL is
     * the entry's rather than a path on somebody's storage.
     *
     * The {locale} prefix is the first route parameter, so it is absorbed here.
     */
    public function show(string $locale, string $slug): StreamedResponse
    {
        $translation = BlogPostTranslation::query()
            ->where('locale', app()->getLocale())
            ->where('slug', $slug)
            ->whereNotNull('og_image_path')
            ->first();

        if ($translation === null) {
            throw new NotFoundHttpException;
        }

        $path = (string) $translation->og_image_path;
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
