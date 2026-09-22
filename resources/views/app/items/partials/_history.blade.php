{{-- The history of one copy, chosen with the pills and read one section at a time. --}}

@use('App\Helpers\Money')

@php
  $sectionsMeta = [
      ['key' => 'timeline', 'label' => __('Timeline'), 'color' => 'var(--color-ink)', 'round' => true, 'ready' => true],
      ['key' => 'transactions', 'label' => __('Transactions'), 'color' => '#34d399', 'round' => false, 'ready' => true],
      ['key' => 'valuations', 'label' => __('Valuations'), 'color' => '#3b82f6', 'round' => false, 'ready' => true],
      ['key' => 'provenance', 'label' => __('Provenance'), 'color' => '#6366f1', 'round' => true, 'ready' => true],
      ['key' => 'insurance', 'label' => __('Insurance'), 'color' => '#8b5cf6', 'round' => true, 'ready' => true],
      ['key' => 'maintenance', 'label' => __('Maintenance'), 'color' => '#f59e0b', 'round' => false, 'ready' => true],
      ['key' => 'loans', 'label' => __('Loans'), 'color' => '#ec4899', 'round' => true, 'ready' => true],
      ['key' => 'locations', 'label' => __('Locations'), 'color' => '#14b8a6', 'round' => false, 'ready' => true],
      ['key' => 'documents', 'label' => __('Documents'), 'color' => '#64748b', 'round' => false, 'ready' => true],
  ];
@endphp

@if ($item->copies->isEmpty())
  <div class="rounded-xl border border-hairline">
    <x-empty-state data-test="no-copies-to-track">
      <x-slot:icon>
        <x-lucide-clock class="size-6 text-muted" />
      </x-slot>

      {{ __('This item has no copies, so there is nothing to track the history of.') }}
    </x-empty-state>
  </div>
@else
  @php
    $copyNumber = $item->copies->search(fn ($copy) => $copy->id === $selectedCopy->id) + 1;
    $latestValuation = $selectedCopy->latestValuation;

    // The count each section shows. Only the built sections carry data, so the
    // rest are left blank rather than showing a zero that reads as a real count.
    $valuationCount = $selectedCopy->valuations->count();
    $transactionCount = $selectedCopy->transactions->count();
    $provenanceCount = $selectedCopy->provenanceEvents->count();
    $insuranceCount = $selectedCopy->insuranceRecords->count();
    $maintenanceCount = $selectedCopy->maintenanceRecords->count();
    $loanCount = $selectedCopy->loans->count();
    $locationCount = $selectedCopy->locationHistory->count();
    $documentCount = $selectedCopy->documents->count();
    $counts = [
        'timeline' => $valuationCount,
        'transactions' => $transactionCount,
        'valuations' => $valuationCount,
        'provenance' => $provenanceCount,
        'insurance' => $insuranceCount,
        'maintenance' => $maintenanceCount,
        'loans' => $loanCount,
        'locations' => $locationCount,
        'documents' => $documentCount,
    ];

    // The loan that currently has the copy out of custody, if any. It drives the
    // banner that says where the copy is and when it is due back.
    $activeLoan = $selectedCopy->activeLoan;

    // The acquisition date and price are read from the earliest transaction that
    // brought the copy in, not stored on the copy.
    $acquisition = $selectedCopy->acquiringTransaction;
    $acquisitionTotal = $acquisition?->total();

    $statusTextColor = match ($selectedCopy->status->color()) {
        'emerald' => 'text-badge-emerald',
        'orange' => 'text-badge-orange',
        'pink' => 'text-badge-pink',
        'violet' => 'text-badge-violet',
        'error' => 'text-error',
        default => 'text-ink',
    };
  @endphp

  <div id="history-panel" class="flex flex-col gap-6" data-test="history-copy-{{ $selectedCopy->id }}">
    <div class="flex flex-wrap items-center gap-2.5">
      <span class="text-[11px] font-semibold tracking-wide text-muted-soft uppercase">{{ __('Physical copy') }}</span>

      @foreach ($item->copies as $copy)
        <a
          href="{{ route('items.history.show', [$catalog, $item, $copy, $section]) }}"
          data-turbo="true"
          @class([
              'flex items-center gap-2.5 rounded-full border px-3.5 py-2 transition-colors',
              'border-ink bg-card' => $copy->id === $selectedCopy->id,
              'border-hairline hover:border-ink' => $copy->id !== $selectedCopy->id,
          ])
          @if ($copy->id === $selectedCopy->id) aria-current="page" @endif
          data-test="history-copy-pill-{{ $copy->id }}"
        >
          <span class="size-[7px] shrink-0 rounded-full" style="background-color: {{ $copy->status->color() === 'error' ? 'var(--color-error)' : '#34d399' }}"></span>
          <span class="text-[13px] font-semibold text-ink">{{ __('Copy :number', ['number' => $loop->iteration]) }}</span>
          @if ($copy->itemCondition)
            <span class="text-xs text-muted-soft">{{ $copy->itemCondition->name }}</span>
          @endif
        </a>
      @endforeach
    </div>

    @if ($activeLoan)
      @php
        $loanOverdue = $activeLoan->isOverdue();
      @endphp

      <a
        href="{{ route('items.history.show', [$catalog, $item, $selectedCopy, 'loans']) }}"
        data-turbo="true"
        @class([
            'flex flex-wrap items-center gap-3 rounded-xl border px-5 py-4 transition-colors',
            'border-error/40 bg-error/10 hover:border-error/60' => $loanOverdue,
            'border-badge-pink/40 bg-badge-pink/10 hover:border-badge-pink/60' => ! $loanOverdue,
        ])
        data-test="loan-banner-{{ $selectedCopy->id }}"
      >
        <span @class([
            'flex size-9 shrink-0 items-center justify-center rounded-full',
            'bg-error/15 text-error' => $loanOverdue,
            'bg-badge-pink/15 text-badge-pink' => ! $loanOverdue,
        ])>
          <x-lucide-arrow-up-right class="size-5" />
        </span>

        <div class="min-w-0 flex-1">
          <p class="text-sm font-semibold text-ink">
            @if ($loanOverdue)
              {{ __('Overdue: this copy is out with :party', ['party' => $activeLoan->party]) }}
            @else
              {{ __('On loan to :party', ['party' => $activeLoan->party]) }}
            @endif
          </p>
          <p class="mt-0.5 text-xs text-muted-soft">
            {{ __('Lent :date', ['date' => $activeLoan->loaned_at->isoFormat('MMM D, YYYY')]) }}
            @if ($activeLoan->due_at)
              · {{ $loanOverdue ? __('was due :date', ['date' => $activeLoan->due_at->isoFormat('MMM D, YYYY')]) : __('due back :date', ['date' => $activeLoan->due_at->isoFormat('MMM D, YYYY')]) }}
            @else
              · {{ __('no return date set') }}
            @endif
          </p>
        </div>

        <span @class([
            'shrink-0 rounded-full px-2.5 py-1 text-[11.5px] font-semibold',
            'bg-error/15 text-error' => $loanOverdue,
            'bg-badge-pink/15 text-badge-pink' => ! $loanOverdue,
        ])>{{ $activeLoan->status->label() }}</span>
      </a>
    @endif

    <div class="grid grid-cols-2 overflow-hidden rounded-xl border border-hairline lg:grid-cols-4" data-test="history-summary">
      @php
        $summary = [
            [
                'label' => __('Status'),
                'value' => $selectedCopy->status->label(),
                'valueClass' => $statusTextColor,
                'sub' => __('Copy :number', ['number' => $copyNumber]) . ($selectedCopy->identifier ? ' · ' . $selectedCopy->identifier : ''),
            ],
            [
                'label' => __('Current value'),
                'value' => $latestValuation ? $money($latestValuation->amount) : '—',
                'valueClass' => 'text-ink',
                'sub' => $latestValuation?->type->label() ?? __('No valuation yet'),
            ],
            [
                'label' => __('Acquired'),
                'value' => $acquisition ? $acquisition->occurred_at->isoFormat('MMM D, YYYY') : '—',
                'valueClass' => 'text-ink',
                'sub' => $acquisition
                    ? ($acquisitionTotal !== null ? Money::format($acquisitionTotal, $acquisition->currency_code) : $acquisition->type->label())
                    : __('No acquisition recorded'),
            ],
            [
                'label' => __('Location'),
                'value' => $selectedCopy->currentLocation?->name ?? '—',
                'valueClass' => 'text-ink',
                'sub' => $selectedCopy->openLocationHistory
                    ? __('since :date', ['date' => $selectedCopy->openLocationHistory->moved_at->isoFormat('MMM YYYY')])
                    : ($selectedCopy->currentLocation ? __('No move recorded') : __('Not stored anywhere')),
            ],
        ];
      @endphp

      @foreach ($summary as $stat)
        <div class="border-b border-hairline px-5 py-4 last:border-r-0 sm:border-r sm:border-r-hairline lg:border-b-0">
          <p class="mb-1.5 text-xs text-muted-soft">{{ $stat['label'] }}</p>
          <p class="text-lg font-semibold {{ $stat['valueClass'] }}">{{ $stat['value'] }}</p>
          <p class="mt-0.5 truncate text-xs text-muted-soft">{{ $stat['sub'] }}</p>
        </div>
      @endforeach
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-[200px_1fr] lg:items-start">
      <nav class="flex flex-col gap-0.5 lg:sticky lg:top-6" data-test="history-sections">
        @foreach ($sectionsMeta as $meta)
          <a
            href="{{ route('items.history.show', [$catalog, $item, $selectedCopy, $meta['key']]) }}"
            data-turbo="true"
            @class([
                'flex items-center justify-between gap-2 rounded-md px-3 py-2 transition-colors',
                'bg-card' => $section === $meta['key'],
                'hover:bg-card/60' => $section !== $meta['key'],
            ])
            data-test="history-section-{{ $meta['key'] }}"
          >
            <span class="flex items-center gap-2.5">
              <span class="size-2 shrink-0 {{ $meta['round'] ? 'rounded-full' : 'rounded-sm' }}" style="background-color: {{ $meta['color'] }}"></span>
              <span class="text-[13.5px] {{ $section === $meta['key'] ? 'font-semibold text-ink' : 'font-medium text-muted' }}">{{ $meta['label'] }}</span>
            </span>

            @if (($counts[$meta['key']] ?? 0) > 0)
              <span class="text-[11px] font-semibold text-muted-soft">{{ $counts[$meta['key']] }}</span>
            @endif
          </a>
        @endforeach
      </nav>

      <div class="min-w-0">
        @php
          $active = collect($sectionsMeta)->firstWhere('key', $section);
        @endphp

        @if ($section === 'timeline')
          @include('app.items.partials._historyTimeline')
        @elseif ($section === 'transactions')
          @include('app.items.partials._historyTransactions')
        @elseif ($section === 'provenance')
          @include('app.items.partials._historyProvenance')
        @elseif ($section === 'valuations')
          @include('app.items.partials._historyValuations')
        @elseif ($section === 'insurance')
          @include('app.items.partials._historyInsurance')
        @elseif ($section === 'maintenance')
          @include('app.items.partials._historyMaintenance')
        @elseif ($section === 'loans')
          @include('app.items.partials._historyLoans')
        @elseif ($section === 'locations')
          @include('app.items.partials._historyLocations')
        @elseif ($section === 'documents')
          @include('app.items.partials._historyDocuments')
        @else
          <div class="mb-4">
            <p class="text-lg font-semibold text-ink">{{ $active['label'] }}</p>
          </div>

          <div class="rounded-xl border border-hairline">
            <x-empty-state data-test="history-section-soon">
              <x-slot:icon>
                <x-lucide-clock class="size-6 text-muted" />
              </x-slot>

              {{ __('This part of the history is not built yet.') }}
              <x-soon />
            </x-empty-state>
          </div>
        @endif
      </div>
    </div>
  </div>
@endif
