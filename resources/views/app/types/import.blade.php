@php
  /*
   * The same sentences the import action uses, handed to the browser so it can
   * flag the obvious problems before the document is ever submitted.
   */
  $validatorLabels = [
    'tooLarge' => __('The document is too large to import (:max KB maximum).', ['max' => (int) ($maxLength / 1024)]),
    'invalidJson' => __('This is not valid JSON: :message'),
    'rootMustBeObject' => __('The root of the document must be a JSON object.'),
    'missingSchemaVersion' => __('The document is missing its "schemaVersion" number. Export a type from KolleK to get a valid document.'),
    'missingType' => __('The document must contain a "type" object.'),
    'theType' => __('The type'),
    'group' => __('Group :position'),
    'field' => __(':label, field :position'),
    'standaloneFields' => __('Standalone fields'),
    'groupsMustBeArray' => __('"groups" must be an array.'),
    'fieldsMustBeArray' => __(':label: "fields" must be an array.'),
    'mustBeObject' => __(':label must be an object.'),
    'needsName' => __(':label needs a name.'),
    'unknownType' => __(':label has an unknown type. It must be one of :types.'),
    'needsOptions' => __(':label is a select field, so it needs a non-empty "options" array.'),
    'empty' => __('empty'),
    'chars' => __(':count chars'),
    'idleTitle' => __('Paste a schema to validate it'),
    'validTitle' => __('Schema looks good'),
    'oneProblem' => __('1 problem found'),
    'manyProblems' => __(':count problems found'),
  ];
@endphp

<x-app-layout>
  <x-slot:title>
    {{ __('Import JSON') }}
  </x-slot>

  <div class="px-6 py-8 lg:px-12 lg:py-10">
    <div class="mx-auto w-full max-w-5xl space-y-8">
      <div class="flex items-center gap-1.5 text-[13px]">
        @if (auth()->user()->isOwner())
          <a href="{{ route('settings.index') }}" data-turbo="true" class="font-medium text-muted-soft transition-colors hover:text-ink">{{ __('Account settings') }}</a>
        @else
          <span class="text-muted-soft">{{ __('Account settings') }}</span>
        @endif
        <span class="text-muted-soft">/</span>
        <a href="{{ route('settings.types.index') }}" data-turbo="true" class="font-medium text-muted-soft transition-colors hover:text-ink">{{ __('Collection types') }}</a>
        <span class="text-muted-soft">/</span>
        <span class="font-medium text-ink">{{ __('Import JSON') }}</span>
      </div>

      <div>
        <div class="flex items-center gap-2">
          <h1 class="text-[28px] font-semibold tracking-tight text-ink">{{ __('Import a collection type') }}</h1>
          <x-help id="settings.import_collection_type" />
        </div>
        <p class="mt-1.5 max-w-2xl text-[15px] text-muted">{{ __('Paste a type schema exported from KolleK to recreate it here. Importing creates a brand new type, it never overwrites an existing one.') }}</p>
      </div>

      <x-form method="post" :action="route('settings.types.import.create')" data-turbo="true" data-test="import-type-form">
        <div
          x-data="typeSchemaValidator(@js(['sample' => $sample, 'fieldTypes' => $fieldTypes, 'maxLength' => $maxLength, 'labels' => $validatorLabels]))"
          class="grid grid-cols-1 items-start gap-8 lg:grid-cols-[minmax(0,1fr)_minmax(0,340px)]"
        >
          <div class="flex flex-col gap-4">
            <div class="overflow-hidden rounded-2xl border transition-colors" :class="editorBorderClass">
              <div class="flex flex-wrap items-center gap-3 border-b border-hairline bg-card px-4 py-2.5">
                <div class="flex min-w-0 flex-1 items-center gap-2">
                  @svg('lucide-code', 'size-4 shrink-0 text-muted-soft')
                  <span class="truncate font-mono text-xs text-muted">type-schema.json</span>
                </div>

                <span class="shrink-0 font-mono text-[11px] text-muted-soft" x-text="charLabel" data-test="import-char-count">{{ __('empty') }}</span>

                <div class="flex shrink-0 gap-2">
                  <x-button.secondary type="button" x-on:click="json = sample" class="h-8 text-xs" data-test="load-sample-button">
                    {{ __('Load sample') }}
                  </x-button.secondary>

                  <x-button.secondary type="button" x-on:click="json = ''" class="h-8 text-xs" data-test="clear-json-button">
                    {{ __('Clear') }}
                  </x-button.secondary>
                </div>
              </div>

              <textarea
                name="json"
                x-ref="editor"
                x-model="json"
                spellcheck="false"
                rows="16"
                placeholder='{ "schemaVersion": 1, "type": { ... } }'
                class="block w-full resize-y bg-canvas p-4 font-mono text-[13px] leading-relaxed whitespace-pre text-ink outline-none"
                data-test="import-json-input"
              >{{ old('json') }}</textarea>
            </div>

            <div class="rounded-xl border p-4 transition-colors" :class="statusBoxClass" data-test="import-status">
              <div class="flex items-center gap-2.5">
                <span class="flex size-6 shrink-0 items-center justify-center rounded-full" :class="statusIconClass">
                  <span x-show="status === 'idle'">@svg('lucide-minus', 'size-3.5')</span>
                  <span x-cloak x-show="status === 'error'">@svg('lucide-alert-circle', 'size-3.5')</span>
                  <span x-cloak x-show="status === 'valid'">@svg('lucide-check', 'size-3.5')</span>
                </span>
                <span class="text-sm font-semibold text-muted" :class="statusTitleClass" x-text="statusTitle">{{ __('Paste a schema to validate it') }}</span>
              </div>

              <ul x-cloak x-show="status === 'error'" class="mt-3 space-y-1.5">
                <template x-for="error in errors" :key="error">
                  <li class="flex items-start gap-2.5 text-[13px] leading-snug text-error">
                    <span class="mt-1.5 size-1 shrink-0 rounded-full bg-error"></span>
                    <span x-text="error"></span>
                  </li>
                </template>
              </ul>

              <div x-cloak x-show="status === 'valid'" class="mt-3 flex flex-wrap gap-2">
                <span class="flex items-center gap-2 rounded-full border border-hairline bg-canvas px-3 py-1.5 text-[13px] font-semibold text-ink">
                  <span class="size-2 shrink-0 rounded-full" :style="`background-color: ${summary.color}`"></span>
                  <span x-text="summary.name"></span>
                </span>
                <span class="flex items-center gap-1.5 rounded-full border border-hairline bg-canvas px-3 py-1.5 text-[13px] text-muted">
                  <span class="font-semibold text-ink" x-text="summary.groups"></span>
                  <span x-text="summary.groups === 1 ? @js(__('group')) : @js(__('groups'))"></span>
                </span>
                <span class="flex items-center gap-1.5 rounded-full border border-hairline bg-canvas px-3 py-1.5 text-[13px] text-muted">
                  <span class="font-semibold text-ink" x-text="summary.fields"></span>
                  <span x-text="summary.fields === 1 ? @js(__('field')) : @js(__('fields'))"></span>
                </span>
              </div>
            </div>

            <x-error :messages="$errors->get('json')" data-test="import-server-errors" />

            <div class="flex items-center justify-end gap-3">
              <x-button.secondary href="{{ route('settings.types.index') }}" turbo="true">
                {{ __('Cancel') }}
              </x-button.secondary>

              <x-button type="submit" x-bind:disabled="status !== 'valid'" data-test="import-type-button">
                <x-slot:icon>
                  @svg('lucide-download', 'size-4')
                </x-slot>
                {{ __('Import type') }}
              </x-button>
            </div>
          </div>

          <div class="flex flex-col gap-6 lg:sticky lg:top-6">
            <div>
              <h2 class="mb-3 text-xs font-semibold tracking-wide text-muted-soft uppercase">{{ __('How to import') }}</h2>

              <div class="flex flex-col gap-3.5">
                @foreach ([
                  __('On the type you want to copy, open Export as JSON and copy the document to your clipboard.'),
                  __('Paste it into the editor on the left, or press Load sample to try one out.'),
                  __('Fix anything we flag, then press Import type to create the new type.'),
                ] as $index => $step)
                  <div class="flex items-start gap-3">
                    <span class="flex size-6 shrink-0 items-center justify-center rounded-full border border-hairline bg-card text-xs font-semibold text-ink">{{ $index + 1 }}</span>
                    <p class="text-[13px] leading-relaxed text-muted">{{ $step }}</p>
                  </div>
                @endforeach
              </div>
            </div>

            <div class="h-px bg-hairline-soft"></div>

            <div>
              <h2 class="mb-2.5 text-xs font-semibold tracking-wide text-muted-soft uppercase">{{ __('Expected shape') }}</h2>
              <pre class="overflow-auto rounded-lg border border-hairline bg-card p-3.5 font-mono text-[11.5px] leading-relaxed text-muted">{{ $sample }}</pre>
            </div>

            <div class="flex items-start gap-2.5 rounded-lg border border-hairline bg-card px-3.5 py-3 text-xs leading-relaxed text-muted">
              @svg('lucide-info', 'mt-px size-4 shrink-0 text-muted-soft')
              <span>{{ __('Field types must be one of :types. A select field needs a non-empty options array.', ['types' => implode(', ', $fieldTypes)]) }}</span>
            </div>
          </div>
        </div>
      </x-form>
    </div>
  </div>
</x-app-layout>
