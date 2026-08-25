@props (['links'])

{{--
  The footer language picker. Every link is the page the visitor is already on, in another
  language, built by App\ViewModels\MarketingLanguages. The menu opens upwards because the
  footer sits at the bottom of the page.

  These are ordinary links rather than Turbo visits on purpose: switching language changes
  the lang attribute on <html>, which a body-only Turbo replacement would leave behind.
--}}
@php
    $current = collect($links)->firstWhere('current', true) ?? collect($links)->first();
@endphp

<div x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false" class="relative inline-block">
  <button type="button" @click="open = !open" :aria-expanded="open" aria-haspopup="true" class="flex items-center gap-x-2 rounded-md border border-[#242424] px-2.5 py-2 text-[13px] font-medium text-[#a1a1aa] transition-colors hover:bg-[#1a1a1a] hover:text-white">
    <x-lucide-globe class="h-4 w-4" />
    {{ $current['label'] }}
    <x-lucide-chevron-down class="h-3.5 w-3.5 transition-transform duration-200" ::class="open ? 'rotate-180' : ''" />
  </button>

  <div x-show="open" x-cloak class="absolute bottom-11 left-0 z-50 w-52 rounded-xl border border-[#242424] bg-[#161616] p-1.5 shadow-xl">
    @foreach ($links as $link)
      <a href="{{ $link['url'] }}" lang="{{ str_replace('_', '-', $link['locale']) }}" class="flex items-center gap-x-2.5 rounded-lg px-2.5 py-2 text-[13px] font-medium transition-colors hover:bg-[#1f1f1f] {{ $link['current'] ? 'text-white' : 'text-[#a1a1aa]' }}">
        <span aria-hidden="true">{{ $link['flag'] }}</span>
        <span class="flex-1">{{ $link['label'] }}</span>
        @if ($link['current'])
          <x-lucide-check class="h-[15px] w-[15px]" />
        @endif
      </a>
    @endforeach
  </div>
</div>
