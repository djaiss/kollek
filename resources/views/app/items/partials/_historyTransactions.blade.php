{{-- The transactions of the copy, the source of truth for its commercial data. --}}

@use('App\Helpers\Money')

@php
  $user = auth()->user();
  $canManage = $user->account->allowsManagementBy($user);
@endphp

<div x-data="{ adding: false }">
  <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
    <div class="min-w-0">
      <div class="flex items-center gap-2">
        <p class="text-lg font-semibold text-ink">{{ __('Transactions') }}</p>
        <x-help id="history.transactions" />
      </div>
      <p class="mt-1 max-w-xl text-[13px] leading-relaxed text-muted">{{ __('Financial and ownership exchanges. The source of truth for prices, fees and totals.') }}</p>
    </div>

    @if ($canManage)
      <x-button type="button" x-on:click="adding = ! adding" class="shrink-0 text-[13px]" data-test="new-transaction-{{ $selectedCopy->id }}">
        <x-slot:icon>
          <x-lucide-plus class="size-4" />
        </x-slot>
        {{ __('Add transaction') }}
      </x-button>
    @endif
  </div>

  @if ($canManage)
    <div x-show="adding" x-cloak class="mb-4">
      @include('app.items.partials._transactionForm', [
          'formId' => 'add-transaction-'.$selectedCopy->id,
          'action' => route('transactions.create', [$catalog, $item, $selectedCopy]),
          'method' => 'post',
          'openVar' => 'adding',
          'submitLabel' => __('Add transaction'),
          'dataTest' => 'create-transaction-form-'.$selectedCopy->id,
          'transaction' => null,
      ])
    </div>
  @endif

  <div class="flex flex-col gap-3">
    @forelse ($selectedCopy->transactions as $transaction)
      @php
        $total = $transaction->total();
        $breakdown = [
            __('Amount') => $transaction->amount,
            __('Tax') => $transaction->tax_amount,
            __('Fees') => $transaction->fee_amount,
            __('Shipping') => $transaction->shipping_amount,
        ];
      @endphp

      <div class="rounded-xl border border-hairline" x-data="{ editing: false }" data-test="transaction-{{ $transaction->id }}">
        <div class="flex flex-wrap items-start justify-between gap-3 px-4 py-3.5">
          <div class="min-w-0">
            <x-badge data-test="transaction-type-{{ $transaction->id }}">{{ $transaction->type->label() }}</x-badge>

            @if ($transaction->provenanceEvent)
              <x-badge color="violet" class="ml-1.5" data-test="transaction-provenance-{{ $transaction->id }}">{{ __('In the provenance') }}</x-badge>
            @endif

            <p class="mt-1.5 truncate text-[13px] text-muted" data-test="transaction-counterparty-{{ $transaction->id }}">{{ $transaction->counterparty ?? __('No counterparty recorded.') }}</p>
          </div>

          <div class="flex items-start gap-2.5">
            <div class="text-right">
              <p class="text-base font-semibold text-ink" data-test="transaction-total-{{ $transaction->id }}">{{ $total === null ? '—' : Money::format($total, $transaction->currency_code) }}</p>
              <p class="text-[11px] text-muted-soft">{{ $transaction->occurred_at->isoFormat('MMM D, YYYY') }}</p>
            </div>

            @if ($canManage)
              <button type="button" x-on:click="editing = ! editing" class="flex h-8 shrink-0 items-center justify-center rounded-md border border-hairline px-3 text-[13px] font-semibold text-muted hover:bg-card" data-test="edit-transaction-{{ $transaction->id }}">
                {{ __('Edit') }}
              </button>
            @endif
          </div>
        </div>

        <div class="grid grid-cols-2 border-t border-hairline sm:grid-cols-4">
          @foreach ($breakdown as $label => $cents)
            <div class="border-b border-hairline px-4 py-3 last:border-r-0 sm:border-b-0 sm:border-r sm:border-r-hairline">
              <p class="mb-1 text-xs text-muted-soft">{{ $label }}</p>
              <p class="truncate text-sm font-semibold text-ink">{{ $cents === null ? '—' : Money::format($cents, $transaction->currency_code) }}</p>
            </div>
          @endforeach
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-hairline px-4 py-3">
          <p class="min-w-0 flex-1 text-[13px] leading-relaxed text-muted">{{ $transaction->note ?? __('No note on this transaction.') }}</p>

          @if ($transaction->reference_number)
            <span class="shrink-0 rounded-md bg-card px-2 py-0.5 font-mono text-xs text-muted" data-test="transaction-reference-{{ $transaction->id }}">{{ $transaction->reference_number }}</span>
          @endif
        </div>

        @if ($canManage)
          <div x-show="editing" x-cloak class="border-t border-hairline bg-card/40 p-4">
            @include('app.items.partials._transactionForm', [
                'formId' => 'edit-transaction-'.$transaction->id,
                'action' => route('transactions.update', [$catalog, $item, $selectedCopy, $transaction]),
                'deleteAction' => route('transactions.destroy', [$catalog, $item, $selectedCopy, $transaction]),
                'method' => 'put',
                'openVar' => 'editing',
                'submitLabel' => __('Save'),
                'dataTest' => 'edit-transaction-form-'.$transaction->id,
                'transaction' => $transaction,
            ])
          </div>
        @endif

        <div class="border-t border-hairline px-5 py-4">
          <p class="mb-2.5 text-[11px] font-semibold tracking-wide text-muted-soft uppercase">{{ __('Documents') }}</p>
          @include('app.items.partials._documentsFor', ['documentable' => $transaction, 'catalog' => $catalog, 'item' => $item, 'selectedCopy' => $selectedCopy, 'canManage' => $canManage])
        </div>
      </div>
    @empty
      <div x-show="! adding" class="rounded-xl border border-hairline">
        <x-empty-state data-test="no-transactions-{{ $selectedCopy->id }}">
          <x-slot:icon>
            <x-lucide-receipt class="size-6 text-muted" />
          </x-slot>

          {{ __('No transaction has been recorded against this copy yet.') }}
        </x-empty-state>
      </div>
    @endforelse
  </div>
</div>
