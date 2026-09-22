@php
  use App\Helpers\Money;

  $money = fn (int $cents): string => Money::format($cents, $catalog->currency);

  // The series colours cycle, so a chart never runs out of them however many
  // categories or locations it has to draw.
  $palette = ['#3b82f6', '#8b5cf6', '#34d399', '#fb923c', '#ec4899'];
  $colour = fn (int $index): string => $palette[$index % count($palette)];

  // The value line, drawn over a 640 x 220 box. The top of the curve is kept
  // just under the ceiling so the peak does not sit flat against the edge.
  $series = array_column($valueOverTime, 'value');
  $ceiling = max($series) > 0 ? max($series) * 1.08 : 1;
  $points = [];

  foreach ($series as $index => $value) {
      $x = count($series) > 1 ? ($index / (count($series) - 1)) * 640 : 0;
      $y = 208 - ($value / $ceiling) * 196;
      $points[] = round($x, 1) . ',' . round($y, 1);
  }

  $line = implode(' ', $points);

  $acquisitionPeak = max(array_column($acquisitions, 'count')) ?: 1;
  $conditionPeak = $conditions === [] ? 1 : max(array_column($conditions, 'count'));
  $locationPeak = $locations === [] ? 1 : max(array_column($locations, 'value'));

  // The donut is one conic gradient, each category taking the share of the turn
  // its items are worth.
  $categoryTotal = array_sum(array_column($categories, 'count'));
  $stops = [];
  $offset = 0;

  foreach ($categories as $index => $category) {
      $start = $categoryTotal === 0 ? 0 : ($offset / $categoryTotal) * 360;
      $offset += $category['count'];
      $end = $categoryTotal === 0 ? 0 : ($offset / $categoryTotal) * 360;
      $stops[] = $colour($index) . ' ' . round($start, 2) . 'deg ' . round($end, 2) . 'deg';
  }

  $donut = $stops === [] ? 'var(--color-card)' : 'conic-gradient(' . implode(', ', $stops) . ')';
@endphp

<x-app-layout :catalog="$catalog">
  <x-slot:title>
    {{ __('Statistics') }}
  </x-slot>

  <div class="px-6 py-8 lg:px-12 lg:py-10">
    <div class="mx-auto w-full max-w-6xl">
      <div class="mb-5 flex items-center gap-1.5 text-[13px]">
        <a href="{{ route('collections.index') }}" data-turbo="true" class="font-medium text-muted-soft transition-colors hover:text-ink">{{ __('Collections') }}</a>
        <span class="text-muted-soft">/</span>
        <a href="{{ route('collections.show', $catalog->id) }}" data-turbo="true" class="truncate font-medium text-muted-soft transition-colors hover:text-ink">{{ $catalog->name }}</a>
        <span class="text-muted-soft">/</span>
        <span class="font-medium text-ink">{{ __('Statistics') }}</span>
      </div>

      <div class="mb-7">
        <h1 class="text-[28px] font-semibold tracking-tight text-ink">{{ __('Statistics') }}</h1>
        <p class="mt-1 max-w-xl text-[15px] text-muted">{{ __('What this collection holds, what it is worth, and how it has grown over the last twelve months.') }}</p>
      </div>

      @if ($totals['items'] === 0)
        <div class="flex flex-col items-center rounded-xl border border-hairline px-6 py-14 text-center" data-test="no-statistics">
          <div class="mb-5 flex size-16 items-center justify-center rounded-xl bg-card">
            <x-lucide-chart-no-axes-column class="size-7 text-ink" />
          </div>

          <p class="text-[21px] font-semibold tracking-tight text-ink">{{ __('Nothing to measure yet') }}</p>
          <p class="mt-2.5 max-w-[460px] text-[15px] leading-relaxed text-muted">
            {{ __('Add a few items, then record what each copy cost, the shape it is in and where you keep it. The charts fill themselves in from there.') }}
          </p>

          <x-button :href="route('collections.show', $catalog->id)" class="mt-7" data-test="add-first-item-button">
            {{ __('Go to the items') }}
          </x-button>
        </div>
      @else
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4" data-test="statistics-kpis">
          @foreach ([
              ['label' => __('Total items'), 'value' => number_format($totals['items']), 'note' => __('+:count added this month', ['count' => $totals['itemsAddedThisMonth']]), 'dot' => 'bg-brand'],
              ['label' => __('Estimated value'), 'value' => $money($totals['value']), 'note' => __(':amount acquired this month', ['amount' => $money($totals['valueAddedThisMonth'])]), 'dot' => 'bg-badge-violet'],
              ['label' => __('Average per item'), 'value' => $money($totals['average']), 'note' => trans_choice('across :count copy|across :count copies', $totals['copies'], ['count' => $totals['copies']]), 'dot' => 'bg-badge-emerald'],
              ['label' => __('Sets completion'), 'value' => $sets === null ? '—' : $sets['percentage'] . '%', 'note' => $sets === null ? __('No set has a target yet') : trans_choice(':count item to go|:count items to go', $sets['remaining'], ['count' => $sets['remaining']]), 'dot' => 'bg-badge-orange'],
          ] as $kpi)
            <div class="flex flex-col gap-1.5 rounded-xl bg-card p-5">
              <div class="flex items-center gap-2">
                <span class="size-2.5 shrink-0 rounded-sm {{ $kpi['dot'] }}"></span>
                <span class="text-[13px] font-medium text-muted">{{ $kpi['label'] }}</span>
              </div>
              <div x-data="spinningCounter" class="text-[28px] leading-9 font-semibold tracking-tight text-ink">{{ $kpi['value'] }}</div>
              <div class="text-[13px] font-medium text-muted-soft">{{ $kpi['note'] }}</div>
            </div>
          @endforeach
        </div>

        <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-[1.7fr_1fr]">
          <div class="rounded-xl border border-hairline bg-canvas p-6" data-test="value-over-time">
            <div class="mb-4 flex items-start justify-between gap-4">
              <div>
                <div class="text-base font-semibold text-ink">{{ __('Estimated value over time') }}</div>
                <p class="mt-0.5 text-[13px] text-muted">{{ __('Running total, by the date each copy was acquired.') }}</p>
              </div>
              <div class="shrink-0 text-right">
                <div class="text-[22px] font-semibold tracking-tight text-ink">{{ $money(end($series) ?: 0) }}</div>
              </div>
            </div>

            <svg viewBox="0 0 640 220" width="100%" height="220" preserveAspectRatio="none" class="block" role="img" aria-label="{{ __('Estimated value over time') }}">
              <defs>
                <linearGradient id="value-fill" x1="0" y1="0" x2="0" y2="1">
                  <stop offset="0%" stop-color="#3b82f6" stop-opacity="0.22" />
                  <stop offset="100%" stop-color="#3b82f6" stop-opacity="0" />
                </linearGradient>
              </defs>

              @foreach ([55, 110, 165] as $gridline)
                <line x1="0" y1="{{ $gridline }}" x2="640" y2="{{ $gridline }}" class="stroke-hairline" stroke-width="1" />
              @endforeach

              <polygon points="0,220 {{ $line }} 640,220" fill="url(#value-fill)" />
              <polyline points="{{ $line }}" fill="none" stroke="#3b82f6" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round" />
            </svg>

            <div class="mt-2.5 flex justify-between">
              @foreach ($valueOverTime as $month)
                <span class="font-mono text-[11px] text-muted-soft">{{ $month['label'] }}</span>
              @endforeach
            </div>

            @if ($totals['undatedCopies'] > 0)
              <p class="mt-4 text-[13px] text-muted-soft" data-test="undated-copies">
                {{ trans_choice(':count copy has no acquisition date and is left out of the two charts over time.|:count copies have no acquisition date and are left out of the two charts over time.', $totals['undatedCopies'], ['count' => $totals['undatedCopies']]) }}
              </p>
            @endif
          </div>

          <div class="flex flex-col rounded-xl border border-hairline bg-canvas p-6" data-test="items-by-category">
            <div class="text-base font-semibold text-ink">{{ __('Items by category') }}</div>
            <p class="mt-0.5 mb-5 text-[13px] text-muted">
              {{ trans_choice(':count item across :groups group|:count items across :groups groups', $totals['items'], ['count' => $totals['items'], 'groups' => count($categories)]) }}
            </p>

            <div class="flex flex-1 items-center gap-6">
              <div class="relative size-[130px] shrink-0">
                <div class="size-[130px] rounded-full" style="background: {{ $donut }}"></div>
                <div class="absolute inset-[22px] flex flex-col items-center justify-center rounded-full bg-canvas">
                  <div class="text-[22px] font-semibold tracking-tight text-ink">{{ number_format($totals['items']) }}</div>
                  <div class="text-[11px] text-muted-soft">{{ trans_choice('item|items', $totals['items']) }}</div>
                </div>
              </div>

              <div class="flex flex-1 flex-col gap-3">
                @foreach ($categories as $index => $category)
                  <div class="flex items-center gap-2.5">
                    <span class="size-2.5 shrink-0 rounded-sm" style="background: {{ $colour($index) }}"></span>
                    <span class="min-w-0 flex-1 truncate text-[13px] font-medium text-ink">
                      {{ $category['label'] ?? ($category['other'] ? __('Other categories') : __('No category')) }}
                    </span>
                    <span class="text-[13px] font-semibold text-ink">{{ $category['count'] }}</span>
                    <span class="w-9 text-right text-xs text-muted-soft">{{ $category['percentage'] }}%</span>
                  </div>
                @endforeach
              </div>
            </div>
          </div>
        </div>

        <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-[1.7fr_1fr]">
          <div class="rounded-xl border border-hairline bg-canvas p-6" data-test="acquisitions-per-month">
            <div class="mb-5 flex items-start justify-between gap-4">
              <div>
                <div class="text-base font-semibold text-ink">{{ __('Acquisitions per month') }}</div>
                <p class="mt-0.5 text-[13px] text-muted">{{ __('Copies acquired over the last twelve months.') }}</p>
              </div>
              <div class="flex shrink-0 items-center gap-1.5 text-xs text-muted">
                <span class="size-2 rounded-xs bg-badge-violet"></span>
                {{ __('Acquired') }}
              </div>
            </div>

            <div class="flex h-[180px] items-end gap-2.5">
              @foreach ($acquisitions as $index => $month)
                <div class="flex h-full flex-1 flex-col items-center justify-end gap-2">
                  <span class="text-[11px] font-semibold text-muted">{{ $month['count'] }}</span>
                  <div
                    class="w-full rounded-t-md {{ $index === count($acquisitions) - 1 ? 'bg-badge-violet' : 'bg-badge-violet/30' }}"
                    style="height: {{ max(6, (int) round(($month['count'] / $acquisitionPeak) * 150)) }}px"
                  ></div>
                  <span class="font-mono text-[11px] text-muted-soft">{{ $month['label'] }}</span>
                </div>
              @endforeach
            </div>
          </div>

          <div class="rounded-xl border border-hairline bg-canvas p-6" data-test="copies-by-condition">
            <div class="text-base font-semibold text-ink">{{ __('Condition') }}</div>
            <p class="mt-0.5 mb-5 text-[13px] text-muted">{{ __('The shape the copies are in.') }}</p>

            <div class="flex flex-col gap-4">
              @foreach ($conditions as $index => $condition)
                <div>
                  <div class="mb-1.5 flex justify-between text-[13px]">
                    <span class="truncate pr-2 font-medium text-ink">{{ $condition['label'] ?? __('No condition') }}</span>
                    <span class="shrink-0 text-muted">{{ $condition['count'] }}</span>
                  </div>
                  <div class="h-2 overflow-hidden rounded-full bg-card">
                    <div class="h-full rounded-full" style="width: {{ round(($condition['count'] / $conditionPeak) * 100) }}%; background: {{ $colour($index) }}"></div>
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        </div>

        <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2">
          <div class="rounded-xl border border-hairline bg-canvas p-6" data-test="value-by-location">
            <div class="text-base font-semibold text-ink">{{ __('Value by location') }}</div>
            <p class="mt-0.5 mb-5 text-[13px] text-muted">{{ __('Where the value sits.') }}</p>

            @if ($locations === [])
              <p class="py-6 text-center text-sm text-muted">{{ __('No copy carries an estimated value yet.') }}</p>
            @else
              <div class="flex flex-col gap-4">
                @foreach ($locations as $index => $location)
                  <div class="flex items-center gap-3.5">
                    <span class="w-[72px] shrink-0 truncate text-[13px] font-medium text-ink">{{ $location['label'] ?? __('No location') }}</span>
                    <div class="h-2.5 min-w-20 flex-1 overflow-hidden rounded-full bg-card">
                      <div class="h-full rounded-full" style="width: {{ round(($location['value'] / $locationPeak) * 100) }}%; background: {{ $colour($index) }}"></div>
                    </div>
                    <span class="shrink-0 text-right text-[13px] font-semibold text-ink">{{ $money($location['value']) }}</span>
                  </div>
                @endforeach
              </div>
            @endif
          </div>

          <div class="rounded-xl border border-hairline bg-canvas p-6" data-test="top-items">
            <div class="mb-4 flex items-center justify-between gap-4">
              <div>
                <div class="text-base font-semibold text-ink">{{ __('Top items by value') }}</div>
                <p class="mt-0.5 text-[13px] text-muted">{{ __('Your most valuable pieces.') }}</p>
              </div>
              <a href="{{ route('collections.show', $catalog->id) }}" data-turbo="true" class="shrink-0 text-[13px] font-semibold text-ink transition-colors hover:text-muted">{{ __('View all') }}</a>
            </div>

            @if ($topItems === [])
              <p class="py-6 text-center text-sm text-muted">{{ __('No copy carries an estimated value yet.') }}</p>
            @else
              <div class="flex flex-col">
                @foreach ($topItems as $index => $row)
                  <a
                    href="{{ route('items.show', [$catalog->id, $row['item']->id]) }}"
                    data-turbo="true"
                    class="flex items-center gap-3 border-b border-hairline-soft py-3 last:border-b-0"
                    data-test="top-item-{{ $row['item']->id }}"
                  >
                    <span class="w-5 shrink-0 text-center text-[13px] font-semibold text-muted-soft">{{ $index + 1 }}</span>
                    <div class="min-w-0 flex-1">
                      <div class="truncate text-sm font-semibold text-ink">{{ $row['item']->name }}</div>
                      <div class="truncate text-xs text-muted-soft">
                        {{ collect([$row['condition'], $row['location']])->filter()->join(' · ') ?: __('No condition or location recorded') }}
                      </div>
                    </div>
                    <span class="shrink-0 text-sm font-semibold text-ink">{{ $money($row['value']) }}</span>
                  </a>
                @endforeach
              </div>
            @endif
          </div>
        </div>
      @endif
    </div>
  </div>
</x-app-layout>
