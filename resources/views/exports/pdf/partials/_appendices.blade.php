@use('App\Enums\ExportSection')
@use('App\Helpers\ImpreciseDate')

{{--
  The appendices repeat what the sheets already showed, gathered collection wide
  so a reader can scan one kind of record across everything at once. The rows were
  collected during the single pass over the items, so nothing is read twice.
--}}
@php
  $currency = $meta['currency'];

  $tables = [
      'transactions' => ['title' => __('Full transaction history'), 'group' => 'transactions'],
      'valuations' => ['title' => __('Full valuation history'), 'group' => 'valuations'],
      'insurance' => ['title' => __('Full insurance history'), 'group' => 'insurance'],
  ];
@endphp

@foreach($tables as $block => $table)
  @continue(! array_key_exists('appendices.'.$block, $appendices) || $appendixRows[$block] === [])

  @php
    // The item and the copy are what place a row once it has left its sheet, so
    // they lead every slice as ordinary columns rather than as extra headers.
    $columns = [
        ['key' => '_item', 'label' => __('Item')],
        ['key' => '_copy', 'label' => __('Copy')],
        ...$blueprint->columns($selection, ExportSection::ItemSheets, $table['group']),
    ];
  @endphp
  @continue(count($columns) <= 2)

  <div class="section page-break">
    <h2>{{ $table['title'] }}</h2>

    @php $rows = array_map(fn (array $entry): array => ['_item' => $entry['item'], '_copy' => $entry['copy'], ...$entry['row']], $appendixRows[$block]); @endphp

    @foreach($writer->columnSlices($columns, ['_item', '_copy']) as $slice)
      @if(! $loop->first)
        <h3>{{ __('Continued (:current of :total)', ['current' => $loop->iteration, 'total' => count($writer->columnSlices($columns, ['_item', '_copy']))]) }}</h3>
      @endif

      <table style="font-size: 7pt; margin-bottom: 10pt;">
        <thead>
          <tr>
            @foreach($slice as $column)
              <th style="padding: 3pt 4pt;">{{ $column['label'] }}</th>
            @endforeach
          </tr>
        </thead>
        <tbody>
          @foreach($rows as $row)
            <tr>
              @foreach($slice as $column)
                <td style="padding: 3pt 4pt;">{{ $writer->text($column['key'], $row[$column['key']] ?? null, $currency) }}</td>
              @endforeach
            </tr>
          @endforeach
        </tbody>
      </table>
    @endforeach
  </div>
@endforeach

@if(array_key_exists('appendices.timeline', $appendices) && $appendixRows['timeline'] !== [])
  <div class="section page-break">
    <h2>{{ __('Full timeline') }}</h2>

    <table style="font-size: 7pt;">
      <thead>
        <tr>
          <th style="width: 14%; padding: 3pt 4pt;">{{ __('Date') }}</th>
          <th style="width: 20%; padding: 3pt 4pt;">{{ __('Item') }}</th>
          <th style="width: 12%; padding: 3pt 4pt;">{{ __('Source') }}</th>
          <th style="padding: 3pt 4pt;">{{ __('Entry') }}</th>
          <th style="width: 13%; padding: 3pt 4pt; text-align: right;">{{ __('Amount') }}</th>
        </tr>
      </thead>
      <tbody>
        @foreach($appendixRows['timeline'] as $entry)
          <tr>
            <td style="padding: 3pt 4pt;">{{ ImpreciseDate::format($entry['entry']->date, $entry['entry']->precision) }}</td>
            <td style="padding: 3pt 4pt;">{{ $entry['item'] }}</td>
            <td style="padding: 3pt 4pt;">{{ $entry['entry']->source->label() }}</td>
            <td style="padding: 3pt 4pt;">{{ $entry['entry']->title }}</td>
            <td style="padding: 3pt 4pt; text-align: right;">
              {{ $entry['entry']->amountCents === null ? '—' : $writer->money($entry['entry']->amountCents, $entry['entry']->currencyCode) }}
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
@endif

@if(array_key_exists('appendices.photos', $appendices) && $selection->option('thumbnails') && $appendixRows['photos'] !== [])
  <div class="section page-break">
    <h2>{{ __('Photographs') }}</h2>

    @foreach(array_chunk($appendixRows['photos'], 2) as $pair)
      <table class="plate avoid-break">
        <tr>
          @foreach($pair as $plate)
            @php $source = $writer->image($plate['photo'], 360); @endphp
            <td style="width: 50%; border: 0; padding-left: 0;">
              @if($source !== null)
                <img src="{{ $source }}" style="width: 210pt;" alt="" />
              @endif
              <br /><span class="muted" style="font-size: 7pt;">{{ $plate['item'] }}</span>
            </td>
          @endforeach
        </tr>
      </table>
    @endforeach
  </div>
@endif

@if(array_key_exists('appendices.documents', $appendices) && $documents !== null && $documents['rows'] !== [])
  <div class="section page-break">
    <h2>{{ __('Supporting documents') }}</h2>

    @if($selection->option('embed_documents'))
      <p class="muted">{{ __('Only image documents can be reproduced here. Every other file stays in Kollek and is listed in the register with its metadata.') }}</p>

      @php
        $plates = [];

        foreach ($documents['rows'] as $row) {
            if (! isset($row['_document'])) {
                continue;
            }

            $source = $writer->documentImage($row['_document']);

            if ($source !== null) {
                $plates[] = ['source' => $source, 'name' => $row['documents.name'] ?? $row['_document']->name];
            }
        }
      @endphp

      @forelse($plates as $plate)
        <div class="plate avoid-break">
          <img src="{{ $plate['source'] }}" style="width: 340pt;" alt="" />
          <br /><span class="muted" style="font-size: 7pt;">{{ $plate['name'] }}</span>
        </div>
      @empty
        <p class="muted">{{ __('None of the documents on file is an image, so none could be reproduced.') }}</p>
      @endforelse
    @else
      <p class="muted">
        {{ __('The documents are listed in the register with their metadata. Their files stay in Kollek and are not embedded in this document.') }}
      </p>
    @endif
  </div>
@endif
