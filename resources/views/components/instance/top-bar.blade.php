{{-- The top bar of the instance administration panel. --}}
@php($admin = auth()->user())

<div class="sticky top-0 z-30 flex h-12 items-center gap-3 bg-gradient-to-r from-amber-900 to-amber-700 px-4 text-white lg:px-6">
    <button
        type="button"
        @click="sidebarOpen = true"
        class="flex size-8 shrink-0 items-center justify-center rounded-md border border-white/25 text-white lg:hidden"
        aria-label="Open menu"
    >
        @svg('lucide-menu', 'size-4')
    </button>

    <div class="flex items-center gap-2.5">
        @svg('lucide-shield', 'size-4 shrink-0 text-amber-200')
        <span class="text-[12.5px] font-bold tracking-[0.06em] uppercase">Instance admin panel</span>
        <span class="size-1.5 shrink-0 animate-pulse rounded-full bg-amber-400"></span>
    </div>

    <p class="hidden text-[12.5px] font-medium text-amber-100/90 xl:block">
        Acting across <b class="font-semibold text-white">every account</b> on this instance. Changes are global.
    </p>

    <div class="flex-1"></div>

    <span class="hidden font-mono text-xs font-semibold text-amber-200 sm:inline">env: {{ app()->environment() }}</span>

    <div class="hidden items-center gap-2 rounded-full bg-black/15 py-1 pr-2.5 pl-1 md:flex">
        <x-avatar :user="$admin" :size="32" class="size-6 shrink-0 text-[10px]" />
        <span class="text-xs font-semibold text-white">{{ $admin->getFullName() }}</span>
        <span class="rounded bg-amber-400 px-1.5 py-0.5 text-[10px] font-bold tracking-wide text-amber-900 uppercase">Instance admin</span>
    </div>

    <a
        href="{{ route('dashboard.index') }}"
        data-turbo="true"
        class="flex shrink-0 items-center gap-1.5 rounded-lg border border-white/25 px-2.5 py-1.5 text-[12.5px] font-semibold text-white transition-colors hover:bg-white/10"
    >
        @svg('lucide-arrow-left', 'size-3.5')
        <span class="hidden sm:inline">Back to app</span>
    </a>
</div>
