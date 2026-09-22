{{-- The public homepage of the marketing site. --}}

<x-marketing-layout>
  <section id="top" class="mx-auto max-w-[1200px] px-5 pt-16 text-center sm:px-8 sm:pt-24">
    <a href="{{ config('marketing.github_url') }}" target="_blank" rel="noopener" class="mb-7 inline-flex items-center gap-x-2 rounded-full bg-card py-1.5 pr-3.5 pl-1.5 text-[13px] font-medium text-body">
      <span class="rounded-full bg-primary px-2 py-[3px] text-[11px] font-semibold text-on-primary">{{ __('MIT') }}</span>
      {{ __('Open source and self-hostable, forever') }}
    </a>

    <h1 class="mx-auto max-w-[820px] text-[32px] leading-[1.08] font-semibold tracking-[-1px] text-balance text-ink sm:text-5xl sm:tracking-[-1.5px] lg:text-[64px] lg:leading-[1.05] lg:tracking-[-2px]">
      {{ __('The collection manager that belongs to you.') }}
    </h1>

    <p class="mx-auto mt-6 max-w-[600px] text-[17px] leading-relaxed text-muted sm:text-[19px]">
      {{ __('Catalog comics, books, vinyl, trading cards, wine, watches, games, anything you collect. Own your data. Self-host or use the cloud.') }}
    </p>

    <div class="mt-9 flex flex-col justify-center gap-3 sm:flex-row">
      <a href="{{ route('register') }}" class="flex h-12 items-center justify-center rounded-md bg-primary px-6 text-[15px] font-semibold text-on-primary transition-opacity hover:opacity-90">{{ __('Get started') }}</a>

      <a href="{{ config('marketing.github_url') }}" target="_blank" rel="noopener" class="flex h-12 items-center justify-center gap-x-2 rounded-md border border-hairline bg-canvas px-5.5 text-[15px] font-semibold text-ink transition-colors hover:bg-sidebar">
        <x-lucide-github class="h-[18px] w-[18px]" />
        {{ __('View on GitHub') }}
      </a>
    </div>

    <div class="mt-6 flex flex-col items-center gap-y-1.5 text-[13px] text-muted-soft sm:flex-row sm:justify-center sm:gap-x-6">
      <span>{{ __('Pay once, own it forever') }}</span>
      <span aria-hidden="true" class="hidden sm:inline">&middot;</span>
      <span>{{ __('No subscription') }}</span>
    </div>
  </section>

  <section class="mx-auto mt-10 max-w-[1200px] px-5 sm:mt-14 sm:px-8">
    <div class="overflow-hidden rounded-xl border border-hairline bg-canvas shadow-[0_24px_60px_rgba(17,17,17,0.10),0_4px_12px_rgba(17,17,17,0.05)]">
      <div class="flex h-11 items-center gap-x-2 border-b border-hairline-soft bg-sidebar px-4">
        <span class="h-[11px] w-[11px] rounded-full bg-hairline"></span>
        <span class="h-[11px] w-[11px] rounded-full bg-hairline"></span>
        <span class="h-[11px] w-[11px] rounded-full bg-hairline"></span>
        <div class="ml-3 hidden h-[26px] max-w-[340px] flex-1 items-center rounded-sm border border-hairline bg-input px-2.5 text-xs text-muted-soft sm:flex">
          {{ Str::lower(config('app.name')) }}.app/dashboard
        </div>
      </div>

      <div class="flex min-h-[440px]">
        <div class="hidden w-[212px] shrink-0 flex-col gap-y-4 border-r border-hairline-soft bg-sidebar p-4 md:flex">
          <div class="flex items-center justify-between px-1.5">
            <div class="flex items-center gap-x-2">
              <x-logo size="22" aria-hidden="true" />
              <x-wordmark height="14" class="text-ink" />
            </div>
            <span class="flex size-7 items-center justify-center rounded-md border border-hairline bg-canvas text-muted">
              <x-lucide-moon class="size-3.5" />
            </span>
          </div>

          <div class="flex items-center gap-x-2.5 rounded-md p-2 text-[13px] font-medium text-body">
            <x-lucide-search class="size-4 shrink-0 text-muted-soft" />
            {{ __('Search') }}
            <span class="ml-auto rounded-sm border border-hairline px-1.5 py-px text-[10px] font-semibold text-muted-soft">&#8984;K</span>
          </div>

          @foreach ([
              ['heading' => __('Workspace'), 'links' => [
                  ['icon' => 'rocket', 'label' => __('Getting started'), 'active' => false, 'count' => null],
                  ['icon' => 'layout-grid', 'label' => __('Dashboard'), 'active' => true, 'count' => null],
                  ['icon' => 'layers', 'label' => __('Collections'), 'active' => false, 'count' => null],
                  ['icon' => 'library', 'label' => __('Series'), 'active' => false, 'count' => null],
                  ['icon' => 'map-pin', 'label' => __('Locations'), 'active' => false, 'count' => null],
                  ['icon' => 'arrow-left-right', 'label' => __('Loans'), 'active' => false, 'count' => 1],
              ]],
              ['heading' => __('Account management'), 'links' => [
                  ['icon' => 'settings', 'label' => __('Account settings'), 'active' => false, 'count' => null],
              ]],
              ['heading' => __('Documentation'), 'links' => [
                  ['icon' => 'book-open', 'label' => __('Documentation site'), 'active' => false, 'count' => null],
                  ['icon' => 'code', 'label' => __('API Docs'), 'active' => false, 'count' => null],
                  ['icon' => 'life-buoy', 'label' => __('Support'), 'active' => false, 'count' => null],
              ]],
          ] as $group)
            <div class="flex flex-col gap-y-0.5">
              <p class="px-2 py-1 text-[11px] font-semibold tracking-wide text-muted-soft uppercase">{{ $group['heading'] }}</p>
              @foreach ($group['links'] as $link)
                <div @class([
                    'flex items-center gap-x-2.5 rounded-md p-2 text-[13px] font-medium',
                    'bg-canvas text-ink' => $link['active'],
                    'text-body' => ! $link['active'],
                ])>
                  <x-dynamic-component :component="'lucide-' . $link['icon']" class="size-4 shrink-0" />
                  {{ $link['label'] }}
                  @if ($link['count'])
                    <span class="ml-auto rounded-full bg-error px-1.5 text-[10px] font-semibold text-white">{{ $link['count'] }}</span>
                  @endif
                </div>
              @endforeach
            </div>
          @endforeach

          <div class="flex-1"></div>

          <div class="flex items-center gap-x-2.5 border-t border-hairline pt-3">
            <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-badge-emerald text-[11px] font-semibold text-white">MG</span>
            <span class="flex min-w-0 flex-col">
              <span class="truncate text-[13px] font-semibold text-ink">Monica Geller</span>
              <span class="text-xs text-muted-soft">{{ __('Owner') }}</span>
            </span>
            <x-lucide-chevrons-up-down class="ml-auto size-4 shrink-0 text-muted-soft" />
          </div>
        </div>

        <div class="min-w-0 flex-1 p-5 sm:p-7">
          <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between sm:gap-4">
            <div class="min-w-0">
              <p class="text-lg font-semibold tracking-[-0.4px] text-ink sm:text-[22px]">{{ __('Good afternoon, :name', ['name' => 'Monica']) }}</p>
              <p class="mt-0.5 text-[13px] text-muted">{{ __("Here's what's happening across your account.") }}</p>
            </div>
            <div class="flex shrink-0 items-center gap-x-3">
              <span class="hidden items-center gap-x-1.5 text-[13px] font-medium text-body lg:flex">
                <x-lucide-sliders-horizontal class="size-4" />
                {{ __('Customize') }}
              </span>
              <span class="flex items-center gap-x-1 rounded-md bg-primary px-3.5 py-2 text-[13px] font-semibold text-on-primary">
                <x-lucide-plus class="size-4" />
                {{ __('New collection') }}
              </span>
            </div>
          </div>

          <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
            @foreach ([
                ['label' => __('Collections'), 'value' => '6', 'note' => __('across your account'), 'dot' => 'bg-badge-violet'],
                ['label' => __('Items'), 'value' => '1,284', 'note' => __('+:count added this month', ['count' => 37]), 'dot' => 'bg-brand'],
                ['label' => __('Copies'), 'value' => '1,510', 'note' => __('physical pieces tracked'), 'dot' => 'bg-badge-emerald'],
                ['label' => __('Estimated value'), 'value' => '$48,920', 'note' => trans_choice('across :count valued copy|across :count valued copies', 1463, ['count' => '1,463']), 'dot' => 'bg-success'],
                ['label' => __('Added this month'), 'value' => '$2,140', 'note' => __('what came in this month is worth'), 'dot' => 'bg-badge-orange'],
                ['label' => __('Average per item'), 'value' => '$38', 'note' => __('across the whole account'), 'dot' => 'bg-badge-pink'],
            ] as $kpi)
              <div class="flex flex-col gap-1 rounded-xl border border-hairline bg-canvas p-3.5">
                <div class="flex items-center gap-1.5">
                  <span class="size-2 shrink-0 rounded-sm {{ $kpi['dot'] }}"></span>
                  <span class="truncate text-[10px] font-semibold text-muted">{{ $kpi['label'] }}</span>
                </div>
                <div class="text-[22px] leading-7 font-semibold tracking-[-0.5px] text-ink">{{ $kpi['value'] }}</div>
                <div class="text-[11px] font-medium text-muted-soft">{{ $kpi['note'] }}</div>
              </div>
            @endforeach
          </div>

          <div class="grid grid-cols-1 gap-5 lg:grid-cols-[1.6fr_1fr] lg:items-start">
            <div class="overflow-hidden rounded-xl border border-hairline bg-canvas">
              <div class="flex items-center gap-2.5 border-b border-hairline-soft px-4 py-3">
                <span class="size-2.5 shrink-0 rounded-full bg-brand"></span>
                <h3 class="text-sm font-semibold text-ink">{{ __('Recent additions') }}</h3>
                <span class="hidden text-xs text-muted-soft xl:inline">{{ __('the newest things you catalogued') }}</span>
                <span class="ml-auto shrink-0 text-xs font-semibold text-body">{{ __('View all') }} &rarr;</span>
              </div>

              @foreach ([
                  ['emoji' => '📚', 'name' => 'Daredevil #281', 'collection' => __('Marvel Comics 1990s'), 'condition' => __('Near Mint'), 'location' => __('Display Case'), 'copies' => 1],
                  ['emoji' => '💿', 'name' => 'Kind of Blue', 'collection' => __('Jazz LPs'), 'condition' => __('Near Mint'), 'location' => __('Living Room'), 'copies' => 2],
                  ['emoji' => '🍷', 'name' => 'Château Margaux 1995', 'collection' => __('Wine Cellar'), 'condition' => __('Good'), 'location' => __('Storage'), 'copies' => 6],
                  ['emoji' => '🃏', 'name' => 'Charizard, 1st Edition', 'collection' => __('Trading Cards'), 'condition' => __('Near Mint'), 'location' => __('Display Case'), 'copies' => 1],
              ] as $row)
                <div class="flex items-center gap-3.5 border-b border-hairline-soft px-4 py-3 last:border-b-0">
                  <div class="flex h-14 w-10 shrink-0 items-center justify-center rounded-lg bg-card text-lg">{{ $row['emoji'] }}</div>
                  <div class="min-w-0 flex-1">
                    <div class="truncate text-sm font-semibold text-ink">{{ $row['name'] }}</div>
                    <div class="mt-0.5 truncate text-xs text-muted-soft">{{ $row['collection'] }}<span class="hidden sm:inline"> &middot; {{ $row['condition'] }} &middot; {{ $row['location'] }}</span></div>
                  </div>
                  <div class="shrink-0 text-right">
                    <div class="text-xs font-semibold text-ink">{{ trans_choice(':count copy|:count copies', $row['copies'], ['count' => $row['copies']]) }}</div>
                    <div class="mt-0.5 text-xs text-muted-soft">{{ __('1 day ago') }}</div>
                  </div>
                </div>
              @endforeach
            </div>

            <div class="flex flex-col gap-5">
              <div class="hidden rounded-xl border border-hairline bg-canvas p-4 lg:block">
                <div class="mb-3 flex items-center justify-between gap-3">
                  <h3 class="text-sm font-semibold text-ink">{{ __('Loan snapshot') }}</h3>
                  <span class="shrink-0 text-xs font-semibold text-body">{{ __('Open loans') }} &rarr;</span>
                </div>
                <div class="grid grid-cols-3 gap-y-3">
                  @foreach ([
                      ['label' => __('Lent out'), 'value' => '3', 'class' => 'text-ink'],
                      ['label' => __('Borrowed in'), 'value' => '1', 'class' => 'text-ink'],
                      ['label' => __('Overdue'), 'value' => '1', 'class' => 'text-error'],
                      ['label' => __('Due soon'), 'value' => '2', 'class' => 'text-warning'],
                      ['label' => __('Planned'), 'value' => '4', 'class' => 'text-ink'],
                      ['label' => __('Returned'), 'value' => '27', 'class' => 'text-muted'],
                  ] as $loan)
                    <div>
                      <div class="text-xl font-semibold tracking-[-0.5px] {{ $loan['class'] }}">{{ $loan['value'] }}</div>
                      <div class="text-[11px] text-muted-soft">{{ $loan['label'] }}</div>
                    </div>
                  @endforeach
                </div>
              </div>

              <div class="rounded-xl border border-hairline bg-canvas p-4">
                <h3 class="text-sm font-semibold text-ink">{{ __('Where things are') }}</h3>
                <p class="mt-0.5 mb-3.5 text-xs text-muted">{{ __('Estimated value by location.') }}</p>
                <div class="flex flex-col gap-3">
                  @foreach ([
                      ['label' => __('Living Room'), 'value' => '$18,240', 'width' => 100, 'bar' => 'bg-brand'],
                      ['label' => __('Storage'), 'value' => '$12,470', 'width' => 68, 'bar' => 'bg-badge-violet'],
                      ['label' => __('Display Case'), 'value' => '$9,880', 'width' => 54, 'bar' => 'bg-badge-pink'],
                      ['label' => __('Garage'), 'value' => '$5,410', 'width' => 30, 'bar' => 'bg-badge-emerald'],
                      ['label' => __('Office'), 'value' => '$2,920', 'width' => 16, 'bar' => 'bg-badge-orange'],
                  ] as $location)
                    <div class="flex items-center gap-2.5">
                      <div class="w-20 shrink-0 truncate text-xs font-medium text-body">{{ $location['label'] }}</div>
                      <div class="h-2 min-w-8 flex-1 overflow-hidden rounded-full bg-card">
                        <div class="h-full rounded-full {{ $location['bar'] }}" style="width: {{ $location['width'] }}%"></div>
                      </div>
                      <div class="w-14 shrink-0 text-right text-xs font-semibold text-ink">{{ $location['value'] }}</div>
                    </div>
                  @endforeach
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="mx-auto max-w-[1200px] px-5 pt-16 sm:px-8 sm:pt-24">
    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
      @foreach ([
          [
              'icon' => 'library-big',
              'title' => __('Any collection'),
              'items' => [__('Books'), __('Comics'), __('Vinyl'), __('Wine'), __('Coins'), __('Games')],
              'description' => __('One tool for every hobby. Collect anything, and the app adapts to it.'),
          ],
          [
              'icon' => 'database',
              'title' => __('Your data'),
              'items' => [__('Import'), __('Export'), __('Self-host'), __('Keep it')],
              'description' => __('Open schema, one click export, no lock-in. Keep your catalog forever.'),
          ],
          [
              'icon' => 'git-fork',
              'title' => __('Open source'),
              'items' => [__('MIT'), __('Transparent'), __('Community'), __('Built to last')],
              'description' => __('The whole source, in the open. Auditable, forkable, community driven.'),
          ],
      ] as $card)
        <div class="flex flex-col gap-y-5 rounded-lg bg-card p-8">
          <span class="flex h-10 w-10 items-center justify-center rounded-[10px] bg-primary">
            <x-dynamic-component :component="'lucide-' . $card['icon']" class="h-5 w-5 text-on-primary" />
          </span>
          <p class="text-[22px] font-semibold tracking-[-0.3px] text-ink">{{ $card['title'] }}</p>
          <div class="flex flex-wrap gap-2">
            @foreach ($card['items'] as $item)
              <span class="rounded-full border border-hairline bg-canvas px-3 py-[5px] text-sm font-medium text-body">{{ $item }}</span>
            @endforeach
          </div>
          <p class="mt-0.5 text-[15px] leading-relaxed text-muted">{{ $card['description'] }}</p>
        </div>
      @endforeach
    </div>
  </section>

  <section id="features" class="mx-auto max-w-[1200px] scroll-mt-24 px-5 pt-16 sm:px-8 sm:pt-24">
    <div class="mb-12 max-w-[640px]">
      <p class="mb-3.5 text-[13px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ __('Organize everything') }}</p>
      <h2 class="text-[28px] leading-[1.1] font-semibold tracking-[-1px] text-ink sm:text-4xl lg:text-5xl lg:tracking-[-1.5px]">{{ __('Structure that bends to how you collect.') }}</h2>
      <p class="mt-5 text-[17px] leading-relaxed text-muted">
        {{ __('Unlimited collections, nested categories, tags, locations, and conditions, all on beautiful item pages that make your catalog feel like a museum, not a spreadsheet.') }}
      </p>
    </div>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
      @foreach ([
          ['title' => __('Unlimited collections'), 'description' => __('Split what you own however you like. No tier gates the structure.'), 'dot' => 'bg-brand'],
          ['title' => __('Nested categories'), 'description' => __('Marvel, then Spider-Man, then 1990s. Structure as deep as you need.'), 'dot' => 'bg-badge-violet'],
          ['title' => __('Tags'), 'description' => __('Cross-cut your catalog with flexible, colour coded labels.'), 'dot' => 'bg-badge-pink'],
          ['title' => __('Nested locations'), 'description' => __('Room, shelf, box. Always know where a piece lives.'), 'dot' => 'bg-badge-emerald'],
          ['title' => __('Conditions'), 'description' => __('Grade every item on the scale that fits your hobby.'), 'dot' => 'bg-badge-orange'],
          ['title' => __('Beautiful item pages'), 'description' => __('Rich pages that make a catalog feel like a museum.'), 'dot' => 'bg-primary'],
      ] as $feature)
        <div class="flex flex-col gap-y-3 rounded-lg border border-hairline bg-canvas p-6 transition-shadow hover:shadow-[0_4px_12px_rgba(0,0,0,0.06)]">
          <span class="flex h-[34px] w-[34px] items-center justify-center rounded-md bg-card">
            <span class="h-3.5 w-3.5 rounded-sm {{ $feature['dot'] }}"></span>
          </span>
          <p class="text-base font-semibold text-ink">{{ $feature['title'] }}</p>
          <p class="text-sm leading-relaxed text-muted">{{ $feature['description'] }}</p>
        </div>
      @endforeach
    </div>
  </section>

  @php
      // Alpine swaps the schema panel client side, so the three types travel to the browser.
      $itemTypes = [
          [
              'name' => __('Books'),
              'fields' => [
                  ['name' => __('Author'), 'type' => __('txt'), 'sample' => 'Le Guin, U.'],
                  ['name' => __('ISBN'), 'type' => '#', 'sample' => '978-0-441…'],
                  ['name' => __('Publisher'), 'type' => __('txt'), 'sample' => 'Ace Books'],
                  ['name' => __('First edition'), 'type' => '☑', 'sample' => 'true'],
                  ['name' => __('Pages'), 'type' => '#', 'sample' => '304'],
              ],
          ],
          [
              'name' => __('Wine'),
              'fields' => [
                  ['name' => __('Vintage'), 'type' => '#', 'sample' => '2015'],
                  ['name' => __('Region'), 'type' => __('txt'), 'sample' => 'Barolo'],
                  ['name' => __('Winery'), 'type' => __('txt'), 'sample' => 'G. Conterno'],
                  ['name' => __('Drink by'), 'type' => __('date'), 'sample' => '2035'],
                  ['name' => __('Bottles'), 'type' => '#', 'sample' => '6'],
              ],
          ],
          [
              'name' => __('Comics'),
              'fields' => [
                  ['name' => __('Issue'), 'type' => '#', 'sample' => '#300'],
                  ['name' => __('Publisher'), 'type' => __('txt'), 'sample' => 'Marvel'],
                  ['name' => __('Grade'), 'type' => __('txt'), 'sample' => 'CGC 9.8'],
                  ['name' => __('Key issue'), 'type' => '☑', 'sample' => 'true'],
                  ['name' => __('Writer'), 'type' => __('txt'), 'sample' => 'Michelinie'],
              ],
          ],
      ];
  @endphp

  <section class="mx-auto max-w-[1200px] px-5 pt-16 sm:px-8 sm:pt-24">
    <div
      class="grid grid-cols-1 items-center gap-8 rounded-xl bg-card p-6 sm:p-12 lg:grid-cols-2 lg:gap-12"
      x-data="{ active: 0, types: {{ Js::from($itemTypes) }} }"
    >
      <div>
        <p class="mb-3.5 text-[13px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ __('Adapt to your hobby') }}</p>
        <h2 class="text-[28px] leading-[1.15] font-semibold tracking-[-1px] text-ink sm:text-4xl">{{ __('Every collection is different. Your fields should be too.') }}</h2>
        <p class="mt-4.5 text-base leading-relaxed text-muted">
          {{ __("Define completely custom item types with your own fields, sections, and metadata. A book isn't a bottle of wine, so don't force them into the same form.") }}
        </p>

        <div class="mt-6 flex flex-wrap gap-2">
          @foreach ($itemTypes as $index => $type)
            <button
              type="button"
              @click="active = {{ $index }}"
              x-bind:class="active === {{ $index }} ? 'border-primary bg-primary text-on-primary' : 'border-hairline bg-canvas text-ink'"
              class="rounded-md border px-4 py-2 text-sm font-semibold transition-colors"
            >{{ $type['name'] }}</button>
          @endforeach
        </div>
      </div>

      <div class="rounded-lg border border-hairline bg-canvas p-6 shadow-[0_4px_12px_rgba(0,0,0,0.05)]">
        <div class="mb-4.5 flex items-center justify-between">
          <p class="text-[15px] font-semibold text-ink"><span x-text="types[active].name"></span> {{ __('type') }}</p>
          <span class="rounded-full bg-card px-2.5 py-1 text-xs font-medium text-body">{{ __('Custom') }}</span>
        </div>

        <div class="flex flex-col">
          <template x-for="field in types[active].fields" :key="field.name">
            <div class="flex items-center justify-between gap-3 border-b border-hairline-soft py-3">
              <div class="flex min-w-0 items-center gap-x-2.5">
                <span class="flex h-[26px] min-w-[26px] shrink-0 items-center justify-center rounded-sm bg-card px-1.5 font-mono text-[11px] text-muted" x-text="field.type"></span>
                <span class="truncate text-sm font-medium text-ink" x-text="field.name"></span>
              </div>
              <span class="shrink-0 font-mono text-xs text-muted-soft" x-text="field.sample"></span>
            </div>
          </template>

          <div class="flex items-center gap-x-2 pt-3.5 text-[13px] font-semibold text-body">
            <span class="flex h-[18px] w-[18px] items-center justify-center rounded-sm bg-card text-[13px] text-muted">+</span>
            {{ __('Add field') }}
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="mx-auto max-w-[1200px] px-5 pt-16 sm:px-8 sm:pt-24">
    <div class="grid grid-cols-1 items-center gap-8 lg:grid-cols-2 lg:gap-12">
      <div class="overflow-hidden rounded-lg border border-hairline">
        <div class="flex items-center justify-between gap-3 border-b border-hairline-soft px-5 py-4">
          <p class="text-[15px] font-semibold text-ink">{{ __('Amazing Spider-Man #1') }}</p>
          <span class="shrink-0 rounded-full bg-card px-2.5 py-1 text-xs font-medium text-body">{{ __(':count copies owned', ['count' => 3]) }}</span>
        </div>

        @foreach ([
            ['condition' => __('Near Mint'), 'location' => __('Display Case'), 'added' => __('Aug 2023'), 'value' => '$640', 'paid' => '$420', 'from' => '#fb923c', 'to' => '#fdba74'],
            ['condition' => __('Very Fine'), 'location' => __('Box A1'), 'added' => __('Jan 2023'), 'value' => '$180', 'paid' => '$120', 'from' => '#8b5cf6', 'to' => '#c4b5fd'],
            ['condition' => __('Good'), 'location' => __('Box B1'), 'added' => __('Jun 2023'), 'value' => '$95', 'paid' => '$60', 'from' => '#34d399', 'to' => '#6ee7b7'],
        ] as $copy)
          <div class="flex items-center gap-x-3.5 border-b border-hairline-soft px-5 py-3.5">
            <span class="h-13 w-10 shrink-0 rounded-sm" style="background: repeating-linear-gradient(135deg, {{ $copy['from'] }} 0px, {{ $copy['from'] }} 7px, {{ $copy['to'] }} 7px, {{ $copy['to'] }} 14px)"></span>
            <div class="min-w-0 flex-1">
              <p class="text-sm font-semibold text-ink">{{ $copy['condition'] }}</p>
              <p class="mt-0.5 truncate text-xs text-muted-soft">{{ __(':location · added :date', ['location' => $copy['location'], 'date' => $copy['added']]) }}</p>
            </div>
            <div class="shrink-0 text-right">
              <p class="text-sm font-semibold text-ink">{{ $copy['value'] }}</p>
              <p class="text-xs text-muted-soft">{{ __('paid :amount', ['amount' => $copy['paid']]) }}</p>
            </div>
          </div>
        @endforeach
      </div>

      <div>
        <p class="mb-3.5 text-[13px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ __('Track physical copies') }}</p>
        <h2 class="text-[28px] leading-[1.15] font-semibold tracking-[-1px] text-ink sm:text-4xl">{{ __('One catalog entry. Every copy you own.') }}</h2>
        <p class="mt-4.5 mb-6 text-base leading-relaxed text-muted">
          {{ __('Some things you own more than once. Track each copy independently: purchase date, price paid, estimated value, condition, location, and provenance.') }}
        </p>

        <div class="flex flex-wrap gap-2.5">
          @foreach ([__('Purchase date'), __('Price paid'), __('Estimated value'), __('Condition'), __('Location'), __('Provenance')] as $field)
            <span class="rounded-full bg-card px-3.5 py-1.5 text-sm font-medium text-body">{{ $field }}</span>
          @endforeach
        </div>
      </div>
    </div>
  </section>

  <section class="mx-auto max-w-[1200px] px-5 pt-16 text-center sm:px-8 sm:pt-24">
    <h2 class="text-[28px] leading-[1.1] font-semibold tracking-[-1px] text-ink sm:text-4xl lg:text-5xl lg:tracking-[-1.5px]">{{ __('One app for every collection.') }}</h2>
    <p class="mx-auto mt-4.5 max-w-[520px] text-[17px] text-muted">
      {{ __('Not just books. Not just comics. Not just wine. If you collect it, :name catalogs it.', ['name' => config('app.name')]) }}
    </p>

    @php
        $catalogs = [
            ['name' => __('Books'), 'spins' => false, 'svg' => '<rect x="22" y="60" width="52" height="13" rx="3" fill="#4f6bed"/><rect x="20" y="47" width="46" height="13" rx="3" fill="#e08a3c" transform="rotate(-4 43 53)"/><rect x="27" y="33" width="42" height="13" rx="3" fill="#3fae6b"/><rect x="22" y="63.5" width="6" height="6" rx="1.5" fill="#fff" opacity=".5"/>'],
            ['name' => __('Comics'), 'spins' => false, 'svg' => '<rect x="24" y="20" width="44" height="56" rx="5" fill="#e0574f"/><rect x="30" y="26" width="32" height="44" rx="3" fill="#fdf1d6"/><path d="M38 34l4 8 8 2-6 5 2 8-8-4-8 4 2-8-6-5 8-2z" fill="#4f6bed"/><rect x="34" y="58" width="24" height="7" rx="3.5" fill="#e08a3c"/>'],
            ['name' => __('Vinyl Records'), 'spins' => true, 'svg' => '<circle cx="48" cy="48" r="28" fill="#222"/><circle cx="48" cy="48" r="19" fill="none" stroke="#444" stroke-width="1.5"/><circle cx="48" cy="48" r="10" fill="#e08a3c"/><circle cx="48" cy="48" r="3" fill="#222"/>'],
            ['name' => __('CDs'), 'spins' => true, 'svg' => '<circle cx="48" cy="48" r="28" fill="#c7ccd6"/><circle cx="48" cy="48" r="28" fill="none" stroke="#4f6bed" stroke-width="2" opacity=".35"/><circle cx="48" cy="48" r="18" fill="none" stroke="#fff" stroke-width="2" opacity=".7"/><circle cx="48" cy="48" r="8" fill="#eef0f4"/><circle cx="48" cy="48" r="8" fill="none" stroke="#9aa1b0" stroke-width="1.5"/>'],
            ['name' => __('Movies'), 'spins' => true, 'svg' => '<circle cx="48" cy="48" r="28" fill="#2b2b2b"/><circle cx="48" cy="48" r="7" fill="#c7ccd6"/><circle cx="48" cy="30" r="6" fill="#c7ccd6"/><circle cx="48" cy="66" r="6" fill="#c7ccd6"/><circle cx="30" cy="48" r="6" fill="#c7ccd6"/><circle cx="66" cy="48" r="6" fill="#c7ccd6"/>'],
            ['name' => __('Video Games'), 'spins' => false, 'svg' => '<rect x="18" y="34" width="60" height="34" rx="17" fill="#3a3f4a"/><circle cx="34" cy="51" r="4" fill="#c7ccd6"/><rect x="30" y="47" width="8" height="8" rx="2" fill="#c7ccd6"/><rect x="26" y="47" width="16" height="8" rx="2" fill="#c7ccd6" opacity="0"/><circle cx="61" cy="45" r="3.5" fill="#e0574f"/><circle cx="68" cy="52" r="3.5" fill="#3fae6b"/><circle cx="54" cy="52" r="3.5" fill="#4f6bed"/><circle cx="61" cy="59" r="3.5" fill="#e08a3c"/>'],
            ['name' => __('Trading Cards'), 'spins' => false, 'svg' => '<rect x="26" y="22" width="38" height="52" rx="5" fill="#4f6bed" transform="rotate(-8 45 48)"/><rect x="34" y="24" width="38" height="52" rx="5" fill="#fdf1d6"/><path d="M53 34l4 9 9 1-7 6 2 9-8-5-8 5 2-9-7-6 9-1z" fill="#e08a3c"/>'],
            ['name' => __('Art'), 'spins' => false, 'svg' => '<rect x="20" y="24" width="56" height="48" rx="4" fill="#a8763c"/><rect x="27" y="31" width="42" height="34" rx="2" fill="#bfe3f2"/><circle cx="60" cy="41" r="5" fill="#e8c65a"/><path d="M27 65l14-18 10 11 8-8 10 15z" fill="#3fae6b"/>'],
            ['name' => __('Collectibles'), 'spins' => false, 'svg' => '<circle cx="48" cy="32" r="12" fill="#e08a3c"/><path d="M32 76c0-11 7-19 16-19s16 8 16 19z" fill="#4f6bed"/><rect x="40" y="70" width="16" height="6" rx="2" fill="#2b2b2b"/>'],
            ['name' => __('Memorabilia'), 'spins' => false, 'svg' => '<rect x="24" y="30" width="48" height="42" rx="4" fill="none" stroke="#9aa1b0" stroke-width="2.5"/><rect x="24" y="66" width="48" height="8" rx="2" fill="#2b2b2b"/><circle cx="48" cy="49" r="13" fill="#fdf1d6"/><path d="M40 44c4 3 12 3 16 0M40 54c4-3 12-3 16 0" stroke="#e0574f" stroke-width="1.5" fill="none"/>'],
            ['name' => __('Watches'), 'spins' => false, 'svg' => '<rect x="42" y="16" width="12" height="18" rx="3" fill="#3a3f4a"/><rect x="42" y="62" width="12" height="18" rx="3" fill="#3a3f4a"/><circle cx="48" cy="48" r="19" fill="#eef0f4" stroke="#3a3f4a" stroke-width="3"/><path d="M48 48V37M48 48l8 5" stroke="#2b2b2b" stroke-width="2.5" stroke-linecap="round"/><circle cx="48" cy="48" r="2" fill="#e0574f"/>'],
        ];
    @endphp

    <div class="mt-12 grid grid-cols-3 gap-4 sm:grid-cols-4 lg:grid-cols-6 lg:gap-5">
      @foreach ($catalogs as $catalog)
        <div class="group flex flex-col items-center gap-y-3.5">
          <div class="flex aspect-square w-full items-center justify-center overflow-hidden rounded-[20px] bg-card transition-shadow duration-200 group-hover:shadow-[0_8px_20px_rgba(17,17,17,0.08)]">
            <span @class([
                'flex h-[58%] w-[58%] items-center justify-center transition-transform duration-300 ease-out group-hover:-translate-y-1 group-hover:scale-110',
                'group-hover:rotate-[18deg]' => $catalog['spins'],
            ])>
              <svg viewBox="0 0 96 96" class="h-full w-full overflow-visible" fill="none" xmlns="http://www.w3.org/2000/svg">{!! $catalog['svg'] !!}</svg>
            </span>
          </div>
          <p class="text-base font-semibold tracking-[-0.2px] text-ink">{{ $catalog['name'] }}</p>
        </div>
      @endforeach

      <div class="flex flex-col items-center gap-y-3.5">
        <div class="flex aspect-square w-full items-center justify-center rounded-[20px] border border-dashed border-hairline bg-sidebar">
          <div class="flex gap-x-1.5">
            <span class="size-1.5 rounded-full bg-muted-soft"></span>
            <span class="size-1.5 rounded-full bg-muted-soft"></span>
            <span class="size-1.5 rounded-full bg-muted-soft"></span>
          </div>
        </div>
        <p class="text-base font-semibold tracking-[-0.2px] text-muted">{{ __('And more…') }}</p>
      </div>
    </div>
  </section>

  @if ($testimonialCount > 0)
  <section id="testimonials" class="mx-auto max-w-[1200px] scroll-mt-24 px-5 pt-16 sm:px-8 sm:pt-24">
    <div class="mx-auto max-w-[560px] text-center">
      <p class="mb-3.5 text-[13px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ __('Loved by collectors') }}</p>
      <h2 class="text-[28px] leading-[1.1] font-semibold tracking-[-1px] text-ink sm:text-4xl lg:text-5xl lg:tracking-[-1.5px]">{{ __('Straight from the wall.') }}</h2>
      <p class="mx-auto mt-4.5 max-w-[520px] text-[17px] text-muted">{{ __('Real notes from people who catalog their world in :name.', ['name' => config('app.name')]) }}</p>
    </div>

    <div class="mt-12 gap-5 sm:columns-2 lg:columns-3">
      @php
        $tilts = ['-rotate-2', 'rotate-1', '-rotate-1', 'rotate-2', '-rotate-1', 'rotate-1'];
      @endphp
      @foreach ($testimonials as $testimonial)
        <x-marketing.testimonial-note :testimonial="$testimonial" :tilt="$tilts[$loop->index % count($tilts)]" />
      @endforeach
    </div>

    <div class="mt-8 flex justify-center">
      <a href="{{ route('marketing.testimonials.index') }}" data-turbo="true" class="inline-flex h-12 items-center gap-x-2 rounded-md border border-hairline bg-canvas px-6 text-[15px] font-semibold text-ink transition-colors hover:bg-sidebar">
        {{ __('Read all :count testimonials', ['count' => number_format($testimonialCount)]) }}
        @svg('lucide-arrow-right', 'size-4')
      </a>
    </div>
  </section>
  @endif

  <section id="opensource" class="mx-auto max-w-[1200px] scroll-mt-24 px-5 pt-16 sm:px-8 sm:pt-24">
    <div class="rounded-xl bg-[#101010] p-6 text-white sm:p-12 lg:p-16">
      <div class="grid grid-cols-1 items-center gap-10 lg:grid-cols-2 lg:gap-14">
        <div>
          <div class="mb-5 inline-flex items-center gap-x-2 rounded-full bg-[#1a1a1a] px-3 py-1.5 text-xs font-semibold text-[#a1a1aa]">
            <span class="text-badge-emerald" aria-hidden="true">&bull;</span> {{ __('MIT LICENSED') }}
          </div>

          <h2 class="text-[28px] leading-[1.1] font-semibold tracking-[-1px] sm:text-4xl lg:text-5xl lg:tracking-[-1.5px]">{{ __('Open by design.') }}</h2>
          <p class="mt-5 mb-7 text-[17px] leading-relaxed text-[#a1a1aa]">
            {{ __(':name is released under the MIT License, the whole source, no strings. Read it, fork it, ship it commercially, run it on your own hardware. No vendor lock-in, ever.', ['name' => config('app.name')]) }}
          </p>

          <div class="mb-8 flex flex-col gap-y-3">
            @foreach ([__('No vendor lock-in'), __('Full source code'), __('Self-hosting, always free'), __('Commercial use allowed'), __('Community contributions')] as $point)
              <div class="flex items-center gap-x-3 text-[15px] text-[#e5e7eb]">
                <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-[#1a1a1a]">
                  <x-lucide-check class="h-[11px] w-[11px] text-badge-emerald" stroke-width="3" />
                </span>
                {{ $point }}
              </div>
            @endforeach
          </div>

          <a href="{{ config('marketing.github_url') }}" target="_blank" rel="noopener" class="inline-flex h-12 items-center gap-x-2.5 rounded-md bg-white px-5.5 text-[15px] font-semibold text-[#111111] transition-colors hover:bg-[#e5e7eb]">
            <x-lucide-github class="h-[18px] w-[18px]" />
            {{ __('Star on GitHub') }}
          </a>
        </div>

        <div class="flex flex-col gap-4">
          <div class="grid grid-cols-3 gap-4">
            @foreach ([['4.8k', __('Stars')], ['112', __('Contributors')], ['3.4k', __('Commits')]] as [$value, $label])
              <div class="rounded-lg bg-[#1a1a1a] p-5">
                <p class="text-2xl font-semibold tracking-[-1px] sm:text-3xl">{{ $value }}</p>
                <p class="mt-1 text-[13px] text-[#a1a1aa]">{{ $label }}</p>
              </div>
            @endforeach
          </div>

          <div class="overflow-hidden rounded-lg bg-[#1a1a1a]">
            <div class="flex items-center gap-x-2 border-b border-[#242424] px-4 py-3">
              <span class="h-2.5 w-2.5 rounded-full bg-[#333333]"></span>
              <span class="h-2.5 w-2.5 rounded-full bg-[#333333]"></span>
              <span class="h-2.5 w-2.5 rounded-full bg-[#333333]"></span>
              <span class="ml-2 font-mono text-xs text-[#6b7280]">{{ __('terminal, self-host') }}</span>
            </div>
            <div class="overflow-x-auto px-4.5 py-4.5 font-mono text-[13px] leading-relaxed whitespace-nowrap">
              <p class="text-[#6b7280]"># {{ __('up and running in one command') }}</p>
              <p><span class="text-badge-emerald">$</span> <span class="text-[#e5e7eb]">docker run -p 8000:8000 \</span></p>
              <p class="pl-4 text-[#e5e7eb]">-v {{ Str::lower(config('app.name')) }}:/data ghcr.io/djaiss/{{ Str::lower(config('app.name')) }}</p>
              <p class="mt-2 text-[#6b7280]">&check; {{ __('listening on http://localhost:8000') }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section id="privacy" class="mx-auto max-w-[1200px] scroll-mt-24 px-5 pt-16 sm:px-8 sm:pt-24">
    <div class="mb-12 max-w-[640px]">
      <p class="mb-3.5 text-[13px] font-semibold tracking-[0.6px] text-muted-soft uppercase">{{ __('Privacy') }}</p>
      <h2 class="text-[28px] leading-[1.1] font-semibold tracking-[-1px] text-ink sm:text-4xl lg:text-5xl lg:tracking-[-1.5px]">{{ __('Private by default. Not by policy.') }}</h2>
      <p class="mt-5 text-[17px] leading-relaxed text-muted">
        {{ __("Your catalog is nobody's business but yours. No cookie banners to click away, because there's nothing behind them.") }}
      </p>
    </div>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
      @foreach ([
          [
              'icon' => 'lock',
              'title' => __('Encrypted at rest'),
              'description' => __("Every catalog, item, and uploaded image is encrypted at rest. Even on our cloud, your data sits behind encryption you don't have to think about."),
          ],
          [
              'icon' => 'eye-off',
              'title' => __('Zero tracking'),
              'description' => __("No analytics, no trackers, no third-party pixels, no telemetry phoning home. We don't watch what you collect, and there's nothing to sell because we never collect it."),
          ],
      ] as $card)
        <div class="flex flex-col gap-y-5 rounded-lg bg-card p-8">
          <span class="flex h-10 w-10 items-center justify-center rounded-[10px] bg-primary">
            <x-dynamic-component :component="'lucide-' . $card['icon']" class="h-5 w-5 text-on-primary" />
          </span>
          <p class="text-[22px] font-semibold tracking-[-0.3px] text-ink">{{ $card['title'] }}</p>
          <p class="text-[15px] leading-relaxed text-muted">{{ $card['description'] }}</p>
        </div>
      @endforeach
    </div>
  </section>

  <section id="pricing" class="mx-auto max-w-[1200px] scroll-mt-24 px-5 pt-16 sm:px-8 sm:pt-24">
    <div class="mb-8 text-center">
      <div class="mb-5 inline-flex items-center gap-x-2 rounded-full bg-card py-1.5 pr-3.5 pl-1.5 text-[13px] font-semibold text-ink">
        <span class="rounded-full bg-primary px-2 py-[3px] text-[11px] text-on-primary">{{ __('NO SUBSCRIPTION') }}</span>
        {{ __('Pay once. Own it forever.') }}
      </div>
      <h2 class="text-[28px] leading-[1.1] font-semibold tracking-[-1px] text-ink sm:text-4xl lg:text-5xl lg:tracking-[-1.5px]">{{ __('One price. No subscriptions.') }}</h2>
      <p class="mx-auto mt-4.5 max-w-[520px] text-[17px] text-muted">
        {{ __('Self-host it for free with no limits at all, or try the managed cloud with ten items free and unlock it with one payment. No monthly bill, no renewal, ever. Your data is portable either way.') }}
      </p>
    </div>

    <div class="mx-auto grid max-w-[840px] grid-cols-1 gap-6 md:grid-cols-2">
      <div class="flex flex-col rounded-lg border border-hairline bg-canvas p-8">
        <p class="text-[22px] font-semibold tracking-[-0.3px] text-ink">{{ __('Self-host') }}</p>
        <p class="mt-3 text-[28px] font-semibold tracking-[-0.5px] text-ink">{{ __('Free') }}<span class="text-[15px] font-medium text-muted"> &middot; {{ __('forever') }}</span></p>

        <div class="my-6 flex flex-col gap-y-3">
          @foreach ([__('Free forever, with no item limit'), __('One command Docker deploy'), __('Full control of your data'), __('Unlimited customization')] as $feature)
            <div class="flex items-center gap-x-2.5 text-[15px] text-body">
              <x-lucide-check class="h-4 w-4 shrink-0 text-ink" stroke-width="2.4" />
              {{ $feature }}
            </div>
          @endforeach
        </div>

        <div class="flex-1"></div>
        <a href="{{ route('marketing.docs.api.index') }}" class="flex h-11 items-center justify-center rounded-md border border-hairline bg-canvas text-sm font-semibold text-ink transition-colors hover:bg-sidebar">{{ __('Read the docs') }}</a>
      </div>

      <div class="flex flex-col rounded-lg bg-[#101010] p-8 text-white">
        <div class="flex items-center gap-x-2.5">
          <p class="text-[22px] font-semibold tracking-[-0.3px]">{{ __('Cloud') }}</p>
          <span class="rounded-full bg-[#1a1a1a] px-2 py-[3px] text-[11px] font-semibold text-badge-emerald">{{ __('PAY ONCE') }}</span>
        </div>
        <div class="mt-3 flex flex-wrap items-baseline gap-x-2">
          <p class="text-[28px] font-semibold tracking-[-0.5px]">$49<span class="text-[15px] font-medium text-[#a1a1aa]"> {{ __('once') }}</span></p>
          <span class="text-[13px] text-[#6b7280] line-through">{{ __(':price/mo forever', ['price' => '$6']) }}</span>
        </div>

        <div class="my-6 flex flex-col gap-y-3">
          @foreach ([__('Ten items free, then one payment'), __('One payment, no renewals'), __('Lifetime managed hosting'), __('Automatic updates and backups')] as $feature)
            <div class="flex items-center gap-x-2.5 text-[15px] text-[#e5e7eb]">
              <x-lucide-check class="h-4 w-4 shrink-0 text-badge-emerald" stroke-width="2.4" />
              {{ $feature }}
            </div>
          @endforeach
        </div>

        <div class="flex-1"></div>
        <a href="{{ route('register') }}" class="flex h-11 items-center justify-center rounded-md bg-white text-sm font-semibold text-[#111111] transition-colors hover:bg-[#e5e7eb]">{{ __('Buy once, :price', ['price' => '$49']) }}</a>
      </div>
    </div>
  </section>

  <section id="faq" class="mx-auto max-w-[760px] scroll-mt-24 px-5 pt-16 sm:px-8 sm:pt-24">
    <h2 class="mb-10 text-center text-[28px] leading-[1.15] font-semibold tracking-[-1px] text-ink sm:text-4xl">{{ __('Questions, answered.') }}</h2>

    <div class="flex flex-col border-t border-hairline" x-data="{ open: null }">
      @foreach ([
          [
              'question' => __('Can I really self-host it?'),
              'answer' => __(':name ships as a single container. One docker run and you have the full application on your own hardware, with your data on a volume you control.', ['name' => config('app.name')]),
          ],
          [
              'question' => __('Is :name free?', ['name' => config('app.name')]),
              'answer' => __('Self-hosting is free forever under the MIT License, with no item limit at all. On the managed cloud an account holds ten items for free, and a single payment unlocks it for good. There is no subscription either way.'),
          ],
          [
              'question' => __('Who owns my data?'),
              'answer' => __('You do. There is no proprietary format and no lock-in. Everything is stored in an open schema you can inspect, back up, and take with you.'),
          ],
          [
              'question' => __('Can I export everything?'),
              'answer' => __('Not yet, and we would rather say so here. Collection type definitions export and import as JSON, and open loans download as CSV. If you self-host, a backup of the database and the storage volume holds everything.'),
          ],
          [
              'question' => __('Is there an API?'),
              'answer' => __('Yes. A documented REST API covers every collection, item, and custom field, so you can script imports, sync tools, or build your own front end.'),
          ],
          [
              'question' => __('Can I contribute?'),
              'answer' => __('Absolutely, that is the point of MIT. Open an issue, send a pull request, or fork it entirely. The roadmap is public and community driven.'),
          ],
      ] as $index => $faq)
        <div class="border-b border-hairline">
          <button
            type="button"
            @click="open = open === {{ $index }} ? null : {{ $index }}"
            x-bind:aria-expanded="open === {{ $index }} ? 'true' : 'false'"
            class="flex w-full items-center justify-between gap-4 py-5.5 text-left"
          >
            <span class="text-[17px] font-semibold text-ink">{{ $faq['question'] }}</span>
            <span
              class="flex h-6 w-6 shrink-0 items-center justify-center text-xl text-muted transition-transform duration-200"
              x-bind:class="open === {{ $index }} ? 'rotate-45' : ''"
              aria-hidden="true"
            >+</span>
          </button>

          <div x-show="open === {{ $index }}" x-cloak>
            <p class="pr-11 pb-6 pl-1 text-[15px] leading-relaxed text-muted">{{ $faq['answer'] }}</p>
          </div>
        </div>
      @endforeach
    </div>

    <p class="mt-8 text-center text-[15px] text-muted">
      <a href="{{ route('marketing.faq.index') }}" data-turbo="true" class="font-semibold text-ink underline underline-offset-4 hover:text-body">{{ __('Read the full FAQ') }}</a>
    </p>
  </section>

  <section class="mx-auto max-w-[1200px] px-5 pt-16 sm:px-8 sm:pt-24">
    <div class="rounded-xl bg-card px-6 py-14 text-center sm:px-12 sm:py-18">
      <h2 class="mx-auto max-w-[620px] text-[28px] leading-[1.15] font-semibold tracking-[-1px] text-balance text-ink sm:text-[40px]">
        {{ __('Your collection deserves better than spreadsheets.') }}
      </h2>
      <p class="mx-auto mt-4.5 max-w-[460px] text-[17px] text-muted">
        {{ __('Open source, MIT licensed, and yours to keep. Start in the cloud or self-host in a single command.') }}
      </p>

      <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
        <a href="{{ route('register') }}" class="flex h-12 items-center justify-center rounded-md bg-primary px-6 text-[15px] font-semibold text-on-primary transition-opacity hover:opacity-90">{{ __('Get started') }}</a>

        <a href="{{ config('marketing.github_url') }}" target="_blank" rel="noopener" class="flex h-12 items-center justify-center gap-x-2 rounded-md border border-hairline bg-canvas px-5.5 text-[15px] font-semibold text-ink transition-colors hover:bg-sidebar">
          <x-lucide-github class="h-[18px] w-[18px]" />
          {{ __('View on GitHub') }}
        </a>
      </div>
    </div>
  </section>
</x-marketing-layout>
