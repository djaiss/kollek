@props([
  'code',
  'name',
  'accent',
  'accentSoft',
  'badge',
  'headline',
  'body',
  'primaryLabel',
  'primaryHref',
  'secondaryLabel' => null,
  'secondaryHref' => null,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    @include('partials.meta', ['title' => $code.' · '.$name])

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
      @keyframes floatY {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-8px); }
      }

      @media (prefers-reduced-motion: no-preference) {
        .animate-float { animation: floatY 5s ease-in-out infinite; }
      }
    </style>
  </head>
  <body class="min-h-screen bg-page font-sans text-ink antialiased" style="--accent: {{ $accent }}; --accent-soft: {{ $accentSoft }};">
    <div class="flex min-h-screen flex-col">
      <header class="flex items-center justify-between gap-4 border-b border-hairline px-8 py-4">
        <a href="{{ $primaryHref }}" class="flex items-center gap-2" data-turbo="true">
          <x-wordmark height="16" class="text-ink" />
        </a>

        <x-theme-toggle class="border border-hairline bg-canvas text-muted hover:text-ink" />
      </header>

      <main class="flex flex-1 items-center justify-center p-12">
        <div class="flex w-full max-w-[560px] flex-col items-center text-center">
          <div class="animate-float relative mb-9">
            <div
              class="flex size-[180px] items-center justify-center overflow-hidden rounded-[28px]"
              style="background: repeating-linear-gradient(135deg, var(--accent) 0 14px, var(--accent-soft) 14px 28px);"
            >
              <span class="text-[64px] font-bold tracking-[-2px] text-white [text-shadow:0_4px_16px_rgba(0,0,0,0.22)]">{{ $code }}</span>
            </div>

            <div class="absolute -right-2.5 -bottom-2.5 rounded-xl border border-hairline bg-canvas px-3 py-2 font-mono text-[11px] font-medium text-[var(--accent)] shadow-[0_8px_24px_rgba(0,0,0,0.10)]">
              {{ $badge }}
            </div>
          </div>

          <div class="mb-[18px] inline-flex items-center gap-[7px] rounded-full bg-[color-mix(in_oklab,var(--accent),transparent_88%)] px-[13px] py-[5px] text-xs font-semibold text-[var(--accent)]">
            <span class="size-[7px] rounded-full bg-[var(--accent)]"></span>
            {{ __('Error :code · :name', ['code' => $code, 'name' => $name]) }}
          </div>

          <h1 class="mb-3.5 max-w-[460px] text-[34px] leading-[1.12] font-bold tracking-[-0.8px] text-pretty">{{ $headline }}</h1>

          <p class="mb-7 max-w-[440px] text-base leading-relaxed text-body text-pretty">{{ $body }}</p>

          @isset($context)
            <div class="mb-7 w-full max-w-[440px] overflow-hidden rounded-xl border border-hairline bg-canvas [&>*:last-child]:border-0">
              {{ $context }}
            </div>
          @endisset

          <div class="flex flex-wrap items-center justify-center gap-3">
            <x-button :href="$primaryHref" turbo>{{ $primaryLabel }}</x-button>

            @if($secondaryLabel && $secondaryHref)
              <x-button.secondary :href="$secondaryHref" turbo>{{ $secondaryLabel }}</x-button.secondary>
            @endif
          </div>
        </div>
      </main>
    </div>
  </body>
</html>
