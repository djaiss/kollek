<x-app-layout>
  <x-slot:title>
    {{ __('Locations') }}
  </x-slot>

  <div class="px-6 py-8 lg:px-12 lg:py-10">
    <div class="mx-auto w-full max-w-3xl" x-data="{ showAddForm: false, addParentId: '', addEmoji: '📦' }">
      <div class="mb-2 flex items-start justify-between gap-4">
        <div>
          <div class="flex items-center gap-2">
            <h1 class="text-[28px] font-semibold tracking-tight text-ink">{{ __('Locations') }}</h1>
            <x-help id="locations.list" />
          </div>
          <p class="mt-1 max-w-lg text-[15px] text-muted">{{ __('Where items are physically stored: shelves, boxes, display cases. Locations can be nested.') }}</p>
        </div>

        <x-button type="button" x-on:click="showAddForm = true; addParentId = ''; addEmoji = '📦'; $refs.addName.value = ''" data-test="new-location-button">
          <x-slot:icon>
            <x-lucide-plus class="size-4" />
          </x-slot>
          {{ __('New location') }}
        </x-button>
      </div>

      <div id="add-location-panel" x-show="showAddForm" x-cloak class="mt-6 rounded-xl border border-hairline bg-canvas p-6">
        <div class="text-base font-semibold text-ink">{{ __('New location') }}</div>
        <p class="mt-0.5 mb-4 text-[13px] text-muted">
          <span x-show="addParentId !== ''">{{ __('Adding a sublocation.') }}</span>
          <span x-show="addParentId === ''">{{ __('Adding a top-level location.') }}</span>
        </p>

        <x-form method="post" :action="route('locations.create')" data-test="create-location-form" x-target="locations-tree add-location-fields notifications" x-on:ajax:after="showAddForm = document.querySelector('#add-location-fields .text-error') !== null">
          <div id="add-location-fields">
            <div class="mb-4 flex flex-wrap gap-3.5">
              <div class="min-w-[200px] flex-1">
                <x-input id="name" x-ref="addName" :label="__('Name')" :placeholder="__('e.g. Box A1')" :error="$errors->get('name')" required autofocus />
              </div>

              <div class="min-w-[200px]">
                <x-label for="add-parent-id">{{ __('Parent location') }}</x-label>
                <select id="add-parent-id" name="parent_id" x-model="addParentId" class="mt-1.5 h-10 w-full appearance-none rounded-md border border-hairline bg-input pl-3 pr-9 text-sm text-ink">
                  @foreach ($parentOptions as $id => $label)
                    <option value="{{ $id }}">{{ $label }}</option>
                  @endforeach
                </select>
                <x-error :messages="$errors->get('parent_id')" class="mt-2" />
              </div>
            </div>

            <div class="mb-5">
              <x-label>{{ __('Emoji') }}</x-label>
              <div class="mt-1.5 flex flex-wrap gap-1.5">
                @foreach ($emojiOptions as $option)
                  <label class="flex size-8 cursor-pointer items-center justify-center rounded-lg border text-base transition-colors" :class="addEmoji === '{{ $option }}' ? 'border-ink bg-card' : 'border-hairline'">
                    <input type="radio" name="emoji" value="{{ $option }}" class="sr-only" x-model="addEmoji" />
                    {{ $option }}
                  </label>
                @endforeach
              </div>
              <x-error :messages="$errors->get('emoji')" class="mt-2" />
            </div>
          </div>

          <div class="flex justify-end gap-2.5">
            <x-button.secondary type="button" x-on:click="showAddForm = false">
              {{ __('Cancel') }}
            </x-button.secondary>

            <x-button type="submit" data-test="add-location-button">
              {{ __('Add location') }}
            </x-button>
          </div>
        </x-form>
      </div>

      <div id="locations-tree" class="mt-7 overflow-hidden rounded-xl border border-hairline bg-canvas">
        @forelse ($tree as $node)
          @include('app.locations._row', ['node' => $node, 'depth' => 0, 'parentOptions' => $parentOptions, 'emojiOptions' => $emojiOptions])
        @empty
          <x-empty-state data-test="no-locations">
            <x-slot:icon>
              <x-lucide-map-pin class="size-6 text-muted" />
            </x-slot>
            {{ __('No locations yet. Add one to start organizing where items are stored.') }}
          </x-empty-state>
        @endforelse
      </div>
    </div>
  </div>
</x-app-layout>
