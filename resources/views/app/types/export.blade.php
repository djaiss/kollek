<x-app-layout>
  <x-slot:title>
    {{ __('Export :name', ['name' => $type->name !== '' ? $type->name : __('Untitled type')]) }}
  </x-slot>

  <div class="px-6 py-8 lg:px-12 lg:py-10">
    <div class="w-full">
      <div class="mb-6 flex items-center gap-2 text-xs text-muted-soft">
        @if (auth()->user()->isOwner())
          <a href="{{ route('settings.index') }}" data-turbo="true" class="font-medium transition-colors hover:text-ink">{{ __('Account settings') }}</a>
        @else
          <span>{{ __('Account settings') }}</span>
        @endif
        <span>/</span>
        <a href="{{ route('settings.types.index') }}" data-turbo="true" class="font-medium transition-colors hover:text-ink">{{ __('Collection types') }}</a>
        <span>/</span>
        <a href="{{ route('settings.types.edit', $type->id) }}" data-turbo="true" class="font-medium transition-colors hover:text-ink">{{ $type->name !== '' ? $type->name : __('Untitled type') }}</a>
        <span>/</span>
        <span class="font-medium text-ink">{{ __('Export') }}</span>
      </div>

      <div class="mb-8 flex items-center gap-3.5">
        <span class="size-11 shrink-0 rounded-full" style="background-color: {{ $type->color }}"></span>

        <div class="min-w-0">
          <h1 class="truncate text-2xl font-semibold tracking-tight text-ink">{{ __('Export :name', ['name' => $type->name !== '' ? $type->name : __('Untitled type')]) }}</h1>
          <p class="mt-0.5 text-sm text-muted">{{ __('A portable JSON snapshot of this type\'s fields, groups and options.') }}</p>
        </div>
      </div>

      <div class="grid grid-cols-1 items-start gap-7 lg:grid-cols-[minmax(0,380px)_minmax(0,1fr)]">
        <div class="flex flex-col gap-6">
          <div>
            <h2 class="mb-2 text-xs font-semibold tracking-wide text-muted-soft uppercase">{{ __('What is this?') }}</h2>
            <p class="text-sm leading-relaxed text-muted">{{ __('The export captures the full schema of this type: every field, its kind, its ordering, its groups and its select options, as a single JSON document.') }}</p>
          </div>

          <div>
            <h2 class="mb-3 text-xs font-semibold tracking-wide text-muted-soft uppercase">{{ __('Why export a type?') }}</h2>

            <div class="flex flex-col gap-3.5">
              @foreach ([
                ['icon' => 'lucide-share-2', 'title' => __('Share with a collaborator'), 'body' => __('Send the JSON to a friend or a teammate so they can rebuild the exact same type in their own account.')],
                ['icon' => 'lucide-rotate-ccw', 'title' => __('Back up before big edits'), 'body' => __('Save a snapshot before restructuring groups or fields, so you always know what the schema looked like before.')],
                ['icon' => 'lucide-copy', 'title' => __('Reuse a proven structure'), 'body' => __('Keep a field structure that works as a reference when setting up a new type, instead of rebuilding it from memory.')],
                ['icon' => 'lucide-git-branch', 'title' => __('Version control and audit'), 'body' => __('Commit the file to a repository or a document store to track how your schema evolves over time.')],
              ] as $reason)
                <div class="flex items-start gap-3">
                  <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-card text-ink">
                    @svg($reason['icon'], 'size-4')
                  </span>
                  <div class="min-w-0">
                    <h3 class="text-sm font-semibold text-ink">{{ $reason['title'] }}</h3>
                    <p class="mt-0.5 text-xs leading-relaxed text-muted">{{ $reason['body'] }}</p>
                  </div>
                </div>
              @endforeach
            </div>
          </div>

          <div class="flex items-start gap-2.5 rounded-lg border border-hairline bg-card px-3.5 py-3 text-xs leading-relaxed text-muted">
            @svg('lucide-info', 'mt-px size-4 shrink-0 text-muted-soft')
            <span>{{ __('This is the structure only. Your items, their copies and their photos are never part of the export.') }}</span>
          </div>
        </div>

        <div
          x-data="{
            copied: false,
            json: @js($json),
            async copy() {
              try {
                await navigator.clipboard.writeText(this.json);
              } catch (error) {
                const area = document.createElement('textarea');
                area.value = this.json;
                document.body.appendChild(area);
                area.select();
                document.execCommand('copy');
                document.body.removeChild(area);
              }

              this.copied = true;
              setTimeout(() => (this.copied = false), 2000);
            },
          }"
          class="overflow-hidden rounded-2xl border border-hairline bg-card"
        >
          <div class="flex flex-wrap items-center gap-3 border-b border-hairline px-4 py-3">
            <div class="flex min-w-0 flex-1 items-center gap-2">
              @svg('lucide-code', 'size-4 shrink-0 text-muted-soft')
              <span class="truncate font-mono text-xs text-muted" data-test="export-file-name">{{ $fileName }}</span>
            </div>

            <span class="shrink-0 font-mono text-[11px] text-muted-soft">{{ __(':count lines', ['count' => $lineCount]) }} · {{ $size }}</span>

            <x-button type="button" x-on:click="copy()" data-test="copy-json-button" class="h-8 shrink-0 text-xs">
              <x-slot:icon>
                <span x-show="! copied">@svg('lucide-copy', 'size-3.5')</span>
                <span x-cloak x-show="copied">@svg('lucide-check', 'size-3.5')</span>
              </x-slot>
              <span x-show="! copied">{{ __('Copy JSON') }}</span>
              <span x-cloak x-show="copied">{{ __('Copied') }}</span>
            </x-button>
          </div>

          <div class="flex max-h-[36rem] overflow-auto bg-canvas">
            <pre class="shrink-0 py-4 pr-2.5 pl-4 text-right font-mono text-xs leading-relaxed text-muted-soft select-none">{{ $gutter }}</pre>
            <pre class="min-w-0 flex-1 py-4 pr-4 pl-3 font-mono text-xs leading-relaxed text-ink" data-test="export-json">{{ $json }}</pre>
          </div>
        </div>
      </div>
    </div>
  </div>
</x-app-layout>
