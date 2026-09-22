@php
    // Only tags the item does not already carry are worth suggesting.
    $usedTagIds = $item->tags->pluck('id')->all();
    $suggestions = $tags->reject(fn ($tag) => in_array($tag->id, $usedTagIds, true));
@endphp

<div id="item-tags" x-merge="replace" class="mb-6 flex flex-wrap items-center gap-2">
  @foreach ($item->tags as $tag)
    @if ($canManage)
      <span class="inline-flex items-center gap-1.5 rounded-full bg-card py-1 pr-2 pl-3 text-[13px] font-medium whitespace-nowrap text-ink" data-test="item-tag-{{ $tag->id }}">
        {{ $tag->name }}

        <x-form
          method="delete"
          :action="route('items.tags.destroy', [$catalog, $item, $tag])"
          x-target="item-tags notifications"
          class="flex"
        >
          <button
            type="submit"
            class="flex size-4 cursor-pointer items-center justify-center rounded-full text-muted-soft transition-colors hover:bg-hairline hover:text-ink"
            aria-label="{{ __('Remove the tag :name', ['name' => $tag->name]) }}"
            data-test="remove-tag-{{ $tag->id }}"
          >
            @svg('lucide-x', 'size-3')
          </button>
        </x-form>
      </span>
    @else
      <x-badge>{{ $tag->name }}</x-badge>
    @endif
  @endforeach

  @if ($canManage)
    <x-form
      method="post"
      :action="route('items.tags.create', [$catalog, $item])"
      x-target="item-tags notifications"
      x-on:ajax:success="$refs.tagInput.value = ''"
      data-test="add-tag-form"
      class="flex"
    >
      <input
        name="name"
        x-ref="tagInput"
        list="item-tag-options"
        autocomplete="off"
        maxlength="255"
        required
        placeholder="{{ __('Add tag + Enter') }}"
        class="h-[30px] w-36 rounded-full border border-dashed border-hairline bg-transparent px-3 text-[13px] text-ink placeholder-muted-soft focus:border-muted-soft focus:outline-none"
        data-test="add-tag-input"
      />

      <datalist id="item-tag-options">
        @foreach ($suggestions as $suggestion)
          <option value="{{ $suggestion->name }}"></option>
        @endforeach
      </datalist>
    </x-form>
  @endif

  <x-error :messages="$errors->get('name')" class="w-full" />
</div>
