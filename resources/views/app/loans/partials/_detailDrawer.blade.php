{{-- The drawer showing one loan in full, with its dates, condition, deposit and return flow. --}}
@use('App\Enums\LoanDirection')
@use('App\Enums\LoanStatus')
@use('App\Helpers\Money')

@php
  $user = auth()->user();
  $canManage = $user->account->allowsManagementBy($user);
  $copy = $loan->copy;
  $item = $copy->item;
  $catalog = $item->catalog;
  $isOut = $loan->direction === LoanDirection::Outgoing;
  $closeUrl = route('loans.show', ['direction' => $direction->slug(), 'tab' => $tab]);

  $otherOpen = $copy->loans()
      ->where('direction', LoanDirection::Outgoing)
      ->whereIn('status', LoanStatus::openCases())
      ->whereKeyNot($loan->id)
      ->count();
@endphp

<div class="fixed inset-0 z-40" x-data="{ returning: false }" data-test="loan-detail-drawer">
  <a href="{{ $closeUrl }}" data-turbo="true" class="absolute inset-0 bg-black/30" aria-label="{{ __('Close') }}"></a>

  <div class="absolute inset-y-0 right-0 flex w-full max-w-[540px] flex-col overflow-y-auto border-l border-hairline bg-page shadow-xl">
    <div class="flex items-start justify-between gap-3 border-b border-hairline px-5 py-4">
      <div class="flex flex-wrap items-center gap-2">
        <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $isOut ? 'bg-card text-muted' : 'bg-info/15 text-info' }}">{{ $loan->direction->label() }}</span>
        @include('app.loans.partials._statusBadge', ['status' => $loan->status])
        @if ($loan->include_in_provenance)
          <span class="rounded-full bg-info/15 px-2.5 py-0.5 text-xs font-medium text-info">{{ __('In provenance') }}</span>
        @endif
      </div>
      <a href="{{ $closeUrl }}" data-turbo="true" class="text-muted hover:text-ink" data-test="close-drawer">
        <x-lucide-x class="size-5" />
      </a>
    </div>

    <div class="flex flex-col gap-5 px-5 py-5">
      <div class="flex items-center gap-3">
        <div class="flex size-11 shrink-0 items-center justify-center rounded-lg bg-card text-sm font-semibold text-muted">{{ \Illuminate\Support\Str::of($item->name)->substr(0, 1)->upper() }}</div>
        <div class="min-w-0">
          <a href="{{ route('items.history.show', [$catalog, $item, $copy, 'loans']) }}" class="truncate text-base font-semibold text-ink hover:underline">{{ $item->name }}</a>
          <div class="truncate font-mono text-[12px] text-muted">{{ $copy->identifier ?? '#'.$copy->id }} · {{ $catalog->name }}</div>
        </div>
      </div>

      <div class="rounded-lg bg-info/10 px-3.5 py-3 text-[13px] leading-relaxed text-muted-2">
        {{ $isOut
            ? __('This is a loan out: you still own the copy, someone else is just holding it.')
            : __('This is a loan in: someone else owns the copy, you are holding it for now.') }}
        @if ($isOut && $loan->status->hasLeftCustody())
          <span class="text-ink">{{ __('This copy currently reads as Loaned in your collection.') }}</span>
        @endif
      </div>

      @if ($otherOpen > 0)
        <div class="rounded-lg border border-error/30 bg-error/10 px-3.5 py-3 text-[13px] text-error">
          {{ trans_choice('Overlapping open loan: this copy has :count other open outgoing loan. Only one copy can be out at a time.|Overlapping open loans: this copy has :count other open outgoing loans. Only one copy can be out at a time.', $otherOpen, ['count' => $otherOpen]) }}
        </div>
      @endif

      <div class="grid grid-cols-2 gap-3">
        <div class="rounded-lg border border-hairline px-3 py-2.5">
          <div class="text-[10px] text-muted-soft uppercase">{{ $isOut ? __('Loaned to') : __('Borrowed from') }}</div>
          <div class="text-sm text-ink">{{ $loan->party }}</div>
        </div>
        <div class="rounded-lg border border-hairline px-3 py-2.5">
          <div class="text-[10px] text-muted-soft uppercase">{{ __('Loaned on') }}</div>
          <div class="text-sm text-ink">{{ $loan->loaned_at->isoFormat('ll') }}</div>
        </div>
        <div class="rounded-lg border border-hairline px-3 py-2.5">
          <div class="text-[10px] text-muted-soft uppercase">{{ __('Due') }}</div>
          <div class="text-sm text-ink">{{ $loan->due_at?->isoFormat('ll') ?? __('Open-ended') }}</div>
        </div>
        @if ($loan->returned_at !== null)
          <div class="rounded-lg border border-hairline px-3 py-2.5">
            <div class="text-[10px] text-muted-soft uppercase">{{ __('Returned on') }}</div>
            <div class="text-sm text-ink">{{ $loan->returned_at->isoFormat('ll') }}</div>
          </div>
        @endif
      </div>

      @include('app.loans.partials._conditionComparison', ['loan' => $loan])

      @if ($loan->deposit_amount !== null)
        <div class="rounded-lg border border-hairline px-3.5 py-3">
          <div class="text-[10px] text-muted-soft uppercase">{{ $isOut ? __('Deposit held') : __('Deposit owed') }}</div>
          <div class="text-lg font-semibold text-ink">{{ Money::format($loan->deposit_amount, $loan->deposit_currency_code) }}</div>
          <p class="mt-0.5 text-[12px] text-muted">
            {{ $loan->status === LoanStatus::Returned
                ? __('Deposit settled on return.')
                : ($isOut ? __('Held from the borrower until safe return.') : __('Paid to the owner; refunded on return.')) }}
          </p>
        </div>
      @endif

      <div>
        <div class="mb-2 text-[11px] font-semibold tracking-wide text-muted-soft uppercase">{{ __('Documents') }}</div>
        @forelse ($loan->documents as $document)
          <a href="{{ route('documents.show', $document) }}" class="flex items-center gap-2 rounded-lg border border-hairline px-3 py-2 text-[13px] text-ink hover:bg-canvas" data-turbo="false">
            <x-lucide-file-text class="size-4 text-muted" />
            <span class="truncate">{{ $document->name }}</span>
          </a>
        @empty
          <p class="rounded-lg border border-dashed border-hairline px-3 py-3 text-[12px] text-muted-soft">{{ __('No documents attached. Agreements, receipts, and condition reports live here.') }}</p>
        @endforelse
      </div>

      @if ($loan->loanProvenanceEvent !== null || $loan->returnProvenanceEvent !== null)
        <div>
          <div class="mb-2 text-[11px] font-semibold tracking-wide text-muted-soft uppercase">{{ __('Provenance') }}</div>
          <div class="flex flex-col gap-1.5 text-[13px] text-muted">
            @if ($loan->loanProvenanceEvent !== null)
              <div class="flex items-center gap-2"><span class="size-1.5 rounded-full bg-info"></span>{{ $isOut ? __('Lent to :party', ['party' => $loan->party]) : __('Borrowed from :party', ['party' => $loan->party]) }}</div>
            @endif
            @if ($loan->returnProvenanceEvent !== null)
              <div class="flex items-center gap-2"><span class="size-1.5 rounded-full bg-info"></span>{{ __('Returned') }}</div>
            @endif
          </div>
        </div>
      @endif

      @if ($canManage && $loan->status->hasLeftCustody())
        <div class="border-t border-hairline pt-4">
          <x-button type="button" x-on:click="returning = ! returning" class="w-full justify-center" data-test="mark-returned">
            {{ __('Mark as returned') }}
          </x-button>

          <div x-show="returning" x-cloak class="mt-4">
            <x-form method="put" :action="route('loans.return.update', [$catalog, $item, $copy, $loan])" data-test="return-loan-form">
              <input type="hidden" name="from" value="loans" />
              <div class="mb-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div>
                  <x-label for="returned_at">{{ __('Returned on') }}</x-label>
                  <input id="returned_at" name="returned_at" type="date" value="{{ now()->toDateString() }}" class="mt-1.5 h-10 w-full rounded-md border border-hairline bg-input px-3 text-sm text-ink" />
                  <x-error :messages="$errors->get('returned_at')" class="mt-2" />
                </div>
                <div>
                  <x-label for="item_condition_in_id">{{ __('Condition in') }}</x-label>
                  <select id="item_condition_in_id" name="item_condition_in_id" class="mt-1.5 h-10 w-full rounded-md border border-hairline bg-input px-3 text-sm text-ink">
                    <option value="">{{ __('Not recorded') }}</option>
                    @foreach ($conditions as $id => $name)
                      <option value="{{ $id }}" @selected($loan->item_condition_out_id === $id)>{{ $name }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="flex justify-end gap-2">
                <x-button.secondary type="button" x-on:click="returning = false">{{ __('Cancel') }}</x-button.secondary>
                <x-button type="submit" data-test="confirm-return">{{ __('Confirm return') }}</x-button>
              </div>
            </x-form>
          </div>
        </div>
      @endif

      @if ($canManage)
        <div class="border-t border-hairline pt-4">
          <x-form method="delete" :action="route('loans.destroy', [$catalog, $item, $copy, $loan])" onsubmit="return confirm('{{ __('Delete this loan? This cannot be undone.') }}')" data-test="delete-loan-form">
            <input type="hidden" name="from" value="loans" />
            <button type="submit" class="text-[13px] font-medium text-error hover:underline">{{ __('Delete this loan') }}</button>
          </x-form>
        </div>
      @endif
    </div>
  </div>
</div>
