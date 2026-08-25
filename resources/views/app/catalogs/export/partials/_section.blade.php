{{--
  One section card: a master checkbox that ticks the whole thing, the count of
  what is ticked inside it, and the field pills.

  The master checkbox is not a field of its own, so it posts under `sections[]`
  while the pills post under `fields[]`. That is what lets a section be left out
  whole without having to untick everything inside it.
--}}
<div class="rounded-xl border border-hairline" data-test="section-{{ $section['key'] }}">
  <div class="flex items-center gap-3 p-4">
    <input
      type="checkbox"
      name="sections[]"
      value="{{ $section['key'] }}"
      :checked="sections['{{ $section['key'] }}']"
      @change="toggleSection('{{ $section['key'] }}', $event.target.checked)"
      class="size-[18px] shrink-0 cursor-pointer rounded accent-ink"
      aria-label="{{ $section['label'] }}"
    />

    <span class="w-3 shrink-0 text-[13px] text-muted-soft">{{ $section['number'] }}</span>

    <div class="min-w-0 flex-1">
      <p class="text-[15px] font-semibold text-ink">{{ $section['label'] }}</p>
      <p class="text-[13px] text-muted">{{ $section['description'] }}</p>
    </div>

    <span class="shrink-0 text-[13px] text-muted-soft" x-text="checkedIn('{{ $section['key'] }}') + ' / {{ $section['count'] }}'">
      {{ $section['count'] }} / {{ $section['count'] }}
    </span>

    <button
      type="button"
      @click="open['{{ $section['key'] }}'] = ! open['{{ $section['key'] }}']"
      :aria-expanded="open['{{ $section['key'] }}'] ? 'true' : 'false'"
      aria-label="{{ __('Show the fields of :section', ['section' => $section['label']]) }}"
      class="flex size-7 shrink-0 cursor-pointer items-center justify-center rounded-md text-muted transition-colors hover:text-ink"
    >
      @svg('lucide-chevron-up', 'size-4 transition-transform duration-150', ['x-bind:class' => "! open['" . $section['key'] . "'] && 'rotate-180'"])
    </button>
  </div>

  <div x-show="open['{{ $section['key'] }}']" x-cloak class="border-t border-hairline p-4">
    @foreach($section['groups'] as $group)
      @if($group['label'] !== null)
        <p class="mb-2 text-[11px] font-semibold tracking-wide text-muted-soft uppercase @if(! $loop->first) mt-4 @endif">{{ $group['label'] }}</p>
      @endif

      <div class="flex flex-wrap gap-2">
        @foreach($group['fields'] as $field)
          <label
            class="flex cursor-pointer items-center gap-2 rounded-full border py-1.5 pr-3.5 pl-2.5 text-[13px] transition-colors"
            :class="fields['{{ $field['key'] }}'] && sections['{{ $section['key'] }}'] ? 'border-hairline bg-card text-ink' : 'border-hairline text-muted-soft'"
          >
            <input
              type="checkbox"
              name="fields[]"
              value="{{ $field['key'] }}"
              x-model="fields['{{ $field['key'] }}']"
              :disabled="! sections['{{ $section['key'] }}']"
              class="size-4 shrink-0 cursor-pointer rounded accent-ink"
            />
            {{ $field['label'] }}
          </label>
        @endforeach
      </div>
    @endforeach
  </div>
</div>
