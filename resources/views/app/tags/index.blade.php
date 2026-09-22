<x-app-layout>
  <x-slot:title>
    {{ __('Tags') }}
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

          return ! [...this.$el.querySelectorAll('[data-tag-name]')].some((row) => row.dataset.tagName.includes(query))
        },
      }"
    >
      <div>
        <div class="flex items-center gap-2">
          <h1 class="text-[28px] font-semibold tracking-tight text-ink">{{ __('Tags') }}</h1>
          <x-help id="settings.tags" />
        </div>
        <p class="mt-1 max-w-lg text-[15px] text-muted">{{ __('Free-form labels items can carry, e.g. "Signed" or "First Issue". Shared across every collection in this account.') }}</p>
      </div>

      <div class="mt-7 flex flex-wrap items-center gap-2.5">
        <x-form
          method="post"
          :action="route('settings.tags.create')"
          data-test="create-tag-form"
          x-target="add-tag-fields tags-search tags-list notifications"
          x-on:ajax:success="$refs.addInput.value = ''"
          class="flex flex-1 flex-wrap gap-2.5"
        >
          <div id="add-tag-fields" class="min-w-[200px] flex-1">
            <x-input id="name" x-ref="addInput" :placeholder="__('New tag name…')" :error="$errors->get('name')" maxlength="255" required />
          </div>

          <x-button type="submit" data-test="new-tag-button">
            {{ __('New tag') }}
          </x-button>
        </x-form>

        <div id="tags-search">
          @if ($tags->isNotEmpty())
            <input
              type="search"
              x-model="search"
              placeholder="{{ __('Search tags…') }}"
              class="h-10 w-56 rounded-md border border-hairline bg-input px-3 text-sm text-ink"
              data-test="search-tags"
            />
          @endif
        </div>
      </div>

      <div id="tags-list" x-merge="replace">
        @if ($tags->isEmpty())
          <div class="mt-8 rounded-lg border border-hairline">
            <x-empty-state data-test="no-tags">
              <x-slot:icon>
                <x-lucide-tag class="size-6 text-muted" />
              </x-slot>

              {{ __('No tags yet. Add one above.') }}
            </x-empty-state>
          </div>
        @else
          <div class="mt-8 flex items-center gap-3 border-b border-hairline px-5 pb-3 text-xs font-medium tracking-wide text-muted-soft uppercase">
            <span class="size-4 shrink-0"></span>
            <span class="min-w-0 flex-1">{{ __('Name') }}</span>
            <span class="w-28 shrink-0 text-right">{{ __('Updated') }}</span>
            <span class="w-[72px] shrink-0"></span>
          </div>

          @foreach ($tags as $tag)
            @include('app.tags._row', ['tag' => $tag])
          @endforeach

          <div x-show="noResults" x-cloak class="p-8 text-center text-sm text-muted" data-test="no-tag-results">
            {{ __('No tags match your search.') }}
          </div>

          <p id="tags-count" class="mt-5 text-[13px] text-muted-soft">{{ trans_choice(':count tag total|:count tags total', $tags->count(), ['count' => $tags->count()]) }}</p>
        @endif
      </div>
    </div>
  </div>
</x-app-layout>
