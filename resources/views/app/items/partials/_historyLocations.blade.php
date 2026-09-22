{{-- Where the copy has been stored over time. --}}

@php
  $user = auth()->user();
  $canManage = $user->account->allowsManagementBy($user);
  $records = $selectedCopy->locationHistory;
@endphp

<div x-data="{ moving: false }">
  <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
    <div class="min-w-0">
      <div class="flex items-center gap-2">
        <p class="text-lg font-semibold text-ink">{{ __('Locations') }}</p>
        <x-help id="history.locations" />
      </div>
      <p class="mt-1 max-w-xl text-[13px] leading-relaxed text-muted">{{ __('Where this copy has been stored, and for how long. Move it to open a new record and close the last.') }}</p>
    </div>

    @if ($canManage)
      <x-button.secondary type="button" x-on:click="moving = ! moving" class="shrink-0 !h-9 !px-4 text-[13px]" data-test="new-location-{{ $selectedCopy->id }}">
        <x-slot:icon>
          <x-lucide-arrow-right-left class="size-4" />
        </x-slot>
        {{ __('Move copy') }}
      </x-button.secondary>
    @endif
  </div>

  @if ($canManage)
    <div x-show="moving" x-cloak class="mb-5">
      @include('app.items.partials._locationMoveForm', [
          'formId' => 'add-location-'.$selectedCopy->id,
          'action' => route('locationHistory.create', [$catalog, $item, $selectedCopy]),
          'method' => 'post',
          'openVar' => 'moving',
          'submitLabel' => __('Move copy'),
          'dataTest' => 'create-location-form-'.$selectedCopy->id,
          'record' => null,
      ])
    </div>
  @endif

  <div class="flex flex-col gap-3.5">
    @forelse ($records as $record)
      @php
        $location = $record->location;
        $open = $record->isOpen();
        $period = $record->moved_at->isoFormat('MMM D, YYYY')
            . ' → ' . ($record->moved_out_at ? $record->moved_out_at->isoFormat('MMM D, YYYY') : __('present'));
        $facts = [
            __('Period') => $period,
            __('Reason') => $record->reason ?? '—',
        ];
      @endphp

      <div @class(['overflow-hidden rounded-xl border border-hairline', 'opacity-60' => ! $open]) x-data="{ editing: false }" data-test="location-{{ $record->id }}">
        <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4">
          <div class="flex min-w-0 flex-wrap items-center gap-2.5">
            @if ($location?->emoji)
              <span class="text-base">{{ $location->emoji }}</span>
            @endif

            <span class="text-[15px] font-semibold text-ink" data-test="location-name-{{ $record->id }}">{{ $location?->name ?? __('Unknown location') }}</span>

            @if ($open)
              <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11.5px] font-semibold" style="background-color: #14b8a624; color: #0f766e" data-test="location-current-{{ $record->id }}">
                <span class="size-1.5 rounded-sm" style="background-color: #14b8a6"></span>
                {{ __('Here now') }}
              </span>
            @endif
          </div>

          @if ($canManage)
            <button type="button" x-on:click="editing = ! editing" class="flex h-8 shrink-0 items-center justify-center rounded-md border border-hairline px-3 text-[13px] font-semibold text-muted hover:bg-card" data-test="edit-location-{{ $record->id }}">
              {{ __('Correct') }}
            </button>
          @endif
        </div>

        <div class="grid grid-cols-1 border-t border-hairline sm:grid-cols-2">
          @foreach ($facts as $label => $value)
            <div class="border-b border-hairline px-5 py-3 last:border-b-0 sm:border-b-0 sm:border-r sm:border-r-hairline sm:last:border-r-0">
              <p class="mb-1 text-[11.5px] text-muted-soft">{{ $label }}</p>
              <p class="truncate text-[13.5px] font-semibold text-ink">{{ $value }}</p>
            </div>
          @endforeach
        </div>

        @if ($record->note)
          <div class="border-t border-hairline px-5 py-3">
            <p class="text-[13px] leading-relaxed text-muted">{{ $record->note }}</p>
          </div>
        @endif

        @if ($canManage)
          <div x-show="editing" x-cloak class="border-t border-hairline bg-card/40 p-4">
            @include('app.items.partials._locationMoveForm', [
                'formId' => 'edit-location-'.$record->id,
                'action' => route('locationHistory.update', [$catalog, $item, $selectedCopy, $record]),
                'deleteAction' => route('locationHistory.destroy', [$catalog, $item, $selectedCopy, $record]),
                'method' => 'put',
                'openVar' => 'editing',
                'submitLabel' => __('Save changes'),
                'dataTest' => 'edit-location-form-'.$record->id,
                'record' => $record,
            ])
          </div>
        @endif
      </div>
    @empty
      <div x-show="! moving" class="rounded-xl border border-hairline">
        <x-empty-state data-test="no-location-{{ $selectedCopy->id }}">
          <x-slot:icon>
            <x-lucide-map-pin class="size-6 text-muted" />
          </x-slot>

          {{ __('No location has been recorded for this copy yet.') }}
        </x-empty-state>
      </div>
    @endforelse
  </div>
</div>
