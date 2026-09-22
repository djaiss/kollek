{{-- The form behind moving a copy to a new location and correcting a past move. --}}

@php
    $isEdit = $record !== null;

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
  :title="$isEdit ? __('Correcting a move') : __('Move this copy')"
  :subtitle="$record?->location?->name"
  :data-test="$dataTest"
  :delete-action="$isEdit ? $deleteAction : null"
  :delete-confirm="__('Delete this location record? This cannot be undone.')"
  :delete-data-test="$isEdit ? 'delete-location-'.$record->id : null"
>
  <div>
    <div class="mb-3.5 grid grid-cols-1 gap-3.5 sm:grid-cols-2">
      <div>
        <label for="{{ $formId }}-location" class="{{ $labelClasses }}">{{ $isEdit ? __('Location') : __('Move to') }}</label>
        <select id="{{ $formId }}-location" name="location_id" class="{{ $inputClasses }}" data-test="{{ $formId }}-location">
          <option value="">{{ __('Select a location') }}</option>
          @foreach ($locations as $id => $name)
            <option value="{{ $id }}" @selected($record?->location_id === $id)>{{ $name }}</option>
          @endforeach
        </select>
        <x-error :messages="$errors->get('location_id')" class="mt-2" />
      </div>

      <div>
        <label for="{{ $formId }}-moved-at" class="{{ $labelClasses }}">{{ __('Moved in') }}</label>
        <input id="{{ $formId }}-moved-at" name="moved_at" type="date" value="{{ $record?->moved_at?->toDateString() ?? now()->toDateString() }}" class="{{ $inputClasses }}" data-test="{{ $formId }}-moved-at" />
        <x-error :messages="$errors->get('moved_at')" class="mt-2" />
      </div>
    </div>

    @if ($isEdit)
      <div class="mb-3.5">
        <label for="{{ $formId }}-moved-out-at" class="{{ $labelClasses }}">{{ __('Moved out') }} {!! $optional !!}</label>
        <input id="{{ $formId }}-moved-out-at" name="moved_out_at" type="date" value="{{ $record?->moved_out_at?->toDateString() }}" placeholder="{{ __('Leave blank while still here') }}" class="{{ $inputClasses }}" />
        <x-error :messages="$errors->get('moved_out_at')" class="mt-2" />
      </div>
    @endif

    <div class="mb-3.5">
      <label for="{{ $formId }}-reason" class="{{ $labelClasses }}">{{ __('Reason') }} {!! $optional !!}</label>
      <input id="{{ $formId }}-reason" name="reason" value="{{ $record?->reason }}" placeholder="{{ __('Why the copy was moved') }}" class="{{ $inputClasses }}" />
      <x-error :messages="$errors->get('reason')" class="mt-2" />
    </div>

    <div class="mb-4">
      <label for="{{ $formId }}-note" class="{{ $labelClasses }}">{{ __('Note') }} {!! $optional !!}</label>
      <textarea id="{{ $formId }}-note" name="note" rows="2" placeholder="{{ __('Anything worth recording about this move.') }}" class="mt-1.5 w-full rounded-md border border-hairline bg-input px-3 py-2 text-sm text-ink placeholder-muted-soft">{{ $record?->note }}</textarea>
      <x-error :messages="$errors->get('note')" class="mt-2" />
    </div>
  </div>
</x-history.inline-form>
