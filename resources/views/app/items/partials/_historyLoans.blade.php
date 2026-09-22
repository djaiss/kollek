{{-- The loans of the copy: the pieces lent out and the pieces borrowed in. --}}

@use('App\Enums\LoanDirection')
@use('App\Helpers\Money')

@php
  $user = auth()->user();
  $canManage = $user->account->allowsManagementBy($user);
  $loans = $selectedCopy->loans;

  $statusClasses = fn (?string $color): string => match ($color) {
      'emerald' => 'bg-badge-emerald/15 text-badge-emerald',
      'orange' => 'bg-badge-orange/15 text-badge-orange',
      'error' => 'bg-error/15 text-error',
      default => 'bg-card text-muted',
  };
@endphp

<div x-data="{ adding: false }">
  <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
    <div class="min-w-0">
      <div class="flex items-center gap-2">
        <p class="text-lg font-semibold text-ink">{{ __('Loans') }}</p>
        <x-help id="history.loans" />
      </div>
      <p class="mt-1 max-w-xl text-[13px] leading-relaxed text-muted">{{ __('Custody moving out and in over time, without any change of ownership. An active loan means the copy is not in your hands right now.') }}</p>
    </div>

    @if ($canManage)
      <x-button.secondary type="button" x-on:click="adding = ! adding" class="shrink-0 !h-9 !px-4 text-[13px]" data-test="new-loan-{{ $selectedCopy->id }}">
        <x-slot:icon>
          <x-lucide-plus class="size-4" />
        </x-slot>
        {{ __('Loan') }}
      </x-button.secondary>
    @endif
  </div>

  @if ($canManage)
    <div x-show="adding" x-cloak class="mb-5">
      @include('app.items.partials._loanForm', [
          'formId' => 'add-loan-'.$selectedCopy->id,
          'action' => route('loans.create', [$catalog, $item, $selectedCopy]),
          'method' => 'post',
          'openVar' => 'adding',
          'submitLabel' => __('Add loan'),
          'dataTest' => 'create-loan-form-'.$selectedCopy->id,
          'loan' => null,
      ])
    </div>
  @endif

  <div class="flex flex-col gap-3.5">
    @forelse ($loans as $loan)
      @php
        $outgoing = $loan->direction === LoanDirection::Outgoing;
        $out = $loan->itemConditionOut?->name;
        $in = $loan->itemConditionIn?->name;
        $facts = [
            __('Loaned') => $loan->loaned_at->isoFormat('MMM D, YYYY'),
            __('Due back') => $loan->due_at ? $loan->due_at->isoFormat('MMM D, YYYY') : '—',
            __('Returned') => $loan->returned_at ? $loan->returned_at->isoFormat('MMM D, YYYY') : '—',
            __('Condition') => ($out || $in) ? (($out ?? '—') . ' → ' . ($in ?? '—')) : '—',
        ];
      @endphp

      <div class="overflow-hidden rounded-xl border border-hairline" x-data="{ editing: false, returning: false }" data-test="loan-{{ $loan->id }}">
        <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4">
          <div class="flex min-w-0 flex-wrap items-center gap-2.5">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-card px-2.5 py-1 text-[11.5px] font-semibold text-muted" data-test="loan-direction-{{ $loan->id }}">
              @if ($outgoing)
                <x-lucide-arrow-up-right class="size-3" />
              @else
                <x-lucide-arrow-down-left class="size-3" />
              @endif
              {{ $loan->direction->label() }}
            </span>

            <span class="text-[15px] font-semibold text-ink" data-test="loan-party-{{ $loan->id }}">{{ $loan->party }}</span>

            <span class="rounded-full px-2.5 py-1 text-[11.5px] font-semibold {{ $statusClasses($loan->status->color()) }}" data-test="loan-status-{{ $loan->id }}">{{ $loan->status->label() }}</span>

            @if ($loan->include_in_provenance)
              <span class="rounded-full bg-card px-2.5 py-1 text-[11.5px] font-semibold text-muted" data-test="loan-provenance-{{ $loan->id }}">{{ __('In provenance') }}</span>
            @endif
          </div>

          <div class="flex items-center gap-3.5">
            @if ($loan->deposit_amount !== null)
              <div class="text-right">
                <p class="text-base font-semibold text-ink" data-test="loan-deposit-{{ $loan->id }}">{{ Money::format($loan->deposit_amount, $loan->deposit_currency_code) }}</p>
                <p class="text-xs text-muted-soft">{{ __('deposit') }}</p>
              </div>
            @endif

            @if ($canManage)
              @if ($loan->status->isOpen())
                <button type="button" x-on:click="returning = ! returning; editing = false" class="flex h-8 shrink-0 items-center justify-center rounded-md bg-ink px-3 text-[13px] font-semibold text-page hover:opacity-90" data-test="return-loan-{{ $loan->id }}">
                  {{ __('Mark as returned') }}
                </button>
              @endif

              <button type="button" x-on:click="editing = ! editing; returning = false" class="flex h-8 shrink-0 items-center justify-center rounded-md border border-hairline px-3 text-[13px] font-semibold text-muted hover:bg-card" data-test="edit-loan-{{ $loan->id }}">
                {{ __('Edit') }}
              </button>
            @endif
          </div>
        </div>

        <div class="grid grid-cols-2 border-t border-hairline sm:grid-cols-4">
          @foreach ($facts as $label => $value)
            <div class="border-b border-hairline px-5 py-3 last:border-r-0 sm:border-b-0 sm:border-r sm:border-r-hairline">
              <p class="mb-1 text-[11.5px] text-muted-soft">{{ $label }}</p>
              <p class="truncate text-[13.5px] font-semibold text-ink">{{ $value }}</p>
            </div>
          @endforeach
        </div>

        @if ($loan->purpose)
          <div class="border-t border-hairline px-5 py-3">
            <p class="text-[13px] leading-relaxed text-muted">{{ $loan->purpose }}</p>
          </div>
        @endif

        @if ($canManage)
          <div x-show="returning" x-cloak class="border-t border-hairline bg-card/40 p-4">
            @include('app.items.partials._loanReturnForm', ['loan' => $loan])
          </div>

          <div x-show="editing" x-cloak class="border-t border-hairline bg-card/40 p-4">
            @include('app.items.partials._loanForm', [
                'formId' => 'edit-loan-'.$loan->id,
                'action' => route('loans.update', [$catalog, $item, $selectedCopy, $loan]),
                'deleteAction' => route('loans.destroy', [$catalog, $item, $selectedCopy, $loan]),
                'method' => 'put',
                'openVar' => 'editing',
                'submitLabel' => __('Save changes'),
                'dataTest' => 'edit-loan-form-'.$loan->id,
                'loan' => $loan,
            ])
          </div>
        @endif

        <div class="border-t border-hairline px-5 py-4">
          <p class="mb-2.5 text-[11px] font-semibold tracking-wide text-muted-soft uppercase">{{ __('Documents') }}</p>
          @include('app.items.partials._documentsFor', ['documentable' => $loan, 'catalog' => $catalog, 'item' => $item, 'selectedCopy' => $selectedCopy, 'canManage' => $canManage])
        </div>
      </div>
    @empty
      <div x-show="! adding" class="rounded-xl border border-hairline">
        <x-empty-state data-test="no-loans-{{ $selectedCopy->id }}">
          <x-slot:icon>
            <x-lucide-arrow-left-right class="size-6 text-muted" />
          </x-slot>

          {{ __('No loans have been recorded for this copy yet.') }}
        </x-empty-state>
      </div>
    @endforelse
  </div>
</div>
