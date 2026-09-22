{{-- The Cloudflare Turnstile widget, shown only on an instance that has turned it on. --}}
@if (config('turnstile.enabled'))
  @once
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
  @endonce

  <div>
    <div {{ $attributes->merge(['class' => 'cf-turnstile']) }} data-sitekey="{{ config('turnstile.site_key') }}" data-theme="auto"></div>

    <x-error :messages="$errors->get('cf-turnstile-response')" class="mt-2" />
  </div>
@endif
