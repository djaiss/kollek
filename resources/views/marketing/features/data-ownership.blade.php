{{-- The "Data ownership" feature page of the marketing site. --}}

@php
    $appTiers = [
        ['name' => __('Web'), 'sub' => __('Serves the UI and API'), 'dot' => '#3b82f6', 'square' => true],
        ['name' => __('Queue worker'), 'sub' => __('Background jobs'), 'dot' => '#8b5cf6', 'square' => true],
        ['name' => __('Scheduler'), 'sub' => __('Recurring tasks'), 'dot' => '#fb923c', 'square' => false],
    ];
    $stateTiers = [
        ['name' => __('Database'), 'sub' => __('Encrypted at rest with your application key'), 'dot' => '#34d399', 'square' => true],
        ['name' => __('Persistent storage'), 'sub' => __('Uploaded photos & documents · local or S3'), 'dot' => '#0ea5e9', 'square' => true],
    ];

    $storageChips = [__('Local disk'), __('S3-compatible'), __('Your own volume'), __('MIT licensed')];

    // A condensed but faithful docker-compose.yml. Code stays literal.
    $composeLines = [
        ['t' => 'services:', 'c' => '#e5e7eb'],
        ['t' => '  app:', 'c' => '#93c5fd'],
        ['t' => '    image: kollek:latest', 'c' => '#a1a1aa'],
        ['t' => '    ports: ["${APP_PORT:-8000}:80"]', 'c' => '#a1a1aa'],
        ['t' => '    environment:', 'c' => '#e5e7eb'],
        ['t' => '      APP_KEY: ${APP_KEY}', 'c' => '#34d399'],
        ['t' => '    volumes: [storage-data:/var/www/html/storage]', 'c' => '#a1a1aa'],
        ['t' => '  mysql:', 'c' => '#93c5fd'],
        ['t' => '    volumes: [db-data:/var/lib/mysql]', 'c' => '#a1a1aa'],
        ['t' => '  # your data. your paths. your call.', 'c' => '#6b7280'],
    ];

    // Encryption at rest: the app shows plain values; the database columns hold ciphertext.
    $plainFields = [
        ['label' => __('Estimated value'), 'value' => '$6,400'],
        ['label' => __('Location'), 'value' => __('Home safe · Shelf 3')],
        ['label' => __('Purchase note'), 'value' => __('Bought at auction, 2019')],
        ['label' => __('Condition'), 'value' => 'CGC 9.8'],
    ];
    $cipherFields = [
        ['col' => 'value', 'cipher' => 'q7Xk9…vT2aZ1pLc8Hh=='],
        ['col' => 'location', 'cipher' => 'M2fD…uR0bQ9sW4nEy=='],
        ['col' => 'note', 'cipher' => 'aP5…Lz8Kd3Xmt1oJ7g=='],
        ['col' => 'condition', 'cipher' => 'Yh1…Ns6Ce0Wq2Bv9d=='],
    ];

    // The three things an operator backs up. Tags are the real volume/env names.
    $backupItems = [
        ['title' => __('The database'), 'desc' => __('Every collection, item, custom field, and history entry: the whole catalogue.'), 'tag' => 'db-data', 'dot' => '#34d399', 'square' => true],
        ['title' => __('Photos & documents'), 'desc' => __('Uploaded files from local disk or S3-compatible storage.'), 'tag' => 'storage-data', 'dot' => '#0ea5e9', 'square' => true],
        ['title' => __('The application key'), 'desc' => __('Decrypts the encrypted fields. Without it, the backup is gibberish too.'), 'tag' => 'APP_KEY', 'dot' => '#ec4899', 'square' => false],
    ];

    $chargePoints = [
        ['title' => __('You own upgrades'), 'desc' => __('Pull a new image on your schedule, not ours.'), 'dot' => '#3b82f6'],
        ['title' => __('You own backups'), 'desc' => __('Real copies of the database, storage, and key.'), 'dot' => '#8b5cf6'],
        ['title' => __('You own the key'), 'desc' => __('The application key never leaves your instance.'), 'dot' => '#34d399'],
    ];

    // The candid two column footer (three rows each). Caveats first so they stack above the
    // pitch on mobile. These directly state the claim boundary.
    $notFor = [
        __('You do not want any responsibility for hosting, upgrades, or backups.'),
        __('You need end-to-end encryption where the operator cannot access application data.'),
        __('You expect an automated in-app backup button.'),
    ];
    $chooseWhen = [
        __('You want to decide where the app, data, photos, and documents live.'),
        __('You want sensitive fields encrypted at rest and control over the encryption key.'),
        __('You are willing to make real backups of the database, storage, and application key.'),
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
        <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-2 lg:gap-16">
            <div>
                <p class="text-[12px] leading-[1.5] font-semibold tracking-[1px] text-muted-soft uppercase">{{ __('Your collection. Your server. Your slightly over-specified NAS.') }}</p>
                <h1 class="mt-5 text-[32px] leading-[1.08] font-semibold tracking-[-1px] text-balance text-ink sm:text-5xl sm:tracking-[-1.5px] lg:text-[52px] lg:leading-[1.06] lg:tracking-[-1.8px]">
                    {{ __('Keep your collection where you can point at it.') }}
                </h1>
                <p class="mt-5.5 max-w-[520px] text-[17px] leading-relaxed text-pretty text-muted sm:text-[18px]">
                    {{ __(':name is built to run on infrastructure you choose. Keep the app, your photos, your documents, and the details of what you own under your control.', ['name' => config('app.name')]) }}
                </p>
                <div class="mt-8 flex">
                    <a href="{{ route('marketing.docs.portal.home.show') }}" data-turbo="true" class="inline-flex h-12 items-center justify-center gap-x-2.5 rounded-md bg-primary px-6 text-[15px] font-semibold text-on-primary transition-opacity hover:opacity-90">{{ __('Put it on your hardware') }} @svg('lucide-arrow-right', 'size-4')</a>
                </div>
            </div>

            <div class="rounded-2xl border border-hairline bg-canvas p-6 shadow-[0_24px_60px_rgba(17,17,17,0.08),0_4px_12px_rgba(17,17,17,0.04)]">
                <div class="mb-4.5 flex items-center justify-between">
                    <div class="flex items-center gap-x-2">
                        <span class="h-2 w-2 rounded-full bg-success"></span>
                        <span class="text-[12px] font-semibold tracking-[0.6px] text-body uppercase">{{ __('Your server') }}</span>
                    </div>
                    <span class="font-mono text-[11px] text-muted-soft">docker compose up</span>
                </div>
                <p class="mb-2 text-[10px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ __('Application') }}</p>
                <div class="grid grid-cols-3 gap-2.5">
                    @foreach ($appTiers as $tier)
                        <div class="flex flex-col gap-2 rounded-[10px] border border-hairline bg-sidebar p-2.5">
                            <span class="flex h-[22px] w-[22px] items-center justify-center rounded-md bg-card">
                                <span class="h-[9px] w-[9px] {{ $tier['square'] ? 'rounded-[3px]' : 'rounded-full' }}" style="background:{{ $tier['dot'] }};"></span>
                            </span>
                            <span class="text-[12px] font-semibold tracking-[-0.1px] text-ink">{{ $tier['name'] }}</span>
                            <span class="text-[10.5px] leading-[1.3] text-muted">{{ $tier['sub'] }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="flex h-[22px] justify-center gap-[60px]">
                    <span class="w-px bg-hairline"></span>
                    <span class="w-px bg-hairline"></span>
                </div>
                <p class="mb-2 text-[10px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ __('State: you own this') }}</p>
                <div class="grid grid-cols-2 gap-2.5">
                    @foreach ($stateTiers as $tier)
                        <div class="flex flex-col gap-2 rounded-[10px] bg-[#101010] p-3.5 text-white">
                            <span class="flex h-6 w-6 items-center justify-center rounded-[7px] bg-[#1a1a1a]">
                                <span class="h-2.5 w-2.5 {{ $tier['square'] ? 'rounded-[3px]' : 'rounded-full' }}" style="background:{{ $tier['dot'] }};"></span>
                            </span>
                            <span class="text-[12.5px] font-semibold tracking-[-0.1px]">{{ $tier['name'] }}</span>
                            <span class="text-[10.5px] leading-[1.35] text-[#a1a1aa]">{{ $tier['sub'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section id="practical" class="mx-auto max-w-[1200px] scroll-mt-24 px-5 pt-24 sm:px-8 sm:pt-28">
        <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-2 lg:gap-14">
            <div>
                <p class="mb-3.5 text-[13px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ __('Run it yourself') }}</p>
                <h2 class="text-[28px] leading-[1.12] font-semibold tracking-[-1px] text-ink sm:text-4xl lg:text-[38px] lg:tracking-[-1.1px]">{{ __('Your data has an address.') }}</h2>
                <p class="mt-5 text-[16px] leading-relaxed text-pretty text-muted">
                    {{ __('Run :name yourself with Docker. Your database and uploaded files live where you decide they live, not behind a vague promise and a login page you hope stays around forever.', ['name' => config('app.name')]) }}
                </p>
                <div class="mt-6 flex flex-wrap gap-2">
                    @foreach ($storageChips as $chip)
                        <span class="rounded-full border border-hairline bg-canvas px-3.5 py-1.5 text-[14px] font-medium text-body">{{ $chip }}</span>
                    @endforeach
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-[#20242c] bg-[#101010] shadow-[0_24px_60px_rgba(17,17,17,0.14)]">
                <div class="flex items-center gap-x-2 border-b border-[#242424] px-4 py-3">
                    <span class="h-2.5 w-2.5 rounded-full bg-[#333]"></span>
                    <span class="h-2.5 w-2.5 rounded-full bg-[#333]"></span>
                    <span class="h-2.5 w-2.5 rounded-full bg-[#333]"></span>
                    <span class="ml-2 font-mono text-[12px] text-muted">docker-compose.yml</span>
                </div>
                <div class="overflow-x-auto px-4.5 py-4.5 font-mono text-[12.5px] leading-[1.75]">
                    @foreach ($composeLines as $line)
                        <div class="whitespace-pre" style="color:{{ $line['c'] }};">{{ $line['t'] }}</div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-[1200px] px-5 pt-24 sm:px-8 sm:pt-28">
        <div class="mb-11 max-w-[640px]">
            <p class="mb-3.5 text-[13px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ __('Encryption at rest') }}</p>
            <h2 class="text-[28px] leading-[1.12] font-semibold tracking-[-1px] text-ink sm:text-4xl lg:text-[38px] lg:tracking-[-1.1px]">{{ __('Sensitive details are not stored as plain text.') }}</h2>
            <p class="mt-5 text-[16px] leading-relaxed text-pretty text-muted">
                {{ __(':name encrypts sensitive fields at rest. Names, values, locations, and item details are protected in the database by your instance’s application key. Curious database thieves get gibberish.', ['name' => config('app.name')]) }}
            </p>
        </div>

        <div class="grid grid-cols-1 items-center gap-4 md:grid-cols-[1fr_auto_1fr]">
            <div class="overflow-hidden rounded-2xl border border-hairline bg-canvas shadow-[0_4px_12px_rgba(17,17,17,0.04)]">
                <div class="flex items-center justify-between border-b border-hairline-soft px-4.5 py-3.5">
                    <div class="flex items-center gap-x-2">
                        <span class="h-[9px] w-[9px] rounded-full bg-badge-violet"></span>
                        <span class="text-[14px] font-semibold text-ink">Amazing Spider-Man #300</span>
                    </div>
                    <span class="rounded-full bg-card px-2.5 py-1 text-[11px] font-medium text-muted">{{ __('In the app') }}</span>
                </div>
                @foreach ($plainFields as $field)
                    <div class="flex items-center justify-between border-b border-hairline-soft px-4.5 py-3 last:border-b-0">
                        <span class="flex items-center gap-x-2 text-[13px] text-muted">
                            @svg('lucide-lock', 'size-3 text-success')
                            {{ $field['label'] }}
                        </span>
                        <span class="text-[13.5px] font-semibold text-ink">{{ $field['value'] }}</span>
                    </div>
                @endforeach
            </div>

            <div class="flex justify-center">
                @svg('lucide-arrow-right', 'hidden size-6 text-muted-soft md:block')
                @svg('lucide-arrow-down', 'size-6 text-muted-soft md:hidden')
            </div>

            <div class="overflow-hidden rounded-2xl bg-[#101010]">
                <div class="flex items-center justify-between border-b border-[#242424] px-4.5 py-3.5">
                    <div class="flex items-center gap-x-2">
                        <span class="h-[9px] w-[9px] rounded-[2px] bg-[#34d399]"></span>
                        <span class="font-mono text-[13px] font-medium text-[#e5e7eb]">items</span>
                    </div>
                    <span class="rounded-full bg-[#1a1a1a] px-2.5 py-1 text-[11px] font-medium text-[#a1a1aa]">{{ __('In the database') }}</span>
                </div>
                @foreach ($cipherFields as $field)
                    <div class="flex items-center justify-between gap-3.5 border-b border-[#1e1e1e] px-4.5 py-3 last:border-b-0">
                        <span class="shrink-0 font-mono text-[12px] text-[#6b7280]">{{ $field['col'] }}_enc</span>
                        <span class="truncate font-mono text-[12px] text-[#34d399]">{{ $field['cipher'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-[1200px] px-5 pt-24 sm:px-8 sm:pt-28">
        <div class="mb-11 max-w-[640px]">
            <p class="mb-3.5 text-[13px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ __('Backups') }}</p>
            <h2 class="text-[28px] leading-[1.12] font-semibold tracking-[-1px] text-ink sm:text-4xl lg:text-[38px] lg:tracking-[-1.1px]">{{ __('Backups are real, not a decorative button.') }}</h2>
            <p class="mt-5 text-[16px] leading-relaxed text-pretty text-muted">
                {{ __('A complete backup includes the database, uploaded photos and documents, and your application key. Keep all three. Losing the key would be an extremely bad plot twist.') }}
            </p>
        </div>
        <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
            @foreach ($backupItems as $item)
                <div class="flex flex-col gap-3.5 rounded-2xl border border-hairline bg-canvas p-6">
                    <div class="flex items-center justify-between">
                        <span class="flex h-10 w-10 items-center justify-center rounded-[10px] bg-card">
                            <span class="h-[15px] w-[15px] {{ $item['square'] ? 'rounded-[3px]' : 'rounded-full' }}" style="background:{{ $item['dot'] }};"></span>
                        </span>
                        <span class="font-mono text-[11px] text-muted-soft">{{ $item['tag'] }}</span>
                    </div>
                    <p class="text-[17px] font-semibold tracking-[-0.3px] text-ink">{{ $item['title'] }}</p>
                    <p class="text-[14px] leading-[1.5] text-pretty text-muted">{{ $item['desc'] }}</p>
                </div>
            @endforeach
        </div>
        <div class="mt-4.5 flex items-start gap-x-2.5 rounded-xl border border-hairline bg-sidebar px-4 py-3">
            @svg('lucide-check', 'size-4 shrink-0 text-success mt-0.5')
            <span class="text-[13.5px] leading-[1.5] text-body">{{ __('All three, together. Restore into any :name instance and you are exactly where you left off.', ['name' => config('app.name')]) }}</span>
        </div>
    </section>

    <section class="mx-auto max-w-[1200px] px-5 pt-24 sm:px-8 sm:pt-28">
        <div class="grid grid-cols-1 items-center gap-10 rounded-3xl bg-[#101010] px-6 py-14 text-white sm:px-12 lg:grid-cols-[1.3fr_1fr] lg:gap-14">
            <div>
                <h2 class="text-[28px] leading-[1.1] font-semibold tracking-[-1px] text-balance sm:text-[36px] lg:text-[40px] lg:tracking-[-1.3px]">{{ __('You are in charge. That is the point.') }}</h2>
                <p class="mt-5 mb-7 max-w-[520px] text-[16.5px] leading-relaxed text-pretty text-[#a1a1aa]">
                    {{ __('Self-hosting asks you to own upgrades and backups. In exchange, you get meaningful control over the collection and the system that holds it.') }}
                </p>
                <a href="{{ route('marketing.docs.portal.home.show') }}" data-turbo="true" class="inline-flex h-12 items-center justify-center gap-x-2.5 rounded-md bg-white px-6 text-[15px] font-semibold text-[#111111] transition-colors hover:bg-[#e5e7eb]">{{ __('Read the self-hosting guide') }} @svg('lucide-arrow-right', 'size-4')</a>
            </div>
            <div class="flex flex-col gap-3">
                @foreach ($chargePoints as $point)
                    <div class="flex items-start gap-x-3 rounded-xl bg-[#1a1a1a] p-4">
                        <span class="mt-0.5 flex h-[22px] w-[22px] shrink-0 items-center justify-center rounded-full bg-[#242424]">
                            <span class="h-2 w-2 rounded-full" style="background:{{ $point['dot'] }};"></span>
                        </span>
                        <div class="min-w-0">
                            <p class="text-[14px] font-semibold text-[#f2f2f2]">{{ $point['title'] }}</p>
                            <p class="mt-0.5 text-[13px] leading-[1.45] text-[#a1a1aa]">{{ $point['desc'] }}</p>
                        </div>
                    </div>
                @endforeach
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
        <p class="mt-6 max-w-[680px] text-[13.5px] leading-relaxed text-muted-soft">
            {{ __('The operator holds the encryption key; backups are an operator responsibility.') }}
            <a href="{{ route('marketing.docs.portal.home.show') }}" data-turbo="true" class="border-b border-hairline text-body transition-colors hover:text-ink">{{ __('The feature status page has the boring-but-important details.') }}</a>
        </p>
    </section>
</x-marketing-layout>
