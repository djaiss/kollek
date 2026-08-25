@props (['columns'])

{{-- The desktop "Features" mega menu. It renders inside the header's Features
     wrapper, which owns the hover open/close via mouseenter/mouseleave. Because
     this panel is a DOM descendant of that wrapper, moving the pointer from the
     trigger down into the card never triggers the wrapper's mouseleave, so the
     menu stays open; leaving the card in any direction does.

     Only the card itself is a pointer target. The full-width positioning layer
     and the dimmed backdrop are pointer-events-none, so the empty space around
     the card resolves to the page underneath (outside the wrapper) and closes
     the menu. --}}

{{-- Visibility is driven by bound opacity/translate classes rather than x-show,
     and interactivity by `inert`. We deliberately do not toggle `display`: an
     x-show/x-transition leave that is interrupted (a quick sweep over the nav)
     can strand the panel visible, since its resting display is block. Animating
     opacity on an always-block element closes reliably every time. --}}

{{-- Both halves carry the closed state as a static opacity-0, and Alpine only
     paints over it once it is open. An x-cloak would look equivalent and is not:
     Alpine drops x-cloak the moment it boots, and undoes the inline styles it
     added when the element leaves the document, so the copy Turbo caches on the
     way out of a page is the menu stripped of everything keeping it hidden.
     Coming back to that cached page paints the whole menu at full opacity for a
     frame, and the transitions below then animate it away. A class Alpine never
     wrote is a class it cannot take back. --}}

{{-- Dim the rest of the page behind the panel (visual only). Opacity is bound
     inline rather than through utility classes so the animation never depends on
     Tailwind emitting a class it only sees inside an Alpine expression. --}}
<div :style="featuresOpen ? 'opacity:1' : 'opacity:0'" class="pointer-events-none fixed inset-0 top-16 z-40 hidden bg-ink/25 opacity-0 backdrop-blur-[1px] transition-opacity duration-200 lg:block" aria-hidden="true"></div>

<div :inert="!featuresOpen" :style="featuresOpen ? 'opacity:1; transform:translateY(0); transition:opacity .2s ease-out, transform .2s ease-out' : 'opacity:0; transform:translateY(-8px); transition:opacity .15s ease-in, transform .15s ease-in'" class="pointer-events-none absolute inset-x-0 top-full z-50 hidden opacity-0 lg:block">
  {{-- The card plus the small bridge gap above it: the only pointer target. --}}
  <div class="pointer-events-auto relative mx-auto max-w-[1080px] px-5 pt-3.5">
    {{-- Caret pointing up toward the Features trigger. --}}
    <div class="absolute top-[7px] left-[26%] h-3.5 w-3.5 rotate-45 border-t border-l border-hairline bg-page"></div>

    <div class="overflow-hidden rounded-[18px] border border-hairline bg-page shadow-[0_32px_80px_rgba(17,17,17,0.18),0_8px_24px_rgba(17,17,17,0.08)]">
      <div class="grid grid-cols-1 lg:grid-cols-[1fr_300px]">
        {{-- Left: the feature grid. --}}
        <div class="p-9">
          <div class="mb-6 flex items-baseline justify-between">
            <div>
              <div class="text-xs font-semibold tracking-[0.7px] text-muted-soft uppercase">{{ __('Everything :name does', ['name' => config('app.name')]) }}</div>
              <div class="mt-1.5 text-[22px] font-semibold tracking-[-0.5px] text-ink">{{ __('Features') }}</div>
            </div>
            <a href="{{ route('marketing.features.index') }}" data-turbo="true" class="inline-flex items-center gap-1.5 text-[13px] font-semibold text-body transition-colors hover:text-ink">
              {{ __('All features') }}
              <x-lucide-arrow-right class="h-3.5 w-3.5" />
            </a>
          </div>

          <div class="grid grid-cols-3 gap-x-[30px] gap-y-8">
            @foreach ($columns as $column)
              <div class="flex min-w-0 flex-col gap-1">
                <div class="mb-1 flex items-center gap-2 border-b border-hairline-soft pb-3">
                  <span class="h-1.5 w-1.5 rounded-full" style="background:{{ $column['dot'] }};"></span>
                  <span class="text-[11px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ $column['label'] }}</span>
                </div>

                @foreach ($column['items'] as $item)
                  <a href="{{ route('marketing.features.show', $item['slug']) }}" data-turbo="true" class="-mx-2.5 flex items-start gap-3 rounded-[10px] p-2.5 transition-colors hover:bg-sidebar">
                    <span class="flex h-[30px] w-[30px] shrink-0 items-center justify-center rounded-lg bg-card">
                      <span class="h-3 w-3" style="border-radius:{{ $item['iconRadius'] }}; background:{{ $item['dot'] }};"></span>
                    </span>
                    <span class="min-w-0">
                      <span class="flex items-center gap-[7px]">
                        <span class="text-sm font-semibold tracking-[-0.1px] text-ink">{{ $item['title'] }}</span>
                        @if ($item['isNew'])
                          <span class="rounded-full bg-[#e7f6ee] px-1.5 py-0.5 text-[9px] font-bold tracking-[0.4px] text-[#0f7a4d]">{{ __('NEW') }}</span>
                        @endif
                      </span>
                      <span class="mt-0.5 block text-[12.5px] leading-[1.45] text-pretty text-muted">{{ $item['desc'] }}</span>
                    </span>
                  </a>
                @endforeach
              </div>
            @endforeach
          </div>
        </div>

        {{-- Right: the promo rail. --}}
        <div class="flex flex-col gap-3.5 border-l border-hairline-soft bg-sidebar p-8">
          <div class="text-[11px] font-semibold tracking-[0.7px] text-muted-soft uppercase">{{ __('Get started') }}</div>

          <a href="{{ route('marketing.features.show', 'self-hosting') }}" data-turbo="true" class="block rounded-[14px] bg-[#101010] p-[22px] text-white transition-colors hover:bg-black">
            <span class="mb-4 flex h-[34px] w-[34px] items-center justify-center rounded-[9px] bg-[#1a1a1a]">
              <x-lucide-monitor-check class="h-[17px] w-[17px] text-badge-emerald" />
            </span>
            <span class="block text-[15px] font-semibold tracking-[-0.2px]">{{ __('Self-host in one command') }}</span>
            <span class="mt-1.5 block text-[12.5px] leading-[1.5] text-[#a1a1aa]">{{ __('Spin up a full instance with Docker. Free forever, MIT licensed.') }}</span>
            <span class="mt-3.5 block rounded-[7px] bg-[#1a1a1a] px-[11px] py-[9px] font-mono text-[11px] text-[#e5e7eb]">
              <span class="text-badge-emerald">$</span>
              docker run kollek/app
            </span>
          </a>

          <a href="{{ route('marketing.docs.portal.home.show') }}" data-turbo="true" class="flex items-center gap-3 rounded-xl border border-hairline bg-page p-4 transition-colors hover:bg-card">
            <span class="flex h-[34px] w-[34px] shrink-0 items-center justify-center rounded-[9px] bg-card">
              <x-lucide-file-text class="h-4 w-4 text-ink" />
            </span>
            <span class="min-w-0">
              <span class="block text-[13.5px] font-semibold text-ink">{{ __('Read the docs') }}</span>
              <span class="mt-0.5 block text-xs text-muted">{{ __('Guides, schema, and the JSON API.') }}</span>
            </span>
          </a>

          <div class="flex-1"></div>
          <div class="flex items-center gap-2 border-t border-hairline-soft pt-1.5 text-xs text-muted-soft">
            <span class="text-badge-emerald">&bull;</span>
            {{ __(':count feature areas · MIT licensed', ['count' => 12]) }}
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
