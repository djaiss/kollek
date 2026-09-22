{{-- The public page about who is behind KolleK. --}}

@php
    $github = config('marketing.github_url');

    $team = [
        [
            'name' => 'Régis',
            'initial' => 'R',
            // The one human on the list is the one with a face. Everybody else keeps
            // their initial, which is what makes the difference read at a glance.
            'photo' => 'images/regis.png',
            'kind' => __('Human'),
            'role' => __('Founder, support, main engineer'),
            'blurb' => __('Designs the product, writes the code, answers support requests and occasionally remembers to eat lunch.'),
        ],
        [
            'name' => 'Claude',
            'initial' => 'C',
            'kind' => __('AI coworker'),
            'role' => __('Senior backend developer'),
            'blurb' => __('Keeps the architecture clean, the code maintainable and politely disagrees with questionable ideas.'),
        ],
        [
            'name' => 'ChatGPT',
            'initial' => 'G',
            'kind' => __('AI coworker'),
            'role' => __('Product manager and professional rubber duck'),
            'blurb' => __('Always available for brainstorming, feature discussions and asking inconvenient questions before writing code.'),
        ],
        [
            'name' => 'Gemini',
            'initial' => 'Ge',
            'kind' => __('AI coworker'),
            'role' => __('QA engineer, occasionally argumentative'),
            'blurb' => __('Excellent at spotting edge cases and occasionally convinced everyone else is wrong.'),
        ],
        [
            'name' => 'Codex',
            'initial' => 'Cx',
            'kind' => __('AI coworker'),
            'role' => __('Frontend developer, works best after coffee'),
            'blurb' => __('Turns ideas into interfaces and somehow always knows another keyboard shortcut.'),
        ],
        [
            'name' => 'Cursor',
            'initial' => 'Cu',
            'kind' => __('AI coworker'),
            'role' => __('Junior developer, types really fast'),
            'blurb' => __('Produces code at impressive speed. Occasionally needs adult supervision.'),
        ],
        [
            'name' => 'GitHub Copilot',
            'initial' => 'Co',
            'kind' => __('AI coworker'),
            'role' => __('Intern, never sleeps'),
            'blurb' => __('Always eager to help. Occasionally a little too eager.'),
        ],
    ];

    $facts = [
        ['label' => __('Founded'), 'value' => '2026'],
        ['label' => __('Human employees'), 'value' => '1'],
        ['label' => __('Team members according to this page'), 'value' => '7'],
        ['label' => __('Offices'), 'value' => __('1 desk')],
        ['label' => __('Venture capital'), 'value' => '$0'],
        ['label' => __('Investors'), 'value' => __('None')],
        ['label' => __('Meetings this week'), 'value' => __('Hopefully 0')],
        ['label' => __('Coffee consumed'), 'value' => __('Classified')],
        ['label' => __('Bus factor'), 'value' => __('Concerning')],
    ];

    // A bare year reads the same in every locale, so only the full date is translated.
    $timeline = [
        [
            'date' => '1981',
            'title' => __('Régis is born.'),
            'body' => __('No collection yet. Give it time.'),
        ],
        [
            'date' => '1987',
            'title' => __('The first object is carefully kept for no logical reason.'),
            'body' => __('A collector is born. Nobody notices, least of all him.'),
        ],
        [
            'date' => '1996',
            'title' => __('The first Pokémon card enters the collection.'),
            'body' => __('Things escalate quickly. Sleeves are purchased. A binder becomes several binders.'),
        ],
        [
            'date' => '2004',
            'title' => __('The first manga arrives, after years of Dragon Ball Z.'),
            'body' => __('Discovers a new passion for collecting manga.'),
        ],
        [
            'date' => '2026',
            'title' => __('KolleK starts.'),
            'body' => __('After years of trying collection software, spreadsheets and increasingly creative folder structures, Régis starts building KolleK.'),
        ],
        [
            'date' => __('August 2, 2026'),
            'title' => __('KolleK launches.'),
            'body' => __('Everything will definitely work perfectly on day one. Probably.'),
        ],
    ];

    $promises = [
        ['title' => __('MIT licensed'), 'body' => __('Short licence, no clauses waiting to surprise you later.')],
        ['title' => __('Open source'), 'body' => __('The full source is public and the work happens in the open.')],
        ['title' => __('Self-hostable'), 'body' => __('One Docker command and it runs on hardware you control.')],
        ['title' => __('Your data is yours'), 'body' => __('Stored where you choose, exportable in full, whenever you want.')],
    ];

    $ingredients = [
        'Laravel',
        'Blade',
        'Alpine.js',
        __('Several AI assistants'),
        __('An unreasonable number of Git commits'),
        __('Coffee'),
        __('Late-night ideas'),
        __('Absolutely no blockchain'),
    ];

    $method = [
        ['n' => '01', 'text' => __('Have an idea, usually at a time no reasonable person would call working hours.')],
        ['n' => '02', 'text' => __('Argue about it with the AI coworkers until the idea is either better or gone.')],
        ['n' => '03', 'text' => __('Build it, test it, break it, fix it, commit far too often.')],
        ['n' => '04', 'text' => __('Ship, then sleep. Order negotiable.')],
    ];

    $notBuilding = [
        ['title' => __('Blockchain'), 'note' => __('Your comic book collection does not need a consensus mechanism.')],
        ['title' => __('NFTs'), 'note' => __('You already own the object. That was the whole point.')],
        ['title' => __('Selling your data'), 'note' => __('It is not for sale, mostly because it is not mine.')],
        ['title' => __('Ads everywhere'), 'note' => __('Nobody catalogues a wine cellar hoping to see a banner.')],
        ['title' => __('Seventeen subscription tiers'), 'note' => __('One price, and a self-hosted option that costs nothing.')],
        ['title' => __('AI features that exist only because they are fashionable'), 'note' => __('The AI helps build KolleK. It does not need to live inside it.')],
    ];

    $openSourcePoints = [
        ['title' => __('Read the code'), 'body' => __('The whole application, not a trimmed community edition.')],
        ['title' => __('Contribute'), 'body' => __('Issues, patches and translations are all welcome.')],
        ['title' => __('Self-host it'), 'body' => __('Inspect it, run it, keep the data on your own volume.')],
        ['title' => __('Built in public'), 'body' => __('Releases on GitHub and an honest feature status page.')],
    ];

    $sectionTitle = 'text-[30px] leading-[1.1] font-semibold tracking-[-1px] text-ink sm:text-[40px] sm:tracking-[-1.5px]';
    $sectionLede = 'mt-4.5 text-[17px] leading-relaxed text-muted';
    $eyebrow = 'font-mono text-xs font-medium tracking-[1.4px] text-muted-soft uppercase';
@endphp

<x-marketing-layout :title="__('About')">
  <section class="mx-auto max-w-[1200px] px-5 pt-12 sm:px-8 sm:pt-24">
    <p class="{{ $eyebrow }} mb-6">{{ __('About') }}</p>

    <h1 class="max-w-[760px] text-[40px] leading-[1.04] font-semibold tracking-[-1.4px] text-balance text-ink sm:text-5xl lg:text-[64px] lg:tracking-[-2.4px]">
      {{ __('Built by a team of seven.') }}
    </h1>

    <p class="mt-6.5 max-w-[620px] text-[19px] leading-[1.5] text-body sm:text-[21px]">
      {{ __('One human founder. Six incredibly productive AI coworkers.') }}
    </p>

    <div class="mt-8 grid max-w-[900px] grid-cols-1 gap-7 lg:grid-cols-2">
      <p class="text-base leading-[1.7] text-muted">
        {{ __('KolleK is an independent open source project built by Régis. There is no company behind it, no board to report to and no growth target written on a whiteboard. There is a desk, a collection that got out of hand and a lot of commits.') }}
      </p>
      <p class="text-base leading-[1.7] text-muted">
        {{ __('The rest of the team is a set of AI tools that help write code, review ideas and catch the things one person alone would miss at 1am. They do not attend meetings, which is their best quality.') }}
      </p>
    </div>

    <div class="mt-9 flex flex-wrap gap-3">
      <a href="#team" class="flex h-12 items-center rounded-md bg-primary px-5.5 text-[15px] font-semibold text-on-primary transition-opacity hover:opacity-90">{{ __('Meet the team') }}</a>
      <a href="#timeline" class="flex h-12 items-center rounded-md border border-hairline bg-canvas px-5.5 text-[15px] font-semibold text-ink transition-colors hover:bg-sidebar">{{ __('How it started') }}</a>
    </div>
  </section>

  <section id="team" class="mx-auto max-w-[1200px] scroll-mt-24 px-5 pt-16 sm:px-8 sm:pt-28">
    <div class="max-w-[640px]">
      <h2 class="{{ $sectionTitle }}">{{ __('Meet the team') }}</h2>
      <p class="{{ $sectionLede }}">{{ __('Roles are approximate. Enthusiasm is not.') }}</p>
    </div>

    <ul class="mt-11 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
      @foreach ($team as $member)
        <li class="flex flex-col gap-4 rounded-2xl border border-hairline-soft bg-card p-6.5 transition-colors hover:border-hairline">
          <div class="flex items-center gap-3.5">
            @if (! empty($member['photo']))
              <x-image
                :src="asset($member['photo'])"
                :srcset="asset($member['photo']) . ', ' . asset(Str::replaceLast('.', '@2x.', $member['photo'])) . ' 2x'"
                alt=""
                height="48"
                width="48"
                class="size-12 shrink-0 rounded-xl border border-hairline object-cover"
              />
            @else
              <span aria-hidden="true" class="flex size-12 shrink-0 items-center justify-center rounded-xl border border-hairline bg-page font-mono text-lg font-medium text-ink">{{ $member['initial'] }}</span>
            @endif

            <div class="flex min-w-0 flex-col gap-1">
              <h3 class="text-lg font-semibold tracking-[-0.3px] text-ink">{{ $member['name'] }}</h3>
              <p class="font-mono text-[10px] tracking-[1.1px] text-muted-soft uppercase">{{ $member['kind'] }}</p>
            </div>
          </div>

          <p class="text-sm leading-[1.45] font-semibold text-body">{{ $member['role'] }}</p>
          <p class="text-[15px] leading-[1.65] text-muted">{{ $member['blurb'] }}</p>
        </li>
      @endforeach
    </ul>

    <aside class="mt-6 flex items-start gap-3.5 rounded-2xl border border-hairline bg-sidebar px-6 py-5.5">
      @svg('lucide-info', 'mt-0.5 size-4.5 shrink-0 text-muted-soft')
      <p class="text-[15px] leading-[1.65] text-muted">
        <strong class="font-semibold text-ink">{{ __('Disclaimer:') }}</strong>
        {{ __('Only one member of this team is human. The others are AI tools that help write code, review ideas, test features and make KolleK better every day.') }}
      </p>
    </aside>
  </section>

  <section class="mx-auto max-w-[1200px] px-5 pt-16 sm:px-8 sm:pt-28">
    <h2 class="{{ $sectionTitle }}">{{ __('Company facts') }}</h2>
    <p class="{{ $sectionLede }} max-w-[560px]">{{ __('Every number here is accurate, which is the part we are quietly proud of.') }}</p>

    <dl class="mt-11 grid grid-cols-1 gap-px overflow-hidden rounded-2xl border border-hairline bg-hairline sm:grid-cols-2 lg:grid-cols-3">
      @foreach ($facts as $fact)
        <div class="flex flex-col gap-2 bg-page px-6 py-6.5">
          <dt class="font-mono text-[11px] tracking-[1.1px] text-muted-soft uppercase">{{ $fact['label'] }}</dt>
          <dd class="text-[26px] font-semibold tracking-[-0.8px] text-ink">{{ $fact['value'] }}</dd>
        </div>
      @endforeach
    </dl>
  </section>

  <section id="timeline" class="mx-auto max-w-[1200px] scroll-mt-24 px-5 pt-16 sm:px-8 sm:pt-28">
    <div class="max-w-[620px]">
      <h2 class="{{ $sectionTitle }}">{{ __('The road to KolleK') }}</h2>
      <p class="{{ $sectionLede }}">{{ __('A collection app is rarely the first symptom.') }}</p>
    </div>

    <ol class="mt-12 flex max-w-[760px] flex-col gap-10 border-l border-hairline pl-7.5">
      @foreach ($timeline as $entry)
        <li class="relative">
          <span aria-hidden="true" class="absolute top-1.5 -left-[37px] size-3.5 rounded-full border-2 border-ink bg-page"></span>

          <p class="font-mono text-xs font-medium tracking-[0.8px] text-body uppercase">{{ $entry['date'] }}</p>
          <h3 class="mt-2.5 text-xl font-semibold tracking-[-0.5px] text-ink">{{ $entry['title'] }}</h3>
          <p class="mt-2 max-w-[560px] text-base leading-[1.7] text-muted">{{ $entry['body'] }}</p>
        </li>
      @endforeach
    </ol>
  </section>

  <section class="mx-auto max-w-[1200px] px-5 pt-16 sm:px-8 sm:pt-28">
    <div class="grid grid-cols-1 items-start gap-10 lg:grid-cols-2 lg:gap-14">
      <div>
        <h2 class="{{ $sectionTitle }}">{{ __('Why KolleK exists') }}</h2>

        <div class="mt-6 flex flex-col gap-4.5">
          <p class="text-[17px] leading-[1.7] text-muted">{{ __('I wanted an app that treated my collection the way I think about it: one object at a time, with its own condition, its own price, its own small history. Nothing I tried did that without asking me to bend my collection into its shape first.') }}</p>
          <p class="text-[17px] leading-[1.7] text-muted">{{ __('The other thing that bothered me was ownership. A collection is a long term project. Handing years of careful records to a service that might quietly shut down, or start charging per item, never felt right.') }}</p>
          <p class="text-[17px] leading-[1.7] text-muted">{{ __('So KolleK is MIT licensed, open source and self-hostable. Run it on your own machine, keep the database on a drive you can hold and export everything whenever you feel like it. Your data belongs to you, not to me.') }}</p>
        </div>
      </div>

      <ul class="flex flex-col gap-px overflow-hidden rounded-2xl border border-hairline bg-hairline">
        @foreach ($promises as $promise)
          <li class="flex items-start gap-3.5 bg-card p-6">
            @svg('lucide-check', 'mt-0.5 size-4.5 shrink-0 text-ink')
            <div>
              <p class="text-base font-semibold tracking-[-0.2px] text-ink">{{ $promise['title'] }}</p>
              <p class="mt-1.5 text-[15px] leading-[1.6] text-muted">{{ $promise['body'] }}</p>
            </div>
          </li>
        @endforeach
      </ul>
    </div>
  </section>

  <section class="mx-auto max-w-[1200px] px-5 pt-16 sm:px-8 sm:pt-28">
    <div class="rounded-2xl border border-hairline p-7 sm:p-12">
      <h2 class="max-w-[620px] text-[26px] leading-[1.15] font-semibold tracking-[-1px] text-ink sm:text-[34px] sm:tracking-[-1.2px]">
        {{ __('Our highly sophisticated development process') }}
      </h2>

      <div class="mt-9 grid grid-cols-1 gap-10 lg:grid-cols-[1fr_1.2fr] lg:gap-12">
        <div>
          <h3 class="{{ $eyebrow }} mb-4.5 tracking-[1.2px]">{{ __('Ingredients') }}</h3>
          <ul class="flex flex-col">
            @foreach ($ingredients as $ingredient)
              <li class="border-b border-hairline-soft py-3 text-base text-body">{{ $ingredient }}</li>
            @endforeach
          </ul>
        </div>

        <div>
          <h3 class="{{ $eyebrow }} mb-4.5 tracking-[1.2px]">{{ __('Method') }}</h3>
          <ol class="flex flex-col gap-4">
            @foreach ($method as $step)
              <li class="flex items-start gap-3.5">
                <span aria-hidden="true" class="w-5 shrink-0 pt-1 font-mono text-xs text-muted-soft">{{ $step['n'] }}</span>
                <span class="text-base leading-[1.65] text-muted">{{ $step['text'] }}</span>
              </li>
            @endforeach
          </ol>

          <p class="mt-6 text-sm leading-[1.7] text-muted-soft italic">{{ __('Serves one developer. Reheats badly on Mondays.') }}</p>
        </div>
      </div>
    </div>
  </section>

  <section class="mx-auto max-w-[1200px] px-5 pt-16 sm:px-8 sm:pt-28">
    <h2 class="{{ $sectionTitle }}">{{ __('Currently not on the roadmap') }}</h2>
    <p class="{{ $sectionLede }} max-w-[560px]">{{ __('Saying no is most of the work.') }}</p>

    <ul class="mt-10 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
      @foreach ($notBuilding as $item)
        <li class="flex items-start gap-3 rounded-xl border border-hairline bg-page px-5.5 py-5">
          @svg('lucide-x', 'mt-1 size-4 shrink-0 text-muted-soft')
          <div>
            <p class="text-base font-semibold tracking-[-0.2px] text-ink">{{ $item['title'] }}</p>
            <p class="mt-1.5 text-sm leading-[1.6] text-muted">{{ $item['note'] }}</p>
          </div>
        </li>
      @endforeach
    </ul>
  </section>

  <section class="mx-auto max-w-[1200px] px-5 pt-16 sm:px-8 sm:pt-28">
    <div class="rounded-3xl bg-[#101010] px-6 py-12 text-white sm:px-14 sm:py-14">
      <div class="grid grid-cols-1 items-center gap-10 lg:grid-cols-[1.1fr_1fr] lg:gap-12">
        <div>
          <p class="mb-4 font-mono text-[11px] tracking-[1.2px] text-[#71717a] uppercase">{{ __('Open source') }}</p>

          <h2 class="text-[28px] leading-[1.12] font-semibold tracking-[-1px] text-balance sm:text-4xl sm:tracking-[-1.2px]">
            {{ __('The code is public. Including the parts I would rather rewrite.') }}
          </h2>

          <p class="mt-5 text-[17px] leading-[1.7] text-[#a1a1aa]">
            {{ __('Every commit lands in the open. You can read the source, check what happens to your data, file an issue, send a patch or fork the whole thing and run it your way. Contributions are welcome, and so is a well argued disagreement.') }}
          </p>

          <div class="mt-8 flex flex-wrap gap-3">
            <a href="{{ $github }}" target="_blank" rel="noopener" class="flex h-12 items-center gap-x-2.5 rounded-md bg-white px-5.5 text-[15px] font-semibold text-[#111111] transition-colors hover:bg-[#dcdcdc]">
              @svg('lucide-github', 'size-4.5')
              {{ __('View on GitHub') }}
            </a>
            <a href="{{ route('marketing.docs.portal.home.show') }}" data-turbo="true" class="flex h-12 items-center rounded-md border border-[#2f2f2f] px-5.5 text-[15px] font-semibold text-white transition-colors hover:bg-[#1a1a1a]">{{ __('Documentation') }}</a>
          </div>
        </div>

        <ul class="flex flex-col gap-px overflow-hidden rounded-xl border border-[#242424] bg-[#242424]">
          @foreach ($openSourcePoints as $point)
            <li class="bg-[#141414] px-5.5 py-5">
              <p class="text-[15px] font-semibold text-white">{{ $point['title'] }}</p>
              <p class="mt-1.5 text-sm leading-[1.6] text-[#a1a1aa]">{{ $point['body'] }}</p>
            </li>
          @endforeach
        </ul>
      </div>
    </div>
  </section>

  <section class="mx-auto max-w-[1200px] px-5 pt-16 sm:px-8 sm:pt-28">
    <div class="rounded-2xl bg-card px-6 py-14 text-center sm:px-12 sm:py-16">
      <h2 class="mx-auto max-w-[640px] text-[26px] leading-[1.25] font-semibold tracking-[-1px] text-balance text-ink sm:text-[34px] sm:tracking-[-1.1px]">
        {{ __('KolleK is the collection app I always wanted to use.') }}
      </h2>

      <p class="mx-auto mt-4.5 max-w-[620px] text-[19px] leading-[1.6] text-muted">
        {{ __('I hope you will enjoy using it as much as I enjoy building it.') }}
      </p>

      <div class="mt-8 flex items-center justify-center gap-x-3">
        <x-image src="{{ asset('images/regis.png') }}" srcset="{{ asset('images/regis.png') }}, {{ asset('images/regis@2x.png') }} 2x" height="52" width="52" alt="Regis Freyd" class="rounded-full" />
        <p class="text-[15px] font-semibold text-ink">Régis</p>
      </div>

      <p class="mx-auto mt-10 max-w-[520px] border-t border-hairline pt-7 font-mono text-xs leading-[1.8] text-muted-soft">
        {{ __('If you have read this entire page, congratulations. You now know more about this company than most investors ever will.') }}
      </p>
    </div>
  </section>
</x-marketing-layout>
