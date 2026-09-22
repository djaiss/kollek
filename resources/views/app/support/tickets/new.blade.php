<x-app-layout>
  <x-slot:title>
    {{ __('New conversation') }}
  </x-slot>

  <div class="px-6 py-8 lg:px-12 lg:py-10">
    <div
      class="mx-auto w-full max-w-2xl"
      x-data="{ category: @js(old('category', '')), subject: @js(old('subject', '')), message: @js(old('message', '')) }"
    >
      <h1 class="text-3xl font-bold tracking-tight text-ink">{{ __('Hi!') }} 👋</h1>
      <div class="mt-5 space-y-3 text-[15px] leading-relaxed text-body">
        <p>{{ __('Whether you\'ve found a bug, have an idea, or simply need a hand, you\'ve come to the right place.') }}</p>
        <p>{{ __('Every message is read by a real person.') }}</p>
        <p class="font-semibold text-ink">{{ __('Actually, by me.') }}</p>
      </div>

      <div class="mt-6 flex items-center gap-3.5 rounded-xl border border-hairline bg-card p-4">
        <x-image src="{{ asset('images/regis.png') }}" srcset="{{ asset('images/regis.png') }}, {{ asset('images/regis@2x.png') }} 2x" height="44" width="44" alt="Regis Freyd" class="size-11 shrink-0 rounded-full" />

        <div class="min-w-0">
          <p class="text-[15px] font-semibold text-ink">{{ __('Regis') }}</p>
          <p class="mt-0.5 text-[13px] text-muted-soft">{{ __('Creator of :name. I\'ll personally reply as soon as I can.', ['name' => config('app.name')]) }}</p>
        </div>
      </div>

      <div class="mt-10 border-t border-hairline pt-8">
        <h2 class="text-lg font-semibold text-ink">{{ __('What do you need help with?') }}</h2>
        <p class="mt-1 text-sm text-muted">{{ __('This helps us understand what you\'re looking for.') }}</p>

        <div class="mt-5 grid gap-3 sm:grid-cols-2">
          @foreach ($categories as $category)
            <button
              type="button"
              @click="category = '{{ $category->value }}'"
              data-test="category-{{ $category->value }}"
              class="flex cursor-pointer items-center gap-3 rounded-xl border p-4 text-left transition-colors"
              :class="category === '{{ $category->value }}' ? 'border-ink bg-card' : 'border-hairline bg-canvas hover:bg-card'"
            >
              <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-card text-muted">
                @svg('lucide-'.$category->icon(), 'size-[18px]')
              </span>
              <span class="flex-1 text-sm font-medium text-ink">{{ $category->prompt() }}</span>
              <x-lucide-check class="size-4 shrink-0 text-ink" x-show="category === '{{ $category->value }}'" x-cloak />
            </button>
          @endforeach
        </div>
      </div>

      <div x-show="category !== ''" x-cloak class="mt-10 border-t border-hairline pt-8">
        @foreach ($categories as $category)
          <div x-show="category === '{{ $category->value }}'" x-cloak class="mb-8">
            <h2 class="text-xl font-semibold text-ink">{{ $category->heading() }}</h2>
            <div class="mt-3 space-y-3 text-[15px] leading-relaxed text-body">
              @foreach ($category->paragraphs() as $paragraph)
                <p>{{ $paragraph }}</p>
              @endforeach
            </div>
          </div>
        @endforeach

        <x-form method="post" action="{{ route('support.tickets.create') }}" class="space-y-6">
          <input type="hidden" name="category" :value="category" />

          <div>
            <label for="subject" class="block text-sm font-semibold text-ink">{{ __('Subject') }}</label>
            <p class="mt-1 mb-3 text-sm text-muted">{{ __('A short summary so you can spot this conversation later.') }}</p>
            <input
              id="subject"
              name="subject"
              type="text"
              x-model="subject"
              maxlength="255"
              placeholder="{{ __('e.g. Import keeps timing out') }}"
              value="{{ old('subject') }}"
              class="block w-full rounded-md border border-hairline bg-input px-3 py-2.5 text-sm text-ink placeholder-muted-soft shadow-xs aria-invalid:border-error dark:shadow-none"
            />
            <x-error :messages="$errors->get('subject')" />
          </div>

          <div>
            <label for="message" class="block text-sm font-semibold text-ink">{{ __('What\'s your question, comment, or issue?') }}</label>
            <p class="mt-1 mb-3 text-sm text-muted">{{ __('Share as much detail as you can. The more context you provide, the easier it is to help.') }}</p>
            <textarea
              id="message"
              name="message"
              x-model="message"
              rows="6"
              placeholder="{{ __('Tell us what\'s going on…') }}"
              class="block w-full resize-y rounded-md border border-hairline bg-input px-3 py-2.5 text-sm leading-relaxed text-ink placeholder-muted-soft shadow-xs aria-invalid:border-error dark:shadow-none"
            >{{ old('message') }}</textarea>
            <x-error :messages="$errors->get('message')" />
          </div>

          <div class="flex items-center gap-4">
            <x-button type="submit" data-test="send-message-button" x-bind:disabled="! subject.trim() || ! message.trim()">
              <x-slot:icon>
                <x-lucide-send class="size-4" />
              </x-slot>
              {{ __('Send message') }}
            </x-button>
            <span class="text-xs text-muted-soft">{{ __('We\'ll reply as soon as we can.') }}</span>
          </div>
        </x-form>
      </div>
    </div>
  </div>
</x-app-layout>
