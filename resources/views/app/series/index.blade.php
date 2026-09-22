@php
  $searchTexts = $series->map(fn ($one): string => Str::lower($one->name))->values()->all();
@endphp

<x-app-layout>
  <x-slot:title>
    {{ __('Series') }}
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
        <span class="font-medium text-ink">{{ __('Series') }}</span>
      </div>

      <div class="mb-2 flex items-start justify-between gap-4">
        <div>
          <div class="flex items-center gap-2.5">
            <h1 class="text-[28px] font-semibold tracking-tight text-ink">{{ __('Series') }}</h1>
            <x-help id="series.list" />
            <span class="rounded-full bg-brand/10 px-2.5 py-0.5 text-[11px] font-semibold tracking-wide text-brand uppercase">{{ __('Account-wide') }}</span>
          </div>
          <p class="mt-1 max-w-xl text-[15px] text-muted">{{ __('A series links related items into a broader franchise or body of work, like Harry Potter or Marvel, regardless of type or collection. Unlike sets, a series has no target and no completion.') }}</p>
        </div>

        @if ($series->isNotEmpty())
          <x-button type="button" x-on:click="showAddForm = true; $refs.addName.value = ''" data-test="new-series-button">
            <x-slot:icon>
              <x-lucide-plus class="size-4" />
            </x-slot>
            {{ __('New series') }}
          </x-button>
        @endif
      </div>

      <div class="mt-6 flex flex-col gap-4 rounded-xl border border-hairline bg-sidebar px-4.5 py-4 sm:flex-row sm:gap-3.5">
        <div class="min-w-0 flex-1">
          <div class="mb-1.5 flex items-center gap-2">
            <span class="size-2.5 shrink-0 rounded-sm bg-brand"></span>
            <span class="text-[13px] font-semibold text-ink">{{ __('Series') }}</span>
          </div>
          <p class="text-[13px] leading-relaxed text-muted">{{ __('“What larger franchise does this item belong to?” Spans collections, groups related items. No completion tracking.') }}</p>
        </div>

        <div class="hidden w-px shrink-0 bg-hairline sm:block"></div>

        <div class="min-w-0 flex-1">
          <div class="mb-1.5 flex items-center gap-2">
            <span class="size-2.5 shrink-0 rounded-sm bg-badge-emerald"></span>
            <span class="text-[13px] font-semibold text-ink">{{ __('Set') }}</span>
          </div>
          <p class="text-[13px] leading-relaxed text-muted">{{ __('“Which finite collection is this part of?” Lives in one collection, tracks a target and what is missing.') }}</p>
        </div>
      </div>

      <div id="add-series-panel" x-show="showAddForm" x-cloak class="mt-6 rounded-xl border border-hairline bg-canvas p-6">
        <div class="text-base font-semibold text-ink">{{ __('New series') }}</div>
        <p class="mt-0.5 mb-4 text-[13px] text-muted">{{ __('Name the franchise. Items from any collection in the account can then be linked to it.') }}</p>

        <x-form method="post" :action="route('series.create')" data-test="create-series-form" x-target="series-panel add-series-fields notifications" x-on:ajax:after="showAddForm = document.querySelector('#add-series-fields .text-error') !== null">
          <div id="add-series-fields">
            <div class="mb-5">
              <x-input id="name" x-ref="addName" :label="__('Name')" :placeholder="__('e.g. Star Wars')" :error="$errors->get('name')" required autofocus />
            </div>

            <div class="mb-5">
              <x-label for="add-description">{{ __('Description') }}</x-label>
              <textarea id="add-description" name="description" rows="2" placeholder="{{ __('Optional. What connects these items?') }}" class="mt-1.5 w-full rounded-md border border-hairline bg-input px-3 py-2 text-sm text-ink"></textarea>
              <x-error :messages="$errors->get('description')" class="mt-2" />
            </div>

            <div class="flex justify-end gap-2.5">
              <x-button.secondary type="button" x-on:click="showAddForm = false">
                {{ __('Cancel') }}
              </x-button.secondary>

              <x-button type="submit" data-test="add-series-button">
                {{ __('Create series') }}
              </x-button>
            </div>
          </div>
        </x-form>
      </div>

      <div id="series-panel">
        @if ($series->isEmpty())
          <div class="mt-7 flex flex-col items-center rounded-xl border border-hairline px-6 py-14 text-center" data-test="no-series">
            <div class="mb-5 flex size-16 items-center justify-center rounded-xl bg-card">
              <x-lucide-library class="size-7 text-ink" />
            </div>

            <p class="text-[21px] font-semibold tracking-tight text-ink">{{ __('No series yet') }}</p>
            <p class="mt-2.5 max-w-[480px] text-[15px] leading-relaxed text-muted">
              {{ __('A set lives inside one collection and counts towards a target. A series does neither: it reaches across every collection in the account to gather everything from one franchise, whatever shape it takes.') }}
            </p>

            <div class="mt-7 w-full max-w-[440px] rounded-xl border border-dashed border-hairline bg-sidebar px-5 py-5 text-left">
              <p class="mb-3.5 font-mono text-[11px] tracking-wide text-muted-soft uppercase">{{ __('Example series') }}</p>

              <div class="mb-3.5 flex items-center gap-2.5">
                <span class="size-2.5 shrink-0 rounded-sm bg-brand"></span>
                <span class="text-[15px] font-semibold text-ink">{{ __('Harry Potter') }}</span>
              </div>

              @foreach ([[__('Books'), __('Philosopher\'s Stone, Chamber of Secrets')], [__('Films'), __('Philosopher\'s Stone (4K)')], [__('LEGO'), __('Hogwarts Express 75955')]] as [$exampleCollection, $exampleItems])
                <div class="flex items-baseline gap-2.5 py-1">
                  <span class="w-[52px] shrink-0 text-[11px] font-semibold tracking-wide text-muted-soft uppercase">{{ $exampleCollection }}</span>
                  <span class="text-[13px] text-muted">{{ $exampleItems }}</span>
                </div>
              @endforeach
            </div>

            <x-button type="button" class="mt-7" x-on:click="showAddForm = true" data-test="create-first-series-button">
              <x-slot:icon>
                <x-lucide-plus class="size-4" />
              </x-slot>
              {{ __('Create your first series') }}
            </x-button>
          </div>
        @else
          <div class="mt-7 flex items-center justify-between gap-4">
            <p class="text-[13px] text-muted-soft" data-test="series-count">
              {{ trans_choice(':count series|:count series', $totalCount, ['count' => $totalCount]) }} &middot; {{ trans_choice(':count item linked|:count items linked', $linkedItemCount, ['count' => $linkedItemCount]) }}
            </p>

            <input
              type="search"
              x-model="search"
              placeholder="{{ __('Search series…') }}"
              class="h-10 w-full max-w-[240px] rounded-md border border-hairline bg-input px-3 text-sm text-ink"
              data-test="search-series"
              aria-label="{{ __('Search series') }}"
            />
          </div>

          <div class="mt-4 flex flex-col gap-3.5">
            @foreach ($series as $one)
              @include('app.series._card', ['series' => $one])
            @endforeach
          </div>

          <div x-show="noResults" x-cloak class="p-8 text-center text-sm text-muted" data-test="no-series-results">
            {{ __('No series match your search.') }}
          </div>
        @endif
      </div>
    </div>
  </div>
</x-app-layout>
