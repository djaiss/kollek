@php
  // A per entry social card when one has been made, and the site wide card
  // otherwise. Scrapers fetch it with no session, so it is served from the
  // entry's own public URL rather than from a path on the storage disk.
  $card = $translation->og_image_path === null
      ? null
      : route('marketing.blog.ogImage.show', $translation->slug);

  $graph = app(\App\ViewModels\MarketingStructuredData::class)->forBlogPost(request(), $post, $translation);
@endphp

<x-marketing-layout :title="$translation->metaTitle()" :description="$translation->metaDescription()" :image="$card" :structured-data="$graph">
  {{-- How far down the entry the reader is. Decorative, so it is hidden from
       assistive technology rather than announced on every scroll. --}}
  <div
    x-data="{ progress: 0 }"
    x-init="
      progress = 0;
      const update = () => {
        const max = document.documentElement.scrollHeight - document.documentElement.clientHeight;
        progress = max > 0 ? Math.min(100, (document.documentElement.scrollTop / max) * 100) : 0;
      };
      update();
      window.addEventListener('scroll', update, { passive: true });
    "
    aria-hidden="true"
    class="sticky top-16 z-40 h-0.5 w-full bg-transparent">
    <div class="h-full bg-primary transition-[width] duration-75" :style="`width: ${progress}%`"></div>
  </div>

  <article class="mx-auto max-w-[1200px] px-5 pt-12 sm:px-8 sm:pt-16">
    <a href="{{ route('marketing.blog.index') }}" data-turbo="true" class="inline-flex items-center gap-2 text-[13px] font-medium text-muted transition-colors hover:text-ink">
      @svg ('lucide-arrow-left', 'size-3.5')
      {{ __('Back to the catalogue') }}
    </a>

    <div class="mt-8 flex flex-wrap items-center gap-x-3 gap-y-1 text-[12px] text-muted-soft">
      <span class="tracking-[0.6px]">{{ $post->reference() }}</span>
      <span aria-hidden="true">·</span>
      <span>{{ $post->shelf->label() }}</span>
      <span aria-hidden="true">·</span>
      <span>{{ $post->published_at->isoFormat('LL') }}</span>
      <span aria-hidden="true">·</span>
      <span>{{ __(':count min read', ['count' => $readingMinutes]) }}</span>
    </div>

    <h1 class="mt-4 max-w-[860px] text-[32px] leading-[1.08] font-semibold tracking-[-1px] text-balance text-ink sm:text-[44px] lg:tracking-[-1.6px]">{{ $translation->title }}</h1>
    <p class="mt-5 max-w-[720px] text-[17px] leading-[1.6] text-pretty text-muted sm:text-[19px]">{{ $translation->excerpt }}</p>

    <div class="mt-7 flex items-center gap-3 border-b border-hairline pb-8">
      <span aria-hidden="true" class="flex size-10 shrink-0 items-center justify-center rounded-full bg-card text-sm font-semibold text-ink">{{ Str::upper(Str::substr($post->author_name, 0, 1)) }}</span>
      <div>
        <p class="text-[14px] font-semibold text-ink">{{ $post->author_name }}</p>
        <p class="text-[12px] text-muted-soft">{{ __('Written for the :name blog', ['name' => config('app.name')]) }}</p>
      </div>
    </div>

    <div class="mt-10 grid gap-14 lg:grid-cols-[minmax(0,1fr)_300px] lg:gap-16">
      {{-- The entry itself --}}
      <div class="prose prose-gray dark:prose-invert prose-headings:font-semibold prose-headings:tracking-tight prose-a:font-normal prose-a:text-ink hover:prose-a:underline prose-code:rounded prose-code:bg-hairline-soft prose-code:px-1.5 prose-code:py-0.5 prose-code:font-normal prose-code:before:content-none prose-code:after:content-none prose-pre:rounded-xl prose-pre:border prose-pre:border-hairline prose-pre:bg-sidebar prose-pre:text-body prose-img:rounded-xl prose-img:border prose-img:border-hairline min-w-0 max-w-none">
        {!! $body !!}

        <nav aria-label="{{ __('Adjacent entries') }}" class="mt-14 grid gap-3 border-t border-hairline pt-8 sm:grid-cols-2">
          @if ($previous)
            <a href="{{ route('marketing.blog.show', $previous['slug']) }}" data-turbo="true" class="rounded-xl border border-hairline bg-canvas p-4 transition-colors hover:bg-sidebar">
              <span class="block text-[11px] tracking-[0.6px] text-muted-soft">&larr; {{ $previous['reference'] }}</span>
              <span class="mt-1 block text-[14px] font-medium text-ink">{{ $previous['title'] }}</span>
            </a>
          @else
            <div class="rounded-xl border border-dashed border-hairline p-4">
              <span class="block text-[11px] tracking-[0.6px] text-muted-soft">&larr;</span>
              <span class="mt-1 block text-[14px] text-muted-soft">{{ __('The start of the catalogue') }}</span>
            </div>
          @endif

          @if ($next)
            <a href="{{ route('marketing.blog.show', $next['slug']) }}" data-turbo="true" class="rounded-xl border border-hairline bg-canvas p-4 transition-colors hover:bg-sidebar sm:text-right">
              <span class="block text-[11px] tracking-[0.6px] text-muted-soft">{{ $next['reference'] }} &rarr;</span>
              <span class="mt-1 block text-[14px] font-medium text-ink">{{ $next['title'] }}</span>
            </a>
          @else
            <div class="rounded-xl border border-dashed border-hairline p-4 sm:text-right">
              <span class="block text-[11px] tracking-[0.6px] text-muted-soft">&rarr;</span>
              <span class="mt-1 block text-[14px] text-muted-soft">{{ __('Not yet catalogued') }}</span>
            </div>
          @endif
        </nav>
      </div>

      {{-- The record. Sticky on a wide screen, in the flow on a narrow one. --}}
      <aside aria-label="{{ __('Entry record') }}" class="flex flex-col gap-8 lg:sticky lg:top-24 lg:self-start">
        @if (count($toc) > 0)
          <nav aria-label="{{ __('Contents') }}">
            <h2 class="mb-3 text-[11px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ __('Contents') }}</h2>
            <ol class="flex list-none flex-col gap-2 p-0">
              @foreach ($toc as $heading)
                <li>
                  <a href="#{{ $heading['id'] }}" class="flex gap-2.5 text-[13px] text-muted transition-colors hover:text-ink {{ $heading['level'] === 3 ? 'pl-4' : '' }}">
                    <span aria-hidden="true" class="text-muted-soft">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                    <span>{{ $heading['text'] }}</span>
                  </a>
                </li>
              @endforeach
            </ol>
          </nav>
        @endif

        <section>
          <h2 class="mb-3 text-[11px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ __('Catalogue record') }}</h2>
          <dl class="flex flex-col gap-2 text-[13px]">
            @php
              $record = [
                ['label' => __('Reference'), 'value' => $post->reference()],
                ['label' => __('Shelf'), 'value' => $post->shelf->label()],
                ['label' => __('Published'), 'value' => $post->published_at->isoFormat('YYYY-MM-DD')],
                ['label' => __('Last revised'), 'value' => $translation->updated_at?->isoFormat('YYYY-MM-DD') ?? '—'],
                ['label' => __('Author'), 'value' => $post->author_name],
                ['label' => __('Language'), 'value' => Str::upper(config('docs.locales.' . $translation->locale . '.code'))],
                ['label' => __('Licence'), 'value' => config('marketing.blog.licence')],
              ];
            @endphp
            @foreach ($record as $row)
              <div class="flex items-baseline justify-between gap-4 border-b border-hairline-soft pb-2">
                <dt class="text-muted-soft">{{ $row['label'] }}</dt>
                <dd class="text-right font-medium text-ink">{{ $row['value'] }}</dd>
              </div>
            @endforeach
          </dl>
        </section>

        <section>
          <h2 class="mb-3 text-[11px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ __('Measurements') }}</h2>
          <dl class="grid grid-cols-2 gap-3">
            @foreach ($measurements as $measurement)
              <div class="rounded-lg border border-hairline bg-canvas px-3 py-2.5">
                <dd class="text-[17px] font-semibold tracking-[-0.4px] text-ink">{{ $measurement['value'] }}</dd>
                <dt class="mt-0.5 text-[11px] text-muted-soft">{{ $measurement['label'] }}</dt>
              </div>
            @endforeach
          </dl>
        </section>

        <section>
          <h2 class="mb-1.5 text-[11px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ __('Shelved against the classics') }}</h2>
          <p class="mb-3.5 text-[12px] text-muted">{{ __('This entry, as a percentage of each book.') }}</p>
          <ul class="flex list-none flex-col gap-3 p-0">
            @foreach ($classics as $book)
              <li>
                <div class="flex items-baseline justify-between gap-3">
                  <span class="text-[12px] text-ink">{{ $book['title'] }}</span>
                  <span class="text-[11px] font-medium text-muted">{{ $book['percentage'] }}%</span>
                </div>
                <div aria-hidden="true" class="mt-1.5 h-1 w-full overflow-hidden rounded-full bg-card">
                  <div class="h-full rounded-full bg-primary" style="width: {{ $book['width'] }}%"></div>
                </div>
                <span class="mt-1 block text-[11px] text-muted-soft">{{ __(':count words', ['count' => number_format($book['words'])]) }}</span>
              </li>
            @endforeach
          </ul>
          <p class="mt-3 text-[11px] text-muted-soft">{{ __('Bars are scaled for legibility, not to 100%. Word counts from published editions.') }}</p>
        </section>

        <section>
          <h2 class="mb-3 text-[11px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ __('Reading pace') }}</h2>
          <ul class="flex list-none flex-col gap-2 p-0 text-[13px]">
            @foreach ($pace as $row)
              <li class="flex items-baseline justify-between gap-4 border-b border-hairline-soft pb-2">
                <span class="text-muted-soft">{{ $row['label'] }}</span>
                <span class="font-medium text-ink">{{ $row['value'] }}</span>
              </li>
            @endforeach
          </ul>
        </section>

        @if ($post->tags->isNotEmpty())
          <section>
            <h2 class="mb-3 text-[11px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ __('Filed under') }}</h2>
            <div class="flex flex-wrap gap-2">
              @foreach ($post->tags as $tag)
                <span class="rounded-md border border-hairline bg-canvas px-2.5 py-1 text-[12px] text-muted">{{ $tag->name }}</span>
              @endforeach
            </div>
          </section>
        @endif
      </aside>
    </div>
  </article>

  @if (count($related) > 0)
    <section aria-labelledby="related-title" class="mx-auto max-w-[1200px] px-5 pt-16 pb-8 sm:px-8 sm:pt-24">
      <h2 id="related-title" class="mb-5 border-b border-hairline pb-4 text-[11px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ __('Nearby on the same shelf') }}</h2>
      <ol class="list-none p-0">
        @foreach ($related as $entry)
          <li class="border-b border-hairline-soft">
            <a href="{{ route('marketing.blog.show', $entry['slug']) }}" data-turbo="true" class="-mx-3 grid grid-cols-[56px_1fr] items-baseline gap-x-6 gap-y-1 rounded-lg px-3 py-4 transition-colors hover:bg-sidebar sm:grid-cols-[104px_1fr_auto]">
              <span class="text-[12px] tracking-[0.6px] text-muted-soft">{{ $entry['reference'] }}</span>
              <span class="text-[15px] font-medium text-ink">{{ $entry['title'] }}</span>
              <span class="col-start-2 text-[11px] text-muted-soft sm:col-start-3">{{ $entry['date'] }}</span>
            </a>
          </li>
        @endforeach
      </ol>
    </section>
  @endif
</x-marketing-layout>
