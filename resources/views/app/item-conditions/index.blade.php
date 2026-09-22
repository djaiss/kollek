<x-app-layout>
  <x-slot:title>
    {{ __('Item conditions') }}
  </x-slot>

  <div class="px-6 py-8 lg:px-12 lg:py-10">
    <div
      class="mx-auto w-full max-w-3xl"
      x-data="{
        search: '',
        matches(name) {
          const query = this.search.trim().toLowerCase()

          return query === '' || name.includes(query)
        },
        get noResults() {
          const query = this.search.trim().toLowerCase()

          if (query === '') {
            return false
          }

          return ! [...this.$el.querySelectorAll('[data-condition-name]')].some((row) => row.dataset.conditionName.includes(query))
        },
      }"
    >
      <div>
        <div class="flex items-center gap-2">
          <h1 class="text-[28px] font-semibold tracking-tight text-ink">{{ __('Item conditions') }}</h1>
          <x-help id="settings.item_conditions" />
        </div>
        <p class="mt-1 max-w-lg text-[15px] text-muted">{{ __('The condition levels an item\'s copies can be in, e.g. "New", "Used", or "Damaged". Shared across every collection in this account.') }}</p>
      </div>

      <div class="mt-7 flex flex-wrap items-center gap-2.5">
        <x-form
          method="post"
          :action="route('settings.itemConditions.create')"
          data-test="create-condition-form"
          x-target="add-condition-fields conditions-search conditions-list notifications"
          x-on:ajax:success="$refs.addInput.value = ''"
          class="flex flex-1 flex-wrap gap-2.5"
        >
          <div id="add-condition-fields" class="min-w-[200px] flex-1">
            <x-input id="name" x-ref="addInput" :placeholder="__('New condition name…')" :error="$errors->get('name')" maxlength="255" required />
          </div>

          <x-button type="submit" data-test="new-condition-button">
            {{ __('New condition') }}
          </x-button>
        </x-form>

        <div id="conditions-search">
          @if ($conditions->isNotEmpty())
            <input
              type="search"
              x-model="search"
              placeholder="{{ __('Search conditions…') }}"
              class="h-10 w-56 rounded-md border border-hairline bg-input px-3 text-sm text-ink"
              data-test="search-conditions"
            />
          @endif
        </div>
      </div>

      <div id="conditions-list" x-merge="replace">
        @if ($conditions->isEmpty())
          <div class="mt-8 rounded-lg border border-hairline">
            <x-empty-state data-test="no-conditions">
              <x-slot:icon>
                <x-lucide-gauge class="size-6 text-muted" />
              </x-slot>

              {{ __('No conditions yet. Add one above.') }}
            </x-empty-state>
          </div>
        @else
          <div class="mt-8 flex items-center gap-3 border-b border-hairline px-5 pb-3 text-xs font-medium tracking-wide text-muted-soft uppercase">
            <span class="size-4 shrink-0"></span>
            <span class="min-w-0 flex-1">{{ __('Name') }}</span>
            <span class="w-28 shrink-0 text-right">{{ __('Updated') }}</span>
            <span class="w-[72px] shrink-0"></span>
          </div>

          @foreach ($conditions as $condition)
            @include('app.item-conditions._row', ['condition' => $condition])
          @endforeach

          <div x-show="noResults" x-cloak class="p-8 text-center text-sm text-muted" data-test="no-condition-results">
            {{ __('No conditions match your search.') }}
          </div>

          <p id="conditions-count" class="mt-5 text-[13px] text-muted-soft">{{ trans_choice(':count condition total|:count conditions total', $conditions->count(), ['count' => $conditions->count()]) }}</p>
        @endif
      </div>
    </div>
  </div>
</x-app-layout>
