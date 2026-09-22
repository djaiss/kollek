{{-- The form behind both attaching and editing a document. --}}

@use('App\Enums\DocumentType')

@php
    $isEdit = $document !== null;
    $accept = implode(',', config('documents.allowed_mime_types'));
    $maxKb = (int) config('documents.max_size_in_kilobytes');
    $maxMb = (int) ($maxKb / 1024);

    $labelClasses = 'block text-[11px] font-semibold tracking-wide text-muted-soft uppercase';
    $inputClasses = 'mt-1.5 h-10 w-full rounded-md border border-hairline bg-input px-3 text-sm text-ink placeholder-muted-soft';
    $optional = '<span class="font-medium normal-case text-muted-soft/70">— '.__('optional').'</span>';
@endphp

<x-form
  :method="$method"
  :action="$action"
  :upload="! $isEdit"
  :data-test="$dataTest"
  class="overflow-hidden rounded-xl border border-hairline"
>
  <div class="flex items-center gap-2 border-b border-hairline bg-card/40 px-5 py-3.5">
    <span class="size-2 shrink-0 rounded-full" style="background-color: #64748b"></span>
    <span class="text-sm font-semibold text-ink">{{ $isEdit ? __('Editing document') : __('New document') }}</span>
    @if ($isEdit)
      <span class="truncate text-sm text-muted-soft">· {{ $document->name }}</span>
    @endif
  </div>

  <div class="p-5">
    @unless ($isEdit)
      <input type="hidden" name="documentable_type" value="{{ $documentableType }}" />
      <input type="hidden" name="documentable_id" value="{{ $documentableId }}" />

      <div
        x-data="{
          source: 'file',
          filename: '',
          over: false,
          sizeError: '',
          pick(files) {
            this.sizeError = ''
            // Reject an oversized file in the browser and clear the input, so the
            // request never carries it and never bounces off the server limit.
            if (window.oversizedFiles(files, {{ $maxKb }}).length) {
              // The semicolon is load bearing. A blade echo compiles to a php echo, and php
              // eats the newline after the closing tag, gluing the next statement onto this one.
              this.sizeError = @js(__('The file must be under :size MB.', ['size' => $maxMb]));
              this.$refs.file.value = ''
              this.filename = ''
              return
            }
            this.filename = files[0]?.name ?? ''
          },
        }"
        class="mb-3.5"
      >
        <div class="mb-2.5 inline-flex rounded-md border border-hairline p-0.5">
          <button type="button" x-on:click="source = 'file'" x-bind:class="source === 'file' ? 'bg-card text-ink' : 'text-muted'" class="rounded px-3 py-1.5 text-[13px] font-semibold" data-test="{{ $formId }}-source-file">{{ __('Upload a file') }}</button>
          <button type="button" x-on:click="source = 'url'" x-bind:class="source === 'url' ? 'bg-card text-ink' : 'text-muted'" class="rounded px-3 py-1.5 text-[13px] font-semibold" data-test="{{ $formId }}-source-url">{{ __('Link to a file') }}</button>
        </div>

        <div
          x-show="source === 'file'"
          x-on:dragover.prevent="over = true"
          x-on:dragleave.prevent="over = false"
          x-on:drop.prevent="over = false; $refs.file.files = $event.dataTransfer.files; pick($refs.file.files)"
          x-bind:class="over ? 'border-ink bg-card/60' : 'border-hairline'"
          class="rounded-md border border-dashed px-4 py-6 text-center transition-colors"
        >
          <input type="file" name="file" x-ref="file" accept="{{ $accept }}" id="{{ $formId }}-file" class="hidden" x-on:change="pick($refs.file.files)" data-test="{{ $formId }}-file" />
          <label for="{{ $formId }}-file" class="flex cursor-pointer flex-col items-center gap-2">
            <x-lucide-upload class="size-6 text-muted" />
            <span class="text-[13px] font-semibold text-ink" x-text="filename || '{{ __('Drop a file here, or click to choose') }}'"></span>
            <span class="text-xs text-muted-soft">{{ __('PDF, image, document or spreadsheet, up to :size MB.', ['size' => $maxMb]) }}</span>
          </label>
        </div>

        <p x-show="sizeError" x-cloak x-text="sizeError" class="mt-2 text-sm text-error"></p>

        <div x-show="source === 'url'" x-cloak>
          <input name="external_url" type="url" value="{{ old('external_url') }}" placeholder="https://" class="{{ $inputClasses }} !mt-0" data-test="{{ $formId }}-external-url" />
        </div>
      </div>

      <x-error :messages="$errors->get('file')" class="mb-2" />
      <x-error :messages="$errors->get('external_url')" class="mb-2" />
    @endunless

    <div class="mb-3.5 grid grid-cols-1 gap-3.5 sm:grid-cols-2">
      <div>
        <label for="{{ $formId }}-type" class="{{ $labelClasses }}">{{ __('Type') }}</label>
        <select id="{{ $formId }}-type" name="type" class="{{ $inputClasses }}" data-test="{{ $formId }}-type">
          @foreach (DocumentType::options() as $value => $label)
            <option value="{{ $value }}" @selected(($document?->type->value ?? DocumentType::Receipt->value) === $value)>{{ $label }}</option>
          @endforeach
        </select>
        <x-error :messages="$errors->get('type')" class="mt-2" />
      </div>

      <div>
        <label for="{{ $formId }}-issued-at" class="{{ $labelClasses }}">{{ __('Issued') }} {!! $optional !!}</label>
        <input id="{{ $formId }}-issued-at" name="issued_at" type="date" value="{{ $document?->issued_at?->toDateString() }}" class="{{ $inputClasses }}" data-test="{{ $formId }}-issued-at" />
        <x-error :messages="$errors->get('issued_at')" class="mt-2" />
      </div>
    </div>

    <div class="mb-3.5">
      <label for="{{ $formId }}-name" class="{{ $labelClasses }}">{{ __('Name') }}</label>
      <input id="{{ $formId }}-name" name="name" value="{{ $document?->name }}" placeholder="{{ __('What this document is') }}" class="{{ $inputClasses }}" data-test="{{ $formId }}-name" />
      <x-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div class="mb-3.5">
      <label for="{{ $formId }}-reference" class="{{ $labelClasses }}">{{ __('Reference number') }} {!! $optional !!}</label>
      <input id="{{ $formId }}-reference" name="reference_number" value="{{ $document?->reference_number }}" placeholder="{{ __('Invoice or certificate number') }}" class="{{ $inputClasses }}" data-test="{{ $formId }}-reference" />
      <x-error :messages="$errors->get('reference_number')" class="mt-2" />
    </div>

    <div class="mb-4">
      <label for="{{ $formId }}-description" class="{{ $labelClasses }}">{{ __('Description') }} {!! $optional !!}</label>
      <textarea id="{{ $formId }}-description" name="description" rows="2" placeholder="{{ __('A note about this document.') }}" class="mt-1.5 w-full rounded-md border border-hairline bg-input px-3 py-2 text-sm text-ink placeholder-muted-soft">{{ $document?->description }}</textarea>
      <x-error :messages="$errors->get('description')" class="mt-2" />
    </div>

    <div class="flex items-center gap-2.5">
      <x-button type="submit" class="text-[13px]" data-test="{{ $formId }}-submit">
        {{ $submitLabel }}
      </x-button>

      <x-button.secondary type="button" x-on:click="{{ $openVar }} = false" class="text-[13px]">
        {{ __('Cancel') }}
      </x-button.secondary>
    </div>
  </div>
</x-form>
