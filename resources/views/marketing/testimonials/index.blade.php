<x-marketing-layout>
  <section class="mx-auto max-w-[1200px] px-5 pt-16 sm:px-8 sm:pt-24">
    <div class="mx-auto max-w-[620px] text-center">
      <p class="mb-3.5 text-[13px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ __('Loved by collectors') }}</p>
      <h1 class="text-[32px] leading-[1.1] font-semibold tracking-[-1px] text-ink sm:text-5xl lg:tracking-[-1.5px]">{{ __('Straight from the wall.') }}</h1>
      <p class="mx-auto mt-4.5 max-w-[520px] text-[17px] text-muted">{{ __('Every note collectors have shared about :name.', ['name' => config('app.name')]) }}</p>
    </div>

    @if ($testimonials->isEmpty())
      <div class="mx-auto mt-16 max-w-[420px] rounded-lg border border-dashed border-hairline bg-card px-6 py-12 text-center">
        <p class="text-[15px] text-muted">{{ __('No testimonials yet. Check back soon.') }}</p>
      </div>
    @else
      <div class="mt-14 gap-5 sm:columns-2 lg:columns-3">
        @php
          $tilts = ['-rotate-2', 'rotate-1', '-rotate-1', 'rotate-2', '-rotate-1', 'rotate-1'];
        @endphp
        @foreach ($testimonials as $testimonial)
          <x-marketing.testimonial-note :testimonial="$testimonial" :tilt="$tilts[$loop->index % count($tilts)]" />
        @endforeach
      </div>
    @endif
  </section>

  <section class="mx-auto max-w-[1200px] px-5 pt-16 pb-8 text-center sm:px-8 sm:pt-24">
    <a href="{{ route('dashboard.index') }}" data-turbo="true" class="inline-flex h-12 items-center gap-x-2 rounded-md border border-hairline bg-canvas px-6 text-[15px] font-semibold text-ink transition-colors hover:bg-sidebar">
      {{ __('Share your own') }}
      @svg('lucide-arrow-right', 'size-4')
    </a>
  </section>
</x-marketing-layout>
