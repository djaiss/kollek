@php
  // Collections have an emoji but no colour, so the breakdown chip dot is derived from the
  // collection id. It only has to be stable and varied, never meaningful.
  $dots = ['bg-badge-orange', 'bg-badge-violet', 'bg-badge-emerald', 'bg-brand', 'bg-badge-pink', 'bg-warning'];

  $breakdown = $series->items
      ->groupBy('catalog_id')
      ->map(fn ($items): array => ['catalog' => $items->first()->catalog, 'count' => $items->count()])
      ->sortBy(fn (array $group): string => Str::lower($group['catalog']->name))
      ->values();
@endphp

<div
  x-data="{
    editing: false,
    name: @js(Str::lower($series->name)),
    get visible() {
      const needle = search.trim().toLowerCase()
      return needle === '' || this.name.includes(needle)
    },
  }"
  x-show="visible"
  class="rounded-xl border border-hairline bg-canvas px-5 py-4"
  data-test="series-card-{{ $series->id }}"
>
  <div class="flex items-start gap-3.5">
    <span class="mt-0.5 flex size-10 shrink-0 items-center justify-center rounded-[10px] bg-brand/10">
      @svg('lucide-library', 'size-[18px] text-brand')
    </span>

    <div class="min-w-0 flex-1">
      <div class="flex flex-wrap items-center gap-x-2.5 gap-y-1">
        <a href="{{ route('series.show', $series->id) }}" data-turbo="true" class="truncate text-base font-semibold text-ink transition-opacity hover:opacity-75" data-test="series-name-{{ $series->id }}">{{ $series->name }}</a>
        <span class="text-xs text-muted-soft" data-test="series-span-{{ $series->id }}">
          {{ trans_choice(':count item|:count items', $series->items->count(), ['count' => $series->items->count()]) }} &middot; {{ trans_choice(':count collection|:count collections', $breakdown->count(), ['count' => $breakdown->count()]) }}
        </span>
      </div>

      @if ($series->description)
        <div class="mt-1 max-w-xl text-[13px] leading-relaxed text-muted">{{ $series->description }}</div>
      @endif

      @if ($breakdown->isNotEmpty())
        <div class="mt-3 flex flex-wrap items-center gap-2">
          @foreach ($breakdown as $group)
            <span class="inline-flex items-center gap-1.5 rounded-full bg-card px-2.5 py-1 text-xs font-medium text-ink">
              <span class="size-[7px] shrink-0 rounded-full {{ $dots[$group['catalog']->id % count($dots)] }}"></span>
              <span class="max-w-[160px] truncate">{{ $group['catalog']->name }}</span>
              <span class="text-muted-soft">{{ $group['count'] }}</span>
            </span>
          @endforeach
        </div>
      @endif
    </div>

    <div class="flex shrink-0 items-center gap-1.5">
      <button type="button" x-on:click="editing = !editing" class="flex size-8 items-center justify-center rounded-md border border-hairline text-muted hover:bg-card" aria-label="{{ __('Rename series') }}" title="{{ __('Rename series') }}" data-test="edit-series-{{ $series->id }}">
        @svg('lucide-pencil', 'size-3.5')
      </button>

      <x-form method="delete" :action="route('series.destroy', $series->id)" x-target="series-panel notifications" x-on:ajax:before="confirm('{{ __('Delete this series? This removes the series and unlinks it from its items. The items themselves stay in your collections. This cannot be undone.') }}') || $event.preventDefault()">
        <button type="submit" class="flex size-8 items-center justify-center rounded-md border border-hairline text-muted hover:bg-card" aria-label="{{ __('Delete series') }}" data-test="delete-series-{{ $series->id }}">
          @svg('lucide-trash-2', 'size-3.5')
        </button>
      </x-form>
    </div>
  </div>

  <div x-show="editing" x-cloak class="mt-4 border-t border-hairline-soft pt-4">
    <x-form method="put" :action="route('series.update', $series->id)" data-test="edit-series-form-{{ $series->id }}" x-target="series-panel notifications" x-on:ajax:after="editing = document.querySelector('[data-test=&quot;edit-series-form-{{ $series->id }}&quot;] .text-error') !== null">
      <div class="mb-3.5">
        <x-label>{{ __('Name') }}</x-label>
        <input name="name" value="{{ $series->name }}" placeholder="{{ __('Series name') }}" class="mt-1.5 h-9 w-full rounded-md border border-hairline bg-input px-3 text-sm text-ink" data-test="series-name-input-{{ $series->id }}" />
        <x-error :messages="$errors->get('name')" class="mt-2" />
      </div>

      <div class="mb-3.5">
        <x-label>{{ __('Description') }}</x-label>
        <textarea name="description" rows="2" placeholder="{{ __('Optional. What connects these items?') }}" class="mt-1.5 w-full rounded-md border border-hairline bg-input px-3 py-2 text-sm text-ink">{{ $series->description }}</textarea>
        <x-error :messages="$errors->get('description')" class="mt-2" />
      </div>

      <div class="flex justify-end gap-2.5">
        <x-button.secondary type="button" x-on:click="editing = false" class="text-[13px]">
          {{ __('Cancel') }}
        </x-button.secondary>

        <x-button type="submit" class="text-[13px]" data-test="save-series-{{ $series->id }}">
          {{ __('Save') }}
        </x-button>
      </div>
    </x-form>
  </div>
</div>
