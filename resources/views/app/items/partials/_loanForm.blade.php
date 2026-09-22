{{-- The form behind adding and editing a loan on a copy. --}}

@use('App\Enums\LoanDirection')
@use('App\Enums\LoanStatus')

@php
    $isEdit = $loan !== null;
    $units = fn (?int $cents): string => $cents === null ? '' : number_format($cents / 100, 2, '.', '');
    $selectedCurrency = $loan?->deposit_currency_code ?? $catalog->currency ?? array_key_first($currencies);

    $labelClasses = 'block text-[11px] font-semibold tracking-wide text-muted-soft uppercase';
    $inputClasses = 'mt-1.5 h-10 w-full rounded-md border border-hairline bg-input px-3 text-sm text-ink placeholder-muted-soft';
    $optional = '<span class="font-medium normal-case text-muted-soft/70">— '.__('optional').'</span>';
@endphp

<x-history.inline-form
  :form-id="$formId"
  :method="$method"
  :action="$action"
  :open-var="$openVar"
  :submit-label="$submitLabel"
  :title="$isEdit ? __('Editing loan') : __('New loan')"
  :subtitle="$loan?->party"
  :data-test="$dataTest"
  :delete-action="$isEdit ? $deleteAction : null"
  :delete-confirm="__('Delete this loan? This cannot be undone.')"
  :delete-data-test="$isEdit ? 'delete-loan-'.$loan->id : null"
>
  <div x-data="{ provenance: {{ $loan?->include_in_provenance ? 'true' : 'false' }} }">
    <input type="hidden" name="include_in_provenance" x-bind:value="provenance ? '1' : '0'" />

    @if ($isEdit)
      <input type="hidden" name="returned_at" value="{{ $loan->returned_at?->toDateString() }}" />
      <input type="hidden" name="item_condition_in_id" value="{{ $loan->item_condition_in_id }}" />
    @endif

    <div class="mb-3.5 grid grid-cols-1 gap-3.5 sm:grid-cols-2">
      <div>
        <label for="{{ $formId }}-direction" class="{{ $labelClasses }}">{{ __('Direction') }}</label>
        <select id="{{ $formId }}-direction" name="direction" class="{{ $inputClasses }}" data-test="{{ $formId }}-direction">
          @foreach (LoanDirection::options() as $value => $label)
            <option value="{{ $value }}" @selected(($loan?->direction->value ?? LoanDirection::Outgoing->value) === $value)>{{ $label }}</option>
          @endforeach
        </select>
        <x-error :messages="$errors->get('direction')" class="mt-2" />
      </div>

      <div>
        <label for="{{ $formId }}-status" class="{{ $labelClasses }}">{{ __('Status') }}</label>
        <select id="{{ $formId }}-status" name="status" class="{{ $inputClasses }}" data-test="{{ $formId }}-status">
          @foreach (LoanStatus::options() as $value => $label)
            <option value="{{ $value }}" @selected(($loan?->status->value ?? LoanStatus::Active->value) === $value)>{{ $label }}</option>
          @endforeach
        </select>
        <x-error :messages="$errors->get('status')" class="mt-2" />
      </div>
    </div>

    <div class="mb-3.5">
      <label for="{{ $formId }}-party" class="{{ $labelClasses }}">{{ __('Party') }}</label>
      <input id="{{ $formId }}-party" name="party" value="{{ $loan?->party }}" placeholder="{{ __('Who the copy is with') }}" class="{{ $inputClasses }}" data-test="{{ $formId }}-party" />
      <x-error :messages="$errors->get('party')" class="mt-2" />
    </div>

    <div class="mb-3.5 grid grid-cols-1 gap-3.5 sm:grid-cols-2">
      <div>
        <label for="{{ $formId }}-loaned-at" class="{{ $labelClasses }}">{{ __('Loaned') }}</label>
        <input id="{{ $formId }}-loaned-at" name="loaned_at" type="date" value="{{ $loan?->loaned_at?->toDateString() }}" class="{{ $inputClasses }}" data-test="{{ $formId }}-loaned-at" />
        <x-error :messages="$errors->get('loaned_at')" class="mt-2" />
      </div>

      <div>
        <label for="{{ $formId }}-due-at" class="{{ $labelClasses }}">{{ __('Due back') }} {!! $optional !!}</label>
        <input id="{{ $formId }}-due-at" name="due_at" type="date" value="{{ $loan?->due_at?->toDateString() }}" placeholder="{{ __('Leave blank if open ended') }}" class="{{ $inputClasses }}" data-test="{{ $formId }}-due-at" />
        <x-error :messages="$errors->get('due_at')" class="mt-2" />
      </div>
    </div>

    <div class="mb-3.5 grid grid-cols-1 gap-3.5 sm:grid-cols-2">
      <div>
        <label for="{{ $formId }}-condition-out" class="{{ $labelClasses }}">{{ __('Condition out') }} {!! $optional !!}</label>
        <select id="{{ $formId }}-condition-out" name="item_condition_out_id" class="{{ $inputClasses }}">
          <option value="">{{ __('—') }}</option>
          @foreach ($conditions as $id => $name)
            <option value="{{ $id }}" @selected($loan?->item_condition_out_id === $id)>{{ $name }}</option>
          @endforeach
        </select>
        <x-error :messages="$errors->get('item_condition_out_id')" class="mt-2" />
      </div>

      <div>
        <label for="{{ $formId }}-deposit-amount" class="{{ $labelClasses }}">{{ __('Deposit') }} {!! $optional !!}</label>
        <div class="mt-1.5 flex h-10 items-stretch overflow-hidden rounded-md border border-hairline bg-input">
          <select name="currency" class="border-0 border-r border-hairline bg-card px-3 text-sm font-semibold text-ink focus:ring-0">
            @foreach ($currencies as $code => $label)
              <option value="{{ $code }}" @selected($code === $selectedCurrency)>{{ $code }}</option>
            @endforeach
          </select>
          <input id="{{ $formId }}-deposit-amount" name="deposit_amount" type="number" step="0.01" min="0" value="{{ $units($loan?->deposit_amount) }}" placeholder="0.00" class="min-w-0 flex-1 border-0 bg-transparent px-3 text-sm font-semibold text-ink focus:ring-0" data-test="{{ $formId }}-deposit-amount" />
        </div>
        <x-error :messages="$errors->get('deposit_amount')" class="mt-2" />
        <x-error :messages="$errors->get('currency')" class="mt-2" />
      </div>
    </div>

    <div class="mb-3.5">
      <label for="{{ $formId }}-purpose" class="{{ $labelClasses }}">{{ __('Purpose') }} {!! $optional !!}</label>
      <textarea id="{{ $formId }}-purpose" name="purpose" rows="2" placeholder="{{ __('An exhibition, a repair off site, a loan to a friend.') }}" class="mt-1.5 w-full rounded-md border border-hairline bg-input px-3 py-2 text-sm text-ink placeholder-muted-soft">{{ $loan?->purpose }}</textarea>
      <x-error :messages="$errors->get('purpose')" class="mt-2" />
    </div>

    <button type="button" x-on:click="provenance = ! provenance" class="flex w-full items-center gap-3 rounded-md border border-hairline px-3.5 py-3 text-left mb-3.5" data-test="{{ $formId }}-provenance-toggle">
      <span class="relative h-[22px] w-[38px] shrink-0 rounded-full transition-colors" x-bind:style="provenance ? 'background-color: #6366f1' : 'background-color: #d1d5db'">
        <span class="absolute top-0.5 size-[18px] rounded-full bg-white shadow transition-all" x-bind:style="provenance ? 'left: 18px' : 'left: 2px'"></span>
      </span>
      <span class="min-w-0">
        <span class="block text-[13.5px] font-semibold text-ink">{{ __('Add to the provenance') }}</span>
        <span class="block text-xs text-muted-soft">{{ __('Institutional loans and exhibitions belong to the object\'s story. This creates matching provenance events for the loan and its return, and removing it here deletes them. Informal personal loans can be left off.') }}</span>
      </span>
    </button>
  </div>
</x-history.inline-form>
