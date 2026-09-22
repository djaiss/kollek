{{-- The "Copy tracking" feature page of the marketing site. --}}

@php
    // The three copies of one title, reused across the hero capture, the comparison, the
    // fact cards and the interactive switcher. The status tone is a fixed semantic colour
    // (it does not invert in dark), so a status reads the same everywhere: on display green,
    // stored blue, reading amber.
    $statuses = [
        'display' => ['label' => __('On display'), 'text' => 'text-success', 'dot' => 'bg-success'],
        'stored' => ['label' => __('Stored'), 'text' => 'text-brand', 'dot' => 'bg-brand'],
        'reading' => ['label' => __('Reading copy'), 'text' => 'text-warning', 'dot' => 'bg-warning'],
    ];

    $copies = [
        [
            'condition' => __('Near Mint (CGC 9.8)'),
            'status' => 'display',
            'location' => __('Display case · Living room'),
            'short' => __('Display case'),
            'acquired' => __('Aug 2023'),
            'paid' => '$420',
            'value' => '$640',
            'a' => '#fb923c', 'b' => '#fdba74',
            'history' => [
                ['text' => __('Slabbed and graded CGC 9.8'), 'when' => __('Sep 2023'), 'accent' => true],
                ['text' => __('Moved to the living-room display case'), 'when' => __('Aug 2023'), 'accent' => false],
                ['text' => __('Acquired at auction for :amount', ['amount' => '$420']), 'when' => __('Aug 2023'), 'accent' => false],
            ],
        ],
        [
            'condition' => __('Very Fine (raw)'),
            'status' => 'stored',
            'location' => __('Long box A1 · Closet'),
            'short' => __('Long box A1'),
            'acquired' => __('Jan 2023'),
            'paid' => '$120',
            'value' => '$180',
            'a' => '#8b5cf6', 'b' => '#c4b5fd',
            'history' => [
                ['text' => __('Re-valued to :amount after a market update', ['amount' => '$180']), 'when' => __('Feb 2026'), 'accent' => true],
                ['text' => __('Bagged, boarded, and filed in long box A1'), 'when' => __('Jan 2023'), 'accent' => false],
                ['text' => __('Bought from a local shop for :amount', ['amount' => '$120']), 'when' => __('Jan 2023'), 'accent' => false],
            ],
        ],
        [
            'condition' => __('Good (reading copy)'),
            'status' => 'reading',
            'location' => __('Long box B3 · Garage'),
            'short' => __('Long box B3'),
            'acquired' => __('Jun 2019'),
            'paid' => '$45',
            'value' => '$95',
            'a' => '#34d399', 'b' => '#6ee7b7',
            'history' => [
                ['text' => __('Marked as the reading copy'), 'when' => __('Jul 2019'), 'accent' => true],
                ['text' => __('Filed in long box B3 in the garage'), 'when' => __('Jun 2019'), 'accent' => false],
                ['text' => __('Picked up at a convention for :amount', ['amount' => '$45']), 'when' => __('Jun 2019'), 'accent' => false],
            ],
        ],
    ];

    // The switcher is driven by Alpine, so its copies are resolved (status label and tone
    // classes flattened) and handed to the browser as JSON. The tone classes below appear
    // as literals in $statuses, so Tailwind keeps them even though Alpine binds them.
    $switcher = collect($copies)->map(fn (array $copy): array => [
        'condition' => $copy['condition'],
        'status' => $statuses[$copy['status']]['label'],
        'tone' => $statuses[$copy['status']]['text'],
        'toneDot' => $statuses[$copy['status']]['dot'],
        'location' => $copy['location'],
        'short' => $copy['short'],
        'value' => $copy['value'],
        'a' => $copy['a'],
        'b' => $copy['b'],
        'metrics' => [
            ['k' => __('Acquired'), 'v' => $copy['acquired']],
            ['k' => __('Price paid'), 'v' => $copy['paid']],
            ['k' => __('Status'), 'v' => $statuses[$copy['status']]['label']],
        ],
        'history' => $copy['history'],
    ])->all();

    $collectTags = [__('Variants'), __('Editions'), __('Duplicates'), __('Graded items'), __('Alternate covers'), __('First pressings')];

    // The candid two column footer. The left column (caveats) is authored first so it stacks
    // above the pitch on mobile: a visitor sees the honest bit before the sell.
    $notFor = [
        ['head' => __('You only need a list of titles.'), 'body' => __('One row per thing is plenty.')],
        ['head' => __('You do not care which exact copy you own.'), 'body' => __('The details can stay fuzzy.')],
    ];

    $chooseWhen = [
        ['head' => __('You own duplicates, variants, editions, or graded objects.'), 'body' => __('Each physical copy gets its own record.')],
        ['head' => __('Condition, location, value, and acquisition details matter per object.'), 'body' => __('Because two copies are rarely interchangeable.')],
    ];
@endphp

<x-marketing-layout :title="$feature['title']">
    <section class="hidden border-b border-hairline bg-sidebar md:block">
        <div class="mx-auto max-w-[1200px] px-5 py-5 sm:px-8">
            <div class="mb-4 flex items-center justify-between">
                <p class="text-[11px] font-semibold tracking-[0.7px] text-muted-soft uppercase">{{ __('Browse features') }}</p>
                <a href="{{ route('marketing.features.index') }}" data-turbo="true" class="inline-flex items-center gap-1.5 text-[13px] font-semibold text-body transition-colors hover:text-ink">
                    {{ __('All features') }}
                    @svg('lucide-arrow-right', 'size-3.5')
                </a>
            </div>
            <div class="grid grid-cols-3 gap-x-8 gap-y-1.5">
                @foreach ($columns as $column)
                    <div class="flex flex-col">
                        <div class="mb-1 flex items-center gap-2 border-b border-hairline-soft pb-2">
                            <span class="h-1.5 w-1.5 rounded-full" style="background:{{ $column['dot'] }};"></span>
                            <span class="text-[11px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ $column['label'] }}</span>
                        </div>
                        @foreach ($column['items'] as $item)
                            @php($isCurrent = $item['slug'] === $feature['slug'])
                            <a href="{{ route('marketing.features.show', $item['slug']) }}" data-turbo="true" @class([
                                'group -mx-2.5 flex items-center gap-2.5 rounded-lg px-2.5 py-2 transition-colors',
                                'bg-card' => $isCurrent,
                                'hover:bg-card' => ! $isCurrent,
                            ])>
                                <span class="h-2.5 w-2.5 shrink-0" style="border-radius:{{ $item['iconRadius'] }}; background:{{ $item['dot'] }};"></span>
                                <span @class([
                                    'text-[13.5px] tracking-[-0.1px]',
                                    'font-semibold text-ink' => $isCurrent,
                                    'font-medium text-body' => ! $isCurrent,
                                ])>{{ $item['title'] }}</span>
                                @if ($isCurrent)
                                    <span class="ml-auto rounded-full px-2 py-0.5 text-[9px] font-bold tracking-[0.5px] text-on-primary" style="background:{{ $item['dot'] }};">{{ __('CURRENT') }}</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section id="top" class="mx-auto max-w-[1080px] px-5 pt-16 text-center sm:px-8 sm:pt-24">
        <p class="mx-auto mb-5 max-w-[640px] text-[12.5px] leading-[1.5] font-semibold tracking-[0.8px] text-muted-soft uppercase">
            {{ __('For when “I think I have two” is not a system') }}
        </p>
        <h1 class="mx-auto max-w-[840px] text-[32px] leading-[1.08] font-semibold tracking-[-1px] text-balance text-ink sm:text-5xl sm:tracking-[-1.5px] lg:text-[60px] lg:leading-[1.06] lg:tracking-[-2px]">
            {{ __('Yes, the duplicate is a different creature.') }}
        </h1>
        <p class="mx-auto mt-6 max-w-[680px] text-[17px] leading-relaxed text-pretty text-muted sm:text-[19px]">
            {{ __('One catalogue entry can describe the title. The copies beneath it describe the real objects you own: the pristine one, the battered one, the graded one, and the one currently hiding somewhere extremely safe.') }}
        </p>
        <div class="mt-9 flex flex-col justify-center gap-3 sm:flex-row">
            <a href="{{ route('register') }}" class="flex h-12 items-center justify-center rounded-md bg-primary px-6 text-[15px] font-semibold text-on-primary transition-opacity hover:opacity-90">{{ __('Meet your duplicates') }}</a>
            <a href="#switch" class="flex h-12 items-center justify-center gap-x-2 rounded-md border border-hairline bg-canvas px-5.5 text-[15px] font-semibold text-ink transition-colors hover:bg-sidebar">
                {{ __('See a copy switch') }}
                @svg('lucide-arrow-down', 'size-4')
            </a>
        </div>
    </section>

    <section id="copies" class="mx-auto mt-14 max-w-[1040px] scroll-mt-24 px-5 sm:px-8">
        <div class="overflow-hidden rounded-xl border border-hairline bg-canvas shadow-[0_24px_60px_rgba(17,17,17,0.10),0_4px_12px_rgba(17,17,17,0.05)]">
            <div class="flex h-11 items-center gap-x-2 border-b border-hairline-soft bg-sidebar px-4">
                <span class="h-[11px] w-[11px] rounded-full bg-hairline"></span>
                <span class="h-[11px] w-[11px] rounded-full bg-hairline"></span>
                <span class="h-[11px] w-[11px] rounded-full bg-hairline"></span>
                <div class="ml-3 hidden h-[26px] max-w-[360px] flex-1 items-center rounded-sm border border-hairline bg-input px-2.5 text-xs text-muted-soft sm:flex">
                    {{ Str::lower(config('app.name')) }}.app/item/amazing-spider-man-300
                </div>
            </div>
            <div class="p-6 sm:p-7">
                <div class="flex flex-col gap-4 border-b border-hairline-soft pb-5 sm:flex-row sm:items-start sm:gap-5">
                    <span class="h-[84px] w-16 shrink-0 rounded-lg" style="background:repeating-linear-gradient(135deg,#fb923c 0px,#fb923c 9px,#fdba74 9px,#fdba74 18px);"></span>
                    <div class="min-w-0 flex-1">
                        <p class="text-[12px] font-semibold tracking-[0.5px] text-muted-soft uppercase">{{ __('Marvel Comics 1990s · Title record') }}</p>
                        <p class="mt-1 text-[24px] font-semibold tracking-[-0.6px] text-ink">Amazing Spider-Man #300</p>
                        <p class="mt-1 text-[13.5px] text-muted">{{ __('Marvel · 1988 · First appearance of Venom') }}</p>
                    </div>
                    <div class="flex shrink-0 flex-col items-start gap-1.5 sm:items-end">
                        <span class="rounded-full bg-card px-3 py-1.5 text-[12px] font-semibold text-ink">{{ __(':count copies owned', ['count' => 3]) }}</span>
                        <span class="text-[12px] text-muted-soft">{{ __('Combined value :amount', ['amount' => '$915']) }}</span>
                    </div>
                </div>
                <div class="mt-5 mb-3 flex items-center justify-between">
                    <p class="text-[13px] font-semibold tracking-[0.5px] text-muted-soft uppercase">{{ __('Your copies') }}</p>
                    <span class="rounded-md bg-primary px-3 py-1.5 text-[13px] font-semibold text-on-primary">{{ __('+ Add copy') }}</span>
                </div>
                <div class="overflow-hidden rounded-xl border border-hairline">
                    @foreach ($copies as $copy)
                        @php($status = $statuses[$copy['status']])
                        <div @class(['flex items-center gap-4 px-4 py-3.5 sm:px-[18px]', 'border-t border-hairline-soft' => ! $loop->first])>
                            <span class="h-[58px] w-11 shrink-0 rounded-md" style="background:repeating-linear-gradient(135deg,{{ $copy['a'] }} 0px,{{ $copy['a'] }} 7px,{{ $copy['b'] }} 7px,{{ $copy['b'] }} 14px);"></span>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2.5">
                                    <span class="text-[15px] font-semibold tracking-[-0.2px] text-ink">{{ $copy['condition'] }}</span>
                                    <span class="rounded-full bg-card px-2.5 py-[3px] text-[11px] font-semibold {{ $status['text'] }}">{{ $status['label'] }}</span>
                                </div>
                                <p class="mt-1 truncate text-[13px] text-muted">{{ __(':location · acquired :date', ['location' => $copy['location'], 'date' => $copy['acquired']]) }}</p>
                            </div>
                            <div class="shrink-0 text-right">
                                <p class="text-[15px] font-semibold text-ink">{{ $copy['value'] }}</p>
                                <p class="text-[12px] text-muted-soft">{{ __('paid :amount', ['amount' => $copy['paid']]) }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-[1200px] px-5 pt-24 sm:px-8 sm:pt-28">
        <div class="mb-11 max-w-[660px]">
            <p class="mb-3.5 text-[13px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ __('The title is not the whole story') }}</p>
            <h2 class="text-[28px] leading-[1.1] font-semibold tracking-[-1px] text-ink sm:text-4xl lg:text-[42px] lg:tracking-[-1.4px]">{{ __('One clean record. Every real object underneath.') }}</h2>
            <p class="mt-5 text-[17px] leading-relaxed text-pretty text-muted">
                {{ __('Keep one clean item record for the thing itself, then track every physical copy underneath it. No duplicate item names. No mystery suffixes. No “final-final-actually” spreadsheets.') }}
            </p>
        </div>

        <div class="grid grid-cols-1 items-stretch gap-5 md:grid-cols-[0.85fr_1.15fr]">
            <div class="flex flex-col rounded-2xl border border-dashed border-hairline bg-sidebar p-6">
                <p class="mb-4 text-[12px] font-semibold tracking-[0.5px] text-muted-soft uppercase">{{ __('A title-only list') }}</p>
                <div class="flex items-center gap-3 rounded-xl border border-hairline bg-canvas px-4 py-3.5">
                    <span class="h-[42px] w-8 shrink-0 rounded-md" style="background:repeating-linear-gradient(135deg,var(--color-hairline) 0px,var(--color-hairline) 6px,var(--color-hairline-soft) 6px,var(--color-hairline-soft) 12px);"></span>
                    <div class="min-w-0 flex-1">
                        <p class="text-[14px] font-semibold text-ink">Amazing Spider-Man #300</p>
                        <p class="mt-0.5 text-[12.5px] text-muted-soft">{{ __('Qty: :count', ['count' => 3]) }}</p>
                    </div>
                </div>
                <div class="flex-1"></div>
                <p class="mt-5 text-[13.5px] leading-[1.55] text-pretty text-muted">
                    {!! __('One row says you own three. It cannot tell you <strong>which</strong> three, where they are, or what any single one is worth.') !!}
                </p>
            </div>

            <div class="rounded-2xl border border-hairline bg-canvas p-6 shadow-[0_4px_16px_rgba(17,17,17,0.05)]">
                <div class="mb-4 flex items-center justify-between">
                    <p class="text-[12px] font-semibold tracking-[0.5px] text-ink uppercase">{{ __(':name · the copies beneath it', ['name' => config('app.name')]) }}</p>
                    <span class="rounded-full bg-card px-2.5 py-[3px] text-[11px] font-semibold text-success">{{ __(':count distinct objects', ['count' => 3]) }}</span>
                </div>
                <div class="flex flex-col gap-2.5">
                    @foreach ($copies as $copy)
                        @php($status = $statuses[$copy['status']])
                        <div class="flex items-center gap-3.5 rounded-xl border border-hairline px-4 py-3">
                            <span class="h-10 w-[30px] shrink-0 rounded-md" style="background:repeating-linear-gradient(135deg,{{ $copy['a'] }} 0px,{{ $copy['a'] }} 6px,{{ $copy['b'] }} 6px,{{ $copy['b'] }} 12px);"></span>
                            <div class="min-w-0 flex-1">
                                <p class="text-[13.5px] font-semibold text-ink">{{ $copy['condition'] }}</p>
                                <p class="mt-0.5 truncate text-[12px] text-muted-soft">{{ $copy['location'] }}</p>
                            </div>
                            <div class="shrink-0 text-right">
                                <p class="text-[13.5px] font-semibold text-ink">{{ $copy['value'] }}</p>
                                <p class="text-[11px] font-semibold {{ $status['text'] }}">{{ $status['label'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-[1200px] px-5 pt-24 sm:px-8 sm:pt-28">
        <div class="mb-11 max-w-[660px]">
            <p class="mb-3.5 text-[13px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ __('Give every copy its own facts') }}</p>
            <h2 class="text-[28px] leading-[1.1] font-semibold tracking-[-1px] text-ink sm:text-4xl lg:text-[42px] lg:tracking-[-1.4px]">{{ __('The first pressing and the scuffed spare are not the same job.') }}</h2>
            <p class="mt-5 text-[17px] leading-relaxed text-pretty text-muted">
                {{ __('Condition, location, acquisition date, price paid, estimated value, and status all belong to the individual copy. Because a first pressing on display and a scuffed spare in a box are not doing the same job.') }}
            </p>
        </div>
        <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
            @foreach ($copies as $copy)
                @php($status = $statuses[$copy['status']])
                @php($facts = [
                    ['k' => __('Condition'), 'v' => $copy['condition']],
                    ['k' => __('Location'), 'v' => $copy['location']],
                    ['k' => __('Acquired'), 'v' => $copy['acquired']],
                    ['k' => __('Price paid'), 'v' => $copy['paid']],
                    ['k' => __('Estimated value'), 'v' => $copy['value']],
                ])
                <div class="flex flex-col overflow-hidden rounded-2xl border border-hairline bg-canvas">
                    <div class="flex items-center gap-3.5 border-b border-hairline-soft px-5 py-4.5">
                        <span class="h-13 w-10 shrink-0 rounded-md" style="background:repeating-linear-gradient(135deg,{{ $copy['a'] }} 0px,{{ $copy['a'] }} 7px,{{ $copy['b'] }} 7px,{{ $copy['b'] }} 14px);"></span>
                        <div class="min-w-0 flex-1">
                            <p class="text-[15px] font-semibold tracking-[-0.2px] text-ink">{{ $copy['condition'] }}</p>
                            <span class="mt-1.5 inline-block rounded-full bg-card px-2.5 py-[3px] text-[11px] font-semibold {{ $status['text'] }}">{{ $status['label'] }}</span>
                        </div>
                    </div>
                    <div class="px-5 pt-1 pb-4.5">
                        @foreach ($facts as $fact)
                            <div @class(['flex items-center justify-between gap-3 py-2.5', 'border-b border-hairline-soft' => ! $loop->last])>
                                <span class="text-[12.5px] text-muted-soft">{{ $fact['k'] }}</span>
                                <span class="text-right text-[13.5px] font-semibold text-ink">{{ $fact['v'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <section id="switch" class="mx-auto max-w-[1200px] scroll-mt-24 px-5 pt-24 sm:px-8 sm:pt-28">
        <div class="mb-11 max-w-[660px]">
            <p class="mb-3.5 text-[13px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ __('Switch copies, keep context') }}</p>
            <h2 class="text-[28px] leading-[1.1] font-semibold tracking-[-1px] text-ink sm:text-4xl lg:text-[42px] lg:tracking-[-1.4px]">{{ __('Choose a copy. The details follow the object.') }}</h2>
            <p class="mt-5 text-[17px] leading-relaxed text-pretty text-muted">
                {{ __('Choose a copy and :name changes the details around it: where it lives, what it is worth, and what happened to it. The history follows the object, not the title.', ['name' => config('app.name')]) }}
            </p>
        </div>

        <div
            x-data="{ selected: 0, copies: {{ Js::from($switcher) }} }"
            class="grid grid-cols-1 overflow-hidden rounded-2xl border border-hairline bg-canvas shadow-[0_4px_16px_rgba(17,17,17,0.05)] md:grid-cols-[280px_1fr]"
        >
            <div class="border-b border-hairline-soft bg-sidebar p-4.5 md:border-r md:border-b-0">
                <p class="px-1 pb-3 text-[11px] font-semibold tracking-[0.5px] text-muted-soft uppercase">Amazing Spider-Man #300</p>
                <div class="flex flex-col gap-2">
                    <template x-for="(copy, index) in copies" :key="index">
                        <button
                            type="button"
                            @click="selected = index"
                            :class="selected === index ? 'border-ink bg-canvas' : 'border-hairline bg-transparent'"
                            class="flex items-center gap-3 rounded-xl border px-3 py-3 text-left transition-colors"
                        >
                            <span class="h-10 w-[30px] shrink-0 rounded-md" :style="'background:repeating-linear-gradient(135deg,' + copy.a + ' 0px,' + copy.a + ' 6px,' + copy.b + ' 6px,' + copy.b + ' 12px)'"></span>
                            <div class="min-w-0 flex-1">
                                <p class="text-[13.5px] font-semibold tracking-[-0.1px] text-ink" x-text="copy.condition"></p>
                                <p class="mt-0.5 truncate text-[11.5px] text-muted-soft" x-text="copy.short"></p>
                            </div>
                            <span :class="selected === index ? 'border-ink' : 'border-hairline'" class="flex h-4 w-4 shrink-0 items-center justify-center rounded-full border-2">
                                <span x-show="selected === index" class="h-1.5 w-1.5 rounded-full bg-ink"></span>
                            </span>
                        </button>
                    </template>
                </div>
            </div>

            <div class="p-6 sm:p-7">
                <div class="flex flex-col gap-4 border-b border-hairline-soft pb-5 sm:flex-row sm:items-start sm:justify-between">
                    <div class="flex items-center gap-4">
                        <span class="h-[68px] w-[52px] shrink-0 rounded-lg" :style="'background:repeating-linear-gradient(135deg,' + copies[selected].a + ' 0px,' + copies[selected].a + ' 8px,' + copies[selected].b + ' 8px,' + copies[selected].b + ' 16px)'"></span>
                        <div>
                            <div class="flex items-center gap-2.5">
                                <span class="text-[20px] font-semibold tracking-[-0.4px] text-ink" x-text="copies[selected].condition"></span>
                                <span :class="copies[selected].tone" class="rounded-full bg-card px-2.5 py-1 text-[11px] font-semibold" x-text="copies[selected].status"></span>
                            </div>
                            <p class="mt-1.5 text-[13px] text-muted" x-text="copies[selected].location"></p>
                        </div>
                    </div>
                    <div class="shrink-0 text-left sm:text-right">
                        <p class="text-[22px] font-semibold tracking-[-0.5px] text-ink" x-text="copies[selected].value"></p>
                        <p class="text-[12px] text-muted-soft">{{ __('estimated value') }}</p>
                    </div>
                </div>

                <div class="my-5 grid grid-cols-3 gap-3">
                    <template x-for="(metric, mi) in copies[selected].metrics" :key="mi">
                        <div class="rounded-xl bg-card px-3.5 py-3">
                            <p class="text-[11.5px] font-medium text-muted" x-text="metric.k"></p>
                            <p class="mt-1 text-[16px] font-semibold tracking-[-0.3px] text-ink" x-text="metric.v"></p>
                        </div>
                    </template>
                </div>

                <p class="mb-1 text-[12px] font-semibold tracking-[0.5px] text-muted-soft uppercase">{{ __('History for this copy') }}</p>
                <template x-for="(entry, ei) in copies[selected].history" :key="ei">
                    <div :class="ei < copies[selected].history.length - 1 ? 'border-b border-hairline-soft' : ''" class="flex gap-3.5 py-3">
                        <div class="flex shrink-0 flex-col items-center">
                            <span :class="entry.accent ? copies[selected].toneDot : 'bg-body'" class="mt-[5px] h-[9px] w-[9px] rounded-full"></span>
                            <span x-show="ei < copies[selected].history.length - 1" class="mt-1 w-px flex-1 bg-hairline"></span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-[14px] leading-[1.5] text-pretty text-ink" x-text="entry.text"></p>
                            <p class="mt-0.5 text-[12px] text-muted-soft" x-text="entry.when"></p>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-[1200px] px-5 pt-24 sm:px-8 sm:pt-28">
        <div class="rounded-3xl bg-card px-6 py-14 sm:px-12 sm:py-14">
            <div class="max-w-[640px]">
                <p class="mb-3.5 text-[13px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ __('Built for the things collectors actually collect') }}</p>
                <h2 class="text-[26px] leading-[1.14] font-semibold tracking-[-1px] text-ink sm:text-[34px] lg:text-[36px] lg:tracking-[-1.1px]">{{ __('Better than a comma-separated note.') }}</h2>
                <p class="mt-4.5 text-[16.5px] leading-relaxed text-pretty text-muted">
                    {{ __('Variants. Editions. Duplicates. Graded items. The version you bought because the cover was different. They all deserve a record of their own.') }}
                </p>
            </div>
            <div class="mt-7 flex flex-wrap gap-2.5">
                @foreach ($collectTags as $tag)
                    <span class="rounded-full border border-hairline bg-canvas px-4 py-2 text-[14px] font-medium text-body">{{ $tag }}</span>
                @endforeach
            </div>
            <div class="mt-8 flex">
                <a href="{{ route('register') }}" class="flex h-[50px] items-center justify-center rounded-md bg-primary px-6.5 text-[15px] font-semibold text-on-primary transition-opacity hover:opacity-90">{{ __('Track your first item') }}</a>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-[1080px] px-5 pt-24 pb-8 sm:px-8 sm:pt-28">
        <div class="grid grid-cols-1 gap-12 md:grid-cols-2 md:gap-14">
            <div>
                <h3 class="mb-2 text-[22px] leading-[1.2] font-semibold tracking-[-0.5px] text-ink">{{ __(':name might not be for you (yet) if…', ['name' => config('app.name')]) }}</h3>
                <div class="border-t-2 border-ink">
                    @foreach ($notFor as $row)
                        <div class="border-b border-hairline py-5.5">
                            <p class="text-[16px] leading-[1.45] font-semibold text-pretty text-ink">{{ $row['head'] }}</p>
                            @if (! is_null($row['body']))
                                <p class="mt-1.5 text-[15px] leading-[1.55] text-pretty text-muted">{{ $row['body'] }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
            <div>
                <h3 class="mb-2 text-[22px] leading-[1.2] font-semibold tracking-[-0.5px] text-ink">{{ __('Choose :name when…', ['name' => config('app.name')]) }}</h3>
                <div class="border-t-2 border-ink">
                    @foreach ($chooseWhen as $row)
                        <div class="border-b border-hairline py-5.5">
                            <p class="text-[16px] leading-[1.45] font-semibold text-pretty text-ink">{{ $row['head'] }}</p>
                            @if (! is_null($row['body']))
                                <p class="mt-1.5 text-[15px] leading-[1.55] text-pretty text-muted">{{ $row['body'] }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <p class="mt-6 max-w-[640px] text-[13.5px] leading-relaxed text-muted-soft">
            {{ __('We will update this page when the product changes.') }}
            <a href="{{ route('marketing.docs.portal.home.show') }}" data-turbo="true" class="border-b border-hairline text-body transition-colors hover:text-ink">{{ __('The feature status page has the boring-but-important details.') }}</a>
        </p>
    </section>
</x-marketing-layout>
