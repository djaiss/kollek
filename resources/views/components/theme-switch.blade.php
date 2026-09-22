{{-- The light and dark switch used in the marketing footer. --}}
<button
    type="button"
    x-data
    @click="$store.theme.toggle()"
    :aria-pressed="$store.theme.dark"
    aria-label="{{ __('Toggle theme') }}"
    data-test="theme-switch"
    {{ $attributes->class(['group flex items-center gap-x-2.5']) }}
>
    @svg('lucide-sun', 'h-[15px] w-[15px] text-[#f0f0f0] dark:text-[#777777]')

    <span class="relative h-6 w-11 rounded-full border border-[#2f2f2f] bg-[#242424] transition-colors dark:bg-[#3a3a3a]">
        <span class="absolute top-0.5 left-0.5 h-[18px] w-[18px] rounded-full bg-[#eeeeee] shadow transition-transform duration-200 dark:translate-x-[22px]"></span>
    </span>

    @svg('lucide-moon', 'h-[15px] w-[15px] text-[#777777] dark:text-[#f0f0f0]')
</button>
