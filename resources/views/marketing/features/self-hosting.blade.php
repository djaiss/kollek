{{-- The "Self-hosting" feature page of the marketing site. --}}

@php
    $repo = config('marketing.github_url');

    // The four-command install path, shown in the hero terminal. Commands and machine output
    // stay literal; only the human labels are translated.
    $installSteps = [
        ['label' => __('Step :n · Clone', ['n' => 1]), 'cmd' => 'git clone '.$repo.'.git kollek', 'out' => "Cloning into 'kollek'... done."],
        ['label' => __('Step :n · Configure', ['n' => 2]), 'cmd' => 'cd kollek && cp .env.docker.example .env', 'out' => 'Set APP_KEY, DB_PASSWORD in .env'],
        ['label' => __('Step :n · Set the app key', ['n' => 3]), 'cmd' => 'docker compose run --rm app php artisan key:generate', 'out' => 'Application key set.'],
        ['label' => __('Step :n · Bring it up', ['n' => 4]), 'cmd' => 'docker compose up -d', 'out' => 'Started app, queue, scheduler, mysql · migrations ran on boot'],
    ];

    // The same route as four cards. dot colours are fixed accents (they do not invert).
    $routeSteps = [
        ['n' => '1', 'dot' => '#3b82f6', 'title' => __('Clone the repo'), 'cmd' => 'git clone …/kollek', 'desc' => __('Pull the source and the Compose stack that ships with it.')],
        ['n' => '2', 'dot' => '#8b5cf6', 'title' => __('Set your .env'), 'cmd' => 'cp .env.docker.example .env', 'desc' => __('Copy the example env, then set your app key and database password.')],
        ['n' => '3', 'dot' => '#f59e0b', 'title' => __('Compose up'), 'cmd' => 'docker compose up -d', 'desc' => __('Four containers start together: app, queue, scheduler, and MySQL.')],
        ['n' => '4', 'dot' => '#14b8a6', 'title' => __('It runs'), 'cmd' => 'http://localhost:8000', 'desc' => __('Migrations run on boot. Open the app in your browser.')],
    ];

    // The application tier: the three CONTAINER_ROLE services.
    $appRoles = [
        ['name' => __('Web'), 'svc' => 'app', 'dot' => '#3b82f6', 'job' => __('Serves the app over nginx and PHP-FPM, and runs database migrations on boot.')],
        ['name' => __('Queue worker'), 'svc' => 'queue', 'dot' => '#6366f1', 'job' => __('Processes background jobs on the high, default, and low queues.')],
        ['name' => __('Scheduler'), 'svc' => 'scheduler', 'dot' => '#f59e0b', 'job' => __('Runs the artisan schedule: recurring cleanup and maintenance tasks.')],
    ];

    // The state tier: two durable named volumes.
    $dataRoles = [
        ['name' => 'MySQL', 'tone' => 'text-success', 'dot' => 'bg-success', 'job' => __('Holds your collection, items, copies, and history: the source of truth.'), 'vol' => 'db-data → /var/lib/mysql'],
        ['name' => __('Durable storage'), 'tone' => 'text-brand', 'dot' => 'bg-brand', 'job' => __('Uploaded photos, documents, and generated files. Local disk or any S3-compatible bucket.'), 'vol' => 'storage-data → /var/www/html/storage'],
    ];

    // A real upgrade sequence. Migrations run automatically on boot (the entrypoint), so the
    // last step is a note, not a manual gate.
    $upgradeSteps = [
        ['n' => '1', 'title' => __('Pull the new image'), 'cmd' => 'docker compose pull', 'tag' => __('Code'), 'tone' => 'text-brand'],
        ['n' => '2', 'title' => __('Back up first'), 'cmd' => 'docker compose exec mysql mysqldump kollek > db.sql', 'tag' => __('Safety'), 'tone' => 'text-warning'],
        ['n' => '3', 'title' => __('Recreate containers'), 'cmd' => 'docker compose up -d', 'tag' => __('Volumes kept'), 'tone' => 'text-success'],
        ['n' => '4', 'title' => __('Migrations run on boot'), 'cmd' => 'php artisan migrate --force', 'tag' => __('Automatic'), 'tone' => 'text-body'],
    ];

    // The backup checklist: the two volumes and the application key. No automated command is
    // claimed, because none ships.
    $backupItems = [
        ['name' => __('The database'), 'where' => 'db-data', 'why' => __('Every item, collection, valuation, and history entry. A mysqldump or a volume snapshot both work.'), 'sev' => __('ESSENTIAL'), 'tone' => 'text-success'],
        ['name' => __('Uploaded files'), 'where' => 'storage-data', 'why' => __('Photos, documents, and attachments. Restoring a database without these leaves broken references.'), 'sev' => __('ESSENTIAL'), 'tone' => 'text-success'],
        ['name' => __('The application key'), 'where' => '.env → APP_KEY', 'why' => __(':name encrypts data at rest, so without this key the encrypted values cannot be read back. Easy to forget, painful to lose.', ['name' => config('app.name')]), 'sev' => __('DO NOT SKIP'), 'tone' => 'text-warning'],
    ];

    // The candid two column footer. The left column (caveats) is authored first so it stacks
    // above the pitch on mobile: a visitor sees the honest bit before the sell.
    $notFor = [
        __('You want a zero-maintenance hosted service and never want to see Docker.'),
        __('You need a deployment platform to make every operational decision for you.'),
    ];
    $chooseWhen = [
        __('You want a documented, supported route to running the full app on infrastructure you choose.'),
        __('A small server, Docker Compose, and a clear operating model sound like good news.'),
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
            <p class="text-[12px] font-semibold tracking-[1px] text-muted-soft uppercase">{{ __('Some assembly required. Not much, though.') }}</p>
            <h1 class="mt-5 text-[32px] leading-[1.08] font-semibold tracking-[-1px] text-balance text-ink sm:text-5xl sm:tracking-[-1.5px] lg:text-[56px] lg:leading-[1.04] lg:tracking-[-2px]">
                {{ __('Your own collection app. On your own terms.') }}
            </h1>
            <p class="mt-5.5 max-w-[680px] text-[17px] leading-relaxed text-pretty text-muted sm:text-[18px]">
                {{ __(':name is a full collection app you can run on infrastructure you choose. Start with Docker Compose, keep the moving parts understandable, and retain a clear path for upgrades and backups.', ['name' => config('app.name')]) }}
            </p>
            <div class="mt-8 flex">
                <a href="{{ route('marketing.docs.portal.home.show') }}" data-turbo="true" class="inline-flex h-12 items-center justify-center gap-x-2.5 rounded-md bg-primary px-6 text-[15px] font-semibold text-on-primary transition-opacity hover:opacity-90">{{ __('Run the thing') }} @svg('lucide-arrow-right', 'size-4')</a>
            </div>
        </div>

        <div class="mt-13 overflow-hidden rounded-2xl border border-[#20242c] bg-[#0f1115] shadow-[0_24px_60px_rgba(17,17,17,0.16),0_4px_12px_rgba(17,17,17,0.06)]">
            <div class="flex items-center gap-x-2 border-b border-[#20242c] px-4.5 py-3">
                <span class="h-[11px] w-[11px] rounded-full bg-[#ff5f57]"></span>
                <span class="h-[11px] w-[11px] rounded-full bg-[#febc2e]"></span>
                <span class="h-[11px] w-[11px] rounded-full bg-[#28c840]"></span>
                <div class="flex flex-1 justify-center">
                    <span class="font-mono text-[11px] text-[#6b7280]">bash — kollek@server: ~/kollek</span>
                </div>
            </div>
            <div class="overflow-x-auto px-5 py-5 font-mono text-[13px] leading-[1.85] sm:px-6.5">
                @foreach ($installSteps as $step)
                    <div class="mb-4.5">
                        <p class="mb-1 text-[10px] font-semibold tracking-[0.8px] text-[#4b5563] uppercase">{{ $step['label'] }}</p>
                        <div class="flex gap-x-2.5">
                            <span class="shrink-0 text-[#28c840]">$</span>
                            <span class="whitespace-nowrap text-[#e5e7eb]">{{ $step['cmd'] }}</span>
                        </div>
                        <p class="pl-5 whitespace-pre-wrap text-[#6b7280]">{{ $step['out'] }}</p>
                    </div>
                @endforeach
                <div class="mt-1 flex items-center gap-x-2.5 border-t border-[#20242c] pt-4">
                    <span class="h-2 w-2 rounded-full bg-[#28c840] shadow-[0_0_0_4px_rgba(40,200,64,0.15)]"></span>
                    <span class="text-[12.5px] text-[#28c840]">4 containers healthy · app on http://localhost:8000</span>
                </div>
            </div>
        </div>
        <p class="mt-4 text-center text-[13px] text-muted-soft">
            {{ __('Four commands from an empty directory to a running instance. The last one is “docker compose up”, not a leap of faith.') }}
        </p>
    </section>

    <section class="mx-auto max-w-[1200px] px-5 pt-24 sm:px-8 sm:pt-28">
        <div class="mb-10 max-w-[660px]">
            <p class="mb-3.5 text-[13px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ __('A short route from nothing to running') }}</p>
            <h2 class="text-[28px] leading-[1.12] font-semibold tracking-[-1px] text-ink sm:text-4xl lg:text-[38px] lg:tracking-[-1.1px]">{{ __('The supported path is Docker.') }}</h2>
            <p class="mt-5 text-[16px] leading-relaxed text-pretty text-muted">
                {{ __('Bring up the web app, queue worker, scheduler, database, and storage with a documented setup, instead of assembling a stack from forum posts and optimism.') }}
            </p>
        </div>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($routeSteps as $step)
                <div class="rounded-2xl border border-hairline bg-canvas p-5 shadow-[0_4px_12px_rgba(17,17,17,0.04)]">
                    <div class="flex items-center justify-between">
                        <span class="flex h-[30px] w-[30px] items-center justify-center rounded-lg bg-card font-mono text-[13px] font-semibold text-body">{{ $step['n'] }}</span>
                        <span class="h-2 w-2 rounded-full" style="background:{{ $step['dot'] }};"></span>
                    </div>
                    <p class="mt-4 text-[15px] font-semibold text-ink">{{ $step['title'] }}</p>
                    <p class="mt-2.5 truncate rounded-md bg-card px-2.5 py-1.5 font-mono text-[11.5px] text-success">{{ $step['cmd'] }}</p>
                    <p class="mt-3 text-[12.5px] leading-[1.55] text-pretty text-muted">{{ $step['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <section class="mx-auto max-w-[1200px] px-5 pt-24 sm:px-8 sm:pt-28">
        <div class="mb-10 max-w-[660px]">
            <p class="mb-3.5 text-[13px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ __('Small enough to understand') }}</p>
            <h2 class="text-[28px] leading-[1.12] font-semibold tracking-[-1px] text-ink sm:text-4xl lg:text-[38px] lg:tracking-[-1.1px]">{{ __('Five parts. That is the whole cast.') }}</h2>
            <p class="mt-5 text-[16px] leading-relaxed text-pretty text-muted">
                {{ __(':name uses a web role, a queue role, a scheduler role, MySQL, and durable storage. Each part has a job, and each part is described in the docs.', ['name' => config('app.name')]) }}
            </p>
        </div>

        <div class="overflow-hidden rounded-2xl border border-hairline bg-canvas shadow-[0_4px_12px_rgba(17,17,17,0.04)]">
            <div class="flex items-center gap-x-2.5 border-b border-hairline-soft px-6 py-4">
                <span class="h-2 w-2 rounded-full bg-badge-violet"></span>
                <span class="text-[15px] font-semibold text-ink">{{ __('Compose stack') }}</span>
                <span class="ml-auto font-mono text-[11px] font-medium text-muted">docker-compose.yml · {{ __(':count services', ['count' => 4]) }}</span>
            </div>
            <div class="p-6 sm:px-6">
                <p class="mb-3 text-[11px] font-bold tracking-[0.5px] text-muted-soft uppercase">{{ __('Application') }}</p>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    @foreach ($appRoles as $role)
                        <div class="rounded-xl border border-hairline p-4.5">
                            <div class="flex items-center gap-x-2.5">
                                <span class="h-[9px] w-[9px] rounded-full" style="background:{{ $role['dot'] }};"></span>
                                <span class="text-[14.5px] font-semibold text-ink">{{ $role['name'] }}</span>
                                <span class="ml-auto font-mono text-[10px] text-muted-soft">{{ $role['svc'] }}</span>
                            </div>
                            <p class="mt-2.5 text-[12.5px] leading-[1.55] text-pretty text-muted">{{ $role['job'] }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="my-5.5 flex items-center gap-x-3.5">
                    <span class="h-px flex-1 bg-hairline"></span>
                    <span class="text-[11px] font-semibold tracking-[0.5px] text-muted-soft uppercase">{{ __('reads & writes') }}</span>
                    <span class="h-px flex-1 bg-hairline"></span>
                </div>

                <p class="mb-3 text-[11px] font-bold tracking-[0.5px] text-muted-soft uppercase">{{ __('State & storage') }}</p>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    @foreach ($dataRoles as $role)
                        <div class="rounded-xl border border-hairline bg-sidebar p-4.5">
                            <div class="flex items-center gap-x-2.5">
                                <span class="h-[9px] w-[9px] rounded-[3px] {{ $role['dot'] }}"></span>
                                <span class="text-[14.5px] font-semibold text-ink">{{ $role['name'] }}</span>
                                <span class="ml-auto rounded-full bg-card px-2 py-0.5 text-[10px] font-bold tracking-[0.3px] {{ $role['tone'] }}">{{ __('DURABLE VOLUME') }}</span>
                            </div>
                            <p class="mt-2.5 text-[12.5px] leading-[1.55] text-pretty text-muted">{{ $role['job'] }}</p>
                            <p class="mt-3 overflow-x-auto rounded-md border border-hairline bg-canvas px-2.5 py-1.5 font-mono text-[11px] whitespace-nowrap text-body">{{ $role['vol'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-[1200px] px-5 pt-24 sm:px-8 sm:pt-28">
        <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-[1fr_1.05fr] lg:gap-14">
            <div>
                <p class="mb-3.5 text-[13px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ __('Upgrade without holding your breath') }}</p>
                <h2 class="text-[28px] leading-[1.12] font-semibold tracking-[-1px] text-ink sm:text-4xl lg:text-[38px] lg:tracking-[-1.1px]">{{ __('Your data volumes survive the swap.') }}</h2>
                <p class="mt-5 text-[16px] leading-relaxed text-pretty text-muted">
                    {{ __('Updates preserve the data volumes that hold the database and uploaded files. Follow the documented upgrade path, run the migrations, and keep moving.') }}
                </p>
                <div class="mt-6 flex items-start gap-x-2.5 rounded-xl border border-hairline bg-sidebar px-4 py-3.5">
                    @svg('lucide-check', 'size-4 shrink-0 text-success mt-0.5')
                    <span class="text-[12.5px] leading-[1.55] text-body">
                        {!! __('Containers are replaced. The <strong>named volumes stay put</strong>, so the swap touches code, not your collection.') !!}
                    </span>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-hairline bg-canvas shadow-[0_24px_60px_rgba(17,17,17,0.08),0_4px_12px_rgba(17,17,17,0.04)]">
                <div class="flex items-center gap-x-2.5 border-b border-hairline-soft px-5 py-4">
                    <span class="h-2 w-2 rounded-full bg-brand"></span>
                    <span class="text-[15px] font-semibold text-ink">{{ __('Upgrade run') }}</span>
                    <span class="ml-auto font-mono text-[11px] text-muted">v3.2 → v3.3</span>
                </div>
                <div class="px-5 pt-2 pb-4">
                    @foreach ($upgradeSteps as $step)
                        <div @class(['flex items-center gap-3.5 py-3.5', 'border-b border-hairline-soft' => ! $loop->last])>
                            <span class="flex h-[26px] w-[26px] shrink-0 items-center justify-center rounded-full bg-card font-mono text-[11px] font-semibold text-body">{{ $step['n'] }}</span>
                            <div class="min-w-0 flex-1">
                                <p class="text-[13.5px] font-semibold text-ink">{{ $step['title'] }}</p>
                                <p class="mt-1 truncate font-mono text-[11.5px] text-muted">{{ $step['cmd'] }}</p>
                            </div>
                            <span class="shrink-0 rounded-full bg-card px-2.5 py-1 text-[11px] font-semibold {{ $step['tone'] }}">{{ $step['tag'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-[1200px] px-5 pt-24 sm:px-8 sm:pt-28">
        <div class="mb-10 max-w-[660px]">
            <p class="mb-3.5 text-[13px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ __('Backups are part of the deal') }}</p>
            <h2 class="text-[28px] leading-[1.12] font-semibold tracking-[-1px] text-ink sm:text-4xl lg:text-[38px] lg:tracking-[-1.1px]">{{ __('Three things, backed up together.') }}</h2>
            <p class="mt-5 text-[16px] leading-relaxed text-pretty text-muted">
                {{ __('A reliable backup includes the database, uploaded files, and the application key. It is not glamorous. It is extremely worth doing.') }}
            </p>
        </div>

        <div class="overflow-hidden rounded-2xl border border-hairline bg-canvas shadow-[0_4px_12px_rgba(17,17,17,0.04)]">
            <div class="flex items-center gap-x-2.5 border-b border-hairline-soft px-6 py-4">
                <span class="h-2 w-2 rounded-full bg-warning"></span>
                <span class="text-[15px] font-semibold text-ink">{{ __('Backup checklist') }}</span>
                <span class="ml-auto text-[11px] font-medium text-muted">{{ __(':done of :total covered', ['done' => 3, 'total' => 3]) }}</span>
            </div>
            <div class="px-6">
                @foreach ($backupItems as $item)
                    <div @class(['flex items-start gap-4 py-5', 'border-b border-hairline-soft' => ! $loop->last])>
                        <span class="mt-0.5 flex h-[26px] w-[26px] shrink-0 items-center justify-center rounded-full bg-card">
                            @svg('lucide-check', 'size-3.5 text-success')
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-x-2.5 gap-y-1">
                                <span class="text-[15px] font-semibold text-ink">{{ $item['name'] }}</span>
                                <span class="rounded-[5px] bg-card px-2 py-0.5 font-mono text-[10.5px] text-body">{{ $item['where'] }}</span>
                            </div>
                            <p class="mt-1.5 text-[13px] leading-[1.55] text-pretty text-muted">{{ $item['why'] }}</p>
                        </div>
                        <span class="shrink-0 self-center rounded-full bg-card px-2.5 py-1 text-[11px] font-bold tracking-[0.3px] {{ $item['tone'] }}">{{ $item['sev'] }}</span>
                    </div>
                @endforeach
            </div>
            <p class="border-t border-hairline-soft bg-sidebar px-6 py-3.5 text-[12px] leading-[1.55] text-muted-soft">
                {{ __('No automated backup command ships yet. Snapshot the two named volumes, keep your APP_KEY somewhere safe, and standard Docker tooling handles the rest.') }}
            </p>
        </div>
    </section>

    <section class="mx-auto max-w-[1200px] px-5 pt-24 sm:px-8 sm:pt-28">
        <div class="flex flex-col items-start justify-between gap-8 rounded-3xl bg-[#101010] px-6 py-14 sm:px-12 lg:flex-row lg:items-center">
            <div class="max-w-[580px]">
                <h2 class="text-[26px] leading-[1.12] font-semibold tracking-[-1px] text-balance text-white sm:text-[32px] lg:text-[34px]">{{ __('Ready to run it yourself?') }}</h2>
                <p class="mt-4 text-[16px] leading-relaxed text-pretty text-[#a1a1aa]">
                    {{ __('One documented path covers install, upgrade, and backup. Start with Docker Compose and keep every moving part where you can see it.') }}
                </p>
            </div>
            <a href="{{ route('marketing.docs.portal.home.show') }}" data-turbo="true" class="inline-flex h-[52px] shrink-0 items-center justify-center gap-x-2.5 rounded-[10px] bg-white px-6.5 text-[15px] font-semibold text-[#111111] transition-colors hover:bg-[#e5e7eb]">{{ __('Read the install guide') }} @svg('lucide-arrow-right', 'size-4')</a>
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
