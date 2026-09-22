{{-- The unified history of the copy, newest first, merged from every other section. --}}

@php
  // Every filter control is a link that reloads the panel, matching how the copy
  // pills and sections navigate. This builds the timeline url for a given view
  // and set of types, keeping the url clean when they are at their defaults.
  $timelineUrl = function (string $view, array $types) use ($catalog, $item, $selectedCopy): string {
      $params = [$catalog, $item, $selectedCopy, 'timeline'];

      if ($view === 'complete') {
          $params['view'] = 'complete';
      }

      if ($types !== []) {
          $params['type'] = array_values($types);
      }

      return route('items.history.show', $params);
  };
@endphp

<div x-data="{ filtersOpen: true }">
  <div class="mb-5 flex items-start justify-between gap-4">
    <div>
      <div class="flex items-center gap-2">
        <p class="text-lg font-semibold tracking-[-0.3px] text-ink">{{ __('Unified history') }}</p>
        <x-help id="history.timeline" />
      </div>
      <p class="mt-1 max-w-xl text-[13.5px] leading-relaxed text-muted">{{ __('A combined chronological view built from every record below. Each entry keeps its own source of truth.') }}</p>
    </div>

    @if (! empty($presentSources))
      <button
        type="button"
        x-on:click="filtersOpen = ! filtersOpen"
        :aria-expanded="filtersOpen.toString()"
        class="inline-flex h-9 shrink-0 items-center rounded-lg border border-hairline bg-canvas px-3.5 text-[13px] font-semibold text-ink transition-colors hover:bg-card"
        data-test="timeline-filter-toggle"
      >{{ __('Filter') }}</button>
    @endif
  </div>

  @if (! empty($presentSources))
    <div x-show="filtersOpen" class="mb-6 flex flex-wrap items-center justify-between gap-3" data-test="timeline-filters">
    <div class="flex flex-wrap items-center gap-1.5">
      <a
        href="{{ $timelineUrl($timelineView, []) }}"
        data-turbo="true"
        @class([
            'inline-flex items-center rounded-full border px-2.5 py-1 text-[12.5px] font-semibold transition-colors',
            'border-ink bg-ink text-white' => $selectedTypes === [],
            'border-hairline text-muted hover:text-ink' => $selectedTypes !== [],
        ])
        @if ($selectedTypes === []) aria-current="true" @endif
        data-test="timeline-type-all"
      >{{ __('All') }}</a>

      @foreach ($presentSources as $source)
        @php
          $isActive = in_array($source->value, $selectedTypes, true);
          $toggled = $isActive
              ? array_values(array_diff($selectedTypes, [$source->value]))
              : array_values(array_merge($selectedTypes, [$source->value]));
        @endphp

        <a
          href="{{ $timelineUrl($timelineView, $toggled) }}"
          data-turbo="true"
          @class([
              'inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[12.5px] font-semibold transition-colors',
              'border-hairline text-muted hover:text-ink' => ! $isActive,
          ])
          @if ($isActive) style="border-color: {{ $source->color() }}80; background-color: {{ $source->color() }}22; color: {{ $source->color() }};" aria-current="true" @endif
          data-test="timeline-type-{{ $source->value }}"
        >
          <span class="size-[7px] shrink-0 rounded-full" style="background-color: {{ $source->color() }}" aria-hidden="true"></span>
          {{ $source->label() }}
        </a>
      @endforeach
    </div>

    <div class="flex items-center gap-0.5 rounded-lg bg-card p-1" data-test="timeline-view-toggle" role="group" aria-label="{{ __('History detail') }}">
      <a
        href="{{ $timelineUrl('meaningful', $selectedTypes) }}"
        data-turbo="true"
        @class([
            'rounded-md px-3 py-1 text-[12.5px] font-semibold transition-colors',
            'bg-canvas text-ink shadow-sm' => $timelineView === 'meaningful',
            'text-muted hover:text-ink' => $timelineView !== 'meaningful',
        ])
        @if ($timelineView === 'meaningful') aria-current="true" @endif
        data-test="timeline-view-meaningful"
      >{{ __('Meaningful') }}</a>
      <a
        href="{{ $timelineUrl('complete', $selectedTypes) }}"
        data-turbo="true"
        @class([
            'rounded-md px-3 py-1 text-[12.5px] font-semibold transition-colors',
            'bg-canvas text-ink shadow-sm' => $timelineView === 'complete',
            'text-muted hover:text-ink' => $timelineView !== 'complete',
        ])
        @if ($timelineView === 'complete') aria-current="true" @endif
        data-test="timeline-view-complete"
      >{{ __('Complete') }}</a>
    </div>
  </div>
  @endif
</div>

<div class="flex flex-col">
  @forelse ($timeline as $entry)
    @php
      $color = $entry->source->color();
      $tint = $color.'26';
      $amount = $entry->formattedAmount();
    @endphp

    <a
      href="{{ route('items.history.show', [$catalog, $item, $selectedCopy, $entry->source->section()]) }}"
      data-turbo="true"
      class="group flex gap-4"
      data-test="history-{{ $entry->key() }}"
    >
      <div class="flex shrink-0 flex-col items-center pt-[3px]">
        <span class="size-[13px] rounded-full" style="background-color: {{ $tint }}; border: 2.5px solid {{ $color }};" aria-hidden="true"></span>
        @unless ($loop->last)
          <span class="w-0.5 flex-1 bg-hairline" aria-hidden="true"></span>
        @endunless
      </div>

      <div class="min-w-0 flex-1 pb-6">
        <div class="mb-1 flex flex-wrap items-center gap-2.5">
          <span class="rounded-full px-2 py-0.5 text-[10.5px] font-semibold tracking-wide uppercase" style="background-color: {{ $tint }}; color: {{ $color }};">{{ $entry->source->label() }}</span>
          <span class="text-xs text-muted-soft">{{ $entry->formattedDate() }}</span>
        </div>

        <p class="text-[15px] font-semibold text-ink group-hover:underline">{{ $entry->title }}</p>

        @php
          $detail = collect([$amount, $entry->summary])->filter(fn ($part): bool => $part !== null && $part !== '')->join(' · ');
        @endphp

        @if ($detail !== '')
          <p class="mt-0.5 text-[13.5px] leading-relaxed text-muted">{{ $detail }}</p>
        @endif
      </div>
    </a>
  @empty
    @if (empty($presentSources))
      <div class="rounded-xl border border-hairline">
        <x-empty-state data-test="no-history">
          <x-slot:icon>
            <x-lucide-clock class="size-6 text-muted" />
          </x-slot>

          {{ __('Nothing has been recorded against this copy yet. Record a transaction, valuation or provenance event, and it starts to build a history here.') }}
        </x-empty-state>
      </div>
    @else
      <div class="rounded-xl border border-hairline">
        <x-empty-state data-test="no-history-matches">
          <x-slot:icon>
            <x-lucide-list-filter class="size-6 text-muted" />
          </x-slot>

          {{ __('Nothing matches this filter. Switch to the complete view or clear the filter to see more.') }}
        </x-empty-state>
      </div>
    @endif
  @endforelse
</div>
