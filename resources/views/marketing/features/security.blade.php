{{-- The "Security" feature page of the marketing site. --}}

@php
    $recoveryCodes = ['4f2a-9c1e', 'b83d-77kk', 'p0m2-x4rt', '9zq1-6h5v', 'tt4c-1b8n', 'e7w3-2ky9'];

    $factorCards = [
        ['title' => __('Authenticator app'), 'desc' => __('Time-based codes from any TOTP app. Scan the QR once and you are set.'), 'dot' => '#10b981', 'square' => true],
        ['title' => __('Recovery codes'), 'desc' => __('One-time codes for when the phone is lost. Kept off-device, on purpose.'), 'dot' => '#f59e0b', 'square' => false],
        ['title' => __('Breached-password check'), 'desc' => __('New passwords are checked against known breaches before they are accepted.'), 'dot' => '#8b5cf6', 'square' => true],
    ];

    $magicChips = [__(':count-minute expiry', ['count' => 15]), __('Single use'), __('Ignore-to-cancel')];

    // Security notifications. Tones are fixed data-viz colours; tags sit on bg-card.
    $alerts = [
        ['title' => __('New device signed in'), 'tag' => __('New device'), 'tone' => 'text-brand', 'dot' => '#3b82f6', 'square' => false, 'detail' => __('Chrome on Windows · Denver, US · verified with 2FA.'), 'time' => __(':count hours ago', ['count' => 2])],
        ['title' => __('Sign-in from a new IP address'), 'tag' => __('IP change'), 'tone' => 'text-warning', 'dot' => '#f59e0b', 'square' => true, 'detail' => __('Location changed from Portland to Denver, US.'), 'time' => __(':count hours ago', ['count' => 2])],
        ['title' => __('API key created'), 'tag' => __('API key'), 'tone' => 'text-badge-violet', 'dot' => '#8b5cf6', 'square' => true, 'detail' => __('Key “export-script” created with read-only scope.'), 'time' => __('Yesterday')],
        ['title' => __('Failed sign-in attempts'), 'tag' => __('Blocked'), 'tone' => 'text-error', 'dot' => '#ef4444', 'square' => false, 'detail' => __(':count failed attempts on your account, then rate-limited.', ['count' => 3]), 'time' => __(':count days ago', ['count' => 3])],
    ];

    $settings = [
        ['name' => __('Password'), 'desc' => __('Last changed :count months ago · checked against breaches', ['count' => 4]), 'state' => __('Strong'), 'on' => true],
        ['name' => __('Two-factor authentication'), 'desc' => __('Authenticator app · :count recovery codes remaining', ['count' => 6]), 'state' => __('On'), 'on' => true],
        ['name' => __('Magic-link sign-in'), 'desc' => __('Sign in with a one-time email link'), 'state' => __('On'), 'on' => true],
        ['name' => __('Security alert emails'), 'desc' => __('New logins, IP changes, and API-key events'), 'state' => __('On'), 'on' => true],
        ['name' => __('API keys'), 'desc' => __(':count active key · read-only scope', ['count' => 1]), 'state' => __('Manage'), 'on' => false],
    ];

    // The candid two column footer. Caveats first so they stack above the pitch on mobile.
    $notFor = [
        __('You require SSO, hardware security keys, or a full enterprise identity platform.'),
        __('You need end-to-end encryption.'),
    ];
    $chooseWhen = [
        __('You want strong everyday account protection without turning setup into a project.'),
        __('You want two-factor authentication, recovery codes, magic links, and actionable security alerts.'),
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
        <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-2 lg:gap-14">
            <div>
                <p class="text-[12px] leading-[1.5] font-semibold tracking-[1px] text-muted-soft uppercase">{{ __('A little paranoia is just good housekeeping') }}</p>
                <h1 class="mt-5 text-[32px] leading-[1.08] font-semibold tracking-[-1px] text-balance text-ink sm:text-5xl sm:tracking-[-1.5px] lg:text-[52px] lg:leading-[1.06] lg:tracking-[-1.8px]">
                    {{ __('Keep the keys to the collection in good hands.') }}
                </h1>
                <p class="mt-5.5 text-[17px] leading-relaxed text-pretty text-muted sm:text-[18px]">
                    {{ __('A collection can reveal what you own, what it is worth, and where it lives. :name gives you practical ways to protect access without turning account setup into an evening activity.', ['name' => config('app.name')]) }}
                </p>
                <div class="mt-8 flex">
                    <a href="{{ route('register') }}" class="inline-flex h-12 items-center justify-center gap-x-2.5 rounded-md bg-primary px-6 text-[15px] font-semibold text-on-primary transition-opacity hover:opacity-90">{{ __('Lock it down') }} @svg('lucide-arrow-right', 'size-4')</a>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-hairline bg-canvas shadow-[0_24px_60px_rgba(17,17,17,0.08),0_4px_12px_rgba(17,17,17,0.04)]">
                <div class="flex items-center gap-x-2.5 border-b border-hairline-soft px-5 py-4">
                    <span class="h-2 w-2 rounded-full bg-success"></span>
                    <span class="text-[14px] font-semibold text-ink">{{ __('Two-factor authentication') }}</span>
                    <span class="ml-auto rounded-full bg-card px-2 py-[3px] text-[10px] font-bold tracking-[0.4px] text-success">{{ __('STEP :n OF :total', ['n' => 2, 'total' => 3]) }}</span>
                </div>
                <div class="flex flex-col gap-5 p-5 sm:flex-row sm:gap-5.5">
                    <div class="shrink-0">
                        <div class="h-[132px] w-[132px] rounded-xl border border-hairline p-2.5">
                            <div class="h-full w-full rounded-sm" style="background-color:#111;background-image:repeating-linear-gradient(0deg,#111 0 8px,#fff 8px 16px),repeating-linear-gradient(90deg,transparent 0 8px,rgba(255,255,255,0.55) 8px 16px);background-blend-mode:screen;"></div>
                        </div>
                        <p class="mt-2 text-center text-[11px] text-muted-soft">{{ __('Scan in your authenticator') }}</p>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-[12.5px] leading-[1.55] text-muted">{{ __('Or enter this setup key manually:') }}</p>
                        <p class="mt-2 rounded-lg border border-hairline bg-sidebar px-3 py-2.5 font-mono text-[12.5px] font-semibold tracking-[1px] text-ink">KZ4F · 9QM2 · 7T8W · X1RC</p>
                        <p class="mt-4.5 text-[11px] font-bold tracking-[0.4px] text-muted-soft uppercase">{{ __('Recovery codes') }}</p>
                        <div class="mt-2 grid grid-cols-2 gap-1.5">
                            @foreach ($recoveryCodes as $code)
                                <span class="rounded-[5px] bg-card px-2 py-1.5 text-center font-mono text-[12px] text-body">{{ $code }}</span>
                            @endforeach
                        </div>
                        <div class="mt-3 flex items-center gap-x-2 text-[11.5px] text-body">
                            @svg('lucide-shield', 'size-3.5 shrink-0')
                            {{ __('Store these somewhere safe, not next to your phone.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-[1200px] px-5 pt-24 sm:px-8 sm:pt-28">
        <div class="mb-10 max-w-[660px]">
            <p class="mb-3.5 text-[13px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ __('Add a second factor') }}</p>
            <h2 class="text-[28px] leading-[1.12] font-semibold tracking-[-1px] text-ink sm:text-4xl lg:text-[38px] lg:tracking-[-1.1px]">{{ __('A stolen password stops being enough.') }}</h2>
            <p class="mt-5 text-[16px] leading-relaxed text-pretty text-muted">
                {{ __('Turn on two-factor authentication and one leaked password no longer opens the door. Keep the recovery codes somewhere safe, ideally not in the same place as the phone that generates the codes.') }}
            </p>
        </div>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            @foreach ($factorCards as $card)
                <div class="rounded-2xl border border-hairline bg-canvas p-5.5 shadow-[0_4px_12px_rgba(17,17,17,0.04)]">
                    <span class="flex h-[34px] w-[34px] items-center justify-center rounded-[9px] bg-card">
                        <span class="h-[11px] w-[11px] {{ $card['square'] ? 'rounded-[3px]' : 'rounded-full' }}" style="background:{{ $card['dot'] }};"></span>
                    </span>
                    <p class="mt-4 text-[15px] font-semibold text-ink">{{ $card['title'] }}</p>
                    <p class="mt-2 text-[13px] leading-[1.55] text-pretty text-muted">{{ $card['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <section class="mx-auto max-w-[1200px] px-5 pt-24 sm:px-8 sm:pt-28">
        <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-[1fr_1.05fr] lg:gap-14">
            <div>
                <p class="mb-3.5 text-[13px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ __('Sign in without another password') }}</p>
                <h2 class="text-[28px] leading-[1.12] font-semibold tracking-[-1px] text-ink sm:text-4xl lg:text-[38px] lg:tracking-[-1.1px]">{{ __('Let your inbox do the vouching.') }}</h2>
                <p class="mt-5 text-[16px] leading-relaxed text-pretty text-muted">
                    {{ __('Magic links let you sign in from your email when that is more convenient. Less password wrangling, fewer “which one did I use?” moments.') }}
                </p>
                <div class="mt-6 flex flex-wrap gap-2">
                    @foreach ($magicChips as $chip)
                        <span class="rounded-full border border-hairline bg-canvas px-3 py-1.5 text-[13px] font-medium text-body">{{ $chip }}</span>
                    @endforeach
                </div>
            </div>

            <div class="flex flex-col gap-4">
                <div class="overflow-hidden rounded-2xl border border-hairline bg-canvas shadow-[0_24px_60px_rgba(17,17,17,0.08),0_4px_12px_rgba(17,17,17,0.04)]">
                    <div class="flex items-center gap-x-2.5 border-b border-hairline-soft px-4.5 py-3.5">
                        <x-logo size="26" class="shrink-0" aria-hidden="true" />
                        <div class="min-w-0">
                            <p class="text-[13px] font-semibold text-ink">{{ __('Sign in to :name', ['name' => config('app.name')]) }}</p>
                            <p class="text-[11.5px] text-muted-soft">no-reply@{{ Str::lower(config('app.name')) }}.app · {{ __('to :email', ['email' => 'jamie@…']) }}</p>
                        </div>
                        <span class="ml-auto shrink-0 text-[11px] text-muted-soft">{{ __('now') }}</span>
                    </div>
                    <div class="px-4.5 py-5">
                        <p class="text-[13.5px] leading-relaxed text-body">{{ __('Here is your one-time sign-in link. It expires in :count minutes and can only be used once.', ['count' => 15]) }}</p>
                        <span class="mt-4 inline-flex h-11 items-center gap-x-2.5 rounded-md bg-primary px-5 text-[14px] font-semibold text-on-primary">{{ __('Sign in to :name', ['name' => config('app.name')]) }} @svg('lucide-arrow-right', 'size-4')</span>
                        <p class="mt-3.5 text-[11.5px] text-muted-soft">{{ __('Didn’t request this? You can safely ignore it, nothing changes until the link is used.') }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-x-3 rounded-2xl border border-hairline bg-sidebar px-4.5 py-3.5">
                    <span class="flex h-[30px] w-[30px] shrink-0 items-center justify-center rounded-full bg-canvas">@svg('lucide-check', 'size-4 text-success')</span>
                    <div class="min-w-0">
                        <p class="text-[13.5px] font-semibold text-ink">{{ __('Signed in securely') }}</p>
                        <p class="text-[12px] text-muted">MacBook Pro · Portland, US · {{ __('2FA verified') }}</p>
                    </div>
                    <span class="ml-auto shrink-0 text-[11px] font-semibold text-success">{{ __('Trusted device') }}</span>
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-[1200px] px-5 pt-24 sm:px-8 sm:pt-28">
        <div class="mb-10 max-w-[660px]">
            <p class="mb-3.5 text-[13px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ __('Know when something looks off') }}</p>
            <h2 class="text-[28px] leading-[1.12] font-semibold tracking-[-1px] text-ink sm:text-4xl lg:text-[38px] lg:tracking-[-1.1px]">{{ __('The first hint should come from us.') }}</h2>
            <p class="mt-5 text-[16px] leading-relaxed text-pretty text-muted">
                {{ __(':name emails you about failed sign-ins, new devices, changed IP addresses, and API-key changes. The first time you learn about a strange login should not be from a stranger.', ['name' => config('app.name')]) }}
            </p>
        </div>
        <div class="overflow-hidden rounded-2xl border border-hairline bg-canvas shadow-[0_4px_12px_rgba(17,17,17,0.04)]">
            <div class="flex items-center gap-x-2.5 border-b border-hairline-soft px-5 py-4 sm:px-6">
                <span class="h-2 w-2 rounded-full bg-warning"></span>
                <span class="text-[15px] font-semibold text-ink">{{ __('Security notifications') }}</span>
                <span class="ml-auto text-[11px] text-muted">{{ __('Last :count days', ['count' => 7]) }}</span>
            </div>
            <div class="px-5 sm:px-6">
                @foreach ($alerts as $alert)
                    <div class="flex items-start gap-4 border-b border-hairline-soft py-4.5 last:border-b-0">
                        <span class="mt-0.5 flex h-[34px] w-[34px] shrink-0 items-center justify-center rounded-[9px] bg-card">
                            <span class="h-2.5 w-2.5 {{ $alert['square'] ? 'rounded-[3px]' : 'rounded-full' }}" style="background:{{ $alert['dot'] }};"></span>
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-x-2.5 gap-y-1">
                                <span class="text-[14.5px] font-semibold text-ink">{{ $alert['title'] }}</span>
                                <span class="rounded-full bg-card px-2 py-[2px] text-[11px] font-semibold {{ $alert['tone'] }}">{{ $alert['tag'] }}</span>
                            </div>
                            <p class="mt-1 text-[12.5px] text-pretty text-muted">{{ $alert['detail'] }}</p>
                        </div>
                        <span class="shrink-0 self-center text-[11px] text-muted-soft">{{ $alert['time'] }}</span>
                    </div>
                @endforeach
            </div>
            <div class="flex items-center gap-x-2.5 bg-sidebar px-5 py-3.5 sm:px-6">
                @svg('lucide-mail', 'size-3.5 shrink-0 text-body')
                <span class="text-[12.5px] text-body">{{ __('Every alert is emailed the moment it happens, no dashboard-watching required.') }}</span>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-[1200px] px-5 pt-24 sm:px-8 sm:pt-28">
        <div class="mb-10 max-w-[660px]">
            <p class="mb-3.5 text-[13px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ __('Make good choices easy') }}</p>
            <h2 class="text-[28px] leading-[1.12] font-semibold tracking-[-1px] text-ink sm:text-4xl lg:text-[38px] lg:tracking-[-1.1px]">{{ __('Every control in one place.') }}</h2>
            <p class="mt-5 text-[16px] leading-relaxed text-pretty text-muted">
                {{ __('Security settings put passwords, two-factor authentication, recovery codes, API keys, and account protection together. The controls are there before you need them.') }}
            </p>
        </div>
        <div class="overflow-hidden rounded-2xl border border-hairline bg-canvas shadow-[0_4px_12px_rgba(17,17,17,0.04)]">
            <div class="flex items-center gap-x-2.5 border-b border-hairline-soft px-5 py-4 sm:px-6">
                <span class="h-2 w-2 rounded-full" style="background:#64748b;"></span>
                <span class="text-[15px] font-semibold text-ink">{{ __('Settings · Security') }}</span>
                <span class="ml-auto rounded-full bg-card px-2.5 py-[3px] text-[11px] font-semibold text-success">{{ __('Strong') }}</span>
            </div>
            <div class="px-5 sm:px-6">
                @foreach ($settings as $setting)
                    <div class="flex items-center gap-4 border-b border-hairline-soft py-4 last:border-b-0">
                        <div class="min-w-0 flex-1">
                            <p class="text-[14.5px] font-semibold text-ink">{{ $setting['name'] }}</p>
                            <p class="mt-0.5 text-[12.5px] text-muted">{{ $setting['desc'] }}</p>
                        </div>
                        <span class="shrink-0 text-[12px] font-semibold {{ $setting['on'] ? 'text-success' : 'text-body' }}">{{ $setting['state'] }}</span>
                        <span class="relative inline-block h-[23px] w-10 shrink-0 rounded-full {{ $setting['on'] ? 'bg-ink' : 'bg-hairline' }}">
                            <span class="absolute top-0.5 h-[19px] w-[19px] rounded-full bg-white shadow-[0_1px_2px_rgba(0,0,0,0.2)] {{ $setting['on'] ? 'right-0.5' : 'left-0.5' }}"></span>
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-[1200px] px-5 pt-24 sm:px-8 sm:pt-28">
        <div class="flex flex-col items-start justify-between gap-8 rounded-3xl bg-[#101010] px-6 py-14 text-white sm:px-12 lg:flex-row lg:items-center">
            <div class="max-w-[580px]">
                <h2 class="text-[26px] leading-[1.12] font-semibold tracking-[-1px] text-balance sm:text-[32px] lg:text-[34px]">{{ __('Turn on the protection while it is quiet.') }}</h2>
                <p class="mt-4 text-[16px] leading-relaxed text-pretty text-[#a1a1aa]">
                    {{ __('Two-factor, recovery codes, magic links, and actionable alerts, set them once from one settings page.') }}
                </p>
            </div>
            <a href="{{ route('register') }}" class="inline-flex h-[52px] shrink-0 items-center justify-center gap-x-2.5 rounded-[10px] bg-white px-6.5 text-[15px] font-semibold text-[#111111] transition-colors hover:bg-[#e5e7eb]">{{ __('Review your security settings') }} @svg('lucide-arrow-right', 'size-4')</a>
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
            {{ __('Encryption at rest is covered on the data ownership page; this is not end-to-end encryption.') }}
            <a href="{{ route('marketing.docs.portal.home.show') }}" data-turbo="true" class="border-b border-hairline text-body transition-colors hover:text-ink">{{ __('The feature status page has the boring-but-important details.') }}</a>
        </p>
    </section>
</x-marketing-layout>
