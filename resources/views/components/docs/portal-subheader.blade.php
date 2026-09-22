@props(['locale', 'urlLocale', 'languageUrls'])

@php
    $current = collect($languageUrls)->firstWhere('current', true);
@endphp

<div class="sticky top-16 z-40 border-b border-hairline bg-page">
  <div class="mx-auto flex h-14 max-w-[1440px] items-center gap-4 px-5 sm:px-8">
    <a href="{{ route('marketing.docs.portal.home.show', ['locale' => $urlLocale]) }}" data-turbo="true" class="flex shrink-0 items-center gap-2 text-sm font-semibold text-ink">
      <x-lucide-book-open class="h-4 w-4" />
      {{ __('Documentation') }}
    </a>

    <div class="flex-1"></div>

    <div x-data="{ open: false }" class="relative shrink-0" @click.outside="open = false">
      <button
        type="button"
        @click="open = !open"
        class="flex items-center gap-2 rounded-lg border border-hairline bg-page px-2.5 py-[7px] text-[13px] font-semibold text-ink hover:bg-sidebar"
      >
        <x-lucide-globe class="h-[15px] w-[15px] text-muted" />
        {{ $current['code'] ?? strtoupper($locale) }}
        <x-lucide-chevron-down class="h-3.5 w-3.5 text-muted-soft" />
      </button>

      <div
        x-show="open"
        x-cloak
        class="absolute top-11 right-0 z-50 w-56 rounded-xl border border-hairline bg-page p-1.5 shadow-xl"
      >
        @foreach ($languageUrls as $language)
          <a
            href="{{ $language['url'] }}"
            data-turbo="true"
            class="flex items-center gap-2.5 rounded-lg px-2.5 py-2 text-[13px] font-medium text-ink hover:bg-sidebar {{ $language['current'] ? 'bg-sidebar' : '' }}"
          >
            <span class="w-6 font-mono text-[11px] font-semibold text-muted">{{ $language['code'] }}</span>
            <span class="flex-1">{{ $language['label'] }}</span>
            @if ($language['current'])
              <x-lucide-check class="h-[15px] w-[15px] text-brand" />
            @elseif (! $language['translated'])
              <span class="text-[11px] font-medium text-warning">{{ __('no version') }}</span>
            @endif
          </a>
        @endforeach
      </div>
    </div>
  </div>
</div>
