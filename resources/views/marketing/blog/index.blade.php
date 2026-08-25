<x-marketing-layout :title="__('Blog')">
  @php
    $totalMinutes = collect($entries)->sum('readingMinutes');
  @endphp

  {{-- Hero --}}
  <section class="mx-auto max-w-[1200px] px-5 pt-16 sm:px-8 sm:pt-24">
    <p class="mb-6 text-[12px] font-semibold tracking-[1.4px] text-muted-soft uppercase">{{ __('Blog') }}</p>
    <h1 class="max-w-[820px] text-[32px] leading-[1.04] font-semibold tracking-[-1px] text-balance text-ink sm:text-5xl lg:text-[64px] lg:tracking-[-2.4px]">{{ __('Every post we have ever written, catalogued.') }}</h1>

    <dl class="mt-10 flex flex-wrap gap-x-11 gap-y-6 sm:mt-12">
      <div class="flex flex-col gap-1.5">
        <dt class="text-[11px] font-medium tracking-[1.1px] text-muted-soft uppercase">{{ __('Entries') }}</dt>
        <dd class="text-[28px] font-semibold tracking-[-0.9px] text-ink">{{ count($entries) }}</dd>
      </div>
      <div class="flex flex-col gap-1.5">
        <dt class="text-[11px] font-medium tracking-[1.1px] text-muted-soft uppercase">{{ __('Shelves') }}</dt>
        <dd class="text-[28px] font-semibold tracking-[-0.9px] text-ink">{{ count($shelves) }}</dd>
      </div>
      <div class="flex flex-col gap-1.5">
        <dt class="text-[11px] font-medium tracking-[1.1px] text-muted-soft uppercase">{{ __('Reading time') }}</dt>
        <dd class="text-[28px] font-semibold tracking-[-0.9px] text-ink">{{ __(':count min', ['count' => $totalMinutes]) }}</dd>
      </div>
      <div class="flex flex-col gap-1.5">
        <dt class="text-[11px] font-medium tracking-[1.1px] text-muted-soft uppercase">{{ __('Languages') }}</dt>
        <dd class="text-[28px] font-semibold tracking-[-0.9px] text-ink">{{ count(config('docs.locales')) }}</dd>
      </div>
    </dl>
  </section>

  {{-- The catalogue. The whole list is rendered and the shelf filter runs in
       the browser: the page is held by the CDN as one document for everybody,
       so filtering on the server would mean one cached copy per shelf. --}}
  <section id="archive" class="mx-auto max-w-[1200px] px-5 pt-16 sm:px-8 sm:pt-24" x-data="{ shelf: 'all' }">
    <div class="flex flex-wrap items-end justify-between gap-6 border-b border-hairline pb-5">
      <div role="group" aria-label="{{ __('Filter by shelf') }}" class="flex flex-wrap gap-2">
        <button type="button" @click="shelf = 'all'" :aria-pressed="shelf === 'all' ? 'true' : 'false'" class="rounded-md border px-3 py-2 text-[11px] font-medium tracking-[1px] uppercase transition-colors" :class="shelf === 'all' ? 'border-ink bg-ink text-page' : 'border-hairline text-muted hover:bg-sidebar'">{{ __('All') }}</button>
        @foreach ($shelves as $shelf)
          <button type="button" @click="shelf = '{{ $shelf['value'] }}'" :aria-pressed="shelf === '{{ $shelf['value'] }}' ? 'true' : 'false'" class="rounded-md border px-3 py-2 text-[11px] font-medium tracking-[1px] uppercase transition-colors" :class="shelf === '{{ $shelf['value'] }}' ? 'border-ink bg-ink text-page' : 'border-hairline text-muted hover:bg-sidebar'">{{ $shelf['label'] }}</button>
        @endforeach
      </div>

      <a href="{{ route('marketing.blog.feed.index') }}" class="flex items-center gap-2 rounded-md border border-hairline px-3 py-2 text-[11px] font-medium tracking-[1px] text-ink uppercase transition-colors hover:bg-sidebar">
        @svg ('lucide-rss', 'size-3.5 text-muted')
        {{ __('RSS') }}
      </a>
    </div>

    @if (count($entries) === 0)
      <div class="mx-auto mt-16 max-w-[420px] rounded-lg border border-dashed border-hairline bg-card px-6 py-12 text-center">
        <p class="text-[15px] text-muted">{{ __('Nothing written yet. Check back soon.') }}</p>
      </div>
    @else
      <ol class="list-none p-0">
        @foreach ($entries as $entry)
          <li x-show="shelf === 'all' || shelf === '{{ $entry['shelf'] }}'" class="border-b border-hairline-soft">
            <a href="{{ route('marketing.blog.show', $entry['slug']) }}" data-turbo="true" class="-mx-3 grid grid-cols-[56px_1fr] items-baseline gap-x-6 gap-y-1.5 rounded-lg px-3 py-5 transition-colors hover:bg-sidebar sm:grid-cols-[104px_1fr_auto]">
              <span class="text-[12px] tracking-[0.6px] whitespace-nowrap text-muted-soft">{{ $entry['reference'] }}</span>

              <span class="flex flex-wrap items-baseline gap-x-3">
                <span class="text-[17px] leading-[1.35] font-medium tracking-[-0.5px] text-ink sm:text-[21px]">{{ $entry['title'] }}</span>
                @if ($entry['isNew'])
                  <span class="rounded-md bg-ink px-1.5 py-0.5 text-[10px] font-semibold tracking-[1px] text-page uppercase">{{ __('New') }}</span>
                @endif
              </span>

              <span class="col-start-2 flex items-center gap-5 sm:col-start-3 sm:justify-end">
                <span class="text-[11px] tracking-[0.8px] whitespace-nowrap text-muted-soft uppercase">{{ $entry['shelfLabel'] }}</span>
                <span class="text-[11px] whitespace-nowrap text-muted-soft sm:min-w-[74px] sm:text-right">{{ $entry['publishedAt']->isoFormat('MMM YYYY') }}</span>
                <span class="text-[11px] whitespace-nowrap text-muted-soft sm:min-w-[44px] sm:text-right">{{ __(':count min', ['count' => $entry['readingMinutes']]) }}</span>
              </span>
            </a>
          </li>
        @endforeach
      </ol>

      {{-- Only shown once a shelf is chosen, so the catalogue does not carry a
           "clear" control when there is nothing to clear. --}}
      <button type="button" x-show="shelf !== 'all'" x-cloak @click="shelf = 'all'" class="mt-7 inline-flex items-center gap-2.5 rounded-md border border-hairline px-4 py-2.5 text-sm font-semibold text-ink transition-colors hover:bg-sidebar">
        @svg ('lucide-x', 'size-3.5 text-muted-soft')
        {{ __('Show the whole catalogue') }}
      </button>
    @endif
  </section>

  <section class="mx-auto max-w-[1200px] px-5 pt-16 pb-8 sm:px-8 sm:pt-24">
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
      @foreach ($shelves as $shelf)
        <div class="rounded-xl border border-hairline bg-canvas p-5">
          <div class="mb-2 flex items-baseline justify-between gap-2">
            <p class="text-[11px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ $shelf['label'] }}</p>
            <p class="text-[11px] text-muted-soft">{{ __(':count entries', ['count' => $counts[$shelf['value']] ?? 0]) }}</p>
          </div>
          <p class="text-[13px] leading-[1.5] text-pretty text-muted">{{ $shelf['description'] }}</p>
        </div>
      @endforeach
    </div>
  </section>
</x-marketing-layout>
