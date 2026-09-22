{{-- The terms of use and the privacy policy, rendered from a Markdown file. --}}

<x-marketing-layout :title="$title">
  <div class="mx-auto max-w-[760px] px-5 py-16 sm:px-8">
    @if (app()->getLocale() !== config('docs.default_locale'))
      <div class="mb-10 flex items-start gap-3 rounded-lg border border-hairline bg-canvas px-4 py-3">
        @svg('lucide-languages', 'mt-0.5 size-4 shrink-0 text-muted')
        <p class="text-sm text-muted">{{ __('This page is only available in English.') }}</p>
      </div>
    @endif

    <div class="prose prose-gray max-w-none dark:prose-invert prose-headings:font-semibold prose-headings:tracking-tight prose-h1:mb-3 prose-h1:text-3xl prose-h2:mt-10 prose-h2:text-xl prose-a:font-normal prose-a:text-ink hover:prose-a:underline">
      {!! $content !!}
    </div>
  </div>
</x-marketing-layout>
