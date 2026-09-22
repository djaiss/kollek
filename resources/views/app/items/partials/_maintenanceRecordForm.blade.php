{{-- The form behind adding and editing a maintenance record on a copy. --}}

@use('App\Enums\MaintenanceType')

@php
    $isEdit = $record !== null;
    $units = fn (?int $cents): string => $cents === null ? '' : number_format($cents / 100, 2, '.', '');
    $selectedCurrency = $record?->cost_currency_code ?? $catalog->currency ?? array_key_first($currencies);

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
  :title="$isEdit ? __('Editing maintenance record') : __('New maintenance record')"
  :subtitle="$record?->title"
  :data-test="$dataTest"
  :delete-action="$isEdit ? $deleteAction : null"
  :delete-confirm="__('Delete this maintenance record? This cannot be undone.')"
  :delete-data-test="$isEdit ? 'delete-maintenance-'.$record->id : null"
>
  <div x-data="{ provenance: {{ $record?->include_in_provenance ? 'true' : 'false' }} }">
    <input type="hidden" name="include_in_provenance" x-bind:value="provenance ? '1' : '0'" />

    <div class="mb-3.5 grid grid-cols-1 gap-3.5 sm:grid-cols-[1fr_1.4fr]">
      <div>
        <label for="{{ $formId }}-type" class="{{ $labelClasses }}">{{ __('Type') }}</label>
        <select id="{{ $formId }}-type" name="type" class="{{ $inputClasses }}" data-test="{{ $formId }}-type">
          @foreach (MaintenanceType::options() as $value => $label)
            <option value="{{ $value }}" @selected(($record?->type->value ?? MaintenanceType::Cleaning->value) === $value)>{{ $label }}</option>
          @endforeach
        </select>
        <x-error :messages="$errors->get('type')" class="mt-2" />
      </div>

      <div>
        <label for="{{ $formId }}-title" class="{{ $labelClasses }}">{{ __('Title') }}</label>
        <input id="{{ $formId }}-title" name="title" value="{{ $record?->title }}" placeholder="{{ __('What was done') }}" class="{{ $inputClasses }}" data-test="{{ $formId }}-title" />
        <x-error :messages="$errors->get('title')" class="mt-2" />
      </div>
    </div>

    <div class="mb-3.5 grid grid-cols-1 gap-3.5 sm:grid-cols-2">
      <div>
        <label for="{{ $formId }}-performed-by" class="{{ $labelClasses }}">{{ __('Performed by') }} {!! $optional !!}</label>
        <input id="{{ $formId }}-performed-by" name="performed_by" value="{{ $record?->performed_by }}" placeholder="{{ __('Who did the work') }}" class="{{ $inputClasses }}" />
        <x-error :messages="$errors->get('performed_by')" class="mt-2" />
      </div>

      <div>
        <label for="{{ $formId }}-performed-at" class="{{ $labelClasses }}">{{ __('Performed') }} {!! $optional !!}</label>
        <input id="{{ $formId }}-performed-at" name="performed_at" type="date" value="{{ $record?->performed_at?->toDateString() }}" class="{{ $inputClasses }}" data-test="{{ $formId }}-performed-at" />
        <x-error :messages="$errors->get('performed_at')" class="mt-2" />
      </div>
    </div>

    <div class="mb-3.5 grid grid-cols-1 gap-3.5 sm:grid-cols-2">
      <div>
        <label for="{{ $formId }}-cost-amount" class="{{ $labelClasses }}">{{ __('Cost') }} {!! $optional !!}</label>
        <div class="mt-1.5 flex h-10 items-stretch overflow-hidden rounded-md border border-hairline bg-input">
          <select name="currency" class="border-0 border-r border-hairline bg-card px-3 text-sm font-semibold text-ink focus:ring-0">
            @foreach ($currencies as $code => $label)
              <option value="{{ $code }}" @selected($code === $selectedCurrency)>{{ $code }}</option>
            @endforeach
          </select>
          <input id="{{ $formId }}-cost-amount" name="cost_amount" type="number" step="0.01" min="0" value="{{ $units($record?->cost_amount) }}" placeholder="0.00" class="min-w-0 flex-1 border-0 bg-transparent px-3 text-sm font-semibold text-ink focus:ring-0" data-test="{{ $formId }}-cost-amount" />
        </div>
        <x-error :messages="$errors->get('cost_amount')" class="mt-2" />
        <x-error :messages="$errors->get('currency')" class="mt-2" />
      </div>

      <div>
        <label for="{{ $formId }}-next-due-at" class="{{ $labelClasses }}">{{ __('Next due') }} {!! $optional !!}</label>
        <input id="{{ $formId }}-next-due-at" name="next_due_at" type="date" value="{{ $record?->next_due_at?->toDateString() }}" placeholder="{{ __('Leave blank if not recurring') }}" class="{{ $inputClasses }}" />
        <x-error :messages="$errors->get('next_due_at')" class="mt-2" />
      </div>
    </div>

    <div class="mb-3.5 grid grid-cols-1 gap-3.5 sm:grid-cols-2">
      <div>
        <label for="{{ $formId }}-condition-before" class="{{ $labelClasses }}">{{ __('Condition before') }} {!! $optional !!}</label>
        <select id="{{ $formId }}-condition-before" name="item_condition_before_id" class="{{ $inputClasses }}">
          <option value="">{{ __('—') }}</option>
          @foreach ($conditions as $id => $name)
            <option value="{{ $id }}" @selected($record?->item_condition_before_id === $id)>{{ $name }}</option>
          @endforeach
        </select>
        <x-error :messages="$errors->get('item_condition_before_id')" class="mt-2" />
      </div>

      <div>
        <label for="{{ $formId }}-condition-after" class="{{ $labelClasses }}">{{ __('Condition after') }} {!! $optional !!}</label>
        <select id="{{ $formId }}-condition-after" name="item_condition_after_id" class="{{ $inputClasses }}" data-test="{{ $formId }}-condition-after">
          <option value="">{{ __('—') }}</option>
          @foreach ($conditions as $id => $name)
            <option value="{{ $id }}" @selected($record?->item_condition_after_id === $id)>{{ $name }}</option>
          @endforeach
        </select>
        <x-error :messages="$errors->get('item_condition_after_id')" class="mt-2" />
        <p class="mt-1.5 text-[11.5px] leading-relaxed text-muted-soft">{{ __('Setting this updates the copy\'s current condition.') }}</p>
      </div>
    </div>

    <div class="mb-3.5">
      <label for="{{ $formId }}-description" class="{{ $labelClasses }}">{{ __('Description') }} {!! $optional !!}</label>
      <textarea id="{{ $formId }}-description" name="description" rows="2" placeholder="{{ __('Anything worth recording about this work.') }}" class="mt-1.5 w-full rounded-md border border-hairline bg-input px-3 py-2 text-sm text-ink placeholder-muted-soft">{{ $record?->description }}</textarea>
      <x-error :messages="$errors->get('description')" class="mt-2" />
    </div>

    <button type="button" x-on:click="provenance = ! provenance" class="flex w-full items-center gap-3 rounded-md border border-hairline px-3.5 py-3 text-left mb-3.5" data-test="{{ $formId }}-provenance-toggle">
      <span class="relative h-[22px] w-[38px] shrink-0 rounded-full transition-colors" x-bind:style="provenance ? 'background-color: #6366f1' : 'background-color: #d1d5db'">
        <span class="absolute top-0.5 size-[18px] rounded-full bg-white shadow transition-all" x-bind:style="provenance ? 'left: 18px' : 'left: 2px'"></span>
      </span>
      <span class="min-w-0">
        <span class="block text-[13.5px] font-semibold text-ink">{{ __('Add to the provenance') }}</span>
        <span class="block text-xs text-muted-soft">{{ __('Significant work belongs to the object\'s story. This creates a matching provenance event, and removing it here deletes that event.') }}</span>
      </span>
    </button>
  </div>
</x-history.inline-form>
