{{-- One loan as a clickable row that opens its detail drawer. --}}
@props(['loan', 'direction', 'tab', 'compact' => false])

@use('App\Enums\LoanDirection')
@use('App\Helpers\Money')

@php
  $copy = $loan->copy;
  $item = $copy->item;
  $identifier = $copy->identifier ?? '#'.$copy->id;
  $docCount = $loan->documents->count();

  if ($loan->returned_at !== null) {
      $dueText = __('Returned :date', ['date' => $loan->returned_at->isoFormat('ll')]);
  } elseif ($loan->isOpenEnded()) {
      $dueText = __('Open-ended');
  } elseif ($loan->due_at !== null) {
      $dueText = $loan->isEffectivelyOverdue()
          ? __(':days overdue', ['days' => $loan->due_at->diffForHumans(['parts' => 1, 'short' => true, 'syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE])])
          : __('Due :date', ['date' => $loan->due_at->isoFormat('ll')]);
  } else {
      $dueText = __('Loaned :date', ['date' => $loan->loaned_at->isoFormat('ll')]);
  }
@endphp

<a
  href="{{ route('loans.show', ['direction' => $direction->slug(), 'tab' => $tab, 'loan' => $loan->id]) }}"
  data-turbo="true"
  class="flex items-center gap-3 px-4 py-3 transition-colors hover:bg-canvas"
  data-test="loan-row-{{ $loan->id }}"
>
  <div class="flex size-9 shrink-0 items-center justify-center rounded-md bg-card text-xs font-semibold text-muted">
    {{ \Illuminate\Support\Str::of($item->name)->substr(0, 1)->upper() }}
  </div>

  <div class="min-w-0 flex-1">
    <div class="flex items-center gap-1.5">
      <span class="truncate text-sm font-medium text-ink">{{ $item->name }}</span>
      @if ($loan->include_in_provenance)
        <span class="rounded bg-info/15 px-1 py-0.5 text-[9px] font-semibold tracking-wide text-info uppercase">{{ __('Prov') }}</span>
      @endif
    </div>
    <div class="truncate font-mono text-[11px] text-muted">
      {{ $identifier }} · {{ $docCount === 0 ? __('No docs') : trans_choice(':count doc|:count docs', $docCount, ['count' => $docCount]) }}
    </div>
  </div>

  @unless ($compact)
    <div class="hidden w-40 min-w-0 md:block">
      <div class="truncate text-sm text-ink">{{ $loan->party }}</div>
      <div class="truncate text-[11px] text-muted">{{ $item->catalog->name }}</div>
    </div>

    <div class="hidden w-28 shrink-0 text-[13px] text-muted lg:block">
      {{ $loan->itemConditionOut?->name ?? __('—') }}
    </div>
  @endunless

  <div class="w-28 shrink-0 text-right md:text-left">
    <div class="text-[13px] text-ink {{ $loan->isEffectivelyOverdue() ? 'text-error' : '' }}">{{ $dueText }}</div>
    @if ($loan->deposit_amount !== null)
      <div class="text-[11px] text-muted">{{ Money::format($loan->deposit_amount, $loan->deposit_currency_code) }}</div>
    @endif
  </div>

  <div class="shrink-0">
    @include('app.loans.partials._statusBadge', ['status' => $loan->status])
  </div>
</a>
