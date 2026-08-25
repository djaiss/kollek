@php
    // "Features" is not a plain link: it opens the mega menu on hover and leads
    // to the features hub on click, so it is rendered on its own below and left
    // out of this list.
    $navigation = [
        ['label' => __('Pricing'), 'url' => route('marketing.pricing.index')],
        ['label' => __('Blog'), 'url' => route('marketing.blog.index')],
        ['label' => __('Docs'), 'url' => route('marketing.docs.portal.home.show')],
        ['label' => __('API'), 'url' => route('marketing.docs.api.index')],
        ['label' => __('FAQ'), 'url' => route('marketing.faq.index')],
    ];

    $featureColumns = app(\App\ViewModels\MarketingFeatures::class)->columns();

    // The reviews link only appears once there is something to read, so the site
    // never points visitors at an empty page.
    if (\App\Models\Testimonial::query()->published()->exists()) {
        $navigation[] = ['label' => __('Reviews'), 'url' => route('marketing.testimonials.index')];
    }

    $banner = \App\Models\SiteOption::current()->bannerFor(app()->getLocale());
@endphp

{{-- display:contents so the sticky nav below resolves against the page container,
     not this short wrapper. Otherwise the nav can only stick within the wrapper's
     own height (announcement bar + nav) and unsticks as soon as you scroll past it. --}}
<div
  x-data="{
    mobileMenuOpen: false,
    mobileFeaturesOpen: false,
    featuresOpen: false,
    featuresTimer: null,
    openFeatures() {
      clearTimeout(this.featuresTimer);
      this.featuresOpen = true;
    },
    closeFeatures() {
      this.featuresTimer = setTimeout(() => (this.featuresOpen = false), 140);
    },
  }"
  @keydown.escape.window="featuresOpen = false"
  class="contents">
  {{-- Skip to content. The wrapper is display:contents, so this stays the first
       thing in the tab order of every page the header sits on, and a keyboard
       visitor reaches the page without walking the whole navigation first. It is
       off screen until it takes focus. --}}
  <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-60 focus:rounded-md focus:bg-primary focus:px-4 focus:py-2.5 focus:text-sm focus:font-semibold focus:text-on-primary">{{ __('Skip to content') }}</a>

  {{-- The announcement bar is written by an instance administrator in the site
       options, so it is only here when there is something to announce. --}}
  @if ($banner)
    <div class="flex flex-col items-center justify-center gap-2 bg-[#101010] px-4 py-2 text-center text-[13px] font-medium sm:h-10 sm:flex-row sm:py-0">
      <div class="flex items-center gap-2">
        @if ($banner['version'])
          <span class="rounded-full bg-[#1a1a1a] px-2 py-[3px] text-[11px] font-semibold tracking-wide text-badge-emerald">{{ $banner['version'] }}</span>
        @endif
        <span class="text-[#a1a1aa]">{{ $banner['text'] }}</span>
      </div>
      @if ($banner['url'])
        <a href="{{ $banner['url'] }}" class="font-semibold text-white hover:underline">{{ $banner['link_label'] ?? __('Read more') }} &rarr;</a>
      @endif
    </div>
  @endif

  {{-- Main nav --}}
  <div class="sticky top-0 z-50 border-b border-hairline bg-page/85 backdrop-blur-md">
    <nav class="mx-auto flex h-16 max-w-[1200px] items-center justify-between px-5 sm:px-8">
      <a href="{{ route('marketing.index') }}" data-turbo="true" class="group flex shrink-0 items-center gap-x-2.5">
        <div class="transition-all duration-400 group-hover:-translate-y-0.5 group-hover:-rotate-3">
          <x-logo size="30" aria-hidden="true" />
        </div>
        <x-wordmark height="17" class="text-ink" />
      </a>

      {{-- Desktop navigation. The bar carries its own text-body so the Features
           link below reads the same as its neighbours before Alpine has run.
           Its colour comes from a bound class, and a bound class is missing both
           in the server HTML and in the copy Turbo caches, which left it
           inheriting text-ink and fading back to grey on every page change. --}}
      <div class="hidden items-center gap-x-1 text-body lg:flex">
        {{-- Features: hover opens the mega menu, click goes to the hub. The
             wrapper holds both the trigger and the panel, so mouseleave only
             fires once the pointer has left both (the panel is a descendant),
             which is what makes the hover close reliable. --}}
        <div @mouseenter="openFeatures()" @mouseleave="closeFeatures()">
          <a href="{{ route('marketing.features.index') }}" data-turbo="true" @click="featuresOpen = false" aria-haspopup="true" :aria-expanded="featuresOpen" class="flex items-center gap-x-1.5 rounded-md px-3 py-2 text-sm font-medium transition-colors hover:bg-sidebar" :class="featuresOpen ? 'bg-sidebar text-ink' : 'text-body'">
            {{ __('Features') }}
            <x-lucide-chevron-down class="h-3.5 w-3.5 transition-transform duration-200" ::class="featuresOpen ? 'rotate-180' : ''" />
          </a>

          <x-marketing.features-menu :columns="$featureColumns" />
        </div>

        @foreach ($navigation as $link)
          <a href="{{ $link['url'] }}" data-turbo="true" class="rounded-md px-3 py-2 text-sm font-medium text-body transition-colors hover:bg-sidebar hover:text-ink">{{ $link['label'] }}</a>
        @endforeach
      </div>

      <div class="flex items-center gap-x-2.5">
        <a href="{{ config('marketing.github_url') }}" target="_blank" rel="noopener" class="hidden items-center gap-x-2 rounded-md border border-hairline px-3.5 py-2 text-sm font-medium text-ink transition-colors hover:bg-sidebar sm:flex">
          <x-lucide-github class="h-4 w-4" />
          {{ __('GitHub') }}
        </a>

        {{-- The public site is cached as one page for everybody, so this cannot ask who is
             reading it: a signed in visitor sees the same call to action as a stranger. --}}
        <a href="{{ route('register') }}" data-turbo="true" class="flex h-10 items-center rounded-md bg-primary px-4.5 text-sm font-semibold text-on-primary transition-colors hover:opacity-90">{{ __('Get started') }}</a>

        {{-- Mobile menu button --}}
        <button type="button" @click="mobileMenuOpen = true" class="-mr-2 inline-flex items-center justify-center rounded-md p-2 text-ink lg:hidden">
          <span class="sr-only">{{ __('Open main menu') }}</span>
          <x-lucide-menu class="h-6 w-6" />
        </button>
      </div>
    </nav>
  </div>

  {{-- Mobile menu (off-canvas) --}}
  <div x-show="mobileMenuOpen" x-cloak class="lg:hidden">
    <div class="fixed inset-0 z-50 bg-ink/20" @click="mobileMenuOpen = false"></div>
    <div class="fixed inset-y-0 right-0 z-50 w-full overflow-y-auto bg-page px-6 py-5 sm:max-w-sm sm:border-l sm:border-hairline">
      <div class="mb-6 flex items-center justify-between">
        <x-wordmark height="17" class="text-ink" />
        <button type="button" @click="mobileMenuOpen = false" class="-mr-2 rounded-md p-2 text-muted hover:bg-sidebar">
          <x-lucide-x class="h-6 w-6" />
          <span class="sr-only">{{ __('Close menu') }}</span>
        </button>
      </div>

      <div class="flex flex-col">
        {{-- Features: a collapsible section listing every feature area, with the
             hub itself one tap away. Replaces the hover mega menu on touch. --}}
        <div class="border-b border-hairline-soft">
          <button type="button" @click="mobileFeaturesOpen = !mobileFeaturesOpen" :aria-expanded="mobileFeaturesOpen" class="flex w-full items-center justify-between py-3.5 text-base font-semibold text-ink">
            {{ __('Features') }}
            <x-lucide-chevron-down class="h-5 w-5 text-muted transition-transform duration-200" ::class="mobileFeaturesOpen ? 'rotate-180' : ''" />
          </button>

          <div x-show="mobileFeaturesOpen" x-cloak class="pb-2">
            <a href="{{ route('marketing.features.index') }}" data-turbo="true" @click="mobileMenuOpen = false" class="flex items-center gap-1.5 pb-2 text-[13px] font-semibold text-body">
              {{ __('All features') }}
              <x-lucide-arrow-right class="h-3.5 w-3.5" />
            </a>
            @foreach ($featureColumns as $column)
              <div class="mt-2 mb-1 flex items-center gap-2">
                <span class="h-1.5 w-1.5 rounded-full" style="background:{{ $column['dot'] }};"></span>
                <span class="text-[11px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ $column['label'] }}</span>
              </div>
              @foreach ($column['items'] as $item)
                <a href="{{ route('marketing.features.show', $item['slug']) }}" data-turbo="true" @click="mobileMenuOpen = false" class="flex items-center gap-2.5 py-2 pl-3.5 text-[15px] text-body">
                  <span class="h-2 w-2 shrink-0 rounded-full" style="background:{{ $item['dot'] }};"></span>
                  {{ $item['title'] }}
                  @if ($item['isNew'])
                    <span class="rounded-full bg-[#e7f6ee] px-1.5 py-0.5 text-[9px] font-bold tracking-[0.4px] text-[#0f7a4d]">{{ __('NEW') }}</span>
                  @endif
                </a>
              @endforeach
            @endforeach
          </div>
        </div>

        @foreach ($navigation as $link)
          <a href="{{ $link['url'] }}" data-turbo="true" @click="mobileMenuOpen = false" class="border-b border-hairline-soft py-3.5 text-base font-semibold text-ink">{{ $link['label'] }}</a>
        @endforeach
        <a href="{{ config('marketing.github_url') }}" target="_blank" rel="noopener" class="border-b border-hairline-soft py-3.5 text-base font-semibold text-ink">{{ __('GitHub') }}</a>

        <a href="{{ route('login') }}" data-turbo="true" class="py-3.5 text-base font-semibold text-ink">{{ __('Sign in') }}</a>
      </div>
    </div>
  </div>
</div>
