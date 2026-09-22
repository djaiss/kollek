{{-- The "API" feature page of the marketing site. --}}

@php
    $docsUrl = route('marketing.docs.api.index');
    $methodColors = ['GET' => '#2563eb', 'POST' => '#059669', 'PATCH' => '#b45309', 'DELETE' => '#dc2626'];

    $docNavGuide = [__('Introduction'), __('Authentication'), __('Pagination'), __('Rate limits')];
    $docNavEndpoints = [
        ['method' => 'GET', 'label' => __('List items'), 'on' => true],
        ['method' => 'GET', 'label' => __('Get item'), 'on' => false],
        ['method' => 'POST', 'label' => __('Create item'), 'on' => false],
        ['method' => 'GET', 'label' => __('List collections'), 'on' => false],
        ['method' => 'GET', 'label' => __('List locations'), 'on' => false],
        ['method' => 'GET', 'label' => __('List collection types'), 'on' => false],
    ];
    // Real query parameters for GET /api/items.
    $heroParams = [
        ['name' => 'collection', 'type' => 'string', 'req' => __('optional')],
        ['name' => 'per_page', 'type' => 'integer', 'req' => __('optional')],
        ['name' => 'page', 'type' => 'integer', 'req' => __('optional')],
    ];

    $resourceChips = ['collections', 'items', 'copies', 'history', 'locations', 'item-types'];

    // Terminal colours (fixed dark).
    $cW = '#e5e7eb';
    $cStr = '#7dd3a8';
    $cKey = '#93c5fd';
    $cVar = '#c4b5fd';
    $cNum = '#f0b57d';
    // Request (cURL) — real base path /api, Bearer auth, page-based params.
    $requestCode = [
        [['t' => 'curl', 'c' => $cStr], ['t' => ' https://kollek.example/api/items \\', 'c' => $cW]],
        [['t' => '  -H ', 'c' => $cW], ['t' => '"Authorization: Bearer $KOLLEK_KEY"', 'c' => $cVar], ['t' => ' \\', 'c' => $cW]],
        [['t' => '  -G --data-urlencode ', 'c' => $cW], ['t' => '"collection=17"', 'c' => $cVar], ['t' => ' \\', 'c' => $cW]],
        [['t' => '  --data-urlencode ', 'c' => $cW], ['t' => '"per_page=2"', 'c' => $cVar]],
    ];
    // Response — real Laravel list shape: data + links + meta.
    $responseCode = [
        [['t' => '{', 'c' => $cW]],
        [['t' => '  "data"', 'c' => $cKey], ['t' => ': [', 'c' => $cW]],
        [['t' => '    { ', 'c' => $cW], ['t' => '"id"', 'c' => $cKey], ['t' => ': ', 'c' => $cW], ['t' => '512', 'c' => $cNum], ['t' => ',', 'c' => $cW]],
        [['t' => '      "name"', 'c' => $cKey], ['t' => ': ', 'c' => $cW], ['t' => '"Kind of Blue"', 'c' => $cStr], ['t' => ',', 'c' => $cW]],
        [['t' => '      "location"', 'c' => $cKey], ['t' => ': ', 'c' => $cW], ['t' => '"Shelf B2"', 'c' => $cStr], ['t' => ' }', 'c' => $cW]],
        [['t' => '  ],', 'c' => $cW]],
        [['t' => '  "links"', 'c' => $cKey], ['t' => ': { ', 'c' => $cW], ['t' => '"next"', 'c' => $cKey], ['t' => ': ', 'c' => $cW], ['t' => '"…?page=2"', 'c' => $cStr], ['t' => ' },', 'c' => $cW]],
        [['t' => '  "meta"', 'c' => $cKey], ['t' => ': { ', 'c' => $cW], ['t' => '"current_page"', 'c' => $cKey], ['t' => ': ', 'c' => $cW], ['t' => '1', 'c' => $cNum], ['t' => ', ', 'c' => $cW], ['t' => '"total"', 'c' => $cKey], ['t' => ': ', 'c' => $cW], ['t' => '128', 'c' => $cNum], ['t' => ' }', 'c' => $cW]],
        [['t' => '}', 'c' => $cW]],
    ];
    $codeTabs = [['label' => 'cURL', 'on' => true], ['label' => 'JavaScript', 'on' => false], ['label' => 'PHP', 'on' => false]];

    $conventions = [
        ['dot' => '#2563eb', 'label' => __('Page-based pagination (links + meta)')],
        ['dot' => '#f59e0b', 'label' => __(':count requests / min per user', ['count' => 60])],
        ['dot' => '#0f7a4d', 'label' => __('Bearer-token authentication')],
    ];

    $workflowTargets = [
        ['title' => __('A private script'), 'desc' => __('Read the catalogue on a schedule and do something specific with it.'), 'dot' => '#3b82f6', 'square' => true],
        ['title' => __('Your own dashboard'), 'desc' => __('Pull items and locations into a view you built yourself.'), 'dot' => '#8b5cf6', 'square' => true],
        ['title' => __('A spreadsheet'), 'desc' => __('Export a slice of the collection when another tool needs the data.'), 'dot' => '#0f7a4d', 'square' => false],
    ];

    $webhookRows = [
        ['k' => __('Signing secret'), 'v' => 'whsec_••••••8d2f'],
        ['k' => __('Signature scheme'), 'v' => 'HMAC-SHA256'],
        ['k' => __('Deliveries'), 'v' => '0'],
    ];

    // The candid two column footer. Caveats first so they stack above the pitch on mobile.
    $notFor = [
        ['lead' => __('You need turnkey integrations with every service under the sun.'), 'rest' => null],
        ['lead' => __('You need event-driven webhooks today.'), 'rest' => __('Endpoints and signing exist, but no product event fires them yet.')],
    ];
    $chooseWhen = [
        ['lead' => __('You want a documented JSON API'), 'rest' => __('for your own tools and scripts.')],
        ['lead' => __('You need direct, token-authenticated access to the catalogue now.'), 'rest' => null],
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
        <div class="max-w-[780px]">
            <p class="text-[12px] leading-[1.5] font-semibold tracking-[1px] text-muted-soft uppercase">{{ __('For people who look at a catalogue and think “API”') }}</p>
            <h1 class="mt-5 text-[32px] leading-[1.08] font-semibold tracking-[-1px] text-balance text-ink sm:text-5xl sm:tracking-[-1.5px] lg:text-[56px] lg:leading-[1.04] lg:tracking-[-2px]">
                {{ __('Your collection can leave the app. Politely.') }}
            </h1>
            <p class="mt-5.5 max-w-[640px] text-[17px] leading-relaxed text-pretty text-muted sm:text-[18px]">
                {{ __(':name has a documented JSON API for the people who want their collection to connect to a script, a dashboard, an import process of their own, or something wonderfully specific.', ['name' => config('app.name')]) }}
            </p>
            <div class="mt-8 flex">
                <a href="{{ $docsUrl }}" data-turbo="true" class="inline-flex h-12 items-center justify-center gap-x-2.5 rounded-md bg-primary px-6 text-[15px] font-semibold text-on-primary transition-opacity hover:opacity-90">{{ __('Open the toolbox') }} @svg('lucide-arrow-right', 'size-4')</a>
            </div>
        </div>

        <div class="mt-13 overflow-hidden rounded-2xl border border-hairline bg-canvas shadow-[0_24px_60px_rgba(17,17,17,0.08),0_4px_12px_rgba(17,17,17,0.04)]">
            <div class="flex h-11 items-center gap-x-2 border-b border-hairline-soft px-4.5">
                <span class="h-[11px] w-[11px] rounded-full bg-hairline"></span>
                <span class="h-[11px] w-[11px] rounded-full bg-hairline"></span>
                <span class="h-[11px] w-[11px] rounded-full bg-hairline"></span>
                <div class="hidden flex-1 justify-center sm:flex">
                    <span class="rounded-full border border-hairline bg-sidebar px-3.5 py-1 font-mono text-[11px] text-muted-soft">{{ Str::lower(config('app.name')) }}.app / docs / api / items</span>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-[230px_1fr]">
                <div class="border-b border-hairline-soft bg-sidebar p-4 md:border-r md:border-b-0">
                    <p class="mb-2 ml-1 text-[10px] font-semibold tracking-[0.06em] text-muted-soft uppercase">{{ __('Getting started') }}</p>
                    @foreach ($docNavGuide as $guide)
                        <p class="rounded-md px-2 py-1.5 text-[13px] font-medium text-body">{{ $guide }}</p>
                    @endforeach
                    <p class="mt-5 mb-2 ml-1 text-[10px] font-semibold tracking-[0.06em] text-muted-soft uppercase">{{ __('Resources') }}</p>
                    @foreach ($docNavEndpoints as $endpoint)
                        <div @class(['flex items-center gap-x-2 rounded-md px-2 py-1.5', 'bg-card' => $endpoint['on']])>
                            <span class="w-[34px] shrink-0 font-mono text-[9px] font-bold" style="color:{{ $methodColors[$endpoint['method']] }};">{{ $endpoint['method'] }}</span>
                            <span @class(['text-[12.5px]', 'font-semibold text-ink' => $endpoint['on'], 'font-medium text-body' => ! $endpoint['on']])>{{ $endpoint['label'] }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="p-6 sm:p-7">
                    <div class="mb-4 flex flex-wrap items-center gap-2.5">
                        <span class="rounded-md px-2 py-1 font-mono text-[11px] font-bold" style="color:#2563eb;background:rgba(59,130,246,0.12);">GET</span>
                        <span class="font-mono text-[14px] font-semibold text-ink">/api/items</span>
                        <span class="ml-auto rounded-full bg-card px-2 py-[3px] text-[10px] font-bold tracking-[0.4px] text-success">{{ __('GENERATED FROM SOURCE') }}</span>
                    </div>
                    <p class="mb-2 text-[22px] font-semibold tracking-[-0.5px] text-ink">{{ __('List items') }}</p>
                    <p class="mb-5 max-w-[440px] text-[14px] leading-relaxed text-muted">{!! __('Retrieve items across every collection, or scope the list to one collection with the <code>collection</code> query parameter. Returns a paginated list of item objects.', ['code' => '<span class="font-mono text-[12.5px] text-body">collection</span>']) !!}</p>
                    <p class="mb-2.5 text-[12px] font-bold tracking-[0.4px] text-muted-soft uppercase">{{ __('Query parameters') }}</p>
                    @foreach ($heroParams as $param)
                        <div class="flex items-baseline gap-x-2.5 border-t border-hairline-soft py-2.5">
                            <span class="font-mono text-[13px] font-bold text-ink">{{ $param['name'] }}</span>
                            <span class="font-mono text-[12px] text-muted-soft">{{ $param['type'] }}</span>
                            <span class="ml-auto font-mono text-[12px] text-muted-soft">{{ $param['req'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-[1200px] px-5 pt-24 sm:px-8 sm:pt-28">
        <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-[1fr_1.05fr] lg:gap-14">
            <div>
                <p class="mb-3.5 text-[13px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ __('Personal API keys') }}</p>
                <h2 class="text-[28px] leading-[1.12] font-semibold tracking-[-1px] text-ink sm:text-4xl lg:text-[38px] lg:tracking-[-1.1px]">{{ __('The same catalogue, available to your tools.') }}</h2>
                <p class="mt-5 text-[16px] leading-relaxed text-pretty text-muted">
                    {{ __('Use a personal API key to read and manage the collection through straightforward HTTP requests. Collections, items, copies, history, locations, and the rest are not trapped behind the interface.') }}
                </p>
                <div class="mt-6 flex flex-wrap gap-2">
                    @foreach ($resourceChips as $chip)
                        <span class="rounded-full border border-hairline bg-canvas px-3 py-1.5 font-mono text-[13px] font-medium text-body">{{ $chip }}</span>
                    @endforeach
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-hairline bg-canvas shadow-[0_24px_60px_rgba(17,17,17,0.08),0_4px_12px_rgba(17,17,17,0.04)]">
                <div class="flex items-center gap-x-2.5 border-b border-hairline-soft px-5 py-4">
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-card">@svg('lucide-key-round', 'size-4 text-success')</span>
                    <span class="text-[15px] font-semibold text-ink">{{ __('Create API key') }}</span>
                    <span class="ml-auto text-[11px] text-muted">{{ __('Settings · Developer') }}</span>
                </div>
                <div class="p-5">
                    <p class="mb-1.5 text-[12px] font-semibold text-body">{{ __('Name') }}</p>
                    <div class="flex h-[38px] items-center rounded-lg border border-hairline bg-canvas px-3 text-[14px] text-ink">{{ __('Household dashboard sync') }}</div>

                    <div class="mt-5 rounded-xl bg-[#0f1115] p-4">
                        <div class="mb-2 flex items-center justify-between">
                            <span class="text-[11px] font-semibold tracking-[0.4px] text-[#8b93a1] uppercase">{{ __('Your new key') }}</span>
                            <span class="inline-flex items-center gap-x-1.5 rounded-md bg-[#1e222b] px-2.5 py-1 text-[11px] font-semibold text-[#e5e7eb]">@svg('lucide-copy', 'size-3') {{ __('Copy') }}</span>
                        </div>
                        <p class="font-mono text-[12.5px] leading-[1.5] break-all text-[#7dd3a8]">14|aB3cD4eF5gH6iJ7kL8mN9oP0qR1sT2uV3wX4yZ5aK</p>
                    </div>
                    <div class="mt-3 flex items-start gap-x-2">
                        @svg('lucide-triangle-alert', 'size-3.5 shrink-0 text-warning mt-0.5')
                        <span class="text-[12.5px] leading-[1.5] text-warning">{{ __('Copy it now. This is the only time the full key will be shown.') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-[1200px] px-5 pt-24 sm:px-8 sm:pt-28">
        <div class="mb-10 max-w-[660px]">
            <p class="mb-3.5 text-[13px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ __('Reference') }}</p>
            <h2 class="text-[28px] leading-[1.12] font-semibold tracking-[-1px] text-ink sm:text-4xl lg:text-[38px] lg:tracking-[-1.1px]">{{ __('Documentation that comes from the product.') }}</h2>
            <p class="mt-5 text-[16px] leading-relaxed text-pretty text-muted">
                {{ __('The API reference is generated from the codebase. That means the endpoint documentation is built to stay close to what the app actually supports, rather than drifting into archaeological fiction.') }}
            </p>
        </div>

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
            <div class="overflow-hidden rounded-2xl bg-[#0f1115] shadow-[0_24px_60px_rgba(17,17,17,0.10)]">
                <div class="flex items-center gap-x-1.5 border-b border-[#1e222b] px-4 py-3">
                    @foreach ($codeTabs as $tab)
                        <span @class(['rounded-md px-2.5 py-1 text-[12px] font-semibold', 'bg-[#1e222b] text-white' => $tab['on'], 'text-[#8b93a1]' => ! $tab['on']])>{{ $tab['label'] }}</span>
                    @endforeach
                    <span class="ml-auto text-[11px] font-semibold text-[#8b93a1]">{{ __('Request') }}</span>
                </div>
                <div class="overflow-x-auto p-4.5">
                    @foreach ($requestCode as $line)
                        <div class="font-mono text-[12.5px] leading-[1.75] whitespace-pre">@foreach ($line as $seg)<span style="color:{{ $seg['c'] }};">{{ $seg['t'] }}</span>@endforeach</div>
                    @endforeach
                </div>
            </div>
            <div class="overflow-hidden rounded-2xl bg-[#0f1115] shadow-[0_24px_60px_rgba(17,17,17,0.10)]">
                <div class="flex items-center gap-x-2 border-b border-[#1e222b] px-4 py-3">
                    <span class="rounded-md px-2 py-1 font-mono text-[11px] font-bold text-[#7dd3a8]" style="background:rgba(125,211,168,0.14);">200 OK</span>
                    <span class="ml-auto text-[11px] font-semibold text-[#8b93a1]">{{ __('Response · application/json') }}</span>
                </div>
                <div class="overflow-x-auto p-4.5">
                    @foreach ($responseCode as $line)
                        <div class="font-mono text-[12.5px] leading-[1.75] whitespace-pre">@foreach ($line as $seg)<span style="color:{{ $seg['c'] }};">{{ $seg['t'] }}</span>@endforeach</div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="mt-5 flex flex-wrap gap-x-6 gap-y-2.5">
            @foreach ($conventions as $convention)
                <span class="flex items-center gap-x-2 text-[13px] text-muted"><span class="h-1.5 w-1.5 rounded-full" style="background:{{ $convention['dot'] }};"></span>{{ $convention['label'] }}</span>
            @endforeach
        </div>
    </section>

    <section class="mx-auto max-w-[1200px] px-5 pt-24 sm:px-8 sm:pt-28">
        <div class="mb-10 max-w-[660px]">
            <p class="mb-3.5 text-[13px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ __('Interoperability') }}</p>
            <h2 class="text-[28px] leading-[1.12] font-semibold tracking-[-1px] text-ink sm:text-4xl lg:text-[38px] lg:tracking-[-1.1px]">{{ __('An escape hatch, on purpose.') }}</h2>
            <p class="mt-5 text-[16px] leading-relaxed text-pretty text-muted">
                {{ __('Need to analyse your catalogue elsewhere? Build a private report? Move information into another workflow? The API gives you a clean route out without making the product less useful inside.') }}
            </p>
        </div>

        <div class="rounded-2xl border border-hairline bg-canvas p-6 shadow-[0_4px_12px_rgba(17,17,17,0.04)] sm:p-10">
            <div class="grid grid-cols-1 items-center gap-8 lg:grid-cols-[auto_1fr] lg:gap-12">
                <div class="flex flex-col items-center gap-3">
                    <div class="w-[160px] rounded-2xl bg-[#101010] p-5 text-center text-white">
                        <x-logo size="24" class="mx-auto mb-2.5" aria-hidden="true" />
                        <p class="text-[14px] font-semibold">{{ __(':name API', ['name' => config('app.name')]) }}</p>
                        <p class="mt-1 font-mono text-[10px] text-[#8b93a1]">{{ Str::lower(config('app.name')) }}.app/api</p>
                    </div>
                    <span class="text-[11px] font-semibold tracking-[0.5px] text-muted-soft uppercase">{{ __('Your catalogue') }}</span>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    @foreach ($workflowTargets as $target)
                        <div class="rounded-xl border border-hairline bg-sidebar p-4.5">
                            <span class="mb-3.5 flex h-[34px] w-[34px] items-center justify-center rounded-[9px] border border-hairline bg-canvas">
                                <span class="h-3.5 w-3.5 {{ $target['square'] ? 'rounded-[3px]' : 'rounded-full' }}" style="background:{{ $target['dot'] }};"></span>
                            </span>
                            <p class="text-[14.5px] font-semibold tracking-[-0.2px] text-ink">{{ $target['title'] }}</p>
                            <p class="mt-1.5 text-[12.5px] leading-[1.5] text-pretty text-muted">{{ $target['desc'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-[1200px] px-5 pt-24 sm:px-8 sm:pt-28">
        <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-[1fr_1.05fr] lg:gap-14">
            <div>
                <p class="mb-3.5 text-[13px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ __('Webhooks') }}</p>
                <h2 class="text-[28px] leading-[1.12] font-semibold tracking-[-1px] text-ink sm:text-4xl lg:text-[38px] lg:tracking-[-1.1px]">{{ __('Webhooks: honest status report.') }}</h2>
                <p class="mt-5 text-[16px] leading-relaxed text-pretty text-muted">
                    {{ __('You can register signed webhook endpoints today. Product events do not trigger deliveries yet. We are not going to call that “coming soon” and hope you infer the rest.') }}
                </p>
            </div>

            <div class="overflow-hidden rounded-2xl border border-hairline bg-canvas shadow-[0_24px_60px_rgba(17,17,17,0.08),0_4px_12px_rgba(17,17,17,0.04)]">
                <div class="flex items-center gap-x-2.5 border-b border-hairline-soft px-5 py-4">
                    <span class="text-[15px] font-semibold text-ink">{{ __('Webhook endpoints') }}</span>
                    <span class="ml-auto text-[11px] text-muted">{{ __(':count registered', ['count' => 1]) }}</span>
                </div>
                <div class="p-5">
                    <div class="rounded-xl border border-hairline px-4 py-3.5">
                        <div class="mb-3 flex items-center gap-x-2">
                            <span class="truncate font-mono text-[12.5px] text-ink">https://hooks.mydash.io/kollek</span>
                            <span class="ml-auto inline-flex shrink-0 items-center gap-x-1.5 rounded-full bg-card px-2 py-[3px] text-[10px] font-semibold text-body"><span class="h-1.5 w-1.5 rounded-full bg-muted-soft"></span>{{ __('Idle') }}</span>
                        </div>
                        @foreach ($webhookRows as $row)
                            <div class="flex items-center justify-between border-t border-hairline-soft py-2.5">
                                <span class="text-[12.5px] text-muted">{{ $row['k'] }}</span>
                                <span class="font-mono text-[12px] text-body">{{ $row['v'] }}</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-3.5 flex items-start gap-x-2.5 rounded-xl border border-hairline bg-sidebar px-4 py-3">
                        @svg('lucide-info', 'size-4 shrink-0 text-muted-soft mt-0.5')
                        <span class="text-[12.5px] leading-[1.55] text-body">{{ __('Endpoint registration and signing work now. No product event fires a delivery yet, so this endpoint will stay idle until it does.') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-[1200px] px-5 pt-24 sm:px-8 sm:pt-28">
        <div class="flex flex-col items-start justify-between gap-8 rounded-3xl bg-[#101010] px-6 py-14 text-white sm:px-12 lg:flex-row lg:items-center">
            <div class="max-w-[560px]">
                <h2 class="text-[26px] leading-[1.12] font-semibold tracking-[-1px] text-balance sm:text-[32px] lg:text-[34px]">{{ __('Read the reference. Build the thing you actually wanted.') }}</h2>
                <p class="mt-4 text-[16px] leading-relaxed text-pretty text-[#a1a1aa]">
                    {{ __('Endpoints, authentication, pagination, and rate limits, generated from the codebase and ready for your key.') }}
                </p>
            </div>
            <a href="{{ $docsUrl }}" data-turbo="true" class="inline-flex h-[52px] shrink-0 items-center justify-center gap-x-2.5 rounded-[10px] bg-white px-6.5 text-[15px] font-semibold text-[#111111] transition-colors hover:bg-[#e5e7eb]">{{ __('Explore the API reference') }} @svg('lucide-arrow-right', 'size-4')</a>
        </div>
    </section>

    <section class="mx-auto max-w-[1080px] px-5 pt-24 pb-8 sm:px-8 sm:pt-28">
        <div class="grid grid-cols-1 gap-12 md:grid-cols-2 md:gap-16">
            <div>
                <h3 class="mb-2 text-[22px] leading-[1.2] font-semibold tracking-[-0.5px] text-ink">{{ __(':name might not be for you (yet) if…', ['name' => config('app.name')]) }}</h3>
                <div class="border-t-2 border-ink">
                    @foreach ($notFor as $row)
                        <div class="border-b border-hairline py-5.5">
                            <p class="text-[16px] leading-[1.5] text-pretty text-ink"><span class="font-semibold">{{ $row['lead'] }}</span>@if (! is_null($row['rest']))<span class="text-muted"> {{ $row['rest'] }}</span>@endif</p>
                        </div>
                    @endforeach
                </div>
            </div>
            <div>
                <h3 class="mb-2 text-[22px] leading-[1.2] font-semibold tracking-[-0.5px] text-ink">{{ __('Choose :name when…', ['name' => config('app.name')]) }}</h3>
                <div class="border-t-2 border-ink">
                    @foreach ($chooseWhen as $row)
                        <div class="border-b border-hairline py-5.5">
                            <p class="text-[16px] leading-[1.5] text-pretty text-ink"><span class="font-semibold">{{ $row['lead'] }}</span>@if (! is_null($row['rest']))<span class="text-muted"> {{ $row['rest'] }}</span>@endif</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <p class="mt-6 max-w-[640px] text-[13.5px] leading-relaxed text-muted-soft">
            {{ __('We will update this page when the product changes.') }}
            <a href="{{ $docsUrl }}" data-turbo="true" class="border-b border-hairline text-body transition-colors hover:text-ink">{{ __('The feature status page has the boring-but-important details.') }}</a>
        </p>
    </section>
</x-marketing-layout>
