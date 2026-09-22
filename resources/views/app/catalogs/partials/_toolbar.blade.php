@use('App\Enums\ItemViewEnum')

<div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    @if ($view !== ItemViewEnum::Table && $catalog->categories->isNotEmpty())
        <div class="flex flex-wrap gap-1.5 rounded-full bg-card p-1.5">
            <a
                href="{{ route('collections.show', $catalog) }}"
                data-turbo="true"
                @class([
                    'rounded-full px-3.5 py-1.5 text-sm font-medium transition-colors',
                    'bg-canvas text-ink shadow-sm' => ! $category,
                    'text-muted hover:text-ink' => $category,
                ])
            >{{ __('All') }}</a>
            @foreach ($catalog->categories->sortBy('name') as $option)
                <a
                    href="{{ route('categories.show', [$catalog, $option]) }}"
                    data-turbo="true"
                    @class([
                        'rounded-full px-3.5 py-1.5 text-sm font-medium transition-colors',
                        'bg-canvas text-ink shadow-sm' => $category?->id === $option->id,
                        'text-muted hover:text-ink' => $category?->id !== $option->id,
                    ])
                    data-test="category-filter-{{ $option->id }}"
                >{{ $option->name }}</a>
            @endforeach
        </div>
    @else
        <div></div>
    @endif

    <div class="flex items-center gap-2.5">
        <div class="relative">
            <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-muted-soft">
                @svg('lucide-search', 'size-4')
            </span>
            <input
                type="search"
                x-model="q"
                placeholder="{{ __('Search items…') }}"
                class="h-10 w-52 rounded-md border border-hairline bg-canvas pr-3 pl-9 text-sm text-ink placeholder:text-muted-soft focus:border-transparent focus:ring-2 focus:ring-[var(--color-accent)]/40 focus:outline-none"
            />
        </div>

        <div class="flex items-center gap-0.5 rounded-md border border-hairline p-0.5">
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
    </div>
</div>
