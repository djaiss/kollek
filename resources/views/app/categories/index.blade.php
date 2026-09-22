@php
  // A branch stays visible while searching if the branch itself or anything nested under it
  // matches, so a hit on a subcategory does not hide the parent it lives in.
  $branchText = function (array $node) use (&$branchText): string {
      return collect($node['children'])
          ->reduce(fn (string $carry, array $child): string => $carry . ' ' . $branchText($child), $node['category']->name);
  };

  $searchTexts = collect($tree)->map(fn (array $node): string => Str::lower($branchText($node)))->values()->all();
@endphp

<x-app-layout :catalog="$catalog">
  <x-slot:title>
    {{ __('Categories') }}
  </x-slot>

  <div class="px-6 py-8 lg:px-12 lg:py-10">
    <div
      class="mx-auto w-full max-w-4xl"
      x-data="{
        showAddForm: false,
        addParentId: '',
        search: '',
        branches: @js($searchTexts),
        get noResults() {
          if (this.search.trim() === '') return false
          const needle = this.search.trim().toLowerCase()
          return ! this.branches.some(text => text.includes(needle))
        },
      }"
    >
      <div class="mb-5 flex items-center gap-1.5 text-[13px]">
        <a href="{{ route('collections.index') }}" data-turbo="true" class="font-medium text-muted-soft transition-colors hover:text-ink">{{ __('Collections') }}</a>
        <span class="text-muted-soft">/</span>
        <a href="{{ route('collections.show', $catalog->id) }}" data-turbo="true" class="truncate font-medium text-muted-soft transition-colors hover:text-ink">{{ $catalog->name }}</a>
        <span class="text-muted-soft">/</span>
        <span class="font-medium text-ink">{{ __('Categories') }}</span>
      </div>

      <div class="mb-2 flex items-start justify-between gap-4">
        <div>
          <div class="flex items-center gap-2">
            <h1 class="text-[28px] font-semibold tracking-tight text-ink">{{ __('Categories') }}</h1>
            <x-help id="categories.list" />
          </div>
          <p class="mt-1 max-w-xl text-[15px] text-muted">{{ __('Group the items in this collection into a browsable structure, like folders. An item sits in one category, and categories can nest.') }}</p>
        </div>

        @if ($tree !== [])
          <x-button type="button" x-on:click="showAddForm = true; addParentId = ''; $refs.addName.value = ''" data-test="new-category-button">
            <x-slot:icon>
              <x-lucide-plus class="size-4" />
            </x-slot>
            {{ __('New category') }}
          </x-button>
        @endif
      </div>

      <div id="add-category-panel" x-show="showAddForm" x-cloak class="mt-6 rounded-xl border border-hairline bg-canvas p-6">
        <div class="text-base font-semibold text-ink">{{ __('New category') }}</div>
        <p class="mt-0.5 mb-4 text-[13px] text-muted">
          <span x-show="addParentId !== ''">{{ __('Adding a subcategory.') }}</span>
          <span x-show="addParentId === ''">{{ __('Adding a top-level category.') }}</span>
        </p>

        <x-form method="post" :action="route('categories.create', $catalog->id)" data-test="create-category-form" x-target="categories-panel add-category-fields notifications" x-on:ajax:after="showAddForm = document.querySelector('#add-category-fields .text-error') !== null">
          <div id="add-category-fields">
            <div class="mb-5 flex flex-wrap gap-3.5">
              <div class="min-w-[200px] flex-1">
                <x-input id="name" x-ref="addName" :label="__('Name')" :placeholder="__('e.g. Spider-Man')" :error="$errors->get('name')" required autofocus />
              </div>

              <div class="min-w-[200px]">
                <div class="flex items-center gap-1.5">
                  <x-label for="add-parent-id">{{ __('Parent category') }}</x-label>
                  <x-help id="categories.parent" align="right" />
                </div>
                <select id="add-parent-id" name="parent_id" x-model="addParentId" class="mt-1.5 h-10 w-full appearance-none rounded-md border border-hairline bg-input pr-9 pl-3 text-sm text-ink">
                  @foreach ($parentOptions as $id => $label)
                    <option value="{{ $id }}">{{ $label }}</option>
                  @endforeach
                </select>
                <x-error :messages="$errors->get('parent_id')" class="mt-2" />
              </div>
            </div>

            <div class="mb-5">
              <x-input id="description" :label="__('Description')" :placeholder="__('e.g. Wall-crawler key issues and full runs.')" :error="$errors->get('description')" />
              <p class="mt-1.5 text-xs text-muted-soft">{{ __('Shown on the category page. Optional.') }}</p>
            </div>

            <div class="flex justify-end gap-2.5">
              <x-button.secondary type="button" x-on:click="showAddForm = false">
                {{ __('Cancel') }}
              </x-button.secondary>

              <x-button type="submit" data-test="add-category-button">
                {{ __('Add category') }}
              </x-button>
            </div>
          </div>
        </x-form>
      </div>

      <div id="categories-panel">
        @if ($tree === [])
          <div class="mt-7 flex flex-col items-center rounded-xl border border-hairline px-6 py-14 text-center" data-test="no-categories">
            <div class="mb-5 flex size-16 items-center justify-center rounded-xl bg-card">
              <x-lucide-folder-tree class="size-7 text-ink" />
            </div>

            <p class="text-[21px] font-semibold tracking-tight text-ink">{{ __('No categories yet') }}</p>
            <p class="mt-2.5 max-w-[460px] text-[15px] leading-relaxed text-muted">
              {{ __('Categories let you shelve items into meaningful groups instead of one long list. Filter by them, and nest them a level deep for finer structure.') }}
            </p>

            <div class="mt-7 w-full max-w-[440px] rounded-xl border border-dashed border-hairline bg-sidebar px-5 py-5 text-left">
              <p class="mb-3.5 font-mono text-[11px] tracking-wide text-muted-soft uppercase">{{ __('Example structure') }}</p>

              @foreach ([
                  ['name' => __('Spider-Man'), 'count' => 58, 'dot' => 'bg-badge-orange', 'children' => [[__('Amazing Spider-Man'), 34], [__('Spectacular Spider-Man'), 24]]],
                  ['name' => __('X-Men'), 'count' => 47, 'dot' => 'bg-badge-violet', 'children' => [[__('Uncanny X-Men'), 29]]],
                  ['name' => __('Infinity Saga'), 'count' => 21, 'dot' => 'bg-badge-emerald', 'children' => []],
              ] as $example)
                <div class="flex items-center gap-2.5 py-1">
                  <span class="size-2.5 shrink-0 rounded-sm {{ $example['dot'] }}"></span>
                  <span class="text-sm font-semibold text-ink">{{ $example['name'] }}</span>
                  <span class="text-xs text-muted-soft">{{ trans_choice(':count item|:count items', $example['count'], ['count' => $example['count']]) }}</span>
                </div>
                @foreach ($example['children'] as [$childName, $childCount])
                  <div class="flex items-center gap-2.5 py-1 pl-6">
                    <span class="h-px w-3.5 shrink-0 bg-hairline"></span>
                    <span class="text-[13px] text-muted">{{ $childName }}</span>
                    <span class="text-xs text-muted-soft">{{ $childCount }}</span>
                  </div>
                @endforeach
              @endforeach
            </div>

            <x-button type="button" class="mt-7" x-on:click="showAddForm = true; addParentId = ''" data-test="create-first-category-button">
              <x-slot:icon>
                <x-lucide-plus class="size-4" />
              </x-slot>
              {{ __('Create your first category') }}
            </x-button>
          </div>
        @else
          <div class="mt-7 flex items-center justify-end">
            <input
              type="search"
              x-model="search"
              placeholder="{{ __('Search categories…') }}"
              class="h-10 w-full max-w-[240px] rounded-md border border-hairline bg-input px-3 text-sm text-ink"
              data-test="search-categories"
              aria-label="{{ __('Search categories') }}"
            />
          </div>

          <div class="mt-4 flex items-center gap-4 border-b border-hairline px-3 pb-2.5 text-xs font-semibold tracking-wide text-muted-soft uppercase">
            <div class="flex-1">{{ __('Category') }}</div>
            <div class="hidden w-20 text-right sm:block">{{ __('Items') }}</div>
            <div class="hidden w-28 text-right md:block">{{ __('Updated') }}</div>
            <div class="w-[104px]"></div>
          </div>

          <div class="overflow-hidden rounded-b-xl border-x border-b border-hairline bg-canvas">
            @foreach ($tree as $node)
              @include('app.categories._row', [
                  'node' => $node,
                  'depth' => 0,
                  'parentOptions' => $parentOptions,
                  'catalog' => $catalog,
                  'branchText' => $branchText,
              ])
            @endforeach
          </div>

          <div x-show="noResults" x-cloak class="p-8 text-center text-sm text-muted" data-test="no-category-results">
            {{ __('No categories match your search.') }}
          </div>

          <p class="mt-5 text-[13px] text-muted-soft" data-test="categories-count">
            {{ __(':count top-level', ['count' => $topLevelCount]) }} &middot; {{ trans_choice(':count category total|:count categories total', $totalCount, ['count' => $totalCount]) }}
          </p>
        @endif
      </div>
    </div>
  </div>
</x-app-layout>
