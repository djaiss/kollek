@use('App\Enums\LoanDirection')
@use('App\Enums\LoanStatus')
@use('App\Helpers\Money')

<x-app-layout>
  <x-slot:title>
    {{ __('Loans') }}
  </x-slot>

  @php
    $slug = $direction->slug();
    $isOut = $direction === LoanDirection::Outgoing;

    // Build a section url for a tab, carrying the current filters along.
    $tabUrl = fn (string $key): string => route('loans.show', ['direction' => $slug, 'tab' => $key]);

    $formatDeposits = function (array $totals): string {
        if ($totals === []) {
            return Money::format(0, null);
        }

        return collect($totals)->map(fn (int $cents, string $code): string => Money::format($cents, $code))->join(' + ');
    };

    $depositTileLabel = $isOut ? __('Deposits held') : __('Deposits owed');

    $tiles = [
        ['label' => __('Active'), 'value' => $stats['active'], 'sub' => __('currently out'), 'dot' => 'bg-badge-emerald', 'url' => $tabUrl('all').'?status=active'],
        ['label' => __('Planned'), 'value' => $stats['planned'], 'sub' => __('not yet started'), 'dot' => 'bg-badge-orange', 'url' => $tabUrl('all').'?status=planned'],
        ['label' => __('Due soon'), 'value' => $stats['dueSoon'], 'sub' => __('within 30 days'), 'dot' => 'bg-badge-orange', 'url' => $tabUrl('due')],
        ['label' => __('Overdue'), 'value' => $stats['overdue'], 'sub' => __('past due date'), 'dot' => 'bg-error', 'url' => $tabUrl('due')],
        ['label' => __('Returned'), 'value' => $stats['returned'], 'sub' => __('back in hand'), 'dot' => 'bg-muted', 'url' => $tabUrl('all').'?status=returned'],
        ['label' => $depositTileLabel, 'value' => $formatDeposits($stats['deposits']), 'sub' => __('open loans'), 'dot' => 'bg-info', 'url' => $tabUrl('deposits')],
    ];

    $tabs = [
        'all' => __('All loans'),
        'due' => __('Due & overdue'),
        'risk' => __('Risk & exceptions'),
        'by-party' => __('By party'),
        'deposits' => __('Deposits'),
        'timeline' => __('Timeline'),
    ];
  @endphp

  <div class="px-6 py-8 lg:px-10 lg:py-10">
    <div class="mx-auto w-full max-w-6xl">
      <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
          <p class="text-[11px] font-semibold tracking-wide text-muted-soft uppercase">{{ __('Custody workflows') }}</p>
          <div class="mt-1 flex items-center gap-2">
            <h1 class="text-[28px] font-semibold tracking-tight text-ink">{{ __('Loans') }}</h1>
            <x-help id="loans.list" />
          </div>
          <p class="mt-1 max-w-xl text-[15px] text-muted">{{ __('A loan moves custody, not ownership. You still own what you lend out.') }}</p>
        </div>

        <div class="flex items-center gap-2.5">
          <x-button.secondary :href="route('loans.export.show', ['direction' => $slug])" data-turbo="false">
            <x-slot:icon>
              <x-lucide-download class="size-4" />
            </x-slot>
            {{ __('Export what\'s out') }}
          </x-button.secondary>

          <x-button :href="route('loans.new', ['direction' => $slug])" data-test="new-loan-button">
            <x-slot:icon>
              <x-lucide-plus class="size-4" />
            </x-slot>
            {{ __('New loan') }}
          </x-button>
        </div>
      </div>

      <div class="mb-6 inline-flex rounded-lg border border-hairline bg-canvas p-1">
        @foreach (LoanDirection::cases() as $case)
          <a
            href="{{ route('loans.show', ['direction' => $case->slug(), 'tab' => $tab]) }}"
            data-turbo="true"
            class="flex items-center gap-2 rounded-md px-4 py-1.5 text-sm font-medium transition-colors {{ $direction === $case ? 'bg-card text-ink shadow-xs' : 'text-muted hover:text-ink' }}"
            data-test="direction-{{ $case->slug() }}"
          >
            @svg($case === LoanDirection::Outgoing ? 'lucide-arrow-up-right' : 'lucide-arrow-down-left', 'size-4')
            {{ $case->label() }}
          </a>
        @endforeach
      </div>

      <div class="mb-6 grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-6">
        @foreach ($tiles as $tile)
          <a href="{{ $tile['url'] }}" data-turbo="true" class="rounded-xl border border-hairline bg-canvas p-4 transition-colors hover:border-muted-soft">
            <div class="flex items-center gap-1.5">
              <span class="size-2 rounded-full {{ $tile['dot'] }}"></span>
              <span class="text-[12px] font-medium text-muted">{{ $tile['label'] }}</span>
            </div>
            <div class="mt-1.5 truncate text-2xl font-semibold text-ink">{{ $tile['value'] }}</div>
            <div class="text-[11px] text-muted-soft">{{ $tile['sub'] }}</div>
          </a>
        @endforeach
      </div>

      <div class="mb-5 flex flex-wrap gap-1 border-b border-hairline">
        @foreach ($tabs as $key => $label)
          <a
            href="{{ $tabUrl($key) }}"
            data-turbo="true"
            class="border-b-2 px-3 py-2 text-sm font-medium transition-colors {{ $tab === $key ? 'border-ink text-ink' : 'border-transparent text-muted hover:text-ink' }}"
            data-test="tab-{{ $key }}"
          >{{ $label }}</a>
        @endforeach
      </div>

      <div>
        @include('app.loans.partials._tab'.\Illuminate\Support\Str::studly($tab === 'by-party' ? 'party' : $tab))
      </div>
    </div>
  </div>

  @if ($selectedLoan !== null)
    @include('app.loans.partials._detailDrawer', ['loan' => $selectedLoan])
  @endif

  @if ($showCreate)
    @include('app.loans.partials._createDrawer')
  @endif
</x-app-layout>
