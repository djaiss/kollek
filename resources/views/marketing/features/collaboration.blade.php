{{-- The "Collaboration" feature page of the marketing site. --}}

@php
    // Role accents reused across the member list, the matrix and the activity feed. The
    // colours are fixed theme tokens (they do not invert in dark), so a role reads the
    // same everywhere: owner green, editor blue, viewer purple.
    $roles = [
        'owner' => ['label' => __('Owner'), 'tag' => __('Runs the account'), 'text' => 'text-success', 'dot' => 'bg-success', 'avatar' => 'bg-success'],
        'editor' => ['label' => __('Editor'), 'tag' => __('Add & update'), 'text' => 'text-brand', 'dot' => 'bg-brand', 'avatar' => 'bg-brand'],
        'viewer' => ['label' => __('Viewer'), 'tag' => __('Look, don\'t touch'), 'text' => 'text-badge-violet', 'dot' => 'bg-badge-violet', 'avatar' => 'bg-badge-violet'],
    ];

    $members = [
        ['name' => 'Monica Geller', 'email' => 'monica@friends.com', 'initials' => 'MG', 'role' => 'owner'],
        ['name' => 'Ross Geller', 'email' => 'ross@friends.com', 'initials' => 'RG', 'role' => 'editor'],
        ['name' => 'Chandler Bing', 'email' => 'chandler@friends.com', 'initials' => 'CB', 'role' => 'editor'],
        ['name' => 'Phoebe Buffay', 'email' => 'phoebe@friends.com', 'initials' => 'PB', 'role' => 'viewer'],
    ];

    // Each row maps a real task to the roles allowed to do it. Browsing is open to all;
    // writing the collection is owner and editor; the last two rows are owner only.
    $matrix = [
        ['task' => __('Browse & search the collection'), 'viewer' => true, 'editor' => true, 'owner' => true],
        ['task' => __('View item details & values'), 'viewer' => true, 'editor' => true, 'owner' => true],
        ['task' => __('Add & edit items'), 'viewer' => false, 'editor' => true, 'owner' => true],
        ['task' => __('Upload & manage photos'), 'viewer' => false, 'editor' => true, 'owner' => true],
        ['task' => __('Manage locations, tags & types'), 'viewer' => false, 'editor' => true, 'owner' => true],
        ['task' => __('Create & delete collections'), 'viewer' => false, 'editor' => true, 'owner' => true],
        ['task' => __('Invite & manage members'), 'viewer' => false, 'editor' => false, 'owner' => true],
        ['task' => __('Manage account settings'), 'viewer' => false, 'editor' => false, 'owner' => true],
    ];

    // scope pins whether a change touched a single item or the whole account, and borrows
    // the item/account accent colours from the matrix (blue for item, green for account).
    $activity = [
        ['who' => 'Ross Geller', 'role' => 'editor', 'initials' => 'RG', 'action' => __('changed the condition of'), 'target' => 'Amazing Spider-Man #300', 'from' => __('Near Mint'), 'to' => __('Very Fine'), 'scope' => 'item', 'time' => __(':count hours ago', ['count' => 2])],
        ['who' => 'Monica Geller', 'role' => 'owner', 'initials' => 'MG', 'action' => __('invited'), 'target' => 'phoebe@friends.com', 'from' => null, 'to' => null, 'scope' => 'account', 'time' => __(':count hours ago', ['count' => 5])],
        ['who' => 'Chandler Bing', 'role' => 'editor', 'initials' => 'CB', 'action' => __('updated the estimated value of'), 'target' => 'Uncanny X-Men #266', 'from' => '$640', 'to' => '$710', 'scope' => 'item', 'time' => __('Yesterday')],
        ['who' => 'Ross Geller', 'role' => 'editor', 'initials' => 'RG', 'action' => __('added :count items to', ['count' => 3]), 'target' => 'Marvel Comics 1990s', 'from' => null, 'to' => null, 'scope' => 'account', 'time' => __('Yesterday')],
        ['who' => 'Monica Geller', 'role' => 'owner', 'initials' => 'MG', 'action' => __('changed Chandler’s role to'), 'target' => __('Editor'), 'from' => null, 'to' => null, 'scope' => 'account', 'time' => __('Mon')],
    ];

    $scopes = [
        'item' => ['label' => __('ITEM'), 'text' => 'text-brand'],
        'account' => ['label' => __('ACCOUNT'), 'text' => 'text-success'],
    ];

    // The candid two column footer. The left column (caveats) is authored first so it
    // stacks above the pitch on mobile: a visitor sees the honest bit before the sell.
    $notFor = [
        ['head' => __('You need complex enterprise identity, SSO, or approval workflows.'), 'body' => null],
        ['head' => __('You need public collection links today.'), 'body' => __('Visibility settings exist, but public links do not yet.')],
    ];

    $chooseWhen = [
        ['head' => __('A clear owner, editor, and viewer model is enough for your household, club, or small team.'), 'body' => null],
        ['head' => __('You want to invite trusted people and keep a readable record of changes.'), 'body' => null],
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
            {{ __('For households, clubs, and the one person who always edits the wrong field') }}
        </p>
        <h1 class="mx-auto max-w-[840px] text-[32px] leading-[1.08] font-semibold tracking-[-1px] text-balance text-ink sm:text-5xl sm:tracking-[-1.5px] lg:text-[60px] lg:leading-[1.06] lg:tracking-[-2px]">
            {{ __('Everyone can help. Not everyone needs the big red button.') }}
        </h1>
        <p class="mx-auto mt-6 max-w-[660px] text-[17px] leading-relaxed text-pretty text-muted sm:text-[19px]">
            {{ __('A collection can be a solo pursuit. It can also be a household project, a club archive, or the shared responsibility of people who all have strong opinions about where the rare one belongs.') }}
        </p>
        <div class="mt-9 flex flex-col justify-center gap-3 sm:flex-row">
            <a href="{{ route('register') }}" class="flex h-12 items-center justify-center rounded-md bg-primary px-6 text-[15px] font-semibold text-on-primary transition-opacity hover:opacity-90">{{ __('Bring in the crew') }}</a>
            <a href="#roles" class="flex h-12 items-center justify-center gap-x-2 rounded-md border border-hairline bg-canvas px-5.5 text-[15px] font-semibold text-ink transition-colors hover:bg-sidebar">
                {{ __('See the roles') }}
                @svg('lucide-arrow-down', 'size-4')
            </a>
        </div>
    </section>

    <section class="mx-auto mt-14 max-w-[980px] px-5 sm:px-8">
        <div class="overflow-hidden rounded-xl border border-hairline bg-canvas shadow-[0_24px_60px_rgba(17,17,17,0.10),0_4px_12px_rgba(17,17,17,0.05)]">
            <div class="flex h-11 items-center gap-x-2 border-b border-hairline-soft bg-sidebar px-4">
                <span class="h-[11px] w-[11px] rounded-full bg-hairline"></span>
                <span class="h-[11px] w-[11px] rounded-full bg-hairline"></span>
                <span class="h-[11px] w-[11px] rounded-full bg-hairline"></span>
                <div class="ml-3 hidden h-[26px] max-w-[340px] flex-1 items-center rounded-sm border border-hairline bg-input px-2.5 text-xs text-muted-soft sm:flex">
                    {{ Str::lower(config('app.name')) }}.app/account/members
                </div>
            </div>
            <div class="p-6 sm:p-7">
                <div class="mb-5 flex items-end justify-between gap-4">
                    <div>
                        <p class="text-[19px] font-semibold tracking-[-0.4px] text-ink">{{ __('Members') }}</p>
                        <p class="mt-0.5 text-[13px] text-muted">{{ __('People with access to the Geller household account.') }}</p>
                    </div>
                    <span class="shrink-0 rounded-md bg-primary px-3.5 py-2 text-[13px] font-semibold text-on-primary">{{ __('+ Invite member') }}</span>
                </div>
                <div class="overflow-hidden rounded-xl border border-hairline">
                    @foreach ($members as $member)
                        @php($role = $roles[$member['role']])
                        <div @class(['flex items-center gap-3.5 px-4 py-4 sm:px-[18px]', 'border-t border-hairline-soft' => ! $loop->first])>
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-[14px] font-semibold text-white {{ $role['avatar'] }}">{{ $member['initials'] }}</span>
                            <div class="min-w-0 flex-1 leading-[1.35]">
                                <p class="text-[15px] font-semibold tracking-[-0.2px] text-ink">{{ $member['name'] }}</p>
                                <p class="truncate text-[13px] text-muted">{{ $member['email'] }}</p>
                            </div>
                            <span class="flex items-center gap-x-2 rounded-full bg-card px-3 py-1.5 text-[13px] font-semibold {{ $role['text'] }}">
                                <span class="h-[7px] w-[7px] rounded-full {{ $role['dot'] }}"></span>
                                {{ $role['label'] }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-[1200px] px-5 pt-24 sm:px-8 sm:pt-28">
        <div class="mb-11 max-w-[660px]">
            <p class="mb-3.5 text-[13px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ __('Invite the right people') }}</p>
            <h2 class="text-[28px] leading-[1.1] font-semibold tracking-[-1px] text-ink sm:text-4xl lg:text-[42px] lg:tracking-[-1.4px]">{{ __('Send an invitation. Not a shared password.') }}</h2>
            <p class="mt-5 text-[17px] leading-relaxed text-pretty text-muted">
                {{ __('Send an invitation, welcome someone into the account, and give them a role that matches how they contribute. No shared password written on a note. No “just be careful” permissions model.') }}
            </p>
        </div>

        <div class="grid grid-cols-1 items-stretch gap-5 md:grid-cols-3">
            <div class="flex flex-col gap-y-3.5">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-primary text-[12px] font-semibold text-on-primary">1</span>
                    <span class="text-[13px] font-semibold tracking-[-0.1px] text-ink">{{ __('Invitation sent') }}</span>
                </div>
                <div class="flex flex-1 flex-col gap-3 rounded-2xl border border-hairline bg-canvas p-4.5">
                    <div>
                        <p class="mb-1.5 text-[11px] font-semibold tracking-[0.4px] text-muted-soft uppercase">{{ __('Email') }}</p>
                        <div class="rounded-lg border border-hairline px-3 py-2.5 text-[14px] text-ink">phoebe@friends.com</div>
                    </div>
                    <div>
                        <p class="mb-1.5 text-[11px] font-semibold tracking-[0.4px] text-muted-soft uppercase">{{ __('Role') }}</p>
                        <div class="flex items-center justify-between rounded-lg border border-hairline px-3 py-2.5 text-[14px]">
                            <span class="flex items-center gap-x-2 font-semibold {{ $roles['editor']['text'] }}">
                                <span class="h-[7px] w-[7px] rounded-full {{ $roles['editor']['dot'] }}"></span>
                                {{ $roles['editor']['label'] }}
                            </span>
                            @svg('lucide-chevron-down', 'size-3.5 text-muted-soft')
                        </div>
                    </div>
                    <div class="flex-1"></div>
                    <div class="flex items-center gap-x-2.5 rounded-lg bg-card px-3 py-2.5">
                        @svg('lucide-check', 'size-4 text-success')
                        <span class="text-[13px] font-semibold text-success">{{ __('Invitation sent') }}</span>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-y-3.5">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-primary text-[12px] font-semibold text-on-primary">2</span>
                    <span class="text-[13px] font-semibold tracking-[-0.1px] text-ink">{{ __('They get an email') }}</span>
                </div>
                <div class="flex flex-1 items-center rounded-2xl border border-hairline bg-sidebar p-4">
                    <div class="w-full overflow-hidden rounded-xl border border-hairline bg-canvas">
                        <div class="px-4.5 pt-4.5 pb-1.5">
                            <div class="mb-4 flex items-center gap-x-2">
                                <x-logo size="20" aria-hidden="true" />
                                <x-wordmark height="13" class="text-ink" />
                            </div>
                            <p class="mb-2 text-[15px] leading-[1.3] font-semibold tracking-[-0.3px] text-ink">{{ __('You’ve been invited to collaborate') }}</p>
                            <p class="mb-3.5 text-[13px] leading-[1.55] text-muted">
                                {!! __('<strong>:owner</strong> invited you to the household collection as an <strong>:role</strong>.', ['owner' => 'Monica', 'role' => __('Editor')]) !!}
                            </p>
                            <span class="inline-block rounded-lg bg-primary px-4.5 py-2.5 text-[13px] font-semibold text-on-primary">{{ __('Accept invitation') }}</span>
                        </div>
                        <div class="mt-3.5 border-t border-hairline-soft px-4.5 py-3 text-[11px] text-muted-soft">{{ __('Sent to :email', ['email' => 'phoebe@friends.com']) }}</div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-y-3.5">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-primary text-[12px] font-semibold text-on-primary">3</span>
                    <span class="text-[13px] font-semibold tracking-[-0.1px] text-ink">{{ __('First access') }}</span>
                </div>
                <div class="flex flex-1 flex-col gap-3 rounded-2xl border border-hairline bg-canvas p-4.5">
                    <div class="flex items-center gap-x-3 border-b border-hairline-soft pb-3.5">
                        <span class="flex h-[38px] w-[38px] items-center justify-center rounded-full text-[14px] font-semibold text-white {{ $roles['viewer']['avatar'] }}">PB</span>
                        <div class="leading-[1.3]">
                            <p class="text-[14px] font-semibold text-ink">Phoebe Buffay</p>
                            <p class="text-[12px] font-semibold {{ $roles['editor']['text'] }}">{{ __('Joined as :role', ['role' => __('Editor')]) }}</p>
                        </div>
                    </div>
                    <p class="text-[13px] font-semibold text-ink">Marvel Comics 1990s</p>
                    <div class="grid grid-cols-2 gap-2.5">
                        <div class="overflow-hidden rounded-lg border border-hairline">
                            <div class="h-10" style="background:repeating-linear-gradient(135deg,#fb923c 0px,#fb923c 8px,#fdba74 8px,#fdba74 16px);"></div>
                            <p class="px-2.5 py-1.5 text-[11px] font-semibold text-ink">Spider-Man #300</p>
                        </div>
                        <div class="overflow-hidden rounded-lg border border-hairline">
                            <div class="h-10" style="background:repeating-linear-gradient(135deg,#8b5cf6 0px,#8b5cf6 8px,#c4b5fd 8px,#c4b5fd 16px);"></div>
                            <p class="px-2.5 py-1.5 text-[11px] font-semibold text-ink">X-Men #266</p>
                        </div>
                    </div>
                    <div class="flex-1"></div>
                    <div class="flex items-center gap-x-2 rounded-lg bg-card px-3 py-2.5 text-[12px] text-muted">
                        @svg('lucide-pencil', 'size-3.5')
                        {{ __('Can add & edit. Can’t manage members.') }}
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="roles" class="mx-auto max-w-[1200px] scroll-mt-24 px-5 pt-24 sm:px-8 sm:pt-28">
        <div class="mb-11 max-w-[660px]">
            <p class="mb-3.5 text-[13px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ __('Three roles') }}</p>
            <h2 class="text-[28px] leading-[1.1] font-semibold tracking-[-1px] text-ink sm:text-4xl lg:text-[42px] lg:tracking-[-1.4px]">{{ __('Three roles. No interpretive dance.') }}</h2>
            <p class="mt-5 text-[17px] leading-relaxed text-pretty text-muted">
                {{ __('Owners manage the account. Editors add and update the collection. Viewers can browse without accidentally reclassifying the entire shelf. Clear enough to remember; useful enough to trust.') }}
            </p>
        </div>

        <div class="overflow-x-auto">
            <div class="min-w-[640px] overflow-hidden rounded-2xl border border-hairline bg-canvas">
                <div class="grid grid-cols-[1.6fr_1fr_1fr_1fr] border-b border-hairline">
                    <div class="px-6 py-5 text-[13px] font-semibold tracking-[0.5px] text-muted-soft uppercase">{{ __('What they can do') }}</div>
                    @foreach (['viewer', 'editor', 'owner'] as $key)
                        @php($role = $roles[$key])
                        <div @class(['border-l border-hairline-soft px-4 py-4 text-center', 'bg-sidebar' => $key === 'owner'])>
                            <span class="inline-flex items-center gap-x-2 text-[15px] font-semibold {{ $role['text'] }}">
                                <span class="h-2 w-2 rounded-full {{ $role['dot'] }}"></span>
                                {{ $role['label'] }}
                            </span>
                            <p class="mt-1 text-[12px] text-muted">{{ $role['tag'] }}</p>
                        </div>
                    @endforeach
                </div>

                @foreach ($matrix as $row)
                    <div @class(['grid grid-cols-[1.6fr_1fr_1fr_1fr]', 'border-t border-hairline-soft' => ! $loop->first])>
                        <div class="flex items-center px-6 py-3.5 text-[15px] font-medium text-ink">{{ $row['task'] }}</div>
                        @foreach (['viewer', 'editor', 'owner'] as $key)
                            <div @class(['flex items-center justify-center border-l border-hairline-soft px-4 py-3.5', 'bg-sidebar' => $key === 'owner'])>
                                @if ($row[$key])
                                    @svg('lucide-check', 'size-[18px] ' . $roles[$key]['text'])
                                @else
                                    <span class="h-0.5 w-3.5 rounded-full bg-hairline"></span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-[1200px] px-5 pt-24 sm:px-8 sm:pt-28">
        <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-[0.9fr_1.1fr] lg:gap-14">
            <div>
                <p class="mb-3.5 text-[13px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ __('Know what changed') }}</p>
                <h2 class="text-[28px] leading-[1.1] font-semibold tracking-[-1px] text-ink sm:text-4xl lg:text-[42px] lg:tracking-[-1.4px]">{{ __('Who changed it, and when.') }}</h2>
                <p class="mt-5 text-[17px] leading-relaxed text-pretty text-muted">
                    {{ __(':name keeps an activity history of changes across the account and on individual items. When something looks different, you can see who changed it and when. This is not an interrogation tool. Mostly.', ['name' => config('app.name')]) }}
                </p>
                <div class="mt-6 flex flex-wrap gap-2">
                    @foreach ([__('Account-wide history'), __('Per-item history'), __('Who & when')] as $chip)
                        <span class="rounded-full bg-card px-3.5 py-1.5 text-[14px] font-medium text-body">{{ $chip }}</span>
                    @endforeach
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-hairline bg-canvas shadow-[0_4px_16px_rgba(17,17,17,0.05)]">
                <div class="flex items-center justify-between border-b border-hairline-soft bg-sidebar px-5 py-4">
                    <p class="text-[14px] font-semibold text-ink">{{ __('Activity') }}</p>
                    <div class="flex gap-1.5">
                        <span class="rounded-full bg-card px-2.5 py-1 text-[12px] font-semibold text-ink">{{ __('All') }}</span>
                        <span class="rounded-full px-2.5 py-1 text-[12px] font-medium text-muted">{{ __('Items') }}</span>
                    </div>
                </div>
                @foreach ($activity as $entry)
                    @php($role = $roles[$entry['role']])
                    @php($scope = $scopes[$entry['scope']])
                    <div @class(['flex gap-x-3 px-5 py-4', 'border-t border-hairline-soft' => ! $loop->first])>
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-[12px] font-semibold text-white {{ $role['avatar'] }}">{{ $entry['initials'] }}</span>
                        <div class="min-w-0 flex-1">
                            <p class="text-[14px] leading-[1.5] text-pretty text-ink">
                                <span class="font-semibold">{{ $entry['who'] }}</span>
                                {{ $entry['action'] }}
                                <span class="font-semibold">{{ $entry['target'] }}</span>
                            </p>
                            @if (! is_null($entry['from']))
                                <div class="mt-2 inline-flex items-center gap-x-2 font-mono text-[12px]">
                                    <span class="rounded-md bg-card px-2 py-[3px] text-muted line-through">{{ $entry['from'] }}</span>
                                    @svg('lucide-arrow-right', 'size-3 text-muted-soft')
                                    <span class="rounded-md bg-card px-2 py-[3px] text-ink">{{ $entry['to'] }}</span>
                                </div>
                            @endif
                            <div class="mt-2 flex items-center gap-x-2.5">
                                <span class="rounded-full bg-card px-2 py-[2px] text-[11px] font-semibold tracking-[0.2px] {{ $scope['text'] }}">{{ $scope['label'] }}</span>
                                <span class="text-[12px] text-muted-soft">{{ $entry['time'] }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-[1200px] px-5 pt-24 sm:px-8 sm:pt-28">
        <div class="rounded-3xl bg-card px-6 py-16 text-center sm:px-12 sm:py-[72px]">
            <p class="mb-4 text-[13px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ __('A shared collection') }}</p>
            <h2 class="mx-auto max-w-[680px] text-[26px] leading-[1.14] font-semibold tracking-[-1px] text-balance text-ink sm:text-[34px] lg:text-[40px] lg:tracking-[-1.2px]">
                {{ __('A shared collection still needs a source of truth.') }}
            </h2>
            <p class="mx-auto mt-5 max-w-[560px] text-[17px] leading-relaxed text-pretty text-muted">
                {{ __('Give everyone one place to look, one vocabulary to use, and the right amount of access. That tends to make “where did it go?” a less frequent conversation.') }}
            </p>
            <div class="mt-8 flex justify-center">
                <a href="{{ route('register') }}" class="flex h-[50px] items-center justify-center rounded-md bg-primary px-6.5 text-[15px] font-semibold text-on-primary transition-opacity hover:opacity-90">{{ __('Invite your first member') }}</a>
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
