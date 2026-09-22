<x-app-layout>
  <x-slot:title>
    {{ __('New collection') }}
  </x-slot>

  <div class="px-6 py-8 lg:px-12 lg:py-10">
    <div class="mx-auto w-full max-w-2xl space-y-8">
      <div class="flex items-center gap-1.5 text-[13px]">
        <a href="{{ route('collections.index') }}" data-turbo="true" class="font-medium text-muted-soft transition-colors hover:text-ink">{{ __('Collections') }}</a>
        <span class="text-muted-soft">/</span>
        <span class="font-medium text-ink">{{ __('New collection') }}</span>
      </div>

      <div>
        <h1 class="text-[28px] font-semibold tracking-tight text-ink">{{ __('Create a collection') }}</h1>
        <p class="mt-1.5 text-[15px] text-muted">{{ __('A collection holds a set of items, give it a name and set who can see it.') }}</p>
      </div>

      <x-form method="post" :action="route('collections.create')" data-turbo="true" data-test="create-collection-form">
        <div x-data="{
          emoji: @js(old('emoji', $emojiOptions[0])),
          visibility: @js(old('visibility', 'shared')),
          selectedTypes: @js(collect(old('collection_type_ids', []))->mapWithKeys(fn ($id) => [(string) $id => true])->all()),
        }" class="space-y-7">
          <div>
            <x-label>{{ __('Cover') }}</x-label>
            <div class="mt-2 flex items-center gap-5">
              <div class="flex size-24 shrink-0 items-center justify-center rounded-xl border border-hairline bg-card text-[44px]" x-text="emoji"></div>
              <div class="flex max-w-md flex-wrap gap-2">
                @foreach ($emojiOptions as $option)
                  <label class="flex size-9 cursor-pointer items-center justify-center rounded-full border text-[19px] transition-colors" :class="emoji === '{{ $option }}' ? 'border-ink bg-card' : 'border-hairline'">
                    <input type="radio" name="emoji" value="{{ $option }}" class="sr-only" x-model="emoji" />
                    {{ $option }}
                  </label>
                @endforeach
              </div>
            </div>
            <x-error :messages="$errors->get('emoji')" class="mt-2" />
          </div>

          <x-input id="name" :label="__('Name')" :placeholder="__('e.g. Marvel Comics 1990s')" :value="old('name')" :error="$errors->get('name')" required autofocus data-test="collection-name-input" />

          <x-textarea id="description" :label="__('Description')" :placeholder="__('What\'s in this collection?')" rows="3" :value="old('description')" :error="$errors->get('description')" />

          <div>
            <div class="flex items-center gap-2">
              <x-label>{{ __('Types') }}</x-label>
              <x-help id="collections.types" />
            </div>
            <p class="mt-0.5 mb-2.5 text-[13px] text-muted-soft">{{ __('Types drive which custom fields apply to items in this collection.') }}</p>
            <div class="flex flex-wrap gap-2">
              @foreach ($types as $type)
                <label class="flex cursor-pointer items-center gap-2 rounded-full border px-3.5 py-2 text-sm font-medium text-ink transition-colors" :class="selectedTypes['{{ $type->id }}'] ? 'border-ink bg-card' : 'border-hairline'">
                  <input type="checkbox" name="collection_type_ids[]" value="{{ $type->id }}" class="sr-only" x-model="selectedTypes['{{ $type->id }}']" />
                  <span class="size-2 shrink-0 rounded-full" style="background-color: {{ $type->color }}"></span>
                  {{ $type->name }}
                </label>
              @endforeach

              <a href="{{ route('settings.types.index') }}" data-turbo="true" class="flex items-center gap-1.5 rounded-full border border-dashed border-hairline px-3.5 py-2 text-sm font-medium text-muted transition-colors hover:text-ink">
                @svg('lucide-plus', 'size-3.5')
                {{ __('New type') }}
              </a>
            </div>
          </div>

          <div class="h-px bg-hairline-soft"></div>

          <div>
            <div class="flex items-center gap-2">
              <x-label>{{ __('Visibility') }}</x-label>
              <x-help id="collections.visibility" />
            </div>
            <div class="mt-2.5 flex flex-col gap-2.5">
              @foreach ($visibilityOptions as $option)
                <label class="flex cursor-pointer items-start gap-3 rounded-lg border px-4 py-3.5 transition-colors" :class="visibility === '{{ $option['key'] }}' ? 'border-ink bg-card' : 'border-hairline'">
                  <input type="radio" name="visibility" value="{{ $option['key'] }}" class="sr-only" x-model="visibility" />
                  <span class="mt-0.5 flex size-[18px] shrink-0 items-center justify-center rounded-full border-2" :class="visibility === '{{ $option['key'] }}' ? 'border-ink' : 'border-muted-soft'">
                    <span class="size-2 rounded-full bg-ink" x-show="visibility === '{{ $option['key'] }}'"></span>
                  </span>
                  <span>
                    <span class="flex items-center gap-2">
                      <span class="text-sm font-semibold text-ink">{{ $option['label'] }}</span>
                      @if ($option['soon'])
                        <x-soon />
                      @endif
                    </span>
                    <span class="mt-0.5 block text-[13px] text-muted">{{ $option['description'] }}</span>
                  </span>
                </label>
              @endforeach
            </div>
            <x-error :messages="$errors->get('visibility')" class="mt-2" />
          </div>

          <div class="max-w-60">
            <x-select id="currency" :label="__('Valuation currency')" helpId="collections.currency" :options="$currencies" :selected="old('currency', $defaultCurrency)" :error="$errors->get('currency')" />
          </div>

          <div class="flex items-center justify-end gap-3 pt-2">
            <x-button.secondary href="{{ route('collections.index') }}" turbo="true">
              {{ __('Cancel') }}
            </x-button.secondary>

            <x-button type="submit" data-test="create-collection-button">
              {{ __('Create collection') }}
            </x-button>
          </div>
        </div>
      </x-form>
    </div>
  </div>
</x-app-layout>
