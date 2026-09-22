{{-- The form behind adding and editing a provenance event on a copy. --}}

@use('App\Enums\DatePrecision')
@use('App\Enums\ProvenanceEventType')

@php
    $isEdit = $event !== null;
    $hints = collect(DatePrecision::cases())->mapWithKeys(fn (DatePrecision $case): array => [$case->value => $case->hint()])->all();
    $selectedPrecision = $event?->occurred_at_precision->value ?? DatePrecision::Exact->value;

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
  :title="$isEdit ? __('Editing provenance event') : __('New provenance event')"
  :subtitle="$event?->title"
  :data-test="$dataTest"
  :delete-action="$isEdit ? $deleteAction : null"
  :delete-confirm="__('Delete this provenance event? This cannot be undone.')"
  :delete-data-test="$isEdit ? 'delete-provenance-event-'.$event->id : null"
>
  <div x-data="{ precision: @js($selectedPrecision), verified: @js((bool) $event?->is_verified), hints: @js($hints) }">
    <div class="mb-3.5 grid grid-cols-1 gap-3.5 sm:grid-cols-2">
      <div>
        <label for="{{ $formId }}-type" class="{{ $labelClasses }}">{{ __('Type') }}</label>
        <div class="relative">
          <select id="{{ $formId }}-type" name="type" class="{{ $selectClasses }}" data-test="{{ $formId }}-type">
            @foreach (ProvenanceEventType::options() as $value => $label)
              <option value="{{ $value }}" @selected($value === ($event?->type->value ?? ProvenanceEventType::Acquisition->value))>{{ $label }}</option>
            @endforeach
          </select>
          <x-lucide-chevrons-up-down class="pointer-events-none absolute top-1/2 right-3 size-4 -translate-y-1/2 text-muted-soft" />
        </div>
        <x-error :messages="$errors->get('type')" class="mt-2" />
      </div>

      <div>
        <label for="{{ $formId }}-title" class="{{ $labelClasses }}">{{ __('Title') }}</label>
        <input id="{{ $formId }}-title" name="title" value="{{ $event?->title }}" placeholder="{{ __('e.g. Bought at the Central Perk auction') }}" class="{{ $inputClasses }}" data-test="{{ $formId }}-title" />
        <x-error :messages="$errors->get('title')" class="mt-2" />
      </div>
    </div>

    <div class="mb-3.5 grid grid-cols-1 gap-3.5 sm:grid-cols-2">
      <div>
        <label for="{{ $formId }}-precision" class="{{ $labelClasses }}">{{ __('Date precision') }}</label>
        <div class="relative">
          <select id="{{ $formId }}-precision" name="occurred_at_precision" x-model="precision" class="{{ $selectClasses }}" data-test="{{ $formId }}-precision">
            @foreach (DatePrecision::options() as $value => $label)
              <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
          </select>
          <x-lucide-chevrons-up-down class="pointer-events-none absolute top-1/2 right-3 size-4 -translate-y-1/2 text-muted-soft" />
        </div>
        <p class="mt-1.5 text-xs text-muted" x-text="hints[precision]" data-test="{{ $formId }}-precision-hint"></p>
        <x-error :messages="$errors->get('occurred_at_precision')" class="mt-2" />
      </div>

      <div>
        <label for="{{ $formId }}-occurred-at" class="{{ $labelClasses }}">{{ __('Date') }}</label>
        <input
          id="{{ $formId }}-occurred-at"
          name="occurred_at"
          type="date"
          value="{{ $event?->occurred_at?->toDateString() }}"
          class="{{ $inputClasses }} disabled:cursor-not-allowed disabled:opacity-50"
          x-bind:disabled="precision === '{{ DatePrecision::Unknown->value }}'"
          data-test="{{ $formId }}-occurred-at"
        />
        <p class="mt-1.5 text-xs text-muted">{{ __('Leave the date out when it is unknown.') }}</p>
        <x-error :messages="$errors->get('occurred_at')" class="mt-2" />
      </div>
    </div>

    <div class="mb-3.5 grid grid-cols-1 gap-3.5 sm:grid-cols-3">
      <div>
        <label for="{{ $formId }}-from-party" class="{{ $labelClasses }}">{{ __('From') }}</label>
        <input id="{{ $formId }}-from-party" name="from_party" value="{{ $event?->from_party }}" placeholder="{{ __('e.g. Gunther') }}" class="{{ $inputClasses }}" data-test="{{ $formId }}-from-party" />
        <x-error :messages="$errors->get('from_party')" class="mt-2" />
      </div>

      <div>
        <label for="{{ $formId }}-to-party" class="{{ $labelClasses }}">{{ __('To') }}</label>
        <input id="{{ $formId }}-to-party" name="to_party" value="{{ $event?->to_party }}" placeholder="{{ __('e.g. Ross Geller') }}" class="{{ $inputClasses }}" data-test="{{ $formId }}-to-party" />
        <x-error :messages="$errors->get('to_party')" class="mt-2" />
      </div>

      <div>
        <label for="{{ $formId }}-location" class="{{ $labelClasses }}">{{ __('Location') }}</label>
        <input id="{{ $formId }}-location" name="location" value="{{ $event?->location }}" placeholder="{{ __('e.g. New York') }}" class="{{ $inputClasses }}" />
        <x-error :messages="$errors->get('location')" class="mt-2" />
      </div>
    </div>

    <div class="mb-3.5">
      <label for="{{ $formId }}-description" class="{{ $labelClasses }}">{{ __('Description') }}</label>
      <textarea id="{{ $formId }}-description" name="description" rows="2" placeholder="{{ __('What happened, and how it is known.') }}" class="{{ $textareaClasses }}">{{ $event?->description }}</textarea>
      <x-error :messages="$errors->get('description')" class="mt-2" />
    </div>

    <div class="mb-3.5 grid grid-cols-1 gap-3.5 sm:grid-cols-2">
      <div>
        <label for="{{ $formId }}-reference-number" class="{{ $labelClasses }}">{{ __('Reference number') }}</label>
        <input id="{{ $formId }}-reference-number" name="reference_number" value="{{ $event?->reference_number }}" placeholder="{{ __('e.g. Lot 118') }}" class="{{ $inputClasses }}" />
        <x-error :messages="$errors->get('reference_number')" class="mt-2" />
      </div>

      <div>
        <label for="{{ $formId }}-source-url" class="{{ $labelClasses }}">{{ __('Source URL') }}</label>
        <input id="{{ $formId }}-source-url" name="source_url" type="url" value="{{ $event?->source_url }}" placeholder="{{ __('Where this event can be checked') }}" class="{{ $inputClasses }}" />
        <x-error :messages="$errors->get('source_url')" class="mt-2" />
      </div>
    </div>

    <div class="mb-3.5">
      <label for="{{ $formId }}-transaction" class="{{ $labelClasses }}">{{ __('Linked transaction') }}</label>
      <div class="relative">
        <select id="{{ $formId }}-transaction" name="transaction_id" class="{{ $selectClasses }}" data-test="{{ $formId }}-transaction">
          <option value="">{{ __('Not linked to a transaction') }}</option>
          @foreach ($copy->transactions as $option)
            <option value="{{ $option->id }}" @selected($option->id === $event?->transaction_id)>{{ $option->type->label() }} ({{ $option->occurred_at->isoFormat('ll') }})</option>
          @endforeach
        </select>
        <x-lucide-chevrons-up-down class="pointer-events-none absolute top-1/2 right-3 size-4 -translate-y-1/2 text-muted-soft" />
      </div>
      <p class="mt-1.5 text-xs text-muted">{{ __('What the moment cost lives on the transaction, so it is not typed again here.') }}</p>
      <x-error :messages="$errors->get('transaction_id')" class="mt-2" />
    </div>

    <div class="mb-4 rounded-xl border border-hairline bg-canvas p-4">
      <label class="flex items-center gap-2.5 text-sm font-medium text-ink">
        <input type="hidden" name="is_verified" value="0" />
        <input type="checkbox" name="is_verified" value="1" x-model="verified" class="rounded-sm border-hairline bg-input text-primary shadow-xs focus:ring-primary/30" data-test="{{ $formId }}-is-verified" />
        {{ __('This event has been verified') }}
      </label>

      <div x-show="verified" x-cloak class="mt-3">
        <label for="{{ $formId }}-verification-note" class="{{ $labelClasses }}">{{ __('How it was verified') }}</label>
        <textarea id="{{ $formId }}-verification-note" name="verification_note" rows="2" placeholder="{{ __('Who checked it, and against what.') }}" class="{{ $textareaClasses }}">{{ $event?->verification_note }}</textarea>
        <x-error :messages="$errors->get('verification_note')" class="mt-2" />
      </div>
    </div>
  </div>
</x-history.inline-form>
