@use('App\Enums\ExportFormat')

{{--
  The summary card: what the current selection comes to, the switches that change
  how the file is built, and the button that makes it.

  The page estimate is only meaningful for the document, so the workbook shows its
  sheet count instead. Everything here is read from the Alpine state, so it moves
  as boxes are ticked.
--}}
<div class="flex flex-col gap-4 lg:sticky lg:top-6">
  <div class="rounded-xl border border-hairline p-5">
    <p class="text-[11px] font-semibold tracking-wide text-muted-soft uppercase">{{ __('Summary') }}</p>

    <div class="mt-3 flex items-baseline gap-2" x-show="isPdf" x-cloak>
      <span class="text-[34px] leading-none font-semibold tracking-tight text-ink" x-text="new Intl.NumberFormat().format(pages)">—</span>
      <span class="text-[13px] text-muted">{{ __('estimated pages') }}</span>
    </div>

    <div class="mt-3 flex items-baseline gap-2" x-show="! isPdf" x-cloak>
      <span class="text-[34px] leading-none font-semibold tracking-tight text-ink" x-text="checkedSections">—</span>
      <span class="text-[13px] text-muted">{{ __('sheets of data') }}</span>
    </div>

    <p class="mt-1 font-mono text-[11px] text-muted-soft" x-show="isPdf" x-cloak>{{ __('A4 portrait') }}</p>

    <dl class="mt-4 flex flex-col gap-2 border-t border-hairline pt-4 text-[13px]">
      <div class="flex items-center justify-between">
        <dt class="text-muted">{{ __('Sections included') }}</dt>
        <dd class="font-medium text-ink"><span x-text="checkedSections">{{ $export->sectionCount() }}</span> / {{ $export->sectionCount() }}</dd>
      </div>
      <div class="flex items-center justify-between">
        <dt class="text-muted">{{ __('Fields ticked') }}</dt>
        <dd class="font-medium text-ink"><span x-text="checkedFields">{{ $export->fieldCount() }}</span> / {{ $export->fieldCount() }}</dd>
      </div>
      <div class="flex items-center justify-between">
        <dt class="text-muted">{{ __('Items') }}</dt>
        <dd class="font-medium text-ink">{{ number_format($counts['items']) }}</dd>
      </div>
      <div class="flex items-center justify-between">
        <dt class="text-muted">{{ __('Copies') }}</dt>
        <dd class="font-medium text-ink">{{ number_format($counts['copies']) }}</dd>
      </div>
      <div class="flex items-center justify-between">
        <dt class="text-muted">{{ __('Documents referenced') }}</dt>
        <dd class="font-medium text-ink">{{ number_format($counts['documents']) }}</dd>
      </div>
    </dl>

    <div class="mt-4 border-t border-hairline pt-4">
      <p class="text-[11px] font-semibold tracking-wide text-muted-soft uppercase">{{ __('Options') }}</p>

      <div class="mt-3 flex flex-col gap-3">
        @foreach($export->options() as $option)
          <div x-show="{{ json_encode($option['formats']) }}.includes(format)" x-cloak>
            <label class="flex cursor-pointer items-center justify-between gap-3">
              <span class="text-[13px] text-body">{{ $option['label'] }}</span>

              {{-- A checkbox drawn as a switch: the input carries the value so it
                   posts with the form, and the track is painted off its state. --}}
              <span class="relative shrink-0">
                <input
                  type="checkbox"
                  name="options[{{ $option['key'] }}]"
                  value="1"
                  x-model="options['{{ $option['key'] }}']"
                  class="peer sr-only"
                  data-test="option-{{ $option['key'] }}"
                />
                <span class="block h-6 w-11 rounded-full border border-hairline bg-card transition-colors peer-checked:border-ink peer-checked:bg-ink"></span>
                <span class="pointer-events-none absolute top-[3px] left-[3px] size-[18px] rounded-full bg-canvas shadow transition-transform duration-200 peer-checked:translate-x-[20px]"></span>
              </span>
            </label>

            @if($option['note'] !== null)
              <p class="mt-1 pr-14 text-[11px] leading-relaxed text-muted-soft">{{ $option['note'] }}</p>
            @endif
          </div>
        @endforeach
      </div>
    </div>

    <p class="mt-4 font-mono text-[11px] break-all text-muted-soft" x-text="fileName">{{ $export->fileName($defaultFormat) }}</p>

    <x-button type="submit" class="mt-3 w-full" data-test="generate-export-button">
      <x-slot:icon>
        @svg('lucide-download', 'size-4')
      </x-slot>
      <span x-show="isPdf" x-cloak>{{ __('Generate the PDF') }}</span>
      <span x-show="! isPdf" x-cloak>{{ __('Generate the workbook') }}</span>
    </x-button>
  </div>

  <div class="flex gap-2.5 rounded-xl border border-hairline p-4">
    @svg('lucide-info', 'mt-0.5 size-4 shrink-0 text-muted-soft')
    <p class="text-[13px] leading-relaxed text-muted">
      {{ __('Documents are listed with their metadata. Their files stay in Kollek and are not embedded unless you turn that option on, and then only images can be reproduced.') }}
    </p>
  </div>
</div>
