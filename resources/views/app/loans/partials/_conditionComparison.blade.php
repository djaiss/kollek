{{-- The condition a copy left in next to the condition it came back in. --}}
@php
  $out = $loan->itemConditionOut;
  $in = $loan->itemConditionIn;
  $worse = $loan->returnedWorse();

  $inLabel = $in?->name ?? ($loan->returned_at === null ? __('Awaiting return') : __('Not recorded'));
@endphp

<div>
  <div class="mb-2 text-[11px] font-semibold tracking-wide text-muted-soft uppercase">{{ __('Condition') }}</div>
  <div class="flex items-stretch gap-2">
    <div class="flex-1 rounded-lg border border-hairline bg-card/40 px-3 py-2">
      <div class="text-[10px] text-muted-soft uppercase">{{ __('Out') }}</div>
      <div class="text-sm font-medium text-ink">{{ $out?->name ?? __('Not recorded') }}</div>
    </div>

    <div class="flex items-center">
      @svg('lucide-arrow-right', 'size-4 '.($worse ? 'text-error' : 'text-muted-soft'))
    </div>

    <div class="flex-1 rounded-lg border px-3 py-2 {{ $worse ? 'border-error/40 bg-error/10' : 'border-hairline bg-card/40' }}">
      <div class="text-[10px] uppercase {{ $worse ? 'text-error' : 'text-muted-soft' }}">{{ __('In') }}</div>
      <div class="text-sm font-medium {{ $worse ? 'text-error' : 'text-ink' }}">{{ $inLabel }}</div>
    </div>
  </div>

  @if ($worse)
    <p class="mt-2 text-[12px] text-error">{{ __('Returned worse than it left. Flagged as possible damage.') }}</p>
  @endif
</div>
