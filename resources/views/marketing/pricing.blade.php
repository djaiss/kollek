{{-- The marketing pricing page, with its interactive pricing calculator. --}}

<x-marketing-layout>
  <style>
    input[type="range"].price-slider {
        -webkit-appearance: none;
        appearance: none;
        width: 100%;
        height: 6px;
        border-radius: 9999px;
        background: var(--hairline);
        outline: none;
    }
    input[type="range"].price-slider::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: 18px;
        height: 18px;
        border-radius: 9999px;
        background: var(--ink);
        cursor: pointer;
        border: 2px solid var(--page);
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.25);
    }
    input[type="range"].price-slider::-moz-range-thumb {
        width: 18px;
        height: 18px;
        border-radius: 9999px;
        background: var(--ink);
        cursor: pointer;
        border: 2px solid var(--page);
    }
    @keyframes price-pop {
        0% { transform: scale(0.96); }
        60% { transform: scale(1.02); }
        100% { transform: scale(1); }
    }
    .price-pop { animation: price-pop 0.3s ease; }
  </style>

  <section id="top" class="mx-auto max-w-[1200px] scroll-mt-24 px-5 pt-16 text-center sm:px-8 sm:pt-24">
    <div class="mb-7 inline-flex items-center gap-x-2 rounded-full bg-card py-1.5 pr-3.5 pl-1.5 text-[13px] font-semibold text-ink">
      <span class="rounded-full bg-primary px-2 py-[3px] text-[11px] text-on-primary">{{ __('NO SUBSCRIPTION') }}</span>
      {{ __('Pay once. Own it forever.') }}
    </div>

    <h1 class="mx-auto max-w-[840px] text-[32px] leading-[1.08] font-semibold tracking-[-1px] text-balance text-ink sm:text-5xl sm:tracking-[-1.5px] lg:text-[64px] lg:leading-[1.05] lg:tracking-[-2px]">
      {{ __("Pricing so simple, it's almost unbelievable.") }}
    </h1>

    <p class="mx-auto mt-6 max-w-[600px] text-[17px] leading-relaxed text-muted sm:text-[19px]">
      {{ __("One price. One time. No monthly nibbling at your wallet. Your first ten items on the cloud cost nothing, so you can see whether we suit you before paying. Or, and we genuinely mean this, host it yourself for free and pay us nothing at all. We won't come back asking for more.") }}
    </p>
  </section>

  <section class="mx-auto mt-14 max-w-[1200px] px-5 sm:px-8">
    <div class="rounded-xl bg-[#101010] p-6 text-white sm:p-12">
      <div class="grid grid-cols-1 items-center gap-10 lg:grid-cols-2 lg:gap-12">
        <div>
          <div class="mb-5 inline-flex items-center gap-x-2 rounded-full bg-[#1a1a1a] px-3 py-1.5 text-xs font-semibold text-[#a1a1aa]">
            <span class="text-badge-emerald" aria-hidden="true">&bull;</span> {{ __('HONESTLY, DO THIS ONE') }}
          </div>

          <h2 class="text-[28px] leading-[1.12] font-semibold tracking-[-1px] sm:text-4xl">{{ __("Self-host it. It's free, and we recommend it.") }}</h2>
          <p class="mt-4.5 mb-6 text-base leading-relaxed text-[#a1a1aa]">
            {{ __("Most billing pages bury the free option. We're putting ours first, because it's genuinely the best deal around. One Docker command and KolleK is running on your own hardware: your data, your rules, zero dollars. Genuinely good, if we say so ourselves.") }}
          </p>

          <div class="mb-7 flex flex-col gap-y-3">
            @foreach ([
                __('Free forever, not a trial or a tease'),
                __('One Docker command and you are up and running'),
                __('Your data on your hardware, always'),
                __('Same full app as the paid cloud'),
            ] as $point)
              <div class="flex items-center gap-x-3 text-[15px] text-[#e5e7eb]">
                <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-[#1a1a1a]">
                  <x-lucide-check class="h-[11px] w-[11px] text-badge-emerald" stroke-width="3" />
                </span>
                {{ $point }}
              </div>
            @endforeach
          </div>

          <a href="{{ config('marketing.github_url') }}" target="_blank" rel="noopener" class="inline-flex h-12 items-center gap-x-2.5 rounded-md bg-white px-5.5 text-[15px] font-semibold text-[#111111] transition-colors hover:bg-[#e5e7eb]">
            {{ __('Read the self-host docs') }}
          </a>
        </div>

        <div class="overflow-hidden rounded-lg bg-[#1a1a1a]">
          <div class="flex items-center gap-x-2 border-b border-[#242424] px-4 py-3">
            <span class="h-2.5 w-2.5 rounded-full bg-[#333333]"></span>
            <span class="h-2.5 w-2.5 rounded-full bg-[#333333]"></span>
            <span class="h-2.5 w-2.5 rounded-full bg-[#333333]"></span>
            <span class="ml-2 font-mono text-xs text-[#6b7280]">{{ __('terminal, get it running') }}</span>
          </div>
          <div class="overflow-x-auto px-4.5 py-4.5 font-mono text-[13px] leading-relaxed whitespace-nowrap">
            <p class="text-[#6b7280]"># {{ __('pull it, run it, done, $0 forever') }}</p>
            <p><span class="text-badge-emerald">$</span> <span class="text-[#e5e7eb]">docker run -p 8080:8080 \</span></p>
            <p class="pl-4 text-[#e5e7eb]">-v kollek:/data ghcr.io/kollek/app</p>
            <p class="mt-2.5 text-[#6b7280]">&check; {{ __('pulling image…') }}</p>
            <p class="text-[#6b7280]">&check; {{ __('starting the app…') }}</p>
            <p class="mt-1.5 text-badge-emerald">&check; {{ __('listening on http://localhost:8080') }}</p>
            <p class="mt-2.5 text-[#6b7280]"># {{ __("that's the whole invoice. seriously.") }}</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section id="buy" class="mx-auto max-w-[1200px] scroll-mt-24 px-5 pt-16 sm:px-8 sm:pt-24">
    <div class="mb-12 text-center">
      <h2 class="text-[28px] leading-[1.1] font-semibold tracking-[-1px] text-ink sm:text-4xl lg:text-5xl lg:tracking-[-1.5px]">{{ __('Two ways in. Both fair.') }}</h2>
      <p class="mx-auto mt-4.5 max-w-[520px] text-[17px] text-muted">
        {{ __('Roll up your sleeves and self-host for free, with no limits at all, or let us handle the ops. A cloud account holds ten items free, and a single one-time $49 unlocks it for good. No fine print, no surprises.') }}
      </p>
    </div>

    <div class="mx-auto grid max-w-[840px] grid-cols-1 gap-6 md:grid-cols-2">
      <div class="flex flex-col rounded-lg border border-hairline bg-canvas p-8">
        <p class="text-[22px] font-semibold tracking-[-0.3px] text-ink">{{ __('Self-host') }}</p>
        <p class="mt-1.5 text-sm text-muted">{{ __("The thrifty collector's choice.") }}</p>
        <p class="mt-4 text-[34px] font-semibold tracking-[-1px] text-ink">$0<span class="text-[15px] font-medium text-muted"> &middot; {{ __('forever') }}</span></p>

        <div class="my-7 flex flex-col gap-y-3">
          @foreach ([
              __('$0, forever'),
              __('One-command Docker deploy'),
              __('Full control of your data'),
              __('Unlimited everything, no item cap'),
              __('Updates via docker pull'),
          ] as $feature)
            <div class="flex items-center gap-x-2.5 text-[15px] text-body">
              <x-lucide-check class="h-4 w-4 shrink-0 text-ink" stroke-width="2.4" />
              {{ $feature }}
            </div>
          @endforeach
        </div>

        <div class="flex-1"></div>
        <a href="{{ config('marketing.github_url') }}" target="_blank" rel="noopener" class="flex h-11 items-center justify-center rounded-md border border-hairline bg-canvas text-sm font-semibold text-ink transition-colors hover:bg-sidebar">docker run &amp; go</a>
      </div>

      <div class="relative flex flex-col rounded-lg bg-[#101010] p-8 text-white">
        <span class="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-badge-emerald px-3 py-[5px] text-[11px] font-bold tracking-[0.4px] text-[#08331f]">{{ __('MOST POPULAR') }}</span>

        <div class="flex items-center gap-x-2.5">
          <p class="text-[22px] font-semibold tracking-[-0.3px]">{{ __('Cloud') }}</p>
          <span class="rounded-full bg-[#1a1a1a] px-2 py-[3px] text-[11px] font-semibold text-badge-emerald">{{ __('PAY ONCE') }}</span>
        </div>
        <p class="mt-1.5 text-sm text-[#a1a1aa]">{{ __("For collectors who'd rather not run servers.") }}</p>
        <div class="mt-4 flex flex-wrap items-baseline gap-x-2">
          <p class="text-[34px] font-semibold tracking-[-1px]">$49<span class="text-[15px] font-medium text-[#a1a1aa]"> {{ __('once') }}</span></p>
          <span class="text-[13px] text-[#6b7280] line-through">{{ __('$6/mo forever') }}</span>
        </div>

        <div class="my-7 flex flex-col gap-y-3">
          @foreach ([
              __('Ten items free before you pay a cent'),
              __('One payment, no renewals'),
              __('Lifetime managed hosting'),
              __('Automatic updates & backups'),
              __('Unlimited members & storage'),
          ] as $feature)
            <div class="flex items-center gap-x-2.5 text-[15px] text-[#e5e7eb]">
              <x-lucide-check class="h-4 w-4 shrink-0 text-badge-emerald" stroke-width="2.4" />
              {{ $feature }}
            </div>
          @endforeach
        </div>

        <div class="flex-1"></div>
        <a href="{{ route('register') }}" class="flex h-11 items-center justify-center rounded-md bg-white text-sm font-semibold text-[#111111] transition-colors hover:bg-[#e5e7eb]">{{ __('Buy once, $49') }}</a>
      </div>
    </div>

    <p class="mt-6 text-center text-[13px] text-muted-soft">{{ __('No hidden fees. No auto-renewals. No surprise charges on your credit card.') }}</p>
  </section>

  @php
    $sliders = [
        ['key' => 'items', 'label' => __('Items in your collection'), 'hint' => __('The serious one. Really weigh this.'), 'min' => 0, 'max' => 100000, 'step' => 100, 'display' => 'itemsDisplay'],
        ['key' => 'collections', 'label' => __('Number of collections'), 'hint' => __('Books, wine, that box of cables, all of it.'), 'min' => 1, 'max' => 500, 'step' => 1, 'display' => 'fmt(collections)'],
        ['key' => 'members', 'label' => __('Team members'), 'hint' => __('Fellow collectors sharing the workspace.'), 'min' => 1, 'max' => 250, 'step' => 1, 'display' => 'fmt(members)'],
        ['key' => 'storage', 'label' => __('Storage needed (GB)'), 'hint' => __('For all those high-res cover scans.'), 'min' => 1, 'max' => 2000, 'step' => 1, 'display' => 'storageDisplay'],
        ['key' => 'chaos', 'label' => __('Current shelf chaos level'), 'hint' => __('0 = librarian. 100 = archaeological dig.'), 'min' => 0, 'max' => 100, 'step' => 1, 'display' => "chaos + '%'"],
        ['key' => 'giveups', 'label' => __('Times you alphabetized then gave up'), 'hint' => __('Be honest. Nobody is judging. Much.'), 'min' => 0, 'max' => 50, 'step' => 1, 'display' => 'giveupsDisplay'],
        ['key' => 'raccoons', 'label' => __('Raccoons infiltrating the collection'), 'hint' => __('A known threat to any well-organized shelf.'), 'min' => 0, 'max' => 12, 'step' => 1, 'display' => 'raccoonsDisplay'],
    ];

    $toggles = [
        ['key' => 'cloudBackups', 'label' => __('Encrypted daily backups'), 'hint' => __('A genuinely good idea. Included regardless.')],
        ['key' => 'countsShelf', 'label' => __('You count your shelf as furniture'), 'hint' => __('Structurally load-bearing books qualify.')],
        ['key' => 'labelMaker', 'label' => __('You own a label maker'), 'hint' => __('And, be honest, you love it.')],
        ['key' => 'namedItems', 'label' => __('You have named individual items'), 'hint' => __('Greg the first-edition. We know.')],
    ];

    /*
     * The enthusiasm levels are keyed by the value the calculator keeps in its state, so
     * the joke survives translation: the browser still compares 'Rabid' to 'Rabid' while
     * the visitor reads their own language.
     */
    $enthusiasms = [
        'Casual' => __('Casual'),
        'Keen' => __('Keen'),
        'Rabid' => __('Rabid'),
        'Unhinged' => __('Unhinged'),
    ];

    /*
     * The same sentences the quote panel renders in the browser, handed to it from here so
     * they go through __() like everything else on the page, placeholders and all.
     */
    $calculator = [
        'locale' => str_replace('_', '-', app()->getLocale()),
        'enthusiasms' => $enthusiasms,
        'labels' => [
            'items' => __(':count items'),
            'storage' => __(':count GB'),
            'raccoonOne' => __('1 raccoon'),
            'raccoonMany' => __(':count raccoons'),
            'baseLicense' => __('Base license (one time)'),
            'itemsLine' => __(':count items × $0.00'),
            'memberOne' => __('1 team member, unlimited'),
            'memberMany' => __(':count team members, unlimited'),
            'storageLine' => __(':count GB storage'),
            'chaosLine' => __('Shelf-chaos handling fee (:percent%)'),
            'raccoonLine' => __('Raccoon containment (:count)'),
            'enthusiasmLine' => __('Enthusiasm multiplier (:level)'),
            'rebateLine' => __('Label-maker rebate'),
            'included' => __('included'),
            'waived' => __('waived'),
            'surchargeWaived' => __(':amount → waived'),
        ],
        'quips' => [
            __('Math checks out. It always does.'),
            __('Our imaginary accountant approves.'),
            __('Suspiciously round. Beautifully flat.'),
            __('The algorithm has spoken. Loudly. $49.'),
            __('No matter how you slice the log: $49.'),
        ],
    ];
  @endphp

  <section id="calculator" class="mx-auto max-w-[1200px] scroll-mt-24 px-5 pt-16 sm:px-8 sm:pt-24" x-data="pricingCalculator(@js($calculator))">
    <div class="mb-12 text-center">
      <p class="mb-3.5 text-[13px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ __('The Suspiciously Accurate Pricing Calculator') }}&trade;</p>
      <h2 class="text-[28px] leading-[1.1] font-semibold tracking-[-1px] text-ink sm:text-4xl lg:text-5xl lg:tracking-[-1.5px]">{{ __('Configure your quote.') }}</h2>
      <p class="mx-auto mt-4.5 max-w-[560px] text-[17px] text-muted">
        {{ __("Our proprietary algorithm weighs :count rigorous factors to calculate your exact, personalized price. Drag away. We'll wait.", ['count' => count($sliders) + count($toggles) + 1]) }}
      </p>
    </div>

    <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-[1.35fr_1fr]">
      <div class="flex flex-col gap-y-6 rounded-xl bg-card p-6 sm:p-8">
        @foreach ($sliders as $slider)
          <div>
            <div class="mb-3 flex items-baseline justify-between gap-3">
              <div>
                <p class="text-[15px] font-semibold text-ink">{{ $slider['label'] }}</p>
                <p class="mt-0.5 text-xs text-muted-soft">{{ $slider['hint'] }}</p>
              </div>
              <span class="shrink-0 rounded-md border border-hairline bg-page px-2.5 py-1 font-mono text-[13px] font-medium text-body whitespace-nowrap" x-text="{{ $slider['display'] }}"></span>
            </div>
            <input type="range" class="price-slider" min="{{ $slider['min'] }}" max="{{ $slider['max'] }}" step="{{ $slider['step'] }}" x-model.number="{{ $slider['key'] }}" />
          </div>
        @endforeach

        <div class="h-px bg-hairline"></div>

        <div class="flex flex-col gap-y-4">
          @foreach ($toggles as $toggle)
            <button type="button" @click="{{ $toggle['key'] }} = ! {{ $toggle['key'] }}" class="flex items-center justify-between gap-4 text-left">
              <span>
                <span class="block text-[15px] font-semibold text-ink">{{ $toggle['label'] }}</span>
                <span class="mt-0.5 block text-xs text-muted-soft">{{ $toggle['hint'] }}</span>
              </span>
              <span class="relative h-6 w-11 shrink-0 rounded-full transition-colors" :class="{{ $toggle['key'] }} ? 'bg-ink' : 'bg-hairline'">
                <span class="absolute top-0.5 left-0.5 h-5 w-5 rounded-full bg-white shadow transition-transform" :class="{{ $toggle['key'] }} ? 'translate-x-[22px]' : ''"></span>
              </span>
            </button>
          @endforeach
        </div>

        <div class="h-px bg-hairline"></div>

        <div>
          <p class="text-[15px] font-semibold text-ink">{{ __('Level of collecting enthusiasm') }}</p>
          <p class="mt-0.5 mb-3 text-xs text-muted-soft">{{ __('This dramatically affects nothing.') }}</p>
          <div class="flex flex-wrap gap-2">
            @foreach ($enthusiasms as $value => $label)
              <button
                type="button"
                @click="enthusiasm = '{{ $value }}'"
                :class="enthusiasm === '{{ $value }}' ? 'border-primary bg-primary text-on-primary' : 'border-hairline bg-canvas text-ink'"
                class="rounded-md border px-3.5 py-2 text-[13px] font-semibold transition-colors"
              >{{ $label }}</button>
            @endforeach
          </div>
        </div>
      </div>

      <div class="sticky top-24 overflow-hidden rounded-xl border border-hairline bg-canvas shadow-[0_12px_40px_rgba(17,17,17,0.08)]">
        <div class="border-b border-hairline-soft px-7 py-5">
          <p class="text-xs font-semibold tracking-[0.5px] text-muted-soft uppercase">{{ __('Your itemized estimate') }}</p>
          <p class="mt-0.5 text-xs text-muted-soft">{{ __('Recalculated live, with real math (trust us).') }}</p>
        </div>

        <div class="flex flex-col px-7 py-2">
          <template x-for="item in lineItems" :key="item.label">
            <div class="flex items-center justify-between gap-3 border-b border-hairline-soft py-2.5">
              <span class="min-w-0 text-[13px] text-body" x-text="item.label"></span>
              <span class="shrink-0 font-mono text-[13px] whitespace-nowrap text-muted-soft" x-text="item.value"></span>
            </div>
          </template>

          <div class="flex items-center justify-between gap-3 pt-3.5 pb-1">
            <span class="text-[13px] font-semibold text-body">{{ __('Subtotal') }}</span>
            <span class="font-mono text-[13px] text-body">$49.00</span>
          </div>
          <div class="flex items-center justify-between gap-3 pt-1 pb-3.5">
            <span class="text-[13px] text-muted-soft">{{ __('Complexity surcharge') }}</span>
            <span class="font-mono text-[13px] text-badge-emerald">&ndash;$0.00</span>
          </div>
        </div>

        <div class="bg-[#101010] px-7 py-7 text-white">
          <div class="flex items-baseline justify-between">
            <span class="text-sm font-medium text-[#a1a1aa]">{{ __('Your total, one time') }}</span>
            <span class="price-pop text-[40px] font-semibold tracking-[-1.5px]" x-effect="pop($el)">$49</span>
          </div>
          <p class="mt-1.5 text-xs text-[#6b7280]" x-text="quip"></p>
          <a href="#buy" class="mt-5 flex h-[46px] items-center justify-center rounded-md bg-white text-sm font-semibold text-[#111111] transition-colors hover:bg-[#e5e7eb]">{{ __('Lock in this incredible rate') }}</a>
          <button type="button" @click="reset()" class="mt-3 w-full text-center text-xs text-[#a1a1aa] transition-colors hover:text-white">{{ __('Reset the abacus') }}</button>
        </div>
      </div>
    </div>

    <p class="mx-auto mt-5 max-w-[640px] text-center text-xs text-muted-soft">
      {{ __('* The Suspiciously Accurate Pricing Calculator™ is fully functional and has never once returned a price other than $49. This is not a bug. It is our entire business model.') }}
    </p>
  </section>

  <section class="mx-auto max-w-[1200px] px-5 pt-16 sm:px-8 sm:pt-24">
    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
      @foreach ([
          [
              'radius' => 'rounded-[4px]',
              'title' => __('No monthly nibbling'),
              'desc' => __("Subscriptions nibble away a little every month until there's nothing left. We charge once and then get out of your way."),
          ],
          [
              'radius' => 'rounded-full',
              'title' => __('Portable either way'),
              'desc' => __('Self-host or cloud, your catalog exports to open JSON in one click. Leave whenever you like, the door is always open.'),
          ],
          [
              'radius' => 'rounded-[3px]',
              'title' => __('Try before you pay'),
              'desc' => __('Ten items free on the cloud, and self-hosting free forever. Make up your mind before any money changes hands, because the payment is final once it does.'),
          ],
      ] as $card)
        <div class="flex flex-col gap-y-3.5 rounded-lg bg-card p-8">
          <span class="flex h-10 w-10 items-center justify-center rounded-[10px] bg-primary">
            <span class="h-4 w-4 {{ $card['radius'] }} bg-on-primary"></span>
          </span>
          <p class="text-[20px] font-semibold tracking-[-0.3px] text-ink">{{ $card['title'] }}</p>
          <p class="text-[15px] leading-relaxed text-muted">{{ $card['desc'] }}</p>
        </div>
      @endforeach
    </div>
  </section>

  <section class="mx-auto max-w-[1200px] px-5 pt-16 sm:px-8 sm:pt-24">
    @php
        $comparisons = [
            ['emoji' => '☕', 'eq' => '≈ 12.25×', 'thing' => __('4 fancy oat-milk lattes'), 'note' => __('Gone in an afternoon. KolleK is gone... never. It just stays and helps.')],
            ['emoji' => '🥐', 'eq' => '≈ 3×', 'thing' => __('16 chocolatines'), 'note' => __('Roughly $3 each at the good bakery. Flakier than a subscription, at least.')],
            ['emoji' => '🏋️', 'eq' => '≈ 0.7×', 'thing' => __('One month of that gym'), 'note' => __("You know the one. You've been twice. KolleK is the app you'll actually use.")],
            ['emoji' => '🎮', 'eq' => '≈ 1×', 'thing' => __('A single video-game skin'), 'note' => __("For a character you'll retire next season. This organizes real treasure.")],
            ['emoji' => '🚕', 'eq' => '≈ 6.5×', 'thing' => __('7 rideshare surge fares'), 'note' => __('One rainy Friday night, basically. KolleK never surge-prices at 2am.')],
            ['emoji' => '🔌', 'eq' => '≈ 0.25×', 'thing' => __('That impulse cable purchase'), 'note' => __('The mystery HDMI drawer thanks you. So does a real catalog.')],
            ['emoji' => '🍿', 'eq' => '≈ 2×', 'thing' => __('2 movie tickets + popcorn'), 'note' => __('Two hours of entertainment vs. a lifetime of tidy shelves. Bold trade.')],
            ['emoji' => '♾️', 'eq' => '≈ ∞×', 'thing' => __('Zero monthly renewals'), 'note' => __('The one comparison that matters: you pay it once and never again.')],
        ];
    @endphp

    <div class="mb-12 text-center">
      <p class="mb-3.5 text-[13px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ __('$49 in perspective') }}</p>
      <h2 class="text-[28px] leading-[1.1] font-semibold tracking-[-1px] text-ink sm:text-4xl lg:text-5xl lg:tracking-[-1.5px]">{{ __('What else is forty-nine bucks?') }}</h2>
      <p class="mx-auto mt-4.5 max-w-[560px] text-[17px] text-muted">
        {{ __("To help you feel good about it, here's exactly what $49 buys elsewhere. KolleK is the only one that also organizes your entire life's collection. Just saying.") }}
      </p>
    </div>

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
      @foreach ($comparisons as $comparison)
        <div class="relative flex min-h-[170px] flex-col gap-2.5 rounded-[14px] bg-card p-6 transition-shadow hover:shadow-[0_8px_20px_rgba(17,17,17,0.07)]">
          <span class="absolute top-4 right-4 flex h-[38px] w-[38px] items-center justify-center rounded-[10px] border border-hairline bg-page text-[19px] leading-none" aria-hidden="true">{{ $comparison['emoji'] }}</span>
          <p class="font-mono text-[13px] font-medium text-muted-soft">{{ $comparison['eq'] }}</p>
          <p class="text-[19px] leading-[1.2] font-semibold tracking-[-0.4px] text-ink">{{ $comparison['thing'] }}</p>
          <div class="flex-1"></div>
          <p class="text-[13px] leading-[1.5] text-muted">{{ $comparison['note'] }}</p>
        </div>
      @endforeach
    </div>

    <p class="mt-6 text-center text-[13px] text-muted-soft">{{ __('Prices approximate, sourced from vibes and one very expensive coffee habit. KolleK, however, is exactly $49.') }}</p>
  </section>

  <section id="faq" class="mx-auto max-w-[760px] scroll-mt-24 px-5 pt-16 sm:px-8 sm:pt-24">
    <h2 class="text-center text-[28px] leading-[1.15] font-semibold tracking-[-1px] text-ink sm:text-4xl">{{ __('Frequently Asked Quest-ions.') }}</h2>
    <p class="mt-3 mb-10 text-center text-base text-muted">{{ __("Everything you're wondering about, answered.") }}</p>

    <div class="flex flex-col border-t border-hairline" x-data="{ open: null }">
      @foreach ([
          [
              'question' => __('Wait, is it really just $49, one time?'),
              'answer' => __("Yes, and the first ten items are free before that. Catalogue those, see whether we suit you, then pay forty-nine dollars once and the cloud-hosted KolleK is yours. No monthly fee, no annual renewal, no surprise 'loyalty' price hikes. We only charge you the one time, and then we leave your wallet a-loan."),
          ],
          [
              'question' => __('Should I self-host instead?'),
              'answer' => __("Honestly? If you're even a little bit technical, yes. Self-hosting is free forever and takes one Docker command. You keep full control of your data and pay us nothing. We put the free option first on this page for a reason: it's the best deal, period."),
          ],
          [
              'question' => __('How do I self-host it?'),
              'answer' => __("Run 'docker run -p 8080:8080 -v kollek:/data ghcr.io/kollek/app' and open localhost:8080. That's the whole thing. Your data lives on a volume you own, updates are a docker pull away, and there's no invoice at the end."),
          ],
          [
              'question' => __('What does the $49 cloud plan actually cover?'),
              'answer' => __("It lifts the ten item limit on your account, and covers managed hosting for life, automatic updates, encrypted daily backups, and zero maintenance on your end. You collect; we keep the servers running. It's the same software as self-host, you're just paying us to handle the infrastructure for you."),
          ],
          [
              'question' => __('Is there a subscription hiding somewhere?'),
              'answer' => __("Not a chance. We built a whole pricing page and couldn't find one either. There is no per-seat fee, no storage tier, no 'premium' upsell. One payment, everything included, forever."),
          ],
          [
              'question' => __('Why does the calculator always say $49?'),
              'answer' => __('Because the price is always $49. We could have hidden that behind fancy tiers and sliders, so we did build the sliders, purely for your entertainment. Drag them all you like; the total is engineered to remain gloriously, stubbornly $49.'),
          ],
          [
              'question' => __('Can I get a refund if I change my mind?'),
              'answer' => __('No, and we would rather say that here than in the small print. A reversal costs a project this size the payment fee, the chargeback penalty and an afternoon of paperwork. That is why the cloud gives you ten items free first, and why self-hosting is free forever: make up your mind before you pay, not after. If something breaks, email us and we will fix it.'),
          ],
          [
              'question' => __('Do you offer a discount for teams, students, or very good collectors?'),
              'answer' => __("The price is already $49 one time with unlimited members, so a team of one pays the same as a team of thirty. That's about as discounted as flat gets. Students and very good collectors: you were already getting the deal."),
          ],
      ] as $index => $faq)
        <div class="border-b border-hairline">
          <button
            type="button"
            @click="open = open === {{ $index }} ? null : {{ $index }}"
            x-bind:aria-expanded="open === {{ $index }} ? 'true' : 'false'"
            class="flex w-full items-center justify-between gap-4 py-5.5 text-left"
          >
            <span class="text-[17px] font-semibold text-ink">{{ $faq['question'] }}</span>
            <span
              class="flex h-6 w-6 shrink-0 items-center justify-center text-xl text-muted transition-transform duration-200"
              x-bind:class="open === {{ $index }} ? 'rotate-45' : ''"
              aria-hidden="true"
            >+</span>
          </button>

          <div x-show="open === {{ $index }}" x-cloak>
            <p class="pr-11 pb-6 pl-1 text-[15px] leading-relaxed text-muted">{{ $faq['answer'] }}</p>
          </div>
        </div>
      @endforeach
    </div>
  </section>

  <section class="mx-auto max-w-[1200px] px-5 pt-16 sm:px-8 sm:pt-24">
    <div class="rounded-xl bg-card px-6 py-14 text-center sm:px-12 sm:py-18">
      <h2 class="mx-auto max-w-[620px] text-[28px] leading-[1.15] font-semibold tracking-[-1px] text-balance text-ink sm:text-[40px]">
        {{ __('Stop putting it off. Start collecting.') }}
      </h2>
      <p class="mx-auto mt-4.5 max-w-[460px] text-[17px] text-muted">
        {{ __("Free forever if you self-host, ten items free then $49 once if you'd like us to handle the hosting. Either way, no subscription is ever going to eat into your bank account.") }}
      </p>

      <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
        <a href="{{ route('register') }}" class="flex h-12 items-center justify-center rounded-md bg-primary px-6 text-[15px] font-semibold text-on-primary transition-opacity hover:opacity-90">{{ __('Get started') }}</a>

        <a href="#calculator" class="flex h-12 items-center justify-center gap-x-2 rounded-md border border-hairline bg-canvas px-5.5 text-[15px] font-semibold text-ink transition-colors hover:bg-sidebar">{{ __('Re-run the calculator') }}</a>
      </div>
    </div>
  </section>
</x-marketing-layout>
