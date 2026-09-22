<x-app-layout>
  <x-slot:title>
    {{ __('Plan and billing') }}
  </x-slot>

  @php
    $price = '$' . config('pricing.price');

    // The copy lives here rather than in the controller: it is a page of prose,
    // and the controller has no business holding sentences.
    $nowState = [
        ['tone' => 'ok', 'title' => __('Everything is safe'), 'note' => __('All :count of your items, their photos and their valuations are stored and untouched.', ['count' => $used])],
        ['tone' => 'ok', 'title' => __('Still fully readable'), 'note' => __('Browse, search, share and export exactly as before.')],
        ['tone' => 'ok', 'title' => __('Nothing is hidden'), 'note' => __('No item has been deleted, locked or downgraded.')],
        ['tone' => 'warn', 'title' => __('Paused once you reach :count', ['count' => $hardLimit]), 'note' => __('Adding a new item waits until the account is unlocked.')],
    ];

    $paidPerks = [
        __('Unlimited items and collections, with no ceiling on records'),
        __('Keeps the :count items you have already catalogued, untouched', ['count' => $used]),
        __('Hosted, backed up and updated by us'),
        __('Every feature included, with no tiers and no upsells'),
        __('One payment, non-refundable, supported by the person who wrote the code'),
    ];

    $hostPerks = [
        __('No item limit and no licence fee, ever'),
        __('Your database lives on hardware you control'),
        __('One Docker command, and updates when you choose'),
        __('The same open source code that runs this app'),
    ];

    $unlocks = [
        ['title' => __('No item ceiling'), 'body' => __('Catalogue forty items or forty thousand. The limit disappears the moment the payment clears.')],
        ['title' => __('Photos and documents'), 'body' => __('Keep attaching images, receipts and certificates to everything you own.')],
        ['title' => __('Valuation history'), 'body' => __('Keep the whole price timeline of an item rather than only its last number.')],
        ['title' => __('Loans and sharing'), 'body' => __('Track what left the shelf, and share a read-only view of any collection.')],
        ['title' => __('Backups'), 'body' => __('Your account is backed up for you, and stays exportable if you ever leave.')],
        ['title' => __('Support from a person'), 'body' => __('Questions reach the inbox of the person who builds this, not a script.')],
    ];

    $faqs = [
        [__('Is :price really a one-time payment?', ['price' => $price]), __('Yes. One payment, one account, unlocked for good. There is no renewal, no annual invoice and no price that quietly changes next year.')],
        [__('What happens to my :count items right now?', ['count' => $used]), __('They stay exactly where they are. Nothing is deleted, hidden or downgraded. You can browse, search, edit and export all of them.')],
        [__('Can I move to self-hosting instead?'), __('Yes, and the door swings both ways. Self-hosting is free, runs the same code, and has no item limit at all.')],
        [__('Why is there a limit at all?'), __('Hosting, backups and photo storage cost money, and we would rather charge once than sell your data or bolt on advertising. Ten items is enough to see whether :name suits how you catalogue before you pay anything.', ['name' => config('app.name')])],
        [__('Is the payment refundable?'), __('No, and we say so here rather than in small print you will not read. If something goes wrong, email us and we will fix it. If you would rather not pay at all, self-hosting is free and always will be.')],
    ];
  @endphp

  <div class="px-6 py-8 lg:px-12 lg:py-10">
    <div class="mx-auto flex w-full max-w-4xl flex-col gap-7">
      <div class="flex flex-col gap-3.5">
        <span class="inline-flex w-fit items-center gap-2 rounded-full bg-warning/10 px-3 py-1.5 text-xs font-semibold text-warning">
          @svg('lucide-lock', 'size-3.5')
          {{ __(':used of :limit free items used', ['used' => $used, 'limit' => $freeLimit]) }}
        </span>

        <h1 class="max-w-2xl text-[32px] leading-tight font-semibold tracking-tight text-ink">
          @if ($isOverFreeLimit)
            {{ __('Your collection outgrew the free plan. Nice work.') }}
          @else
            {{ __('The free plan holds :limit items. Here is what comes next.', ['limit' => $freeLimit]) }}
          @endif
        </h1>

        <p class="max-w-xl text-base leading-relaxed text-muted">
          {{ __('A free account holds :limit items, and :grace more as a grace. Unlock everything with a single :price payment, once and not monthly, or run :name on your own server for free. Both keep every item you have already added.', ['limit' => $freeLimit, 'grace' => $hardLimit - $freeLimit, 'price' => $price, 'name' => config('app.name')]) }}
        </p>
      </div>

      <div class="flex flex-col gap-4 rounded-lg border border-hairline bg-canvas p-6">
        <div class="flex flex-wrap items-end justify-between gap-5">
          <div>
            <p class="text-[13px] font-semibold text-muted">{{ __('Items in this account') }}</p>
            <p class="mt-1.5 flex items-baseline gap-2">
              <span class="text-[32px] font-semibold tracking-tight text-ink">{{ number_format($used) }}</span>
              <span class="text-[15px] text-muted-soft">{{ __('of :limit included', ['limit' => $freeLimit]) }}</span>
            </p>
          </div>

          <p class="text-right text-[13px] leading-relaxed text-muted">
            {{ __('Nothing has been deleted or hidden.') }}<br />
            {{ trans_choice('Your :count item is still here.|Your :count items are all still here.', $used, ['count' => $used]) }}
          </p>
        </div>

        <div class="flex h-3 overflow-hidden rounded-full bg-card">
          <div class="bg-ink" style="width: {{ $freeWidth }}%"></div>
          <div class="bg-warning" style="width: {{ $overWidth }}%"></div>
        </div>

        <div class="flex flex-wrap gap-5">
          <span class="flex items-center gap-2 text-[13px] text-body">
            <span class="size-2.5 shrink-0 rounded-sm bg-ink"></span>
            {{ __('The first :limit items, included free', ['limit' => $freeLimit]) }}
          </span>
          @if ($over > 0)
            <span class="flex items-center gap-2 text-[13px] text-body">
              <span class="size-2.5 shrink-0 rounded-sm bg-warning"></span>
              {{ trans_choice(':count item over the free plan, saved and readable|:count items over the free plan, saved and readable', $over, ['count' => $over]) }}
            </span>
          @endif
        </div>

        <div class="grid gap-px overflow-hidden rounded-lg border border-hairline-soft bg-hairline-soft sm:grid-cols-2">
          @foreach ($nowState as $state)
            <div class="flex items-start gap-2.5 bg-canvas p-4">
              @if ($state['tone'] === 'ok')
                @svg('lucide-circle-check', 'mt-0.5 size-4 shrink-0 text-success')
              @else
                @svg('lucide-circle-alert', 'mt-0.5 size-4 shrink-0 text-warning')
              @endif
              <div>
                <p class="text-[13.5px] font-semibold text-ink">{{ $state['title'] }}</p>
                <p class="mt-0.5 text-[12.5px] leading-relaxed text-muted">{{ $state['note'] }}</p>
              </div>
            </div>
          @endforeach
        </div>
      </div>

      <div class="grid gap-4 lg:grid-cols-2">
        <div class="relative flex flex-col gap-5 rounded-lg border-2 border-ink bg-canvas p-6">
          <span class="absolute -top-2.5 left-6 rounded bg-ink px-2.5 py-0.5 text-[11px] font-bold tracking-wide text-page uppercase">{{ __('Most people pick this') }}</span>

          <div>
            <h2 class="text-lg font-semibold tracking-tight text-ink">{{ __('Unlock this account') }}</h2>
            <p class="mt-1.5 text-sm leading-relaxed text-muted">{{ __('One payment, hosted by us, unlimited items forever. Keep using the account exactly as it is now.') }}</p>
          </div>

          <p class="flex items-end gap-2.5">
            <span class="text-[44px] leading-none font-semibold tracking-tight text-ink">{{ $price }}</span>
            <span class="pb-1.5 text-sm text-muted">{{ __('once, no subscription') }}</span>
          </p>

          <ul class="flex flex-col gap-2.5">
            @foreach ($paidPerks as $perk)
              <li class="flex items-start gap-2.5">
                @svg('lucide-check', 'mt-1 size-4 shrink-0 text-success')
                <span class="text-sm leading-relaxed text-body">{{ $perk }}</span>
              </li>
            @endforeach
          </ul>

          <div class="flex-1"></div>

          @if ($canPurchase)
            <x-button :href="route('upgrade.confirm.new')" turbo class="w-full">
              {{ __('Unlock for :price', ['price' => $price]) }}
              <x-slot:icon>
                @svg('lucide-arrow-right', 'size-4')
              </x-slot>
            </x-button>
          @else
            <p class="rounded-md border border-hairline bg-card p-3 text-[13px] leading-relaxed text-muted">
              {{ __('Only an owner of this account can unlock it. Ask one of them to take a look at this page.') }}
            </p>
          @endif
        </div>

        <div class="flex flex-col gap-5 rounded-lg border border-hairline bg-card p-6">
          <div>
            <h2 class="text-lg font-semibold tracking-tight text-ink">{{ __('Or host it yourself') }}</h2>
            <p class="mt-1.5 text-sm leading-relaxed text-muted">{{ __(':name is MIT licensed and open source. Run it on your own machine and there is no limit and no fee, ever.', ['name' => config('app.name')]) }}</p>
          </div>

          <p class="flex items-end gap-2.5">
            <span class="text-[44px] leading-none font-semibold tracking-tight text-ink">{{ __('Free') }}</span>
            <span class="pb-1.5 text-sm text-muted">{{ __('your server, your rules') }}</span>
          </p>

          <ul class="flex flex-col gap-2.5">
            @foreach ($hostPerks as $perk)
              <li class="flex items-start gap-2.5">
                @svg('lucide-check', 'mt-1 size-4 shrink-0 text-muted')
                <span class="text-sm leading-relaxed text-body">{{ $perk }}</span>
              </li>
            @endforeach
          </ul>

          <code class="overflow-x-auto rounded-md border border-hairline bg-canvas px-3.5 py-3 font-mono text-xs whitespace-nowrap text-body">docker compose up -d</code>

          <div class="flex-1"></div>

          <x-button.secondary :href="route('marketing.docs.portal.show', ['section' => 'self-hosting', 'slug' => 'self-hosting'])" class="w-full">
            {{ __('Read the self-hosting guide') }}
          </x-button.secondary>
        </div>
      </div>

      <x-box :title="__('What the :price unlocks', ['price' => $price])" padding="p-0">
        <x-slot:description>
          {{ __('The same features either way. The fee only covers our hosting.') }}
        </x-slot>

        <div class="grid gap-px overflow-hidden rounded-lg bg-hairline-soft sm:grid-cols-2 lg:grid-cols-3">
          @foreach ($unlocks as $unlock)
            <div class="flex flex-col gap-1.5 bg-canvas p-5">
              <p class="text-sm font-semibold text-ink">{{ $unlock['title'] }}</p>
              <p class="text-[13px] leading-relaxed text-muted">{{ $unlock['body'] }}</p>
            </div>
          @endforeach
        </div>
      </x-box>

      <x-box :title="__('Questions people ask at this exact moment')" padding="p-0">
        @foreach ($faqs as [$question, $answer])
          <details class="border-b border-hairline-soft last:border-b-0">
            <summary class="flex cursor-pointer items-center gap-3.5 px-4 py-4 text-sm font-medium text-ink hover:bg-card">
              {{ $question }}
            </summary>
            <p class="max-w-3xl px-4 pb-4 text-sm leading-relaxed text-muted">{{ $answer }}</p>
          </details>
        @endforeach
      </x-box>

      <div class="flex flex-wrap items-center gap-5 rounded-lg border border-hairline bg-card p-6">
        <div class="min-w-0 flex-1">
          <p class="text-[17px] font-semibold tracking-tight text-ink">{{ __('Not ready to decide today?') }}</p>
          <p class="mt-1.5 text-sm leading-relaxed text-muted">{{ __('That is fine. Your account stays exactly as it is: readable, searchable and exportable. Come back whenever, and pick up where you left off.') }}</p>
        </div>

        <x-button.secondary :href="route('collections.index')" turbo>
          {{ __('Back to my collections') }}
        </x-button.secondary>
      </div>
    </div>
  </div>
</x-app-layout>
