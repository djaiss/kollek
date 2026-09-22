{{-- The form behind adding and editing an insurance record on a copy. --}}

@use('App\Enums\InsuranceStatus')

@php
    $isEdit = $record !== null;
    $units = fn (?int $cents): string => $cents === null ? '' : number_format($cents / 100, 2, '.', '');
    $selectedCurrency = $record?->currency_code ?? $catalog->currency ?? array_key_first($currencies);

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
  :title="$isEdit ? __('Editing insurance record') : __('New insurance record')"
  :subtitle="$record?->provider"
  :data-test="$dataTest"
  :delete-action="$isEdit ? $deleteAction : null"
  :delete-confirm="__('Delete this insurance record? This cannot be undone.')"
  :delete-data-test="$isEdit ? 'delete-insurance-'.$record->id : null"
>
  <div x-data="{ status: '{{ $record?->status->value ?? InsuranceStatus::Active->value }}', scheduled: {{ $record?->is_scheduled_item ? 'true' : 'false' }} }">
    <input type="hidden" name="status" x-bind:value="status" />
    <input type="hidden" name="is_scheduled_item" x-bind:value="scheduled ? '1' : '0'" />

    <div class="mb-3.5 grid grid-cols-1 gap-3.5 sm:grid-cols-[1.3fr_1fr]">
      <div>
        <label for="{{ $formId }}-provider" class="{{ $labelClasses }}">{{ __('Provider') }}</label>
        <input id="{{ $formId }}-provider" name="provider" value="{{ $record?->provider }}" placeholder="{{ __('Insurer name') }}" class="{{ $inputClasses }}" data-test="{{ $formId }}-provider" />
        <x-error :messages="$errors->get('provider')" class="mt-2" />
      </div>

      <div>
        <label class="{{ $labelClasses }}">{{ __('Status') }}</label>
        <div class="mt-1.5 flex gap-1.5">
          @foreach (InsuranceStatus::cases() as $case)
            @php $color = $case->color(); @endphp
            <button
              type="button"
              x-on:click="status = '{{ $case->value }}'"
              class="flex-1 rounded-md border px-2 py-2.5 text-[12.5px] font-semibold capitalize transition"
              x-bind:class="status === '{{ $case->value }}' ? 'border-transparent' : 'border-hairline text-muted hover:bg-card'"
              x-bind:style="status === '{{ $case->value }}' ? 'background-color: {{ $color }}24; color: {{ $color }}; border-color: {{ $color }}80' : ''"
              data-test="{{ $formId }}-status-{{ $case->value }}"
            >{{ $case->label() }}</button>
          @endforeach
        </div>
        <x-error :messages="$errors->get('status')" class="mt-2" />
      </div>
    </div>

    <div class="mb-3.5 grid grid-cols-1 gap-3.5 sm:grid-cols-2">
      <div>
        <label for="{{ $formId }}-policy-number" class="{{ $labelClasses }}">{{ __('Policy #') }} {!! $optional !!}</label>
        <input id="{{ $formId }}-policy-number" name="policy_number" value="{{ $record?->policy_number }}" placeholder="{{ __('Policy number') }}" class="{{ $inputClasses }} font-mono" />
        <x-error :messages="$errors->get('policy_number')" class="mt-2" />
      </div>

      <div>
        <label for="{{ $formId }}-coverage-type" class="{{ $labelClasses }}">{{ __('Coverage type') }} {!! $optional !!}</label>
        <input id="{{ $formId }}-coverage-type" name="coverage_type" value="{{ $record?->coverage_type }}" placeholder="{{ __('Scheduled item, blanket…') }}" class="{{ $inputClasses }}" />
        <x-error :messages="$errors->get('coverage_type')" class="mt-2" />
      </div>
    </div>

    <div class="mb-3.5 grid grid-cols-1 gap-3.5 sm:grid-cols-2">
      <div>
        <label for="{{ $formId }}-insured-value" class="{{ $labelClasses }}">{{ __('Insured value') }}</label>
        <div class="mt-1.5 flex h-10 items-stretch overflow-hidden rounded-md border border-hairline bg-input">
          <select name="currency" class="border-0 border-r border-hairline bg-card px-3 text-sm font-semibold text-ink focus:ring-0">
            @foreach ($currencies as $code => $label)
              <option value="{{ $code }}" @selected($code === $selectedCurrency)>{{ $code }}</option>
            @endforeach
          </select>
          <input id="{{ $formId }}-insured-value" name="insured_value" type="number" step="0.01" min="0" value="{{ $units($record?->insured_value) }}" placeholder="0.00" class="min-w-0 flex-1 border-0 bg-transparent px-3 text-sm font-semibold text-ink focus:ring-0" data-test="{{ $formId }}-insured-value" />
        </div>
        <x-error :messages="$errors->get('insured_value')" class="mt-2" />
        <x-error :messages="$errors->get('currency')" class="mt-2" />
      </div>

      <div>
        <label for="{{ $formId }}-deductible-amount" class="{{ $labelClasses }}">{{ __('Deductible') }} {!! $optional !!}</label>
        <input id="{{ $formId }}-deductible-amount" name="deductible_amount" type="number" step="0.01" min="0" value="{{ $units($record?->deductible_amount) }}" placeholder="0.00" class="{{ $inputClasses }}" />
        <x-error :messages="$errors->get('deductible_amount')" class="mt-2" />
      </div>
    </div>

    <div class="mb-3.5 grid grid-cols-1 gap-3.5 sm:grid-cols-2">
      <div>
        <label for="{{ $formId }}-starts-at" class="{{ $labelClasses }}">{{ __('Starts') }} {!! $optional !!}</label>
        <input id="{{ $formId }}-starts-at" name="starts_at" type="date" value="{{ $record?->starts_at?->toDateString() }}" class="{{ $inputClasses }}" />
        <x-error :messages="$errors->get('starts_at')" class="mt-2" />
      </div>

      <div>
        <label for="{{ $formId }}-ends-at" class="{{ $labelClasses }}">{{ __('Ends') }} {!! $optional !!}</label>
        <input id="{{ $formId }}-ends-at" name="ends_at" type="date" value="{{ $record?->ends_at?->toDateString() }}" placeholder="{{ __('Leave blank if ongoing') }}" class="{{ $inputClasses }}" />
        <x-error :messages="$errors->get('ends_at')" class="mt-2" />
      </div>
    </div>

    <button type="button" x-on:click="scheduled = ! scheduled" class="mb-3.5 flex w-full items-center gap-3 rounded-md border border-hairline px-3.5 py-3 text-left" data-test="{{ $formId }}-scheduled-toggle">
      <span class="relative h-[22px] w-[38px] shrink-0 rounded-full transition-colors" x-bind:style="scheduled ? 'background-color: #8b5cf6' : 'background-color: #d1d5db'">
        <span class="absolute top-0.5 size-[18px] rounded-full bg-white shadow transition-all" x-bind:style="scheduled ? 'left: 18px' : 'left: 2px'"></span>
      </span>
      <span class="min-w-0">
        <span class="block text-[13.5px] font-semibold text-ink">{{ __('Listed individually on the policy') }}</span>
        <span class="block text-xs text-muted-soft">{{ __('This item is specifically named on your insurance policy rather than covered under your general contents coverage.') }}</span>
      </span>
    </button>

    <div class="mb-3.5 grid grid-cols-1 gap-3.5 sm:grid-cols-3">
      <div>
        <label for="{{ $formId }}-contact-name" class="{{ $labelClasses }}">{{ __('Contact') }} {!! $optional !!}</label>
        <input id="{{ $formId }}-contact-name" name="contact_name" value="{{ $record?->contact_name }}" placeholder="{{ __('Agent name') }}" class="{{ $inputClasses }}" />
        <x-error :messages="$errors->get('contact_name')" class="mt-2" />
      </div>

      <div>
        <label for="{{ $formId }}-contact-email" class="{{ $labelClasses }}">{{ __('Email') }} {!! $optional !!}</label>
        <input id="{{ $formId }}-contact-email" name="contact_email" type="email" value="{{ $record?->contact_email }}" placeholder="{{ __('agent@insurer.com') }}" class="{{ $inputClasses }}" />
        <x-error :messages="$errors->get('contact_email')" class="mt-2" />
      </div>

      <div>
        <label for="{{ $formId }}-contact-phone" class="{{ $labelClasses }}">{{ __('Phone') }} {!! $optional !!}</label>
        <input id="{{ $formId }}-contact-phone" name="contact_phone" value="{{ $record?->contact_phone }}" placeholder="{{ __('+1 …') }}" class="{{ $inputClasses }}" />
        <x-error :messages="$errors->get('contact_phone')" class="mt-2" />
      </div>
    </div>

    <div class="mb-4">
      <label for="{{ $formId }}-note" class="{{ $labelClasses }}">{{ __('Note') }} {!! $optional !!}</label>
      <textarea id="{{ $formId }}-note" name="note" rows="2" placeholder="{{ __('Anything worth recording about this coverage.') }}" class="mt-1.5 w-full rounded-md border border-hairline bg-input px-3 py-2 text-sm text-ink placeholder-muted-soft">{{ $record?->note }}</textarea>
      <x-error :messages="$errors->get('note')" class="mt-2" />
    </div>
  </div>
</x-history.inline-form>
