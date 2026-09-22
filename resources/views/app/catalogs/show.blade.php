@use('App\Enums\ItemViewEnum')
@use('App\Helpers\Money')
@use('App\Services\AccountPlan')

@php
    $user = auth()->user();
    $canManage = $user->account->allowsManagementBy($user);

    // Read once and handed to the partials: both view modes need to know whether the
    // account has outgrown the free plan, and the answer costs a count query.
    $plan = new AccountPlan(account: $user->account);
    $isOverFreeLimit = $plan->isOverFreeLimit();
    $hasReachedItemLimit = $isOverFreeLimit && $plan->hasReachedHardLimit();

    // Format an amount held in cents into the collection's currency.
    $money = fn (int $cents): string => Money::format($cents, $catalog->currency);

    // One display row per item, derived from its copies. Condition and location come from the
    // first copy; value is the sum across copies; quantity is the number of copies.
    $rows = $items->getCollection()->map(function ($item) use ($money) {
        $copies = $item->copies;
        $first = $copies->first();
        $valueCents = (int) $copies->sum(fn ($copy): int => $copy->estimatedValue() ?? 0);

        return [
            'id' => $item->id,
            'name' => $item->name,
            'photoUrl' => $item->mainPhoto?->url(),
            'condition' => $first?->itemCondition?->name ?? '—',
            'location' => $first?->currentLocation?->name ?? '—',
            'quantity' => $copies->count(),
            'value' => $valueCents > 0 ? $money($valueCents) : '—',
            'added' => $item->created_at->isoFormat('MMM D, YYYY'),
        ];
    });

    // Distinct locations used on this page, with counts, for the table's left filter pane.
    $locationFilters = $rows
        ->filter(fn (array $row): bool => $row['location'] !== '—')
        ->groupBy('location')
        ->map(fn ($group, string $label): array => ['label' => $label, 'count' => $group->count()])
        ->values();

    $totalValueLabel = $totalValue > 0 ? $money($totalValue) : '—';
@endphp

@if ($view === ItemViewEnum::Table)
    <x-app-layout>
        <x-slot:title>{{ $catalog->name }}</x-slot>
        <x-slot:topNav>
            @include('app.catalogs.partials._top-bar')
        </x-slot>

        <div
            class="flex h-[calc(100vh-3.75rem)] flex-col overflow-hidden"
            x-data="{
                view: @js($view->value),
                serverView: @js($view->value),
                q: '',
                location: 'all',
                selectedId: {{ $rows->first()['id'] ?? 'null' }},
                columns: { condition: true, location: true, quantity: false, value: true, added: false },
                columnsOpen: false,
                items: @js($rows->values()),
                switchView(target) { switchCatalogView(this, target); },
                get selected() { return this.items.find(i => i.id === this.selectedId) ?? null; },
                rowVisible(name, loc) {
                    return (this.location === 'all' || this.location === loc)
                        && (this.q === '' || name.toLowerCase().includes(this.q.toLowerCase()));
                },
            }"
        >
            <input type="hidden" id="collection-view-endpoint" value="{{ route('collections.item-view.update', $catalog) }}" />

            @include('app.catalogs.partials._table-header')

            @if ($isOverFreeLimit)
                <div class="px-6 pt-5">
                    @include('app.catalogs.partials._upgrade-banner')
                </div>
            @endif

            @include('app.catalogs.partials._table')
        </div>
    </x-app-layout>
@else
    <x-app-layout :catalog="$catalog">
        <x-slot:title>{{ $catalog->name }}</x-slot>

        <div
            class="px-6 py-8 lg:px-10"
            x-data="{
                view: @js($view->value),
                serverView: @js($view->value),
                q: '',
                switchView(target) { switchCatalogView(this, target); },
                cardVisible(name) {
                    return this.q === '' || name.toLowerCase().includes(this.q.toLowerCase());
                },
            }"
        >
            @include('app.catalogs.partials._header')

            @if ($isOverFreeLimit)
                @include('app.catalogs.partials._upgrade-banner')
            @endif

            @if ($items->isNotEmpty() || $category)
                @include('app.catalogs.partials._toolbar')
            @endif

            @if ($category)
                @include('app.catalogs.partials._category-panel')
            @endif

            @if ($items->isEmpty())
                <div class="mt-8 rounded-lg border border-hairline">
                    <x-empty-state>
                        <x-slot:icon>
                            <x-lucide-layers class="size-6 text-muted" />
                        </x-slot>
                        @if ($category)
                            {{ __('No items in this category yet.') }}
                        @elseif ($canManage)
                            {{ __('No items yet. Add your first one to start cataloging.') }}
                        @else
                            {{ __('No items yet.') }}
                        @endif
                    </x-empty-state>
                </div>
            @else
                <div x-show="view === '{{ ItemViewEnum::Grid->value }}'" @style(['display: none' => $view !== ItemViewEnum::Grid])>
                    @include('app.catalogs.partials._grid')
                </div>

                <div x-show="view === '{{ ItemViewEnum::List->value }}'" @style(['display: none' => $view !== ItemViewEnum::List])>
                    @include('app.catalogs.partials._list')
                </div>

                @include('app.catalogs.partials._pagination')

                @if ($category)
                    <div class="mt-5 flex flex-wrap items-center justify-between gap-2 text-[13px] text-muted-soft">
                        <span>{{ __('Showing :shown of :total items in :category', ['shown' => number_format($items->count()), 'total' => number_format($itemCount), 'category' => $category->name]) }}</span>
                        <a href="{{ route('collections.show', $catalog) }}" data-turbo="true" class="font-semibold text-ink transition-colors hover:text-muted">{{ __('See all items in collection →') }}</a>
                    </div>
                @endif
            @endif
        </div>
    </x-app-layout>
@endif
