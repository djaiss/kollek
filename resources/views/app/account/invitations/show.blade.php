<x-guest-layout>
  <div class="flex min-h-screen w-screen items-center justify-center px-5 py-10">
    <div class="w-full max-w-md space-y-8">
      <x-box :title="__('Account invitation')">
        @if (! $invitation->isPending())
          <p class="text-sm text-body">
            {{ __('This invitation is no longer valid.') }}
          </p>
        @else
          <p class="mb-6 text-sm text-body">
            {{ __('You have been invited to join :account.', ['account' => $invitation->account->name]) }}
          </p>

          @auth
            <x-form method="post" :action="route('invitations.create', $invitation->token)">
              <x-button type="submit">{{ __('Accept invitation') }}</x-button>
            </x-form>
          @else
            @if ($hasAccount)
              <p class="mb-4 text-sm text-body">
                {{ __('You already have an account. Please log in to accept this invitation.') }}
              </p>
              <x-button href="{{ route('login') }}">{{ __('Log in') }}</x-button>
            @else
              <x-form method="post" :action="route('invitations.create', $invitation->token)" class="space-y-4">
                <x-input id="email" name="email" :label="__('Email')" :value="$invitation->email" disabled :passManagerDisabled="false" autocomplete="username" />
                <x-input id="first_name" name="first_name" :label="__('First name')" :error="$errors->get('first_name')" required autofocus :passManagerDisabled="false" autocomplete="given-name" />
                <x-input id="last_name" name="last_name" :label="__('Last name')" :error="$errors->get('last_name')" required :passManagerDisabled="false" autocomplete="family-name" />
                <x-input id="password" name="password" type="password" :label="__('Password')" :help="__('Minimum 8 characters.')" passwordrules="minlength: 8" :error="$errors->get('password')" required :passManagerDisabled="false" autocomplete="new-password" />
                <x-input id="password_confirmation" name="password_confirmation" type="password" :label="__('Confirm password')" passwordrules="minlength: 8" required :passManagerDisabled="false" autocomplete="new-password" />

                <x-button type="submit">{{ __('Create account and join') }}</x-button>
              </x-form>
            @endif
          @endauth
        @endif
      </x-box>
    </div>
  </div>
</x-guest-layout>
