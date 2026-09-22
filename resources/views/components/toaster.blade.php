@php
  $toasts = collect([
      [
          'title' => Session::get('status'),
          'description' => Session::get('status_description'),
          'icon' => 'lucide-check',
          'tint' => 'bg-success/10 text-success',
      ],
      [
          'title' => Session::get('error'),
          'description' => Session::get('error_description'),
          'icon' => 'lucide-circle-alert',
          'tint' => 'bg-error/10 text-error',
      ],
  ])->filter(fn (array $toast): bool => filled($toast['title']));
@endphp

<div class="pointer-events-none fixed bottom-0 z-50 flex w-full flex-col items-end p-4 sm:p-6 rtl:items-start" role="status" aria-live="polite">
  <div x-sync x-merge="replace" id="notifications" class="pointer-events-auto relative flex w-full max-w-sm flex-col items-end gap-3">
    @foreach ($toasts as $toast)
      <div x-data="{ show: true }" x-transition.duration.300ms x-show="show" x-init="setTimeout(() => (show = false), 4000)" x-transition:enter-start="translate-y-12 opacity-0" x-transition:enter-end="translate-y-0 opacity-100" x-transition:leave-end="scale-90 opacity-0" class="flex w-full transform items-start gap-3 rounded-xl border border-hairline bg-canvas p-4 shadow-lg transition duration-300 ease-in-out">
        <div class="flex size-8 shrink-0 items-center justify-center rounded-full {{ $toast['tint'] }}">
          @svg($toast['icon'], 'size-4')
        </div>

        <div class="min-w-0 flex-1">
          <p class="text-sm font-semibold text-ink">{{ $toast['title'] }}</p>

          @if ($toast['description'])
            <p class="mt-0.5 text-[13px] leading-snug text-muted">{{ $toast['description'] }}</p>
          @endif
        </div>

        <button type="button" x-on:click="show = false" aria-label="{{ __('Dismiss') }}" class="flex size-6 shrink-0 items-center justify-center rounded-full text-muted-soft transition-colors hover:text-ink">
          <x-lucide-x class="size-3.5" />
        </button>
      </div>
    @endforeach
  </div>
</div>
