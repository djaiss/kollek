{{-- The public FAQ, rendered server side and filtered in the browser by Alpine. --}}

<x-marketing-layout :title="__('Frequently asked questions')">
  <div x-data="faq()">
    <section id="top" class="mx-auto max-w-[1200px] scroll-mt-24 px-5 pt-16 sm:px-8 sm:pt-24">
      <div class="max-w-[720px]">
        <p class="mb-6 font-mono text-xs font-medium tracking-[1.4px] text-muted-soft uppercase">{{ __('Frequently asked questions') }}</p>

        <h1 class="text-[32px] leading-[1.05] font-semibold tracking-[-1px] text-balance text-ink sm:text-5xl sm:tracking-[-1.5px] lg:text-[64px] lg:leading-[1.02] lg:tracking-[-2.4px]">
          {{ __('Everything worth asking before you trust us with a collection.') }}
        </h1>

        <p class="mt-7 max-w-[600px] text-[17px] leading-relaxed text-muted sm:text-[19px]">
          {{ __(':questions answers across :sections topics: ownership, privacy, pricing, self-hosting, and the honest limits of what KolleK does.', ['questions' => $totalQuestions, 'sections' => count($sections)]) }}
        </p>
      </div>

      <div class="mt-11 flex max-w-[760px] flex-col gap-3 sm:flex-row sm:items-center">
        <div class="flex h-14 flex-1 items-center gap-x-3 rounded-xl border border-hairline bg-page px-4.5">
          <x-lucide-search class="h-[17px] w-[17px] shrink-0 text-muted-soft" />
          <label for="faq-search" class="sr-only">{{ __('Search every question') }}</label>
          <input
            id="faq-search"
            type="search"
            x-model="query"
            placeholder="{{ __('Search every question…') }}"
            class="min-w-0 flex-1 border-none bg-transparent text-base text-ink outline-none placeholder:text-muted-soft"
          />
          <button type="button" x-show="isSearching" x-cloak @click="clear()" class="shrink-0 text-[13px] whitespace-nowrap text-muted-soft transition-colors hover:text-ink">{{ __('clear') }}</button>
        </div>

        <button
          type="button"
          @click="toggleAll()"
          :aria-pressed="allOpen"
          class="flex h-14 shrink-0 items-center justify-center gap-x-2.5 rounded-xl border border-hairline px-5 text-sm font-semibold text-ink transition-colors hover:bg-sidebar"
        >
          <span class="text-[17px] font-normal text-muted transition-transform duration-200" :class="allOpen ? 'rotate-45' : ''" aria-hidden="true">+</span>
          <span x-text="allOpen ? @js(__('Collapse all')) : @js(__('Expand all answers'))">{{ __('Expand all answers') }}</span>
        </button>
      </div>

      <p
        x-show="isSearching"
        x-cloak
        x-text="matched() === 0 ? @js(__('no matches')) : @js(__(':matched of :total questions match')).replace(':matched', matched()).replace(':total', @js($totalQuestions))"
        class="mt-3.5 h-4 font-mono text-xs text-muted-soft"
      ></p>
    </section>

    <section class="mx-auto max-w-[1200px] px-5 pt-16 sm:px-8 sm:pt-24">
      <div class="flex flex-wrap items-baseline justify-between gap-x-6 gap-y-2 border-b border-hairline pb-6">
        <h2 class="text-[26px] font-semibold tracking-[-0.8px] text-ink sm:text-3xl">{{ __('The ten-second version') }}</h2>
        <p class="text-sm text-muted-soft">{{ __('The questions almost everyone asks first.') }}</p>
      </div>

      <div class="grid grid-cols-1 gap-px border-b border-hairline bg-hairline sm:grid-cols-2 lg:grid-cols-5">
        @foreach ($quickAnswers as $quick)
          <div class="flex min-h-[172px] flex-col gap-y-3 bg-page px-6 py-7">
            <p class="text-[15px] leading-[1.35] font-semibold text-ink">{{ $quick['question'] }}</p>
            <p class="font-mono text-[13px] font-medium text-ink">{{ $quick['verdict'] }}</p>
            <p class="text-[13px] leading-[1.55] text-muted">{{ $quick['note'] }}</p>
          </div>
        @endforeach
      </div>
    </section>

    <section class="mx-auto max-w-[1200px] px-5 pt-16 sm:px-8 sm:pt-24">
      <div class="grid grid-cols-1 gap-x-24 lg:grid-cols-[212px_1fr]">
        <div class="hidden lg:block">
          <div class="sticky top-24">
            <p class="mb-4 font-mono text-[11px] tracking-[1.2px] text-muted-soft uppercase">{{ __('Contents') }}</p>

            <nav class="flex flex-col gap-y-0.5">
              @foreach ($sections as $section)
                <a href="#{{ $section['id'] }}" class="-ml-2.5 flex items-baseline justify-between gap-x-2.5 rounded-md px-2.5 py-1.5 text-sm font-medium text-body transition-colors hover:bg-sidebar hover:text-ink">
                  <span>{{ $section['title'] }}</span>
                  <span class="font-mono text-[11px] text-muted-soft">{{ count($section['items']) }}</span>
                </a>
              @endforeach
            </nav>

            <div class="my-5 h-px bg-hairline"></div>

            <a href="{{ config('marketing.github_url') }}/discussions" target="_blank" rel="noopener" class="block py-1 text-[13px] text-muted transition-colors hover:text-ink">{{ __('Ask a person') }} &rarr;</a>
          </div>
        </div>

        <div class="min-w-0">
          <div x-show="! hasResults" x-cloak class="border-t border-hairline py-16">
            <p class="text-[22px] font-semibold tracking-[-0.5px] text-ink">{{ __('Nothing matches that search.') }}</p>
            <p class="mt-3 mb-6 max-w-[440px] text-[15px] leading-relaxed text-muted">
              {{ __('Try a shorter word, such as “export”, “backup” or “API”. If the answer is genuinely missing, ask and we will add it here.') }}
            </p>
            <a href="{{ config('marketing.github_url') }}/discussions" target="_blank" rel="noopener" class="inline-flex h-11 items-center rounded-md bg-primary px-5 text-sm font-semibold text-on-primary transition-opacity hover:opacity-90">{{ __('Ask a question') }}</a>
          </div>

          @foreach ($sections as $index => $section)
            <div id="{{ $section['id'] }}" data-faq-section x-show="sectionMatches($el)" class="scroll-mt-24 pb-16 sm:pb-22">
              <div class="flex flex-wrap items-baseline gap-x-4 gap-y-1 border-b border-ink pb-5">
                <span class="font-mono text-[13px] text-muted-soft">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                <h2 class="flex-1 text-[26px] font-semibold tracking-[-0.9px] text-ink sm:text-[32px] sm:tracking-[-1.1px]">
                  <a href="#{{ $section['id'] }}" class="inline-flex items-baseline gap-x-2.5 transition-colors hover:text-body">
                    {{ $section['title'] }}
                    <span class="font-mono text-lg font-normal text-hairline" aria-hidden="true">#</span>
                  </a>
                </h2>
                <span x-show="! isSearching" class="font-mono text-xs text-muted-soft">{{ __(':count questions', ['count' => count($section['items'])]) }}</span>
              </div>

              <p class="mt-5 max-w-[560px] text-base leading-relaxed text-muted">{{ $section['blurb'] }}</p>

              <div class="mt-4 flex max-w-[760px] flex-col">
                @foreach ($section['items'] as $itemIndex => $item)
                  @php $key = $section['id'].'-'.$itemIndex; @endphp

                  <div data-faq-question x-show="matches($el)" class="border-b border-hairline">
                    <button
                      type="button"
                      @click="toggle('{{ $key }}')"
                      :aria-expanded="isOpen('{{ $key }}')"
                      aria-controls="{{ $key }}"
                      class="flex w-full items-start justify-between gap-x-6 py-5 text-left transition-colors"
                      :class="isOpen('{{ $key }}') ? 'text-ink' : 'text-body hover:text-ink'"
                    >
                      <span class="text-[17px] leading-[1.4] tracking-[-0.2px]" :class="isOpen('{{ $key }}') ? 'font-semibold' : 'font-medium'">{{ $item['question'] }}</span>
                      <span class="mt-0.5 flex h-5.5 w-5.5 shrink-0 items-center justify-center text-[19px] font-normal text-muted transition-transform duration-200" :class="isOpen('{{ $key }}') ? 'rotate-45' : ''" aria-hidden="true">+</span>
                    </button>

                    <div id="{{ $key }}" x-show="isOpen('{{ $key }}')" x-cloak>
                      <p class="max-w-[680px] pr-6 pb-6 text-base leading-[1.7] text-muted sm:pr-15">{{ $item['answer'] }}</p>
                    </div>
                  </div>
                @endforeach
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </section>

    <section class="mx-auto max-w-[1200px] px-5 sm:px-8">
      <div class="rounded-xl bg-card px-6 py-14 text-center sm:px-12 sm:py-20">
        <h2 class="mx-auto max-w-[560px] text-[28px] leading-[1.12] font-semibold tracking-[-1px] text-balance text-ink sm:text-[40px] sm:tracking-[-1.4px]">
          {{ __('Still holding a question we did not answer?') }}
        </h2>
        <p class="mx-auto mt-5 max-w-[460px] text-[17px] leading-relaxed text-muted">
          {{ __('Ask it in the open and the answer helps the next person too. No ticket queue, no chatbot, no sales follow-up.') }}
        </p>

        <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
          <a href="{{ config('marketing.github_url') }}/discussions" target="_blank" rel="noopener" class="flex h-12 items-center justify-center rounded-md bg-primary px-6 text-[15px] font-semibold text-on-primary transition-opacity hover:opacity-90">{{ __('Ask a question') }}</a>
          <a href="{{ route('marketing.docs.portal.home.show') }}" data-turbo="true" class="flex h-12 items-center justify-center rounded-md border border-hairline bg-canvas px-5.5 text-[15px] font-semibold text-ink transition-colors hover:bg-sidebar">{{ __('Read the documentation') }}</a>
        </div>
      </div>
    </section>
  </div>
</x-marketing-layout>
