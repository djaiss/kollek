<x-app-layout>
  <x-slot:title>
    {{ __('Confirm your unlock') }}
  </x-slot>

  @php
    $price = '$' . config('pricing.price');
    $account = auth()->user()->account;

    $instead = [
        __('Fix the actual problem. A bug gets a patch, not a support script.'),
        __('Help you move to a free self-hosted instance, with your data exported for you.'),
        __('Talk it through by email before you pay, if you are on the fence right now.'),
    ];

    $assurances = [
        __('Card details go straight to the payment processor. We never see or store them.'),
        __('The unlock applies the moment the payment clears, with no waiting on a human.'),
        __('Your data stays exportable in full, unlocked or not.'),
    ];

    // Coming back from a failed submission keeps the boxes that were ticked, so the
    // counter has to start from them rather than from zero.
    $alreadyTicked = collect($choices)->filter(fn ($choice): bool => (bool) old($choice->value))->count();
  @endphp

  <div class="px-6 py-8 lg:px-12 lg:py-10">
    <div class="mx-auto w-full max-w-5xl">
      <a href="{{ route('upgrade.index') }}" data-turbo="true" class="flex w-fit items-center gap-2 text-[13px] font-medium text-muted transition-colors hover:text-ink">
        @svg('lucide-arrow-left', 'size-4')
        {{ __('Back to the plan') }}
      </a>

      <x-form
        method="post"
        :action="route('upgrade.confirm.create')"
        x-data="{ ticked: {{ $alreadyTicked }} }"
        class="mt-6 flex flex-col gap-5 lg:flex-row lg:items-start"
      >
        <div class="flex min-w-0 flex-1 flex-col gap-4">
          <div>
            <h1 class="max-w-xl text-[30px] leading-tight font-semibold tracking-tight text-ink">
              {{ __('Before you pay, let us be completely straight with each other.') }}
            </h1>
            <p class="mt-3.5 max-w-xl text-[15px] leading-relaxed text-muted">
              {{ __(':name is a small project with no investors and no support team. That is why it costs :price once instead of a few dollars every month, and it is also why the next few paragraphs matter more here than they would at a big company.', ['name' => config('app.name'), 'price' => $price]) }}
            </p>
          </div>

          <div class="overflow-hidden rounded-lg border border-hairline bg-canvas">
            <div class="flex items-center gap-2.5 border-b border-hairline bg-warning/10 px-5 py-4">
              @svg('lucide-circle-alert', 'size-4 shrink-0 text-warning')
              <p class="text-sm font-semibold text-warning">{{ __('This purchase is final. There are no refunds.') }}</p>
            </div>

            <div class="flex flex-col gap-4 p-5">
              <p class="text-[15px] leading-relaxed text-body">
                {{ __('We do not offer refunds, and we would rather say that plainly here than bury it in terms you will not read. A reversal costs the project the payment fee, the chargeback penalty and an afternoon of paperwork, which is a genuinely painful hit at this size.') }}
              </p>
              <p class="text-[15px] leading-relaxed text-body">
                {{ __('So please do not file a chargeback with your bank. If anything at all goes wrong, a payment that did not unlock, a bug, or a feature that does not work the way this page implied, email us and we will fix it. A real person reads that inbox.') }}
              </p>

              <div class="flex flex-col gap-3 rounded-md border border-hairline bg-card p-4">
                <p class="text-xs font-bold tracking-wide text-muted-soft uppercase">{{ __('What we can do instead') }}</p>
                @foreach ($instead as $line)
                  <p class="flex items-start gap-2.5">
                    @svg('lucide-check', 'mt-1 size-3.5 shrink-0 text-success')
                    <span class="text-sm leading-relaxed text-body">{{ $line }}</span>
                  </p>
                @endforeach
              </div>
            </div>
          </div>

          <div class="flex flex-wrap items-center gap-4 rounded-lg border border-hairline bg-canvas p-5">
            <div class="min-w-0 flex-1">
              <p class="text-[15px] font-semibold tracking-tight text-ink">{{ __('Still unsure? Self-hosting is free and always will be.') }}</p>
              <p class="mt-1.5 text-sm leading-relaxed text-muted">{{ __('The same code, the same features, no item limit and no payment. If :price feels like a stretch, or you would simply rather own the server, take that route with our blessing. It is the honest alternative, not a downgrade.', ['price' => $price]) }}</p>
            </div>

            <x-button.secondary :href="route('marketing.docs.portal.show', ['section' => 'self-hosting', 'slug' => 'self-hosting'])">
              {{ __('Read the guide') }}
            </x-button.secondary>
          </div>

          <div class="overflow-hidden rounded-lg border border-hairline bg-canvas">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-hairline-soft px-5 py-4">
              <p class="text-[15px] font-semibold tracking-tight text-ink">{{ __('Please confirm you have read the above') }}</p>
              <p class="font-mono text-[11.5px] text-muted-soft">
                <span x-text="ticked">{{ $alreadyTicked }}</span>
                {{ __('of :total confirmed', ['total' => count($choices)]) }}
              </p>
            </div>

            @foreach ($choices as $choice)
              <label class="flex cursor-pointer items-start gap-3.5 border-b border-hairline-soft px-5 py-4 last:border-b-0 hover:bg-card">
                <input
                  type="checkbox"
                  name="{{ $choice->value }}"
                  value="1"
                  x-on:change="ticked += $event.target.checked ? 1 : -1"
                  @checked(old($choice->value))
                  class="mt-0.5 size-5 shrink-0 cursor-pointer rounded border-hairline text-ink focus:ring-ink"
                />
                <span class="text-sm leading-relaxed text-body">{{ $choice->label() }}</span>
              </label>
            @endforeach

            @error($choices[0]->value)
              <p class="border-t border-hairline-soft px-5 py-3 text-sm text-error">{{ __('Every point has to be confirmed before you can continue.') }}</p>
            @enderror
          </div>

          <p class="max-w-2xl text-[13px] leading-relaxed text-muted-soft">
            {{ __('By continuing you agree to the terms and to a one-time, non-refundable charge of :price. Payment is handled by our processor, and we never see your card details.', ['price' => $price]) }}
          </p>
        </div>

        <div class="flex w-full shrink-0 flex-col gap-3.5 lg:sticky lg:top-6 lg:w-80">
          <div class="overflow-hidden rounded-lg border border-hairline bg-canvas">
            <p class="border-b border-hairline-soft px-5 py-3.5 text-[11.5px] font-bold tracking-wide text-muted-soft uppercase">{{ __('Your order') }}</p>

            <div class="flex flex-col gap-3.5 p-5">
              <div>
                <p class="text-[15px] font-semibold text-ink">{{ __(':name account unlock', ['name' => config('app.name')]) }}</p>
                <p class="mt-1 text-[13px] leading-relaxed text-muted">{{ __('Unlimited items on this account. One payment, no renewal.') }}</p>
              </div>

              <div class="flex items-baseline justify-between gap-3 border-t border-hairline-soft pt-3.5">
                <span class="text-[13.5px] text-body">{{ __('Account unlock, one-time') }}</span>
                <span class="font-mono text-[13px] text-body">{{ $price }}</span>
              </div>
              <div class="flex items-baseline justify-between gap-3">
                <span class="text-[13.5px] text-muted">{{ __('Recurring charges') }}</span>
                <span class="font-mono text-[13px] text-muted">{{ __('None') }}</span>
              </div>

              <div class="flex items-baseline justify-between gap-3 border-t border-hairline pt-3.5">
                <span class="text-sm font-semibold text-ink">{{ __('Total due today') }}</span>
                <span class="text-2xl font-semibold tracking-tight text-ink">{{ $price }}</span>
              </div>

              <p class="text-[12.5px] leading-relaxed text-muted-soft">{{ __('Then nothing, forever. There is no second invoice.') }}</p>
            </div>
          </div>

          <div class="flex flex-col gap-3 rounded-lg border border-hairline bg-canvas p-5">
            <p class="text-[11.5px] font-bold tracking-wide text-muted-soft uppercase">{{ __('Unlocking for') }}</p>
            <div class="flex items-center gap-3">
              <x-avatar :user="auth()->user()" :size="34" class="size-9 shrink-0 text-xs" />
              <div class="min-w-0">
                <p class="truncate text-sm font-semibold text-ink">{{ $account->name }}</p>
                <p class="truncate text-[12.5px] text-muted-soft">{{ trans_choice(':count item|:count items', $used, ['count' => $used]) }}</p>
              </div>
            </div>
          </div>

          <x-button type="submit" class="w-full" x-bind:disabled="ticked < {{ count($choices) }}">
            {{ __('Continue to payment') }}
          </x-button>

          <p class="text-center text-[12.5px] leading-relaxed text-muted-soft" x-show="ticked < {{ count($choices) }}">
            {{ __('Every box has to be ticked first.') }}
          </p>

          <x-button.secondary :href="route('upgrade.index')" turbo class="w-full">
            {{ __('Take me back, I will think about it') }}
          </x-button.secondary>

          <div class="flex flex-col gap-2.5 rounded-lg border border-hairline bg-canvas p-5">
            @foreach ($assurances as $assurance)
              <p class="flex items-start gap-2.5">
                @svg('lucide-shield', 'mt-0.5 size-3.5 shrink-0 text-muted-soft')
                <span class="text-[12.5px] leading-relaxed text-muted">{{ $assurance }}</span>
              </p>
            @endforeach
          </div>
        </div>
      </x-form>
    </div>
  </div>
</x-app-layout>
