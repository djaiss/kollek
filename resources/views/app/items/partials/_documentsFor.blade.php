{{-- The documents attached to one record, reused by every panel that shows them. --}}

@use('App\Enums\DocumentType')

@php
    $documents = $documentable->documents;
    $documentableType = $documentable->getMorphClass();
    $documentableId = $documentable->getKey();
    $scope = $documentableType.'-'.$documentableId;

    $iconFor = function (?string $mime): string {
        if ($mime === null) {
            return 'link';
        }

        if (str_starts_with($mime, 'image/')) {
            return 'image';
        }

        if ($mime === 'application/pdf') {
            return 'file-text';
        }

        if (str_contains($mime, 'spreadsheet') || str_contains($mime, 'excel') || $mime === 'text/csv') {
            return 'table';
        }

        return 'file';
    };
@endphp

<div x-data="{ adding: false }" data-test="documents-for-{{ $scope }}">
  @if ($canManage)
    <div class="mb-3 flex justify-end">
      <x-button.secondary type="button" x-on:click="adding = ! adding" class="!h-8 !px-3 text-[12.5px]" data-test="new-document-{{ $scope }}">
        <x-slot:icon>
          <x-lucide-plus class="size-3.5" />
        </x-slot>
        {{ __('Document') }}
      </x-button.secondary>
    </div>

    <div x-show="adding" x-cloak class="mb-3.5">
      @include('app.items.partials._documentForm', [
          'formId' => 'add-document-'.$scope,
          'action' => route('documents.create', [$catalog, $item, $selectedCopy]),
          'method' => 'post',
          'submitLabel' => __('Attach document'),
          'dataTest' => 'create-document-form-'.$scope,
          'openVar' => 'adding',
          'document' => null,
          'documentableType' => $documentableType,
          'documentableId' => $documentableId,
      ])
    </div>
  @endif

  <div class="flex flex-col gap-2.5">
    @forelse ($documents as $document)
      <div class="overflow-hidden rounded-lg border border-hairline" x-data="{ editing: false }" data-test="document-{{ $document->id }}">
        <div class="flex flex-wrap items-center gap-3 px-4 py-3">
          <span class="flex size-9 shrink-0 items-center justify-center rounded-md bg-card text-muted">
            <x-dynamic-component :component="'lucide-'.$iconFor($document->mime_type)" class="size-4.5" />
          </span>

          <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
              <a href="{{ $document->url() }}" target="_blank" rel="noopener" class="truncate text-[14px] font-semibold text-ink hover:underline" data-test="document-name-{{ $document->id }}">{{ $document->name }}</a>

              @if ($document->isExternal())
                <span class="inline-flex items-center gap-1 rounded-full bg-card px-2 py-0.5 text-[10.5px] font-semibold text-muted-soft" data-test="document-external-{{ $document->id }}">
                  <x-lucide-external-link class="size-3" />
                  {{ __('Link') }}
                </span>
              @endif
            </div>

            <p class="mt-0.5 truncate text-xs text-muted-soft">
              {{ $document->type->label() }}
              @if ($document->issued_at)
                · {{ $document->issued_at->isoFormat('MMM D, YYYY') }}
              @endif
              @if ($document->humanSize())
                · {{ $document->humanSize() }}
              @endif
              @if ($document->reference_number)
                · {{ $document->reference_number }}
              @endif
            </p>
          </div>

          <div class="flex shrink-0 items-center gap-3">
            <a href="{{ $document->url() }}" target="_blank" rel="noopener" class="text-muted hover:text-ink" title="{{ $document->isExternal() ? __('Open link') : __('Download') }}" data-test="download-document-{{ $document->id }}">
              @if ($document->isExternal())
                <x-lucide-external-link class="size-4" />
              @else
                <x-lucide-download class="size-4" />
              @endif
            </a>

            @if ($canManage)
              <button type="button" x-on:click="editing = ! editing" class="text-[12.5px] font-semibold text-muted hover:text-ink" data-test="edit-document-{{ $document->id }}">
                {{ __('Edit') }}
              </button>
            @endif
          </div>
        </div>

        @if ($document->description)
          <div class="border-t border-hairline px-4 py-2.5">
            <p class="text-[12.5px] leading-relaxed text-muted">{{ $document->description }}</p>
          </div>
        @endif

        @if ($canManage)
          <div x-show="editing" x-cloak class="border-t border-hairline bg-card/40 p-4">
            @include('app.items.partials._documentForm', [
                'formId' => 'edit-document-'.$document->id,
                'action' => route('documents.update', [$catalog, $item, $selectedCopy, $document]),
                'method' => 'put',
                'submitLabel' => __('Save changes'),
                'dataTest' => 'edit-document-form-'.$document->id,
                'openVar' => 'editing',
                'document' => $document,
                'documentableType' => $documentableType,
                'documentableId' => $documentableId,
            ])

            <x-form
              method="delete"
              :action="route('documents.destroy', [$catalog, $item, $selectedCopy, $document])"
              onsubmit="return confirm('{{ __('Delete this document? The file is removed with it and this cannot be undone.') }}')"
              class="mt-3"
            >
              <button type="submit" class="text-[13px] font-semibold text-error hover:underline" data-test="delete-document-{{ $document->id }}">
                {{ __('Delete document') }}
              </button>
            </x-form>
          </div>
        @endif
      </div>
    @empty
      <p x-show="! adding" class="rounded-lg border border-dashed border-hairline px-4 py-3 text-[12.5px] text-muted-soft" data-test="no-documents-{{ $scope }}">
        {{ __('No documents attached yet.') }}
      </p>
    @endforelse
  </div>
</div>
