@use('App\Enums\ExportSection')

{{--
  The summary. Every chart here is drawn with table cells and a filled bar,
  because dompdf has neither flexbox nor conic gradients: the donut of the
  statistics screen becomes a ranked list, and the value line becomes an svg
  polyline, which is the one drawing primitive dompdf does support.
--}}
<h2>{{ __('Collection summary') }}</h2>

@php
  $currency = $meta['currency'];

  // The six figures that read as a number rather than as a chart.
  $figures = array_filter(
      ['summary.items', 'summary.copies', 'summary.value', 'summary.average', 'summary.value_added_this_month', 'summary.items_added_this_month'],
      fn (string $key): bool => array_key_exists($key, $summary),
  );

  $palette = ['#2b2b2b', '#5b5b5b', '#8a8a8a', '#b0b0b0', '#cfcfcf'];
@endphp

@if($figures !== [])
  <table class="facts avoid-break" style="margin-bottom: 14pt;">
    @foreach(array_chunk($figures, 3) as $chunk)
      <tr>
        @foreach($chunk as $key)
          <td style="width: 33%; padding-bottom: 8pt;">
            <span class="muted" style="font-size: 7.5pt;">{{ $blueprint->label($key) }}</span><br />
            <span style="font-size: 12pt; font-weight: bold;">{{ $writer->text($key, $summary[$key], $currency) }}</span>
          </td>
        @endforeach
      </tr>
    @endforeach
  </table>
@endif

@if(!empty($summary['summary.value_over_time']))
  @php
    $line = $writer->lineChart($summary['summary.value_over_time']);
    $last = end($summary['summary.value_over_time']);
  @endphp

  @if($line !== null)
    <div class="avoid-break">
      <h3>{{ __('Value over the last twelve months') }}</h3>
      <img src="{{ $line }}" style="width: 375pt;" alt="" />
      <table style="margin-top: 2pt; width: 375pt;">
        <tr>
          <td class="muted" style="border: 0; padding: 0; font-size: 7pt;">{{ $summary['summary.value_over_time'][0]['label'] ?? '' }}</td>
          <td class="muted" style="border: 0; padding: 0; font-size: 7pt; text-align: right;">
            {{ $last['label'] ?? '' }} · {{ $writer->money((int) ($last['value'] ?? 0), $currency) }}
          </td>
        </tr>
      </table>
    </div>
  @endif
@endif

@if(!empty($summary['summary.acquisitions_per_month']))
  @php $peak = max(array_column($summary['summary.acquisitions_per_month'], 'count')) ?: 1; @endphp

  <div class="avoid-break">
    <h3>{{ __('Acquisitions per month') }}</h3>
    <table>
      @foreach($summary['summary.acquisitions_per_month'] as $month)
        <tr>
          <td style="width: 18%; border: 0;">{{ $month['label'] }}</td>
          <td style="border: 0;">
            <div class="bar-track"><div class="bar-fill" style="width: {{ $writer->share($month['count'], $peak) }}%;"></div></div>
          </td>
          <td style="width: 10%; border: 0; text-align: right;">{{ number_format($month['count']) }}</td>
        </tr>
      @endforeach
    </table>
  </div>
@endif

@foreach([
    ['key' => 'summary.by_category', 'title' => __('Items by category'), 'metric' => 'count', 'money' => false],
    ['key' => 'summary.by_condition', 'title' => __('Copies by condition'), 'metric' => 'count', 'money' => false],
    ['key' => 'summary.value_by_location', 'title' => __('Value by location'), 'metric' => 'value', 'money' => true],
  ] as $chart)
  @continue(empty($summary[$chart['key']]))

  @php
    $rows = $summary[$chart['key']];
    $peak = max(array_column($rows, $chart['metric'])) ?: 1;
  @endphp

  <div class="avoid-break">
    <h3>{{ $chart['title'] }}</h3>
    <table>
      @foreach($rows as $index => $row)
        <tr>
          <td style="width: 4%; border: 0;"><div class="swatch" style="background: {{ $palette[$index % count($palette)] }};"></div></td>
          <td style="width: 28%; border: 0;">{{ $row['label'] ?? __('Not recorded') }}</td>
          <td style="border: 0;">
            <div class="bar-track"><div class="bar-fill" style="width: {{ $writer->share($row[$chart['metric']], $peak) }}%; background: {{ $palette[$index % count($palette)] }};"></div></div>
          </td>
          <td style="width: 18%; border: 0; text-align: right;">
            {{ $chart['money'] ? $writer->money((int) $row[$chart['metric']], $currency) : number_format((int) $row[$chart['metric']]) }}
          </td>
        </tr>
      @endforeach
    </table>
  </div>
@endforeach

@if(!empty($summary['summary.top_items']))
  <div class="avoid-break">
    <h3>{{ __('Most valuable items') }}</h3>
    <table>
      <thead>
        <tr>
          <th style="width: 6%;">#</th>
          <th>{{ __('Item') }}</th>
          <th>{{ __('Condition') }}</th>
          <th>{{ __('Location') }}</th>
          <th style="text-align: right;">{{ __('Estimated value') }}</th>
        </tr>
      </thead>
      <tbody>
        @foreach($summary['summary.top_items'] as $index => $row)
          <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $row['item']->name }}</td>
            <td>{{ $row['condition'] ?? '—' }}</td>
            <td>{{ $row['location'] ?? '—' }}</td>
            <td style="text-align: right;">{{ $writer->money((int) $row['value'], $currency) }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
@endif

@if(!empty($summary['summary.sets_completion']))
  @php $sets = $summary['summary.sets_completion']; @endphp

  <div class="avoid-break">
    <h3>{{ __('Progress of sets with a target count') }}</h3>
    <div class="bar-track"><div class="bar-fill" style="width: {{ $sets['percentage'] }}%;"></div></div>
    <p class="muted" style="margin-top: 4pt;">
      {{ __(':owned of :target across :sets sets, :remaining still to find.', ['owned' => number_format($sets['owned']), 'target' => number_format($sets['target']), 'sets' => number_format($sets['sets']), 'remaining' => number_format($sets['remaining'])]) }}
    </p>
  </div>
@endif
