@php
  $dots = ['bg-badge-orange', 'bg-badge-violet', 'bg-badge-emerald', 'bg-brand', 'bg-badge-pink', 'bg-warning'];
@endphp

<x-app-layout>
  <x-slot:title>
    {{ $series->name }}
  </x-slot>

  <div class="px-6 py-8 lg:px-12 lg:py-10">
    <div class="mx-auto w-full max-w-4xl">
      <div class="mb-5 flex items-center gap-1.5 text-[13px]">
        <a href="{{ route('series.index') }}" data-turbo="true" class="font-medium text-muted-soft transition-colors hover:text-ink">{{ __('Series') }}</a>
        <span class="text-muted-soft">/</span>
        <span class="truncate font-medium text-ink">{{ $series->name }}</span>
      </div>

      <div class="flex items-start gap-4">
        <span class="flex size-13 shrink-0 items-center justify-center rounded-[13px] bg-brand/10">
          @svg('lucide-library', 'size-6 text-brand')
        </span>

        <div class="min-w-0 flex-1">
          <div class="flex flex-wrap items-center gap-2.5">
            <h1 class="text-[28px] font-semibold tracking-tight text-ink" data-test="series-title">{{ $series->name }}</h1>
            <span class="rounded-full bg-brand/10 px-2.5 py-0.5 text-[11px] font-semibold tracking-wide text-brand uppercase">{{ __('Account-wide') }}</span>
          </div>

          @if ($series->description)
            <p class="mt-1.5 max-w-2xl text-[15px] leading-relaxed text-muted">{{ $series->description }}</p>
          @endif
        </div>
      </div>

      <div class="mt-5 flex flex-wrap items-end justify-between gap-6 border-b border-hairline pb-6">
        <div class="flex gap-7">
          <div>
            <div class="text-[22px] font-semibold text-ink" data-test="series-item-count">{{ number_format($itemCount) }}</div>
            <div class="mt-0.5 text-xs text-muted-soft">{{ trans_choice('Linked item|Linked items', $itemCount) }}</div>
          </div>

          <div>
            <div class="text-[22px] font-semibold text-ink" data-test="series-collection-count">{{ number_format($catalogCount) }}</div>
            <div class="mt-0.5 text-xs text-muted-soft">{{ trans_choice('Collection|Collections', $catalogCount) }}</div>
          </div>
        </div>

        <div class="text-right text-xs text-muted-soft">
          <p>{!! __('Created by :name', ['name' => '<span class="font-semibold text-ink">'.e($series->created_by_name ?? __('someone')).'</span>']) !!}</p>
          <p class="mt-0.5">{{ __('Updated :time', ['time' => $series->updated_at?->diffForHumans() ?? '—']) }}</p>
        </div>
      </div>

      @if ($groups->isEmpty())
        <div class="mt-7 rounded-xl border border-hairline">
          <x-empty-state data-test="no-series-items">
            <x-slot:icon>
              <x-lucide-package class="size-6 text-muted" />
            </x-slot>

            {{ __('No items are linked to this series yet. Pick it from the series field when you add or edit an item.') }}
          </x-empty-state>
        </div>
      @else
        <div class="mt-7 flex flex-col gap-6">
          @foreach ($groups as $group)
            <div>
              <div class="mb-3 flex items-center gap-2.5">
                <span class="size-2.5 shrink-0 rounded-sm {{ $dots[$group['catalog']->id % count($dots)] }}"></span>
                <a href="{{ route('collections.show', $group['catalog']->id) }}" data-turbo="true" class="truncate text-[13px] font-semibold tracking-wide text-muted uppercase transition-colors hover:text-ink">{{ $group['catalog']->name }}</a>
                <span class="text-xs text-muted-soft">{{ count($group['items']) }}</span>
              </div>

              <div class="overflow-hidden rounded-xl border border-hairline">
                @foreach ($group['items'] as $item)
                  <a
                    href="{{ route('items.show', [$group['catalog']->id, $item->id]) }}"
                    data-turbo="true"
                    class="flex items-center gap-3.5 px-4.5 py-3.5 transition-colors hover:bg-card @unless ($loop->last) border-b border-hairline-soft @endunless"
                    data-test="series-item-{{ $item->id }}"
                  >
                    <span class="flex size-9.5 shrink-0 items-center justify-center rounded-lg bg-card text-lg">{{ $group['catalog']->emoji ?? '📦' }}</span>

                    <div class="min-w-0 flex-1">
                      <div class="truncate text-sm font-semibold text-ink">{{ $item->name }}</div>
                      @if ($item->description)
                        <div class="mt-0.5 truncate text-[13px] text-muted-soft">{{ $item->description }}</div>
                      @endif
                    </div>

                    @if ($item->catalogType)
                      <span class="shrink-0 rounded-full bg-card px-2.5 py-1 text-xs font-semibold text-muted">{{ $item->catalogType->name }}</span>
                    @endif
                  </a>
                @endforeach
              </div>
            </div>
          @endforeach
        </div>
      @endif
    </div>
  </div>
</x-app-layout>
