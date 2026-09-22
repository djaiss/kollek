@php
  $searchable = mb_strtolower($tag->name);
@endphp

<div
  x-data="{
    editing: false,
    confirmMessage: @js(__('Delete this tag? It will be removed from every item it\'s applied to. This cannot be undone.')),
    edit() {
      this.editing = true
      this.$nextTick(() => {
        this.$refs.input.focus()
        this.$refs.input.select()
      })
    },
    save() {
      if (! this.editing) {
        return
      }

      this.editing = false
      this.$refs.form.requestSubmit()
    },
    cancel() {
      this.editing = false
      this.$refs.input.value = @js($tag->name)
    },
  }"
  x-show="matches($el.dataset.tagName)"
  data-tag-name="{{ $searchable }}"
  id="tag-{{ $tag->id }}"
  data-test="tag-row-{{ $tag->id }}"
  class="flex scroll-mt-24 items-center gap-3 border-b border-hairline-soft px-5 py-3 last:border-b-0"
>
  @svg('lucide-tag', 'size-4 shrink-0 text-muted-soft')

  <x-form
    method="put"
    :action="route('settings.tags.update', $tag->id)"
    x-ref="form"
    x-target="tags-search tags-list notifications"
    class="min-w-0 flex-1"
  >
    <input
      name="name"
      value="{{ $tag->name }}"
      maxlength="255"
      required
      x-ref="input"
      :readonly="! editing"
      :class="editing ? 'border-hairline bg-input' : 'cursor-default border-transparent bg-transparent'"
      x-on:blur="save()"
      x-on:keydown.enter.prevent="save()"
      x-on:keydown.escape.prevent="cancel()"
      class="w-full truncate rounded-md border px-2 py-1 text-sm font-semibold text-ink focus:outline-none"
      data-test="tag-name-{{ $tag->id }}"
    />
  </x-form>

  <span class="w-28 shrink-0 text-right text-xs text-muted-soft">{{ $tag->updated_at?->diffForHumans() }}</span>

  <div class="flex w-[72px] shrink-0 items-center justify-end gap-1.5">
    <button
      type="button"
      x-on:click="edit()"
      class="flex size-8 items-center justify-center rounded-md border border-hairline text-muted hover:bg-card"
      aria-label="{{ __('Rename tag') }}"
      title="{{ __('Rename') }}"
      data-test="edit-tag-{{ $tag->id }}"
    >
      @svg('lucide-pencil', 'size-3.5')
    </button>

    <x-form
      method="delete"
      :action="route('settings.tags.destroy', $tag->id)"
      x-target="tags-search tags-list notifications"
      x-on:ajax:before="confirm(confirmMessage) || $event.preventDefault()"
    >
      <button
        type="submit"
        class="flex size-8 items-center justify-center rounded-md border border-hairline text-muted hover:bg-card"
        aria-label="{{ __('Delete tag') }}"
        title="{{ __('Delete') }}"
        data-test="delete-tag-{{ $tag->id }}"
      >
        @svg('lucide-x', 'size-3.5')
      </button>
    </x-form>
  </div>
</div>
