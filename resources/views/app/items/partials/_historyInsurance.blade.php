{{-- The insurance coverage held against the copy over time. --}}

@use('App\Helpers\Money')

@php
  $user = auth()->user();
  $canManage = $user->account->allowsManagementBy($user);
  $records = $selectedCopy->insuranceRecords;
@endphp

<div x-data="{ adding: false }">
  <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
    <div class="min-w-0">
      <div class="flex items-center gap-2">
        <p class="text-lg font-semibold text-ink">{{ __('Insurance') }}</p>
        <x-help id="history.insurance" />
      </div>
      <p class="mt-1 max-w-xl text-[13px] leading-relaxed text-muted">{{ __('Coverage records as policies and insured values change over time.') }}</p>
    </div>

    @if ($canManage)
      <x-button.secondary type="button" x-on:click="adding = ! adding" class="shrink-0 !h-9 !px-4 text-[13px]" data-test="new-insurance-{{ $selectedCopy->id }}">
        <x-slot:icon>
          <x-lucide-plus class="size-4" />
        </x-slot>
        {{ __('Insurance record') }}
      </x-button.secondary>
    @endif
  </div>

  @if ($canManage)
    <div x-show="adding" x-cloak class="mb-5">
      @include('app.items.partials._insuranceRecordForm', [
          'formId' => 'add-insurance-'.$selectedCopy->id,
          'action' => route('insuranceRecords.create', [$catalog, $item, $selectedCopy]),
          'method' => 'post',
          'openVar' => 'adding',
          'submitLabel' => __('Add record'),
          'dataTest' => 'create-insurance-form-'.$selectedCopy->id,
          'record' => null,
      ])
    </div>
  @endif

  <div class="flex flex-col gap-3.5">
    @forelse ($records as $record)
      @php
        $statusColor = $record->status->color();
        $currency = $record->currency_code;
        $period = ($record->starts_at ? $record->starts_at->isoFormat('MMM YYYY') : '—')
            . ' → ' . ($record->ends_at ? $record->ends_at->isoFormat('MMM YYYY') : __('present'));
        $facts = [
            __('Policy #') => $record->policy_number ?? '—',
            __('Coverage') => $record->coverage_type ?? '—',
            __('Period') => $period,
            __('Deductible') => $record->deductible_amount === null
                ? '—'
                : Money::format($record->deductible_amount, $record->deductible_currency_code ?? $currency),
        ];
      @endphp

      <div @class(['overflow-hidden rounded-xl border border-hairline', 'opacity-60' => $record->status->isMuted()]) x-data="{ editing: false }" data-test="insurance-{{ $record->id }}">
        <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4">
          <div class="flex min-w-0 flex-wrap items-center gap-2.5">
            <span class="text-[15px] font-semibold text-ink" data-test="insurance-provider-{{ $record->id }}">{{ $record->provider }}</span>

            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11.5px] font-semibold capitalize" style="background-color: {{ $statusColor }}24; color: {{ $statusColor }}" data-test="insurance-status-{{ $record->id }}">
              <span class="size-1.5 rounded-full" style="background-color: {{ $statusColor }}"></span>
              {{ $record->status->label() }}
            </span>

            @if ($record->is_scheduled_item)
              <span class="rounded-full bg-card px-2.5 py-1 text-[11.5px] font-semibold text-muted" data-test="insurance-scheduled-{{ $record->id }}">{{ __('Scheduled item') }}</span>
            @endif
          </div>

          <div class="flex items-center gap-3.5">
            <div class="text-right">
              <p class="text-base font-semibold text-ink" data-test="insurance-value-{{ $record->id }}">{{ Money::format($record->insured_value, $currency) }}</p>
              <p class="text-xs text-muted-soft">{{ __('insured value') }}</p>
            </div>

            @if ($canManage)
              <button type="button" x-on:click="editing = ! editing" class="flex h-8 shrink-0 items-center justify-center rounded-md border border-hairline px-3 text-[13px] font-semibold text-muted hover:bg-card" data-test="edit-insurance-{{ $record->id }}">
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

        @if ($record->note)
          <div class="border-t border-hairline px-5 py-3">
            <p class="text-[13px] leading-relaxed text-muted">{{ $record->note }}</p>
          </div>
        @endif

        @if ($canManage)
          <div x-show="editing" x-cloak class="border-t border-hairline bg-card/40 p-4">
            @include('app.items.partials._insuranceRecordForm', [
                'formId' => 'edit-insurance-'.$record->id,
                'action' => route('insuranceRecords.update', [$catalog, $item, $selectedCopy, $record]),
                'deleteAction' => route('insuranceRecords.destroy', [$catalog, $item, $selectedCopy, $record]),
                'method' => 'put',
                'openVar' => 'editing',
                'submitLabel' => __('Save changes'),
                'dataTest' => 'edit-insurance-form-'.$record->id,
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
        <x-empty-state data-test="no-insurance-{{ $selectedCopy->id }}">
          <x-slot:icon>
            <x-lucide-shield class="size-6 text-muted" />
          </x-slot>

          {{ __('No insurance coverage has been recorded for this copy yet.') }}
        </x-empty-state>
      </div>
    @endforelse
  </div>
</div>
