{{-- The form behind adding and editing a valuation on a copy. --}}

@use('App\Enums\ValuationConfidence')
@use('App\Enums\ValuationType')

@php
    $isEdit = $valuation !== null;
    $units = fn (?int $cents): string => $cents === null ? '' : number_format($cents / 100, 2, '.', '');
    $selectedCurrency = $valuation?->currency_code ?? $catalog->currency ?? array_key_first($currencies);

    $labelClasses = 'block text-[11px] font-semibold tracking-wide text-muted-soft uppercase';
    $inputClasses = 'mt-1.5 h-10 w-full rounded-md border border-hairline bg-input px-3 text-sm text-ink placeholder-muted-soft';
    $selectClasses = 'mt-1.5 h-10 w-full appearance-none rounded-md border border-hairline bg-input pr-9 pl-3 text-sm text-ink';
    $textareaClasses = 'mt-1.5 w-full rounded-md border border-hairline bg-input px-3 py-2 text-sm text-ink placeholder-muted-soft';
@endphp

<x-history.inline-form
  :form-id="$formId"
  :method="$method"
  :action="$action"
  :open-var="$openVar"
  :submit-label="$submitLabel"
  :title="$isEdit ? __('Editing valuation') : __('New valuation')"
  :subtitle="$valuation?->type->label()"
  :data-test="$dataTest"
  :delete-action="$isEdit ? $deleteAction : null"
  :delete-confirm="__('Delete this valuation? This cannot be undone.')"
  :delete-data-test="$isEdit ? 'delete-valuation-'.$valuation->id : null"
>
  <div class="mb-3.5 grid grid-cols-1 gap-3.5 sm:grid-cols-3">
    <div>
      <label for="{{ $formId }}-type" class="{{ $labelClasses }}">{{ __('Type') }}</label>
      <div class="relative">
        <select id="{{ $formId }}-type" name="type" class="{{ $selectClasses }}" data-test="{{ $formId }}-type">
          @foreach (ValuationType::options() as $value => $label)
            <option value="{{ $value }}" @selected($value === ($valuation?->type->value ?? ValuationType::UserEstimate->value))>{{ $label }}</option>
          @endforeach
        </select>
        <x-lucide-chevrons-up-down class="pointer-events-none absolute top-1/2 right-3 size-4 -translate-y-1/2 text-muted-soft" />
      </div>
      <x-error :messages="$errors->get('type')" class="mt-2" />
    </div>

    <div>
      <label for="{{ $formId }}-valued-at" class="{{ $labelClasses }}">{{ __('Date') }}</label>
      <input id="{{ $formId }}-valued-at" name="valued_at" type="date" value="{{ $valuation?->valued_at->toDateString() }}" class="{{ $inputClasses }}" data-test="{{ $formId }}-valued-at" />
      <x-error :messages="$errors->get('valued_at')" class="mt-2" />
    </div>

    <div>
      <div class="flex items-center gap-1.5">
        <label for="{{ $formId }}-confidence" class="{{ $labelClasses }}">{{ __('Confidence') }}</label>
        <x-help id="history.valuations.confidence" />
      </div>
      <div class="relative">
        <select id="{{ $formId }}-confidence" name="confidence" class="{{ $selectClasses }}">
          @foreach (ValuationConfidence::options() as $value => $label)
            <option value="{{ $value }}" @selected($value === ($valuation?->confidence->value ?? ValuationConfidence::Unknown->value))>{{ $label }}</option>
          @endforeach
        </select>
        <x-lucide-chevrons-up-down class="pointer-events-none absolute top-1/2 right-3 size-4 -translate-y-1/2 text-muted-soft" />
      </div>
      <x-error :messages="$errors->get('confidence')" class="mt-2" />
    </div>
  </div>

  <div class="mb-3.5 grid grid-cols-1 gap-3.5 sm:grid-cols-2">
    <div>
      <label for="{{ $formId }}-amount" class="{{ $labelClasses }}">{{ __('Amount') }}</label>
      <input id="{{ $formId }}-amount" name="amount" type="number" step="0.01" min="0" value="{{ $units($valuation?->amount) }}" class="{{ $inputClasses }}" data-test="{{ $formId }}-amount" />
      <x-error :messages="$errors->get('amount')" class="mt-2" />
    </div>

    <div>
      <label for="{{ $formId }}-currency" class="{{ $labelClasses }}">{{ __('Currency') }}</label>
      <div class="relative">
        <select id="{{ $formId }}-currency" name="currency" class="{{ $selectClasses }}">
          @foreach ($currencies as $code => $label)
            <option value="{{ $code }}" @selected($code === $selectedCurrency)>{{ $label }}</option>
          @endforeach
        </select>
        <x-lucide-chevrons-up-down class="pointer-events-none absolute top-1/2 right-3 size-4 -translate-y-1/2 text-muted-soft" />
      </div>
      <x-error :messages="$errors->get('currency')" class="mt-2" />
    </div>
  </div>

  <div class="mb-3.5 grid grid-cols-1 gap-3.5 sm:grid-cols-2">
    <div>
      <label for="{{ $formId }}-valuer" class="{{ $labelClasses }}">{{ __('Valuer') }}</label>
      <input id="{{ $formId }}-valuer" name="valuer" value="{{ $valuation?->valuer }}" placeholder="{{ __('e.g. Central Perk Appraisals') }}" class="{{ $inputClasses }}" />
      <x-error :messages="$errors->get('valuer')" class="mt-2" />
    </div>

    <div>
      <label for="{{ $formId }}-method" class="{{ $labelClasses }}">{{ __('Method') }}</label>
      <input id="{{ $formId }}-method" name="method" value="{{ $valuation?->method }}" placeholder="{{ __('e.g. Comparable sales') }}" class="{{ $inputClasses }}" />
      <x-error :messages="$errors->get('method')" class="mt-2" />
    </div>
  </div>

  <div class="mb-3.5 grid grid-cols-1 gap-3.5 sm:grid-cols-2">
    <div>
      <label for="{{ $formId }}-reference-number" class="{{ $labelClasses }}">{{ __('Reference number') }}</label>
      <input id="{{ $formId }}-reference-number" name="reference_number" value="{{ $valuation?->reference_number }}" placeholder="{{ __('e.g. CP-1994') }}" class="{{ $inputClasses }}" />
      <x-error :messages="$errors->get('reference_number')" class="mt-2" />
    </div>

    <div>
      <label for="{{ $formId }}-source-url" class="{{ $labelClasses }}">{{ __('Source link') }}</label>
      <input id="{{ $formId }}-source-url" name="source_url" type="url" value="{{ $valuation?->source_url }}" placeholder="{{ __('Where this valuation can be checked') }}" class="{{ $inputClasses }}" />
      <x-error :messages="$errors->get('source_url')" class="mt-2" />
    </div>
  </div>

  <div class="mb-4">
    <label for="{{ $formId }}-note" class="{{ $labelClasses }}">{{ __('Note') }}</label>
    <textarea id="{{ $formId }}-note" name="note" rows="2" placeholder="{{ __('Anything worth remembering about this valuation.') }}" class="{{ $textareaClasses }}">{{ $valuation?->note }}</textarea>
    <x-error :messages="$errors->get('note')" class="mt-2" />
  </div>
</x-history.inline-form>
