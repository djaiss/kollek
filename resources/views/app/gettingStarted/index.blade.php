<x-app-layout>
  <x-slot:title>
    {{ __('Getting started') }}
  </x-slot>

  @php
    // The wording of each step lives here rather than in the service, so the translation
    // extractor can find it. The service only says which of them are behind the user.
    $copy = [
        'types' => ['title' => __('Configure collection types'), 'description' => __('Define what you track: comics, vinyl, LEGO, whatever it is.'), 'action' => __('Set up')],
        'tags' => ['title' => __('Configure tags'), 'description' => __('Flexible labels to slice across your items.'), 'action' => __('Set up')],
        'members' => ['title' => __('Add other members'), 'description' => __('Invite people to view or manage the account with you.'), 'action' => __('Invite')],
        'locations' => ['title' => __('Add locations'), 'description' => __('Where things live: shelves, boxes, rooms.'), 'action' => __('Set up')],
        'collection' => ['title' => __('Add your first collection'), 'description' => __('The fun part. Start cataloguing.'), 'action' => __('Create')],
    ];
  @endphp

  <div class="relative flex flex-col items-center overflow-hidden px-6 py-14 lg:px-10">
    {{-- Soft accent wash behind the greeting, purely decorative. --}}
    <div aria-hidden="true" class="pointer-events-none absolute -top-40 left-1/2 h-[420px] w-[640px] -translate-x-1/2 rounded-full bg-brand opacity-[0.09] blur-[120px] dark:opacity-[0.16]"></div>

    <div class="relative flex w-full max-w-[620px] flex-col items-center text-center">
      <p class="mb-3.5 text-[13px] font-semibold tracking-[1px] text-brand uppercase">{{ __('Welcome to :name', ['name' => config('app.name')]) }}</p>

      <h1 class="text-[34px] leading-[1.15] font-semibold tracking-tight text-ink">{{ __('Hi :name, so glad you are here.', ['name' => $firstName]) }}</h1>

      <p class="mt-3 mb-10 max-w-[500px] text-[16px] leading-relaxed text-muted">
        {{ __('Your shelf is empty for now, which means everything is still ahead of you. Let us give the things you care about a proper home.') }}
      </p>

      {{-- A note from the founder --}}
      <div class="relative w-full rounded-[18px] border border-hairline bg-canvas px-10 pt-9.5 pb-8 text-left shadow-[0_18px_50px_rgba(17,17,17,0.07)] dark:shadow-[0_18px_50px_rgba(0,0,0,0.5)]">

        <div class="flex flex-col gap-4 text-[16px] leading-[1.72] text-body">
          <p>{{ __('Thank you, genuinely. Trusting a small tool to hold the things you have spent years hunting down, saving up for, and caring about is a big deal, and I do not take it lightly.') }}</p>
          <p>{{ __('I built :name because I am a collector too, and I wanted somewhere that treated a collection like it matters, not just rows in a spreadsheet. Everything here exists to make cataloguing, organizing and revisiting your stuff feel effortless and even a little joyful.', ['name' => config('app.name')]) }}</p>
          <p>{{ __('Whatever you collect, comics, vinyl, LEGO, books, the oddly specific thing only you love, this is your space now. If anything ever feels off, or you wish it did something it does not, just tell me. I am listening.') }}</p>
        </div>

        <div class="my-6 h-px bg-hairline"></div>

        <div class="flex items-center gap-3.5">
          <x-image src="{{ asset('images/regis.png') }}" srcset="{{ asset('images/regis.png') }}, {{ asset('images/regis@2x.png') }} 2x" height="52" width="52" alt="Regis Freyd" class="rounded-full" />

          <div class="min-w-0 flex-1">
            <p class="text-[15px] font-semibold text-ink">{{ __('Regis') }}</p>
            <p class="mt-0.5 text-[12.5px] text-muted-soft">{{ __('Founder and fellow collector, :name', ['name' => config('app.name')]) }}</p>
          </div>

          <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-brand/10">
            @svg('lucide-heart', 'size-[19px] text-brand')
          </span>
        </div>
      </div>

      {{-- Checklist. Every row reads its state from the account, so it is a report, not a to-do
           list you tick yourself. --}}
      <div class="mt-8.5 w-full text-left">
        <div class="mx-1 mb-3 flex items-baseline justify-between gap-3">
          <p class="text-[13px] font-semibold tracking-wide text-muted-soft uppercase">{{ __('Getting started') }}</p>
          <p class="text-[13px] text-muted-soft" data-test="getting-started-progress">{{ __(':done of :total done', ['done' => $doneCount, 'total' => $steps->count()]) }}</p>
        </div>

        <div class="overflow-hidden rounded-[14px] border border-hairline bg-canvas">
          @foreach ($steps as $step)
            <a
              href="{{ $step['route'] }}"
              data-turbo="true"
              class="flex items-center gap-3.5 px-4.5 py-4 transition-colors hover:bg-card @unless ($loop->last) border-b border-hairline @endunless"
              data-test="getting-started-step-{{ $step['key'] }}"
            >
              <span class="flex size-5.5 shrink-0 items-center justify-center rounded-full border-[1.5px] {{ $step['done'] ? 'border-brand bg-brand' : 'border-muted-soft' }}">
                @if ($step['done'])
                  @svg('lucide-check', 'size-3 text-white')
                @endif
              </span>

              <span class="min-w-0 flex-1">
                <span class="block text-[14.5px] font-semibold {{ $step['done'] ? 'text-muted-soft line-through' : 'text-ink' }}">{{ $copy[$step['key']]['title'] }}</span>
                <span class="mt-0.5 block text-[12.5px] text-muted-soft">{{ $copy[$step['key']]['description'] }}</span>
              </span>

              <span class="shrink-0 text-xs font-semibold text-brand">{{ $step['done'] ? __('Done') : $copy[$step['key']]['action'] }}</span>
              <span class="shrink-0 text-[15px] text-muted-soft" aria-hidden="true">&rarr;</span>
            </a>
          @endforeach
        </div>
      </div>

      <div class="mt-5.5 flex flex-col items-center gap-2.5">
        @if ($canDismiss)
          <x-form
            method="delete"
            :action="route('gettingStarted.destroy')"
            x-on:ajax:before="confirm('{{ __('Hide the getting started screen for everyone in this account? You can bring it back from your account settings.') }}') || $event.preventDefault()"
          >
            <button type="submit" class="flex h-11 cursor-pointer items-center gap-2 rounded-[10px] border border-hairline px-5 text-sm font-semibold text-muted transition-colors hover:text-ink" data-test="dismiss-getting-started">
              {{ __('Skip for now, take me to my dashboard') }}
              <span aria-hidden="true">&rarr;</span>
            </button>
          </x-form>

          <p class="text-[12.5px] text-muted-soft">{{ __('You can reopen this checklist anytime from your account settings.') }}</p>
        @else
          <a href="{{ route('dashboard.index') }}" data-turbo="true" class="flex h-11 items-center gap-2 rounded-[10px] border border-hairline px-5 text-sm font-semibold text-muted transition-colors hover:text-ink">
            {{ __('Take me to my dashboard') }}
            <span aria-hidden="true">&rarr;</span>
          </a>

          <p class="text-[12.5px] text-muted-soft">{{ __('Only an owner can hide this screen for the account.') }}</p>
        @endif
      </div>
    </div>
  </div>
</x-app-layout>
