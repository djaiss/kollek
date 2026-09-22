@use('App\Services\DocumentationPortal')

<x-guest-layout>
  <div class="grid min-h-screen w-screen grid-cols-1 lg:grid-cols-2">
    <!-- Left side -->
    <div class="mx-auto flex w-full max-w-3xl flex-1 flex-col justify-center px-5 py-10 sm:px-30">
      <div class="w-full space-y-8">
        @if (config('app.show_marketing_site'))
          <p class="group mb-10 flex items-center gap-x-1 text-sm text-muted">
            <x-lucide-arrow-left class="h-4 w-4 transition-transform duration-150 group-hover:-translate-x-1" />
            <x-link href="{{ route('marketing.index') }}" class="group-hover:underline">{{ __('Back to the marketing website') }}</x-link>
          </p>
        @endif

        <!-- Session status -->
        <x-error :messages="session('status')" />

        <!-- Title -->
        <div>
          <div class="mb-2 flex items-center gap-x-2">
            <a href="" class="group flex items-center gap-x-2 transition-transform ease-in-out">
              <div class="flex h-7 w-7 items-center justify-center transition-all duration-400 group-hover:-translate-y-0.5 group-hover:-rotate-3">
                <x-logo size="25" />
              </div>
            </a>
            <h1 class="text-2xl font-semibold text-ink">
              {{ __('Sign up for an account') }}
            </h1>
          </div>
          <p class="text-sm text-muted">{{ __('You will be the administrator of this account.') }}</p>
        </div>

        <!-- Registration form -->
        <x-box>
          <x-form method="post" :action="route('register')" class="space-y-4">
            <!-- Name -->
            <div class="flex flex-col gap-2 sm:flex-row sm:gap-4">
              <div class="w-full">
                <x-input type="text" id="first_name" :value="old('first_name')" :label="__('First name')" required placeholder="John" :error="$errors->get('first_name')" :passManagerDisabled="false" autocomplete="given-name" />
              </div>

              <div class="w-full">
                <x-input type="text" id="last_name" :value="old('last_name')" :label="__('Last name')" required placeholder="Doe" :error="$errors->get('last_name')" :passManagerDisabled="false" autocomplete="family-name" />
              </div>
            </div>

            <!-- Email address -->
            <x-input type="email" id="email" :value="old('email')" :label="__('Email address')" required placeholder="john@doe.com" :error="$errors->get('email')" :passManagerDisabled="false" autocomplete="username" :help="__('We will never, ever send you marketing emails.')" />

            <div class="flex flex-col gap-2 sm:flex-row sm:gap-4">
              <div class="w-full">
                <x-input type="password" id="password" :label="__('Password')" :help="__('Minimum 8 characters.')" passwordrules="minlength: 8" required :error="$errors->get('password')" :passManagerDisabled="false" autocomplete="new-password" />
              </div>

              <div class="w-full">
                <x-input type="password" id="password_confirmation" :label="__('Confirm password')" passwordrules="minlength: 8" required :error="$errors->get('password_confirmation')" :passManagerDisabled="false" autocomplete="new-password" />
              </div>
            </div>

            @php
              $urlLocale = app(DocumentationPortal::class)->urlLocaleFor(app()->getLocale());
              $linkClasses = 'font-medium text-ink underline underline-offset-2';
              $terms = '<a href="'.route('marketing.terms.index', ['locale' => $urlLocale]).'" target="_blank" rel="noopener" class="'.$linkClasses.'">'.__('terms of use').'</a>';
              $privacy = '<a href="'.route('marketing.privacy.index', ['locale' => $urlLocale]).'" target="_blank" rel="noopener" class="'.$linkClasses.'">'.__('privacy policy').'</a>';
            @endphp

            <div class="space-y-1">
              <label for="terms" class="flex items-start gap-x-2 text-sm text-body">
                <input type="checkbox" id="terms" name="terms" value="1" required @checked(old('terms')) class="mt-0.5 rounded-sm border-hairline bg-input text-primary shadow-xs focus:ring-primary/30" />
                <span>{!! __('I agree with the :terms and the :privacy.', ['terms' => $terms, 'privacy' => $privacy]) !!}</span>
              </label>

              <x-error :messages="$errors->get('terms')" />
            </div>

            <x-turnstile data-size="flexible" />

            <div class="flex items-center justify-between">
              <x-button class="w-full">{{ __('Next step: validate your email address') }}</x-button>
            </div>
          </x-form>
        </x-box>

        <!-- Register link -->
        <x-box class="text-center text-sm">
          {{ __('Already have an account?') }}
          <x-link :href="'login'" class="ml-1">
            {{ __('Sign in instead') }}
          </x-link>
        </x-box>

        <!-- Language switcher -->
        @include('partials.languageSwitcher')

        <ul class="text-xs text-muted">
          <li>&copy; {{ config('app.name') }} {{ now()->format('Y') }}</li>
        </ul>
      </div>
    </div>

    <!-- Right side -->
    @include('partials.quotes', ['quote' => $quote])
  </div>
</x-guest-layout>
