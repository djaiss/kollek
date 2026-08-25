@use('App\Enums\ExportFormat')

{{--
  The export screen: pick a format, tick what belongs in the file.

  One Alpine component holds the whole selection, so the summary card can report
  what the current choice comes to before anything is generated. Underneath it is
  a plain form, and every checkbox carries its own name, so an export still works
  if Alpine never boots; only the live counts are lost in that case.

  The whole grid sits inside the form, because the button that submits it lives in
  the summary card on the right.
--}}
@php
  $counts = $export->counts();

  // Which fields belong to which section, so the browser can tick a whole
  // section at once and count what is left in it.
  $groups = collect($export->sections())
      ->mapWithKeys(fn (array $section): array => [
          $section['key'] => collect($section['groups'])->flatMap(fn (array $group): array => array_column($group['fields'], 'key'))->all(),
      ])
      ->all();
@endphp

<x-app-layout :catalog="$catalog">
  <x-slot:title>{{ __('Export the collection') }}</x-slot>

  <div class="px-6 py-8 lg:px-12 lg:py-10">
    <div
      class="mx-auto w-full max-w-6xl"
      x-data="{
          format: @js($defaultFormat->value),
          fields: @js(array_fill_keys($export->fieldKeys(), true)),
          sections: @js(array_fill_keys(array_keys($groups), true)),
          open: @js(array_fill_keys(array_keys($groups), true)),
          options: @js(collect($export->options())->mapWithKeys(fn (array $option): array => [$option['key'] => $option['default']])->all()),
          groups: @js($groups),
          fileNames: @js($export->fileNames()),
          counts: @js($counts),
          weights: @js($export->pageWeights()),
          fieldsOf(section) { return this.groups[section] ?? []; },
          checkedIn(section) {
              return this.sections[section] ? this.fieldsOf(section).filter(key => this.fields[key]).length : 0;
          },
          get checkedFields() {
              return Object.keys(this.groups).reduce((total, section) => total + this.checkedIn(section), 0);
          },
          get checkedSections() {
              return Object.values(this.sections).filter(Boolean).length;
          },
          toggleSection(section, value) {
              this.sections[section] = value;
              this.fieldsOf(section).forEach(key => { this.fields[key] = value; });
          },
          setAll(value) {
              Object.keys(this.sections).forEach(section => this.toggleSection(section, value));
          },
          get isPdf() { return this.format === '{{ ExportFormat::Pdf->value }}'; },
          get fileName() { return this.fileNames[this.format]; },
          {{-- A rough page count, so the size of the choice is visible before the
               file is made. The inventory and the register are rows on a page,
               and an item sheet is a page and a bit. --}}
          get pages() {
              let total = 0;

              if (this.checkedIn('cover') > 0) { total += 1; }
              if (this.checkedIn('summary') > 0) { total += 2; }
              if (this.checkedIn('inventory') > 0) { total += Math.ceil(this.counts.copies * this.weights.copies); }
              if (this.checkedIn('item_sheets') > 0) { total += this.counts.items * this.weights.sheets; }
              if (this.checkedIn('documents') > 0) { total += Math.ceil(this.counts.documents * this.weights.documents); }
              if (this.checkedIn('appendices') > 0) { total += Math.ceil(this.counts.copies * this.weights.copies) + 1; }

              return Math.max(0, Math.round(total));
          },
      }"
    >
      <x-form method="post" :action="route('collections.export.create', $catalog)">
        @include('app.catalogs.export.partials._header')

        <div class="mt-7 grid grid-cols-1 items-start gap-6 lg:grid-cols-[1fr_320px]">
          <div class="flex flex-col gap-4">
            @include('app.catalogs.export.partials._formats')

            @foreach($export->sections() as $section)
              @include('app.catalogs.export.partials._section', ['section' => $section])
            @endforeach
          </div>

          @include('app.catalogs.export.partials._summary')
        </div>
      </x-form>
    </div>
  </div>
</x-app-layout>
