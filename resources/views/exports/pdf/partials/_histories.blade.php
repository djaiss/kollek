@use('App\Helpers\ImpreciseDate')

{{--
  The histories that are lists of their own shape rather than rows of catalogue
  fields: where a copy has been, who has borrowed it, what has been done to it,
  and the one line that merges all of it.
--}}
@php
  $histories = $copy['histories'];

  $tables = [
      'sheet.history.locations' => [
          'title' => __('Location history'),
          'columns' => [
              ['key' => 'location', 'label' => __('Location')],
              ['key' => 'moved_at', 'label' => __('Moved in')],
              ['key' => 'moved_out_at', 'label' => __('Moved out')],
              ['key' => 'reason', 'label' => __('Reason')],
              ['key' => 'note', 'label' => __('Note')],
          ],
      ],
      'sheet.history.loans' => [
          'title' => __('Loans'),
          'columns' => [
              ['key' => 'direction', 'label' => __('Direction')],
              ['key' => 'status', 'label' => __('Status')],
              ['key' => 'party', 'label' => __('Party')],
              ['key' => 'purpose', 'label' => __('Purpose')],
              ['key' => 'loaned_at', 'label' => __('Loaned on')],
              ['key' => 'due_at', 'label' => __('Due')],
              ['key' => 'returned_at', 'label' => __('Returned')],
          ],
      ],
      'sheet.history.loan_conditions' => [
          'title' => __('Condition before and after a loan'),
          'columns' => [
              ['key' => 'party', 'label' => __('Party')],
              ['key' => 'before', 'label' => __('Condition out')],
              ['key' => 'after', 'label' => __('Condition in')],
          ],
      ],
      'sheet.history.loan_deposits' => [
          'title' => __('Deposits held against a loan'),
          'columns' => [
              ['key' => 'party', 'label' => __('Party')],
              ['key' => 'amount', 'label' => __('Deposit')],
              ['key' => 'currency', 'label' => __('Currency')],
              ['key' => 'returned_at', 'label' => __('Returned')],
          ],
      ],
      'sheet.history.maintenance' => [
          'title' => __('Maintenance and restoration'),
          'columns' => [
              ['key' => 'type', 'label' => __('Type')],
              ['key' => 'title', 'label' => __('Title')],
              ['key' => 'performed_by', 'label' => __('Performed by')],
              ['key' => 'performed_at', 'label' => __('Performed on')],
              ['key' => 'cost', 'label' => __('Cost')],
              ['key' => 'before', 'label' => __('Condition before')],
              ['key' => 'after', 'label' => __('Condition after')],
              ['key' => 'next_due_at', 'label' => __('Next due')],
          ],
      ],
  ];
@endphp

@foreach($tables as $field => $table)
  @continue(empty($histories[$field]))

  <div class="avoid-break">
    <h3>{{ $table['title'] }}</h3>
    @include('exports.pdf.partials._rows', ['columns' => $table['columns'], 'rows' => $histories[$field], 'currency' => $currency])
  </div>
@endforeach

@if(!empty($histories['sheet.history.timeline']))
  <div class="avoid-break">
    <h3>{{ __('Unified timeline of the copy') }}</h3>
    @include('exports.pdf.partials._timeline', ['entries' => $histories['sheet.history.timeline'], 'currency' => $currency])
  </div>
@endif
