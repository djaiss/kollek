{{-- The "Collection insights" feature page of the marketing site. --}}

@php
    // The 12-month value series drives both the hero sparkline (H=150) and the dashboard chart
    // (H=200). Points are computed here exactly as the product computes them, so the marketing
    // capture matches the real chart geometry.
    $valueSeries = [3200, 3550, 3900, 4100, 4600, 5200, 5500, 6100, 6800, 7300, 7900, 8420];

    $linePoints = function (int $height) use ($valueSeries): array {
        $width = 640;
        $padY = 10;
        $max = max($valueSeries) * 1.08;
        $last = count($valueSeries) - 1;

        $points = [];
        foreach ($valueSeries as $i => $value) {
            $x = ($i / $last) * $width;
            $y = $height - $padY - ($value / $max) * ($height - $padY * 2);
            $points[] = number_format($x, 1, '.', '').','.number_format($y, 1, '.', '');
        }

        $line = implode(' ', $points);

        return ['line' => $line, 'area' => "0,{$height} {$line} {$width},{$height}"];
    };

    $hero = $linePoints(150);
    $grow = $linePoints(200);

    $months = [__('Jul'), __('Aug'), __('Sep'), __('Oct'), __('Nov'), __('Dec'), __('Jan'), __('Feb'), __('Mar'), __('Apr'), __('May'), __('Jun')];

    // The four hero KPIs. Accent colours are fixed data-viz hues (they do not invert), so a
    // metric reads the same in light and dark.
    $heroKpis = [
        ['label' => __('Total estimated value'), 'value' => '$8,420', 'sub' => __('from latest valuations'), 'accent' => '#10b981'],
        ['label' => __('Items catalogued'), 'value' => '142', 'sub' => __('unique titles'), 'accent' => '#3b82f6'],
        ['label' => __('Copies tracked'), 'value' => '168', 'sub' => __('physical objects'), 'accent' => '#8b5cf6'],
        ['label' => __('Average copy value'), 'value' => '$50', 'sub' => __('value ÷ copies'), 'accent' => '#f59e0b'],
    ];

    // One object, two ledgers: money that moved vs what it may be worth. The latest valuation
    // is flagged as the current value; everything else is history.
    $transactions = [
        ['label' => __('Acquired at auction'), 'when' => __('Aug 2023'), 'amount' => '−$420'],
        ['label' => __('CGC grading fee'), 'when' => __('Sep 2023'), 'amount' => '−$28'],
    ];
    $valuations = [
        ['label' => __('Latest valuation'), 'when' => __('Feb 2026'), 'amount' => '$640', 'latest' => true],
        ['label' => __('Re-valued (market update)'), 'when' => __('Jul 2024'), 'amount' => '$520', 'latest' => false],
        ['label' => __('Initial valuation'), 'when' => __('Aug 2023'), 'amount' => '$430', 'latest' => false],
    ];

    // Items by category donut. Labels are proper nouns, so they stay untranslated; the conic
    // gradient is assembled from the same counts the legend shows.
    $categoryData = [
        ['label' => 'Spider-Man', 'count' => 58, 'color' => '#3b82f6'],
        ['label' => 'X-Men', 'count' => 42, 'color' => '#8b5cf6'],
        ['label' => 'Infinity Saga', 'count' => 24, 'color' => '#34d399'],
        ['label' => 'Wolverine', 'count' => 18, 'color' => '#fb923c'],
    ];
    $categoryTotal = array_sum(array_column($categoryData, 'count'));
    $donutStops = [];
    $acc = 0;
    foreach ($categoryData as $category) {
        $start = ($acc / $categoryTotal) * 360;
        $acc += $category['count'];
        $end = ($acc / $categoryTotal) * 360;
        $donutStops[] = "{$category['color']} ".number_format($start, 1, '.', '').'deg '.number_format($end, 1, '.', '').'deg';
    }
    $donutGradient = 'conic-gradient('.implode(', ', $donutStops).')';

    // Acquisitions per month bar chart.
    $acquisitionSeries = [6, 9, 5, 11, 8, 14, 7, 12, 10, 16, 9, 18];
    $acquisitionMax = max($acquisitionSeries);

    $conditionData = [
        ['label' => __('Mint'), 'count' => 12, 'color' => '#34d399'],
        ['label' => __('Near Mint'), 'count' => 45, 'color' => '#3b82f6'],
        ['label' => __('Very Fine'), 'count' => 38, 'color' => '#8b5cf6'],
        ['label' => __('Fine'), 'count' => 28, 'color' => '#fb923c'],
        ['label' => __('Good'), 'count' => 19, 'color' => '#f472b6'],
    ];
    $conditionMax = max(array_column($conditionData, 'count'));

    $locationData = [
        ['label' => __('Display case'), 'value' => 2680, 'color' => '#3b82f6'],
        ['label' => 'Box A1', 'value' => 2140, 'color' => '#8b5cf6'],
        ['label' => 'Box A2', 'value' => 1520, 'color' => '#34d399'],
        ['label' => 'Box B1', 'value' => 1180, 'color' => '#fb923c'],
        ['label' => 'Box B2', 'value' => 900, 'color' => '#f472b6'],
    ];
    $locationMax = max(array_column($locationData, 'value'));

    $topItems = [
        ['name' => 'New Mutants #98', 'condition' => __('Near Mint'), 'location' => 'Box A1', 'value' => '$850', 'a' => '#93c5fd', 'b' => '#bfdbfe'],
        ['name' => 'Spider-Man #1 (McFarlane)', 'condition' => __('Mint'), 'location' => __('Display case'), 'value' => '$640', 'a' => '#a5b4fc', 'b' => '#c7d2fe'],
        ['name' => 'Amazing Spider-Man #1', 'condition' => __('Near Mint'), 'location' => 'Box A1', 'value' => '$420', 'a' => '#fb923c', 'b' => '#fdba74'],
        ['name' => 'X-Men #1 (1991)', 'condition' => __('Near Mint'), 'location' => 'Box A2', 'value' => '$310', 'a' => '#fca5a5', 'b' => '#fecaca'],
        ['name' => 'Amazing Spider-Man #2', 'condition' => __('Very Fine'), 'location' => 'Box A1', 'value' => '$180', 'a' => '#fdba74', 'b' => '#fed7aa'],
    ];

    // Small callouts under the dashboard that name where each number comes from.
    $derivations = [
        ['dot' => '#3b82f6', 'text' => __('Value over time is built from the valuations you recorded, not a live market feed.')],
        ['dot' => '#8b5cf6', 'text' => __('Acquisition trends come from each copy’s earliest acquiring transaction date.')],
        ['dot' => '#34d399', 'text' => __('Every point traces back to a record, so you can check the maths when something looks off.')],
    ];

    // The gaps panel: a nudge, not a scolding. Each row uses a fixed semantic tone.
    $gaps = [
        ['count' => __(':count copies', ['count' => 12]), 'text' => __('still need a current valuation.'), 'cta' => __('Add value'), 'dot' => 'bg-warning'],
        ['count' => __(':count copies', ['count' => 4]), 'text' => __('are missing an acquisition date.'), 'cta' => __('Add date'), 'dot' => 'bg-brand'],
        ['count' => __(':count items', ['count' => 3]), 'text' => __('could use a better photo or note.'), 'cta' => __('Review'), 'dot' => 'bg-success'],
    ];

    // The candid two column footer. The left column (caveats) is authored first so it stacks
    // above the pitch on mobile: a visitor sees the honest bit before the sell.
    $notFor = [
        __('You need live market prices supplied automatically.'),
        __('You want speculative estimates without recording where they came from.'),
    ];
    $chooseWhen = [
        __('You want your own transactions and valuations to become useful collection insights.'),
        __('You want a clear distinction between what you paid and what a copy is worth now.'),
        __('You want value over time, acquisition trends, top items, location value, and completion progress.'),
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
        <p class="mx-auto mb-5 text-[12.5px] leading-[1.5] font-semibold tracking-[1px] text-muted-soft uppercase">
            {{ __('Spreadsheets have had a good run') }}
        </p>
        <h1 class="mx-auto max-w-[900px] text-[32px] leading-[1.08] font-semibold tracking-[-1px] text-balance text-ink sm:text-5xl sm:tracking-[-1.5px] lg:text-[60px] lg:leading-[1.06] lg:tracking-[-2px]">
            {{ __('Finally, numbers that know what they are talking about.') }}
        </h1>
        <p class="mx-auto mt-6 max-w-[700px] text-[17px] leading-relaxed text-pretty text-muted sm:text-[19px]">
            {{ __('Record what you paid and what each copy is worth today. :name turns that history into a collection view that answers useful questions without making up the missing bits.', ['name' => config('app.name')]) }}
        </p>
        <div class="mt-9 flex flex-col justify-center gap-3 sm:flex-row">
            <a href="{{ route('register') }}" class="flex h-12 items-center justify-center rounded-md bg-primary px-6 text-[15px] font-semibold text-on-primary transition-opacity hover:opacity-90">{{ __('Show me the numbers') }}</a>
            <a href="#worth" class="flex h-12 items-center justify-center gap-x-2 rounded-md border border-hairline bg-canvas px-5.5 text-[15px] font-semibold text-ink transition-colors hover:bg-sidebar">
                {{ __('See how value works') }}
                @svg('lucide-arrow-down', 'size-4')
            </a>
        </div>
    </section>

    <section id="overview" class="mx-auto mt-14 max-w-[1040px] scroll-mt-24 px-5 sm:px-8">
        <div class="overflow-hidden rounded-xl border border-hairline bg-canvas shadow-[0_24px_60px_rgba(17,17,17,0.10),0_4px_12px_rgba(17,17,17,0.05)]">
            <div class="flex h-11 items-center gap-x-2 border-b border-hairline-soft bg-sidebar px-4">
                <span class="h-[11px] w-[11px] rounded-full bg-hairline"></span>
                <span class="h-[11px] w-[11px] rounded-full bg-hairline"></span>
                <span class="h-[11px] w-[11px] rounded-full bg-hairline"></span>
                <div class="ml-3 hidden h-[26px] max-w-[380px] flex-1 items-center rounded-sm border border-hairline bg-input px-2.5 text-xs text-muted-soft sm:flex">
                    {{ Str::lower(config('app.name')) }}.app/collections/marvel-90s/statistics
                </div>
            </div>
            <div class="p-6 sm:p-7">
                <div class="mb-4.5 flex items-end justify-between gap-4">
                    <div>
                        <p class="text-[12px] font-semibold tracking-[0.5px] text-muted-soft uppercase">{{ __('Collection overview') }}</p>
                        <p class="mt-0.5 text-[22px] font-semibold tracking-[-0.5px] text-ink">Marvel Comics 1990s</p>
                    </div>
                    <span class="shrink-0 rounded-md bg-card px-3 py-1.5 text-[12px] font-semibold text-body">{{ __('Last 12 months') }}</span>
                </div>
                <div class="grid grid-cols-2 gap-3.5 lg:grid-cols-4">
                    @foreach ($heroKpis as $kpi)
                        <div class="rounded-xl border border-hairline p-4">
                            <div class="flex items-center gap-x-2">
                                <span class="h-[9px] w-[9px] rounded-[3px]" style="background:{{ $kpi['accent'] }};"></span>
                                <span class="text-[12px] font-medium text-muted">{{ $kpi['label'] }}</span>
                            </div>
                            <p class="mt-2 text-[28px] font-semibold tracking-[-0.6px] text-ink">{{ $kpi['value'] }}</p>
                            <p class="mt-0.5 text-[12px] text-muted-soft">{{ $kpi['sub'] }}</p>
                        </div>
                    @endforeach
                </div>
                <div class="mt-3.5 rounded-xl border border-hairline px-5 pt-4.5 pb-3">
                    <div class="mb-1.5 flex items-start justify-between">
                        <span class="text-[14px] font-semibold text-ink">{{ __('Estimated value over time') }}</span>
                        <span class="text-[12px] font-semibold text-success">{{ __('+:percent% this year', ['percent' => 52]) }}</span>
                    </div>
                    <svg viewBox="0 0 640 150" width="100%" height="120" preserveAspectRatio="none" class="block">
                        <defs>
                            <linearGradient id="heroFill" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#3b82f6" stop-opacity="0.20" />
                                <stop offset="100%" stop-color="#3b82f6" stop-opacity="0" />
                            </linearGradient>
                        </defs>
                        <polygon points="{{ $hero['area'] }}" fill="url(#heroFill)" />
                        <polyline points="{{ $hero['line'] }}" fill="none" stroke="#3b82f6" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round" />
                    </svg>
                </div>
            </div>
        </div>
    </section>

    <section id="worth" class="mx-auto max-w-[1200px] scroll-mt-24 px-5 pt-24 sm:px-8 sm:pt-28">
        <div class="mb-11 max-w-[680px]">
            <p class="mb-3.5 text-[13px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ __('What you paid, kept separate') }}</p>
            <h2 class="text-[28px] leading-[1.1] font-semibold tracking-[-1px] text-ink sm:text-4xl lg:text-[42px] lg:tracking-[-1.4px]">{{ __('Cost and value are two different numbers.') }}</h2>
            <p class="mt-5 text-[17px] leading-relaxed text-pretty text-muted">
                {{ __('Transactions record money that actually changed hands. Valuations record what an object may be worth. Keep both, and the collection stops confusing cost with value. A surprisingly popular mix-up.') }}
            </p>
        </div>

        <div class="overflow-hidden rounded-2xl border border-hairline bg-canvas shadow-[0_4px_16px_rgba(17,17,17,0.05)]">
            <div class="flex flex-col gap-4 border-b border-hairline-soft p-6 sm:flex-row sm:items-start sm:justify-between sm:px-7">
                <div class="flex items-center gap-4">
                    <span class="h-[68px] w-[52px] shrink-0 rounded-lg" style="background:repeating-linear-gradient(135deg,#fb923c 0px,#fb923c 8px,#fdba74 8px,#fdba74 16px);"></span>
                    <div>
                        <p class="text-[12px] font-semibold tracking-[0.5px] text-muted-soft uppercase">{{ __('Amazing Spider-Man #300 · Near Mint copy') }}</p>
                        <p class="mt-1 text-[20px] font-semibold tracking-[-0.4px] text-ink">{{ __('One object, two ledgers') }}</p>
                    </div>
                </div>
                <div class="flex gap-7 text-right">
                    <div>
                        <p class="text-[22px] font-semibold tracking-[-0.5px] text-ink">$420</p>
                        <p class="text-[12px] text-muted-soft">{{ __('you paid') }}</p>
                    </div>
                    <div class="w-px bg-hairline"></div>
                    <div>
                        <p class="text-[22px] font-semibold tracking-[-0.5px] text-success">$640</p>
                        <p class="text-[12px] text-muted-soft">{{ __('worth now') }}</p>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2">
                <div class="border-b border-hairline-soft p-6 sm:border-r sm:border-b-0 sm:px-7">
                    <div class="mb-4 flex items-center gap-x-2.5">
                        <span class="h-2 w-2 rounded-[2px] bg-brand"></span>
                        <span class="text-[12px] font-semibold tracking-[0.5px] text-muted-soft uppercase">{{ __('Transactions · money that moved') }}</span>
                    </div>
                    @foreach ($transactions as $tx)
                        <div @class(['flex items-center justify-between gap-3 py-3', 'border-b border-hairline-soft' => ! $loop->last])>
                            <div class="min-w-0">
                                <p class="text-[14px] font-semibold text-ink">{{ $tx['label'] }}</p>
                                <p class="mt-0.5 text-[12px] text-muted-soft">{{ $tx['when'] }}</p>
                            </div>
                            <span class="font-mono text-[15px] font-semibold text-ink">{{ $tx['amount'] }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="p-6 sm:px-7">
                    <div class="mb-4 flex items-center gap-x-2.5">
                        <span class="h-2 w-2 rounded-[2px] bg-success"></span>
                        <span class="text-[12px] font-semibold tracking-[0.5px] text-muted-soft uppercase">{{ __('Valuations · what it may be worth') }}</span>
                    </div>
                    @foreach ($valuations as $vl)
                        <div @class(['flex items-center justify-between gap-3 py-3', 'border-b border-hairline-soft' => ! $loop->last])>
                            <div class="min-w-0">
                                <div class="flex items-center gap-x-2">
                                    <span class="text-[14px] font-semibold text-ink">{{ $vl['label'] }}</span>
                                    @if ($vl['latest'])
                                        <span class="rounded-full bg-success px-2 py-0.5 text-[10px] font-bold tracking-[0.4px] text-white">{{ __('CURRENT VALUE') }}</span>
                                    @endif
                                </div>
                                <p class="mt-0.5 text-[12px] text-muted-soft">{{ $vl['when'] }}</p>
                            </div>
                            <span @class(['font-mono text-[15px] font-semibold', 'text-success' => $vl['latest'], 'text-ink' => ! $vl['latest']])>{{ $vl['amount'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="mt-5 flex items-start gap-4 rounded-2xl border border-hairline bg-sidebar px-6 py-5">
            <span class="flex h-[38px] w-[38px] shrink-0 items-center justify-center rounded-[10px] bg-card">
                <span class="h-3.5 w-3.5 rounded-[4px] bg-warning"></span>
            </span>
            <p class="max-w-[820px] text-[15.5px] leading-[1.55] text-pretty text-body">
                {!! __('A copy’s current estimated value comes from its latest valuation. No valuation? :name calls it <strong>unvalued</strong>, not worthless. Those are very different things.', ['name' => config('app.name')]) !!}
            </p>
        </div>
    </section>

    <section id="grow" class="mx-auto max-w-[1200px] scroll-mt-24 px-5 pt-24 sm:px-8 sm:pt-28">
        <div class="mb-11 max-w-[680px]">
            <p class="mb-3.5 text-[13px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ __('See the collection grow') }}</p>
            <h2 class="text-[28px] leading-[1.1] font-semibold tracking-[-1px] text-ink sm:text-4xl lg:text-[42px] lg:tracking-[-1.4px]">{{ __('Value over time, and everything that shapes it.') }}</h2>
            <p class="mt-5 text-[17px] leading-relaxed text-pretty text-muted">
                {{ __('Watch value over time, acquisitions by month, top items, condition distribution, and value by location. The numbers come from records you entered, so you can trace them back when something looks odd.') }}
            </p>
        </div>

        <div class="overflow-hidden rounded-2xl border border-hairline bg-sidebar shadow-[0_20px_50px_rgba(17,17,17,0.08)]">
            <div class="flex h-11 items-center gap-x-2 border-b border-hairline-soft bg-canvas px-4">
                <span class="h-[11px] w-[11px] rounded-full bg-hairline"></span>
                <span class="h-[11px] w-[11px] rounded-full bg-hairline"></span>
                <span class="h-[11px] w-[11px] rounded-full bg-hairline"></span>
                <span class="ml-3 text-[12px] font-semibold text-body">{{ __('Statistics') }}</span>
            </div>
            <div class="p-4 sm:p-5.5">
                <div class="mb-4 grid grid-cols-1 gap-4 lg:grid-cols-[1.7fr_1fr]">
                    <div class="rounded-xl border border-hairline bg-canvas p-5">
                        <div class="mb-1.5 flex items-start justify-between">
                            <div>
                                <p class="text-[15px] font-semibold text-ink">{{ __('Estimated value over time') }}</p>
                                <p class="mt-0.5 text-[12.5px] text-muted">{{ __('Built from your recorded valuations') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[20px] font-semibold tracking-[-0.5px] text-ink">$8,420</p>
                                <p class="text-[12px] font-semibold text-success">{{ __('+:percent% this year', ['percent' => 52]) }}</p>
                            </div>
                        </div>
                        <svg viewBox="0 0 640 200" width="100%" height="180" preserveAspectRatio="none" class="mt-3 block">
                            <defs>
                                <linearGradient id="growFill" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#3b82f6" stop-opacity="0.20" />
                                    <stop offset="100%" stop-color="#3b82f6" stop-opacity="0" />
                                </linearGradient>
                            </defs>
                            <line x1="0" y1="50" x2="640" y2="50" stroke="var(--color-hairline-soft)" stroke-width="1" />
                            <line x1="0" y1="100" x2="640" y2="100" stroke="var(--color-hairline-soft)" stroke-width="1" />
                            <line x1="0" y1="150" x2="640" y2="150" stroke="var(--color-hairline-soft)" stroke-width="1" />
                            <polygon points="{{ $grow['area'] }}" fill="url(#growFill)" />
                            <polyline points="{{ $grow['line'] }}" fill="none" stroke="#3b82f6" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round" />
                        </svg>
                        <div class="mt-2 flex justify-between">
                            @foreach ($months as $month)
                                <span class="font-mono text-[10px] text-muted-soft">{{ $month }}</span>
                            @endforeach
                        </div>
                    </div>
                    <div class="flex flex-col rounded-xl border border-hairline bg-canvas p-5">
                        <p class="text-[15px] font-semibold text-ink">{{ __('Items by category') }}</p>
                        <p class="mb-4 text-[12.5px] text-muted">{{ __(':count items across :groups groups', ['count' => 142, 'groups' => 4]) }}</p>
                        <div class="flex flex-1 items-center gap-5">
                            <div class="relative h-[118px] w-[118px] shrink-0">
                                <div class="h-[118px] w-[118px] rounded-full" style="background:{{ $donutGradient }};"></div>
                                <div class="absolute inset-[20px] flex flex-col items-center justify-center rounded-full bg-canvas">
                                    <span class="text-[20px] font-semibold tracking-[-0.5px] text-ink">142</span>
                                    <span class="text-[10px] text-muted-soft">{{ __('items') }}</span>
                                </div>
                            </div>
                            <div class="flex flex-1 flex-col gap-2.5">
                                @foreach ($categoryData as $category)
                                    <div class="flex items-center gap-x-2.5">
                                        <span class="h-[9px] w-[9px] shrink-0 rounded-[3px]" style="background:{{ $category['color'] }};"></span>
                                        <span class="min-w-0 flex-1 truncate text-[12.5px] font-medium text-ink">{{ $category['label'] }}</span>
                                        <span class="text-[12.5px] font-semibold text-ink">{{ $category['count'] }}</span>
                                        <span class="w-8 text-right text-[11px] text-muted-soft">{{ round($category['count'] / $categoryTotal * 100) }}%</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-4 grid grid-cols-1 gap-4 lg:grid-cols-[1.7fr_1fr]">
                    <div class="rounded-xl border border-hairline bg-canvas p-5">
                        <div class="mb-4.5 flex items-start justify-between">
                            <div>
                                <p class="text-[15px] font-semibold text-ink">{{ __('Acquisitions per month') }}</p>
                                <p class="mt-0.5 text-[12.5px] text-muted">{{ __('From each copy’s earliest transaction') }}</p>
                            </div>
                            <div class="flex items-center gap-x-1.5 text-[12px] text-muted">
                                <span class="h-[9px] w-[9px] rounded-[2px] bg-badge-violet"></span>
                                <span>{{ __('Added') }}</span>
                            </div>
                        </div>
                        <div class="flex h-40 items-end gap-x-2">
                            @foreach ($acquisitionSeries as $i => $count)
                                <div class="flex h-full flex-1 flex-col items-center justify-end gap-y-1.5">
                                    <span class="text-[10.5px] font-semibold text-muted">{{ $count }}</span>
                                    <span @class(['w-full rounded-t-[5px]', 'bg-badge-violet' => $loop->last, 'bg-badge-violet/30' => ! $loop->last]) style="height:{{ max(6, round($count / $acquisitionMax * 132)) }}px;"></span>
                                    <span class="font-mono text-[10px] text-muted-soft">{{ $months[$i] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="rounded-xl border border-hairline bg-canvas p-5">
                        <p class="text-[15px] font-semibold text-ink">{{ __('Condition grade') }}</p>
                        <p class="mb-4.5 text-[12.5px] text-muted">{{ __('Distribution across the collection') }}</p>
                        <div class="flex flex-col gap-3.5">
                            @foreach ($conditionData as $condition)
                                <div>
                                    <div class="mb-1.5 flex justify-between text-[12.5px]">
                                        <span class="font-medium text-ink">{{ $condition['label'] }}</span>
                                        <span class="text-muted">{{ $condition['count'] }}</span>
                                    </div>
                                    <div class="h-2 overflow-hidden rounded-full bg-card">
                                        <div class="h-full rounded-full" style="width:{{ round($condition['count'] / $conditionMax * 100) }}%; background:{{ $condition['color'] }};"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                    <div class="rounded-xl border border-hairline bg-canvas p-5">
                        <p class="text-[15px] font-semibold text-ink">{{ __('Value by location') }}</p>
                        <p class="mb-4.5 text-[12.5px] text-muted">{{ __('Where the value sits') }}</p>
                        <div class="flex flex-col gap-3.5">
                            @foreach ($locationData as $location)
                                <div class="flex items-center gap-x-3">
                                    <span class="w-[72px] shrink-0 truncate text-[12.5px] font-medium text-ink">{{ $location['label'] }}</span>
                                    <div class="h-[9px] min-w-[60px] flex-1 overflow-hidden rounded-full bg-card">
                                        <div class="h-full rounded-full" style="width:{{ round($location['value'] / $locationMax * 100) }}%; background:{{ $location['color'] }};"></div>
                                    </div>
                                    <span class="w-[58px] shrink-0 text-right text-[12.5px] font-semibold text-ink">${{ number_format($location['value']) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="rounded-xl border border-hairline bg-canvas p-5">
                        <div class="mb-3.5">
                            <p class="text-[15px] font-semibold text-ink">{{ __('Top items by value') }}</p>
                            <p class="mt-0.5 text-[12.5px] text-muted">{{ __('Traced to the latest valuation') }}</p>
                        </div>
                        <div class="flex flex-col">
                            @foreach ($topItems as $item)
                                <div @class(['flex items-center gap-x-3 py-2.5', 'border-b border-hairline-soft' => ! $loop->last])>
                                    <span class="w-5 shrink-0 text-center text-[12.5px] font-semibold text-muted-soft">{{ $loop->iteration }}</span>
                                    <span class="h-[34px] w-[34px] shrink-0 rounded-md" style="background:repeating-linear-gradient(135deg,{{ $item['a'] }} 0px,{{ $item['a'] }} 6px,{{ $item['b'] }} 6px,{{ $item['b'] }} 12px);"></span>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-[13px] font-semibold text-ink">{{ $item['name'] }}</p>
                                        <p class="text-[11.5px] text-muted-soft">{{ $item['condition'] }} · {{ $item['location'] }}</p>
                                    </div>
                                    <span class="shrink-0 text-[13px] font-semibold text-ink">{{ $item['value'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-5 grid grid-cols-1 gap-3.5 sm:grid-cols-3">
            @foreach ($derivations as $derivation)
                <div class="rounded-xl border border-hairline bg-canvas p-4.5">
                    <span class="mb-2.5 block h-2 w-2 rounded-[2px]" style="background:{{ $derivation['dot'] }};"></span>
                    <p class="text-[13.5px] leading-[1.5] text-pretty text-body">{{ $derivation['text'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <section class="mx-auto max-w-[1200px] px-5 pt-24 sm:px-8 sm:pt-28">
        <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-[0.95fr_1.05fr] lg:gap-14">
            <div>
                <p class="mb-3.5 text-[13px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ __('Find the gaps that matter') }}</p>
                <h2 class="text-[28px] leading-[1.1] font-semibold tracking-[-1px] text-ink sm:text-4xl lg:text-[42px] lg:tracking-[-1.4px]">{{ __('A sparse chart is a nudge, not a scolding.') }}</h2>
                <p class="mt-5 text-[17px] leading-relaxed text-pretty text-muted">
                    {{ __('Sparse charts are not a scolding. They are a useful nudge that a few older copies still need a date, a value, or a better story.') }}
                </p>
            </div>
            <div class="overflow-hidden rounded-2xl border border-hairline bg-canvas shadow-[0_4px_16px_rgba(17,17,17,0.05)]">
                <div class="flex items-center justify-between border-b border-hairline-soft px-6 py-5">
                    <div>
                        <p class="text-[14px] font-semibold text-ink">{{ __('Set completion') }}</p>
                        <p class="mt-0.5 text-[12.5px] text-muted">{{ __(':count items to go', ['count' => 25]) }}</p>
                    </div>
                    <span class="text-[24px] font-semibold tracking-[-0.5px] text-ink">71%</span>
                </div>
                <div class="px-6 pt-4.5 pb-2">
                    <div class="mb-5 h-2.5 overflow-hidden rounded-full bg-card">
                        <div class="h-full rounded-full bg-success" style="width:71%;"></div>
                    </div>
                    <p class="mb-1 text-[11px] font-semibold tracking-[0.5px] text-muted-soft uppercase">{{ __('Worth a look') }}</p>
                    @foreach ($gaps as $gap)
                        <div @class(['flex items-center gap-3.5 py-3.5', 'border-b border-hairline-soft' => ! $loop->last])>
                            <span class="flex h-[38px] w-[38px] shrink-0 items-center justify-center rounded-[10px] bg-card">
                                <span class="h-3.5 w-3.5 rounded-[4px] {{ $gap['dot'] }}"></span>
                            </span>
                            <p class="min-w-0 flex-1 text-[14px] leading-[1.45] text-ink">
                                <span class="font-semibold">{{ $gap['count'] }}</span> {{ $gap['text'] }}
                            </p>
                            <span class="shrink-0 text-[12.5px] font-semibold text-body">{{ $gap['cta'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-[1200px] px-5 pt-24 sm:px-8 sm:pt-28">
        <div class="rounded-3xl bg-card px-6 py-16 text-center sm:px-12 sm:py-[72px]">
            <h2 class="mx-auto max-w-[640px] text-[26px] leading-[1.14] font-semibold tracking-[-1px] text-balance text-ink sm:text-[34px] lg:text-[38px] lg:tracking-[-1.2px]">
                {{ __('Records in. Insights out. Nothing invented in between.') }}
            </h2>
            <p class="mx-auto mt-5 max-w-[560px] text-[16.5px] leading-relaxed text-pretty text-muted">
                {{ __('Every figure on the dashboard points back to a transaction or valuation you entered. That is the whole trick.') }}
            </p>
            <div class="mt-8 flex justify-center">
                <a href="{{ route('register') }}" class="flex h-[50px] items-center justify-center rounded-md bg-primary px-6.5 text-[15px] font-semibold text-on-primary transition-opacity hover:opacity-90">{{ __('Explore collection statistics') }}</a>
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
                            <p class="text-[16px] leading-[1.45] font-semibold text-pretty text-ink">{{ $row }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
            <div>
                <h3 class="mb-2 text-[22px] leading-[1.2] font-semibold tracking-[-0.5px] text-ink">{{ __('Choose :name when…', ['name' => config('app.name')]) }}</h3>
                <div class="border-t-2 border-ink">
                    @foreach ($chooseWhen as $row)
                        <div class="border-b border-hairline py-5.5">
                            <p class="text-[16px] leading-[1.45] font-semibold text-pretty text-ink">{{ $row }}</p>
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
