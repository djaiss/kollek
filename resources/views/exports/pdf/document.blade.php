{{--
  The exported document.

  dompdf renders this, not a browser, so the layout is tables and inline styles
  throughout: there is no flexbox, no grid and no modern colour function. Sizes
  are in points because that is what a page is measured in.

  Nothing here decides what to show. Every block was already narrowed by the
  selection before it arrived, so an empty block means the user left it out.
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8" />
    <title>{{ $meta['title'] }}</title>

    <style>
      @page { margin: 34pt 30pt 42pt 30pt; }

      body {
        font-family: 'DejaVu Sans', sans-serif;
        font-size: 8.5pt;
        line-height: 1.45;
        color: #1a1a1a;
      }

      h1 { font-size: 22pt; margin: 0 0 6pt 0; }
      h2 { font-size: 13pt; margin: 0 0 8pt 0; padding-bottom: 4pt; border-bottom: 1pt solid #d8d8d8; }
      h3 { font-size: 10pt; margin: 12pt 0 5pt 0; }
      p { margin: 0 0 6pt 0; }

      .muted { color: #6b6b6b; }
      .page-break { page-break-before: always; }
      .avoid-break { page-break-inside: avoid; }
      .section { margin-bottom: 18pt; }

      table { width: 100%; border-collapse: collapse; }
      th { text-align: left; font-size: 7pt; text-transform: uppercase; letter-spacing: 0.4pt; color: #6b6b6b; padding: 4pt 5pt; border-bottom: 1pt solid #cfcfcf; }
      td { padding: 4pt 5pt; border-bottom: 0.5pt solid #e6e6e6; vertical-align: top; }

      .facts td { border: 0; padding: 3pt 0; }
      .facts .label { color: #6b6b6b; width: 42%; }
      .facts .value { font-weight: bold; }

      .bar-track { background: #ededed; height: 7pt; }
      .bar-fill { background: #2b2b2b; height: 7pt; }

      .swatch { height: 8pt; width: 8pt; }
      .plate { margin-bottom: 10pt; }

      .footer { position: fixed; bottom: -26pt; left: 0; right: 0; font-size: 7pt; color: #8a8a8a; }
    </style>
  </head>

  <body>
    <div class="footer">{{ $meta['title'] }} · {{ $meta['generatedAt']->isoFormat('LL') }}</div>

    @if($cover !== null && $cover !== [])
      @include('exports.pdf.partials._cover')
    @endif

    @if($summary !== null && $summary !== [])
      <div @class(['section', 'page-break' => $cover !== null && $cover !== []])>
        @include('exports.pdf.partials._summary')
      </div>
    @endif

    @if($inventory !== null)
      <div class="section page-break">
        @include('exports.pdf.partials._inventory')
      </div>
    @endif

    @foreach($sheets as $sheet)
      <div @class(['section', 'page-break' => $selection->option('page_per_item') || $loop->first])>
        {!! $sheet !!}
      </div>
    @endforeach

    @if($documents !== null)
      <div class="section page-break">
        @include('exports.pdf.partials._documents')
      </div>
    @endif

    @if($appendices !== null && $appendices !== [])
      @include('exports.pdf.partials._appendices')
    @endif
  </body>
</html>
