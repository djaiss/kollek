{{-- The side panel showing the details of one photo. --}}
<template x-if="drawer">
  <div
    x-on:click.self="close()"
    x-on:keydown.escape.window="close()"
    class="fixed inset-0 z-40 flex justify-end bg-black/35"
    data-test="photo-drawer"
  >
    <div
      x-transition:enter="transition duration-200 ease-out"
      x-transition:enter-start="translate-x-6 opacity-0"
      x-transition:enter-end="translate-x-0 opacity-100"
      class="flex h-screen w-full max-w-[420px] flex-col overflow-y-auto border-l border-hairline bg-canvas"
      role="dialog"
      aria-modal="true"
    >
      <div class="sticky top-0 z-10 flex items-center justify-between border-b border-hairline bg-canvas px-5 py-4">
        <p class="text-[15px] font-semibold text-ink">{{ __('Photo details') }}</p>

        <button
          type="button"
          x-on:click="close()"
          class="flex size-8 cursor-pointer items-center justify-center rounded-md border border-hairline text-muted transition-colors hover:text-ink"
          aria-label="{{ __('Close') }}"
          data-test="close-photo-drawer"
        >
          @svg('lucide-x', 'size-4')
        </button>
      </div>

      <div class="flex flex-col gap-5 p-5">
        <div class="aspect-4/3 overflow-hidden rounded-xl border border-hairline bg-card">
          <img :src="drawer.url" :alt="drawer.filename" class="size-full object-contain" />
        </div>

        <p class="font-mono text-sm font-medium break-all text-ink" x-text="drawer.filename"></p>

        <div>
          <p class="mb-2.5 text-xs font-semibold tracking-wide text-muted-soft uppercase">{{ __('Belongs to') }}</p>

          <div class="rounded-xl border border-hairline bg-card p-3.5">
            <div class="flex items-center gap-3">
              <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-canvas text-muted">
                @svg('lucide-box', 'size-4.5')
              </span>

              <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-semibold text-ink" x-text="drawer.itemName"></p>
                <p class="truncate text-xs text-muted-soft" x-text="drawer.itemSub"></p>
              </div>
            </div>

            <a
              :href="drawer.itemUrl"
              data-turbo="true"
              class="mt-3.5 flex h-9 items-center justify-center gap-1.5 rounded-md border border-hairline bg-canvas text-[13px] font-semibold text-ink transition-colors hover:bg-card"
              data-test="open-item"
            >
              @svg('lucide-external-link', 'size-3.5')
              {{ __('Open item') }}
            </a>

            <template x-if="drawer.isCover">
              <div class="mt-2.5 flex items-center gap-2 rounded-md border border-amber-200 bg-amber-50 px-3 py-2.5 dark:border-amber-900/60 dark:bg-amber-950/40">
                @svg('lucide-star', 'size-3.5 fill-amber-400 text-amber-400')
                <span class="text-[13px] font-semibold text-amber-700 dark:text-amber-300">{{ __('Cover photo for this item') }}</span>
              </div>
            </template>

            <template x-if="! drawer.isCover">
              <form method="post" :action="drawer.coverUrl" class="mt-2.5">
                @csrf
                @method('put')
                <button
                  type="submit"
                  class="flex w-full cursor-pointer items-center gap-2 rounded-md border border-hairline px-3 py-2.5 text-[13px] font-semibold text-ink transition-colors hover:bg-canvas"
                  data-test="set-cover"
                >
                  @svg('lucide-star', 'size-3.5 text-muted-soft')
                  {{ __('Set as cover photo') }}
                </button>
              </form>
            </template>
          </div>
        </div>

        <div>
          <p class="mb-2.5 text-xs font-semibold tracking-wide text-muted-soft uppercase">{{ __('File details') }}</p>

          <dl class="flex flex-col">
            <template x-if="drawer.dimensions">
              <div class="flex items-center justify-between border-b border-hairline-soft py-2.5">
                <dt class="text-[13px] text-muted">{{ __('Dimensions') }}</dt>
                <dd class="text-[13px] font-medium text-ink"><span x-text="drawer.dimensions"></span> {{ __('px') }}</dd>
              </div>
            </template>

            <div class="flex items-center justify-between border-b border-hairline-soft py-2.5">
              <dt class="text-[13px] text-muted">{{ __('File size') }}</dt>
              <dd class="text-[13px] font-medium text-ink" x-text="drawer.size"></dd>
            </div>

            <div class="flex items-center justify-between border-b border-hairline-soft py-2.5">
              <dt class="text-[13px] text-muted">{{ __('Format') }}</dt>
              <dd class="text-[13px] font-medium text-ink" x-text="drawer.format"></dd>
            </div>

            <div class="flex items-center justify-between border-b border-hairline-soft py-2.5">
              <dt class="text-[13px] text-muted">{{ __('Uploaded') }}</dt>
              <dd class="text-[13px] font-medium text-ink" x-text="drawer.uploadedAt"></dd>
            </div>

            <template x-if="drawer.uploadedBy">
              <div class="flex items-center justify-between border-b border-hairline-soft py-2.5">
                <dt class="text-[13px] text-muted">{{ __('Uploaded by') }}</dt>
                <dd class="text-[13px] font-medium text-ink" x-text="drawer.uploadedBy"></dd>
              </div>
            </template>
          </dl>
        </div>

        <form
          method="post"
          :action="drawer.deleteUrl"
          onsubmit="return confirm('{{ __('Delete this photo? The image file is removed for good and it disappears from its item. This cannot be undone.') }}')"
        >
          @csrf
          @method('delete')
          <button
            type="submit"
            class="flex h-10 w-full cursor-pointer items-center justify-center gap-2 rounded-md border border-error/30 text-[13px] font-semibold text-error transition-colors hover:bg-error/5"
            data-test="delete-photo"
          >
            @svg('lucide-trash-2', 'size-3.5')
            {{ __('Delete photo') }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>
