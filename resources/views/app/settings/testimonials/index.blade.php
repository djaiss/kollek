<x-app-layout>
  <x-slot:title>
    {{ __('Testimonials') }}
  </x-slot>

  @php
    $isEditable = $testimonial === null || $testimonial->status->isEditable();
  @endphp

  <div class="px-6 py-8 lg:px-12 lg:py-10">
    <div class="mx-auto w-full max-w-2xl space-y-8">
      <div class="space-y-3">
        <h1 class="text-[26px] leading-tight font-semibold tracking-tight text-ink">{{ __('Has :name been good to your collection?', ['name' => config('app.name')]) }}</h1>
        <p class="text-[15px] leading-relaxed text-muted">
          {{ __('If :name has made your collecting life a little easier, you would help us more than you know by sharing a few words. A note from a real collector is worth more to us than any ad we could buy.', ['name' => config('app.name')]) }}
        </p>
        <p class="text-[15px] leading-relaxed text-muted">
          {{ __('If we publish it, your words show up on the :name homepage for other collectors to see, right next to your name. No pressure, and thank you either way. 🦫', ['name' => config('app.name')]) }}
        </p>
        <p class="flex items-start gap-2 text-[15px] leading-relaxed text-muted">
          @svg('lucide-shield-check', 'mt-0.5 size-4 shrink-0 text-muted')
          <span>{{ __('It is always yours to take back: you can revoke your testimonial at any time, even after it goes live, and it comes off the site right away.') }}</span>
        </p>
      </div>

      @if ($isEditable)
        <x-box :title="__('Say a few words')">
          <x-form method="post" :action="route('settings.testimonials.create')" class="space-y-5">
            <x-input
              id="name"
              :label="__('Your name')"
              :value="old('name', $testimonial?->name)"
              :error="$errors->get('name')"
              required
              :placeholder="__('e.g. Marion Delacroix')"
            />

            <div class="space-y-2">
              <x-input
                id="link"
                type="url"
                :label="__('Link')"
                helpId="settings.testimonials.link"
                :value="old('link', $testimonial?->link)"
                :error="$errors->get('link')"
                placeholder="https://your-site.com"
              />
              <p class="text-xs text-muted">{{ __('If you add a link, your name becomes clickable on the marketing site.') }}</p>
            </div>

            <div class="space-y-2">
              <div class="flex items-center space-x-2">
                <x-label for="body" :value="__('In your own words')" />
                <x-help id="settings.testimonials.body" />
              </div>
              <x-textarea
                id="body"
                rows="5"
                :value="old('body', $testimonial?->body)"
                :error="$errors->get('body')"
                required
                :placeholder="__('What do you use :name for, and what do you love about it? Speak like you would tell a friend.', ['name' => config('app.name')])"
              />
            </div>

            <div class="flex items-start gap-2.5 rounded-md border border-hairline bg-sidebar px-3.5 py-3">
              @svg('lucide-globe', 'mt-0.5 size-4 shrink-0 text-muted')
              <p class="text-[13px] leading-relaxed text-muted">
                {{ __('Please write your testimonial in English. The marketing site is English-only, so testimonials in other languages cannot be published.') }}
              </p>
            </div>

            <div class="flex items-center justify-end">
              <x-button type="submit">{{ __('Share it with us') }}</x-button>
            </div>
          </x-form>
        </x-box>
      @elseif ($testimonial->status === \App\Enums\TestimonialStatus::InReview)
        <div class="space-y-5">
          <div class="flex items-start gap-3 rounded-lg border border-hairline bg-badge-orange/10 px-4 py-4">
            @svg('lucide-clock', 'mt-0.5 size-5 shrink-0 text-badge-orange')
            <div>
              <p class="text-[15px] font-semibold text-ink">{{ __('Thanks so much for your testimonial!') }}</p>
              <p class="mt-0.5 text-sm text-muted">{{ __('It is currently in review and will be live soon.') }}</p>
            </div>
          </div>

          <div class="space-y-2">
            <p class="text-xs font-semibold tracking-wide text-muted-soft uppercase">{{ __('What you submitted') }}</p>
            <x-testimonial-card :testimonial="$testimonial" />
          </div>

          <x-testimonial-withdraw />
        </div>
      @else
        <div class="space-y-5">
          <div class="flex items-start gap-3 rounded-lg border border-hairline bg-success/10 px-4 py-4">
            @svg('lucide-badge-check', 'mt-0.5 size-5 shrink-0 text-success')
            <div>
              <p class="text-[15px] font-semibold text-ink">{{ __('Thanks so much for your testimonial!') }}</p>
              <p class="mt-0.5 text-sm text-muted">{{ __('It is now live on the :name homepage.', ['name' => config('app.name')]) }}</p>
            </div>
          </div>

          <x-testimonial-card :testimonial="$testimonial" />

          @if (config('marketing.show'))
            <div>
              <a href="{{ route('marketing.testimonials.index', ['locale' => 'en']) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-md border border-hairline bg-canvas px-4 py-2 text-sm font-semibold text-ink transition-colors hover:bg-sidebar">
                {{ __('View on marketing site') }}
                @svg('lucide-external-link', 'size-3.5')
              </a>
            </div>
          @endif

          <x-testimonial-withdraw />
        </div>
      @endif
    </div>
  </div>
</x-app-layout>
