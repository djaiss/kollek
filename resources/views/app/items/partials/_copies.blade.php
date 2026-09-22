@use('App\Helpers\Money')

@php
  $user = auth()->user();
  $canManage = $user->account->allowsManagementBy($user);
@endphp

<div class="flex max-w-3xl flex-col gap-4">
  <div class="flex flex-wrap items-center justify-between gap-3">
    <p class="text-sm text-muted">
      {{ trans_choice(':count physical copy|:count physical copies', $item->copies->count(), ['count' => $item->copies->count()]) }}
      @if ($totalEstimated > 0)
        · {{ __('total est. value :value', ['value' => $money($totalEstimated)]) }}
      @endif
    </p>

    @if ($canManage)
      <x-button.secondary :href="route('items.edit', [$catalog, $item])" turbo class="shrink-0 !h-9 !px-4 text-[13px]" data-test="add-copy-button">
        <x-slot:icon>
          <x-lucide-plus class="size-4" />
        </x-slot>
        {{ __('Add copy') }}
      </x-button.secondary>
    @endif
  </div>

  @forelse ($item->copies as $copy)
    @php
      $estimated = $copy->estimatedValue();
    @endphp

    <div class="overflow-hidden rounded-xl border border-hairline" data-test="copy-{{ $copy->id }}">
      <div class="flex flex-wrap items-center justify-between gap-3 border-b border-hairline px-5 py-4">
        <div class="flex flex-wrap items-center gap-2.5">
          <p class="text-[15px] font-semibold text-ink">{{ __('Copy :number', ['number' => $loop->iteration]) }}</p>

          @if ($copy->identifier)
            <span class="rounded-md bg-card px-2 py-0.5 font-mono text-xs text-muted" data-test="copy-identifier">{{ $copy->identifier }}</span>
          @endif

          <x-badge :color="$copy->status->color()" data-test="copy-status">{{ $copy->status->label() }}</x-badge>

          @if ($copy->quantity > 1)
            <span class="text-[13px] text-muted" data-test="copy-quantity">{{ __('× :count', ['count' => number_format($copy->quantity)]) }}</span>
          @endif
        </div>

        <div class="text-right">
          <p class="text-base font-semibold text-ink">{{ $estimated === null ? '—' : $money($estimated) }}</p>
          <p class="text-[11px] text-muted-soft">{{ __('latest valuation') }}</p>
        </div>
      </div>

      <div class="grid grid-cols-2 sm:grid-cols-3">
        @php
          // The acquisition date and the price paid are not columns on the copy.
          // Both are read from the transaction that brought it in, so a copy with
          // no such transaction simply has neither.
          $pricePaid = $copy->pricePaid();

          $facts = [
              ['test' => 'copy-condition', 'label' => __('Condition'), 'value' => $copy->itemCondition?->name ?? '—'],
              ['test' => 'copy-location', 'label' => __('Location'), 'value' => $copy->currentLocation === null
                  ? '—'
                  : ($copy->currentLocation->name . ($copy->openLocationHistory ? ' · ' . __('since :date', ['date' => $copy->openLocationHistory->moved_at->isoFormat('MMM YYYY')]) : ''))],
              ['test' => 'copy-quantity-fact', 'label' => __('Quantity'), 'value' => number_format($copy->quantity)],
              ['test' => 'copy-acquired-at', 'label' => __('Acquired'), 'value' => $copy->acquiredAt()?->isoFormat('MMM YYYY') ?? '—'],
              ['test' => 'copy-price-paid', 'label' => __('Price paid'), 'value' => $pricePaid === null ? '—' : Money::format($pricePaid, $copy->acquiringTransaction?->currency_code)],
              ['test' => 'copy-disposed-at', 'label' => __('Disposed'), 'value' => $copy->disposed_at?->isoFormat('MMM YYYY') ?? '—'],
          ];
        @endphp

        @foreach ($facts as $fact)
          <div class="border-b border-hairline px-5 py-3.5 {{ $loop->iteration % 3 !== 0 ? 'sm:border-r sm:border-r-hairline' : '' }}" data-test="{{ $fact['test'] }}">
            <p class="mb-1 text-xs text-muted-soft">{{ $fact['label'] }}</p>
            <p class="truncate text-sm font-semibold text-ink">{{ $fact['value'] }}</p>
          </div>
        @endforeach
      </div>

      <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4">
        <p class="min-w-0 flex-1 text-[13px] leading-relaxed text-muted">{{ $copy->note ?? __('No note on this copy.') }}</p>

        <a
          href="{{ route('items.history.show', [$catalog, $item, $copy]) }}"
          data-turbo="true"
          class="shrink-0 text-[13px] font-semibold text-ink transition-opacity hover:opacity-75"
          data-test="copy-history-link"
        >
          {{ __('View full history →') }}
        </a>
      </div>
    </div>
  @empty
    <div class="rounded-xl border border-hairline">
      <x-empty-state data-test="no-copies">
        <x-slot:icon>
          <x-lucide-package class="size-6 text-muted" />
        </x-slot>

        {{ __('No copies of this item yet.') }}
      </x-empty-state>
    </div>
  @endforelse
</div>
