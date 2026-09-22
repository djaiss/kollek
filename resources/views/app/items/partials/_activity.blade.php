{{-- The trail of everything done to this item, newest first. --}}
<div class="max-w-2xl">
  @forelse ($activity as $entry)
    <div class="flex gap-3.5">
      <div class="flex shrink-0 flex-col items-center">
        <x-avatar :user="$entry->user" :name="$entry->getUserName()" :size="32" class="z-10 size-[30px] text-[11px]" />

        @unless ($loop->last)
          <div class="w-0.5 flex-1 bg-hairline"></div>
        @endunless
      </div>

      <div class="min-w-0 flex-1 pt-1 pb-5.5">
        <p class="flex flex-wrap items-center gap-2 text-sm leading-normal">
          <span class="font-semibold text-ink">{{ $entry->getUserName() }}</span>
          <span class="text-muted">{{ $entry->getTranslatedDescription() }}</span>

          @foreach ($entry->getChips() as $chip)
            @if ($chip['style'] === 'mono')
              <span class="rounded-md bg-card px-2 py-0.5 font-mono text-xs font-medium text-body">{{ $chip['label'] }}</span>
            @elseif ($chip['style'] === 'file')
              <span class="inline-flex items-center gap-1.5 font-semibold text-ink">
                @svg('lucide-file', 'size-3.5 shrink-0 text-muted-soft')
                {{ $chip['label'] }}
              </span>
            @else
              <span class="font-semibold text-ink">{{ $chip['label'] }}</span>
            @endif
          @endforeach

          <span class="text-muted-soft">·</span>
          <span class="text-[13px] text-muted-soft">{{ $entry->created_at->diffForHumans() }}</span>
        </p>
      </div>
    </div>
  @empty
    <x-empty-state>
      <x-slot:icon>
        @svg('lucide-history', 'size-5 text-muted-soft')
      </x-slot>
      {{ __('No activity yet.') }}
    </x-empty-state>
  @endforelse
</div>
