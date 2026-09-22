{{-- A loan's status as a coloured pill. --}}
@props(['status'])

@php
  $statusClasses = match ($status->color()) {
      'emerald' => 'bg-badge-emerald/15 text-badge-emerald',
      'orange' => 'bg-badge-orange/15 text-badge-orange',
      'error' => 'bg-error/15 text-error',
      default => 'bg-card text-muted',
  };
@endphp

<span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium whitespace-nowrap {{ $statusClasses }}">{{ $status->label() }}</span>
