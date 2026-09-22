@use('App\Enums\PhotoViewEnum')

@php
  $filters = [
      ['value' => 'all', 'label' => __('All'), 'count' => $counts['all']],
      ['value' => 'covers', 'label' => __('Covers'), 'count' => $counts['covers']],
      ['value' => 'extras', 'label' => __('Extras'), 'count' => $counts['extras']],
  ];

  $sorts = [
      'newest' => __('Newest first'),
      'oldest' => __('Oldest first'),
      'largest' => __('Largest file'),
      'smallest' => __('Smallest file'),
  ];

  $views = [
      ['value' => PhotoViewEnum::Grid->value, 'icon' => 'lucide-layout-grid', 'label' => __('Grid view')],
      ['value' => PhotoViewEnum::ByItem->value, 'icon' => 'lucide-list', 'label' => __('Group by item')],
  ];
@endphp

<div class="mt-7 mb-6 flex flex-wrap items-center gap-2.5">
  <div class="flex flex-wrap gap-1.5 rounded-full bg-card p-1.5">
    @foreach ($filters as $option)
      <a
        href="{{ route('settings.photos.index', array_filter(['filter' => $option['value'], 'sort' => $sort, 'q' => $search])) }}"
        data-turbo="true"
        @class([
            'flex items-center gap-1.5 rounded-full px-3.5 py-1.5 text-sm font-medium transition-colors',
            'bg-canvas text-ink shadow-sm' => $filter === $option['value'],
            'text-muted hover:text-ink' => $filter !== $option['value'],
        ])
        data-test="photo-filter-{{ $option['value'] }}"
      >
        {{ $option['label'] }}
        <span class="text-[11px] opacity-70">{{ $option['count'] }}</span>
      </a>
    @endforeach
  </div>

  <div class="flex-1"></div>

  <form method="get" action="{{ route('settings.photos.index') }}" class="flex items-center gap-2.5">
    <input type="hidden" name="filter" value="{{ $filter }}" />

    <div class="relative">
      <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-muted-soft">
        @svg('lucide-search', 'size-4')
      </span>
      <input
        type="search"
        name="q"
        value="{{ $search }}"
        placeholder="{{ __('Search by file or item…') }}"
        maxlength="255"
        class="h-10 w-60 rounded-md border border-hairline bg-canvas pr-3 pl-9 text-sm text-ink placeholder:text-muted-soft focus:border-transparent focus:ring-2 focus:ring-[var(--color-accent)]/40 focus:outline-none"
        data-test="search-photos"
      />
    </div>

    <select
      name="sort"
      x-on:change="$el.form.requestSubmit()"
      class="h-10 cursor-pointer rounded-md border border-hairline bg-canvas px-3 text-[13px] font-medium text-ink focus:outline-none"
      aria-label="{{ __('Sort photos') }}"
      data-test="sort-photos"
    >
      @foreach ($sorts as $value => $label)
        <option value="{{ $value }}" @selected($sort === $value)>{{ $label }}</option>
      @endforeach
    </select>

    <button type="submit" class="sr-only">{{ __('Apply') }}</button>
  </form>

  <div class="flex items-center gap-0.5 rounded-md border border-hairline p-0.5">
    @foreach ($views as $button)
      <button
        type="button"
        x-on:click="switchView(@js($button['value']))"
        :class="view === @js($button['value']) ? 'bg-card text-ink' : 'text-muted hover:text-ink'"
        class="flex size-8 cursor-pointer items-center justify-center rounded transition-colors"
        aria-label="{{ $button['label'] }}"
        data-test="photo-view-{{ $button['value'] }}"
      >
        @svg($button['icon'], 'size-4')
      </button>
    @endforeach
  </div>
</div>
