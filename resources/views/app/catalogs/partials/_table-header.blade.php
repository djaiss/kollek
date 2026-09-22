{{-- Compact collection header for the table view: identity, stats, view switcher, search and add. --}}
@use('App\Enums\ItemViewEnum')

<div class="flex h-13 shrink-0 items-center gap-5 border-b border-hairline bg-page px-6">
    <div class="flex size-[30px] shrink-0 items-center justify-center rounded-lg bg-card text-base">{{ $catalog->emoji ?? '📦' }}</div>

    <div class="flex shrink-0 items-baseline gap-2">
        <h1 class="text-base font-semibold tracking-tight whitespace-nowrap text-ink">{{ $catalog->name }}</h1>
        <x-badge>{{ __(ucfirst($catalog->visibility->value)) }}</x-badge>
    </div>

    <div class="flex shrink-0 items-center gap-4">
        <div class="flex items-baseline gap-1.5">
            <span class="text-sm font-semibold text-ink">{{ number_format($itemCount) }}</span>
            <span class="text-xs text-muted-soft">{{ __('Items') }}</span>
        </div>
        <div class="h-3.5 w-px bg-hairline"></div>
        <div class="flex items-baseline gap-1.5">
            <span class="text-sm font-semibold text-ink">{{ $totalValueLabel }}</span>
            <span class="text-xs text-muted-soft">{{ __('Est. value') }}</span>
        </div>
    </div>

    <div class="flex-1"></div>

    <div class="flex shrink-0 items-center gap-0.5 rounded-md border border-hairline p-0.5">
        @php
            $views = [
                ['value' => ItemViewEnum::List->value, 'icon' => 'lucide-list', 'label' => __('List view')],
                ['value' => ItemViewEnum::Grid->value, 'icon' => 'lucide-layout-grid', 'label' => __('Grid view')],
                ['value' => ItemViewEnum::Table->value, 'icon' => 'lucide-table', 'label' => __('Table view')],
            ];
        @endphp
        @foreach ($views as $button)
            <button
                type="button"
                @click="switchView(@js($button['value']))"
                :class="view === @js($button['value']) ? 'bg-card text-ink' : 'text-muted hover:text-ink'"
                class="flex size-8 cursor-pointer items-center justify-center rounded transition-colors"
                aria-label="{{ $button['label'] }}"
            >
                @svg($button['icon'], 'size-4')
            </button>
        @endforeach
    </div>

    <div class="relative shrink-0">
        <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-muted-soft">
            @svg('lucide-search', 'size-4')
        </span>
        <input
            type="search"
            x-model="q"
            placeholder="{{ __('Search items…') }}"
            class="h-9 w-52 rounded-md border border-hairline bg-canvas pr-3 pl-9 text-sm text-ink placeholder:text-muted-soft focus:border-transparent focus:ring-2 focus:ring-[var(--color-accent)]/40 focus:outline-none"
        />
    </div>

    @if ($canManage)
        <x-button
            :href="$hasReachedItemLimit ? null : route('items.new', $catalog)"
            :type="$hasReachedItemLimit ? 'button' : 'submit'"
            :disabled="$hasReachedItemLimit"
            :title="$hasReachedItemLimit ? __('Unlock the account to add more items.') : null"
            turbo="true"
            data-test="new-item-button"
            class="shrink-0"
        >
            <x-slot:icon>
                <x-lucide-plus class="size-4" />
            </x-slot>
            {{ __('Add item') }}
        </x-button>
    @endif
</div>
