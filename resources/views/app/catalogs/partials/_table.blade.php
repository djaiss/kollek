@php
    $columns = [
        ['key' => 'condition', 'label' => __('Condition')],
        ['key' => 'location', 'label' => __('Location')],
        ['key' => 'quantity', 'label' => __('Quantity')],
        ['key' => 'value', 'label' => __('Value')],
        ['key' => 'added', 'label' => __('Added')],
    ];
@endphp

<div class="flex min-h-0 flex-1 overflow-hidden">

    <div class="w-[15%] min-w-[150px] shrink-0 overflow-y-auto border-r border-hairline bg-sidebar">
        <p class="border-b border-hairline-soft px-4 py-3.5 text-xs font-semibold tracking-wide text-muted-soft uppercase">{{ __('Filter by location') }}</p>
        <button
            type="button"
            @click="location = 'all'"
            :class="location === 'all' ? 'bg-card text-ink' : 'text-muted hover:text-ink'"
            class="flex w-full cursor-pointer items-center justify-between px-4 py-2.5 text-left text-[13px] font-medium transition-colors"
        >
            <span>{{ __('All') }}</span>
            <span class="text-xs text-muted-soft">{{ $rows->count() }}</span>
        </button>
        @foreach ($locationFilters as $filter)
            <button
                type="button"
                @click="location = @js($filter['label'])"
                :class="location === @js($filter['label']) ? 'bg-card text-ink' : 'text-muted hover:text-ink'"
                class="flex w-full cursor-pointer items-center justify-between px-4 py-2.5 text-left text-[13px] font-medium transition-colors"
            >
                <span class="truncate">{{ $filter['label'] }}</span>
                <span class="ml-2 shrink-0 text-xs text-muted-soft">{{ $filter['count'] }}</span>
            </button>
        @endforeach
    </div>

    <div class="flex w-[42.5%] min-w-0 flex-col">
        <div class="relative flex shrink-0 justify-end border-b border-hairline-soft p-2">
            <button
                type="button"
                @click="columnsOpen = !columnsOpen"
                class="cursor-pointer rounded-md border border-hairline px-3 py-1.5 text-[13px] font-medium text-ink hover:bg-card"
            >{{ __('Columns') }}</button>
            <div
                x-show="columnsOpen"
                x-cloak
                @click.outside="columnsOpen = false"
                x-transition.opacity
                class="absolute top-11 right-2 z-10 flex flex-col gap-0.5 rounded-lg border border-hairline bg-canvas p-1.5 shadow-md"
            >
                @foreach ($columns as $column)
                    <label class="flex cursor-pointer items-center gap-2 rounded px-2.5 py-1.5 text-[13px] text-ink hover:bg-card">
                        <input type="checkbox" x-model="columns.{{ $column['key'] }}" class="rounded border-hairline text-[var(--color-accent)] focus:ring-[var(--color-accent)]/40" />
                        <span class="whitespace-nowrap">{{ $column['label'] }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="flex-1 overflow-auto">
            <table class="w-max min-w-full border-collapse">
                <thead>
                    <tr>
                        <th class="sticky top-0 z-[1] min-w-56 border-b border-hairline bg-canvas px-4 py-2.5 text-left text-xs font-semibold tracking-wide text-muted-soft uppercase">{{ __('Name') }}</th>
                        @foreach ($columns as $column)
                            <th x-show="columns.{{ $column['key'] }}" x-cloak class="sticky top-0 z-[1] min-w-36 border-b border-hairline bg-canvas px-4 py-2.5 text-left text-xs font-semibold tracking-wide text-muted-soft uppercase">{{ $column['label'] }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $row)
                        <tr
                            x-show="rowVisible(@js($row['name']), @js($row['location']))"
                            @click="selectedId = {{ $row['id'] }}"
                            :class="selectedId === {{ $row['id'] }} ? 'bg-card' : 'hover:bg-card/60'"
                            class="cursor-pointer"
                        >
                            <td class="border-b border-hairline-soft px-4 py-2.5 text-sm font-semibold whitespace-nowrap text-ink">{{ $row['name'] }}</td>
                            <td x-show="columns.condition" x-cloak class="border-b border-hairline-soft px-4 py-2.5 text-[13px] whitespace-nowrap text-muted">{{ $row['condition'] }}</td>
                            <td x-show="columns.location" x-cloak class="border-b border-hairline-soft px-4 py-2.5 text-[13px] whitespace-nowrap text-muted">{{ $row['location'] }}</td>
                            <td x-show="columns.quantity" x-cloak class="border-b border-hairline-soft px-4 py-2.5 text-[13px] whitespace-nowrap text-muted">{{ $row['quantity'] }}</td>
                            <td x-show="columns.value" x-cloak class="border-b border-hairline-soft px-4 py-2.5 text-[13px] font-semibold whitespace-nowrap text-ink">{{ $row['value'] }}</td>
                            <td x-show="columns.added" x-cloak class="border-b border-hairline-soft px-4 py-2.5 text-[13px] whitespace-nowrap text-muted">{{ $row['added'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($items->hasPages())
            <div class="flex shrink-0 items-center justify-center gap-3 border-t border-hairline-soft px-4 py-2">
                @if ($items->onFirstPage())
                    <span class="flex size-7 items-center justify-center rounded-md border border-hairline text-muted-soft opacity-50">‹</span>
                @else
                    <a href="{{ $items->previousPageUrl() }}" rel="prev" class="flex size-7 items-center justify-center rounded-md border border-hairline text-muted transition-colors hover:text-ink">‹</a>
                @endif

                <span class="text-[13px] font-medium text-muted">{{ __('Page :current of :last', ['current' => $items->currentPage(), 'last' => $items->lastPage()]) }}</span>

                @if ($items->hasMorePages())
                    <a href="{{ $items->nextPageUrl() }}" rel="next" class="flex size-7 items-center justify-center rounded-md border border-hairline text-muted transition-colors hover:text-ink">›</a>
                @else
                    <span class="flex size-7 items-center justify-center rounded-md border border-hairline text-muted-soft opacity-50">›</span>
                @endif
            </div>
        @endif
    </div>

    <div class="w-[42.5%] min-w-0 shrink-0 overflow-y-auto border-l border-hairline">
        <template x-if="selected">
            <div class="flex items-start gap-6 p-7">
                <template x-if="selected.photoUrl">
                    <img :src="selected.photoUrl" :alt="selected.name" class="aspect-[3/4] w-50 shrink-0 rounded-xl object-cover" />
                </template>
                <template x-if="! selected.photoUrl">
                    <div class="flex aspect-[3/4] w-50 shrink-0 items-center justify-center rounded-xl bg-card text-5xl">{{ $catalog->emoji ?? '📦' }}</div>
                </template>

                <div class="min-w-0 flex-1">
                    <p class="mb-1.5 text-xl leading-snug font-semibold text-ink" x-text="selected.name"></p>
                    <span class="mb-5 inline-block rounded-full bg-card px-2.5 py-1 text-xs font-medium text-ink" x-text="selected.condition"></span>
                    <dl class="flex flex-col">
                        <div class="flex justify-between border-b border-hairline-soft py-2.5">
                            <dt class="text-[13px] text-muted-soft">{{ __('Location') }}</dt>
                            <dd class="text-[13px] font-semibold text-ink" x-text="selected.location"></dd>
                        </div>
                        <div class="flex justify-between border-b border-hairline-soft py-2.5">
                            <dt class="text-[13px] text-muted-soft">{{ __('Quantity') }}</dt>
                            <dd class="text-[13px] font-semibold text-ink" x-text="selected.quantity"></dd>
                        </div>
                        <div class="flex justify-between border-b border-hairline-soft py-2.5">
                            <dt class="text-[13px] text-muted-soft">{{ __('Value') }}</dt>
                            <dd class="text-[13px] font-semibold text-ink" x-text="selected.value"></dd>
                        </div>
                        <div class="flex justify-between py-2.5">
                            <dt class="text-[13px] text-muted-soft">{{ __('Added') }}</dt>
                            <dd class="text-[13px] font-semibold text-ink" x-text="selected.added"></dd>
                        </div>
                    </dl>

                    <a
                        :href="`{{ route('collections.show', $catalog) }}/items/${selected.id}`"
                        data-turbo="true"
                        class="mt-5 inline-flex h-9 items-center rounded-md border border-hairline px-3.5 text-[13px] font-semibold text-ink transition-colors hover:bg-card"
                    >{{ __('View item') }}</a>
                </div>
            </div>
        </template>
        <template x-if="! selected">
            <p class="p-7 text-[13px] text-muted">{{ __('Select an item to see its details.') }}</p>
        </template>
    </div>
</div>
