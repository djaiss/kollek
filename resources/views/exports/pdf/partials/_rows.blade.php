{{-- A table of records, drawn from the columns the selection kept. --}}
@foreach($writer->columnSlices($columns) as $slice)
  <table style="font-size: 7.5pt; margin-bottom: {{ $loop->last ? '8pt' : '3pt' }};">
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
