@use('App\Enums\ExportSection')

{{--
  Everything known about one item: what it is, then each of its copies with the
  money, the coverage and the history behind it.

  Rendered on its own by the writer rather than inside the document's loop, so a
  collection of several hundred items is turned into markup one item at a time.
--}}
@php
  $currency = $payload->meta()['currency'];
  $identification = $item['item'];

  $blocks = [
      ['key' => 'transactions', 'group' => 'transactions', 'title' => __('Acquisition and transactions')],
      ['key' => 'valuations', 'group' => 'valuations', 'title' => __('Valuation history')],
      ['key' => 'insurance', 'group' => 'insurance', 'title' => __('Insurance')],
      ['key' => 'provenance', 'group' => 'provenance', 'title' => __('Provenance')],
  ];

  $copyColumns = $blueprint->columns($selection, ExportSection::ItemSheets, 'copies');
@endphp

<h2>{{ $identification['sheet.item.name'] ?? $item['name'] }}</h2>

<table style="margin-bottom: 10pt;">
  <tr>
    @php $main = $selection->option('thumbnails') ? $writer->image($identification['sheet.item.main_photo'] ?? null, 260) : null; @endphp

    @if($main !== null)
      <td style="width: 120pt; border: 0; padding-left: 0;">
        <img src="{{ $main }}" style="width: 110pt;" alt="" />
      </td>
    @endif

    <td style="border: 0;">
      @if(!empty($identification['sheet.item.description']))
        <p>{{ $identification['sheet.item.description'] }}</p>
      @endif

      <table class="facts">
        @foreach(['sheet.item.type', 'sheet.item.category', 'sheet.item.set', 'sheet.item.series', 'sheet.item.custom_fields', 'sheet.item.tags'] as $key)
          @continue(! array_key_exists($key, $identification))
          <tr>
            <td class="label">{{ $blueprint->label($key) }}</td>
            <td class="value">{{ $writer->text($key, $identification[$key], $currency) }}</td>
          </tr>
        @endforeach
      </table>
    </td>
  </tr>
</table>

@if($selection->option('thumbnails') && !empty($identification['sheet.item.other_photos']))
  <table style="margin-bottom: 10pt;">
    <tr>
      @foreach(array_slice($identification['sheet.item.other_photos'], 0, 5) as $photo)
        @php $source = $writer->image($photo, 200); @endphp
        @continue($source === null)
        <td style="border: 0; width: 20%; padding-left: 0;"><img src="{{ $source }}" style="width: 78pt;" alt="" /></td>
      @endforeach
    </tr>
  </table>
@endif

@foreach($item['copies'] as $copy)
  <div class="avoid-break">
    <h3>
      {{ $copy['identifier'] !== null && $copy['identifier'] !== '' ? __('Copy :identifier', ['identifier' => $copy['identifier']]) : __('Copy :number', ['number' => $loop->iteration]) }}
    </h3>

    @if($copyColumns !== [])
      <table class="facts" style="margin-bottom: 6pt;">
        @foreach($copyColumns as $column)
          <tr>
            <td class="label" style="width: 26%;">{{ $column['label'] }}</td>
            <td>{{ $writer->text($column['key'], $copy['fields'][$column['key']] ?? null, $currency) }}</td>
          </tr>
        @endforeach
      </table>
    @endif
  </div>

  @foreach($blocks as $block)
    @php $columns = $blueprint->columns($selection, ExportSection::ItemSheets, $block['group']); @endphp
    @continue($columns === [] || $copy[$block['key']] === [])

    <div class="avoid-break">
      <h3>{{ $block['title'] }}</h3>
      @include('exports.pdf.partials._rows', ['columns' => $columns, 'rows' => $copy[$block['key']], 'currency' => $currency])
    </div>
  @endforeach

  @include('exports.pdf.partials._histories', ['copy' => $copy, 'currency' => $currency])
@endforeach
