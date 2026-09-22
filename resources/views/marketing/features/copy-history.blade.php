{{-- The "Copy history" feature page of the marketing site. --}}

@php
    $hexToRgba = function (string $hex, float $alpha): string {
        $n = hexdec(ltrim($hex, '#'));
        return 'rgba('.(($n >> 16) & 255).','.(($n >> 8) & 255).','.($n & 255).",{$alpha})";
    };

    // The eight record types, each a fixed data-viz colour (does not invert) and a marker
    // shape (square for events, round for relationships).
    $recordTypes = [
        'transaction' => ['label' => __('Transaction'), 'color' => '#059669', 'dot' => '#34d399', 'square' => true],
        'valuation' => ['label' => __('Valuation'), 'color' => '#2563eb', 'dot' => '#3b82f6', 'square' => true],
        'provenance' => ['label' => __('Provenance'), 'color' => '#6366f1', 'dot' => '#6366f1', 'square' => false],
        'insurance' => ['label' => __('Insurance'), 'color' => '#7c3aed', 'dot' => '#8b5cf6', 'square' => false],
        'maintenance' => ['label' => __('Service'), 'color' => '#d97706', 'dot' => '#f59e0b', 'square' => true],
        'loan' => ['label' => __('Loan'), 'color' => '#db2777', 'dot' => '#ec4899', 'square' => false],
        'location' => ['label' => __('Move'), 'color' => '#0d9488', 'dot' => '#14b8a6', 'square' => true],
        'document' => ['label' => __('Document'), 'color' => '#475569', 'dot' => '#64748b', 'square' => true],
    ];
    foreach ($recordTypes as &$rt) {
        $rt['tint'] = $hexToRgba($rt['dot'], 0.14);
    }
    unset($rt);

    // Amazing Spider-Man #1, Copy 1 — chronological. `meaningful` marks the big moments.
    $heroEvents = [
        ['type' => 'transaction', 'top' => 'Jan 8', 'sub' => '2023', 'title' => __('Purchased at auction'), 'subtitle' => 'Heritage Auctions · Lot #4021', 'amount' => '$225.00', 'meaningful' => true, 'linked' => true, 'mono' => false],
        ['type' => 'document', 'top' => 'Jan 8', 'sub' => '2023', 'title' => __('Invoice attached'), 'subtitle' => 'heritage-invoice-4021.pdf', 'amount' => null, 'meaningful' => false, 'linked' => false, 'mono' => true],
        ['type' => 'provenance', 'top' => 'Jan 20', 'sub' => '2023', 'title' => __('Provenance recorded'), 'subtitle' => 'From the Kessler collection, 1998–2023', 'amount' => null, 'meaningful' => true, 'linked' => false, 'mono' => false],
        ['type' => 'location', 'top' => 'Feb 2', 'sub' => '2023', 'title' => __('Moved to Display Case'), 'subtitle' => 'Study · shelf A', 'amount' => null, 'meaningful' => false, 'linked' => false, 'mono' => false],
        ['type' => 'valuation', 'top' => 'Mar 15', 'sub' => '2023', 'title' => __('Appraised'), 'subtitle' => 'Overstreet guide + market comps', 'amount' => '$640.00', 'meaningful' => true, 'linked' => false, 'mono' => false],
        ['type' => 'insurance', 'top' => 'Jun 10', 'sub' => '2023', 'title' => __('Added to policy'), 'subtitle' => 'Collectibles rider · The Hartford', 'amount' => '$650.00', 'meaningful' => false, 'linked' => false, 'mono' => false],
        ['type' => 'maintenance', 'top' => 'Sep 4', 'sub' => '2023', 'title' => __('Pressed & re-graded'), 'subtitle' => 'CGC · grade held at 4.0', 'amount' => '$68.00', 'meaningful' => false, 'linked' => false, 'mono' => false],
        ['type' => 'loan', 'top' => 'Feb 18', 'sub' => '2024', 'title' => __('Loaned out'), 'subtitle' => 'Regional Comic Expo · 4 days', 'amount' => null, 'meaningful' => true, 'linked' => false, 'mono' => false],
        ['type' => 'valuation', 'top' => 'May 30', 'sub' => '2024', 'title' => __('Re-appraised'), 'subtitle' => 'Post-show market comps', 'amount' => '$720.00', 'meaningful' => true, 'linked' => false, 'mono' => false],
    ];

    $storyEvents = [
        ['type' => 'transaction', 'top' => '2019', 'title' => __('Bought from dealer'), 'subtitle' => 'Watches of Knightsbridge', 'amount' => '$4,200'],
        ['type' => 'document', 'top' => '2019', 'title' => __('Box & papers logged'), 'subtitle' => 'omega-warranty-card.pdf', 'amount' => null],
        ['type' => 'provenance', 'top' => '2020', 'title' => __('Prior ownership traced'), 'subtitle' => 'Single owner from new, 2014', 'amount' => null],
        ['type' => 'maintenance', 'top' => '2021', 'title' => __('Full service'), 'subtitle' => 'Omega service centre', 'amount' => '$520'],
        ['type' => 'valuation', 'top' => '2022', 'title' => __('Re-valued'), 'subtitle' => 'Chrono24 market comps', 'amount' => '$5,800'],
        ['type' => 'insurance', 'top' => '2023', 'title' => __('Insured'), 'subtitle' => 'Specified item · $6,000 cover', 'amount' => '$6,000'],
        ['type' => 'loan', 'top' => '2024', 'title' => __('Shown at fair'), 'subtitle' => 'City Watch Fair · 2 days', 'amount' => null],
    ];

    // Resolve events for Alpine (the interactive control section).
    $resolve = fn (array $e): array => array_merge($e, [
        'label' => $recordTypes[$e['type']]['label'],
        'color' => $recordTypes[$e['type']]['color'],
        'tint' => $recordTypes[$e['type']]['tint'],
        'square' => $recordTypes[$e['type']]['square'],
    ]);
    $controlEvents = array_map($resolve, $heroEvents);
    $chipTypes = collect($recordTypes)->map(fn ($rt, $key) => ['key' => $key, 'label' => $rt['label'], 'dot' => $rt['dot'], 'square' => $rt['square']])->values()->all();

    $receiptRows = [
        ['label' => __('Vendor'), 'value' => 'Heritage Auctions', 'mono' => false],
        ['label' => __('Date'), 'value' => 'Jan 8, 2023', 'mono' => false],
        ['label' => __('Lot'), 'value' => '#4021', 'mono' => true],
        ['label' => __('Total paid'), 'value' => '$225.00', 'mono' => true],
    ];

    $storyPoints = [
        __('The same reference, bought years apart, carries different service, value, and provenance.'),
        __('Each copy keeps its own receipts, appraisals, and certificates.'),
        __('Nothing gets averaged, merged, or quietly overwritten.'),
    ];

    // The candid two column footer. Caveats first so they stack above the pitch on mobile.
    $notFor = [
        ['head' => __('A current value and a single note are enough.'), 'body' => __('You do not need the old story.')],
        ['head' => __('You need legal chain-of-custody or specialist appraisal software.'), 'body' => null],
    ];
    $chooseWhen = [
        ['head' => __('You want purchases, valuations, care, loans, moves, and documents in one timeline.'), 'body' => null],
        ['head' => __('You would like the receipt, the record, and the object to agree with each other.'), 'body' => null],
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

    <section id="top" class="mx-auto max-w-[1000px] px-5 pt-16 text-center sm:px-8 sm:pt-24">
        <p class="mx-auto mb-5 text-[12px] leading-[1.5] font-semibold tracking-[1px] text-muted-soft uppercase">{{ __('Receipts have a habit of going walkabout') }}</p>
        <h1 class="mx-auto max-w-[840px] text-[32px] leading-[1.08] font-semibold tracking-[-1px] text-balance text-ink sm:text-5xl sm:tracking-[-1.5px] lg:text-[60px] lg:leading-[1.05] lg:tracking-[-2px]">
            {{ __('Keep the good story. Lose the paper chase.') }}
        </h1>
        <p class="mx-auto mt-6 max-w-[660px] text-[17px] leading-relaxed text-pretty text-muted sm:text-[19px]">
            {{ __('A physical object has a life beyond its title and cover photo. :name keeps the purchase, the latest valuation, the service, the loan, the move, and the paperwork together, where they belong: with the exact copy they describe.', ['name' => config('app.name')]) }}
        </p>
        <div class="mt-9 flex flex-col justify-center gap-3 sm:flex-row">
            <a href="{{ route('register') }}" class="flex h-12 items-center justify-center rounded-md bg-primary px-6 text-[15px] font-semibold text-on-primary transition-opacity hover:opacity-90">{{ __('Follow the plot') }}</a>
            <a href="#control" class="flex h-12 items-center justify-center gap-x-2 rounded-md border border-hairline bg-canvas px-5.5 text-[15px] font-semibold text-ink transition-colors hover:bg-sidebar">
                {{ __('See the whole saga') }}
                @svg('lucide-arrow-down', 'size-4')
            </a>
        </div>
    </section>

    <section id="hero-capture" class="mx-auto mt-14 max-w-[1060px] scroll-mt-24 px-5 sm:px-8">
        <div class="overflow-hidden rounded-xl border border-hairline bg-canvas shadow-[0_24px_60px_rgba(17,17,17,0.10),0_4px_12px_rgba(17,17,17,0.05)]">
            <div class="flex h-11 items-center gap-x-2 border-b border-hairline-soft bg-sidebar px-4">
                <span class="h-[11px] w-[11px] rounded-full bg-hairline"></span>
                <span class="h-[11px] w-[11px] rounded-full bg-hairline"></span>
                <span class="h-[11px] w-[11px] rounded-full bg-hairline"></span>
                <div class="ml-3 hidden h-[26px] max-w-[420px] flex-1 items-center rounded-sm border border-hairline bg-input px-2.5 text-xs text-muted-soft sm:flex">
                    {{ Str::lower(config('app.name')) }}.app/items/amazing-spider-man-1/history
                </div>
            </div>
            <div class="p-6 sm:p-7">
                <div class="mb-5 flex flex-wrap items-center gap-4">
                    <span class="h-[68px] w-[52px] shrink-0 rounded-lg" style="background:repeating-linear-gradient(135deg,#fb923c 0px,#fb923c 8px,#fdba74 8px,#fdba74 16px);"></span>
                    <div class="min-w-0 flex-1">
                        <div class="mb-1.5 flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-x-1.5 rounded-full px-2.5 py-[3px] text-[11px] font-semibold" style="color:#fb923c;background:{{ $hexToRgba('#fb923c', 0.12) }};"><span class="h-[7px] w-[7px] rounded-full" style="background:#fb923c;"></span>{{ __('Comics') }}</span>
                            <span class="inline-flex items-center gap-x-1.5 rounded-full bg-card px-2.5 py-1 text-[12px] font-semibold text-body"><span class="h-[7px] w-[7px] rounded-full bg-success"></span>{{ __('Copy :n · CGC 4.0', ['n' => 1]) }}</span>
                        </div>
                        <p class="text-[22px] font-semibold tracking-[-0.5px] text-ink">Amazing Spider-Man #1</p>
                    </div>
                    <div class="text-right">
                        <p class="text-[11px] tracking-[0.4px] text-muted-soft uppercase">{{ __('Latest valuation') }}</p>
                        <p class="text-[20px] font-semibold tracking-[-0.4px] text-ink">$720.00</p>
                    </div>
                </div>
                <div class="mb-5 flex flex-wrap items-center gap-2">
                    <span class="text-[12px] font-semibold tracking-[0.4px] text-muted-soft uppercase">{{ __('Timeline') }}</span>
                    <span class="rounded-full bg-card px-2.5 py-1 text-[12px] text-muted">{{ __(':count events · :range', ['count' => 9, 'range' => 'Jan 2023 → May 2024']) }}</span>
                </div>

                <div class="flex flex-col">
                    @foreach ($heroEvents as $event)
                        @php($rt = $recordTypes[$event['type']])
                        <div class="grid grid-cols-[52px_1fr] gap-x-4 sm:grid-cols-[88px_1fr] sm:gap-x-5">
                            <div class="pt-px text-right">
                                <p class="text-[13px] font-semibold tracking-[-0.2px] text-ink">{{ $event['top'] }}</p>
                                <p class="text-[11px] text-muted-soft">{{ $event['sub'] }}</p>
                            </div>
                            <div class="relative border-l-2 border-hairline pb-5.5 pl-6.5">
                                <span class="absolute top-0 -left-[9px] flex h-4 w-4 items-center justify-center border-2 bg-canvas {{ $rt['square'] ? 'rounded-[5px]' : 'rounded-full' }}" style="border-color:{{ $rt['color'] }};">
                                    <span class="h-1.5 w-1.5 {{ $rt['square'] ? 'rounded-[3px]' : 'rounded-full' }}" style="background:{{ $rt['color'] }};"></span>
                                </span>
                                <div class="rounded-xl border border-hairline bg-canvas px-4 py-3.5">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <div class="mb-1.5 flex flex-wrap items-center gap-2">
                                                <span class="rounded-full px-2.5 py-0.5 text-[10px] font-semibold tracking-[0.3px] uppercase" style="color:{{ $rt['color'] }};background:{{ $rt['tint'] }};">{{ $rt['label'] }}</span>
                                                @if ($event['linked'])
                                                    <span class="inline-flex items-center gap-x-1.5 text-[10.5px] font-semibold text-muted-soft"><span class="h-[5px] w-[5px] rounded-[2px] bg-muted-soft"></span>{{ __('Receipt attached') }}</span>
                                                @endif
                                            </div>
                                            <p class="text-[14.5px] font-semibold tracking-[-0.2px] text-ink">{{ $event['title'] }}</p>
                                            <p @class(['mt-0.5 text-[13px] text-muted', 'font-mono' => $event['mono']])>{{ $event['subtitle'] }}</p>
                                        </div>
                                        @if (! is_null($event['amount']))
                                            <span class="shrink-0 font-mono text-[14px] font-semibold text-ink">{{ $event['amount'] }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-[760px] px-5 pt-24 text-center sm:px-8 sm:pt-28">
        <p class="mb-3.5 text-[13px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ __('Every important thing, in order') }}</p>
        <h2 class="text-[28px] leading-[1.14] font-semibold tracking-[-1px] text-ink sm:text-4xl lg:text-[38px]">{{ __('Separate records, one readable story.') }}</h2>
        <p class="mx-auto mt-5 text-[17px] leading-relaxed text-pretty text-muted">
            {{ __('A copy’s timeline turns separate records into one readable story. See what happened, when it happened, and why it mattered, without opening eight tabs or excavating a folder named “misc final final”.') }}
        </p>
    </section>

    <section id="evidence" class="mx-auto max-w-[1200px] px-5 pt-14 sm:px-8">
        <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-2 lg:gap-14">
            <div class="overflow-hidden rounded-2xl border border-hairline bg-canvas shadow-[0_4px_14px_rgba(17,17,17,0.05)]">
                <div class="flex items-center justify-between border-b border-hairline-soft bg-sidebar px-4.5 py-3.5">
                    <div class="flex min-w-0 items-center gap-x-2.5">
                        @svg('lucide-file-text', 'size-4 shrink-0 text-body')
                        <span class="truncate font-mono text-[12px] text-body">heritage-invoice-4021.pdf</span>
                    </div>
                    <span class="shrink-0 rounded-full bg-card px-2 py-[3px] text-[10px] font-semibold tracking-[0.3px] text-muted">{{ __('PDF · :size', ['size' => '148 KB']) }}</span>
                </div>
                <div class="p-5">
                    <div class="flex h-[150px] items-center justify-center rounded-xl border border-hairline-soft" style="background:repeating-linear-gradient(135deg,var(--color-sidebar) 0px,var(--color-sidebar) 11px,var(--color-card) 11px,var(--color-card) 22px);">
                        <span class="rounded-md border border-hairline bg-canvas px-3 py-1.5 font-mono text-[11px] text-muted-soft">{{ __('scanned receipt') }}</span>
                    </div>
                    <div class="mt-4">
                        @foreach ($receiptRows as $row)
                            <div class="flex items-center justify-between gap-3.5 border-b border-hairline-soft py-2.5 last:border-b-0">
                                <span class="text-[13px] text-muted">{{ $row['label'] }}</span>
                                <span @class(['text-[13.5px] font-semibold text-ink', 'font-mono' => $row['mono']])>{{ $row['value'] }}</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4 flex items-center gap-x-2 text-[12px] font-semibold text-success">
                        <span class="h-[7px] w-[7px] rounded-[2px] bg-success"></span>{{ __('Attached to the purchase · :date', ['date' => 'Jan 8, 2023']) }}
                    </div>
                </div>
            </div>
            <div>
                <p class="mb-3.5 text-[13px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ __('Keep the proof close') }}</p>
                <h2 class="text-[28px] leading-[1.14] font-semibold tracking-[-1px] text-ink sm:text-4xl lg:text-[38px]">{{ __('The receipt lives with the record it proves.') }}</h2>
                <p class="mt-5 mb-6 text-[16px] leading-relaxed text-pretty text-muted">
                    {{ __('Attach a receipt to the purchase. Keep an appraisal beside the valuation. Store the certificate with the object it certifies. Future you will be quietly pleased.') }}
                </p>
                <div class="overflow-hidden rounded-xl border border-hairline">
                    <div class="flex items-center justify-between gap-3 border-b border-hairline-soft px-4 py-3.5">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full px-2.5 py-0.5 text-[10px] font-semibold tracking-[0.3px] uppercase" style="color:{{ $recordTypes['transaction']['color'] }};background:{{ $recordTypes['transaction']['tint'] }};">{{ __('Transaction') }}</span>
                            <span class="text-[14.5px] font-semibold text-ink">{{ __('Purchased at auction') }}</span>
                        </div>
                        <div class="shrink-0 text-right">
                            <p class="font-mono text-[15px] font-semibold text-ink">$225.00</p>
                            <p class="text-[12px] text-muted-soft">Jan 8, 2023</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-3">
                        <div class="border-r border-hairline-soft px-4 py-3">
                            <p class="mb-0.5 text-[11px] text-muted-soft">{{ __('Seller') }}</p>
                            <p class="text-[13px] font-semibold text-ink">Heritage Auctions</p>
                        </div>
                        <div class="border-r border-hairline-soft px-4 py-3">
                            <p class="mb-0.5 text-[11px] text-muted-soft">{{ __('Reference') }}</p>
                            <p class="font-mono text-[13px] font-semibold text-ink">Lot #4021</p>
                        </div>
                        <div class="px-4 py-3">
                            <p class="mb-0.5 text-[11px] text-muted-soft">{{ __('Documents') }}</p>
                            <p class="text-[13px] font-semibold text-ink">{{ __(':count attached', ['count' => 1]) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="control" class="mx-auto max-w-[1200px] scroll-mt-24 px-5 pt-24 sm:px-8 sm:pt-28">
        <div class="mx-auto mb-10 max-w-[680px] text-center">
            <p class="mb-3.5 text-[13px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ __('Headline version or the whole saga') }}</p>
            <h2 class="text-[28px] leading-[1.12] font-semibold tracking-[-1px] text-balance text-ink sm:text-4xl lg:text-[40px] lg:tracking-[-1.2px]">{{ __('Read the big moments, or every last correction.') }}</h2>
            <p class="mx-auto mt-5 text-[17px] leading-relaxed text-pretty text-muted">{{ __('Use the meaningful timeline when you want the big moments. Switch to the complete view when you need every move, service, and correction. Both are there; neither judges you for being thorough.') }}</p>
        </div>

        <div
            x-data="{
                view: 'meaningful',
                off: {},
                types: {{ Js::from($chipTypes) }},
                events: {{ Js::from($controlEvents) }},
                countTpl: @js(__('Showing :shown of :total records', ['shown' => '{s}', 'total' => '{t}'])),
                get shown() { return this.events.filter(e => (this.view === 'complete' || e.meaningful) && ! this.off[e.type]); },
            }"
            class="mx-auto max-w-[920px] overflow-hidden rounded-2xl border border-hairline bg-canvas shadow-[0_24px_60px_rgba(17,17,17,0.10),0_4px_12px_rgba(17,17,17,0.05)]"
        >
            <div class="flex h-11 items-center gap-x-2 border-b border-hairline-soft bg-sidebar px-4">
                <span class="h-[11px] w-[11px] rounded-full bg-hairline"></span>
                <span class="h-[11px] w-[11px] rounded-full bg-hairline"></span>
                <span class="h-[11px] w-[11px] rounded-full bg-hairline"></span>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-4 px-5 pt-4.5 sm:px-6">
                <div class="flex gap-1 rounded-[9px] bg-card p-[3px]">
                    <button type="button" @click="view = 'meaningful'" :class="view === 'meaningful' ? 'bg-canvas text-ink shadow-[0_1px_3px_rgba(17,17,17,0.12)]' : 'text-muted'" class="rounded-[7px] px-4 py-1.5 text-[13px] font-semibold transition-colors">{{ __('Meaningful') }}</button>
                    <button type="button" @click="view = 'complete'" :class="view === 'complete' ? 'bg-canvas text-ink shadow-[0_1px_3px_rgba(17,17,17,0.12)]' : 'text-muted'" class="rounded-[7px] px-4 py-1.5 text-[13px] font-semibold transition-colors">{{ __('Complete') }}</button>
                </div>
                <span class="text-[12px] font-medium text-muted" x-text="countTpl.replace('{s}', shown.length).replace('{t}', events.length)"></span>
            </div>

            <div class="flex flex-wrap items-center gap-2 border-b border-hairline-soft px-5 pt-4 pb-1 sm:px-6">
                <span class="mr-1 text-[11px] font-semibold tracking-[0.4px] text-muted-soft uppercase">{{ __('Records') }}</span>
                <template x-for="type in types" :key="type.key">
                    <button
                        type="button"
                        @click="off[type.key] = ! off[type.key]"
                        :class="off[type.key] ? 'border-hairline bg-sidebar text-muted-soft' : 'border-hairline text-ink'"
                        class="mb-3 inline-flex items-center gap-x-2 rounded-full border px-3 py-1.5 text-[12.5px] font-semibold transition-colors"
                    >
                        <span class="h-2 w-2" :class="type.square ? 'rounded-[2px]' : 'rounded-full'" :style="'background:' + (off[type.key] ? 'var(--color-hairline)' : type.dot)"></span>
                        <span x-text="type.label"></span>
                    </button>
                </template>
            </div>

            <div class="min-h-[180px] px-5 py-6 sm:px-6">
                <div class="flex flex-col" x-show="shown.length > 0">
                    <template x-for="(e, i) in shown" :key="e.type + i">
                        <div class="grid grid-cols-[52px_1fr] gap-x-4 sm:grid-cols-[88px_1fr] sm:gap-x-5">
                            <div class="pt-px text-right">
                                <p class="text-[13px] font-semibold tracking-[-0.2px] text-ink" x-text="e.top"></p>
                                <p class="text-[11px] text-muted-soft" x-text="e.sub"></p>
                            </div>
                            <div class="relative border-l-2 border-hairline pb-5 pl-6.5">
                                <span class="absolute top-0 -left-[9px] flex h-4 w-4 items-center justify-center border-2 bg-canvas" :class="e.square ? 'rounded-[5px]' : 'rounded-full'" :style="'border-color:' + e.color">
                                    <span class="h-1.5 w-1.5" :class="e.square ? 'rounded-[3px]' : 'rounded-full'" :style="'background:' + e.color"></span>
                                </span>
                                <div class="flex items-start justify-between gap-3 pt-px">
                                    <div class="min-w-0">
                                        <span class="mb-1 inline-block rounded-full px-2.5 py-0.5 text-[10px] font-semibold tracking-[0.3px] uppercase" :style="'color:' + e.color + ';background:' + e.tint" x-text="e.label"></span>
                                        <p class="text-[14px] font-semibold tracking-[-0.2px] text-ink" x-text="e.title"></p>
                                        <p class="mt-0.5 text-[12.5px] text-muted" x-text="e.subtitle"></p>
                                    </div>
                                    <span class="shrink-0 font-mono text-[13.5px] font-semibold text-ink" x-show="e.amount" x-text="e.amount"></span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
                <div class="flex flex-col items-center justify-center gap-2 py-10 text-center" x-show="shown.length === 0">
                    <p class="text-[14px] font-semibold text-ink">{{ __('No records match that filter.') }}</p>
                    <p class="text-[13px] text-muted">{{ __('Turn a record type back on to see its events.') }}</p>
                </div>
            </div>
        </div>
    </section>

    <section id="story" class="mx-auto max-w-[1200px] px-5 pt-24 sm:px-8 sm:pt-28">
        <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-2 lg:gap-14">
            <div>
                <p class="mb-3.5 text-[13px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ __('One object. One true history.') }}</p>
                <h2 class="text-[28px] leading-[1.14] font-semibold tracking-[-1px] text-ink sm:text-4xl lg:text-[38px]">{{ __('Two copies of the same watch. Two different stories.') }}</h2>
                <p class="mt-5 mb-6 text-[16px] leading-relaxed text-pretty text-muted">
                    {{ __('Two copies of the same watch can have completely different stories. :name keeps them separate, because blending them would make both records worse.', ['name' => config('app.name')]) }}
                </p>
                <div class="flex flex-col gap-3">
                    @foreach ($storyPoints as $point)
                        <div class="flex items-start gap-x-3">
                            <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-card">@svg('lucide-check', 'size-3 text-ink')</span>
                            <p class="text-[15px] leading-[1.5] text-body">{{ $point }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-hairline bg-canvas shadow-[0_4px_14px_rgba(17,17,17,0.05)]">
                <div class="flex items-center gap-3.5 border-b border-hairline-soft bg-sidebar px-5 py-4.5">
                    <span class="h-11 w-11 shrink-0 rounded-[10px]" style="background:repeating-linear-gradient(135deg,#64748b 0px,#64748b 7px,#cbd5e1 7px,#cbd5e1 14px);"></span>
                    <div class="min-w-0 flex-1">
                        <span class="mb-1 inline-flex items-center gap-x-1.5 rounded-full bg-card px-2 py-[3px] text-[10px] font-semibold tracking-[0.4px] text-muted uppercase"><span class="h-[5px] w-[5px] rounded-full" style="background:#64748b;"></span>{{ __('Watches · Copy :n', ['n' => 1]) }}</span>
                        <p class="text-[16px] font-semibold tracking-[-0.3px] text-ink">Omega Speedmaster Professional</p>
                    </div>
                    <div class="text-right">
                        <p class="text-[11px] text-muted-soft">{{ __('Now') }}</p>
                        <p class="text-[16px] font-semibold text-ink">$5,800</p>
                    </div>
                </div>
                <div class="px-5 pt-5">
                    @foreach ($storyEvents as $event)
                        @php($rt = $recordTypes[$event['type']])
                        <div class="grid grid-cols-[48px_1fr] gap-x-3.5 sm:grid-cols-[60px_1fr]">
                            <div class="pt-px text-right">
                                <p class="text-[13px] font-semibold text-ink">{{ $event['top'] }}</p>
                            </div>
                            <div class="relative border-l-2 border-hairline pb-4.5 pl-6">
                                <span class="absolute top-0 -left-2 flex h-3.5 w-3.5 items-center justify-center border-2 bg-canvas {{ $rt['square'] ? 'rounded-[4px]' : 'rounded-full' }}" style="border-color:{{ $rt['color'] }};">
                                    <span class="h-[5px] w-[5px] {{ $rt['square'] ? 'rounded-[2px]' : 'rounded-full' }}" style="background:{{ $rt['color'] }};"></span>
                                </span>
                                <div class="flex items-start justify-between gap-2.5">
                                    <div class="min-w-0">
                                        <p class="mb-0.5 text-[10px] font-semibold tracking-[0.3px] uppercase" style="color:{{ $rt['color'] }};">{{ $rt['label'] }}</p>
                                        <p class="text-[13.5px] font-semibold tracking-[-0.2px] text-ink">{{ $event['title'] }}</p>
                                        <p class="mt-0.5 text-[12px] text-muted">{{ $event['subtitle'] }}</p>
                                    </div>
                                    @if (! is_null($event['amount']))
                                        <span class="shrink-0 font-mono text-[12.5px] font-semibold text-ink">{{ $event['amount'] }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-[1200px] px-5 pt-24 sm:px-8 sm:pt-28">
        <div class="rounded-3xl bg-[#101010] px-6 py-16 text-center sm:px-12 sm:py-[72px]">
            <h2 class="mx-auto max-w-[640px] text-[26px] leading-[1.12] font-semibold tracking-[-1px] text-balance text-white sm:text-[34px] lg:text-[40px] lg:tracking-[-1.2px]">
                {{ __('See a complete copy history.') }}
            </h2>
            <p class="mx-auto mt-5 max-w-[480px] text-[16.5px] leading-relaxed text-pretty text-[#a1a1aa]">
                {{ __('Keep the purchase, the valuation, the service, the loan, the move, and the paperwork with the exact copy they describe.') }}
            </p>
            <div class="mt-8 flex justify-center">
                <a href="{{ route('register') }}" class="flex h-[50px] items-center justify-center rounded-md bg-white px-6.5 text-[15px] font-semibold text-[#111111] transition-colors hover:bg-[#e5e7eb]">{{ __('See a complete copy history') }}</a>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-[1080px] px-5 pt-24 pb-8 sm:px-8 sm:pt-28">
        <div class="grid grid-cols-1 gap-12 md:grid-cols-2 md:gap-16">
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
