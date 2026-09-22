{{-- The merged history of a copy, each entry read at the precision it was recorded with. --}}
@use('App\Helpers\ImpreciseDate')

<table style="font-size: 7.5pt; margin-bottom: 8pt;">
  <thead>
    <tr>
      <th style="width: 18%; padding: 3pt 4pt;">{{ __('Date') }}</th>
      <th style="width: 14%; padding: 3pt 4pt;">{{ __('Source') }}</th>
      <th style="padding: 3pt 4pt;">{{ __('Entry') }}</th>
      <th style="width: 15%; padding: 3pt 4pt; text-align: right;">{{ __('Amount') }}</th>
    </tr>
  </thead>
  <tbody>
    @foreach($entries as $entry)
      <tr>
        <td style="padding: 3pt 4pt;">{{ ImpreciseDate::format($entry->date, $entry->precision) }}</td>
        <td style="padding: 3pt 4pt;">{{ $entry->source->label() }}</td>
        <td style="padding: 3pt 4pt;">
          {{ $entry->title }}
          @if($entry->summary !== null)
            <br /><span class="muted">{{ $entry->summary }}</span>
          @endif
        </td>
        <td style="padding: 3pt 4pt; text-align: right;">
          {{ $entry->amountCents === null ? '—' : $writer->money($entry->amountCents, $entry->currencyCode) }}
        </td>
      </tr>
    @endforeach
  </tbody>
</table>
