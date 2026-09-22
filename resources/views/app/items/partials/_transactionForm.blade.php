{{-- The form behind adding and editing a transaction on a copy. --}}

@use('App\Enums\TransactionType')

@php
    $isEdit = $transaction !== null;
    $units = fn (?int $cents): string => $cents === null ? '' : number_format($cents / 100, 2, '.', '');
    $selectedCurrency = $transaction?->currency_code ?? $catalog->currency ?? array_key_first($currencies);

    $deleteConfirm = $transaction?->provenanceEvent
        ? __('Delete this transaction? Its provenance event is kept and simply unlinked. This cannot be undone.')
        : __('Delete this transaction? This cannot be undone.');

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
  :title="$isEdit ? __('Editing transaction') : __('New transaction')"
  :subtitle="$transaction?->type->label()"
  :data-test="$dataTest"
  :delete-action="$isEdit ? $deleteAction : null"
  :delete-confirm="$deleteConfirm"
  :delete-data-test="$isEdit ? 'delete-transaction-'.$transaction->id : null"
>
  <div class="mb-3.5 grid grid-cols-1 gap-3.5 sm:grid-cols-3">
    <div>
      <label for="{{ $formId }}-type" class="{{ $labelClasses }}">{{ __('Type') }}</label>
      <div class="relative">
        <select id="{{ $formId }}-type" name="type" class="{{ $selectClasses }}" data-test="{{ $formId }}-type">
          @foreach (TransactionType::options() as $value => $label)
            <option value="{{ $value }}" @selected($value === ($transaction?->type->value ?? TransactionType::Purchase->value))>{{ $label }}</option>
          @endforeach
        </select>
        <x-lucide-chevrons-up-down class="pointer-events-none absolute top-1/2 right-3 size-4 -translate-y-1/2 text-muted-soft" />
      </div>
      <x-error :messages="$errors->get('type')" class="mt-2" />
    </div>

    <div>
      <label for="{{ $formId }}-occurred-at" class="{{ $labelClasses }}">{{ __('Date') }}</label>
      <input id="{{ $formId }}-occurred-at" name="occurred_at" type="date" value="{{ $transaction?->occurred_at->toDateString() }}" class="{{ $inputClasses }}" data-test="{{ $formId }}-occurred-at" />
      <x-error :messages="$errors->get('occurred_at')" class="mt-2" />
    </div>

    <div>
      <div class="flex items-center gap-1.5">
        <label for="{{ $formId }}-counterparty" class="{{ $labelClasses }}">{{ __('Counterparty') }}</label>
        <x-help id="history.transactions.counterparty" align="right" />
      </div>
      <input id="{{ $formId }}-counterparty" name="counterparty" value="{{ $transaction?->counterparty }}" placeholder="{{ __('e.g. Central Perk Comics') }}" class="{{ $inputClasses }}" />
      <x-error :messages="$errors->get('counterparty')" class="mt-2" />
    </div>
  </div>

  <div class="mb-3.5 grid grid-cols-2 gap-3.5 sm:grid-cols-4">
    <div>
      <label for="{{ $formId }}-amount" class="{{ $labelClasses }}">{{ __('Amount') }}</label>
      <input id="{{ $formId }}-amount" name="amount" type="number" step="0.01" min="0" value="{{ $units($transaction?->amount) }}" class="{{ $inputClasses }}" data-test="{{ $formId }}-amount" />
      <x-error :messages="$errors->get('amount')" class="mt-2" />
    </div>

    <div>
      <label for="{{ $formId }}-tax-amount" class="{{ $labelClasses }}">{{ __('Tax') }}</label>
      <input id="{{ $formId }}-tax-amount" name="tax_amount" type="number" step="0.01" min="0" value="{{ $units($transaction?->tax_amount) }}" class="{{ $inputClasses }}" />
      <x-error :messages="$errors->get('tax_amount')" class="mt-2" />
    </div>

    <div>
      <label for="{{ $formId }}-fee-amount" class="{{ $labelClasses }}">{{ __('Fees') }}</label>
      <input id="{{ $formId }}-fee-amount" name="fee_amount" type="number" step="0.01" min="0" value="{{ $units($transaction?->fee_amount) }}" class="{{ $inputClasses }}" />
      <x-error :messages="$errors->get('fee_amount')" class="mt-2" />
    </div>

    <div>
      <label for="{{ $formId }}-shipping-amount" class="{{ $labelClasses }}">{{ __('Shipping') }}</label>
      <input id="{{ $formId }}-shipping-amount" name="shipping_amount" type="number" step="0.01" min="0" value="{{ $units($transaction?->shipping_amount) }}" class="{{ $inputClasses }}" />
      <x-error :messages="$errors->get('shipping_amount')" class="mt-2" />
    </div>
  </div>

  <div class="mb-3.5 grid grid-cols-1 gap-3.5 sm:grid-cols-3">
    <div>
      <label for="{{ $formId }}-total-amount" class="{{ $labelClasses }}">{{ __('Total') }}</label>
      <input id="{{ $formId }}-total-amount" name="total_amount" type="number" step="0.01" min="0" value="{{ $units($transaction?->total_amount) }}" class="{{ $inputClasses }}" />
      <p class="mt-1.5 text-xs text-muted">{{ __('Leave empty to add the four figures above together.') }}</p>
      <x-error :messages="$errors->get('total_amount')" class="mt-2" />
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

    <div>
      <label for="{{ $formId }}-reference-number" class="{{ $labelClasses }}">{{ __('Reference number') }}</label>
      <input id="{{ $formId }}-reference-number" name="reference_number" value="{{ $transaction?->reference_number }}" placeholder="{{ __('e.g. Invoice 4021') }}" class="{{ $inputClasses }}" />
      <x-error :messages="$errors->get('reference_number')" class="mt-2" />
    </div>
  </div>

  <div class="mb-3.5">
    <label for="{{ $formId }}-source-url" class="{{ $labelClasses }}">{{ __('Source link') }}</label>
    <input id="{{ $formId }}-source-url" name="source_url" type="url" value="{{ $transaction?->source_url }}" placeholder="{{ __('Where this transaction can be checked') }}" class="{{ $inputClasses }}" />
    <x-error :messages="$errors->get('source_url')" class="mt-2" />
  </div>

  <div class="mb-4">
    <label for="{{ $formId }}-note" class="{{ $labelClasses }}">{{ __('Note') }}</label>
    <textarea id="{{ $formId }}-note" name="note" rows="2" placeholder="{{ __('Anything worth remembering about this transaction.') }}" class="{{ $textareaClasses }}">{{ $transaction?->note }}</textarea>
    <x-error :messages="$errors->get('note')" class="mt-2" />
  </div>
</x-history.inline-form>
