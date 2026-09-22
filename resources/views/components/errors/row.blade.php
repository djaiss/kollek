@props([
  'label',
  'value',
  'mono' => false,
])

<div class="flex items-center justify-between gap-4 border-b border-hairline-soft px-[18px] py-3.5">
  <span class="shrink-0 text-[13px] text-muted-soft">{{ $label }}</span>
  <span @class([
    'truncate text-right text-[13px] font-semibold',
    'font-mono' => $mono,
  ])>{{ $value }}</span>
</div>
