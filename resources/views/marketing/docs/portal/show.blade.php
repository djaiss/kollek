@php
    $sectionTitle = collect($navigation)
        ->first(fn (array $section): bool => collect($section['items'])->contains('id', $page['id']))['title'] ?? null;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $locale) }}">
  <head>
    @php
        // The documentation supplies its own title and description: the title comes from the
        // page frontmatter and the description from its opening lines, neither of which the
        // route name could tell you.
        // The portal home already says it is the documentation in its own title.
        $seoTitle = $page['is_home'] ? $page['title'] : $page['title'].' — '.__('documentation');
        $seo = app(\App\ViewModels\MarketingSeo::class)->forRequest(request(), $seoTitle, $excerpt);

        // The page has already been read off disk to render it, so the graph is handed what
        // it needs rather than resolving the same page a second time.
        $structuredData = app(\App\ViewModels\MarketingStructuredData::class)->forDocumentationPage(request(), $page, $excerpt);
    @endphp

    @include('partials.meta', ['title' => $seo['title'], 'description' => $seo['description']])
    @include('partials.marketingMeta', ['seo' => $seo, 'structuredData' => $structuredData])

    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/css/marketing.css', 'resources/js/marketing.js'])

    <style>
      html { scroll-behavior: smooth; }
      [x-cloak] { display: none !important; }
      .doc-scroll::-webkit-scrollbar { width: 8px; }
      .doc-scroll::-webkit-scrollbar-thumb { background: var(--hairline); border-radius: 8px; }
    </style>
  </head>
  <body class="font-sans antialiased">
    <div class="min-h-screen bg-page text-ink">
      @include('components.marketing.header')

      <x-docs.portal-subheader :locale="$locale" :urlLocale="$urlLocale" :languageUrls="$languageUrls" />

      <div class="mx-auto flex max-w-[1440px]">
        <x-docs.portal-sidebar :navigation="$navigation" :locale="$locale" :currentId="$page['id']" />

        <main id="main-content" tabindex="-1" class="min-w-0 flex-1 scroll-mt-20 px-6 py-10 focus:outline-none sm:px-10 lg:px-16 lg:pb-24">
          <div class="mx-auto max-w-[720px]">
            <nav class="mb-5 flex items-center gap-2.5 text-sm text-muted">
              <a href="{{ route('marketing.docs.portal.home.show', ['locale' => $urlLocale]) }}" data-turbo="true" class="hover:text-ink">{{ __('Home') }}</a>
              @if ($sectionTitle)
                <span class="text-muted-soft">/</span>
                <span>{{ $sectionTitle }}</span>
              @endif
            </nav>

            <h1 class="mb-4 text-3xl font-bold tracking-tight text-ink">{{ $page['title'] }}</h1>

            <div
              x-data="{
                  copied: false,
                  copyForLlm() {
                      docsCopy(@js($markdown));
                      this.copied = true;
                      setTimeout(() => (this.copied = false), 1500);
                  },
              }"
              class="mb-3.5 flex flex-wrap items-center gap-0 text-sm font-medium text-muted"
            >
              <button type="button" @click="copyForLlm()" class="flex items-center gap-2 py-1 pr-4.5 hover:text-brand cursor-pointer">
                <x-lucide-clipboard class="h-[15px] w-[15px]" />
                <span x-text="copied ? '{{ __('Copied') }}' : '{{ __('Copy for LLM') }}'"></span>
              </button>
              <span class="h-4 w-px bg-hairline"></span>
              <a href="{{ $markdownUrl }}" target="_blank" class="flex items-center gap-2 py-1 px-4.5 hover:text-brand cursor-pointer">
                <x-lucide-file-text class="h-[15px] w-[15px]" />
                {{ __('View as Markdown') }}
              </a>
            </div>

            <div class="mb-7 h-px bg-hairline-soft"></div>

            <div class="doc-content prose prose-gray max-w-none dark:prose-invert prose-headings:font-semibold prose-headings:tracking-tight prose-a:font-normal prose-a:text-ink hover:prose-a:underline prose-code:rounded prose-code:bg-hairline-soft prose-code:px-1.5 prose-code:py-0.5 prose-code:font-normal prose-code:before:content-none prose-code:after:content-none prose-pre:rounded-xl prose-pre:border prose-pre:border-hairline prose-pre:bg-sidebar prose-pre:text-body prose-img:rounded-xl prose-img:border prose-img:border-hairline">
              {!! $content !!}
            </div>

            <div class="mt-11 flex flex-wrap items-center justify-between gap-3 border-t border-hairline-soft pt-6">
              <span class="text-[13px] text-muted-soft">{{ __('Documentation for :name', ['name' => config('app.name')]) }}</span>
              <a href="{{ config('marketing.github_url') }}" target="_blank" rel="noopener" class="flex items-center gap-2 text-[13px] font-medium text-muted hover:text-ink">
                <x-lucide-github class="h-[15px] w-[15px]" />
                {{ __('Edit this page on GitHub') }}
              </a>
            </div>

            <div class="mt-6 rounded-xl border border-hairline p-5">
              <div class="flex flex-wrap items-center gap-3.5">
                <span class="text-[15px] font-semibold text-ink">{{ __('Was this page useful?') }}</span>
                <div class="flex gap-2">
                  <span class="flex h-9 w-9 items-center justify-center rounded-lg border border-hairline text-muted-soft"><x-lucide-frown class="h-5 w-5" /></span>
                  <span class="flex h-9 w-9 items-center justify-center rounded-lg border border-hairline text-muted-soft"><x-lucide-meh class="h-5 w-5" /></span>
                  <span class="flex h-9 w-9 items-center justify-center rounded-lg border border-hairline text-muted-soft"><x-lucide-smile class="h-5 w-5" /></span>
                </div>
              </div>
            </div>
          </div>
        </main>

        <x-docs.portal-toc :toc="$toc" />
      </div>

      @include('components.marketing.footer')
    </div>

    <script>
      // Highlight the table of contents entry for the heading currently in view.
      // Registered on turbo:load so it re-arms after every Turbo navigation.
      document.addEventListener('turbo:load', () => {
        const links = Array.from(document.querySelectorAll('#doc-toc [data-toc]'));

        if (links.length === 0) {
          return;
        }

        const activeClasses = ['border-brand', 'font-semibold', 'text-ink'];
        const headings = links
          .map((link) => document.getElementById(link.dataset.toc))
          .filter(Boolean);

        const setActive = (id) => {
          links.forEach((link) => {
            link.classList.toggle('border-brand', link.dataset.toc === id);
            link.classList.toggle('font-semibold', link.dataset.toc === id);
            link.classList.toggle('text-ink', link.dataset.toc === id);
          });
        };

        const observer = new IntersectionObserver(
          (entries) => {
            const visible = entries.filter((entry) => entry.isIntersecting);

            if (visible.length > 0) {
              setActive(visible[0].target.id);
            }
          },
          { rootMargin: '-140px 0px -70% 0px' },
        );

        headings.forEach((heading) => observer.observe(heading));
      });
    </script>
  </body>
</html>
