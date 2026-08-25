{{-- The first page: what this collection is, and when the document was made. --}}
<div style="padding-top: 120pt;">
  @if(isset($cover['cover.name']))
    <h1>{{ $cover['cover.name'] }}</h1>
  @endif

  @if($meta['subtitle'] !== null)
    <p class="muted" style="font-size: 10pt; margin-bottom: 22pt;">{{ $meta['subtitle'] }}</p>
  @endif

  <table class="facts" style="width: 58%; margin-top: 24pt;">
    @foreach($blueprint->group(\App\Enums\ExportSection::Cover, 'cover') as $field)
      @continue($field['key'] === 'cover.name' || ! array_key_exists($field['key'], $cover))
      <tr>
        <td class="label">{{ $field['label'] }}</td>
        <td class="value">{{ $writer->text($field['key'], $cover[$field['key']], $meta['currency']) }}</td>
      </tr>
    @endforeach
  </table>
</div>
