{{-- A question mark badge revealing a short help note next to a label or a field. --}}
@props([
  'id',
  'align' => 'left',
])

@inject('helpSnippets', \App\Services\HelpSnippets::class)

@php
  $snippet = $helpSnippets->find($id, app()->getLocale());
@endphp

@if($snippet)
  @php
    $paragraphs = preg_split('/\n\s*\n/', trim($snippet['body']));
    $isRight = $align === 'right';
    $accentSoft = 'color-mix(in oklab, var(--color-accent), transparent 90%)';
    $accentBorder = 'color-mix(in oklab, var(--color-accent), transparent 72%)';
    $noteSoft = 'color-mix(in oklab, var(--color-accent), transparent 94%)';
  @endphp

  <span
    data-help="1"
    x-data="{
      hovering: false,
      pinned: false,
      timer: null,
      canHover: false,
      init() { this.canHover = window.matchMedia('(hover: hover) and (pointer: fine)').matches },
      get open() { return this.hovering || this.pinned },
      enter() { if (! this.canHover) return; clearTimeout(this.timer); this.hovering = true },
      leave() { if (! this.canHover) return; clearTimeout(this.timer); this.timer = setTimeout(() => { this.hovering = false }, 130) },
      toggle() { this.pinned = ! this.pinned; this.hovering = false },
      close() { this.pinned = false; this.hovering = false },
    }"
    @mouseenter="enter()"
    @mouseleave="leave()"
    @mousedown.outside="close()"
    @keydown.escape.window="close()"
    class="relative inline-flex leading-none align-middle"
  >
    <button
      type="button"
      @click="toggle()"
      :aria-expanded="open ? 'true' : 'false'"
      aria-haspopup="dialog"
      aria-label="{{ __('Help') }}"
      :style="open ? 'border-color:var(--color-accent); color:var(--color-accent); background:{{ $accentSoft }};' : null"
      class="flex size-[17px] items-center justify-center rounded-full border border-hairline bg-transparent text-[11px] leading-none font-bold text-muted-soft transition-colors duration-150 focus-visible:ring-2 focus-visible:ring-[var(--color-accent)]/40 focus-visible:outline-none"
    >?</button>

    <div
      x-cloak
      data-morph-skip
      x-show="open"
      x-transition
      role="dialog"
      aria-label="{{ $snippet['title'] }}"
      class="absolute top-full z-[70] w-[330px] max-w-[calc(100vw-2rem)] pt-[11px] text-left {{ $isRight ? 'right-[-7px]' : 'left-[-7px]' }}"
    >
      <div class="absolute top-[5px] size-3 rotate-45 border-t border-l border-hairline bg-canvas {{ $isRight ? 'right-4' : 'left-4' }}"></div>

      <div class="relative overflow-hidden rounded-[13px] border border-hairline bg-canvas shadow-xl">
        <div class="flex items-center gap-[11px] px-4 pt-[15px] pb-3">
          <span class="flex size-7 shrink-0 items-center justify-center rounded-lg border text-[13px] font-bold text-[var(--color-accent)]" style="background:{{ $accentSoft }}; border-color:{{ $accentBorder }};">?</span>

          <div class="flex min-w-0 flex-1 flex-col gap-[3px]">
            @if($snippet['kicker'])
              <span class="text-[10.5px] leading-none font-semibold tracking-[0.5px] text-muted-soft uppercase">{{ $snippet['kicker'] }}</span>
            @endif
            <span class="text-[15px] leading-tight font-semibold tracking-[-0.2px] text-ink">{{ $snippet['title'] }}</span>
          </div>

          <button
            type="button"
            x-show="pinned"
            @click="close()"
            aria-label="{{ __('Close') }}"
            class="flex size-6 shrink-0 items-center justify-center rounded-md text-muted-soft transition-colors hover:text-ink"
          >
            @svg('lucide-x', 'size-3.5')
          </button>
        </div>

        <div class="px-4 pb-1">
          @foreach($paragraphs as $paragraph)
            <p class="mb-[11px] text-[13.5px] leading-relaxed text-muted">{{ $paragraph }}</p>
          @endforeach

          @if($snippet['note'])
            <div class="mt-0.5 mb-3.5 flex gap-2.5 rounded-[9px] border-y border-r border-hairline border-l-[3px] border-l-[var(--color-accent)] px-3 py-[11px]" style="background:{{ $noteSoft }};">
              @svg('lucide-info', 'mt-px size-4 shrink-0 text-[var(--color-accent)]')
              <div>
                @if($snippet['note']['title'])
                  <div class="mb-1 text-[12.5px] font-semibold text-ink">{{ $snippet['note']['title'] }}</div>
                @endif
                <div class="text-[12.5px] leading-normal text-muted">{{ $snippet['note']['text'] }}</div>
              </div>
            </div>
          @endif
        </div>

        @if($snippet['url'])
          <div class="flex items-center justify-between gap-3 border-t border-hairline-soft bg-[var(--color-hairline-soft)] px-4 py-3">
            <span class="text-[11.5px] text-muted-soft">{{ __('From the documentation') }}</span>
            <a href="{{ $snippet['url'] }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 text-[13px] font-semibold text-[var(--color-accent)]">
              {{ __('Read more') }}
              @svg('lucide-external-link', 'size-3.5')
            </a>
          </div>
        @endif
      </div>
    </div>
  </span>
@endif
