{{-- The "Photos & browsing" feature page of the marketing site. --}}

@php
    // A tile gradient standing in for cover art. Fixed hues (they represent photos, which do
    // not invert); null gives the neutral "no dominant colour" tile.
    $tile = function (?int $hue): array {
        if ($hue === null) {
            return ['bg' => 'repeating-linear-gradient(45deg,#f1f1f3,#f1f1f3 7px,#e9e9ec 7px,#e9e9ec 14px)', 'icon' => '#b7b7be'];
        }
        $a = "hsl({$hue} 44% 94%)";
        $b = "hsl({$hue} 44% 89%)";
        return ['bg' => "repeating-linear-gradient(45deg,{$a},{$a} 7px,{$b} 7px,{$b} 14px)", 'icon' => "hsl({$hue} 30% 56%)"];
    };
    $dot = fn (int $hue): string => "hsl({$hue} 52% 52%)";

    $heroCovers = [
        ['Kind of Blue', 210], ['Abbey Road', 28], ['Dark Side of the Moon', 285],
        ['Rumours', 340], ['Blue Train', 200], ['Pet Sounds', 45],
        ['A Love Supreme', 160], ['Nevermind', 190], ['Thriller', 10],
        ['Blonde on Blonde', 260], ['Bitches Brew', 300], ['Giant Steps', 175],
    ];

    $galleryChips = [__('Choose the cover'), __('Reorder the gallery'), __('Labels & detail shots')];
    $galleryThumbs = [
        ['hue' => 285, 'tag' => __('cover'), 'cover' => true],
        ['hue' => 285, 'tag' => __('sleeve'), 'cover' => false],
        ['hue' => 285, 'tag' => __('label'), 'cover' => false],
        ['hue' => 285, 'tag' => __('insert'), 'cover' => false],
    ];

    // Browse grid: mixed square covers and portrait comic ratios.
    $browseCovers = [
        [210, '1/1', __('cover')], [28, '1/1', __('cover')], [285, '1/1', __('cover')], [340, '1/1', __('cover')],
        [200, '1/1', __('cover')], [45, '1/1', __('cover')], [160, '1/1', __('cover')], [190, '1/1', __('cover')],
        [150, '2/3', __('comic')], [338, '2/3', __('comic')], [12, '2/3', __('comic')], [264, '2/3', __('comic')],
        [95, '1/1', __('cover')], [305, '1/1', __('cover')], [18, '1/1', __('cover')], [228, '1/1', __('cover')],
    ];

    $libFilters = [
        ['label' => __('All'), 'count' => 48, 'on' => true],
        ['label' => __('Covers'), 'count' => 14, 'on' => false],
        ['label' => __('Extras'), 'count' => 34, 'on' => false],
    ];
    $libPhotos = [
        ['fn' => 'af15_cover_9-4.jpg', 'hue' => 150, 'item' => 'Amazing Fantasy #15', 'dims' => '1800 × 2700', 'cover' => true],
        ['fn' => 'dsotm_cover.jpg', 'hue' => 285, 'item' => 'Dark Side of the Moon', 'dims' => '2200 × 2200', 'cover' => true],
        ['fn' => 'abbeyroad_gatefold.png', 'hue' => 28, 'item' => 'Abbey Road', 'dims' => '3000 × 1500', 'cover' => false],
        ['fn' => 'hulk181_cover.jpg', 'hue' => 340, 'item' => 'Hulk #181', 'dims' => '1800 × 2700', 'cover' => true],
        ['fn' => 'kob_label_side_a.jpg', 'hue' => 210, 'item' => 'Kind of Blue', 'dims' => '1600 × 1600', 'cover' => false],
        ['fn' => 'IMG_4821.HEIC', 'hue' => null, 'item' => null, 'dims' => '4032 × 3024', 'cover' => false],
        ['fn' => 'kob_back_sleeve.jpg', 'hue' => 210, 'item' => 'Kind of Blue', 'dims' => '2400 × 1800', 'cover' => false],
        ['fn' => 'af15_cgc_cert.jpg', 'hue' => 150, 'item' => 'Amazing Fantasy #15', 'dims' => '1200 × 1600', 'cover' => false],
        ['fn' => 'dsotm_poster_insert.jpg', 'hue' => 285, 'item' => 'Dark Side of the Moon', 'dims' => '2600 × 1700', 'cover' => false],
        ['fn' => 'shelf_overview.jpg', 'hue' => null, 'item' => null, 'dims' => '3000 × 2000', 'cover' => false],
        ['fn' => 'abbeyroad_front.png', 'hue' => 28, 'item' => 'Abbey Road', 'dims' => '3000 × 2250', 'cover' => false],
        ['fn' => 'scan_003_lot.png', 'hue' => null, 'item' => null, 'dims' => '2480 × 3508', 'cover' => false],
    ];

    $detailRows = [
        ['label' => __('Dimensions'), 'value' => '1800 × 2700 px'],
        ['label' => __('File size'), 'value' => '4.0 MB'],
        ['label' => __('Format'), 'value' => 'JPEG'],
        ['label' => __('Cover status'), 'value' => __('Cover photo'), 'dot' => true],
        ['label' => __('Uploaded'), 'value' => __(':count days ago', ['count' => 5])],
        ['label' => __('Uploaded by'), 'value' => 'Chandler Bing'],
        ['label' => __('Belongs to'), 'value' => 'Amazing Fantasy #15'],
    ];

    // The candid two column footer. Caveats first so they stack above the pitch on mobile.
    $notFor = [
        __('One tiny thumbnail per title is enough.'),
        __('You need professional digital-asset-management workflows with advanced image editing.'),
    ];
    $chooseWhen = [
        __('Covers, labels, packaging, and detail shots help you recognize what you own.'),
        __('You want one account-wide photo library with search, filters, and bulk cleanup.'),
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
        <div class="max-w-[760px]">
            <p class="text-[12px] leading-[1.5] font-semibold tracking-[1px] text-muted-soft uppercase">{{ __('Because you recognize the cover before the catalogue number') }}</p>
            <h1 class="mt-5 text-[32px] leading-[1.08] font-semibold tracking-[-1px] text-balance text-ink sm:text-5xl sm:tracking-[-1.5px] lg:text-[56px] lg:leading-[1.04] lg:tracking-[-2px]">
                {{ __('A collection should look this good on screen, too.') }}
            </h1>
            <p class="mt-5.5 max-w-[600px] text-[17px] leading-relaxed text-pretty text-muted sm:text-[18px]">
                {{ __('Covers, labels, packaging, and detail shots are part of how collectors remember what they own. :name gives the visual side of a collection enough room to do its job.', ['name' => config('app.name')]) }}
            </p>
            <div class="mt-8 flex">
                <a href="{{ route('register') }}" class="flex h-12 items-center justify-center rounded-md bg-primary px-6 text-[15px] font-semibold text-on-primary transition-opacity hover:opacity-90">{{ __('Give it some shelf appeal') }}</a>
            </div>
        </div>

        <div class="mt-13 overflow-hidden rounded-2xl border border-hairline bg-canvas shadow-[0_24px_60px_rgba(17,17,17,0.08),0_4px_12px_rgba(17,17,17,0.04)]">
            <div class="flex h-11 items-center gap-x-2 border-b border-hairline-soft px-4.5">
                <span class="h-[11px] w-[11px] rounded-full bg-hairline"></span>
                <span class="h-[11px] w-[11px] rounded-full bg-hairline"></span>
                <span class="h-[11px] w-[11px] rounded-full bg-hairline"></span>
                <div class="hidden flex-1 justify-center sm:flex">
                    <span class="rounded-full border border-hairline bg-sidebar px-3.5 py-1 font-mono text-[11px] text-muted-soft">{{ Str::lower(config('app.name')) }}.app / collection / vintage-vinyl</span>
                </div>
            </div>
            <div class="p-5 sm:p-6">
                <div class="mb-5 flex items-center justify-between">
                    <div>
                        <p class="text-[20px] font-semibold tracking-[-0.4px] text-ink">Vintage Vinyl</p>
                        <p class="mt-0.5 text-[13px] text-muted">{{ __(':count records · sorted by shelf', ['count' => 142]) }}</p>
                    </div>
                    <div class="flex gap-0.5 rounded-lg border border-hairline bg-card p-[3px]">
                        <span class="flex h-[30px] w-[34px] items-center justify-center rounded-md bg-canvas text-ink shadow-[0_1px_2px_rgba(0,0,0,0.06)]">@svg('lucide-layout-grid', 'size-4')</span>
                        <span class="flex h-[30px] w-[34px] items-center justify-center rounded-md text-muted-soft">@svg('lucide-list', 'size-4')</span>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-3.5 sm:grid-cols-4 lg:grid-cols-6">
                    @foreach ($heroCovers as [$title, $hue])
                        @php($t = $tile($hue))
                        <div>
                            <div class="relative aspect-square rounded-[10px] border border-hairline" style="background:{{ $t['bg'] }};">
                                <span class="absolute right-0 bottom-1.5 left-0 text-center font-mono text-[9px] opacity-90" style="color:{{ $t['icon'] }};">{{ __('cover') }}</span>
                            </div>
                            <p class="mt-2 truncate text-[11.5px] font-medium text-body">{{ $title }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-[1200px] px-5 pt-24 sm:px-8 sm:pt-28">
        <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-[1fr_1.1fr] lg:gap-14">
            <div>
                <p class="mb-3.5 text-[13px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ __('On the item') }}</p>
                <h2 class="text-[28px] leading-[1.12] font-semibold tracking-[-1px] text-ink sm:text-4xl lg:text-[38px] lg:tracking-[-1.1px]">{{ __('More than one photo, when one photo is obviously not enough.') }}</h2>
                <p class="mt-5 text-[16px] leading-relaxed text-pretty text-muted">
                    {{ __('Attach several images to an item, choose the cover that represents it best, and reorder the gallery until the first thing you see is the right thing.') }}
                </p>
                <div class="mt-6 flex flex-wrap gap-2">
                    @foreach ($galleryChips as $chip)
                        <span class="rounded-full border border-hairline bg-canvas px-3.5 py-1.5 text-[14px] font-medium text-body">{{ $chip }}</span>
                    @endforeach
                </div>
            </div>

            <div class="rounded-2xl border border-hairline bg-canvas p-5 shadow-[0_24px_60px_rgba(17,17,17,0.08),0_4px_12px_rgba(17,17,17,0.04)]">
                <div class="mb-4 flex items-center gap-x-2.5">
                    <span class="h-[9px] w-[9px] rounded-full bg-badge-violet"></span>
                    <span class="text-[14px] font-semibold text-ink">The Dark Side of the Moon</span>
                    <span class="ml-auto rounded-full bg-card px-2.5 py-1 text-[11px] font-medium text-muted">{{ __(':count photos', ['count' => 4]) }}</span>
                </div>
                @php($main = $tile(285))
                <div class="relative aspect-[16/10] overflow-hidden rounded-xl border border-hairline" style="background:{{ $main['bg'] }};">
                    <span class="absolute top-2.5 left-2.5 flex h-6 items-center gap-x-1.5 rounded-full bg-[rgba(17,17,17,0.82)] px-2.5 text-[11px] font-semibold text-white">
                        @svg('lucide-star', 'size-3 fill-[#fbbf24] text-[#fbbf24]')
                        {{ __('Cover') }}
                    </span>
                    <span class="absolute right-0 bottom-2.5 left-0 text-center font-mono text-[11px] opacity-90" style="color:{{ $main['icon'] }};">dsotm_cover.jpg · 2200 × 2200</span>
                </div>
                <div class="mt-3.5 mb-2 flex items-center justify-between">
                    <span class="text-[12px] font-semibold tracking-[0.5px] text-muted-soft uppercase">{{ __('Drag to reorder') }}</span>
                    <span class="font-mono text-[11px] text-muted-soft">{{ __('cover = first') }}</span>
                </div>
                <div class="grid grid-cols-4 gap-2.5">
                    @foreach ($galleryThumbs as $thumb)
                        @php($tt = $tile($thumb['hue']))
                        <div class="relative aspect-square rounded-[9px] border-[1.5px] {{ $thumb['cover'] ? 'border-ink' : 'border-hairline' }}" style="background:{{ $tt['bg'] }};">
                            @if ($thumb['cover'])
                                <span class="absolute top-1.5 right-1.5 flex h-[18px] w-[18px] items-center justify-center rounded-full bg-[rgba(17,17,17,0.85)]">@svg('lucide-star', 'size-2.5 fill-[#fbbf24] text-[#fbbf24]')</span>
                            @endif
                            <span class="absolute right-0 bottom-1 left-0 text-center font-mono text-[8px]" style="color:{{ $tt['icon'] }};">{{ $thumb['tag'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-[1200px] px-5 pt-24 sm:px-8 sm:pt-28">
        <div class="mb-10 max-w-[640px]">
            <p class="mb-3.5 text-[13px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ __('Grid view') }}</p>
            <h2 class="text-[28px] leading-[1.12] font-semibold tracking-[-1px] text-ink sm:text-4xl lg:text-[38px] lg:tracking-[-1.1px]">{{ __('Browse what you actually recognize.') }}</h2>
            <p class="mt-5 text-[16px] leading-relaxed text-pretty text-muted">
                {{ __('Use the collection grid when the cover, label, or box art is faster than the title. It is often faster. We are not going to pretend otherwise.') }}
            </p>
        </div>
        <div class="rounded-2xl border border-hairline bg-canvas p-5 shadow-[0_4px_12px_rgba(17,17,17,0.04)] sm:p-6">
            <div class="grid grid-cols-4 gap-3 sm:grid-cols-6 lg:grid-cols-8 lg:gap-3.5">
                @foreach ($browseCovers as [$hue, $ratio, $kind])
                    @php($t = $tile($hue))
                    <div class="relative rounded-[9px] border border-hairline" style="aspect-ratio:{{ $ratio }};background:{{ $t['bg'] }};">
                        <span class="absolute right-0 bottom-1.5 left-0 text-center font-mono text-[8px] opacity-85" style="color:{{ $t['icon'] }};">{{ $kind }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-[1200px] px-5 pt-24 sm:px-8 sm:pt-28">
        <div class="mb-10 max-w-[640px]">
            <p class="mb-3.5 text-[13px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ __('Photo library') }}</p>
            <h2 class="text-[28px] leading-[1.12] font-semibold tracking-[-1px] text-ink sm:text-4xl lg:text-[38px] lg:tracking-[-1.1px]">{{ __('One library for every image.') }}</h2>
            <p class="mt-5 text-[16px] leading-relaxed text-pretty text-muted">
                {{ __('Every item photo also lives in an account-wide photo library. Search by file or item name, filter covers from extras, sort by size, and find the images that need a little attention.') }}
            </p>
        </div>

        <div class="overflow-hidden rounded-2xl border border-hairline bg-canvas shadow-[0_24px_60px_rgba(17,17,17,0.08),0_4px_12px_rgba(17,17,17,0.04)]">
            <div class="flex flex-wrap items-center gap-3 border-b border-hairline-soft px-5 py-4 sm:px-6">
                <div class="flex gap-1 rounded-full border border-hairline bg-card p-[3px]">
                    @foreach ($libFilters as $filter)
                        <span @class([
                            'flex h-[30px] items-center gap-x-1.5 rounded-full px-3.5 text-[13px] font-semibold',
                            'bg-canvas text-ink shadow-[0_1px_3px_rgba(0,0,0,0.10)]' => $filter['on'],
                            'text-muted' => ! $filter['on'],
                        ])>
                            {{ $filter['label'] }}
                            <span class="text-[11px] opacity-70">{{ $filter['count'] }}</span>
                        </span>
                    @endforeach
                </div>
                <div class="flex-1"></div>
                <div class="relative hidden items-center sm:flex">
                    @svg('lucide-search', 'size-3.5 text-muted-soft absolute left-3')
                    <span class="flex h-[38px] w-[230px] items-center rounded-lg border border-hairline bg-canvas pr-3.5 pl-9 text-[14px] text-muted-soft">{{ __('Search by file or item…') }}</span>
                </div>
                <span class="flex h-[38px] items-center gap-x-2 rounded-lg border border-hairline bg-canvas px-3 text-[13px] font-medium text-ink">
                    {{ __('Largest file') }}
                    @svg('lucide-chevron-down', 'size-3.5 text-muted-soft')
                </span>
            </div>
            <div class="p-5 sm:p-6">
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-4 lg:grid-cols-6">
                    @foreach ($libPhotos as $photo)
                        @php($t = $tile($photo['hue']))
                        <div class="overflow-hidden rounded-xl border-[1.5px] {{ $photo['cover'] ? 'border-ink' : 'border-hairline' }} bg-canvas">
                            <div class="relative aspect-[4/3]" style="background:{{ $t['bg'] }};">
                                @if ($photo['cover'])
                                    <span class="absolute top-1.5 right-1.5 flex h-5 items-center gap-x-1 rounded-full bg-[rgba(17,17,17,0.82)] px-2 text-[9px] font-semibold text-white">
                                        @svg('lucide-star', 'size-2 fill-[#fbbf24] text-[#fbbf24]')
                                        {{ __('Cover') }}
                                    </span>
                                @endif
                                <span class="absolute right-0 bottom-1.5 left-0 text-center font-mono text-[9px] opacity-85" style="color:{{ $t['icon'] }};">{{ $photo['dims'] }}</span>
                            </div>
                            <div class="px-2.5 pt-2.5 pb-3">
                                <p class="truncate font-mono text-[11px] text-ink">{{ $photo['fn'] }}</p>
                                <div class="mt-1.5 flex items-center gap-x-1.5">
                                    @if ($photo['item'])
                                        <span class="h-1.5 w-1.5 shrink-0 rounded-full" style="background:{{ $dot($photo['hue']) }};"></span>
                                        <span class="truncate text-[11px] text-muted">{{ $photo['item'] }}</span>
                                    @else
                                        <span class="rounded-full bg-card px-2 py-[2px] text-[10px] font-semibold text-warning">{{ __('Unassigned') }}</span>
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
        <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-2 lg:gap-14">
            <div>
                <p class="mb-3.5 text-[13px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ __('Photo details') }}</p>
                <h2 class="text-[28px] leading-[1.12] font-semibold tracking-[-1px] text-ink sm:text-4xl lg:text-[38px] lg:tracking-[-1.1px]">{{ __('The details are there when you need them.') }}</h2>
                <p class="mt-5 text-[16px] leading-relaxed text-pretty text-muted">
                    {{ __('See file size, format, dimensions, upload date, uploader, cover status, and the item a photo belongs to. Useful for the careful collector and the person wondering why storage disappeared.') }}
                </p>
                <div class="mt-8 flex">
                    <a href="{{ route('register') }}" class="inline-flex h-12 items-center justify-center gap-x-2.5 rounded-md bg-primary px-6 text-[15px] font-semibold text-on-primary transition-opacity hover:opacity-90">{{ __('Open the photo library') }} @svg('lucide-arrow-right', 'size-4')</a>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-hairline bg-canvas shadow-[0_24px_60px_rgba(17,17,17,0.08),0_4px_12px_rgba(17,17,17,0.04)]">
                @php($dp = $tile(150))
                <div class="relative aspect-[16/9] border-b border-hairline-soft" style="background:{{ $dp['bg'] }};">
                    <span class="absolute top-3 left-3 flex h-6 items-center gap-x-1.5 rounded-full bg-[rgba(17,17,17,0.82)] px-2.5 text-[11px] font-semibold text-white">
                        @svg('lucide-star', 'size-3 fill-[#fbbf24] text-[#fbbf24]')
                        {{ __('Cover') }}
                    </span>
                </div>
                <div class="px-5 py-4.5">
                    <p class="mb-4 font-mono text-[13px] font-medium text-ink">af15_cover_9-4.jpg</p>
                    <div class="flex flex-col">
                        @foreach ($detailRows as $row)
                            <div class="flex items-center justify-between border-b border-hairline-soft py-2.5 last:border-b-0">
                                <span class="text-[13px] text-muted">{{ $row['label'] }}</span>
                                <span class="flex items-center gap-x-2 text-[13px] font-medium text-ink">
                                    @if ($row['dot'] ?? false)
                                        <span class="h-[7px] w-[7px] rounded-full bg-success"></span>
                                    @endif
                                    {{ $row['value'] }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
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
