{{-- The filter bar for the loan list tabs, submitting as a plain GET back to the same tab. --}}
@php($withStatusSort = $withStatusSort ?? false)

<form method="get" action="{{ route('loans.show', ['direction' => $direction->slug(), 'tab' => $tab]) }}" class="mb-4 flex flex-wrap items-center gap-2.5" data-test="loans-filter-bar">
  <div class="relative min-w-[220px] flex-1">
    <x-lucide-search class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-soft" />
    <input
      type="search"
      name="search"
      value="{{ $filters['search'] }}"
      placeholder="{{ __('Search party, item, or copy…') }}"
      class="h-10 w-full rounded-md border border-hairline bg-input pr-3 pl-9 text-sm text-ink placeholder-muted-soft"
      data-test="loans-search"
    />
  </div>

  <select name="collection" class="h-10 rounded-md border border-hairline bg-input px-3 text-sm text-ink" data-test="loans-collection-filter">
    <option value="">{{ __('All collections') }}</option>
    @foreach ($filterCatalogs as $id => $name)
      <option value="{{ $id }}" @selected($filters['catalog'] === $id)>{{ $name }}</option>
    @endforeach
  </select>

  @if ($withStatusSort)
    <select name="status" class="h-10 rounded-md border border-hairline bg-input px-3 text-sm text-ink" data-test="loans-status-filter">
      <option value="">{{ __('All statuses') }}</option>
      <option value="active" @selected($filters['status'] === 'active')>{{ __('Active') }}</option>
      <option value="planned" @selected($filters['status'] === 'planned')>{{ __('Planned') }}</option>
      <option value="overdue" @selected($filters['status'] === 'overdue')>{{ __('Overdue') }}</option>
      <option value="due-soon" @selected($filters['status'] === 'due-soon')>{{ __('Due soon') }}</option>
      <option value="returned" @selected($filters['status'] === 'returned')>{{ __('Returned') }}</option>
      <option value="cancelled" @selected($filters['status'] === 'cancelled')>{{ __('Cancelled') }}</option>
      <option value="lost" @selected($filters['status'] === 'lost')>{{ __('Lost') }}</option>
    </select>

    <select name="sort" class="h-10 rounded-md border border-hairline bg-input px-3 text-sm text-ink" data-test="loans-sort">
      <option value="due" @selected($filters['sort'] === 'due')>{{ __('Sort: due date') }}</option>
      <option value="recent" @selected($filters['sort'] === 'recent')>{{ __('Sort: recently loaned') }}</option>
      <option value="party" @selected($filters['sort'] === 'party')>{{ __('Sort: party') }}</option>
    </select>
  @endif

  <x-button.secondary type="submit" class="!h-10">{{ __('Apply') }}</x-button.secondary>
</form>
