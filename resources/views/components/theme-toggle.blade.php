{{-- The light and dark switch shared by the sidebar and the marketing footer. --}}
<button
    type="button"
    x-data
    @click="$store.theme.toggle()"
    aria-label="{{ __('Toggle theme') }}"
    data-test="theme-toggle"
    {{ $attributes->class(['flex size-8 items-center justify-center rounded-full transition-colors']) }}
>
    <span class="hidden dark:block">@svg('lucide-sun', 'size-4 text-warning')</span>
    <span class="block dark:hidden">@svg('lucide-moon', 'size-4')</span>
</button>
