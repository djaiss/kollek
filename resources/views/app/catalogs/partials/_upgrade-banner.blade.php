{{-- The banner shown on a collection once the account has outgrown the free plan. --}}

<div class="mb-6 flex flex-wrap items-center gap-4 rounded-lg border border-warning/30 bg-warning/10 px-5 py-4">
    <div class="min-w-0 flex-1">
        <p class="text-sm font-semibold text-ink">
            @if ($hasReachedItemLimit)
                {{ __('This account is full. Adding items is paused.') }}
            @else
                {{ trans_choice('You are :count item over the free plan.|You are :count items over the free plan.', $plan->itemsOverLimit(), ['count' => $plan->itemsOverLimit()]) }}
            @endif
        </p>
        <p class="mt-1 text-[13px] leading-relaxed text-muted">
            @if ($hasReachedItemLimit)
                {{ __('Everything you have added is safe, readable and exportable. Unlock the account to keep adding, or move to a free self-hosted instance.') }}
            @else
                {{ trans_choice('You can still add :count more item before the account stops growing.|You can still add :count more items before the account stops growing.', $plan->itemsRemaining(), ['count' => $plan->itemsRemaining()]) }}
            @endif
        </p>
    </div>

    <x-button :href="route('upgrade.index')" turbo class="shrink-0" data-test="upgrade-account-button">
        {{ __('Upgrade account') }}
    </x-button>
</div>
