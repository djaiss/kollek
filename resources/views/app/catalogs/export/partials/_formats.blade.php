{{--
  The format cards. Radios rather than buttons, so the choice posts with the form
  and a keyboard reaches it; the card is the label around the input.
--}}
<div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
  @foreach($export->formats() as $option)
    <label
      class="flex cursor-pointer items-start gap-3 rounded-xl border p-4 transition-colors"
      :class="format === '{{ $option['value'] }}' ? 'border-ink bg-card' : 'border-hairline hover:bg-card'"
      data-test="format-{{ $option['value'] }}"
    >
      <input type="radio" name="format" value="{{ $option['value'] }}" x-model="format" class="sr-only" />

      <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-ink text-[9px] font-bold tracking-tight text-canvas">
        {{ $option['badge'] }}
      </span>

      <span class="min-w-0">
        <span class="block text-[15px] font-semibold text-ink">{{ $option['label'] }}</span>
        <span class="mt-0.5 block text-[13px] leading-relaxed text-muted">{{ $option['description'] }}</span>
      </span>
    </label>
  @endforeach
</div>
