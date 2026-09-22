{{-- The Timeline tab: upcoming due dates on the left, recent activity on the right. --}}
<div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
  <div class="rounded-xl border border-hairline bg-canvas p-4">
    <h3 class="mb-4 text-sm font-semibold text-ink">{{ __('Upcoming due dates') }}</h3>

    @forelse ($tabData['upcoming'] as $loan)
      @php($overdue = $loan->isEffectivelyOverdue())
      <a href="{{ route('loans.show', ['direction' => $direction->slug(), 'tab' => $tab, 'loan' => $loan->id]) }}" data-turbo="true" class="mb-2 flex items-center gap-3 rounded-lg border-l-2 {{ $overdue ? 'border-error' : 'border-badge-orange' }} bg-card/40 px-3 py-2 transition-colors hover:bg-card">
        <div class="w-12 shrink-0 text-center">
          <div class="text-sm font-semibold {{ $overdue ? 'text-error' : 'text-ink' }}">{{ $loan->due_at->isoFormat('DD') }}</div>
          <div class="text-[10px] text-muted uppercase">{{ $loan->due_at->isoFormat('MMM') }}</div>
        </div>
        <div class="min-w-0 flex-1">
          <div class="truncate text-sm font-medium text-ink">{{ $loan->copy->item->name }}</div>
          <div class="truncate text-[12px] text-muted">{{ $loan->party }}</div>
        </div>
      </a>
    @empty
      <p class="text-[13px] text-muted-soft">{{ __('No open loans with a due date.') }}</p>
    @endforelse
  </div>

  <div class="flex flex-col gap-5">
    <div class="rounded-xl border border-hairline bg-canvas p-4">
      <h3 class="mb-3 text-sm font-semibold text-ink">{{ __('Recently returned') }}</h3>
      @forelse ($tabData['recentlyReturned'] as $loan)
        <div class="flex items-center justify-between gap-3 py-1.5">
          <span class="truncate text-[13px] text-ink">{{ $loan->copy->item->name }}</span>
          <span class="shrink-0 text-[12px] text-muted">{{ $loan->returned_at?->isoFormat('ll') }}</span>
        </div>
      @empty
        <p class="text-[13px] text-muted-soft">{{ __('None yet.') }}</p>
      @endforelse
    </div>

    <div class="rounded-xl border border-hairline bg-canvas p-4">
      <h3 class="mb-3 text-sm font-semibold text-ink">{{ __('Recently loaned') }}</h3>
      @forelse ($tabData['recentlyLoaned'] as $loan)
        <div class="flex items-center justify-between gap-3 py-1.5">
          <span class="truncate text-[13px] text-ink">{{ $loan->copy->item->name }}</span>
          <span class="shrink-0 text-[12px] text-muted">{{ $loan->loaned_at->isoFormat('ll') }}</span>
        </div>
      @empty
        <p class="text-[13px] text-muted-soft">{{ __('Nothing loaned yet.') }}</p>
      @endforelse
    </div>
  </div>
</div>
