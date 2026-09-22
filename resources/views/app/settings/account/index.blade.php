<x-app-layout>
  <x-slot:title>
    {{ __('Account settings') }}
  </x-slot>

  <div class="px-6 py-8 lg:px-12 lg:py-10">
    <div class="mx-auto w-full max-w-3xl space-y-8">
      <div id="account-settings-header">
        <h1 class="text-[22px] font-semibold tracking-tight text-ink">{{ __('Account settings') }}</h1>
        <p class="mt-1 text-sm text-muted">{{ __('Manage :account and who can access it.', ['account' => $account->name]) }}</p>
      </div>

      <x-box :title="__('General')">
        <div class="grid grid-cols-1 gap-8 sm:grid-cols-2">
          <div class="space-y-2">
            <p class="text-sm text-muted">{{ __('The account name identifies this workspace to everyone you share it with. Renaming it is safe and takes effect immediately.') }}</p>
            <p class="text-sm text-muted">{{ __('The default currency is used for valuation totals across your collections. Changing it relabels new totals, it does not convert the values you have already recorded.') }}</p>
          </div>

          <x-form id="account-general-form" x-target="account-general-form account-settings-header" method="put" :action="route('settings.update')" class="space-y-4">
            <x-input id="name" name="name" :label="__('Account name')" helpId="settings.general.account_name" :value="$account->name" :error="$errors->get('name')" required />
            <x-select id="currency_code" :label="__('Default currency')" helpId="settings.general.currency" :options="$currencies" :selected="$account->currency_code" :error="$errors->get('currency_code')" required />
            <div class="flex items-center justify-end">
              <x-button type="submit">{{ __('Save') }}</x-button>
            </div>
          </x-form>
        </div>
      </x-box>

      <x-box :title="__('Getting started screen')" helpId="settings.getting_started">
        <div class="grid grid-cols-1 gap-8 sm:grid-cols-2">
          <div class="space-y-2">
            <p class="text-sm text-muted">{{ __('The welcome screen and setup checklist new accounts land on. Turn it back on to bring it into the sidebar, for everyone in the account.') }}</p>
          </div>

          <x-form id="getting-started-form" x-target="getting-started-form" method="put" :action="route('settings.gettingStarted.update')" class="space-y-4">
            <x-select id="show_getting_started" :label="__('Show the getting started screen')" :options="[
              'yes' => __('Yes'),
              'no' => __('No'),
            ]" :selected="old('show_getting_started', $account->show_getting_started ? 'yes' : 'no')" :error="$errors->get('show_getting_started')" required />

            <div class="flex items-center justify-end">
              <x-button type="submit" data-test="save-getting-started">{{ __('Save') }}</x-button>
            </div>
          </x-form>
        </div>
      </x-box>

      <x-box :title="__('Delete account')" helpId="settings.delete_account">
        <p class="mb-4 text-sm text-muted">{{ __('Permanently delete this account, all of its members, and everything it contains. This cannot be undone.') }}</p>
        <x-form method="delete" :action="route('settings.destroy')" onsubmit="return confirm('{{ __('Are you absolutely sure? This action cannot be undone.') }}')">
          <x-button.secondary type="submit">{{ __('Delete account') }}</x-button.secondary>
        </x-form>
      </x-box>
    </div>
  </div>
</x-app-layout>
