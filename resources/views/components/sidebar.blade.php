@use('App\Helpers\Palette')

@props(['catalog' => null, 'categories' => null])

@php
    $user = auth()->user();
    $isProfile = request()->routeIs('profile.*');
    $isAccount = request()->routeIs('settings.*');
    $isInstance = request()->routeIs('instanceAdmin.*');
    $isSupport = request()->routeIs('support.*');
    $isCollection = $catalog !== null;
@endphp

{{-- The closed position is a static class rather than an x-cloak plus :class pair. x-cloak
     would hide the sidebar until Alpine boots, and since it is in flow on desktop that drops
     it out of the layout and shifts the page once it appears. Alpine only removes
     -translate-x-full to slide it in on mobile. --}}
<aside
    data-morph-skip
    :class="{ '-translate-x-full': ! sidebarOpen }"
    class="fixed inset-y-0 left-0 z-40 flex w-60 shrink-0 -translate-x-full flex-col gap-6 border-r border-hairline bg-sidebar px-4 py-5 transition-transform duration-200 lg:sticky lg:top-0 lg:h-screen lg:translate-x-0"
>
    {{-- Logo + theme toggle --}}
    <div class="flex items-center justify-between px-2">
        {{-- The mark is decorative here: the wordmark next to it already names the app,
             so labelling both would announce it twice. --}}
        <a href="{{ route('dashboard.index') }}" data-turbo="true" class="flex items-center gap-2">
            <x-logo size="26" aria-hidden="true" />
            <x-wordmark height="15" class="text-ink" />
        </a>

        <x-theme-toggle class="border border-hairline bg-canvas text-muted hover:text-ink" />
    </div>

    {{-- Only the navigation scrolls. The logo above and the plan card and user block
         below stay put, so a short viewport never puts the account menu or the way out
         of the free plan out of reach. min-h-0 is what lets this shrink below the
         height of its own content: without it a flex child refuses to scroll. --}}
    <div class="flex min-h-0 flex-1 flex-col gap-6 overflow-y-auto">
        @if ($isInstance)
            {{-- The instance administration panel is English only and never translated,
                 so its labels are plain strings rather than __() calls. --}}
            <a href="{{ route('dashboard.index') }}" data-turbo="true" class="flex items-center gap-2 px-2 text-[13px] font-medium text-muted transition-colors hover:text-ink">
                @svg('lucide-arrow-left', 'size-4')
                Back to app
            </a>

            <nav class="flex flex-col gap-0.5">
                <p class="px-2 py-1.5 text-xs font-medium tracking-wide text-muted-soft uppercase">Manage</p>
                <x-sidebar-link :href="route('instanceAdmin.index')" :active="request()->routeIs('instanceAdmin.index')" icon="layout-grid">Overview</x-sidebar-link>
                <x-sidebar-link :href="route('instanceAdmin.accounts.index')" :active="request()->routeIs('instanceAdmin.accounts.*')" icon="users">Accounts & users</x-sidebar-link>
                <x-sidebar-link :href="route('instanceAdmin.support.index')" :active="request()->routeIs('instanceAdmin.support.*')" icon="message-square">Support tickets</x-sidebar-link>
                <x-sidebar-link :href="route('instanceAdmin.deletionReasons.index')" :active="request()->routeIs('instanceAdmin.deletionReasons.*')" icon="door-open">Deletion reasons</x-sidebar-link>
            </nav>

            @php
                $pendingTestimonials = \App\Models\Testimonial::query()->where('status', \App\Enums\TestimonialStatus::InReview)->count();
                $draftPosts = \App\Models\BlogPost::query()->where('status', \App\Enums\BlogPostStatus::Draft)->count();
            @endphp
            <nav class="-mt-2 flex flex-col gap-0.5">
                <p class="px-2 py-1.5 text-xs font-medium tracking-wide text-muted-soft uppercase">Marketing</p>
                <x-sidebar-link :href="route('instanceAdmin.marketing.testimonials.index')" :active="request()->routeIs('instanceAdmin.marketing.testimonials.*')" icon="quote" :count="$pendingTestimonials > 0 ? $pendingTestimonials : null">Testimonials</x-sidebar-link>
                <x-sidebar-link :href="route('instanceAdmin.marketing.blogPosts.index')" :active="request()->routeIs('instanceAdmin.marketing.blogPosts.*')" icon="notebook-pen" :count="$draftPosts > 0 ? $draftPosts : null">Blog posts</x-sidebar-link>
                <x-sidebar-link :href="route('instanceAdmin.siteOptions.index')" :active="request()->routeIs('instanceAdmin.siteOptions.*')" icon="sliders-horizontal">Site options</x-sidebar-link>
            </nav>
        @elseif ($isProfile)
            <a href="{{ route('dashboard.index') }}" data-turbo="true" class="flex items-center gap-2 px-2 text-[13px] font-medium text-muted transition-colors hover:text-ink">
                @svg('lucide-arrow-left', 'size-4')
                {{ __('Back to dashboard') }}
            </a>

            <nav class="flex flex-col gap-0.5">
                <p class="px-2 py-1.5 text-xs font-medium tracking-wide text-muted-soft uppercase">{{ __('Your profile') }}</p>
                <x-sidebar-link :href="route('profile.index')" :active="request()->routeIs('profile.index') || request()->routeIs('profile.logs.*') || request()->routeIs('profile.emails.*')" icon="user">{{ __('Profile') }}</x-sidebar-link>
                <x-sidebar-link :href="route('profile.security.index')" :active="request()->routeIs('profile.security.*')" icon="key">{{ __('Security & access') }}</x-sidebar-link>
                <x-sidebar-link :href="route('profile.webhooks.index')" :active="request()->routeIs('profile.webhooks.*')" icon="webhook">{{ __('Webhooks') }}</x-sidebar-link>
                <x-sidebar-link :href="route('profile.user.index')" :active="request()->routeIs('profile.user.*')" icon="triangle-alert">{{ __('Danger zone') }}</x-sidebar-link>
            </nav>
        @elseif ($isAccount)
            <a href="{{ route('dashboard.index') }}" data-turbo="true" class="flex items-center gap-2 px-2 text-[13px] font-medium text-muted transition-colors hover:text-ink">
                @svg('lucide-arrow-left', 'size-4')
                {{ __('Back to dashboard') }}
            </a>

            <nav class="flex flex-col gap-0.5">
                <p class="px-2 py-1.5 text-xs font-medium tracking-wide text-muted-soft uppercase">{{ __('Account') }}</p>
                @if ($user->isOwner())
                    <x-sidebar-link :href="route('settings.index')" :active="request()->routeIs('settings.index')" icon="settings">{{ __('General') }}</x-sidebar-link>
                    <x-sidebar-link :href="route('settings.members.index')" :active="request()->routeIs('settings.members.*')" icon="users">{{ __('Members') }}</x-sidebar-link>
                @endif
                <x-sidebar-link :href="route('settings.trash.index')" :active="request()->routeIs('settings.trash.*')" icon="trash-2">{{ __('Trash') }}</x-sidebar-link>
            </nav>

            <nav class="-mt-2 flex flex-col gap-0.5">
                <p class="px-2 py-1.5 text-xs font-medium tracking-wide text-muted-soft uppercase">{{ __('Administration') }}</p>
                <x-sidebar-link :href="route('settings.types.index')" :active="request()->routeIs('settings.types.*')" icon="boxes">{{ __('Collection types') }}</x-sidebar-link>
                <x-sidebar-link :href="route('settings.tags.index')" :active="request()->routeIs('settings.tags.*')" icon="tag">{{ __('Tags') }}</x-sidebar-link>
                <x-sidebar-link :href="route('settings.itemConditions.index')" :active="request()->routeIs('settings.itemConditions.*')" icon="gauge">{{ __('Item conditions') }}</x-sidebar-link>
                <x-sidebar-link :href="route('settings.photos.index')" :active="request()->routeIs('settings.photos.*')" icon="image">{{ __('Photos') }}</x-sidebar-link>
            </nav>

            @if (config('marketing.show'))
                <nav class="-mt-2 flex flex-col gap-0.5">
                    <p class="px-2 py-1.5 text-xs font-medium tracking-wide text-muted-soft uppercase">{{ __('Marketing') }}</p>
                    <x-sidebar-link :href="route('settings.testimonials.index')" :active="request()->routeIs('settings.testimonials.*')" icon="quote">{{ __('Testimonials') }}</x-sidebar-link>
                </nav>
            @endif
        @elseif ($isSupport)
            <a href="{{ route('dashboard.index') }}" data-turbo="true" class="flex items-center gap-2 px-2 text-[13px] font-medium text-muted transition-colors hover:text-ink">
                @svg('lucide-arrow-left', 'size-4')
                {{ __('Back to dashboard') }}
            </a>

            <nav class="flex flex-col gap-0.5">
                <p class="px-2 py-1.5 text-xs font-medium tracking-wide text-muted-soft uppercase">{{ __('Support') }}</p>
                <x-sidebar-link :href="route('support.tickets.index')" :active="request()->routeIs('support.tickets.index') || request()->routeIs('support.tickets.show') || request()->routeIs('support.tickets.update') || request()->routeIs('support.tickets.destroy')" icon="messages-square">{{ __('Your conversations') }}</x-sidebar-link>
                <x-sidebar-link :href="route('support.tickets.new')" :active="request()->routeIs('support.tickets.new') || request()->routeIs('support.tickets.create')" icon="plus">{{ __('New conversation') }}</x-sidebar-link>
            </nav>

            <nav class="flex flex-col gap-0.5">
                <p class="px-2 py-1.5 text-xs font-medium tracking-wide text-muted-soft uppercase">{{ __('Resources') }}</p>
                <x-sidebar-link :href="route('marketing.docs.portal.home.show')" :active="false" icon="book-open">{{ __('Documentation') }}</x-sidebar-link>
                <x-sidebar-link :href="route('marketing.docs.api.index')" :active="false" icon="code">{{ __('API Docs') }}</x-sidebar-link>
            </nav>
        @elseif ($isCollection)
            <a href="{{ route('collections.index') }}" data-turbo="true" class="flex items-center gap-2 px-2 text-[13px] font-medium text-muted transition-colors hover:text-ink">
                @svg('lucide-arrow-left', 'size-4')
                {{ __('Back to collections') }}
            </a>

            <nav class="flex flex-col gap-0.5">
                <p class="truncate px-2 py-1.5 text-xs font-medium tracking-wide text-muted-soft uppercase">{{ $catalog->name }}</p>
                <x-sidebar-link :href="route('collections.show', $catalog)" :active="request()->routeIs('collections.show') || request()->routeIs('items.*')" icon="library-big">{{ __('Items') }}</x-sidebar-link>
                <x-sidebar-link :href="route('sets.index', $catalog)" :active="request()->routeIs('sets.*')" icon="radiation">{{ __('Sets') }}</x-sidebar-link>
                <x-sidebar-link :href="route('statistics.index', $catalog)" :active="request()->routeIs('statistics.*')" icon="chart-no-axes-combined">{{ __('Statistics') }}</x-sidebar-link>
            </nav>

            {{-- The categories are the navigation of the collection, so they are listed here
                 rather than hidden behind the screen that manages them. Nesting is flattened:
                 the tree is what the manage screen is for. --}}
            @if ($categories?->isNotEmpty())
                <nav class="flex flex-col gap-0.5">
                    <p class="px-2 py-1.5 text-xs font-medium tracking-wide text-muted-soft uppercase">{{ __('Categories') }}</p>
                    @foreach ($categories as $category)
                        <x-sidebar-link
                            :href="route('categories.show', [$catalog, $category])"
                            :active="request()->routeIs('categories.show') && (int) request()->route('category') === $category->id"
                            :dot="Palette::forId($category->id)"
                            :count="$category->items_count"
                            data-test="sidebar-category-{{ $category->id }}"
                        >{{ $category->name }}</x-sidebar-link>
                    @endforeach
                </nav>
            @endif

            <nav class="flex flex-col gap-0.5">
                <p class="px-2 py-1.5 text-xs font-medium tracking-wide text-muted-soft uppercase">{{ __('Manage collection') }}</p>
                <x-sidebar-link :href="route('categories.index', $catalog)" :active="request()->routeIs('categories.index')" icon="folder-tree">{{ __('Manage categories') }}</x-sidebar-link>
            </nav>
        @else
            <nav class="flex flex-col gap-0.5">
                {{-- The shortcut lives on the link itself, so it works from every screen the
                     sidebar is on and always opens the same page the link does. --}}
                <x-sidebar-link
                    :href="route('search.index')"
                    :active="request()->routeIs('search.*')"
                    icon="search"
                    shortcut
                    x-data
                    x-on:keydown.window.prevent.meta.k="$el.click()"
                    x-on:keydown.window.prevent.ctrl.k="$el.click()"
                    data-test="sidebar-search"
                >{{ __('Search') }}</x-sidebar-link>
                <p class="px-2 pt-4 pb-1.5 text-xs font-medium tracking-wide text-muted-soft uppercase">{{ __('Workspace') }}</p>
                {{-- Only while the account still wants the screen. Dismissing it takes the link with it. --}}
                @if ($user->account->show_getting_started)
                    <x-sidebar-link :href="route('gettingStarted.index')" :active="request()->routeIs('gettingStarted.*')" icon="rocket">{{ __('Getting started') }}</x-sidebar-link>
                @endif
                <x-sidebar-link :href="route('dashboard.index')" :active="request()->routeIs('dashboard.*')" icon="layout-grid">{{ __('Dashboard') }}</x-sidebar-link>
                <x-sidebar-link :href="route('collections.index')" :active="request()->routeIs('collections.*')" icon="layers">{{ __('Collections') }}</x-sidebar-link>
                {{-- A series spans collections rather than living in one, so it sits beside them
                     in the workspace nav rather than inside a collection. --}}
                <x-sidebar-link :href="route('series.index')" :active="request()->routeIs('series.*')" icon="library">{{ __('Series') }}</x-sidebar-link>
                <x-sidebar-link :href="route('locations.index')" :active="request()->routeIs('locations.*')" icon="map-pin">{{ __('Locations') }}</x-sidebar-link>
                @php($overdueLoans = \App\Models\Loan::query()->forAccount($user->account)->where('status', \App\Enums\LoanStatus::Overdue)->count())
                <x-sidebar-link :href="route('loans.index')" :active="request()->routeIs('loans.*')" icon="arrow-left-right" :count="$overdueLoans > 0 ? $overdueLoans : null">{{ __('Loans') }}</x-sidebar-link>

                <p class="px-2 pt-4 pb-1.5 text-xs font-medium tracking-wide text-muted-soft uppercase">{{ __('Account management') }}</p>
                @if ($user->isOwner())
                    <x-sidebar-link :href="route('settings.index')" :active="false" icon="settings">{{ __('Account settings') }}</x-sidebar-link>
                @elseif ($user->account->allowsManagementBy($user))
                    <x-sidebar-link :href="route('settings.types.index')" :active="false" icon="boxes">{{ __('Collection types') }}</x-sidebar-link>
                @endif

                <p class="px-2 pt-4 pb-1.5 text-xs font-medium tracking-wide text-muted-soft uppercase">{{ __('Documentation') }}</p>
                <x-sidebar-link :href="route('marketing.docs.portal.home.show')" :active="false" icon="book-open">{{ __('Documentation site') }}</x-sidebar-link>
                <x-sidebar-link :href="route('marketing.docs.api.index')" :active="false" icon="code">{{ __('API Docs') }}</x-sidebar-link>
                {{-- The support section only exists when the instance turns it on. --}}
                @if (config('support.enabled'))
                    <x-sidebar-link :href="route('support.tickets.index')" :active="request()->routeIs('support.*')" icon="life-buoy">{{ __('Support') }}</x-sidebar-link>
                @endif
            </nav>
        @endif
    </div>

    {{-- The free plan, wherever the user happens to be. Not in the instance
         administration: that panel looks across every account and belongs to
         whoever runs the instance, not to the account they are reading. --}}
    @unless ($isInstance)
        <x-plan-usage />
    @endunless

    {{-- User block --}}
    <div x-data="{ open: false }" class="relative border-t border-hairline pt-3">
        <button type="button" @click="open = !open" class="cursor-pointer flex w-full items-center gap-2.5 rounded-md px-2 py-2 transition-colors hover:bg-canvas">
            <x-avatar :user="$user" :size="32" class="size-8 text-xs" />
            <span class="flex min-w-0 flex-1 flex-col text-left">
                <span class="truncate text-[13px] font-semibold text-ink">{{ $user->getFullName() }}</span>
                <span class="text-xs text-muted-soft capitalize">{{ __(ucfirst($user->role)) }}</span>
            </span>
            @svg('lucide-chevrons-up-down', 'size-4 shrink-0 text-muted-soft')
        </button>

        <div
            x-cloak
            x-show="open"
            @click.away="open = false"
            x-transition.opacity
            class="absolute bottom-full left-0 mb-1 w-full rounded-md border border-hairline bg-canvas p-1 shadow-md"
        >
            <a href="{{ route('profile.index') }}" data-turbo="true" class="cursor-pointer flex items-center gap-2 rounded px-2 py-1.5 text-sm text-body transition-colors hover:bg-card hover:text-ink">
                @svg('lucide-user', 'size-4 text-muted')
                {{ __('Profile') }}
            </a>
            @if ($user->isInstanceAdministrator())
                <a href="{{ route('instanceAdmin.index') }}" data-turbo="true" class="cursor-pointer flex items-center gap-2 rounded px-2 py-1.5 text-sm text-body transition-colors hover:bg-card hover:text-ink">
                    @svg('lucide-shield', 'size-4 text-muted')
                    {{ __('Instance admin') }}
                </a>
            @endif
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex w-full cursor-pointer items-center gap-2 rounded px-2 py-1.5 text-sm text-body transition-colors hover:bg-card hover:text-ink">
                    @svg('lucide-log-out', 'size-4 text-muted')
                    {{ __('Logout') }}
                </button>
            </form>
        </div>
    </div>
</aside>
