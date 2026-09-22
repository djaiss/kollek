{{-- The social and crawler half of the head, for the public pages only. --}}

<link rel="canonical" href="{{ $seo['canonical'] }}" />

@foreach ($seo['alternates'] as $alternate)
  <link rel="alternate" hreflang="{{ $alternate['hreflang'] }}" href="{{ $alternate['url'] }}" />
@endforeach

<meta property="og:type" content="{{ $seo['type'] }}" />
<meta property="og:site_name" content="{{ config('app.name') }}" />
<meta property="og:url" content="{{ $seo['canonical'] }}" />
<meta property="og:title" content="{{ $seo['ogTitle'] }}" />
<meta property="og:description" content="{{ $seo['description'] }}" />
<meta property="og:image" content="{{ $seo['image'] }}" />
<meta property="og:image:width" content="1200" />
<meta property="og:image:height" content="630" />
<meta property="og:image:alt" content="{{ config('app.name') }}" />
<meta property="og:locale" content="{{ $seo['locale'] }}" />

@foreach ($seo['alternateLocales'] as $alternateLocale)
  <meta property="og:locale:alternate" content="{{ $alternateLocale }}" />
@endforeach

<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:title" content="{{ $seo['ogTitle'] }}" />
<meta name="twitter:description" content="{{ $seo['description'] }}" />
<meta name="twitter:image" content="{{ $seo['image'] }}" />

<script type="application/ld+json">{!! json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
