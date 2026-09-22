<x-app-layout>
  <x-slot:title>
    {{ __('Search') }}
  </x-slot>

  <div class="sticky top-0 z-20 border-b border-hairline bg-page px-6 py-4 lg:px-12">
    <div class="mx-auto w-full max-w-5xl">
      <form method="get" action="{{ route('search.index') }}" class="flex items-center gap-3">
        <div class="relative flex-1">
          <span class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-muted-soft">
            @svg('lucide-search', 'size-4')
          </span>
          <input
            type="search"
            name="q"
            value="{{ $query }}"
            placeholder="{{ __('Search items, collections, copies, loans…') }}"
            maxlength="255"
            autofocus
            autocomplete="off"
            class="h-11 w-full rounded-lg border border-hairline bg-canvas pr-24 pl-10 text-[15px] text-ink placeholder:text-muted-soft focus:border-transparent focus:ring-2 focus:ring-[var(--color-accent)]/40 focus:outline-none"
            data-test="search-input"
          />
          <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center gap-1.5">
            @if ($hasQuery)
              <a href="{{ route('search.index') }}" data-turbo="true" class="pointer-events-auto text-xs font-semibold text-muted-soft hover:text-ink" data-test="clear-search">{{ __('Clear') }}</a>
            @endif
            <kbd class="rounded border border-hairline px-1.5 py-0.5 font-mono text-[10.5px] text-muted-soft" x-data x-text="navigator.platform.toLowerCase().includes('mac') ? '⌘K' : 'Ctrl K'">⌘K</kbd>
          </div>
        </div>

        <button type="submit" class="sr-only">{{ __('Search') }}</button>
      </form>
    </div>
  </div>

  <div class="px-6 py-8 lg:px-12 lg:py-10">
    <div class="mx-auto w-full max-w-5xl">
      @if (! $hasQuery)
        <div class="flex flex-col gap-8">
          <div>
            <div class="flex items-center gap-2">
              <h1 class="text-[28px] font-semibold tracking-tight text-ink">{{ __('Search across your account.') }}</h1>
              <x-help id="search.query" />
            </div>
            <p class="mt-1.5 max-w-2xl text-[15px] text-muted">{{ __('Items, collections, copies, photos, loans, locations, sets, series, categories, tags and documents. All indexed, all private to your account.') }}</p>
          </div>

          <div class="overflow-hidden rounded-xl border border-hairline">
            <div class="flex items-center gap-2.5 border-b border-hairline-soft px-5 py-3.5">
              <span class="text-[15px] font-semibold text-ink">{{ __("What's indexed") }}</span>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4">
              @foreach ($indexed as $entry)
                <div class="flex items-center gap-2.5 border-r border-b border-hairline-soft px-5 py-3.5 last:border-r-0">
                  <span class="shrink-0 text-muted-soft">@svg('lucide-'.$entry['icon'], 'size-4')</span>
                  <span class="min-w-0 flex-1 truncate text-[13px] font-medium text-body">{{ $entry['label'] }}</span>
                  <span class="text-[13px] font-semibold text-ink">{{ number_format($entry['count']) }}</span>
                </div>
              @endforeach
            </div>
          </div>
        </div>
      @else
        <div class="flex flex-col gap-5">
          <div class="flex flex-wrap items-baseline gap-2.5">
            <h1 class="text-[22px] font-semibold tracking-tight text-ink" data-test="search-headline">
              @if ($total === 0)
                {{ __('No matches for “:query”', ['query' => $query]) }}
              @else
                {{ trans_choice(':count result for “:query”|:count results for “:query”', $total, ['count' => $total, 'query' => $query]) }}
              @endif
            </h1>
            @if ($total > 0)
              <span class="text-[13px] text-muted-soft">{{ __('Grouped by type. Every word has to match.') }}</span>
            @endif
          </div>

          @if (count($filters) > 0)
            <div class="flex flex-wrap gap-1.5">
              @foreach ($filters as $filter)
                <a
                  href="{{ route('search.index', array_filter(['type' => $filter['slug'], 'q' => $query])) }}"
                  data-turbo="true"
                  @class([
                      'flex items-center gap-1.5 rounded-full border px-3.5 py-1.5 text-[13px] transition-colors',
                      'border-ink bg-ink font-semibold text-page' => $filter['slug'] === ($selectedType?->slug()),
                      'border-hairline text-body hover:border-hairline-soft hover:bg-card' => $filter['slug'] !== ($selectedType?->slug()),
                  ])
                  data-test="search-filter-{{ $filter['slug'] ?? 'all' }}"
                >
                  {{ $filter['label'] }}
                  <span class="font-mono text-[11px] opacity-70">{{ $filter['count'] }}</span>
                </a>
              @endforeach
            </div>
          @endif

          @if (count($groups) === 0)
            <div class="flex flex-col items-center gap-2 rounded-xl border border-dashed border-hairline px-8 py-12 text-center" data-test="search-no-results">
              <div class="text-base font-semibold text-ink">
                {{ $isQueryTooShort ? __('Query too short') : __('Nothing matched') }}
              </div>
              <p class="max-w-md text-[13.5px] leading-relaxed text-muted">
                {{ $isQueryTooShort
                    ? __('Single letters are not indexed on their own, so this returns nothing rather than everything. Try at least two characters.')
                    : __('Every word in your query has to match somewhere in a record. Try fewer words, or the start of one, such as “spi”.') }}
              </p>
            </div>
          @endif

          @foreach ($groups as $group)
            <div class="overflow-hidden rounded-xl border border-hairline" data-test="search-group-{{ $loop->index }}">
              <div class="flex items-center gap-2.5 border-b border-hairline-soft px-5 py-3.5">
                <span class="flex size-7 shrink-0 items-center justify-center rounded-[8px] {{ $group['badgeClasses'] }}">
                  @svg('lucide-'.$group['icon'], 'size-3.5')
                </span>
                <span class="text-[15px] font-semibold text-ink">{{ $group['label'] }}</span>
                <span class="rounded-full bg-card px-2 py-0.5 text-xs font-semibold text-muted">{{ $group['count'] }}</span>
              </div>

              @foreach ($group['rows'] as $row)
                <a
                  href="{{ $row['url'] }}"
                  data-turbo="true"
                  class="flex items-center gap-3.5 border-b border-hairline-soft px-5 py-3 transition-colors last:border-b-0 hover:bg-card"
                  data-test="search-result-{{ $row['id'] }}"
                >
                  <span class="flex size-10 shrink-0 items-center justify-center overflow-hidden rounded-md bg-card text-muted-soft">
                    @if ($row['thumbnailUrl'])
                      <img src="{{ $row['thumbnailUrl'] }}" alt="" class="size-full object-cover" loading="lazy" />
                    @else
                      @svg('lucide-'.$group['icon'], 'size-4')
                    @endif
                  </span>

                  <span class="min-w-0 flex-1">
                    <span class="flex items-center gap-2">
                      <span class="truncate text-[14.5px] font-semibold text-ink">{{ $row['title'] }}</span>
                      <span class="shrink-0 rounded px-1.5 py-0.5 text-[10px] font-bold tracking-wide uppercase {{ $group['badgeClasses'] }}">{{ $group['badge'] }}</span>
                      @if ($row['collectionName'])
                        <span class="hidden shrink-0 text-[11.5px] text-muted-soft sm:inline">{{ $row['collectionName'] }}</span>
                      @endif
                    </span>
                    <span class="mt-0.5 block truncate text-[12.5px] text-muted">{{ $row['context'] }}</span>
                  </span>

                  <span class="hidden shrink-0 text-[11px] text-muted-soft md:block">{{ $row['matched'] }}</span>
                </a>
              @endforeach
            </div>
          @endforeach

          @if ($isCapped)
            <p class="text-center text-[12.5px] text-muted-soft">{{ __('Showing the first 50 of :count matches.', ['count' => $matched]) }}</p>
          @endif
        </div>
      @endif
    </div>
  </div>
</x-app-layout>
