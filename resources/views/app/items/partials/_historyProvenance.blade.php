{{-- The provenance of the copy: its ownership, custody, origin and authenticity. --}}

@php
  $user = auth()->user();
  $canManage = $user->account->allowsManagementBy($user);
@endphp

<div x-data="{ addingEvent: false }">
  <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
    <div class="min-w-0">
      <div class="flex items-center gap-2">
        <p class="text-lg font-semibold text-ink">{{ __('Provenance') }}</p>
        <x-help id="history.provenance" />
      </div>
      <p class="mt-1 max-w-xl text-[13px] leading-relaxed text-muted">{{ __('Meaningful events in ownership, custody, origin and authenticity. No financial data lives here.') }}</p>
    </div>

    @if ($canManage)
      <x-button type="button" x-on:click="addingEvent = ! addingEvent" class="shrink-0 text-[13px]" data-test="new-provenance-event-{{ $selectedCopy->id }}">
        <x-slot:icon>
          <x-lucide-plus class="size-4" />
        </x-slot>
        {{ __('Add event') }}
      </x-button>
    @endif
  </div>

  @if ($canManage)
    <div x-show="addingEvent" x-cloak class="mb-4">
      @include('app.items.partials._provenanceEventForm', [
          'formId' => 'add-provenance-event-'.$selectedCopy->id,
          'action' => route('provenanceEvents.create', [$catalog, $item, $selectedCopy]),
          'method' => 'post',
          'openVar' => 'addingEvent',
          'submitLabel' => __('Add event'),
          'dataTest' => 'create-provenance-event-form-'.$selectedCopy->id,
          'copy' => $selectedCopy,
          'event' => null,
      ])
    </div>
  @endif

  @forelse ($selectedCopy->provenanceEvents as $event)
    <div class="relative flex gap-4 pb-5 last:pb-0" x-data="{ editingEvent: false }" data-test="provenance-event-{{ $event->id }}">
      <div class="relative flex shrink-0 flex-col items-center">
        <span class="mt-1.5 size-2.5 shrink-0 rounded-full bg-badge-emerald"></span>
        <span class="mt-1 w-px flex-1 bg-hairline"></span>
      </div>

      <div class="min-w-0 flex-1">
        <div class="flex flex-wrap items-start justify-between gap-2.5">
          <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-1.5">
              <x-badge data-test="provenance-event-type-{{ $event->id }}">{{ $event->type->label() }}</x-badge>

              @if ($event->is_verified)
                <x-badge color="success" data-test="provenance-event-verified-{{ $event->id }}">{{ __('Verified') }}</x-badge>
              @endif
            </div>

            <p class="mt-1.5 text-sm font-semibold text-ink" data-test="provenance-event-title-{{ $event->id }}">{{ $event->title }}</p>
          </div>

          <div class="flex items-start gap-2.5">
            <span class="shrink-0 pt-1 font-mono text-xs text-muted-soft" data-test="provenance-event-date-{{ $event->id }}">{{ $event->shortDate() }}</span>

            @if ($canManage)
              <button type="button" x-on:click="editingEvent = ! editingEvent" class="flex h-8 shrink-0 items-center justify-center rounded-md border border-hairline px-3 text-[13px] font-semibold text-muted hover:bg-card" data-test="edit-provenance-event-{{ $event->id }}">
                {{ __('Edit') }}
              </button>
            @endif
          </div>
        </div>

        <p class="mt-1 text-xs text-muted-soft" data-test="provenance-event-full-date-{{ $event->id }}">{{ $event->formattedDate() }}</p>

        @if ($event->from_party || $event->to_party)
          <p class="mt-1.5 text-[13px] text-muted" data-test="provenance-event-parties-{{ $event->id }}">
            {{ __('From :from to :to', ['from' => $event->from_party ?? __('someone unrecorded'), 'to' => $event->to_party ?? __('someone unrecorded')]) }}
          </p>
        @endif

        @if ($event->location)
          <p class="mt-1 text-[13px] text-muted" data-test="provenance-event-location-{{ $event->id }}">{{ $event->location }}</p>
        @endif

        @if ($event->description)
          <p class="mt-1.5 text-[13px] leading-relaxed text-muted">{{ $event->description }}</p>
        @endif

        @if ($event->is_verified && $event->verification_note)
          <p class="mt-1.5 text-[13px] leading-relaxed text-muted" data-test="provenance-event-verification-{{ $event->id }}">{{ $event->verification_note }}</p>
        @endif

        <div class="mt-2 flex flex-wrap items-center gap-2">
          @if ($event->reference_number)
            <span class="rounded-md bg-card px-2 py-0.5 font-mono text-xs text-muted" data-test="provenance-event-reference-{{ $event->id }}">{{ $event->reference_number }}</span>
          @endif

          @if ($event->transaction)
            <span class="text-xs text-muted-soft" data-test="provenance-event-transaction-{{ $event->id }}">
              {{ __('Linked to the :type transaction of :date. Deleting that transaction keeps this event and only unlinks it.', ['type' => $event->transaction->type->label(), 'date' => $event->transaction->occurred_at->isoFormat('ll')]) }}
            </span>
          @endif
        </div>

        @if ($canManage)
          <div x-show="editingEvent" x-cloak class="mt-3 rounded-xl border border-hairline bg-card/40 p-4">
            @include('app.items.partials._provenanceEventForm', [
                'formId' => 'edit-provenance-event-'.$event->id,
                'action' => route('provenanceEvents.update', [$catalog, $item, $selectedCopy, $event]),
                'deleteAction' => route('provenanceEvents.destroy', [$catalog, $item, $selectedCopy, $event]),
                'method' => 'put',
                'openVar' => 'editingEvent',
                'submitLabel' => __('Save changes'),
                'dataTest' => 'edit-provenance-event-form-'.$event->id,
                'copy' => $selectedCopy,
                'event' => $event,
            ])
          </div>
        @endif

        <div class="mt-3 border-t border-hairline pt-3">
          <p class="mb-2 text-[11px] font-semibold tracking-wide text-muted-soft uppercase">{{ __('Documents') }}</p>
          @include('app.items.partials._documentsFor', ['documentable' => $event, 'catalog' => $catalog, 'item' => $item, 'selectedCopy' => $selectedCopy, 'canManage' => $canManage])
        </div>
      </div>
    </div>
  @empty
    <div x-show="! addingEvent" class="rounded-xl border border-hairline">
      <x-empty-state data-test="no-provenance-{{ $selectedCopy->id }}">
        <x-slot:icon>
          <x-lucide-scroll-text class="size-6 text-muted" />
        </x-slot>

        {{ __('No provenance event has been recorded against this copy yet.') }}
      </x-empty-state>
    </div>
  @endforelse
</div>
