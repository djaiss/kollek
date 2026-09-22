@use('App\Enums\DashboardSectionEnum')
@use('App\Helpers\Money')

@php
    // The dashboard reads across the whole account, so every amount reads in the
    // currency of the account rather than in the one a collection names for itself.
    $money = fn (int $cents): string => Money::format($cents, $currency);

    // Every block is rendered; the ones this user turned off start hidden. Doing that
    // server side rather than with x-cloak keeps the visible blocks from flashing while
    // Alpine boots.
    $hides = fn (DashboardSectionEnum $section): bool => in_array($section->value, $hiddenSections, true);

    $kpis = [
        [
            'label' => __('Collections'),
            'value' => number_format($totals['collections']),
            'note' => __('across your account'),
            'dot' => 'bg-badge-violet',
        ],
        [
            'label' => __('Items'),
            'value' => number_format($totals['items']),
            'note' => __('+:count added this month', ['count' => $totals['itemsAddedThisMonth']]),
            'dot' => 'bg-brand',
        ],
        [
            'label' => __('Copies'),
            'value' => number_format($totals['copies']),
            'note' => __('physical pieces tracked'),
            'dot' => 'bg-badge-emerald',
        ],
        [
            'label' => __('Estimated value'),
            'value' => $money($totals['value']),
            'note' => trans_choice('across :count valued copy|across :count valued copies', $totals['valuedCopies'], ['count' => $totals['valuedCopies']]),
            'dot' => 'bg-success',
        ],
        [
            'label' => __('Added this month'),
            'value' => $money($totals['valueAddedThisMonth']),
            'note' => __('what came in this month is worth'),
            'dot' => 'bg-badge-orange',
        ],
        [
            'label' => __('Average per item'),
            'value' => $money($totals['average']),
            'note' => __('across the whole account'),
            'dot' => 'bg-badge-pink',
        ],
    ];

    $loanStats = [
        ['label' => __('Lent out'), 'value' => $loans['outgoing'], 'class' => 'text-ink'],
        ['label' => __('Borrowed in'), 'value' => $loans['incoming'], 'class' => 'text-ink'],
        ['label' => __('Overdue'), 'value' => $loans['overdue'], 'class' => $loans['overdue'] > 0 ? 'text-error' : 'text-ink'],
        ['label' => __('Due soon'), 'value' => $loans['dueSoon'], 'class' => $loans['dueSoon'] > 0 ? 'text-warning' : 'text-ink'],
        ['label' => __('Planned'), 'value' => $loans['planned'], 'class' => 'text-ink'],
        ['label' => __('Returned'), 'value' => $loans['returned'], 'class' => 'text-muted'],
    ];

    // The bars are drawn against the biggest location rather than against the whole,
    // so the top one always fills the track and the rest read as shares of it.
    $locationPeak = $locations === [] ? 1 : max(array_column($locations, 'value'));
    $locationColours = ['bg-brand', 'bg-badge-violet', 'bg-badge-pink', 'bg-badge-emerald', 'bg-badge-orange'];
@endphp

<x-app-layout>
  <x-slot:title>
    {{ __('Dashboard') }}
  </x-slot>

  <div
    class="px-6 py-8 lg:px-12 lg:py-10"
    x-data="{
        hidden: @js($hiddenSections),
        customizeOpen: false,
        shows(section) { return ! this.hidden.includes(section); },
        toggle(section) {
            this.hidden = this.shows(section)
                ? [...this.hidden, section]
                : this.hidden.filter((key) => key !== section);
            saveDashboardSections(this.hidden);
        },
        reset() {
            this.hidden = [];
            saveDashboardSections(this.hidden);
        },
    }"
  >
    <input type="hidden" id="dashboard-sections-endpoint" value="{{ route('dashboard.sections.update') }}" />

    <div class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
      <div class="min-w-0">
        <h1 class="text-[28px] font-semibold tracking-tight text-ink">{{ $greeting }}, {{ $firstName }}</h1>
        <p class="mt-1 text-[15px] text-muted">
          @if ($loans['overdue'] > 0)
            {{ trans_choice('Here\'s what\'s happening across your account. :count loan is overdue.|Here\'s what\'s happening across your account. :count loans are overdue.', $loans['overdue'], ['count' => $loans['overdue']]) }}
          @else
            {{ __("Here's what's happening across your account.") }}
          @endif
        </p>
      </div>

      <div class="flex shrink-0 items-center gap-2">
        <div class="relative">
          <button
            type="button"
            @click="customizeOpen = ! customizeOpen"
            class="flex h-10 cursor-pointer items-center gap-2 rounded-md px-3 text-[13px] font-medium text-muted transition-colors hover:bg-card hover:text-ink"
            data-test="customize-dashboard"
          >
            <x-lucide-sliders-horizontal class="size-4" />
            {{ __('Customize') }}
          </button>

          <div
            x-show="customizeOpen"
            x-cloak
            @click.outside="customizeOpen = false"
            x-transition.opacity
            class="absolute top-12 right-0 z-20 w-64 rounded-xl border border-hairline bg-canvas p-1.5 shadow-lg"
          >
            <div class="flex items-center justify-between px-2.5 py-2">
              <span class="text-[13px] font-semibold text-body">{{ __('Show on dashboard') }}</span>
              <button type="button" @click="reset()" class="cursor-pointer text-xs font-semibold text-muted-soft transition-colors hover:text-ink">{{ __('Reset') }}</button>
            </div>

            @foreach ($sections as $section)
              <button
                type="button"
                @click="toggle(@js($section->value))"
                class="flex w-full cursor-pointer items-center gap-3 rounded-lg px-2.5 py-2 text-left transition-colors hover:bg-card"
                data-test="toggle-section-{{ $section->value }}"
              >
                <span class="flex-1 text-[13px] font-medium" :class="shows(@js($section->value)) ? 'text-ink' : 'text-muted-soft'">{{ $section->label() }}</span>
                <span class="relative h-[18px] w-8 shrink-0 rounded-full transition-colors" :class="shows(@js($section->value)) ? 'bg-ink' : 'bg-muted-soft'">
                  <span class="absolute top-0.5 size-3.5 rounded-full bg-page shadow-sm transition-all" :class="shows(@js($section->value)) ? 'left-4' : 'left-0.5'"></span>
                </span>
              </button>
            @endforeach
          </div>
        </div>

        <x-button href="{{ route('collections.new') }}" turbo="true">
          <x-slot:icon>
            <x-lucide-plus class="size-4" />
          </x-slot>
          {{ __('New collection') }}
        </x-button>
      </div>
    </div>

    <div x-show="shows(@js(DashboardSectionEnum::Summary->value))" @style(['display: none' => $hides(DashboardSectionEnum::Summary)]) class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-3 xl:grid-cols-6" data-test="dashboard-summary">
      @foreach ($kpis as $kpi)
        <div class="flex flex-col gap-1.5 rounded-xl border border-hairline bg-canvas p-4">
          <div class="flex items-center gap-2">
            <span class="size-2 shrink-0 rounded-sm {{ $kpi['dot'] }}"></span>
            <span class="text-xs font-semibold text-muted">{{ $kpi['label'] }}</span>
          </div>
          <div class="text-[26px] leading-8 font-semibold tracking-tight text-ink">{{ $kpi['value'] }}</div>
          <div class="text-xs font-medium text-muted-soft">{{ $kpi['note'] }}</div>
        </div>
      @endforeach
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-[1.6fr_1fr] lg:items-start">
      <div class="flex flex-col gap-6">
        <div x-show="shows(@js(DashboardSectionEnum::Additions->value))" @style(['display: none' => $hides(DashboardSectionEnum::Additions)]) class="overflow-hidden rounded-xl border border-hairline bg-canvas" data-test="dashboard-additions">
          <div class="flex items-center gap-3 border-b border-hairline-soft px-5 py-4">
            <span class="size-2.5 shrink-0 rounded-full bg-brand"></span>
            <h2 class="text-base font-semibold text-ink">{{ __('Recent additions') }}</h2>
            <span class="hidden text-[13px] text-muted-soft sm:inline">{{ __('the newest things you catalogued') }}</span>
            <a href="{{ route('collections.index') }}" data-turbo="true" class="ml-auto shrink-0 text-[13px] font-semibold text-body transition-colors hover:text-ink">{{ __('View all') }} &rarr;</a>
          </div>

          @forelse ($recentAdditions as $row)
            @php($item = $row['item'])
            <a
              href="{{ route('items.show', [$item->catalog_id, $item->id]) }}"
              data-turbo="true"
              class="flex items-center gap-4 border-b border-hairline-soft px-5 py-3.5 transition-colors last:border-b-0 hover:bg-card"
              data-test="addition-{{ $item->id }}"
            >
              @if ($item->mainPhoto)
                <img src="{{ $item->mainPhoto->url() }}" alt="{{ $item->name }}" loading="lazy" class="h-16 w-12 shrink-0 rounded-lg object-cover" />
              @else
                <div class="flex h-16 w-12 shrink-0 items-center justify-center rounded-lg bg-card text-xl">{{ $item->catalog->emoji ?? '📦' }}</div>
              @endif

              <div class="min-w-0 flex-1">
                <div class="truncate text-[15px] font-semibold text-ink">{{ $item->name }}</div>
                <div class="mt-0.5 truncate text-xs text-muted-soft">
                  {{ collect([$item->catalog->name, $row['condition'], $row['location']])->filter()->implode(' · ') }}
                </div>
              </div>

              <div class="shrink-0 text-right">
                <div class="text-[13px] font-semibold text-ink">{{ trans_choice(':count copy|:count copies', $row['copies'], ['count' => $row['copies']]) }}</div>
                <div class="mt-0.5 text-xs text-muted-soft">{{ $item->created_at->diffForHumans() }}</div>
              </div>
            </a>
          @empty
            <x-empty-state>
              <x-slot:icon>
                <x-lucide-package class="size-6 text-muted" />
              </x-slot>
              {{ __('Nothing catalogued yet. The items you add show up here, newest first.') }}
            </x-empty-state>
          @endforelse
        </div>

        <div x-show="shows(@js(DashboardSectionEnum::Collections->value))" @style(['display: none' => $hides(DashboardSectionEnum::Collections)]) data-test="dashboard-collections">
          <div class="mb-3.5 flex items-center justify-between">
            <h2 class="text-base font-semibold text-ink">{{ __('Your collections') }}</h2>
            <a href="{{ route('collections.index') }}" data-turbo="true" class="text-[13px] font-semibold text-body transition-colors hover:text-ink">{{ __('All collections') }} &rarr;</a>
          </div>

          <div class="grid grid-cols-1 gap-3.5 sm:grid-cols-2">
            @foreach ($collections as $row)
              @php($catalog = $row['catalog'])
              <a
                href="{{ route('collections.show', $catalog->id) }}"
                data-turbo="true"
                class="flex flex-col gap-3.5 rounded-xl border border-hairline bg-canvas p-4 transition-colors hover:bg-card"
                data-test="collection-card-{{ $catalog->id }}"
              >
                <div class="flex items-start gap-3">
                  <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-card text-2xl">{{ $catalog->emoji ?? '📦' }}</div>
                  <div class="min-w-0 flex-1">
                    <div class="truncate text-[15px] font-semibold text-ink">{{ $catalog->name }}</div>
                    <div class="mt-1 flex items-center gap-2">
                      <x-badge class="px-2 py-0.5 text-[11px]">{{ __(ucfirst($catalog->visibility->value)) }}</x-badge>
                      <span class="truncate text-xs text-muted-soft">{{ $catalog->updated_at?->diffForHumans() }}</span>
                    </div>
                  </div>
                </div>

                <div class="flex items-end justify-between gap-3 border-t border-hairline-soft pt-3">
                  <div class="flex gap-5">
                    <div>
                      <div class="text-xs text-muted-soft">{{ __('Items') }}</div>
                      <div class="mt-0.5 text-[15px] font-semibold text-ink">{{ number_format($row['items']) }}</div>
                    </div>
                    <div>
                      <div class="text-xs text-muted-soft">{{ __('Copies') }}</div>
                      <div class="mt-0.5 text-[15px] font-semibold text-ink">{{ number_format($row['copies']) }}</div>
                    </div>
                  </div>
                  <div class="text-right">
                    <div class="text-xs text-muted-soft">{{ __('Estimated value') }}</div>
                    <div class="mt-0.5 text-[15px] font-semibold text-ink">{{ $row['value'] > 0 ? $money($row['value']) : '—' }}</div>
                  </div>
                </div>
              </a>
            @endforeach
          </div>
        </div>
      </div>

      <div class="flex flex-col gap-6">
        <div x-show="shows(@js(DashboardSectionEnum::Loans->value))" @style(['display: none' => $hides(DashboardSectionEnum::Loans)]) class="overflow-hidden rounded-xl border border-hairline bg-canvas" data-test="dashboard-loans">
          <div class="flex items-center justify-between border-b border-hairline-soft px-5 py-4">
            <h2 class="text-[15px] font-semibold text-ink">{{ __('Loan snapshot') }}</h2>
            <a href="{{ route('loans.index') }}" data-turbo="true" class="text-[13px] font-semibold text-body transition-colors hover:text-ink">{{ __('Open loans') }} &rarr;</a>
          </div>

          <div class="grid grid-cols-3 gap-px bg-hairline-soft">
            @foreach ($loanStats as $stat)
              <div class="bg-canvas px-4 py-3.5">
                <div class="text-[22px] font-semibold tracking-tight {{ $stat['class'] }}">{{ number_format($stat['value']) }}</div>
                <div class="mt-0.5 text-xs text-muted">{{ $stat['label'] }}</div>
              </div>
            @endforeach
          </div>

          @if ($loans['deposits'] !== [])
            <div class="flex items-center justify-between gap-3 border-t border-hairline-soft px-5 py-3.5">
              <span class="text-[13px] text-muted">{{ __('Deposits across open loans') }}</span>
              <span class="text-[15px] font-semibold text-ink">{{ collect($loans['deposits'])->map(fn (int $cents, string $code): string => Money::format($cents, $code))->join(' + ') }}</span>
            </div>
          @endif
        </div>

        <div x-show="shows(@js(DashboardSectionEnum::Locations->value))" @style(['display: none' => $hides(DashboardSectionEnum::Locations)]) class="rounded-xl border border-hairline bg-canvas p-5" data-test="dashboard-locations">
          <h2 class="text-[15px] font-semibold text-ink">{{ __('Where things are') }}</h2>
          <p class="mt-0.5 mb-4 text-[13px] text-muted">{{ __('Estimated value by location.') }}</p>

          @if ($locations === [])
            <p class="text-[13px] text-muted-soft">{{ __('Nothing is valued and shelved yet. Record what a copy is worth and where you keep it, and it shows up here.') }}</p>
          @else
            <div class="flex flex-col gap-3.5">
              @foreach ($locations as $index => $location)
                <div class="flex items-center gap-3">
                  <div class="w-22 shrink-0 truncate text-[13px] font-medium text-body">{{ $location['label'] ?? __('No location') }}</div>
                  <div class="h-2 min-w-10 flex-1 overflow-hidden rounded-full bg-card">
                    <div class="h-full rounded-full {{ $locationColours[$index % count($locationColours)] }}" style="width: {{ round(($location['value'] / $locationPeak) * 100) }}%"></div>
                  </div>
                  <div class="w-16 shrink-0 text-right text-[13px] font-semibold text-ink">{{ $money($location['value']) }}</div>
                </div>
              @endforeach
            </div>
          @endif
        </div>

        <div x-show="shows(@js(DashboardSectionEnum::Activity->value))" @style(['display: none' => $hides(DashboardSectionEnum::Activity)]) class="overflow-hidden rounded-xl border border-hairline bg-canvas" data-test="dashboard-activity">
          <div class="flex items-center justify-between border-b border-hairline-soft px-5 py-4">
            <h2 class="text-[15px] font-semibold text-ink">{{ __('Account activity') }}</h2>
            <a href="{{ route('profile.logs.index') }}" data-turbo="true" class="text-[13px] font-semibold text-body transition-colors hover:text-ink">{{ __('View all') }} &rarr;</a>
          </div>

          @forelse ($activity as $entry)
            <div class="flex gap-3 border-b border-hairline-soft px-5 py-3.5 last:border-b-0">
              <x-avatar :user="$entry->user" :name="$entry->name" :size="32" class="size-7 text-xs" />
              <div class="min-w-0">
                <p class="text-[13px] leading-snug text-body">
                  <span class="font-semibold text-ink">{{ $entry->name }}</span>
                  {{ $entry->description }}
                </p>
                <p class="mt-0.5 text-xs text-muted-soft">{{ $entry->createdAtHuman }}</p>
              </div>
            </div>
          @empty
            <x-empty-state>
              <x-slot:icon>
                <x-lucide-activity class="size-6 text-muted" />
              </x-slot>
              {{ __('No activity yet.') }}
            </x-empty-state>
          @endforelse
        </div>
      </div>
    </div>
  </div>
</x-app-layout>
