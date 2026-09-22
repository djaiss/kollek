<div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-[1.6fr_1fr]">
  <div class="flex min-w-0 flex-col gap-8">
    <div>
      @if ($item->photos->isEmpty())
        <div class="flex aspect-4/3 w-full items-center justify-center rounded-xl border border-dashed border-hairline bg-card text-5xl">
          {{ $catalog->emoji ?? '📦' }}
        </div>
      @else
        <div x-data="tiltCard" class="t-tilt">
          <div class="t-tilt-card relative aspect-4/3 w-full overflow-hidden rounded-xl border border-hairline">
            @foreach ($item->photos as $photo)
              <img
                x-show="photo === {{ $loop->index }}"
                @if (! $loop->first) x-cloak @endif
                src="{{ $photo->url() }}"
                alt="{{ $item->name }}"
                class="absolute inset-0 size-full object-cover"
              />
            @endforeach

            @if ($item->photos->count() > 1)
              <span
                class="absolute right-3.5 bottom-3.5 rounded-md bg-black/40 px-2.5 py-1 font-mono text-[11px] text-white"
                data-test="photo-position"
              >
                <span x-text="photo + 1">1</span> / {{ $item->photos->count() }}
              </span>
            @endif

            <div class="t-tilt-glare"></div>
          </div>
        </div>
      @endif

      @if ($item->photos->isNotEmpty())
        <div class="mt-3 flex flex-wrap gap-2.5">
          @foreach ($item->photos as $photo)
            <button
              type="button"
              x-on:click="photo = {{ $loop->index }}"
              :class="photo === {{ $loop->index }} ? 'border-ink' : 'border-hairline'"
              class="relative h-[60px] w-[78px] shrink-0 cursor-pointer overflow-hidden rounded-lg border-2"
            >
              <img src="{{ $photo->url() }}" alt="" class="size-full object-cover" />
              @if ($photo->is_main)
                <span class="absolute top-1 left-1 rounded bg-black/55 px-1.5 py-px text-[9px] font-semibold text-white">{{ __('Main') }}</span>
              @endif
            </button>
          @endforeach
        </div>
      @endif
    </div>

    <div>
      <p class="mb-3 text-[13px] font-semibold tracking-wide text-muted-soft uppercase">{{ __('Description') }}</p>

      @if ($item->description)
        <p class="max-w-2xl text-[15px] leading-relaxed text-muted">{{ $item->description }}</p>
      @else
        <p class="text-[15px] text-muted-soft">{{ __('No description.') }}</p>
      @endif
    </div>

    <div>
      <p class="mb-3.5 text-[13px] font-semibold tracking-wide text-muted-soft uppercase">{{ __('Details') }}</p>

      @if (! $hasDetails)
        <div class="rounded-xl border border-hairline">
          <x-empty-state data-test="no-item-details">
            <x-slot:icon>
              <x-lucide-list class="size-6 text-muted" />
            </x-slot>

            {{ __('This item has no type, so it has no custom fields to show.') }}
          </x-empty-state>
        </div>
      @else
        <div class="overflow-hidden rounded-xl border border-hairline">
          @if ($ungroupedFields->isNotEmpty())
            <div class="grid grid-cols-1 sm:grid-cols-2">
              @foreach ($ungroupedFields as $field)
                @include('app.items.partials._field', ['field' => $field])
              @endforeach
            </div>
          @endif

          @foreach ($fieldGroups as $group)
            <div class="border-y border-hairline bg-card px-4.5 py-2.5 text-xs font-semibold tracking-wide text-muted uppercase">{{ $group->name }}</div>

            <div class="grid grid-cols-1 sm:grid-cols-2">
              @foreach ($group->customFields as $field)
                @include('app.items.partials._field', ['field' => $field])
              @endforeach
            </div>
          @endforeach
        </div>
      @endif
    </div>
  </div>

  <div class="flex flex-col gap-5">
    <div class="rounded-xl border border-hairline px-4.5">
      @php
        $glance = [
            __('Type') => $item->catalogType?->name ?? '—',
            // A nested category means little on its own, so it is shown under its parent.
            __('Category') => $item->category
                ? ($item->category->parent ? $item->category->parent->name . ' › ' . $item->category->name : $item->category->name)
                : '—',
            __('Series') => $item->series?->name ?? '—',
            __('Set') => $item->set?->name ?? '—',
            __('Copies owned') => number_format($item->copies->count()),
            __('Est. value') => $totalEstimated > 0 ? $money($totalEstimated) : '—',
            __('Photos') => number_format($item->photos->count()),
        ];
      @endphp

      @foreach ($glance as $label => $value)
        <div class="flex items-center justify-between gap-4 py-3 @unless ($loop->last) border-b border-hairline @endunless">
          <span class="shrink-0 text-[13px] text-muted-soft">{{ $label }}</span>
          <span class="truncate text-[13px] font-semibold text-ink">{{ $value }}</span>
        </div>
      @endforeach
    </div>

    @if ($item->series)
      <div class="rounded-xl border border-hairline p-4.5">
        <div class="mb-2 flex items-center justify-between gap-3">
          <div class="flex items-center gap-2">
            <span class="size-2.5 shrink-0 rounded-sm bg-brand"></span>
            <p class="text-[13px] font-semibold text-ink">{{ __('Series') }}</p>
          </div>
          <span class="shrink-0 rounded-full bg-brand/10 px-2 py-0.5 text-[10px] font-semibold tracking-wide text-brand uppercase">{{ __('Account-wide') }}</span>
        </div>

        <p class="text-base font-semibold text-ink">{{ $item->series->name }}</p>

        @if ($item->series->description)
          <p class="mt-1 text-[13px] leading-relaxed text-muted">{{ $item->series->description }}</p>
        @endif

        <p class="mt-2.5 text-xs text-muted-soft" data-test="series-span">
          {{ __(':items across :collections', [
              'items' => trans_choice(':count item|:count items', $seriesItemCount, ['count' => $seriesItemCount]),
              'collections' => trans_choice(':count collection|:count collections', $seriesCatalogCount, ['count' => $seriesCatalogCount]),
          ]) }}
        </p>

        <a href="{{ route('series.show', $item->series_id) }}" data-turbo="true" class="mt-3.5 inline-block text-[13px] font-semibold text-ink transition-opacity hover:opacity-75">{{ __('View series →') }}</a>
      </div>
    @endif

    @if ($item->set)
      @php
        // A set without a target has nothing to be complete against, so it shows a count only.
        $setTarget = $item->set->target_count;
        $hasSetTarget = $setTarget !== null && $setTarget > 0;
        $setPercent = $hasSetTarget ? min(100, (int) round($setItemCount / $setTarget * 100)) : 0;
      @endphp

      <div class="rounded-xl border border-hairline p-4.5">
        <div class="mb-1.5 flex items-center justify-between gap-3">
          <p class="text-[13px] font-semibold text-ink">{{ __('Part of a set') }}</p>
          @if ($hasSetTarget)
            <span class="shrink-0 text-xs text-muted-soft" data-test="set-completion-ratio">{{ $setItemCount }}/{{ $setTarget }}</span>
          @endif
        </div>

        <p class="text-[13px] text-muted">{{ $item->set->name }}</p>

        @if ($hasSetTarget)
          <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-hairline-soft">
            <div class="h-full bg-brand" style="width: {{ $setPercent }}%"></div>
          </div>
        @else
          <p class="mt-3 text-[13px] text-muted-soft">
            {{ trans_choice(':count item in this set|:count items in this set', $setItemCount, ['count' => $setItemCount]) }}
          </p>
        @endif

        <a href="{{ route('sets.index', $item->catalog_id) }}" data-turbo="true" class="mt-3.5 inline-block text-[13px] font-semibold text-ink transition-opacity hover:opacity-75">{{ __('View set →') }}</a>
      </div>
    @endif

    <div class="flex flex-col gap-2 rounded-xl border border-hairline p-4.5 text-xs text-muted-soft">
      <p>{!! __('Added by :name', ['name' => '<span class="font-semibold text-ink">'.e($item->created_by_name ?? __('someone')).'</span>']) !!} · {{ $item->created_at->isoFormat('MMM D, YYYY') }}</p>
      <p>{!! __('Last edited by :name', ['name' => '<span class="font-semibold text-ink">'.e($item->updated_by_name ?? __('someone')).'</span>']) !!} · {{ $item->updated_at?->diffForHumans() }}</p>
    </div>
  </div>
</div>
