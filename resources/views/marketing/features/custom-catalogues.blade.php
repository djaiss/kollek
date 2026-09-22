{{-- The "Custom catalogues" feature page of the marketing site. --}}

@php
    // The six custom field kinds. The short code (abc, 123, …) is a UI token, so it stays
    // literal; only the human label is translated.
    $fieldKinds = [
        ['k' => 'abc', 'label' => __('Text')],
        ['k' => '123', 'label' => __('Number')],
        ['k' => 'date', 'label' => __('Date')],
        ['k' => 'y/n', 'label' => __('Yes / no')],
        ['k' => 'list', 'label' => __('Select')],
        ['k' => '1–5', 'label' => __('Rating')],
    ];

    // The twelve ready-made types, with a swatch (fixed pastel hues that do not invert) and a
    // field count. Names are translatable category labels.
    $readyTypes = [
        ['name' => __('Comics'), 'fields' => 9, 'a' => '#fb923c', 'b' => '#fdba74', 'selected' => true],
        ['name' => __('Trading cards'), 'fields' => 8, 'a' => '#34d399', 'b' => '#6ee7b7'],
        ['name' => __('Vinyl'), 'fields' => 10, 'a' => '#8b5cf6', 'b' => '#c4b5fd'],
        ['name' => __('Books'), 'fields' => 7, 'a' => '#3b82f6', 'b' => '#93c5fd'],
        ['name' => __('Watches'), 'fields' => 8, 'a' => '#64748b', 'b' => '#cbd5e1'],
        ['name' => __('Wine'), 'fields' => 9, 'a' => '#ec4899', 'b' => '#f9a8d4'],
        ['name' => __('Coins'), 'fields' => 8, 'a' => '#f59e0b', 'b' => '#fcd34d'],
        ['name' => __('Video games'), 'fields' => 7, 'a' => '#0ea5e9', 'b' => '#7dd3fc'],
        ['name' => __('CDs'), 'fields' => 6, 'a' => '#14b8a6', 'b' => '#5eead4'],
        ['name' => __('Movies'), 'fields' => 6, 'a' => '#f43f5e', 'b' => '#fda4af'],
        ['name' => __('Art'), 'fields' => 7, 'a' => '#a855f7', 'b' => '#d8b4fe'],
        ['name' => __('Stamps'), 'fields' => 6, 'a' => '#10b981', 'b' => '#6ee7b7'],
    ];

    // The featured type being edited: Comics, with its six fields.
    $editorFields = [
        ['name' => __('Issue number'), 'kind' => '123', 'sample' => '#300'],
        ['name' => __('Publisher'), 'kind' => 'abc', 'sample' => 'Marvel'],
        ['name' => __('Cover date'), 'kind' => 'date', 'sample' => __('May 1988')],
        ['name' => __('Grade'), 'kind' => 'list', 'sample' => 'CGC 9.8'],
        ['name' => __('Key issue'), 'kind' => 'y/n', 'sample' => __('Yes')],
        ['name' => __('Signed'), 'kind' => 'y/n', 'sample' => __('No')],
    ];

    // The form layout: three groups, the last collapsed.
    $formGroups = [
        ['name' => __('Identification'), 'meta' => __(':count fields', ['count' => 3]), 'open' => true, 'fields' => [
            ['name' => __('Issue number'), 'kind' => '123'],
            ['name' => __('Publisher'), 'kind' => 'abc'],
            ['name' => __('Cover date'), 'kind' => 'date'],
        ]],
        ['name' => __('Condition & value'), 'meta' => __(':count fields', ['count' => 3]), 'open' => true, 'fields' => [
            ['name' => __('Grade'), 'kind' => 'list'],
            ['name' => __('Key issue'), 'kind' => 'y/n'],
            ['name' => __('Signed'), 'kind' => 'y/n'],
        ]],
        ['name' => __('Notes & extras'), 'meta' => __(':count fields · collapsed', ['count' => 2]), 'open' => false, 'fields' => []],
    ];

    // The proof: the same item page, two vocabularies. Field labels translate; sample values
    // (proper nouns, grades, dates) stay literal, except Yes/No.
    $proofForms = [
        ['type' => __('Comics'), 'title' => 'Amazing Spider-Man #300', 'a' => '#fb923c', 'b' => '#fdba74', 'rows' => [
            ['kind' => '123', 'label' => __('Issue number'), 'value' => '#300'],
            ['kind' => 'abc', 'label' => __('Publisher'), 'value' => 'Marvel'],
            ['kind' => 'list', 'label' => __('Grade'), 'value' => 'CGC 9.8'],
            ['kind' => 'y/n', 'label' => __('Key issue'), 'value' => __('Yes')],
            ['kind' => 'abc', 'label' => __('Writer'), 'value' => 'D. Michelinie'],
            ['kind' => 'y/n', 'label' => __('Signed'), 'value' => __('No')],
        ]],
        ['type' => __('Wine'), 'title' => 'Barolo Monfortino 2015', 'a' => '#ec4899', 'b' => '#f9a8d4', 'rows' => [
            ['kind' => '123', 'label' => __('Vintage'), 'value' => '2015'],
            ['kind' => 'abc', 'label' => __('Region'), 'value' => 'Piedmont'],
            ['kind' => 'abc', 'label' => __('Winery'), 'value' => 'G. Conterno'],
            ['kind' => 'date', 'label' => __('Drink by'), 'value' => '2035'],
            ['kind' => '123', 'label' => __('Bottles'), 'value' => '6'],
            ['kind' => '1–5', 'label' => __('Rating'), 'value' => '★★★★☆'],
        ]],
    ];

    // Reusable types across the account.
    $reuseTypes = [
        ['name' => __('Vinyl'), 'meta' => __('Used in :count collections · :fields fields', ['count' => 12, 'fields' => 10]), 'a' => '#8b5cf6', 'b' => '#c4b5fd'],
        ['name' => __('Comics'), 'meta' => __('Used in :count collections · :fields fields', ['count' => 8, 'fields' => 9]), 'a' => '#fb923c', 'b' => '#fdba74'],
        ['name' => __('Wine'), 'meta' => __('Used in :count collections · :fields fields', ['count' => 3, 'fields' => 9]), 'a' => '#ec4899', 'b' => '#f9a8d4'],
        ['name' => __('Books'), 'meta' => __('Used in :count collections · :fields fields', ['count' => 5, 'fields' => 7]), 'a' => '#3b82f6', 'b' => '#93c5fd'],
    ];

    // The candid two column footer. Caveats first so they stack above the pitch on mobile.
    $notFor = [
        ['head' => __('A fixed, genre-specific database already contains every field you need.'), 'body' => __('You never need to add a field, option, or grouping.')],
        ['head' => __('You want a massive public reference database maintained for you.'), 'body' => null],
    ];
    $chooseWhen = [
        ['head' => __('Your collection has its own vocabulary.'), 'body' => __(':name lets the catalogue learn it.', ['name' => config('app.name')])],
        ['head' => __('You want reusable types, custom fields, and forms that make sense to your hobby.'), 'body' => null],
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
        <p class="mx-auto mb-5 max-w-[720px] text-[12px] leading-[1.5] font-semibold tracking-[1px] text-muted-soft uppercase">
            {{ __('For people who have ever said “it’s basically the same thing”') }}
        </p>
        <h1 class="mx-auto max-w-[820px] text-[32px] leading-[1.08] font-semibold tracking-[-1px] text-balance text-ink sm:text-5xl sm:tracking-[-1.5px] lg:text-[60px] lg:leading-[1.05] lg:tracking-[-2px]">
            {{ __('Your hobby has jargon. We came prepared.') }}
        </h1>
        <p class="mx-auto mt-6 max-w-[640px] text-[17px] leading-relaxed text-pretty text-muted sm:text-[19px]">
            {{ __('A watch is not just a thing with a name. A comic is not just a book. A bottle is not just a drink. Start with a collection type that gets close, then teach :name the details that actually matter to you.', ['name' => config('app.name')]) }}
        </p>
        <div class="mt-9 flex flex-col justify-center gap-3 sm:flex-row">
            <a href="{{ route('register') }}" class="flex h-12 items-center justify-center rounded-md bg-primary px-6 text-[15px] font-semibold text-on-primary transition-opacity hover:opacity-90">{{ __('Teach :name your hobby', ['name' => config('app.name')]) }}</a>
            <a href="#fields" class="flex h-12 items-center justify-center gap-x-2 rounded-md border border-hairline bg-canvas px-5.5 text-[15px] font-semibold text-ink transition-colors hover:bg-sidebar">
                {{ __('See a type in action') }}
                @svg('lucide-arrow-down', 'size-4')
            </a>
        </div>
    </section>

    <section id="types" class="mx-auto mt-14 max-w-[1060px] scroll-mt-24 px-5 sm:px-8">
        <div class="overflow-hidden rounded-xl border border-hairline bg-canvas shadow-[0_24px_60px_rgba(17,17,17,0.10),0_4px_12px_rgba(17,17,17,0.05)]">
            <div class="flex h-11 items-center gap-x-2 border-b border-hairline-soft bg-sidebar px-4">
                <span class="h-[11px] w-[11px] rounded-full bg-hairline"></span>
                <span class="h-[11px] w-[11px] rounded-full bg-hairline"></span>
                <span class="h-[11px] w-[11px] rounded-full bg-hairline"></span>
                <div class="ml-3 hidden h-[26px] max-w-[360px] flex-1 items-center rounded-sm border border-hairline bg-input px-2.5 text-xs text-muted-soft sm:flex">
                    {{ Str::lower(config('app.name')) }}.app/collections/new
                </div>
            </div>
            <div class="p-6 sm:p-7">
                <div class="mb-5 flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <p class="text-[20px] font-semibold tracking-[-0.4px] text-ink">{{ __('Choose a collection type') }}</p>
                        <p class="mt-0.5 text-[13px] text-muted">{{ __('Start from a ready-made type, then make it yours.') }}</p>
                    </div>
                    <span class="flex items-center gap-x-2 rounded-full bg-card px-3 py-1.5 text-[12px] font-medium text-body">
                        <span class="h-1.5 w-1.5 rounded-full bg-success"></span>{{ __(':count built-in types · all editable', ['count' => 12]) }}
                    </span>
                </div>
                <div class="grid grid-cols-2 gap-3.5 sm:grid-cols-3 lg:grid-cols-4">
                    @foreach ($readyTypes as $type)
                        @php($selected = $type['selected'] ?? false)
                        <div @class([
                            'relative flex flex-col gap-3 rounded-xl border-[1.5px] p-3.5',
                            'border-ink bg-sidebar' => $selected,
                            'border-hairline bg-canvas' => ! $selected,
                        ])>
                            @if ($selected)
                                <span class="absolute top-3 right-3 flex h-[18px] w-[18px] items-center justify-center rounded-full bg-ink">
                                    @svg('lucide-check', 'size-2.5 text-on-primary')
                                </span>
                            @endif
                            <span class="h-13 rounded-lg" style="background:repeating-linear-gradient(135deg,{{ $type['a'] }} 0px,{{ $type['a'] }} 8px,{{ $type['b'] }} 8px,{{ $type['b'] }} 16px);"></span>
                            <div class="min-w-0">
                                <p class="truncate text-[14px] font-semibold tracking-[-0.2px] text-ink">{{ $type['name'] }}</p>
                                <p class="mt-0.5 text-[12px] text-muted">{{ __(':count fields', ['count' => $type['fields']]) }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-[760px] px-5 pt-24 text-center sm:px-8 sm:pt-28">
        <h2 class="text-[28px] leading-[1.12] font-semibold tracking-[-1px] text-balance text-ink sm:text-4xl lg:text-[40px] lg:tracking-[-1.2px]">{{ __('Start with a head start.') }}</h2>
        <p class="mx-auto mt-5 text-[17px] leading-relaxed text-pretty text-muted">
            {{ __(':name arrives with ready-made types for comics, trading cards, vinyl, books, watches, wine, coins, video games, and more. Pick one, make it yours, and get on with the satisfying bit: cataloguing the collection.', ['name' => config('app.name')]) }}
        </p>
    </section>

    <section id="fields" class="mx-auto max-w-[1200px] scroll-mt-24 px-5 pt-24 sm:px-8 sm:pt-28">
        <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-2 lg:gap-14">
            <div>
                <p class="mb-3.5 text-[13px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ __('Add the details worth keeping') }}</p>
                <h2 class="text-[28px] leading-[1.14] font-semibold tracking-[-1px] text-ink sm:text-4xl lg:text-[38px]">{{ __('The fields your hobby actually uses.') }}</h2>
                <p class="mt-5 mb-6 text-[16px] leading-relaxed text-pretty text-muted">
                    {{ __('Issue number. Pressing. Publisher. Vintage. Signed? First edition? Where it came from? Add the fields you need, choose the right kind of answer, and stop making one generic notes field carry the emotional weight of your entire hobby.') }}
                </p>
                <div class="flex flex-wrap gap-2">
                    @foreach ($fieldKinds as $kind)
                        <span class="inline-flex items-center gap-x-2 rounded-full bg-card py-1.5 pr-3 pl-2 text-[13px] font-medium text-body">
                            <span class="rounded-[5px] border border-hairline bg-canvas px-1.5 py-0.5 font-mono text-[10px] text-muted">{{ $kind['k'] }}</span>{{ $kind['label'] }}
                        </span>
                    @endforeach
                </div>
            </div>
            <div class="overflow-hidden rounded-2xl border border-hairline bg-canvas shadow-[0_4px_14px_rgba(17,17,17,0.05)]">
                <div class="flex items-center justify-between border-b border-hairline-soft bg-sidebar px-5 py-4">
                    <div class="flex items-center gap-x-2.5">
                        <span class="h-[22px] w-[22px] rounded-md" style="background:repeating-linear-gradient(135deg,#fb923c 0px,#fb923c 5px,#fdba74 5px,#fdba74 10px);"></span>
                        <span class="text-[14px] font-semibold text-ink">{{ __('Edit type · :name', ['name' => __('Comics')]) }}</span>
                    </div>
                    <span class="rounded-full bg-card px-2.5 py-1 text-[11px] font-semibold text-body">{{ __('Custom') }}</span>
                </div>
                <div class="px-5 pt-1">
                    @foreach ($editorFields as $field)
                        <div class="flex items-center gap-x-3 border-b border-hairline-soft py-3">
                            @svg('lucide-grip-vertical', 'size-3.5 shrink-0 text-muted-soft')
                            <span class="flex h-[26px] w-[30px] shrink-0 items-center justify-center rounded-md bg-card font-mono text-[10px] text-muted">{{ $field['kind'] }}</span>
                            <span class="min-w-0 flex-1 truncate text-[14px] font-medium text-ink">{{ $field['name'] }}</span>
                            <span class="shrink-0 font-mono text-[12px] text-muted-soft">{{ $field['sample'] }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="m-4 rounded-xl border border-hairline bg-sidebar p-3.5 shadow-[0_8px_20px_rgba(17,17,17,0.06)]">
                    <div class="mb-2.5 flex items-center gap-x-2 text-[12px] font-semibold text-body">
                        <span class="flex h-4 w-4 items-center justify-center rounded-[5px] bg-ink">@svg('lucide-plus', 'size-2.5 text-on-primary')</span>
                        {{ __('New field (pick a kind)') }}
                    </div>
                    <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                        @foreach ($fieldKinds as $kind)
                            <div class="flex items-center gap-x-2 rounded-lg border border-hairline bg-canvas px-2.5 py-2">
                                <span class="rounded-[4px] bg-card px-1.5 py-0.5 font-mono text-[10px] text-muted">{{ $kind['k'] }}</span>
                                <span class="truncate text-[12.5px] font-medium text-ink">{{ $kind['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="layout" class="mx-auto max-w-[1200px] px-5 pt-24 sm:px-8 sm:pt-28">
        <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-2 lg:gap-14">
            <div class="order-2 overflow-hidden rounded-2xl border border-hairline bg-canvas shadow-[0_4px_14px_rgba(17,17,17,0.05)] lg:order-1">
                <div class="flex items-center justify-between border-b border-hairline-soft bg-sidebar px-5 py-4">
                    <span class="text-[14px] font-semibold text-ink">{{ __(':name · form layout', ['name' => __('Comics')]) }}</span>
                    <span class="font-mono text-[11px] font-medium text-muted">{{ __('drag to arrange') }}</span>
                </div>
                <div class="flex flex-col gap-3 p-4.5">
                    @foreach ($formGroups as $group)
                        <div class="overflow-hidden rounded-xl border border-hairline">
                            <div class="flex items-center justify-between bg-card px-3 py-2.5">
                                <div class="flex items-center gap-x-2.5">
                                    @svg('lucide-grip-vertical', 'size-3.5 text-muted-soft')
                                    <span class="text-[13px] font-semibold text-ink">{{ $group['name'] }}</span>
                                    <span class="text-[11px] text-muted-soft">{{ $group['meta'] }}</span>
                                </div>
                                @svg('lucide-chevron-'.($group['open'] ? 'down' : 'right'), 'size-4 text-muted')
                            </div>
                            @if ($group['open'])
                                <div class="px-3">
                                    @foreach ($group['fields'] as $field)
                                        <div class="flex items-center gap-x-2.5 border-b border-hairline-soft py-2.5 last:border-b-0">
                                            @svg('lucide-grip-vertical', 'size-3 shrink-0 text-muted-soft')
                                            <span class="flex-1 text-[13.5px] font-medium text-body">{{ $field['name'] }}</span>
                                            <span class="rounded-[4px] bg-card px-1.5 py-0.5 font-mono text-[10px] text-muted">{{ $field['kind'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="order-1 lg:order-2">
                <p class="mb-3.5 text-[13px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ __('Make the form make sense') }}</p>
                <h2 class="text-[28px] leading-[1.14] font-semibold tracking-[-1px] text-ink sm:text-4xl lg:text-[38px]">{{ __('Group it, order it, hide the rest.') }}</h2>
                <p class="mt-5 text-[16px] leading-relaxed text-pretty text-muted">
                    {{ __('Put related details together. Keep the important stuff near the top. Move the rest out of the way until you need it. It is your collection; the form should behave accordingly.') }}
                </p>
            </div>
        </div>
    </section>

    <section id="proof" class="mx-auto max-w-[1200px] px-5 pt-24 sm:px-8 sm:pt-28">
        <div class="mx-auto mb-11 max-w-[640px] text-center">
            <p class="mb-3.5 text-[13px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ __('One app, two vocabularies') }}</p>
            <h2 class="text-[28px] leading-[1.12] font-semibold tracking-[-1px] text-balance text-ink sm:text-4xl lg:text-[40px] lg:tracking-[-1.2px]">{{ __('The same item page, speaking your hobby.') }}</h2>
            <p class="mx-auto mt-5 text-[17px] leading-relaxed text-pretty text-muted">{{ __('Same :name. Two types. Neither pretends to be the other.', ['name' => config('app.name')]) }}</p>
        </div>
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            @foreach ($proofForms as $form)
                <div class="overflow-hidden rounded-2xl border border-hairline bg-canvas shadow-[0_4px_14px_rgba(17,17,17,0.05)]">
                    <div class="flex items-center gap-3.5 border-b border-hairline-soft px-5 py-4.5">
                        <span class="h-[60px] w-[46px] shrink-0 rounded-lg" style="background:repeating-linear-gradient(135deg,{{ $form['a'] }} 0px,{{ $form['a'] }} 8px,{{ $form['b'] }} 8px,{{ $form['b'] }} 16px);"></span>
                        <div class="min-w-0">
                            <span class="mb-1.5 inline-flex items-center gap-x-1.5 rounded-full bg-card px-2 py-[3px] text-[10px] font-semibold tracking-[0.4px] text-muted uppercase">
                                <span class="h-[5px] w-[5px] rounded-full" style="background:{{ $form['a'] }};"></span>{{ $form['type'] }}
                            </span>
                            <p class="truncate text-[16px] font-semibold tracking-[-0.3px] text-ink">{{ $form['title'] }}</p>
                        </div>
                    </div>
                    <div class="px-5 pt-1 pb-4">
                        @foreach ($form['rows'] as $row)
                            <div class="flex items-center justify-between gap-3.5 border-b border-hairline-soft py-3 last:border-b-0">
                                <div class="flex min-w-0 items-center gap-x-2.5">
                                    <span class="shrink-0 rounded-[4px] bg-card px-1.5 py-0.5 font-mono text-[10px] text-muted">{{ $row['kind'] }}</span>
                                    <span class="truncate text-[13.5px] text-muted">{{ $row['label'] }}</span>
                                </div>
                                <span class="truncate text-right text-[13.5px] font-semibold text-ink">{{ $row['value'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <section id="reuse" class="mx-auto max-w-[1200px] px-5 pt-24 sm:px-8 sm:pt-28">
        <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-2 lg:gap-14">
            <div>
                <p class="mb-3.5 text-[13px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ __('Reuse the good work') }}</p>
                <h2 class="text-[28px] leading-[1.14] font-semibold tracking-[-1px] text-ink sm:text-4xl lg:text-[38px]">{{ __('Build a type once. Use it everywhere.') }}</h2>
                <p class="mt-5 mb-6 text-[16px] leading-relaxed text-pretty text-muted">
                    {{ __('Build a type once and use it across the account. A carefully tuned “Vinyl” type should not have to be rebuilt every time another shelf becomes a collection.') }}
                </p>
                <a href="{{ route('register') }}" class="inline-flex h-12 items-center justify-center gap-x-2.5 rounded-md bg-primary px-5.5 text-[15px] font-semibold text-on-primary transition-opacity hover:opacity-90">{{ __('Set up a collection type') }} @svg('lucide-arrow-right', 'size-4')</a>
            </div>
            <div class="overflow-hidden rounded-2xl border border-hairline bg-canvas shadow-[0_4px_14px_rgba(17,17,17,0.05)]">
                <div class="flex items-center justify-between border-b border-hairline-soft bg-sidebar px-5 py-4">
                    <span class="text-[14px] font-semibold text-ink">{{ __('Settings · Collection types') }}</span>
                    <span class="rounded-full bg-card px-2.5 py-1 font-mono text-[11px] font-semibold text-body">{{ __('JSON import / export') }}</span>
                </div>
                <div class="px-5 pt-1">
                    @foreach ($reuseTypes as $type)
                        <div class="flex items-center gap-x-3.5 border-b border-hairline-soft py-3.5 last:border-b-0">
                            <span class="h-[30px] w-[30px] shrink-0 rounded-lg" style="background:repeating-linear-gradient(135deg,{{ $type['a'] }} 0px,{{ $type['a'] }} 6px,{{ $type['b'] }} 6px,{{ $type['b'] }} 12px);"></span>
                            <div class="min-w-0 flex-1">
                                <p class="text-[14px] font-semibold text-ink">{{ $type['name'] }}</p>
                                <p class="mt-0.5 text-[12px] text-muted">{{ $type['meta'] }}</p>
                            </div>
                            <span class="relative h-[22px] w-[38px] shrink-0 rounded-full bg-ink">
                                <span class="absolute top-0.5 left-[18px] h-[18px] w-[18px] rounded-full bg-white shadow-[0_1px_2px_rgba(0,0,0,0.2)]"></span>
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-[1200px] px-5 pt-24 sm:px-8 sm:pt-28">
        <div class="rounded-3xl bg-[#101010] px-6 py-16 text-center sm:px-12 sm:py-[72px]">
            <h2 class="mx-auto max-w-[620px] text-[26px] leading-[1.12] font-semibold tracking-[-1px] text-balance text-white sm:text-[34px] lg:text-[40px] lg:tracking-[-1.2px]">
                {{ __('Teach :name your hobby.', ['name' => config('app.name')]) }}
            </h2>
            <p class="mx-auto mt-5 max-w-[480px] text-[16.5px] leading-relaxed text-pretty text-[#a1a1aa]">
                {{ __('Pick a ready-made type, add the fields that matter, and catalogue your collection the way it deserves.') }}
            </p>
            <div class="mt-8 flex justify-center">
                <a href="{{ route('register') }}" class="flex h-[50px] items-center justify-center rounded-md bg-white px-6.5 text-[15px] font-semibold text-[#111111] transition-colors hover:bg-[#e5e7eb]">{{ __('Set up a collection type') }}</a>
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
