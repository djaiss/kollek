{{-- The "Protection & care" feature page of the marketing site. --}}

@php
    // Record-type tabs across the top of the item record. Insurance is the open tab.
    $recordTabs = [
        ['label' => __('Timeline'), 'dot' => '#111111', 'count' => null, 'on' => false],
        ['label' => __('Insurance'), 'dot' => '#8b5cf6', 'count' => '1', 'on' => true],
        ['label' => __('Maintenance'), 'dot' => '#f59e0b', 'count' => '2', 'on' => false],
        ['label' => __('Loans'), 'dot' => '#6366f1', 'count' => '2', 'on' => false],
        ['label' => __('Locations'), 'dot' => '#fb923c', 'count' => '3', 'on' => false],
        ['label' => __('Valuations'), 'dot' => '#3b82f6', 'count' => '3', 'on' => false],
        ['label' => __('Documents'), 'dot' => '#0ea5e9', 'count' => '5', 'on' => false],
    ];

    $insRows = [
        ['k' => __('Coverage'), 'v' => __('Scheduled item')],
        ['k' => __('Sum insured'), 'v' => '$450'],
        ['k' => __('Renews'), 'v' => __('Jan 2026')],
    ];
    $insChips = [__('Insurer & policy #'), __('Coverage type'), __('Sum insured'), __('Deductible'), __('Renewal dates')];

    $valuations = [
        ['type' => __('User estimate'), 'meta' => 'Jamie Diaz · '.__('purchase-based'), 'amount' => '$250', 'date' => __('Jan 2023'), 'dot' => '#94a3b8'],
        ['type' => __('Market estimate'), 'meta' => 'GoCollect · '.__('marketplace average'), 'amount' => '$340', 'date' => __('Jun 2024'), 'dot' => '#3b82f6'],
        ['type' => __('Professional appraisal'), 'meta' => 'Metropolis · '.__('comparable sales'), 'amount' => '$420', 'date' => __('May 2025'), 'dot' => '#0f7a4d'],
    ];

    $loans = [
        [
            'dir' => __('Outgoing'), 'counterparty' => 'Midtown Museum of Fine Art',
            'status' => __('Active'), 'statusTone' => 'text-warning',
            'fields' => [
                ['k' => __('Loaned'), 'v' => __('Apr 2025')],
                ['k' => __('Due'), 'v' => __('Oct 2025')],
                ['k' => __('Returned'), 'v' => '—'],
                ['k' => __('Condition out'), 'v' => 'CGC 4.5'],
            ],
        ],
        [
            'dir' => __('Outgoing'), 'counterparty' => __('Rae Kim (household)'),
            'status' => __('Returned'), 'statusTone' => 'text-success',
            'fields' => [
                ['k' => __('Loaned'), 'v' => __('Aug 2024')],
                ['k' => __('Due'), 'v' => __('Sep 2024')],
                ['k' => __('Returned'), 'v' => __('Sep 2024')],
                ['k' => __('Condition in'), 'v' => __('Unchanged')],
            ],
        ],
    ];

    $timeline = [
        ['title' => __('Acquisition'), 'desc' => __('Purchased from Heritage Auctions, Lot #4021.'), 'date' => __('Jan 2023'), 'dot' => '#6366f1', 'square' => false],
        ['title' => __('Authentication'), 'desc' => __('CGC slab, Stan Lee signature verified.'), 'date' => __('Feb 2023'), 'dot' => '#0f7a4d', 'square' => false],
        ['title' => __('Location move'), 'desc' => __('Box A1 → Display Case.'), 'date' => __('Jul 2025'), 'dot' => '#fb923c', 'square' => true],
        ['title' => __('Loan'), 'desc' => __('Out to Midtown Museum of Fine Art.'), 'date' => __('Apr 2025'), 'dot' => '#4338ca', 'square' => false],
        ['title' => __('Valuation'), 'desc' => __(':from → :to professional appraisal.', ['from' => '$340', 'to' => '$420']), 'date' => __('May 2025'), 'dot' => '#3b82f6', 'square' => true],
    ];

    $docs = [
        ['name' => 'invoice-4021.pdf', 'attached' => __('Transaction · Purchase'), 'size' => '214 KB', 'ext' => 'PDF', 'pdf' => true],
        ['name' => 'cgc-cert-0801234.pdf', 'attached' => __('Provenance · Authentication'), 'size' => '96 KB', 'ext' => 'PDF', 'pdf' => true],
        ['name' => 'appraisal-metropolis-2025.pdf', 'attached' => __('Valuation · Appraisal'), 'size' => '340 KB', 'ext' => 'PDF', 'pdf' => true],
        ['name' => 'policy-CIS-88231.pdf', 'attached' => __('Insurance · :policy', ['policy' => 'CIS-88231']), 'size' => '1.2 MB', 'ext' => 'PDF', 'pdf' => true],
        ['name' => 'cgc-label.jpg', 'attached' => __('Copy · Photograph'), 'size' => '2.1 MB', 'ext' => 'JPG', 'pdf' => false],
    ];

    // The candid two column footer. Caveats first so they stack above the pitch on mobile.
    $notFor = [
        __('You need insurance claims handling or policy administration software.'),
        __('You need regulated conservation or museum collections software.'),
    ];
    $chooseWhen = [
        __('You want insurance details, valuations, documents, and the object itself in one place.'),
        __('You want a practical log of care, condition, custody, storage, and provenance.'),
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

    <section id="top" class="mx-auto max-w-[1200px] px-5 pt-16 sm:px-8 sm:pt-24">
        <div class="max-w-[820px]">
            <p class="text-[12px] leading-[1.5] font-semibold tracking-[1px] text-muted-soft uppercase">{{ __('For the objects that would make a bad day worse') }}</p>
            <h1 class="mt-5 text-[32px] leading-[1.08] font-semibold tracking-[-1px] text-balance text-ink sm:text-5xl sm:tracking-[-1.5px] lg:text-[56px] lg:leading-[1.04] lg:tracking-[-2px]">
                {{ __('Keep the important details close. Very close.') }}
            </h1>
            <p class="mt-5.5 max-w-[680px] text-[17px] leading-relaxed text-pretty text-muted sm:text-[18px]">
                {{ __('Some objects need more than a title and a photo. :name keeps insurance, care, custody, condition, storage, and proof of ownership beside the physical copy they describe.', ['name' => config('app.name')]) }}
            </p>
            <div class="mt-8 flex">
                <a href="{{ route('register') }}" class="inline-flex h-12 items-center justify-center gap-x-2.5 rounded-md bg-primary px-6 text-[15px] font-semibold text-on-primary transition-opacity hover:opacity-90">{{ __('Give valuables a paper trail') }} @svg('lucide-arrow-right', 'size-4')</a>
            </div>
        </div>

        <div class="mt-13 overflow-hidden rounded-2xl border border-hairline bg-canvas shadow-[0_24px_60px_rgba(17,17,17,0.08),0_4px_12px_rgba(17,17,17,0.04)]">
            <div class="flex h-11 items-center gap-x-2 border-b border-hairline-soft px-4.5">
                <span class="h-[11px] w-[11px] rounded-full bg-hairline"></span>
                <span class="h-[11px] w-[11px] rounded-full bg-hairline"></span>
                <span class="h-[11px] w-[11px] rounded-full bg-hairline"></span>
                <div class="hidden flex-1 justify-center sm:flex">
                    <span class="rounded-full border border-hairline bg-sidebar px-3.5 py-1 font-mono text-[11px] text-muted-soft">{{ Str::lower(config('app.name')) }}.app / items / amazing-fantasy-15 / copy-1</span>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-4 border-b border-hairline-soft px-5 py-4 sm:px-6">
                <span class="h-[60px] w-11 shrink-0 rounded-md border border-hairline" style="background:repeating-linear-gradient(135deg,#eceef1,#eceef1 6px,#f4f6f8 6px,#f4f6f8 12px);"></span>
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-x-2.5 gap-y-1">
                        <span class="text-[17px] font-semibold tracking-[-0.3px] text-ink">Amazing Fantasy #15 · {{ __('Copy :n', ['n' => 1]) }}</span>
                        <span class="rounded-full bg-card px-2 py-[3px] text-[10px] font-bold tracking-[0.4px] text-success">{{ __('OWNED') }}</span>
                    </div>
                    <p class="mt-1 text-[13px] text-muted">{{ __('CGC 4.5 · Display Case · insured & appraised') }}</p>
                </div>
                <div class="ml-auto text-right">
                    <p class="text-[20px] font-semibold tracking-[-0.4px] text-ink">$420</p>
                    <p class="text-[11px] text-muted-soft">{{ __('latest valuation') }}</p>
                </div>
            </div>

            <div class="flex items-center gap-0.5 overflow-x-auto border-b border-hairline-soft px-5 sm:px-6" style="scrollbar-width:none;">
                @foreach ($recordTabs as $tab)
                    <div @class([
                        'flex shrink-0 items-center gap-x-1.5 border-b-2 px-3 py-3.5 text-[13px]',
                        'border-ink font-semibold text-ink' => $tab['on'],
                        'border-transparent font-medium text-body' => ! $tab['on'],
                    ])>
                        <span class="h-[7px] w-[7px] rounded-full" style="background:{{ $tab['dot'] }};"></span>
                        {{ $tab['label'] }}
                        @if ($tab['count'])
                            <span class="rounded-full bg-card px-1.5 py-px text-[11px] font-semibold text-muted-soft">{{ $tab['count'] }}</span>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="grid grid-cols-1 items-stretch gap-5 p-5 sm:p-6 lg:grid-cols-[1.1fr_1fr]">
                <div class="overflow-hidden rounded-xl border border-hairline">
                    <div class="flex items-center gap-x-2.5 border-b border-hairline-soft bg-sidebar px-4 py-3">
                        <span class="h-2 w-2 rounded-full bg-badge-violet"></span>
                        <span class="text-[13px] font-semibold text-ink">{{ __('Insurance') }}</span>
                        <span class="ml-auto rounded-full bg-card px-2 py-[3px] text-[10px] font-bold tracking-[0.4px] text-success">{{ __('ACTIVE') }}</span>
                    </div>
                    <div class="p-4">
                        <p class="text-[15px] font-semibold text-ink">Collectibles Insurance Services</p>
                        <p class="mt-0.5 text-[12.5px] text-muted">{{ __('Scheduled item · Policy :policy', ['policy' => 'CIS-88231']) }}</p>
                        <div class="mt-3.5 flex flex-col">
                            @foreach ($insRows as $row)
                                <div class="flex items-center justify-between border-t border-hairline-soft py-2.5">
                                    <span class="text-[12.5px] text-muted">{{ $row['k'] }}</span>
                                    <span class="text-[12.5px] font-semibold text-ink">{{ $row['v'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-3.5">
                    <div class="flex items-center gap-x-2.5 rounded-xl border border-dashed border-hairline bg-sidebar px-3.5 py-3">
                        @svg('lucide-link-2', 'size-4 shrink-0 text-badge-violet')
                        <span class="text-[12.5px] leading-[1.5] text-body">{!! __('Sum insured tracks the <strong>latest appraisal</strong> below.') !!}</span>
                    </div>
                    <div class="flex-1 overflow-hidden rounded-xl border border-hairline">
                        <div class="flex items-center gap-x-2.5 border-b border-hairline-soft bg-sidebar px-4 py-3">
                            <span class="h-2 w-2 rounded-full bg-brand"></span>
                            <span class="text-[13px] font-semibold text-ink">{{ __('Latest valuation') }}</span>
                        </div>
                        <div class="p-4">
                            <p class="text-[26px] font-semibold tracking-[-0.6px] text-ink">$420</p>
                            <p class="mt-1 text-[13px] font-semibold text-ink">{{ __('Professional appraisal') }}</p>
                            <p class="mt-0.5 text-[12.5px] text-muted">Metropolis Collectibles · {{ __('May 2025') }} · {{ __('comparable sales') }}</p>
                            <span class="mt-3 inline-flex items-center gap-x-1.5 rounded-full bg-card px-2.5 py-1 text-[11px] font-semibold text-success">
                                <span class="h-1.5 w-1.5 rounded-full bg-success"></span>{{ __('High confidence') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-[1200px] px-5 pt-24 sm:px-8 sm:pt-28">
        <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-[1fr_1.05fr] lg:gap-14">
            <div>
                <p class="mb-3.5 text-[13px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ __('Insurance & value') }}</p>
                <h2 class="text-[28px] leading-[1.12] font-semibold tracking-[-1px] text-ink sm:text-4xl lg:text-[38px] lg:tracking-[-1.1px]">{{ __('Keep cover and value together.') }}</h2>
                <p class="mt-5 text-[16px] leading-relaxed text-pretty text-muted">
                    {{ __('Record insurance details alongside the latest valuation and the documents that support it. When someone asks what it is worth, you will have more than a hopeful answer.') }}
                </p>
                <div class="mt-6 flex flex-wrap gap-2">
                    @foreach ($insChips as $chip)
                        <span class="rounded-full border border-hairline bg-canvas px-3 py-1.5 text-[13px] font-medium text-body">{{ $chip }}</span>
                    @endforeach
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-hairline bg-canvas shadow-[0_24px_60px_rgba(17,17,17,0.08),0_4px_12px_rgba(17,17,17,0.04)]">
                <div class="flex items-center gap-x-2.5 border-b border-hairline-soft px-5 py-4">
                    <span class="h-2 w-2 rounded-full bg-brand"></span>
                    <span class="text-[15px] font-semibold text-ink">{{ __('Valuations') }}</span>
                    <span class="ml-auto text-[11px] text-muted">{{ __(':count recorded', ['count' => 3]) }}</span>
                </div>
                <div class="px-5 pt-1">
                    @foreach ($valuations as $valuation)
                        <div class="flex items-center gap-x-3.5 border-b border-hairline-soft py-3.5">
                            <span class="flex h-[34px] w-[34px] shrink-0 items-center justify-center rounded-[9px] bg-card">
                                <span class="h-[11px] w-[11px] rounded-[3px]" style="background:{{ $valuation['dot'] }};"></span>
                            </span>
                            <div class="min-w-0">
                                <p class="text-[13.5px] font-semibold text-ink">{{ $valuation['type'] }}</p>
                                <p class="mt-0.5 text-[12px] text-muted">{{ $valuation['meta'] }}</p>
                            </div>
                            <div class="ml-auto text-right">
                                <p class="text-[16px] font-semibold text-ink">{{ $valuation['amount'] }}</p>
                                <p class="text-[11px] text-muted-soft">{{ $valuation['date'] }}</p>
                            </div>
                        </div>
                    @endforeach
                    <div class="flex items-center justify-between py-3.5">
                        <span class="text-[12.5px] text-muted">{{ __('Insured value follows latest') }}</span>
                        <span class="inline-flex items-center gap-x-1.5 text-[12px] font-semibold text-badge-violet"><span class="h-1.5 w-1.5 rounded-full bg-badge-violet"></span>CIS-88231 · $450</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-[1200px] px-5 pt-24 sm:px-8 sm:pt-28">
        <div class="mb-10 max-w-[660px]">
            <p class="mb-3.5 text-[13px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ __('Care log') }}</p>
            <h2 class="text-[28px] leading-[1.12] font-semibold tracking-[-1px] text-ink sm:text-4xl lg:text-[38px] lg:tracking-[-1.1px]">{{ __('Remember the care it received.') }}</h2>
            <p class="mt-5 text-[16px] leading-relaxed text-pretty text-muted">
                {{ __('Log cleaning, servicing, restoration, and repairs. Record the condition before and after. The object keeps its history; you do not have to keep it in your head.') }}
            </p>
        </div>
        <div class="overflow-hidden rounded-2xl border border-hairline bg-canvas shadow-[0_4px_12px_rgba(17,17,17,0.04)]">
            <div class="flex items-center gap-x-2.5 border-b border-hairline-soft px-5 py-4 sm:px-6">
                <span class="h-2 w-2 rounded-full bg-warning"></span>
                <span class="text-[15px] font-semibold text-ink">{{ __('Maintenance') }}</span>
                <span class="ml-auto text-[11px] text-muted">{{ __('Servicing · :date', ['date' => __('Feb 2023')]) }}</span>
            </div>
            <div class="grid grid-cols-1 items-center gap-5 p-5 sm:p-6 md:grid-cols-[1fr_auto_1fr]">
                <div class="rounded-xl border border-hairline p-4.5">
                    <p class="text-[11px] font-bold tracking-[0.5px] text-muted-soft uppercase">{{ __('Condition before') }}</p>
                    <p class="mt-2 text-[19px] font-semibold text-ink">{{ __('Raw · Very Fine') }}</p>
                    <p class="mt-2 text-[12.5px] leading-[1.55] text-pretty text-muted">{{ __('Light spine stress and one corner tick. Un-slabbed and unverified.') }}</p>
                </div>
                <div class="flex flex-col items-center gap-2">
                    <span class="flex h-[38px] w-[38px] items-center justify-center rounded-full bg-card">@svg('lucide-arrow-right', 'size-4 text-warning rotate-90 md:rotate-0')</span>
                    <span class="text-center text-[11px] font-semibold text-muted-soft">{{ __('Press & grade · Metropolis') }}</span>
                </div>
                <div class="rounded-xl border border-hairline bg-sidebar p-4.5">
                    <p class="text-[11px] font-bold tracking-[0.5px] text-warning uppercase">{{ __('Condition after') }}</p>
                    <p class="mt-2 text-[19px] font-semibold text-ink">CGC 4.5</p>
                    <p class="mt-2 text-[12.5px] leading-[1.55] text-pretty text-muted">{{ __('Pressed and encapsulated. Stan Lee signature verified on the label.') }}</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-x-6 gap-y-2 px-5 pb-5 text-[12.5px] text-muted sm:px-6">
                <span>{!! __('Performed by <strong>:by</strong>', ['by' => 'Metropolis Collectibles']) !!}</span>
                <span>{!! __('Cost <strong>:cost</strong>', ['cost' => '$68.00']) !!}</span>
                <span>{!! __('Attached <strong>:file</strong>', ['file' => 'condition-report.pdf']) !!}</span>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-[1200px] px-5 pt-24 sm:px-8 sm:pt-28">
        <div class="mb-10 max-w-[660px]">
            <p class="mb-3.5 text-[13px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ __('Custody') }}</p>
            <h2 class="text-[28px] leading-[1.12] font-semibold tracking-[-1px] text-ink sm:text-4xl lg:text-[38px] lg:tracking-[-1.1px]">{{ __('Know where it went, and whether it came back.') }}</h2>
            <p class="mt-5 text-[16px] leading-relaxed text-pretty text-muted">
                {{ __('Track loans and returns, including who has the object, when it left, and what condition it was in when it came home. Awkward conversations are not eliminated, but they are better documented.') }}
            </p>
        </div>
        <div class="overflow-hidden rounded-2xl border border-hairline bg-canvas shadow-[0_4px_12px_rgba(17,17,17,0.04)]">
            <div class="flex items-center gap-x-2.5 border-b border-hairline-soft px-5 py-4 sm:px-6">
                <span class="h-2 w-2 rounded-full" style="background:#6366f1;"></span>
                <span class="text-[15px] font-semibold text-ink">{{ __('Loans') }}</span>
                <span class="ml-auto text-[11px] text-muted">{{ __(':closed closed · :active active', ['closed' => 1, 'active' => 1]) }}</span>
            </div>
            <div class="px-5 sm:px-6">
                @foreach ($loans as $loan)
                    <div class="border-b border-hairline-soft py-4.5">
                        <div class="flex flex-wrap items-center gap-x-3 gap-y-2">
                            <span class="rounded-full bg-card px-2.5 py-[3px] text-[11px] font-semibold tracking-[0.3px] text-brand uppercase">{{ $loan['dir'] }}</span>
                            <span class="text-[15px] font-semibold text-ink">{{ $loan['counterparty'] }}</span>
                            <span class="ml-auto rounded-full bg-card px-2.5 py-[3px] text-[11px] font-semibold {{ $loan['statusTone'] }}">{{ $loan['status'] }}</span>
                        </div>
                        <div class="mt-3.5 grid grid-cols-2 gap-4 sm:grid-cols-4">
                            @foreach ($loan['fields'] as $field)
                                <div>
                                    <p class="text-[11px] font-semibold tracking-[0.3px] text-muted-soft uppercase">{{ $field['k'] }}</p>
                                    <p class="mt-1 text-[13.5px] font-medium text-ink">{{ $field['v'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
                <div class="my-4 flex items-start gap-x-2.5 rounded-xl border border-hairline bg-sidebar px-4 py-3">
                    @svg('lucide-info', 'size-4 shrink-0 text-muted-soft mt-0.5')
                    <span class="text-[12.5px] leading-[1.55] text-body">{{ __('While an outgoing loan is active, this copy is marked as not in your physical custody, so a quick glance tells you it is out on exhibition, not lost.') }}</span>
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-[1200px] px-5 pt-24 sm:px-8 sm:pt-28">
        <div class="mb-10 max-w-[660px]">
            <p class="mb-3.5 text-[13px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ __('The paper trail') }}</p>
            <h2 class="text-[28px] leading-[1.12] font-semibold tracking-[-1px] text-ink sm:text-4xl lg:text-[38px] lg:tracking-[-1.1px]">{{ __('Build a credible record over time.') }}</h2>
            <p class="mt-5 text-[16px] leading-relaxed text-pretty text-muted">
                {{ __('Keep provenance, location moves, receipts, certificates, and external links with the copy. It is the practical trail behind the thing you care about.') }}
            </p>
        </div>

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
            <div class="overflow-hidden rounded-2xl border border-hairline bg-canvas shadow-[0_4px_12px_rgba(17,17,17,0.04)]">
                <div class="border-b border-hairline-soft px-5 py-4">
                    <span class="text-[15px] font-semibold text-ink">{{ __('Provenance & locations') }}</span>
                </div>
                <div class="px-5 pt-1">
                    @foreach ($timeline as $event)
                        <div class="flex gap-x-3.5 border-b border-hairline-soft py-3.5 last:border-b-0">
                            <span class="mt-[5px] h-[9px] w-[9px] shrink-0 {{ $event['square'] ? 'rounded-[3px]' : 'rounded-full' }}" style="background:{{ $event['dot'] }};"></span>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-baseline gap-x-2">
                                    <span class="text-[13.5px] font-semibold text-ink">{{ $event['title'] }}</span>
                                    <span class="ml-auto shrink-0 text-[11px] text-muted-soft">{{ $event['date'] }}</span>
                                </div>
                                <p class="mt-0.5 text-[12.5px] text-pretty text-muted">{{ $event['desc'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-hairline bg-canvas shadow-[0_4px_12px_rgba(17,17,17,0.04)]">
                <div class="flex items-center gap-x-2.5 border-b border-hairline-soft px-5 py-4">
                    <span class="text-[15px] font-semibold text-ink">{{ __('Documents & links') }}</span>
                    <span class="ml-auto text-[11px] text-muted">{{ __(':count attached', ['count' => 5]) }}</span>
                </div>
                <div class="px-5 pt-1">
                    @foreach ($docs as $doc)
                        <div class="flex items-center gap-x-3 border-b border-hairline-soft py-3">
                            <span class="flex h-[38px] w-8 shrink-0 items-end justify-center rounded-[5px] border border-hairline bg-card pb-1 font-mono text-[8px] font-bold {{ $doc['pdf'] ? 'text-error' : 'text-brand' }}">{{ $doc['ext'] }}</span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-[13px] font-semibold text-ink">{{ $doc['name'] }}</p>
                                <p class="mt-0.5 text-[11.5px] text-muted">{{ $doc['attached'] }}</p>
                            </div>
                            <span class="shrink-0 text-[11px] text-muted-soft">{{ $doc['size'] }}</span>
                        </div>
                    @endforeach
                    <div class="flex items-center gap-x-2 py-3 text-[12.5px] font-semibold text-body">
                        @svg('lucide-external-link', 'size-3.5')
                        {{ __('GoCollect market page · external link') }}
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-9 flex justify-center">
            <a href="{{ route('register') }}" class="inline-flex h-12 items-center justify-center gap-x-2.5 rounded-md border border-hairline bg-canvas px-6 text-[15px] font-semibold text-ink transition-colors hover:bg-sidebar">{{ __('Track a valuable object') }} @svg('lucide-arrow-right', 'size-4')</a>
        </div>
    </section>

    <section class="mx-auto max-w-[1200px] px-5 pt-24 sm:px-8 sm:pt-28">
        <div class="flex flex-col items-start justify-between gap-8 rounded-3xl bg-[#101010] px-6 py-14 text-white sm:px-12 lg:flex-row lg:items-center">
            <div class="max-w-[580px]">
                <h2 class="text-[26px] leading-[1.12] font-semibold tracking-[-1px] text-balance sm:text-[32px] lg:text-[34px]">{{ __('Give your best objects the record they deserve.') }}</h2>
                <p class="mt-4 text-[16px] leading-relaxed text-pretty text-[#a1a1aa]">
                    {{ __('Insurance, valuations, care, custody, storage, and proof of ownership, all beside the physical copy they describe.') }}
                </p>
            </div>
            <a href="{{ route('register') }}" class="inline-flex h-[52px] shrink-0 items-center justify-center gap-x-2.5 rounded-[10px] bg-white px-6.5 text-[15px] font-semibold text-[#111111] transition-colors hover:bg-[#e5e7eb]">{{ __('Give valuables a paper trail') }} @svg('lucide-arrow-right', 'size-4')</a>
        </div>
    </section>

    <section class="mx-auto max-w-[1080px] px-5 pt-24 pb-8 sm:px-8 sm:pt-28">
        <div class="grid grid-cols-1 gap-12 md:grid-cols-2 md:gap-16">
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
