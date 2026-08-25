<footer class="mt-24 bg-[#101010] text-[#a1a1aa]">
  <div class="mx-auto max-w-[1200px] px-5 py-16 sm:px-8">
    <div class="grid grid-cols-2 gap-8 border-b border-[#242424] pb-12 sm:grid-cols-3 lg:grid-cols-[1.6fr_1fr_1fr_1fr_1fr_1fr]">
      <div class="col-span-2 sm:col-span-3 lg:col-span-1">
        <div class="mb-4 flex items-center gap-x-2.5">
          <x-logo size="28" hoverColor="#ffffff" aria-hidden="true" />
          <x-wordmark height="17" class="text-white" />
        </div>
        <p class="max-w-60 text-sm leading-relaxed">{{ __('The open source collection manager that belongs to you.') }}</p>

        {{-- The picker keeps the visitor on the page they are reading, so it needs the
             current request rather than just the list of languages. --}}
        @php
            $languages = app(\App\ViewModels\MarketingLanguages::class);
        @endphp

        @if ($languages->isOffered())
          <div class="mt-5">
            <x-marketing.language-picker :links="$languages->links(request())" />
          </div>
        @endif
      </div>

      @php
          $github = config('marketing.github_url');
          $columns = [
              [
                  'title' => __('Product'),
                  'links' => array_values(array_filter([
                      ['label' => __('Features'), 'url' => route('marketing.index') . '#features'],
                      ['label' => __('Pricing'), 'url' => route('marketing.pricing.index')],
                      // Only linked once there is something published to read.
                      \App\Models\Testimonial::query()->published()->exists()
                          ? ['label' => __('Reviews'), 'url' => route('marketing.testimonials.index')]
                          : null,
                  ])),
              ],
              [
                  'title' => __('Support'),
                  'links' => [
                      ['label' => __('Documentation'), 'url' => route('marketing.docs.portal.home.show')],
                      ['label' => __('API reference'), 'url' => route('marketing.docs.api.index')],
                      ['label' => __('FAQ'), 'url' => route('marketing.faq.index')],
                      ['label' => __('Changelog'), 'url' => $github . '/releases'],
                  ],
              ],
              [
                  'title' => __('Company'),
                  'links' => [
                      ['label' => __('About'), 'url' => route('marketing.about.index')],
                      ['label' => __('Blog'), 'url' => route('marketing.blog.index')],
                      ['label' => __('Media kit'), 'url' => route('marketing.mediaKit.index')],
                  ],
              ],
              [
                  'title' => __('Community'),
                  'links' => [
                      ['label' => __('GitHub'), 'url' => $github],
                      ['label' => __('Discussions'), 'url' => $github . '/discussions'],
                      ['label' => __('Issues'), 'url' => $github . '/issues'],
                  ],
              ],
              [
                  'title' => __('Legal'),
                  'links' => [
                      ['label' => __('Privacy'), 'url' => route('marketing.privacy.index')],
                      ['label' => __('Terms'), 'url' => route('marketing.terms.index')],
                      ['label' => __('MIT License'), 'url' => $github . '/blob/main/LICENSE'],
                  ],
              ],
          ];
      @endphp

      @foreach ($columns as $column)
        <div>
          <p class="mb-4 text-[13px] font-semibold text-white">{{ $column['title'] }}</p>
          <div class="flex flex-col gap-y-3">
            @foreach ($column['links'] as $link)
              {{-- Drive the in app links through Turbo; the GitHub and placeholder
                   links point off site (or nowhere) and are left alone. --}}
              <a href="{{ $link['url'] }}" @if (str_starts_with($link['url'], config('app.url'))) data-turbo="true" @endif class="text-sm text-[#a1a1aa] transition-colors hover:text-white">{{ $link['label'] }}</a>
            @endforeach
          </div>
        </div>
      @endforeach
    </div>

    <div class="flex flex-wrap items-center justify-between gap-3 pt-7">
      <p class="text-[13px] text-[#898989]">{{ __('© :year :name. Released under the MIT License.', ['year' => date('Y'), 'name' => config('app.name')]) }}</p>

      <div class="flex items-center gap-x-4">
        <x-theme-switch />

        <p class="flex items-center gap-x-2 text-[13px] text-[#898989]">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 9600 4800" aria-hidden="true">
            <title>{{ __('Flag of Canada') }}</title>
            <path fill="#f00" d="m0 0h2400l99 99h4602l99-99h2400v4800h-2400l-99-99h-4602l-99 99H0z" />
            <path fill="#fff" d="m2400 0h4800v4800h-4800zm2490 4430-45-863a95 95 0 0 1 111-98l859 151-116-320a65 65 0 0 1 20-73l941-762-212-99a65 65 0 0 1-34-79l186-572-542 115a65 65 0 0 1-73-38l-105-247-423 454a65 65 0 0 1-111-57l204-1052-327 189a65 65 0 0 1-91-27l-332-652-332 652a65 65 0 0 1-91 27l-327-189 204 1052a65 65 0 0 1-111 57l-423-454-105 247a65 65 0 0 1-73 38l-542-115 186 572a65 65 0 0 1-34 79l-212 99 941 762a65 65 0 0 1 20 73l-116 320 859-151a95 95 0 0 1 111 98l-45 863z" />
          </svg>
          {{ __('Made by collectors, for collectors. Proudly Canadian.') }}
        </p>
      </div>
    </div>
  </div>
</footer>
