@use('App\Helpers\Palette')

@php
  $category = $node['category'];
  $hasChildren = count($node['children']) > 0;

  // A row cannot become its own parent.
  $rowOptions = collect($parentOptions)
      ->except([$category->id])
      ->all();
@endphp

<div
  x-data="{
    expanded: true,
    editing: false,
    editName: @js($category->name),
    editDescription: @js($category->description ?? ''),
    editParentId: @js((string) ($category->parent_id ?? '')),
    branch: @js(Str::lower($branchText($node))),
    get visible() {
      const needle = search.trim().toLowerCase()
      return needle === '' || this.branch.includes(needle)
    },
  }"
  x-show="visible"
  data-category-row
  class="border-b border-hairline-soft last:border-b-0"
>
  <div class="flex min-h-14 items-stretch {{ $depth === 0 ? 'bg-card/40' : '' }}">
    <div class="flex flex-1 items-center gap-3 py-2.5 pr-3" style="padding-left: {{ 12 + $depth * 28 }}px" data-test="category-row-{{ $category->id }}">
      <div class="flex w-7 shrink-0 items-center justify-center">
        @if ($hasChildren)
          <button type="button" x-on:click="expanded = !expanded" :class="expanded ? 'rotate-90' : 'rotate-0'" class="flex size-7 shrink-0 items-center justify-center rounded-md text-ink transition-transform hover:bg-card" aria-label="{{ __('Toggle subcategories') }}">
            @svg('lucide-chevron-right', 'size-4')
          </button>
        @endif
      </div>

      @if ($depth === 0)
        <span class="size-2.5 shrink-0 rounded-sm" style="background-color: {{ Palette::forId($category->id) }}"></span>
      @else
        <span class="size-2.5 shrink-0 rounded-sm bg-hairline"></span>
      @endif

      <div class="min-w-0 flex-1">
        <div class="{{ $depth === 0 ? 'text-[15px] font-semibold' : 'text-sm font-medium' }} truncate text-ink" data-test="category-name-{{ $category->id }}">{{ $category->name }}</div>
        @if ($hasChildren)
          <div class="text-xs text-muted-soft">{{ trans_choice(':count subcategory|:count subcategories', count($node['children']), ['count' => count($node['children'])]) }}</div>
        @endif
      </div>

      <div class="hidden w-20 shrink-0 text-right text-[13px] font-semibold text-ink sm:block" data-test="category-items-{{ $category->id }}">{{ $category->items_count }}</div>
      <div class="hidden w-28 shrink-0 text-right text-xs text-muted-soft md:block">{{ $category->updated_at?->diffForHumans() ?? '—' }}</div>

      <div class="flex w-[104px] shrink-0 items-center justify-end gap-1.5">
        @if ($depth === 0)
          <button type="button" x-on:click="
            showAddForm = true
            addParentId = '{{ $category->id }}'
            $refs.addName.value = ''
          " class="flex size-8 items-center justify-center rounded-md border border-hairline text-muted hover:bg-card" aria-label="{{ __('Add subcategory') }}" title="{{ __('Add subcategory') }}" data-test="add-subcategory-{{ $category->id }}">
            @svg('lucide-plus', 'size-3.5')
          </button>
        @endif

        <button type="button" x-on:click="editing = !editing" class="flex size-8 items-center justify-center rounded-md border border-hairline text-muted hover:bg-card" aria-label="{{ __('Rename category') }}" title="{{ __('Rename category') }}" data-test="edit-category-{{ $category->id }}">
          @svg('lucide-pencil', 'size-3.5')
        </button>

        <x-form method="delete" :action="route('categories.destroy', [$catalog->id, $category->id])" x-target="categories-panel notifications" x-on:ajax:before="confirm('{{ $hasChildren ? __('Delete this category? Its subcategories go too. Items keep their data but lose this grouping. This cannot be undone.') : __('Delete this category? Items keep their data but lose this grouping. This cannot be undone.') }}') || $event.preventDefault()">
          <button type="submit" class="flex size-8 items-center justify-center rounded-md border border-hairline text-muted hover:bg-card" aria-label="{{ __('Delete category') }}" data-test="delete-category-{{ $category->id }}">
            @svg('lucide-trash-2', 'size-3.5')
          </button>
        </x-form>
      </div>
    </div>
  </div>

  <div x-show="editing" x-cloak class="border-t border-hairline-soft bg-card/40 p-4" style="padding-left: calc({{ 12 + $depth * 28 }}px + 30px)">
    <x-form method="put" :action="route('categories.update', [$catalog->id, $category->id])" data-test="edit-category-form-{{ $category->id }}" x-target="categories-panel notifications" x-on:ajax:after="editing = document.querySelector('[data-test=&quot;edit-category-form-{{ $category->id }}&quot;] .text-error') !== null">
      <div class="mb-3.5 flex flex-wrap gap-3.5">
        <div class="min-w-[180px] flex-1">
          <x-label>{{ __('Name') }}</x-label>
          <input name="name" x-model="editName" placeholder="{{ __('Category name') }}" class="mt-1.5 h-9 w-full rounded-md border border-hairline bg-input px-3 text-sm text-ink" data-test="category-name-input-{{ $category->id }}" />
          <x-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="min-w-[180px]">
          <div class="flex items-center gap-1.5">
            <x-label>{{ __('Parent category') }}</x-label>
            <x-help id="categories.parent" align="right" />
          </div>
          <select name="parent_id" x-model="editParentId" class="mt-1.5 h-9 w-full appearance-none rounded-md border border-hairline bg-input pr-9 pl-3 text-sm text-ink">
            @foreach ($rowOptions as $id => $label)
              <option value="{{ $id }}">{{ $label }}</option>
            @endforeach
          </select>
          <x-error :messages="$errors->get('parent_id')" class="mt-2" />
        </div>
      </div>

      <div class="mb-3.5">
        <x-label>{{ __('Description') }}</x-label>
        <input name="description" x-model="editDescription" placeholder="{{ __('Shown on the category page. Optional.') }}" class="mt-1.5 h-9 w-full rounded-md border border-hairline bg-input px-3 text-sm text-ink" data-test="category-description-input-{{ $category->id }}" />
        <x-error :messages="$errors->get('description')" class="mt-2" />
      </div>

      <div class="flex justify-end gap-2.5">
        <x-button.secondary type="button" x-on:click="editing = false" class="text-[13px]">
          {{ __('Cancel') }}
        </x-button.secondary>

        <x-button type="submit" class="text-[13px]" data-test="save-category-{{ $category->id }}">
          {{ __('Save') }}
        </x-button>
      </div>
    </x-form>
  </div>

  <div x-show="expanded" x-cloak>
    @foreach ($node['children'] as $child)
      @include('app.categories._row', [
          'node' => $child,
          'depth' => $depth + 1,
          'parentOptions' => $parentOptions,
          'catalog' => $catalog,
          'branchText' => $branchText,
      ])
    @endforeach
  </div>
</div>
