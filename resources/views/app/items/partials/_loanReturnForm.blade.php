{{-- The flow for marking a loan as returned, with the date and the condition it came back in. --}}

@php
    $formId = 'return-loan-'.$loan->id;
    $labelClasses = 'block text-[11px] font-semibold tracking-wide text-muted-soft uppercase';
    $inputClasses = 'mt-1.5 h-10 w-full rounded-md border border-hairline bg-input px-3 text-sm text-ink placeholder-muted-soft';
    $optional = '<span class="font-medium normal-case text-muted-soft/70">— '.__('optional').'</span>';
@endphp

<x-history.inline-form
  :form-id="$formId"
  method="put"
  :action="route('loans.return.update', [$catalog, $item, $selectedCopy, $loan])"
  open-var="returning"
  :submit-label="__('Mark as returned')"
  :title="__('Return this loan')"
  :subtitle="$loan->party"
  :data-test="'return-loan-form-'.$loan->id"
>
  <div class="mb-3.5 grid grid-cols-1 gap-3.5 sm:grid-cols-2">
    <div>
      <label for="{{ $formId }}-returned-at" class="{{ $labelClasses }}">{{ __('Returned') }}</label>
      <input id="{{ $formId }}-returned-at" name="returned_at" type="date" value="{{ now()->toDateString() }}" class="{{ $inputClasses }}" data-test="{{ $formId }}-returned-at" />
      <x-error :messages="$errors->get('returned_at')" class="mt-2" />
    </div>

    <div>
      <label for="{{ $formId }}-condition-in" class="{{ $labelClasses }}">{{ __('Condition in') }} {!! $optional !!}</label>
      <select id="{{ $formId }}-condition-in" name="item_condition_in_id" class="{{ $inputClasses }}" data-test="{{ $formId }}-condition-in">
        <option value="">{{ __('—') }}</option>
        @foreach ($conditions as $id => $name)
          <option value="{{ $id }}">{{ $name }}</option>
        @endforeach
      </select>
      <x-error :messages="$errors->get('item_condition_in_id')" class="mt-2" />
      <p class="mt-1.5 text-[11.5px] leading-relaxed text-muted-soft">{{ __('Setting this updates the copy\'s current condition.') }}</p>
    </div>
  </div>
</x-history.inline-form>
