{{-- One editable custom field. Used for both the fields inside a group and the standalone ones. --}}
@use('App\Enums\FieldTypeEnum')

<div id="field-row-{{ $field->id }}" class="rounded-xl border border-hairline bg-canvas p-4" data-test="field-row-{{ $field->id }}">
  <x-form method="put" :action="route('settings.types.fields.order.update', [$type->id, $field->id])" id="field-up-{{ $field->id }}" data-turbo="true" class="hidden">
    <input type="hidden" name="direction" value="up" />
  </x-form>
  <x-form method="put" :action="route('settings.types.fields.order.update', [$type->id, $field->id])" id="field-down-{{ $field->id }}" data-turbo="true" class="hidden">
    <input type="hidden" name="direction" value="down" />
  </x-form>
  <x-form method="delete" :action="route('settings.types.fields.destroy', [$type->id, $field->id])" id="field-delete-{{ $field->id }}" data-turbo="true" class="hidden" onsubmit="return confirm('{{ __('Delete this custom field? The data stored in it on every item will be permanently deleted. This cannot be undone.') }}')"></x-form>

  <x-form method="put" :action="route('settings.types.fields.update', [$type->id, $field->id])" data-turbo="true" onchange="this.requestSubmit()">
    <div class="flex items-end gap-2.5">
      <div class="min-w-0 flex-1">
        <label class="mb-1.5 block text-xs font-semibold text-muted-soft">{{ __('Field name') }}</label>
        <input name="name" value="{{ $field->name }}" placeholder="{{ $placeholder ?? __('e.g. Issue #') }}" data-test="field-name-{{ $field->id }}" class="h-10 w-full rounded-md border border-hairline bg-input px-3 text-sm text-ink" />
      </div>

      <div class="w-40 shrink-0">
        <label class="mb-1.5 block text-xs font-semibold text-muted-soft">{{ __('Field type') }}</label>
        <select name="field_type" class="h-10 w-full appearance-none rounded-md border border-hairline bg-input pl-3 pr-9 text-sm text-ink">
          @foreach ($fieldTypes as $value => $label)
            <option value="{{ $value }}" @selected($field->field_type->value === $value)>{{ $label }}</option>
          @endforeach
        </select>
      </div>

      <div class="flex shrink-0 flex-col">
        <button type="submit" form="field-up-{{ $field->id }}" aria-label="{{ __('Move up') }}" data-test="move-up-{{ $field->id }}" class="flex h-[19px] w-8 cursor-pointer items-center justify-center rounded-t-md border border-hairline text-[10px] text-muted hover:bg-card">▲</button>
        <button type="submit" form="field-down-{{ $field->id }}" aria-label="{{ __('Move down') }}" data-test="move-down-{{ $field->id }}" class="flex h-[19px] w-8 cursor-pointer items-center justify-center rounded-b-md border border-t-0 border-hairline text-[10px] text-muted hover:bg-card">▼</button>
      </div>

      <button type="submit" form="field-delete-{{ $field->id }}" aria-label="{{ __('Remove field') }}" data-test="delete-field-{{ $field->id }}" class="flex size-10 shrink-0 cursor-pointer items-center justify-center rounded-md border border-hairline text-muted hover:bg-card">×</button>
    </div>

    @if ($field->field_type === FieldTypeEnum::Select)
      <div class="mt-3.5 border-t border-hairline-soft pt-3.5">
        <label class="mb-2 block text-xs font-semibold text-muted-soft">{{ __('Options') }}</label>

        <div class="mb-2.5 flex flex-col gap-1.5">
          @foreach ($field->options ?? [] as $option)
            <div class="flex items-center gap-2" data-opt-row>
              <input type="hidden" name="options[]" value="{{ $option }}" />
              <div class="flex-1 rounded-md bg-card px-3 py-2 text-sm font-medium text-ink">{{ $option }}</div>
              <button
                type="button"
                aria-label="{{ __('Remove option') }}"
                data-test="remove-option-{{ $loop->index }}"
                onclick="
                  if (!confirm('{{ __('Delete this option? Any item set to it will lose that value. This cannot be undone.') }}')) return;
                  const f = this.form;
                  this.closest('[data-opt-row]').remove();
                  f.requestSubmit();
                "
                class="flex size-8 cursor-pointer items-center justify-center rounded-md text-muted hover:text-ink">
                ×
              </button>
            </div>
          @endforeach
        </div>

        <div class="flex gap-2">
          <input name="options[]" value="" placeholder="{{ __('Add option…') }}" data-test="option-draft" class="h-9 flex-1 rounded-md border border-hairline bg-input px-3 text-sm text-ink" />
          <button type="submit" data-test="add-option-button" class="h-9 cursor-pointer rounded-md bg-card px-3.5 text-sm font-semibold text-ink">{{ __('Add') }}</button>
        </div>
      </div>
    @endif
  </x-form>
</div>
