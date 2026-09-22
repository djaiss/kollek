{{-- How much of the free plan the account has used, in the sidebar. --}}
@use('App\Services\AccountPlan')

@php
  $plan = new AccountPlan(account: auth()->user()->account);
@endphp

@if ($plan->isLimited())
  @php
    $used = $plan->itemsUsed();
    $limit = $plan->freeLimit();
    $over = $plan->itemsOverLimit();
    $price = '$' . config('pricing.price');
    $tone = $over > 0 ? 'text-warning' : 'text-muted';
  @endphp

  <div class="flex flex-col gap-2.5 rounded-lg border border-hairline bg-page p-3.5">
    <div class="flex items-baseline justify-between gap-2">
      <span class="text-xs font-semibold text-body">{{ __('Free account') }}</span>
      <span class="font-mono text-[11.5px] {{ $tone }}">{{ $used }}/{{ $limit }}</span>
    </div>

    <div class="h-1.5 overflow-hidden rounded-full bg-card">
      <div class="h-1.5 rounded-full {{ $over > 0 ? 'bg-warning' : 'bg-ink' }}" style="width: {{ min(100, (int) round($used / $limit * 100)) }}%"></div>
    </div>

    <p class="text-[11.5px] leading-relaxed text-muted">
      @if ($plan->hasReachedHardLimit())
        {{ __('New items are paused until you unlock the account.') }}
      @elseif ($over > 0)
        {{ trans_choice('You are :count item over the free limit.|You are :count items over the free limit.', $over, ['count' => $over]) }}
      @else
        {{ trans_choice(':count item left on the free plan.|:count items left on the free plan.', $limit - $used, ['count' => $limit - $used]) }}
      @endif
    </p>

    <a
      href="{{ route('upgrade.index') }}"
      data-turbo="true"
      class="flex h-8 items-center justify-center gap-1.5 rounded-md bg-ink text-[12.5px] font-semibold text-page transition-opacity hover:opacity-90"
    >
      @svg('lucide-lock', 'size-3')
      {{ __('Unlock for :price', ['price' => $price]) }}
    </a>
  </div>
@endif
