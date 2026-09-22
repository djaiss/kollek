{{-- The "Organization" feature page of the marketing site. --}}

@php
    // Sample collection data, kept illustrative (real album titles and artists) so the
    // captures read like a populated product rather than lorem ipsum.
    $gridItems = [
        ['initials' => 'KB', 'name' => 'Kind of Blue', 'meta' => 'Miles Davis · 1959'],
        ['initials' => 'LS', 'name' => 'A Love Supreme', 'meta' => 'John Coltrane · 1965'],
        ['initials' => 'TS', 'name' => 'The Sidewinder', 'meta' => 'Lee Morgan · 1964'],
        ['initials' => 'SC', 'name' => 'Saxophone Colossus', 'meta' => 'Sonny Rollins · 1957'],
        ['initials' => 'MA', 'name' => 'Mingus Ah Um', 'meta' => 'Charles Mingus · 1959'],
        ['initials' => 'TO', 'name' => 'Time Out', 'meta' => 'Dave Brubeck · 1959'],
    ];

    $listRows = [
        ['initials' => 'MM', 'name' => 'Moanin’', 'meta' => 'Art Blakey · Blue Note', 'tag' => 'Hard bop'],
        ['initials' => 'SN', 'name' => 'Speak No Evil', 'meta' => 'Wayne Shorter · 1966', 'tag' => 'Modal'],
        ['initials' => 'GO', 'name' => 'Go', 'meta' => 'Dexter Gordon · 1962', 'tag' => 'Hard bop'],
        ['initials' => 'OL', 'name' => 'Out to Lunch!', 'meta' => 'Eric Dolphy · 1964', 'tag' => 'Free'],
    ];

    $tableRows = [
        ['title' => 'Kind of Blue', 'artist' => 'Miles Davis', 'year' => '1959', 'condition' => 'NM'],
        ['title' => 'A Love Supreme', 'artist' => 'John Coltrane', 'year' => '1965', 'condition' => 'VG+'],
        ['title' => 'The Sidewinder', 'artist' => 'Lee Morgan', 'year' => '1964', 'condition' => 'NM'],
        ['title' => 'Mingus Ah Um', 'artist' => 'Charles Mingus', 'year' => '1959', 'condition' => 'VG'],
    ];

    // The other ways an object belongs, beyond its location. Dot colours are fixed theme
    // accents (they do not invert), so a facet reads the same in light and dark.
    $attributeChips = [
        ['label' => __('Categories'), 'dot' => '#fb923c'],
        ['label' => __('Locations'), 'dot' => '#3b82f6'],
        ['label' => __('Series'), 'dot' => '#8b5cf6'],
        ['label' => __('Tags'), 'dot' => '#0ea5e9'],
        ['label' => __('Conditions'), 'dot' => '#10b981'],
    ];

    // A location nests from room to cabinet to shelf. pad is the indent, and leaf rows (the
    // shelves) show a small square marker instead of a folder chevron.
    $tree = [
        ['label' => __('Home'), 'count' => 128, 'pad' => '10px', 'icon' => 'lucide-chevron-down', 'text' => 'text-ink', 'weight' => 'font-semibold'],
        ['label' => __('Living room'), 'count' => 96, 'pad' => '30px', 'icon' => 'lucide-chevron-down', 'text' => 'text-body', 'weight' => 'font-semibold'],
        ['label' => __('Record cabinet'), 'count' => 96, 'pad' => '50px', 'icon' => 'lucide-chevron-down', 'text' => 'text-body', 'weight' => 'font-semibold', 'highlight' => true],
        ['label' => __('Shelf A · Hard bop'), 'count' => 41, 'pad' => '72px', 'leaf' => '#fb923c', 'text' => 'text-ink', 'weight' => 'font-semibold'],
        ['label' => __('Shelf B · Modal & free'), 'count' => 33, 'pad' => '72px', 'leaf' => '#fb923c', 'text' => 'text-ink', 'weight' => 'font-semibold'],
        ['label' => __('Shelf C · 78s & oddities'), 'count' => 22, 'pad' => '72px', 'leaf' => 'muted', 'text' => 'text-muted', 'weight' => 'font-medium'],
        ['label' => __('Office'), 'count' => 32, 'pad' => '30px', 'icon' => 'lucide-chevron-right', 'text' => 'text-body', 'weight' => 'font-semibold'],
    ];

    $smallSets = [
        ['name' => 'Impulse! Coltrane', 'pct' => '61%', 'count' => '11 / 18'],
        ['name' => 'Riverside Monk', 'pct' => '38%', 'count' => '5 / 13'],
    ];

    // The in-collection filter. Mono is the checked tag in this capture.
    $filterTags = [
        ['label' => 'Reissue', 'count' => 44, 'on' => false],
        ['label' => 'Mono', 'count' => 18, 'on' => true],
        ['label' => 'Original press', 'count' => 27, 'on' => false],
        ['label' => 'Sealed', 'count' => 9, 'on' => false],
        ['label' => 'Signed', 'count' => 3, 'on' => false],
    ];

    $filteredItems = [
        ['initials' => 'KB', 'name' => 'Kind of Blue'],
        ['initials' => 'MA', 'name' => 'Mingus Ah Um'],
        ['initials' => 'BC', 'name' => 'Brilliant Corners'],
        ['initials' => 'CB', 'name' => 'Clifford Brown'],
        ['initials' => 'EI', 'name' => 'Empyrean Isles'],
        ['initials' => 'GC', 'name' => 'The Great Concert'],
    ];

    // The candid two column footer. The left column (caveats) is authored first so it stacks
    // above the pitch on mobile: a visitor sees the honest bit before the sell.
    $notFor = [
        ['head' => __('You need global search across every collection today.'), 'body' => __('It is planned, not shipped.')],
        ['head' => __('One flat alphabetical list is your ideal organization system.'), 'body' => null],
    ];

    $chooseWhen = [
        ['head' => __('You want focused in-collection filtering and several ways to browse what is already in front of you.'), 'body' => null],
        ['head' => __('Categories, locations, sets, series, tags, and conditions reflect how you think about the collection.'), 'body' => null],
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
            <p class="text-[12px] font-semibold tracking-[1px] text-muted-soft uppercase">{{ __('The shelf is not a database. Sadly.') }}</p>
            <h1 class="mt-5 text-[32px] leading-[1.08] font-semibold tracking-[-1px] text-balance text-ink sm:text-5xl sm:tracking-[-1.5px] lg:text-[56px] lg:leading-[1.04] lg:tracking-[-2px]">
                {{ __('Organize it your way. Then find it again.') }}
            </h1>
            <p class="mt-5.5 max-w-[660px] text-[17px] leading-relaxed text-pretty text-muted sm:text-[18px]">
                {{ __('Collections grow. Shelves fill. The box labelled “other” becomes a lifestyle. :name gives you several sensible ways to arrange what you own, then lets you browse it according to the question in front of you.', ['name' => config('app.name')]) }}
            </p>
            <div class="mt-8 flex">
                <a href="{{ route('register') }}" class="inline-flex h-12 items-center justify-center gap-x-2.5 rounded-md bg-primary px-6 text-[15px] font-semibold text-on-primary transition-opacity hover:opacity-90">{{ __('Tidy the chaos') }} @svg('lucide-arrow-right', 'size-4')</a>
            </div>
        </div>

        <div class="mt-13 overflow-hidden rounded-2xl border border-hairline bg-canvas shadow-[0_24px_60px_rgba(17,17,17,0.08),0_4px_12px_rgba(17,17,17,0.04)]">
            <div class="flex items-center gap-x-2 border-b border-hairline-soft px-4.5 py-3">
                <span class="h-[11px] w-[11px] rounded-full bg-hairline"></span>
                <span class="h-[11px] w-[11px] rounded-full bg-hairline"></span>
                <span class="h-[11px] w-[11px] rounded-full bg-hairline"></span>
                <div class="hidden flex-1 justify-center sm:flex">
                    <span class="rounded-full border border-hairline bg-sidebar px-3.5 py-1 font-mono text-[11px] text-muted-soft">{{ Str::lower(config('app.name')) }}.app / collections / jazz-on-vinyl</span>
                </div>
            </div>
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-hairline-soft px-5 py-4 sm:px-6">
                <div>
                    <p class="text-[19px] font-semibold tracking-[-0.4px] text-ink">Jazz on Vinyl</p>
                    <p class="mt-0.5 text-[13px] text-muted">{{ __(':count items', ['count' => 128]) }} · {{ __('Living room') }}, {{ __('Record cabinet') }}</p>
                </div>
                <div class="flex items-center gap-x-3.5">
                    <span class="hidden text-[11px] font-semibold tracking-[0.5px] text-muted-soft uppercase sm:inline">{{ __('View') }}</span>
                    <div class="flex gap-1 rounded-[9px] border border-hairline bg-sidebar p-[3px]">
                        <span class="flex items-center gap-x-1.5 rounded-[7px] border border-hairline bg-canvas px-3 py-1.5 text-[13px] font-semibold text-ink shadow-[0_1px_2px_rgba(17,17,17,0.05)]">
                            @svg('lucide-layout-grid', 'size-3.5')
                            {{ __('Grid') }}
                        </span>
                        <span class="flex items-center gap-x-1.5 rounded-[7px] px-3 py-1.5 text-[13px] font-medium text-muted">
                            @svg('lucide-list', 'size-3.5')
                            {{ __('List') }}
                        </span>
                        <span class="flex items-center gap-x-1.5 rounded-[7px] px-3 py-1.5 text-[13px] font-medium text-muted">
                            @svg('lucide-table', 'size-3.5')
                            {{ __('Table') }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="bg-sidebar px-5 py-6 sm:px-6">
                <div class="grid grid-cols-3 gap-4 sm:grid-cols-6 sm:gap-4.5">
                    @foreach ($gridItems as $item)
                        <div>
                            <div class="flex aspect-square items-end rounded-[10px] border border-hairline p-2.5" style="background:repeating-linear-gradient(135deg,var(--color-hairline) 0 10px,var(--color-hairline-soft) 10px 20px);">
                                <span class="font-mono text-[18px] font-semibold text-muted-soft">{{ $item['initials'] }}</span>
                            </div>
                            <p class="mt-2 truncate text-[13px] font-semibold tracking-[-0.2px] text-ink">{{ $item['name'] }}</p>
                            <p class="mt-0.5 truncate text-[11.5px] text-muted">{{ $item['meta'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-[1200px] px-5 pt-24 sm:px-8 sm:pt-28">
        <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-[1fr_1.1fr] lg:gap-14">
            <div>
                <p class="mb-3.5 text-[13px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ __('Views') }}</p>
                <h2 class="text-[28px] leading-[1.12] font-semibold tracking-[-1px] text-ink sm:text-4xl lg:text-[38px] lg:tracking-[-1.1px]">{{ __('Browse the way your brain works.') }}</h2>
                <p class="mt-5 text-[16px] leading-relaxed text-pretty text-muted">
                    {{ __('Use a visual grid when covers do the talking, a list when you want to scan quickly, or a table when details need comparing. :name remembers the view you prefer for each collection.', ['name' => config('app.name')]) }}
                </p>
            </div>

            <div class="flex flex-col gap-4">
                <div class="overflow-hidden rounded-2xl border border-hairline bg-canvas shadow-[0_12px_32px_rgba(17,17,17,0.06)]">
                    <div class="flex items-center gap-x-2 border-b border-hairline-soft px-4 py-3 text-ink">
                        @svg('lucide-list', 'size-3.5')
                        <span class="text-[13px] font-semibold">{{ __('List view') }}</span>
                    </div>
                    @foreach ($listRows as $row)
                        <div class="flex items-center gap-3.5 border-t border-hairline-soft px-4 py-3">
                            <span class="flex h-[38px] w-[38px] shrink-0 items-center justify-center rounded-md border border-hairline font-mono text-[11px] font-semibold text-muted-soft" style="background:repeating-linear-gradient(135deg,var(--color-hairline) 0 6px,var(--color-hairline-soft) 6px 12px);">{{ $row['initials'] }}</span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-[13.5px] font-semibold tracking-[-0.2px] text-ink">{{ $row['name'] }}</p>
                                <p class="mt-0.5 truncate text-[12px] text-muted">{{ $row['meta'] }}</p>
                            </div>
                            <span class="shrink-0 rounded-full bg-card px-2.5 py-1 text-[11px] font-semibold text-body">{{ $row['tag'] }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="overflow-hidden rounded-2xl border border-hairline bg-canvas shadow-[0_12px_32px_rgba(17,17,17,0.06)]">
                    <div class="flex items-center gap-x-2 border-b border-hairline-soft px-4 py-3 text-ink">
                        @svg('lucide-table', 'size-3.5')
                        <span class="text-[13px] font-semibold">{{ __('Table view') }}</span>
                    </div>
                    <div class="grid grid-cols-[1.6fr_1fr_0.8fr_1fr] border-t border-hairline-soft bg-sidebar px-4 py-2.5">
                        @foreach ([__('Title'), __('Artist'), __('Year'), __('Condition')] as $head)
                            <span class="text-[10px] font-bold tracking-[0.5px] text-muted-soft uppercase">{{ $head }}</span>
                        @endforeach
                    </div>
                    @foreach ($tableRows as $row)
                        <div class="grid grid-cols-[1.6fr_1fr_0.8fr_1fr] items-center border-t border-hairline-soft px-4 py-2.5">
                            <span class="truncate text-[12.5px] font-semibold tracking-[-0.2px] text-ink">{{ $row['title'] }}</span>
                            <span class="truncate text-[12.5px] text-body">{{ $row['artist'] }}</span>
                            <span class="text-[12.5px] text-body">{{ $row['year'] }}</span>
                            <span class="font-mono text-[11px] text-muted">{{ $row['condition'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-[1200px] px-5 pt-24 sm:px-8 sm:pt-28">
        <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-[1.1fr_1fr] lg:gap-14">
            <div class="order-2 overflow-hidden rounded-2xl border border-hairline bg-canvas shadow-[0_24px_60px_rgba(17,17,17,0.08),0_4px_12px_rgba(17,17,17,0.04)] lg:order-1">
                <div class="flex items-center gap-x-2.5 border-b border-hairline-soft px-5 py-4 text-ink">
                    @svg('lucide-house', 'size-4')
                    <span class="text-[15px] font-semibold">{{ __('Locations') }}</span>
                    <span class="ml-auto text-[11px] text-muted">{{ __(':rooms rooms · :count items', ['rooms' => 3, 'count' => 128]) }}</span>
                </div>
                <div class="p-3">
                    @foreach ($tree as $node)
                        <div @class(['flex items-center gap-x-2 rounded-lg py-2', 'border border-hairline bg-sidebar' => $node['highlight'] ?? false]) style="padding-left:{{ $node['pad'] }}; padding-right:10px;">
                            @if (isset($node['leaf']))
                                @if ($node['leaf'] === 'muted')
                                    <span class="h-1.5 w-1.5 shrink-0 rounded-[2px] bg-hairline"></span>
                                @else
                                    <span class="h-1.5 w-1.5 shrink-0 rounded-[2px]" style="background:{{ $node['leaf'] }};"></span>
                                @endif
                            @else
                                @svg($node['icon'], 'size-3 shrink-0 text-muted-soft')
                            @endif
                            <span class="text-[13px] {{ $node['weight'] }} {{ $node['text'] }}">{{ $node['label'] }}</span>
                            <span class="ml-auto text-[11px] text-muted-soft">{{ $node['count'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="order-1 lg:order-2">
                <p class="mb-3.5 text-[13px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ __('Structure') }}</p>
                <h2 class="text-[28px] leading-[1.12] font-semibold tracking-[-1px] text-ink sm:text-4xl lg:text-[38px] lg:tracking-[-1.1px]">{{ __('Give things a place.') }}</h2>
                <p class="mt-5 text-[16px] leading-relaxed text-pretty text-muted">
                    {{ __('Build locations from room to cabinet to shelf. Use categories, tags, series, and conditions to describe the other ways an object belongs. One collection; more than one useful map.') }}
                </p>
                <div class="mt-6 flex flex-wrap gap-2">
                    @foreach ($attributeChips as $chip)
                        <span class="inline-flex items-center gap-x-2 rounded-full border border-hairline bg-canvas px-3 py-1.5 text-[13px] font-medium text-body">
                            <span class="h-[7px] w-[7px] rounded-[2px]" style="background:{{ $chip['dot'] }};"></span>{{ $chip['label'] }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-[1200px] px-5 pt-24 sm:px-8 sm:pt-28">
        <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-[1fr_1.05fr] lg:gap-14">
            <div>
                <p class="mb-3.5 text-[13px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ __('Sets & series') }}</p>
                <h2 class="text-[28px] leading-[1.12] font-semibold tracking-[-1px] text-ink sm:text-4xl lg:text-[38px] lg:tracking-[-1.1px]">{{ __('Keep an eye on the missing pieces.') }}</h2>
                <p class="mt-5 text-[16px] leading-relaxed text-pretty text-muted">
                    {{ __('Sets make completion visible. You can see what you have, what you are still chasing, and whether you have accidentally collected more than the target. It happens.') }}
                </p>
            </div>

            <div class="overflow-hidden rounded-2xl border border-hairline bg-canvas shadow-[0_24px_60px_rgba(17,17,17,0.08),0_4px_12px_rgba(17,17,17,0.04)]">
                <div class="flex items-center border-b border-hairline-soft px-5 py-4">
                    <span class="text-[15px] font-semibold text-ink">{{ __('Sets') }}</span>
                    <span class="ml-auto text-[11px] text-muted">{{ __(':count tracked', ['count' => 3]) }}</span>
                </div>
                <div class="p-5">
                    <div class="rounded-xl border border-hairline p-4.5">
                        <div class="mb-3 flex items-baseline justify-between gap-3">
                            <div>
                                <p class="text-[15px] font-semibold tracking-[-0.2px] text-ink">Blue Note 1500 series</p>
                                <p class="mt-0.5 text-[12px] text-muted">{{ __(':owned of :total owned · :chasing still chasing', ['owned' => 42, 'total' => 50, 'chasing' => 6]) }}</p>
                            </div>
                            <span class="text-[20px] font-semibold tracking-[-0.5px] text-ink">84%</span>
                        </div>
                        <div class="flex h-[9px] overflow-hidden rounded-full bg-card">
                            <span class="bg-success" style="width:84%;"></span>
                        </div>
                        <div class="mt-3.5 flex flex-wrap gap-x-4.5 gap-y-2">
                            <span class="flex items-center gap-x-1.5 text-[12px] font-medium text-body"><span class="h-[9px] w-[9px] rounded-[2px] bg-success"></span>{{ __(':count owned', ['count' => 42]) }}</span>
                            <span class="flex items-center gap-x-1.5 text-[12px] font-medium text-body"><span class="h-[9px] w-[9px] rounded-[2px] bg-hairline"></span>{{ __(':count chasing', ['count' => 6]) }}</span>
                            <span class="flex items-center gap-x-1.5 text-[12px] font-medium text-warning"><span class="h-[9px] w-[9px] rounded-[2px] bg-warning"></span>{{ __(':count duplicates', ['count' => 2]) }}</span>
                        </div>
                    </div>
                    @foreach ($smallSets as $set)
                        <div class="flex items-center gap-3.5 border-b border-hairline-soft px-1 py-3.5">
                            <div class="min-w-0 flex-1">
                                <p class="text-[13.5px] font-semibold tracking-[-0.2px] text-ink">{{ $set['name'] }}</p>
                                <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-card">
                                    <span class="block h-full bg-ink" style="width:{{ $set['pct'] }};"></span>
                                </div>
                            </div>
                            <span class="shrink-0 text-[12.5px] font-semibold text-body">{{ $set['count'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-[1200px] px-5 pt-24 sm:px-8 sm:pt-28">
        <div class="mb-10 max-w-[660px]">
            <p class="mb-3.5 text-[13px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ __('In-collection filter') }}</p>
            <h2 class="text-[28px] leading-[1.12] font-semibold tracking-[-1px] text-ink sm:text-4xl lg:text-[38px] lg:tracking-[-1.1px]">{{ __('Filter the collection in front of you.') }}</h2>
            <p class="mt-5 text-[16px] leading-relaxed text-pretty text-muted">
                {{ __('Narrow a collection by the items already loaded on the page. It is quick, focused, and useful when you know where to look.') }}
            </p>
        </div>

        <div class="grid grid-cols-1 overflow-hidden rounded-2xl border border-hairline bg-canvas shadow-[0_24px_60px_rgba(17,17,17,0.08),0_4px_12px_rgba(17,17,17,0.04)] md:grid-cols-[280px_1fr]">
            <div class="border-b border-hairline-soft bg-sidebar p-5 md:border-r md:border-b-0">
                <div class="mb-4.5 flex items-center gap-x-2 text-ink">
                    @svg('lucide-funnel', 'size-3.5')
                    <span class="text-[14px] font-semibold">{{ __('Filter this collection') }}</span>
                </div>
                <p class="mb-2.5 text-[10px] font-bold tracking-[0.5px] text-muted-soft uppercase">{{ __('Tags') }}</p>
                @foreach ($filterTags as $tag)
                    <div class="flex items-center gap-x-2.5 py-1.5">
                        <span @class([
                            'flex h-4 w-4 shrink-0 items-center justify-center rounded-[5px] border',
                            'border-ink bg-ink' => $tag['on'],
                            'border-hairline bg-canvas' => ! $tag['on'],
                        ])>
                            @if ($tag['on'])
                                @svg('lucide-check', 'size-2.5 text-on-primary')
                            @endif
                        </span>
                        <span @class(['text-[13px]', 'font-semibold text-ink' => $tag['on'], 'font-medium text-body' => ! $tag['on']])>{{ $tag['label'] }}</span>
                        <span class="ml-auto text-[11px] text-muted-soft">{{ $tag['count'] }}</span>
                    </div>
                @endforeach
                <p class="mt-4.5 mb-2.5 text-[10px] font-bold tracking-[0.5px] text-muted-soft uppercase">{{ __('Condition') }}</p>
                <div class="flex flex-wrap gap-1.5">
                    <span class="rounded-[7px] border border-hairline bg-canvas px-2.5 py-1.5 text-[12px] font-semibold text-ink">{{ __('Near Mint') }}</span>
                    <span class="rounded-[7px] border border-hairline bg-canvas px-2.5 py-1.5 text-[12px] font-medium text-muted">VG+</span>
                    <span class="rounded-[7px] border border-hairline bg-canvas px-2.5 py-1.5 text-[12px] font-medium text-muted">Sealed</span>
                </div>
            </div>
            <div class="p-5 sm:p-6">
                <div class="mb-4 flex flex-wrap items-center gap-2.5">
                    <span class="text-[13px] font-semibold text-ink">{{ __('Showing :shown of :total', ['shown' => 18, 'total' => 128]) }}</span>
                    <span class="inline-flex items-center gap-x-1.5 rounded-full bg-card px-2.5 py-1 text-[11px] font-semibold text-body">Mono <span class="text-muted-soft">×</span></span>
                    <span class="inline-flex items-center gap-x-1.5 rounded-full bg-card px-2.5 py-1 text-[11px] font-semibold text-body">{{ __('Near Mint') }} <span class="text-muted-soft">×</span></span>
                    <span class="w-full text-[12px] text-muted-soft sm:ml-auto sm:w-auto">{{ __('In-collection · not account-wide search') }}</span>
                </div>
                <div class="grid grid-cols-3 gap-3.5 sm:grid-cols-6">
                    @foreach ($filteredItems as $item)
                        <div>
                            <div class="flex aspect-square items-end rounded-lg border border-hairline p-2" style="background:repeating-linear-gradient(135deg,var(--color-hairline) 0 8px,var(--color-hairline-soft) 8px 16px);">
                                <span class="font-mono text-[14px] font-semibold text-muted-soft">{{ $item['initials'] }}</span>
                            </div>
                            <p class="mt-1.5 truncate text-[11.5px] font-semibold tracking-[-0.2px] text-ink">{{ $item['name'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="mt-8 flex">
            <a href="{{ route('register') }}" class="inline-flex h-12 items-center justify-center gap-x-2.5 rounded-md bg-primary px-6 text-[15px] font-semibold text-on-primary transition-opacity hover:opacity-90">{{ __('Organize a collection') }} @svg('lucide-arrow-right', 'size-4')</a>
        </div>
    </section>

    <section class="mx-auto max-w-[1200px] px-5 pt-24 sm:px-8 sm:pt-28">
        <div class="flex flex-col items-start justify-between gap-8 rounded-3xl bg-[#101010] px-6 py-14 sm:px-12 lg:flex-row lg:items-center">
            <div class="max-w-[560px]">
                <h2 class="text-[26px] leading-[1.12] font-semibold tracking-[-1px] text-balance text-white sm:text-[32px] lg:text-[34px]">{{ __('Give the collection a shape you can actually navigate.') }}</h2>
                <p class="mt-4 text-[16px] leading-relaxed text-pretty text-[#a1a1aa]">
                    {{ __('Grid, list, and table views. Locations, categories, sets, series, tags, and conditions. Filter what is in front of you and get on with it.') }}
                </p>
            </div>
            <a href="{{ route('register') }}" class="inline-flex h-[52px] shrink-0 items-center justify-center gap-x-2.5 rounded-[10px] bg-white px-6.5 text-[15px] font-semibold text-[#111111] transition-colors hover:bg-[#e5e7eb]">{{ __('Tidy the chaos') }} @svg('lucide-arrow-right', 'size-4')</a>
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
