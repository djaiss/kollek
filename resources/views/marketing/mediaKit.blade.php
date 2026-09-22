{{-- The media kit, the boilerplate and assets a journalist copies from. --}}

@php
    $updated = '27 July 2026';
    $github = config('marketing.github_url');
    $pressEmail = config('marketing.press_email');

    $oneLiner = 'KolleK is an open source collection manager that catalogues the individual objects you own, with their photos, values, locations and history, on infrastructure you control.';

    $shortDescription = 'KolleK is an open source collection manager for people who catalogue what they own: books, records, comics, watches, wine, cards, tools. Instead of one flat inventory list, it records every physical copy separately, each with its own photos, condition, valuation, location and history. Custom collection types let a bottle of wine and a first edition novel carry entirely different fields. KolleK is MIT licensed and runs on hardware you control through Docker, where it is free and unlimited; the managed instance holds ten items for free and is then unlocked by a single payment rather than a subscription.';

    $longDescription = 'KolleK is an open source collection manager built for private collectors who care about individual objects rather than titles. Where most inventory software assumes every entry looks alike, KolleK lets collectors define their own collection types and fields, so a watch, a comic and a bottle of wine can each be catalogued in their own vocabulary. Every physical copy keeps its own record: photographs, condition, purchases and sales, a history of valuations, insurance and loan records, provenance events, maintenance, and a trail of where it has been stored. Names, descriptions and notes are encrypted at rest, and search still reaches them through a hashed index rather than storing them in the clear. The application is MIT licensed and runs from a Docker Compose stack on hardware the collector owns; a managed instance holds ten items for free and is unlocked by a single payment rather than a subscription, though checkout is not open yet. KolleK is built in the open by one developer, and its documentation carries a feature status page that lists what does not work yet. The interface, the documentation and the public site are available in seven languages.';

    $founderBio = 'Regis Freyd is the founder and sole developer of KolleK, an open source collection manager. He writes every line of the application, answers every support message himself, and develops the project in public on GitHub under the MIT licence. He is based in Canada.';

    $facts = [
        ['key' => 'Founder', 'value' => 'Regis Freyd', 'note' => 'Sole developer. No employees.'],
        ['key' => 'Based', 'value' => 'Canada', 'note' => 'Remote, no offices.'],
        ['key' => 'Status', 'value' => 'In development', 'note' => 'Just launched. The documentation keeps a public feature status page of what is finished and what is not.'],
        ['key' => 'Licence', 'value' => 'MIT', 'note' => 'The whole application, not an open core split.'],
        ['key' => 'Price', 'value' => 'Free to self-host', 'note' => 'Self-hosting is free and unlimited. A managed account holds ten items for free and is unlocked by one payment of $49, non-refundable. Checkout is not open yet.'],
        ['key' => 'Platform', 'value' => 'Web application', 'note' => 'Any modern browser, laid out down to phone width. There is no native mobile app.'],
        ['key' => 'Self-hosting', 'value' => 'Docker Compose', 'note' => 'Web server, queue worker, scheduler and MySQL, with your data on volumes you own.'],
        ['key' => 'Data', 'value' => 'Encrypted at rest', 'note' => 'Encrypted by the application with your instance key. Not end to end: whoever runs the instance holds it.'],
        ['key' => 'Languages', 'value' => 'Seven', 'note' => 'English, French, Spanish, German, Brazilian Portuguese, Simplified Chinese, Japanese.'],
        ['key' => 'Funding', 'value' => 'Bootstrapped', 'note' => 'No investors, no outside capital.'],
    ];

    $numbers = [
        ['value' => '0', 'label' => 'Trackers', 'note' => 'No advertising trackers, no third party analytics, no pixels anywhere in the application.'],
        ['value' => '7', 'label' => 'Languages', 'note' => 'The application, the documentation portal and this site, each fully translated.'],
        ['value' => '148', 'label' => 'API endpoints', 'note' => 'A test fails the build when an endpoint ships without documentation.'],
        ['value' => '$0', 'label' => 'To self-host, forever', 'note' => 'No account with us, no licence key, no feature held back from the free version.'],
    ];

    $logos = [
        ['title' => 'Primary lockup', 'note' => 'For light backgrounds', 'dark' => false, 'monogram' => false],
        ['title' => 'Reversed lockup', 'note' => 'For dark backgrounds', 'dark' => true, 'monogram' => false],
        ['title' => 'Monogram', 'note' => 'Avatars, favicons, app icon', 'dark' => false, 'monogram' => true],
        ['title' => 'Monogram on dark', 'note' => 'Dark interfaces, stickers', 'dark' => true, 'monogram' => true],
    ];

    $screenshots = [
        ['title' => 'Dashboard', 'slot' => 'dashboard capture', 'caption' => 'Collections, recent activity and what the account is worth.'],
        ['title' => 'Collection grid', 'slot' => 'collection grid capture', 'caption' => 'Photo first browsing, with the list and table views alongside it.'],
        ['title' => 'Item detail', 'slot' => 'item detail capture', 'caption' => 'One item, its custom fields, its photos and the copies owned of it.'],
        ['title' => 'Copy history', 'slot' => 'history timeline capture', 'caption' => 'Purchases, valuations, loans, moves, care and provenance on one timeline.'],
        ['title' => 'Custom collection types', 'slot' => 'custom fields capture', 'caption' => 'The field builder that lets a type describe any hobby.'],
        ['title' => 'Self-hosted install', 'slot' => 'docker install capture', 'caption' => 'The Docker Compose stack coming up, in a terminal.'],
    ];

    $links = [
        ['key' => 'Website', 'value' => 'getkollek.com', 'url' => route('marketing.index')],
        ['key' => 'Source', 'value' => 'github.com/djaiss/kollek', 'url' => $github],
        ['key' => 'Documentation', 'value' => 'The product documentation portal', 'url' => route('marketing.docs.portal.home.show')],
        ['key' => 'API reference', 'value' => 'Every endpoint, with examples', 'url' => route('marketing.docs.api.index')],
        ['key' => 'Pricing', 'value' => 'What it costs, and why', 'url' => route('marketing.pricing.index')],
        ['key' => 'FAQ', 'value' => 'A hundred answers, limits included', 'url' => route('marketing.faq.index')],
        ['key' => 'Changelog', 'value' => 'Releases on GitHub', 'url' => $github.'/releases'],
        ['key' => 'Discussions', 'value' => 'Questions and ideas in the open', 'url' => $github.'/discussions'],
    ];

    $sectionHeading = 'flex flex-wrap items-baseline gap-x-4 gap-y-1 border-b border-ink pb-5';
    $sectionNumber = 'font-mono text-[13px] text-muted-soft';
    $sectionTitle = 'flex-1 text-[26px] font-semibold tracking-[-0.9px] text-ink sm:text-[32px] sm:tracking-[-1.1px]';
    $sectionAside = 'font-mono text-xs text-muted-soft';
    $copyButton = 'inline-flex items-center gap-x-2 rounded-lg border border-hairline px-3 py-2 font-mono text-xs text-muted transition-colors hover:bg-sidebar hover:text-ink';
@endphp

<x-marketing-layout title="Media kit">
  <style>
    .media-slot {
        background-image: repeating-linear-gradient(
            135deg,
            var(--sidebar) 0px,
            var(--sidebar) 11px,
            var(--card) 11px,
            var(--card) 22px
        );
    }
  </style>

  <div class="mx-auto max-w-[1200px] px-5 pt-10 sm:px-8 sm:pt-16">
    <div class="flex items-start gap-3 rounded-lg border border-hairline bg-canvas px-4 py-3">
      @svg('lucide-languages', 'mt-0.5 size-4 shrink-0 text-muted')
      <p class="text-sm text-muted">{{ __('This page is only available in English.') }}</p>
    </div>
  </div>

  <section id="top" class="mx-auto max-w-[1200px] scroll-mt-24 px-5 pt-12 sm:px-8 sm:pt-16">
    <div class="grid grid-cols-1 items-end gap-12 lg:grid-cols-[1.35fr_1fr] lg:gap-20">
      <div>
        <p class="mb-6 font-mono text-xs font-medium tracking-[1.4px] text-muted-soft uppercase">Media kit &middot; Updated {{ $updated }}</p>

        <h1 class="text-[32px] leading-[1.05] font-semibold tracking-[-1px] text-balance text-ink sm:text-5xl sm:tracking-[-1.5px] lg:text-[64px] lg:leading-[1.02] lg:tracking-[-2.4px]">
          Everything a journalist needs to write about KolleK.
        </h1>

        <p class="mt-7 max-w-[600px] text-[17px] leading-relaxed text-muted sm:text-[19px]">
          Boilerplate you can paste, facts you can check, and assets you can publish. No form, no embargo, no approval loop.
        </p>

        <div class="mt-9 flex flex-wrap gap-3">
          <a href="#contact" class="flex h-12 items-center rounded-md bg-primary px-6 text-[15px] font-semibold text-on-primary transition-opacity hover:opacity-90">Press contact</a>
          <a href="#assets" class="flex h-12 items-center rounded-md border border-hairline bg-canvas px-5.5 text-[15px] font-semibold text-ink transition-colors hover:bg-sidebar">Brand assets</a>
        </div>
      </div>

      <div class="border-hairline lg:border-l lg:pl-8" x-data="copyToClipboard(@js($oneLiner))">
        <p class="mb-4 font-mono text-[11px] tracking-[1.2px] text-muted-soft uppercase">In one sentence</p>
        <p class="text-[22px] leading-[1.45] font-medium tracking-[-0.5px] text-ink">{{ $oneLiner }}</p>

        <button type="button" @click="copy()" class="{{ $copyButton }} mt-6">
          <span x-show="! copied">Copy sentence</span>
          <span x-show="copied" x-cloak>&check; Copied</span>
        </button>
      </div>
    </div>
  </section>

  <section id="boilerplate" class="mx-auto max-w-[1200px] scroll-mt-24 px-5 pt-16 sm:px-8 sm:pt-24">
    <div class="{{ $sectionHeading }}">
      <span class="{{ $sectionNumber }}">01</span>
      <h2 class="{{ $sectionTitle }}">Descriptions you can reuse</h2>
      <span class="{{ $sectionAside }}">verbatim, no attribution needed</span>
    </div>

    <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-[1fr_1.6fr]">
      @foreach ([
          ['label' => 'Short description', 'text' => $shortDescription, 'action' => 'Copy short'],
          ['label' => 'Long description', 'text' => $longDescription, 'action' => 'Copy long'],
      ] as $boilerplate)
        <div class="flex flex-col gap-4 rounded-2xl border border-hairline bg-page p-7" x-data="copyToClipboard(@js($boilerplate['text']))">
          <div class="flex items-center justify-between gap-4">
            <p class="text-[15px] font-semibold tracking-[-0.2px] text-ink">{{ $boilerplate['label'] }}</p>
            <span class="font-mono text-[11px] text-muted-soft">{{ str_word_count($boilerplate['text']) }} words</span>
          </div>

          <p class="text-base leading-[1.7] text-body">{{ $boilerplate['text'] }}</p>

          <div class="mt-auto pt-1">
            <button type="button" @click="copy()" class="{{ $copyButton }}">
              <span x-show="! copied">{{ $boilerplate['action'] }}</span>
              <span x-show="copied" x-cloak>&check; Copied</span>
            </button>
          </div>
        </div>
      @endforeach
    </div>
  </section>

  <section id="facts" class="mx-auto max-w-[1200px] scroll-mt-24 px-5 pt-16 sm:px-8 sm:pt-24">
    <div class="{{ $sectionHeading }}">
      <span class="{{ $sectionNumber }}">02</span>
      <h2 class="{{ $sectionTitle }}">Key facts</h2>
      <span class="{{ $sectionAside }}">checked against the code, not the pitch</span>
    </div>

    <div class="grid grid-cols-1 gap-x-16 lg:grid-cols-2">
      @foreach ($facts as $fact)
        <div class="grid grid-cols-1 gap-x-6 border-b border-hairline py-5 sm:grid-cols-[150px_1fr]">
          <p class="pt-0.5 font-mono text-xs tracking-[1px] text-muted-soft uppercase">{{ $fact['key'] }}</p>
          <div class="mt-1 sm:mt-0">
            <p class="text-base font-medium tracking-[-0.2px] text-ink">{{ $fact['value'] }}</p>
            <p class="mt-1 text-[13px] leading-[1.5] text-muted">{{ $fact['note'] }}</p>
          </div>
        </div>
      @endforeach
    </div>
  </section>

  <section id="numbers" class="mx-auto max-w-[1200px] scroll-mt-24 px-5 pt-16 sm:px-8 sm:pt-24">
    <div class="{{ $sectionHeading }}">
      <span class="{{ $sectionNumber }}">03</span>
      <h2 class="{{ $sectionTitle }}">Numbers worth printing</h2>
      <span class="{{ $sectionAside }}">as of {{ $updated }}</span>
    </div>

    <div class="mt-px grid grid-cols-1 gap-px border-b border-hairline bg-hairline sm:grid-cols-2 lg:grid-cols-4">
      @foreach ($numbers as $number)
        <div class="flex flex-col gap-2.5 bg-page px-6 py-8">
          <p class="text-[42px] leading-none font-semibold tracking-[-1.8px] text-ink">{{ $number['value'] }}</p>
          <p class="text-sm font-semibold text-ink">{{ $number['label'] }}</p>
          <p class="text-[13px] leading-[1.55] text-muted">{{ $number['note'] }}</p>
        </div>
      @endforeach
    </div>

    <p class="mt-5 max-w-[640px] text-[13px] leading-relaxed text-muted-soft">
      There is no user count here, and that is not modesty. A self-hosted instance calls home to nothing, so nobody, including us, can count them. We would rather print no number than a guess.
    </p>
  </section>

  <section id="assets" class="mx-auto max-w-[1200px] scroll-mt-24 px-5 pt-16 sm:px-8 sm:pt-24">
    <div class="{{ $sectionHeading }}">
      <span class="{{ $sectionNumber }}">04</span>
      <h2 class="{{ $sectionTitle }}">Logos</h2>
      <span class="{{ $sectionAside }}">downloads on the way</span>
    </div>

    <div class="mt-8 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
      @foreach ($logos as $logo)
        <div class="overflow-hidden rounded-2xl border border-hairline">
          <div class="flex h-[150px] items-center justify-center gap-x-2.5 border-b border-hairline {{ $logo['dark'] ? 'bg-[#101010]' : 'bg-white' }}">
            <x-logo :size="$logo['monogram'] ? 72 : 34" hoverColor="{{ $logo['dark'] ? '#ffffff' : '#111111' }}" aria-hidden="true" />
            @unless ($logo['monogram'])
              <x-wordmark height="22" class="{{ $logo['dark'] ? 'text-white' : 'text-[#111111]' }}" />
            @endunless
          </div>

          <div class="px-4.5 pt-4 pb-4.5">
            <p class="text-sm font-semibold text-ink">{{ $logo['title'] }}</p>
            <p class="mt-1 text-[13px] text-muted">{{ $logo['note'] }}</p>

            <div class="mt-3.5 flex gap-2">
              @foreach (['SVG', 'PNG'] as $format)
                <span class="flex flex-1 items-center justify-center gap-x-1.5 rounded-md border border-hairline py-2 font-mono text-xs text-muted-soft">
                  {{ $format }}
                  <x-soon />
                </span>
              @endforeach
            </div>
          </div>
        </div>
      @endforeach
    </div>

    <div class="mt-6 flex flex-wrap gap-x-8 gap-y-2 text-[13px] leading-relaxed text-muted">
      <p>Clear space: at least the corner radius of the monogram on every side.</p>
      <p>Minimum width: 96 px for the lockup, 24 px for the monogram.</p>
      <p>Do not recolour, outline, stretch, or add effects.</p>
    </div>
  </section>

  {{-- SCREENSHOTS. Commented out until there are real captures to put in the slots:
       a press page full of grey placeholder boxes is worse than one section short.
       The sections below were renumbered to close the gap, so bringing this back means
       numbering it 05 and pushing Founder and Links down again. --}}
  {{-- <section id="screenshots" class="mx-auto max-w-[1200px] scroll-mt-24 px-5 pt-16 sm:px-8 sm:pt-24">
    <div class="{{ $sectionHeading }}">
      <span class="{{ $sectionNumber }}">05</span>
      <h2 class="{{ $sectionTitle }}">Product screenshots</h2>
      <span class="{{ $sectionAside }}">captures being prepared</span>
    </div>

    <div class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
      @foreach ($screenshots as $screenshot)
        <div>
          <div class="media-slot flex aspect-[16/10] items-end rounded-xl border border-hairline p-3.5">
            <span class="rounded-md border border-hairline bg-page px-2.5 py-1 font-mono text-[11px] text-muted">{{ $screenshot['slot'] }}</span>
          </div>

          <div class="mt-3 flex items-baseline justify-between gap-3">
            <p class="text-[15px] font-semibold tracking-[-0.2px] text-ink">{{ $screenshot['title'] }}</p>
            <span class="font-mono text-[11px] text-muted-soft">2880&times;1800</span>
          </div>

          <p class="mt-1 text-[13px] leading-[1.55] text-muted">{{ $screenshot['caption'] }}</p>
        </div>
      @endforeach
    </div>
  </section> --}}

  <section id="founder" class="mx-auto max-w-[1200px] scroll-mt-24 px-5 pt-16 sm:px-8 sm:pt-24">
    <div class="{{ $sectionHeading }}">
      <span class="{{ $sectionNumber }}">05</span>
      <h2 class="{{ $sectionTitle }}">Founder</h2>
      <span class="{{ $sectionAside }}">bio cleared for print</span>
    </div>

    <div class="mt-9 grid grid-cols-1 gap-10 lg:grid-cols-[340px_1fr] lg:gap-14">
      <div>
        <div class="media-slot flex items-end rounded-2xl border border-hairline p-3.5">
          <x-image src="{{ asset('images/marketing/media-kit/regis.webp') }}" srcset="{{ asset('images/marketing/media-kit/regis.webp') }}, {{ asset('images/marketing/media-kit/regis@2x.webp') }} 2x" height="340" width="340" alt="Regis Freyd" class="" />
        </div>

        <div class="mt-3 flex gap-2">
          @foreach (['JPG · print', 'JPG · web'] as $format)
            <span class="flex flex-1 items-center justify-center gap-x-1.5 rounded-md border border-hairline py-2 font-mono text-xs text-muted-soft">
              {{ $format }}
              <x-soon />
            </span>
          @endforeach
        </div>
      </div>

      <div x-data="copyToClipboard(@js($founderBio))">
        <p class="text-[26px] font-semibold tracking-[-0.8px] text-ink">Regis Freyd</p>
        <p class="mt-2 font-mono text-xs tracking-[1px] text-muted-soft uppercase">Founder &amp; sole developer &middot; Canada</p>

        <div class="mt-6 flex max-w-[620px] flex-col gap-4">
          <p class="text-base leading-[1.7] text-body">{{ $founderBio }}</p>
          <p class="text-base leading-[1.7] text-body">
            The application is built around one idea: collectors do not own categories, they own particular objects with particular histories. That shows up in the data model, where an item and the individual copies owned of it are deliberately two different things.
          </p>
        </div>

        <button type="button" @click="copy()" class="{{ $copyButton }} mt-7">
          <span x-show="! copied">Copy bio</span>
          <span x-show="copied" x-cloak>&check; Copied</span>
        </button>
      </div>
    </div>
  </section>

  <section id="links" class="mx-auto max-w-[1200px] scroll-mt-24 px-5 pt-16 sm:px-8 sm:pt-24">
    <div class="{{ $sectionHeading }}">
      <span class="{{ $sectionNumber }}">06</span>
      <h2 class="{{ $sectionTitle }}">Links</h2>
    </div>

    <div class="grid grid-cols-1 gap-x-16 lg:grid-cols-2">
      @foreach ($links as $link)
        <a
          href="{{ $link['url'] }}"
          @if (! str_starts_with($link['url'], config('app.url'))) target="_blank" rel="noopener" @endif
          class="grid grid-cols-[130px_1fr_auto] items-baseline gap-x-6 border-b border-hairline py-4.5 text-ink transition-colors hover:text-body"
        >
          <span class="font-mono text-xs tracking-[1px] text-muted-soft uppercase">{{ $link['key'] }}</span>
          <span class="text-[15px] font-medium">{{ $link['value'] }}</span>
          <x-lucide-arrow-up-right class="h-4 w-4 text-muted-soft" />
        </a>
      @endforeach
    </div>
  </section>

  <section id="contact" class="mx-auto max-w-[1200px] scroll-mt-24 px-5 pt-16 sm:px-8 sm:pt-24">
    <div class="grid grid-cols-1 gap-12 rounded-2xl bg-card px-6 py-12 sm:px-12 sm:py-14 lg:grid-cols-2 lg:gap-14">
      <div @if ($pressEmail) x-data="copyToClipboard()" @endif>
        <p class="mb-4 font-mono text-[11px] tracking-[1.2px] text-muted-soft uppercase">Press contact</p>
        <h2 class="text-[28px] leading-[1.1] font-semibold tracking-[-1.2px] text-ink sm:text-[34px]">One inbox. One person.</h2>

        @if ($pressEmail)
          <x-marketing.email :address="$pressEmail" x-ref="pressEmail" class="mt-6 inline-block border-b border-hairline font-mono text-[19px] font-medium text-ink hover:border-ink" />
        @endif

        <p class="mt-5 max-w-[420px] text-[15px] leading-relaxed text-muted">
          Interviews, fact checks, a demo instance, or a screenshot at a size that is not in the kit: ask and it gets made. Answers come in English or French, usually the same day.
        </p>

        @if ($pressEmail)
          <button type="button" @click="copy($refs.pressEmail.href.replace('mailto:', ''))" class="{{ $copyButton }} mt-6 border-hairline">
            <span x-show="! copied">Copy address</span>
            <span x-show="copied" x-cloak>&check; Copied</span>
          </button>
        @else
          <a href="{{ $github }}/discussions" target="_blank" rel="noopener" class="{{ $copyButton }} mt-6">Ask on GitHub</a>
        @endif
      </div>

      <div>
        <p class="mb-4 font-mono text-[11px] tracking-[1.2px] text-muted-soft uppercase">Usage</p>
        <div class="flex max-w-[460px] flex-col gap-3.5">
          <p class="text-base leading-[1.7] text-body">
            Everything on this page, the logos, the portrait and the copy, may be used in editorial coverage of KolleK without asking first, in print, on video, and as podcast artwork.
          </p>
          <p class="text-base leading-[1.7] text-body">
            Please do not alter the marks, imply a partnership or an endorsement, or use them on merchandise, in advertising, or in your own product. The software is MIT licensed. The brand assets are not.
          </p>
        </div>
      </div>
    </div>
  </section>
</x-marketing-layout>
