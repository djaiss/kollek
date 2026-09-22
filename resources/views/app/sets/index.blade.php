@php
  $searchTexts = $sets->map(fn ($set): string => Str::lower($set->name))->values()->all();
@endphp

<x-app-layout :catalog="$catalog">
  <x-slot:title>
    {{ __('Sets') }}
  </x-slot>

  <div class="px-6 py-8 lg:px-12 lg:py-10">
    <div
      class="mx-auto w-full max-w-4xl"
      x-data="{
        showAddForm: false,
        search: '',
        names: @js($searchTexts),
        get noResults() {
          if (this.search.trim() === '') return false
          const needle = this.search.trim().toLowerCase()
          return ! this.names.some(name => name.includes(needle))
        },
      }"
    >
      <div class="mb-5 flex items-center gap-1.5 text-[13px]">
        <a href="{{ route('collections.index') }}" data-turbo="true" class="font-medium text-muted-soft transition-colors hover:text-ink">{{ __('Collections') }}</a>
        <span class="text-muted-soft">/</span>
        <a href="{{ route('collections.show', $catalog->id) }}" data-turbo="true" class="truncate font-medium text-muted-soft transition-colors hover:text-ink">{{ $catalog->name }}</a>
        <span class="text-muted-soft">/</span>
        <span class="font-medium text-ink">{{ __('Sets') }}</span>
      </div>

      <div class="mb-2 flex items-start justify-between gap-4">
        <div>
          <div class="flex items-center gap-2">
            <h1 class="text-[28px] font-semibold tracking-tight text-ink">{{ __('Sets') }}</h1>
            <x-help id="sets.list" />
          </div>
          <p class="mt-1 max-w-xl text-[15px] text-muted">{{ __('A set is a checklist of the items that belong together: a full run, a series, a want list. KolleK tracks which ones you own and how close you are to complete.') }}</p>
        </div>

        @if ($sets->isNotEmpty())
          <x-button type="button" x-on:click="showAddForm = true; $refs.addName.value = ''" data-test="new-set-button">
            <x-slot:icon>
              <x-lucide-plus class="size-4" />
            </x-slot>
            {{ __('New set') }}
          </x-button>
        @endif
      </div>

      <div id="add-set-panel" x-show="showAddForm" x-cloak class="mt-6 rounded-xl border border-hairline bg-canvas p-6">
        <div class="text-base font-semibold text-ink">{{ __('New set') }}</div>
        <p class="mt-0.5 mb-4 text-[13px] text-muted">{{ __('Give it a target so KolleK can show how complete it is. Leave the target empty to just group items.') }}</p>

        <x-form method="post" :action="route('sets.create', $catalog->id)" data-test="create-set-form" x-target="sets-panel add-set-fields notifications" x-on:ajax:after="showAddForm = document.querySelector('#add-set-fields .text-error') !== null">
          <div id="add-set-fields">
            <div class="mb-5 flex flex-wrap gap-3.5">
              <div class="min-w-[200px] flex-1">
                <x-input id="name" x-ref="addName" :label="__('Name')" :placeholder="__('e.g. Amazing Spider-Man #1-10')" :error="$errors->get('name')" required autofocus />
              </div>

              <div class="w-[120px]">
                <x-input id="target_count" type="number" min="1" :label="__('Target')" helpId="sets.target" helpAlign="right" placeholder="10" :error="$errors->get('target_count')" />
              </div>
            </div>

            <div class="mb-5">
              <x-label for="add-description">{{ __('Description') }}</x-label>
              <textarea id="add-description" name="description" rows="2" placeholder="{{ __('Optional. What belongs in this set?') }}" class="mt-1.5 w-full rounded-md border border-hairline bg-input px-3 py-2 text-sm text-ink"></textarea>
              <x-error :messages="$errors->get('description')" class="mt-2" />
            </div>

            <div class="flex justify-end gap-2.5">
              <x-button.secondary type="button" x-on:click="showAddForm = false">
                {{ __('Cancel') }}
              </x-button.secondary>

              <x-button type="submit" data-test="add-set-button">
                {{ __('Add set') }}
              </x-button>
            </div>
          </div>
        </x-form>
      </div>

      <div id="sets-panel">
        @if ($sets->isEmpty())
          <div class="mt-7 flex flex-col items-center rounded-xl border border-hairline px-6 py-14 text-center" data-test="no-sets">
            <div class="mb-5 flex size-16 items-center justify-center rounded-xl bg-card">
              <x-lucide-list-checks class="size-7 text-ink" />
            </div>

            <p class="text-[21px] font-semibold tracking-tight text-ink">{{ __('No sets yet') }}</p>
            <p class="mt-2.5 max-w-[480px] text-[15px] leading-relaxed text-muted">
              {{ __('Unlike categories, which just group items, a set defines a target: the complete list of what you are chasing. KolleK checks your collection against it, shows a completion bar, and flags how many pieces are still missing.') }}
            </p>

            <div class="mt-7 w-full max-w-[440px] rounded-xl border border-dashed border-hairline bg-sidebar px-5 py-5 text-left">
              <p class="mb-3.5 font-mono text-[11px] tracking-wide text-muted-soft uppercase">{{ __('Example set') }}</p>

              <div class="mb-1.5 flex items-center gap-2.5">
                <span class="size-2.5 shrink-0 rounded-sm bg-badge-orange"></span>
                <span class="text-[15px] font-semibold text-ink">{{ __('Amazing Spider-Man #1-10') }}</span>
              </div>

              <div class="mb-1.5 flex justify-between text-xs text-muted-soft">
                <span>{{ __(':owned of :target owned', ['owned' => 8, 'target' => 10]) }}</span>
                <span>{{ __(':percent% complete', ['percent' => 80]) }}</span>
              </div>

              <div class="mb-3.5 h-2 overflow-hidden rounded-full bg-hairline-soft">
                <div class="h-full bg-badge-orange" style="width: 80%"></div>
              </div>

              @foreach ([[__('Amazing Spider-Man #1'), true], [__('Amazing Spider-Man #2'), true], [__('Amazing Spider-Man #3'), false], [__('Amazing Spider-Man #4'), false]] as [$exampleName, $owned])
                <div class="flex items-center gap-2.5 py-1">
                  <span class="flex size-4 shrink-0 items-center justify-center rounded-[5px] border {{ $owned ? 'border-badge-orange bg-badge-orange' : 'border-hairline' }}">
                    @if ($owned)
                      @svg('lucide-check', 'size-2.5 text-white')
                    @endif
                  </span>
                  <span class="text-[13px] {{ $owned ? 'text-ink' : 'text-muted-soft' }}">{{ $exampleName }}</span>
                </div>
              @endforeach
            </div>

            <x-button type="button" class="mt-7" x-on:click="showAddForm = true" data-test="create-first-set-button">
              <x-slot:icon>
                <x-lucide-plus class="size-4" />
              </x-slot>
              {{ __('Create your first set') }}
            </x-button>
          </div>
        @else
          <div class="mt-7 flex items-center justify-end">
            <input
              type="search"
              x-model="search"
              placeholder="{{ __('Search sets…') }}"
              class="h-10 w-full max-w-[240px] rounded-md border border-hairline bg-input px-3 text-sm text-ink"
              data-test="search-sets"
              aria-label="{{ __('Search sets') }}"
            />
          </div>

          <div class="mt-4 flex flex-col gap-3.5">
            @foreach ($sets as $set)
              @include('app.sets._card', ['set' => $set, 'catalog' => $catalog])
            @endforeach
          </div>

          <div x-show="noResults" x-cloak class="p-8 text-center text-sm text-muted" data-test="no-set-results">
            {{ __('No sets match your search.') }}
          </div>

          <p class="mt-5 text-[13px] text-muted-soft" data-test="sets-count">
            {{ trans_choice(':count set|:count sets', $totalCount, ['count' => $totalCount]) }} &middot; {{ __(':owned of :target items collected', ['owned' => $ownedCount, 'target' => $targetCount]) }}
          </p>
        @endif
      </div>
    </div>
  </div>
</x-app-layout>
