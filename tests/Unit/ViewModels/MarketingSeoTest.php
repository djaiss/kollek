<?php

declare(strict_types=1);

use App\Services\BlogCatalogue;
use App\Services\DocumentationPortal;
use App\ViewModels\MarketingFeatures;
use App\ViewModels\MarketingLanguages;
use App\ViewModels\MarketingSeo;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;

/**
 * Stands in for the real language resolver so a page can be described as missing
 * from a language. Every documentation page on disk is translated today, so the
 * fallback below has no URL that would exercise it.
 */
final class FakeMarketingLanguages extends MarketingLanguages
{
    /**
     * @param  array<int, array<string, mixed>>  $fake
     */
    public function __construct(private array $fake)
    {
        parent::__construct(app(DocumentationPortal::class), app(BlogCatalogue::class));
    }

    public function links(Request $request): array
    {
        return $this->fake;
    }
}

/**
 * @param  array<int, array<string, mixed>>  $links
 * @return array<string, mixed>
 */
function seoFor(string $routeName, array $links): array
{
    $request = Request::create('https://kollek.test/fr/docs/section/slug');

    $route = new Route(['GET'], '{locale}/docs/{section}/{slug}', fn () => null);
    $route->name($routeName);
    $route->bind($request);

    $request->setRouteResolver(fn (): Route => $route);

    return new MarketingSeo(new FakeMarketingLanguages($links), new MarketingFeatures)
        ->forRequest($request);
}

/**
 * @return array<int, array<string, mixed>>
 */
function seoLinks(bool $frenchTranslated): array
{
    return [
        ['locale' => 'en', 'code' => 'EN', 'label' => 'English', 'flag' => '🇬🇧', 'url' => 'https://kollek.test/en/docs/section/slug', 'current' => false, 'translated' => true],
        ['locale' => 'fr_FR', 'code' => 'FR', 'label' => 'Français', 'flag' => '🇫🇷', 'url' => $frenchTranslated ? 'https://kollek.test/fr/docs/section/slug' : 'https://kollek.test/fr/docs', 'current' => true, 'translated' => $frenchTranslated],
    ];
}

it('hands an untranslated documentation page to the english page it is copying', function () {
    $seo = seoFor('marketing.docs.portal.show', seoLinks(frenchTranslated: false));

    expect($seo['canonical'])->toBe('https://kollek.test/en/docs/section/slug');
});

it('leaves a language that does not carry the page out of the alternates', function () {
    $seo = seoFor('marketing.docs.portal.show', seoLinks(frenchTranslated: false));

    // English still gets an alternate: the page does exist there, it is only the
    // French URL that has nothing of its own to offer.
    expect(collect($seo['alternates'])->pluck('hreflang')->all())->toBe(['en', 'x-default']);
    expect($seo['alternateLocales'])->toBe(['en_US']);
});

it('points a translated page at itself and keeps both languages', function () {
    $seo = seoFor('marketing.docs.portal.show', seoLinks(frenchTranslated: true));

    expect($seo['canonical'])->toBe('https://kollek.test/fr/docs/section/slug');
    expect(collect($seo['alternates'])->pluck('hreflang')->all())->toBe(['en', 'fr-FR', 'x-default']);
    expect($seo['alternateLocales'])->toBe(['en_US']);
});

it('gives an english only page one url and no alternates at all', function () {
    $seo = seoFor('marketing.mediaKit.index', seoLinks(frenchTranslated: true));

    expect($seo['canonical'])->toBe('https://kollek.test/en/docs/section/slug');
    expect($seo['alternates'])->toBe([]);
    expect($seo['alternateLocales'])->toBe([]);
});

it('suffixes the browser title but leaves the card title alone', function () {
    $seo = seoFor('marketing.mediaKit.index', seoLinks(frenchTranslated: true));

    expect($seo['ogTitle'])->toBe('Media kit');
    expect($seo['title'])->toBe('Media kit · '.config('app.name'));
});
