{{-- The shared frame of the inline add and edit forms in a copy's history. --}}

@props([
  'formId',
  'method',
  'action',
  'openVar',
  'submitLabel',
  'title',
  'dataTest' => null,
  'subtitle' => null,
  'deleteAction' => null,
  'deleteConfirm' => null,
  'deleteLabel' => null,
  'deleteDataTest' => null,
])

@if ($deleteAction)
  <x-form
    method="delete"
    :action="$deleteAction"
    :id="$formId.'-delete'"
    x-target="history-panel notifications"
    class="hidden"
  ></x-form>
@endif

<x-form
  :method="$method"
  :action="$action"
  :id="$formId"
  :data-test="$dataTest"
  x-target="history-panel notifications"
  x-on:ajax:after="{{ $openVar }} = document.querySelector('#{{ $formId }}-fields .text-error') !== null"
  class="rounded-xl border border-brand/60"
>
  <div class="flex items-center gap-2 rounded-t-xl border-b border-brand/15 bg-brand/8 px-5 py-3.5">
    <span class="size-2 shrink-0 rounded-full bg-brand"></span>
    <span class="text-sm font-semibold text-brand">{{ $title }}</span>
    @if ($subtitle)
      <span class="truncate text-sm text-muted-soft">· {{ $subtitle }}</span>
    @endif
  </div>

  <div id="{{ $formId }}-fields" class="p-5">
    {{ $slot }}

    <div class="flex items-center justify-between gap-2.5">
      <div class="flex items-center gap-2.5">
        <x-button type="submit" class="text-[13px]" data-test="{{ $formId }}-submit">
          {{ $submitLabel }}
        </x-button>

        <x-button.secondary type="button" x-on:click="{{ $openVar }} = false" class="text-[13px]">
          {{ __('Cancel') }}
        </x-button.secondary>
      </div>

      @if ($deleteAction)
        <button type="submit" form="{{ $formId }}-delete" x-on:click="if (! confirm('{{ $deleteConfirm }}')) { $event.preventDefault() }" class="text-[13px] font-semibold text-error hover:underline" data-test="{{ $deleteDataTest }}">
          {{ $deleteLabel ?? __('Delete') }}
        </button>
      @endif
    </div>
  </div>
</x-form>
