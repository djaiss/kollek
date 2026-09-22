@use('App\Helpers\Palette')

@php
    $colour = Palette::forId($category->id);

    // The share is read against the whole collection, so the items filed under no
    // category at all still count towards the denominator.
    $collectionItems = $catalogTotals['items'];
    $collectionValue = $catalogTotals['value'];

    $share = fn (int $part, int $whole): int => $whole === 0 ? 0 : (int) round(($part / $whole) * 100);

    $siblings = collect($categoryBreakdown)->reject(fn (array $row): bool => $row['id'] === $category->id);
@endphp

<div class="mb-7 flex flex-col gap-4 rounded-xl border border-hairline bg-card p-5">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <span class="size-10 shrink-0 rounded-[10px]" style="background-color: {{ $colour }}"></span>

        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
                <h2 class="text-base leading-tight font-semibold text-ink">{{ $category->name }}</h2>
                <span class="flex items-center gap-1.5 rounded-full border border-hairline bg-canvas px-2.5 py-0.5 text-xs font-medium text-muted">
                    <span class="size-1.5 rounded-full" style="background-color: {{ $colour }}"></span>
                    {{ __('Category in :collection', ['collection' => $catalog->name]) }}
                </span>
            </div>

            @if ($category->description)
                <p class="mt-1 max-w-2xl text-[13px] leading-relaxed text-muted">{{ $category->description }}</p>
            @endif
        </div>

        <div class="flex shrink-0 gap-6">
            <div>
                <p class="text-base font-semibold text-ink">{{ number_format($itemCount) }}</p>
                <p class="text-xs text-muted-soft">{{ __('items') }}</p>
            </div>
            <div>
                <p class="text-base font-semibold text-ink">{{ $totalValueLabel }}</p>
                <p class="text-xs text-muted-soft">{{ __('est. value') }}</p>
            </div>
            <div>
                <p class="text-base font-semibold text-ink">{{ $itemCount > 0 && $totalValue > 0 ? $money((int) round($totalValue / $itemCount)) : '—' }}</p>
                <p class="text-xs text-muted-soft">{{ __('avg. item') }}</p>
            </div>
        </div>
    </div>

    <div>
        <div class="mb-2 flex flex-wrap items-baseline justify-between gap-2">
            <p class="text-[13px] text-muted">
                {!! __('<strong>:category</strong> is <strong>:percentage%</strong> of this collection by item count.', [
                    'category' => e($category->name),
                    'percentage' => $share($itemCount, $collectionItems),
                ]) !!}
            </p>
            <p class="text-xs text-muted-soft">{{ __('and :percentage% of its value', ['percentage' => $share($totalValue, $collectionValue)]) }}</p>
        </div>

        <div class="flex h-3 gap-0.5 overflow-hidden rounded-full">
            @foreach ($categoryBreakdown as $row)
                <a
                    href="{{ route('categories.show', [$catalog, $row['id']]) }}"
                    data-turbo="true"
                    title="{{ $row['name'] }}"
                    class="h-full"
                    style="width: {{ $share($row['count'], $collectionItems) }}%; background-color: {{ Palette::forId($row['id']) }}; opacity: {{ $row['id'] === $category->id ? '1' : '0.28' }}"
                ></a>
            @endforeach
            <span class="h-full flex-1 bg-hairline"></span>
        </div>
    </div>

    @if ($siblings->isNotEmpty())
        <div class="flex flex-wrap items-center gap-2.5">
            <span class="text-xs font-semibold tracking-wide text-muted-soft uppercase">{{ __('Other categories') }}</span>
            @foreach ($siblings as $row)
                <a
                    href="{{ route('categories.show', [$catalog, $row['id']]) }}"
                    data-turbo="true"
                    class="flex items-center gap-2 rounded-full border border-hairline bg-canvas py-1.5 pr-3 pl-2.5 text-[13px] font-medium text-ink transition-colors hover:bg-card"
                    data-test="sibling-category-{{ $row['id'] }}"
                >
                    <span class="size-2 shrink-0 rounded-full" style="background-color: {{ Palette::forId($row['id']) }}"></span>
                    <span>{{ $row['name'] }}</span>
                    <span class="text-xs text-muted-soft">{{ number_format($row['count']) }}</span>
                </a>
            @endforeach
        </div>
    @endif
</div>
