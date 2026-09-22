<x-app-layout>
  <x-slot:title>
    {{ __('Trash') }}
  </x-slot>

  @php
    $counts = $entries->countBy(fn (array $entry): string => $entry['type']->value);
  @endphp

  <div class="px-6 py-8 lg:px-12 lg:py-10">
    <div
      class="mx-auto w-full max-w-4xl"
      x-data="{
        search: '',
        activeType: 'all',
        matches(type, haystack) {
          const query = this.search.trim().toLowerCase()

          if (this.activeType !== 'all' && this.activeType !== type) {
            return false
          }

          return query === '' || haystack.includes(query)
        },
        get visibleCount() {
          return [...this.$el.querySelectorAll('[data-trash-row]')]
            .filter((row) => this.matches(row.dataset.trashType, row.dataset.trashName))
            .length
        },
      }"
    >
      <div>
        <div class="flex items-center gap-2">
          <h1 class="text-[28px] font-semibold tracking-tight text-ink">{{ __('Trash') }}</h1>
          <x-help id="settings.trash" />
        </div>
        <p class="mt-1 max-w-xl text-[15px] text-muted">{{ __('Deleted objects are kept here for :count days, then permanently removed. Restore anything before its timer runs out.', ['count' => $retentionDays]) }}</p>
      </div>

      <div id="trash-list" x-merge="replace">
        @if ($entries->isEmpty())
          <div class="mt-8 rounded-lg border border-hairline">
            <x-empty-state data-test="empty-trash">
              <x-slot:icon>
                <x-lucide-trash-2 class="size-6 text-muted" />
              </x-slot>

              {{ __('The trash is empty. Deleted objects will show up here.') }}
            </x-empty-state>
          </div>
        @else
          <div class="mt-7 flex flex-wrap items-center gap-2">
            <button
              type="button"
              x-on:click="activeType = 'all'"
              :class="activeType === 'all' ? 'border-primary bg-primary text-on-primary' : 'border-hairline text-muted hover:text-ink'"
              class="flex h-8.5 cursor-pointer items-center gap-2 rounded-full border px-3.5 text-[13px] font-semibold"
              data-test="trash-filter-all"
            >
              {{ __('All') }}
              <span :class="activeType === 'all' ? 'bg-black/15' : 'bg-card'" class="rounded-full px-1.5 py-px text-xs">{{ $entries->count() }}</span>
            </button>

            @foreach (App\Enums\TrashableEnum::cases() as $type)
              @if ($counts->has($type->value))
                <button
                  type="button"
                  x-on:click="activeType = @js($type->value)"
                  :class="activeType === @js($type->value) ? 'border-primary bg-primary text-on-primary' : 'border-hairline text-muted hover:text-ink'"
                  class="flex h-8.5 cursor-pointer items-center gap-2 rounded-full border px-3.5 text-[13px] font-semibold"
                  data-test="trash-filter-{{ $type->value }}"
                >
                  {{ $type->pluralLabel() }}
                  <span :class="activeType === @js($type->value) ? 'bg-black/15' : 'bg-card'" class="rounded-full px-1.5 py-px text-xs">{{ $counts->get($type->value) }}</span>
                </button>
              @endif
            @endforeach

            <div class="flex-1"></div>

            <input
              type="search"
              x-model="search"
              placeholder="{{ __('Search trash…') }}"
              class="h-9 w-56 rounded-md border border-hairline bg-input px-3 text-sm text-ink"
              data-test="search-trash"
            />

            <x-form
              method="delete"
              :action="route('settings.trash.destroy')"
              x-target="trash-list notifications"
              x-on:ajax:before="confirm(@js(__('Permanently delete everything in the trash? The :count objects below cannot be recovered afterwards.', ['count' => $entries->count()]))) || $event.preventDefault()"
            >
              <button
                type="submit"
                class="flex h-9 cursor-pointer items-center gap-1.5 rounded-md border border-hairline px-3.5 text-[13px] font-semibold text-error hover:bg-card"
                data-test="empty-trash-button"
              >
                @svg('lucide-trash-2', 'size-3.5')
                {{ __('Empty trash') }}
              </button>
            </x-form>
          </div>

          <div class="mt-6 flex items-center gap-4 border-b border-hairline px-1 pb-3 text-xs font-medium tracking-wide text-muted-soft uppercase">
            <span class="min-w-0 flex-1">{{ __('Name') }}</span>
            <span class="w-28 shrink-0">{{ __('Type') }}</span>
            <span class="w-36 shrink-0">{{ __('Deleted') }}</span>
            <span class="w-28 shrink-0">{{ __('Time left') }}</span>
            <span class="w-24 shrink-0"></span>
          </div>

          @foreach ($entries as $entry)
            @include('app.trash._row', ['entry' => $entry])
          @endforeach

          <div x-show="visibleCount === 0" x-cloak class="p-8 text-center text-sm text-muted" data-test="no-trash-results">
            {{ __('Nothing in the trash matches your search.') }}
          </div>

          <p x-show="visibleCount > 0" class="mt-5 text-[13px] text-muted-soft" data-test="trash-count">
            <span x-text="visibleCount"></span>
            <span x-show="visibleCount === 1">{{ __('deleted object') }}</span>
            <span x-show="visibleCount !== 1" x-cloak>{{ __('deleted objects') }}</span>
          </p>
        @endif
      </div>
    </div>
  </div>
</x-app-layout>
