{{-- The work performed on the copy: cleanings, repairs, servicings and inspections. --}}

@use('App\Helpers\Money')

@php
  $user = auth()->user();
  $canManage = $user->account->allowsManagementBy($user);
  $records = $selectedCopy->maintenanceRecords;
@endphp

<div x-data="{ adding: false }">
  <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
    <div class="min-w-0">
      <div class="flex items-center gap-2">
        <p class="text-lg font-semibold text-ink">{{ __('Maintenance') }}</p>
        <x-help id="history.maintenance" />
      </div>
      <p class="mt-1 max-w-xl text-[13px] leading-relaxed text-muted">{{ __('The work done on this copy over time, with the condition before and after each job.') }}</p>
    </div>

    @if ($canManage)
      <x-button.secondary type="button" x-on:click="adding = ! adding" class="shrink-0 !h-9 !px-4 text-[13px]" data-test="new-maintenance-{{ $selectedCopy->id }}">
        <x-slot:icon>
          <x-lucide-plus class="size-4" />
        </x-slot>
        {{ __('Maintenance record') }}
      </x-button.secondary>
    @endif
  </div>

  @if ($canManage)
    <div x-show="adding" x-cloak class="mb-5">
      @include('app.items.partials._maintenanceRecordForm', [
          'formId' => 'add-maintenance-'.$selectedCopy->id,
          'action' => route('maintenanceRecords.create', [$catalog, $item, $selectedCopy]),
          'method' => 'post',
          'openVar' => 'adding',
          'submitLabel' => __('Add record'),
          'dataTest' => 'create-maintenance-form-'.$selectedCopy->id,
          'record' => null,
      ])
    </div>
  @endif

  <div class="flex flex-col gap-3.5">
    @forelse ($records as $record)
      @php
        $typeColor = $record->type->color();
        $before = $record->itemConditionBefore?->name;
        $after = $record->itemConditionAfter?->name;
        $dueSoon = $record->isDueSoon();
        $facts = [
            __('Performed by') => $record->performed_by ?? '—',
            __('Performed') => $record->performed_at ? $record->performed_at->isoFormat('MMM D, YYYY') : '—',
            __('Condition') => ($before || $after) ? (($before ?? '—') . ' → ' . ($after ?? '—')) : '—',
            __('Next due') => $record->next_due_at ? $record->next_due_at->isoFormat('MMM D, YYYY') : '—',
        ];
      @endphp

      <div class="overflow-hidden rounded-xl border border-hairline" x-data="{ editing: false }" data-test="maintenance-{{ $record->id }}">
        <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4">
          <div class="flex min-w-0 flex-wrap items-center gap-2.5">
            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11.5px] font-semibold capitalize" style="background-color: {{ $typeColor }}24; color: {{ $typeColor }}" data-test="maintenance-type-{{ $record->id }}">
              <span class="size-1.5 rounded-sm" style="background-color: {{ $typeColor }}"></span>
              {{ $record->type->label() }}
            </span>

            <span class="text-[15px] font-semibold text-ink" data-test="maintenance-title-{{ $record->id }}">{{ $record->title }}</span>

            @if ($record->include_in_provenance)
              <span class="rounded-full bg-card px-2.5 py-1 text-[11.5px] font-semibold text-muted" data-test="maintenance-provenance-{{ $record->id }}">{{ __('In provenance') }}</span>
            @endif

            @if ($dueSoon)
              <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11.5px] font-semibold" style="background-color: #f59e0b24; color: #b45309" data-test="maintenance-due-{{ $record->id }}">
                <x-lucide-bell class="size-3" />
                {{ $record->next_due_at->isPast() ? __('Overdue') : __('Due soon') }}
              </span>
            @endif
          </div>

          <div class="flex items-center gap-3.5">
            @if ($record->cost_amount !== null)
              <div class="text-right">
                <p class="text-base font-semibold text-ink" data-test="maintenance-cost-{{ $record->id }}">{{ Money::format($record->cost_amount, $record->cost_currency_code) }}</p>
                <p class="text-xs text-muted-soft">{{ __('cost') }}</p>
              </div>
            @endif

            @if ($canManage)
              <button type="button" x-on:click="editing = ! editing" class="flex h-8 shrink-0 items-center justify-center rounded-md border border-hairline px-3 text-[13px] font-semibold text-muted hover:bg-card" data-test="edit-maintenance-{{ $record->id }}">
                {{ __('Edit') }}
              </button>
            @endif
          </div>
        </div>

        <div class="grid grid-cols-2 border-t border-hairline sm:grid-cols-4">
          @foreach ($facts as $label => $value)
            <div class="border-b border-hairline px-5 py-3 last:border-r-0 sm:border-b-0 sm:border-r sm:border-r-hairline">
              <p class="mb-1 text-[11.5px] text-muted-soft">{{ $label }}</p>
              <p class="truncate text-[13.5px] font-semibold text-ink">{{ $value }}</p>
            </div>
          @endforeach
        </div>

        @if ($record->description)
          <div class="border-t border-hairline px-5 py-3">
            <p class="text-[13px] leading-relaxed text-muted">{{ $record->description }}</p>
          </div>
        @endif

        @if ($canManage)
          <div x-show="editing" x-cloak class="border-t border-hairline bg-card/40 p-4">
            @include('app.items.partials._maintenanceRecordForm', [
                'formId' => 'edit-maintenance-'.$record->id,
                'action' => route('maintenanceRecords.update', [$catalog, $item, $selectedCopy, $record]),
                'deleteAction' => route('maintenanceRecords.destroy', [$catalog, $item, $selectedCopy, $record]),
                'method' => 'put',
                'openVar' => 'editing',
                'submitLabel' => __('Save changes'),
                'dataTest' => 'edit-maintenance-form-'.$record->id,
                'record' => $record,
            ])
          </div>
        @endif

        <div class="border-t border-hairline px-5 py-4">
          <p class="mb-2.5 text-[11px] font-semibold tracking-wide text-muted-soft uppercase">{{ __('Documents') }}</p>
          @include('app.items.partials._documentsFor', ['documentable' => $record, 'catalog' => $catalog, 'item' => $item, 'selectedCopy' => $selectedCopy, 'canManage' => $canManage])
        </div>
      </div>
    @empty
      <div x-show="! adding" class="rounded-xl border border-hairline">
        <x-empty-state data-test="no-maintenance-{{ $selectedCopy->id }}">
          <x-slot:icon>
            <x-lucide-wrench class="size-6 text-muted" />
          </x-slot>

          {{ __('No maintenance has been recorded for this copy yet.') }}
        </x-empty-state>
      </div>
    @endforelse
  </div>
</div>
