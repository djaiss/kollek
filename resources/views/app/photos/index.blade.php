@use('App\Enums\PhotoViewEnum')

@php
  $statCards = [
      ['label' => __('Photos'), 'value' => $stats['total'], 'sub' => __('across the account'), 'dot' => 'bg-muted-soft'],
      ['label' => __('Storage used'), 'value' => $stats['storage'], 'sub' => __('total size on disk'), 'dot' => 'bg-badge-violet'],
      ['label' => __('Covers'), 'value' => $stats['covers'], 'sub' => __('one per item'), 'dot' => 'bg-badge-emerald'],
      ['label' => __('Items with photos'), 'value' => $stats['items'], 'sub' => __('have at least one'), 'dot' => 'bg-badge-orange'],
  ];

  // The grouped layout groups the page that was loaded, not the whole library, which
  // is what pagination means: an item's photos can straddle two pages.
  $groups = collect($rows)->groupBy('itemId');
@endphp

<x-app-layout>
  <x-slot:title>
    {{ __('Photos') }}
  </x-slot>

  <div class="px-6 py-8 lg:px-12 lg:py-10">
    <div
      class="mx-auto w-full max-w-6xl"
      x-data="{
        view: @js($view->value),
        selected: [],
        drawerId: null,
        photos: @js($rows),
        get drawer() {
          return this.photos.find((photo) => photo.id === this.drawerId) ?? null
        },
        get selectionLabel() {
          return this.selected.length === 1
            ? @js(__('1 photo selected'))
            : @js(__(':count photos selected')).replace(':count', this.selected.length)
        },
        isSelected(id) {
          return this.selected.includes(id)
        },
        toggle(id) {
          this.selected = this.isSelected(id)
            ? this.selected.filter((selectedId) => selectedId !== id)
            : [...this.selected, id]
        },
        open(id) {
          this.drawerId = id
        },
        close() {
          this.drawerId = null
        },
        switchView(target) {
          this.view = target
          switchPhotoView(target)
        },
      }"
    >
      <input type="hidden" id="photos-view-endpoint" value="{{ route('settings.photos.view.update') }}" />

      <div>
        <div class="flex items-center gap-2">
          <h1 class="text-[28px] font-semibold tracking-tight text-ink">{{ __('Photos') }}</h1>
          <x-help id="settings.photos" />
        </div>
        <p class="mt-1 max-w-xl text-[15px] text-muted">{{ __('Every image uploaded across the account. Manage the library in one place and see exactly which item each photo belongs to.') }}</p>
      </div>

      <div class="mt-6 grid grid-cols-2 gap-3.5 lg:grid-cols-4">
        @foreach ($statCards as $card)
          <div class="rounded-xl border border-hairline bg-canvas px-4 py-4">
            <p class="flex items-center gap-2 text-xs font-semibold tracking-wide text-muted-soft uppercase">
              <span class="size-[7px] rounded-full {{ $card['dot'] }}"></span>
              {{ $card['label'] }}
            </p>
            <p class="mt-2 text-[26px] font-semibold tracking-tight text-ink">{{ $card['value'] }}</p>
            <p class="mt-0.5 text-xs text-muted-soft">{{ $card['sub'] }}</p>
          </div>
        @endforeach
      </div>

      @include('app.photos._toolbar')

      @if ($rows === [])
        <div class="rounded-2xl border border-dashed border-hairline">
          <x-empty-state class="py-14" data-test="no-photos">
            <x-slot:icon>
              <x-lucide-image class="size-6 text-muted" />
            </x-slot>

            @if ($search !== '')
              {{ __('Nothing matches ":query". Try another search, or a different filter.', ['query' => $search]) }}
            @elseif ($filter === 'covers')
              {{ __('No item has a cover photo yet.') }}
            @elseif ($filter === 'extras')
              {{ __('Every photo in the account is the cover of its item.') }}
            @else
              {{ __('No photos yet. Add one from any item and it shows up here.') }}
            @endif
          </x-empty-state>
        </div>
      @else
        <div
          x-show="view === @js(PhotoViewEnum::Grid->value)"
          @style(['display: none' => $view !== PhotoViewEnum::Grid])
          class="grid grid-cols-[repeat(auto-fill,minmax(180px,1fr))] gap-4"
          data-test="photos-grid"
        >
          @foreach ($rows as $row)
            @include('app.photos._card', ['row' => $row])
          @endforeach
        </div>

        <div
          x-show="view === @js(PhotoViewEnum::ByItem->value)"
          @style(['display: none' => $view !== PhotoViewEnum::ByItem])
          class="flex flex-col gap-8"
          data-test="photos-by-item"
        >
          @foreach ($groups as $group)
            @php $first = $group->first(); @endphp

            <div>
              <div class="mb-4 flex items-center gap-3 border-b border-hairline pb-3.5">
                <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-card text-muted">
                  @svg('lucide-box', 'size-4.5')
                </span>

                <div class="min-w-0 flex-1">
                  <p class="truncate text-[15px] font-semibold text-ink">{{ $first['itemName'] }}</p>
                  <p class="truncate text-xs text-muted-soft">{{ $first['itemSub'] }}</p>
                </div>

                <span class="shrink-0 text-[13px] font-medium text-muted-soft">{{ trans_choice(':count photo|:count photos', $group->count(), ['count' => $group->count()]) }}</span>

                <a
                  href="{{ $first['itemUrl'] }}"
                  data-turbo="true"
                  class="flex h-8 shrink-0 items-center gap-1.5 rounded-md border border-hairline bg-canvas px-3 text-[13px] font-semibold text-ink transition-colors hover:bg-card"
                >
                  {{ __('View item') }}
                  @svg('lucide-external-link', 'size-3.5')
                </a>
              </div>

              <div class="grid grid-cols-[repeat(auto-fill,minmax(180px,1fr))] gap-4">
                @foreach ($group as $row)
                  @include('app.photos._card', ['row' => $row])
                @endforeach
              </div>
            </div>
          @endforeach
        </div>

        @include('app.photos._pagination')

        <p class="mt-5 text-[13px] text-muted-soft">{{ trans_choice(':count photo total|:count photos total', $photos->total(), ['count' => $photos->total()]) }}</p>
      @endif

      @include('app.photos._drawer')
      @include('app.photos._selection-bar')
    </div>
  </div>
</x-app-layout>
