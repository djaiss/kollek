{{-- The floating bar of actions for the photos currently selected. --}}
<div
  x-cloak
  x-show="selected.length > 0"
  x-transition:enter="transition duration-200 ease-out"
  x-transition:enter-start="translate-y-3 opacity-0"
  x-transition:enter-end="translate-y-0 opacity-100"
  class="fixed bottom-6 left-1/2 z-30 flex -translate-x-1/2 items-center gap-1.5 rounded-xl bg-ink py-2 pr-2 pl-4 shadow-2xl"
  data-test="photo-selection-bar"
>
  <span class="mr-2 text-[13px] font-semibold text-canvas" x-text="selectionLabel"></span>

  <form
    method="post"
    action="{{ route('settings.photos.selection.destroy') }}"
    onsubmit="return confirm('{{ __('Delete the selected photos? Their image files are removed for good and they disappear from their items. This cannot be undone.') }}')"
  >
    @csrf
    @method('delete')

    <template x-for="id in selected" :key="id">
      <input type="hidden" name="ids[]" :value="id" />
    </template>

    <button
      type="submit"
      class="flex h-9 cursor-pointer items-center gap-1.5 rounded-md bg-error px-3 text-[13px] font-semibold text-white transition-opacity hover:opacity-90"
      data-test="bulk-delete-photos"
    >
      @svg('lucide-trash-2', 'size-3.5')
      {{ __('Delete') }}
    </button>
  </form>

  <button
    type="button"
    x-on:click="selected = []"
    class="flex size-9 cursor-pointer items-center justify-center rounded-md text-muted-soft transition-colors hover:text-canvas"
    aria-label="{{ __('Clear selection') }}"
    data-test="clear-photo-selection"
  >
    @svg('lucide-x', 'size-4')
  </button>
</div>
