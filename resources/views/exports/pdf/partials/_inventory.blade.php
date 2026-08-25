{{--
  One row per copy.

  Twenty three columns do not fit an A4 page, so the table is drawn in slices:
  each pass repeats the item name and the copy identifier as the anchor and
  carries the next columns along. Every ticked column appears in one of them.
--}}
<h2>{{ __('Inventory') }}</h2>

@if($inventory['rows'] === [])
  <p class="muted">{{ __('No copies to list.') }}</p>
@else
  @php $slices = $writer->columnSlices($inventory['columns'], ['inventory.item_name', 'inventory.copy_identifier']); @endphp

  @foreach($slices as $slice)
    @if(! $loop->first)
      <h3>{{ __('Inventory, continued (:current of :total)', ['current' => $loop->iteration, 'total' => count($slices)]) }}</h3>
    @endif

    <table style="font-size: 7pt; margin-bottom: 12pt;">
      <thead>
        <tr>
          @foreach($slice as $column)
            <th style="padding: 3pt 4pt;">{{ $column['label'] }}</th>
          @endforeach
        </tr>
      </thead>
      <tbody>
        @foreach($inventory['rows'] as $row)
          <tr>
            @foreach($slice as $column)
              <td style="padding: 3pt 4pt;">
                @if($column['key'] === 'inventory.photo')
                  @php $source = $selection->option('thumbnails') ? $writer->image($row[$column['key']] ?? null, 90) : null; @endphp
                  @if($source !== null)
                    <img src="{{ $source }}" style="width: 34pt;" alt="" />
                  @else
                    <span class="muted">—</span>
                  @endif
                @else
                  {{ $writer->text($column['key'], $row[$column['key']] ?? null, $meta['currency']) }}
                @endif
              </td>
            @endforeach
          </tr>
        @endforeach
      </tbody>
    </table>
  @endforeach
@endif
