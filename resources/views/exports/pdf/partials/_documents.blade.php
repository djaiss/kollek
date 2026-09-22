{{-- The register of the documents on file and what each one is attached to. --}}
<h2>{{ __('Document register') }}</h2>

@if($documents['rows'] === [])
  <p class="muted">{{ __('No documents on file.') }}</p>
@else
  <table>
    <thead>
      <tr>
        @foreach($documents['columns'] as $column)
          <th>{{ $column['label'] }}</th>
        @endforeach
      </tr>
    </thead>
    <tbody>
      @foreach($documents['rows'] as $row)
        <tr>
          @foreach($documents['columns'] as $column)
            <td>{{ $writer->text($column['key'], $row[$column['key']] ?? null, $meta['currency']) }}</td>
          @endforeach
        </tr>
      @endforeach
    </tbody>
  </table>
@endif
