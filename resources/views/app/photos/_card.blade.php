{{-- One photo of the library, as a card in the grid. --}}
<div
  x-on:click="open(@js($row['id']))"
  :class="isSelected(@js($row['id'])) ? 'border-ink' : 'border-hairline'"
  class="cursor-pointer overflow-hidden rounded-xl border-[1.5px] bg-canvas transition-[box-shadow,transform] duration-150 hover:-translate-y-0.5 hover:shadow-lg"
  data-test="photo-card-{{ $row['id'] }}"
>
  <div class="relative aspect-4/3 bg-card">
    <img src="{{ $row['url'] }}" alt="{{ $row['filename'] }}" loading="lazy" class="size-full object-cover" />

    <button
      type="button"
      x-on:click.stop="toggle(@js($row['id']))"
      :class="isSelected(@js($row['id'])) ? 'border-ink bg-ink text-canvas' : 'border-black/15 bg-white/90 text-transparent'"
      class="absolute top-2 left-2 flex size-[22px] cursor-pointer items-center justify-center rounded-md border-[1.5px] shadow-sm"
      :aria-pressed="isSelected(@js($row['id']))"
      aria-label="{{ __('Select photo') }}"
      data-test="select-photo-{{ $row['id'] }}"
    >
      @svg('lucide-check', 'size-3.5')
    </button>

    @if ($row['isCover'])
      <span class="absolute top-2 right-2 flex h-[22px] items-center gap-1 rounded-full bg-black/80 px-2 text-[10px] font-semibold tracking-wide text-white">
        @svg('lucide-star', 'size-2.5 fill-amber-400 text-amber-400')
        {{ __('Cover') }}
      </span>
    @endif

    @if ($row['dimensions'])
      <span class="absolute right-0 bottom-2 left-0 text-center font-mono text-[10px] text-white drop-shadow-[0_1px_2px_rgba(0,0,0,0.9)]">{{ $row['dimensions'] }}</span>
    @endif
  </div>

  <div class="px-3 pt-2.5 pb-3">
    <p class="truncate font-mono text-xs font-medium text-ink" title="{{ $row['filename'] }}">{{ $row['filename'] }}</p>

    <div class="mt-1.5 flex min-w-0 items-center gap-1.5">
      <span class="size-[7px] shrink-0 rounded-full bg-brand"></span>
      <span class="truncate text-xs font-medium text-muted">{{ $row['itemName'] }}</span>
    </div>
  </div>
</div>
