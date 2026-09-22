@php
  $location = $node['location'];
  $hasChildren = count($node['children']) > 0;
  $rowOptions = collect($parentOptions)
    ->except([$location->id])
    ->all();
@endphp

<div x-data="{
  expanded: true,
  editing: false,
  editName: @js($location->name),
  editParentId: @js((string) ($location->parent_id ?? '')),
  editEmoji: @js($location->emoji ?? '📦'),
}" class="border-b border-hairline-soft last:border-b-0">
  <div class="flex min-h-14 items-stretch {{ $depth === 0 ? 'bg-card/40' : '' }}">
    <div id="location-{{ $location->id }}" class="flex flex-1 items-center gap-3 py-2.5 pr-4 scroll-mt-24" style="padding-left: {{ 16 + $depth * 32 }}px" data-test="location-row-{{ $location->id }}">
      <div class="flex w-7 shrink-0 items-center justify-center">
        @if ($hasChildren)
          <button type="button" x-on:click="expanded = !expanded" :class="expanded ? 'rotate-90' : 'rotate-0'" class="flex size-7 shrink-0 items-center justify-center rounded-md text-ink transition-transform hover:bg-card" aria-label="{{ __('Toggle sublocations') }}">
            @svg('lucide-chevron-right', 'size-4')
          </button>
        @endif
      </div>

      <div class="{{ $depth === 0 ? 'size-[34px] text-[17px]' : 'size-7 text-sm' }} flex shrink-0 items-center justify-center rounded-lg bg-card">{{ $location->emoji ?? '📦' }}</div>

      <div class="min-w-0 flex-1">
        <div class="{{ $depth === 0 ? 'text-[15px]' : 'text-sm' }} truncate font-semibold text-ink">{{ $location->name }}</div>
        @if ($hasChildren)
          <div class="text-xs text-muted-soft">{{ trans_choice(':count sublocation|:count sublocations', count($node['children']), ['count' => count($node['children'])]) }}</div>
        @endif
      </div>

      <button type="button" x-on:click="editing = !editing" class="flex h-8 shrink-0 items-center justify-center rounded-md border border-hairline px-3 text-[13px] font-semibold text-muted hover:bg-card" data-test="edit-location-{{ $location->id }}">
        {{ __('Edit') }}
      </button>

      <button type="button" x-on:click="
        showAddForm = true
        addParentId = '{{ $location->id }}'
        addEmoji = '📦'
        $refs.addName.value = ''
      " class="flex size-8 shrink-0 items-center justify-center rounded-md border border-hairline text-muted hover:bg-card" aria-label="{{ __('Add sublocation') }}" title="{{ __('Add sublocation') }}">
        @svg('lucide-plus', 'size-3.5')
      </button>

      <x-form method="delete" :action="route('locations.destroy', $location->id)" x-target="locations-tree notifications" x-on:ajax:before="confirm('{{ __('Delete this location? Nested locations will be deleted too. This cannot be undone.') }}') || $event.preventDefault()">
        <button type="submit" class="flex size-8 shrink-0 items-center justify-center rounded-md border border-hairline text-muted hover:bg-card" aria-label="{{ __('Delete location') }}" data-test="delete-location-{{ $location->id }}">
          @svg('lucide-x', 'size-3.5')
        </button>
      </x-form>
    </div>
  </div>

  <div x-show="editing" x-cloak class="border-t border-hairline-soft bg-card/40 p-4" style="padding-left: calc({{ 16 + $depth * 32 }}px + 30px)">
    <x-form method="put" :action="route('locations.update', $location->id)" data-test="edit-location-form-{{ $location->id }}" x-target="locations-tree notifications" x-on:ajax:after="editing = document.querySelector('[data-test=&quot;edit-location-form-{{ $location->id }}&quot;] .text-error') !== null">
      <div class="mb-3 flex flex-wrap gap-3.5">
        <div class="min-w-[180px] flex-1">
          <x-label>{{ __('Name') }}</x-label>
          <input name="name" x-model="editName" placeholder="{{ __('Location name') }}" class="mt-1.5 h-9 w-full rounded-md border border-hairline bg-input px-3 text-sm text-ink" />
          <x-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="min-w-[180px]">
          <x-label>{{ __('Parent location') }}</x-label>
          <select name="parent_id" x-model="editParentId" class="mt-1.5 h-9 w-full appearance-none rounded-md border border-hairline bg-input pl-3 pr-9 text-sm text-ink">
            @foreach ($rowOptions as $id => $label)
              <option value="{{ $id }}">{{ $label }}</option>
            @endforeach
          </select>
          <x-error :messages="$errors->get('parent_id')" class="mt-2" />
        </div>
      </div>

      <div class="mb-3.5">
        <x-label>{{ __('Emoji') }}</x-label>
        <div class="mt-1.5 flex flex-wrap gap-1.5">
          @foreach ($emojiOptions as $option)
            <label class="flex size-8 cursor-pointer items-center justify-center rounded-lg border text-base transition-colors" :class="editEmoji === '{{ $option }}' ? 'border-ink bg-card' : 'border-hairline'">
              <input type="radio" name="emoji" value="{{ $option }}" class="sr-only" x-model="editEmoji" />
              {{ $option }}
            </label>
          @endforeach
        </div>
        <x-error :messages="$errors->get('emoji')" class="mt-2" />
      </div>

      <div class="flex justify-end gap-2.5">
        <x-button.secondary type="button" x-on:click="editing = false" class="text-[13px]">
          {{ __('Cancel') }}
        </x-button.secondary>

        <x-button type="submit" class="text-[13px]" data-test="save-location-{{ $location->id }}">
          {{ __('Save') }}
        </x-button>
      </div>
    </x-form>
  </div>

  <div x-show="expanded" x-cloak>
    @foreach ($node['children'] as $child)
      @include('app.locations._row', ['node' => $child, 'depth' => $depth + 1, 'parentOptions' => $parentOptions, 'emojiOptions' => $emojiOptions])
    @endforeach
  </div>
</div>
