<x-app-layout>
  <x-slot:title>
    {{ __('Members') }}
  </x-slot>

  @php
    $roleOptions = ['owner' => __('Owner'), 'editor' => __('Editor'), 'viewer' => __('Viewer')];
  @endphp

  <div class="px-6 py-8 lg:px-12 lg:py-10">
    <div class="mx-auto w-full max-w-3xl space-y-8">
      <div>
        <div class="flex items-center gap-2">
          <h1 class="text-[22px] font-semibold tracking-tight text-ink">{{ __('Members') }}</h1>
          <x-help id="settings.members" />
        </div>
        <p class="mt-1 text-sm text-muted">{{ __('People who have access to :account.', ['account' => $account->name]) }}</p>
      </div>

      <div id="members" x-merge="replace" class="space-y-8">
      <x-box padding="p-0">
        @foreach ($members as $member)
          <div class="flex flex-col gap-3 border-b border-hairline-soft px-4 py-4 last:border-b-0 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex min-w-0 items-center gap-3">
              <x-avatar :user="$member" :size="32" class="size-9 text-xs" />
              <div class="min-w-0">
                <p class="truncate text-sm font-semibold text-ink">{{ $member->getFullName() }}</p>
                <p class="truncate text-xs text-muted">{{ $member->email }}</p>
              </div>
            </div>
            <div class="flex items-center gap-2">
              <x-form method="put" :action="route('settings.members.update', $member->id)" x-target="members" class="flex items-center gap-2">
                <x-select id="role" :options="$roleOptions" :selected="$member->role" />
                <x-button type="submit">{{ __('Update') }}</x-button>
              </x-form>
              <x-form method="delete" :action="route('settings.members.destroy', $member->id)" x-target="members" x-on:ajax:before="confirm('{{ __('Are you sure you want to proceed? This can not be undone.') }}') || $event.preventDefault()">
                <x-button.secondary type="submit">{{ __('Remove') }}</x-button.secondary>
              </x-form>
            </div>
          </div>
        @endforeach
      </x-box>

      <x-box :title="__('Invite a member')" helpId="settings.invite_member">
        <x-form method="post" :action="route('settings.members.create')" x-target="members" class="space-y-4">
          <x-input id="email" name="email" :label="__('Email')" :value="$previewEmail ?? ''" :error="$errors->get('email')" required placeholder="ross@friends.com" />
          <x-select id="role" :label="__('Role')" :options="$roleOptions" :selected="$previewRole ?? 'viewer'" :error="$errors->get('role')" required />
          <div class="flex items-center justify-end gap-2">
            <x-button.secondary
              type="submit"
              name="preview"
              :value="($showPreview ?? false) ? '0' : '1'"
              formmethod="get"
              formaction="{{ route('settings.members.index') }}"
            >
              {{ ($showPreview ?? false) ? __('Hide preview') : __('Preview email') }}
            </x-button.secondary>
            <x-button type="submit">{{ __('Send invitation') }}</x-button>
          </div>

          @if ($showPreview ?? false)
            <div class="overflow-hidden rounded-lg border border-hairline">
              <div class="flex flex-wrap items-center justify-between gap-2 border-b border-hairline-soft bg-card px-4 py-2.5">
                <div class="flex items-center gap-2 text-xs font-semibold tracking-wide text-muted uppercase">
                  @svg('lucide-eye', 'size-4')
                  {{ __('Preview (not sent yet)') }}
                </div>
                @if (str_contains($previewEmail ?? '', '@'))
                  <div class="text-xs text-muted">{{ __('To:') }} <span class="font-medium text-ink">{{ $previewEmail }}</span></div>
                @endif
              </div>

              <div class="flex items-center gap-2 border-b border-warning/20 bg-warning/10 px-4 py-2 text-xs text-warning">
                @svg('lucide-triangle-alert', 'size-4 shrink-0')
                {{ __('This is a preview. Links are disabled and nothing has been sent.') }}
              </div>

              <div class="bg-card p-4">
                {!! $previewHtml !!}
              </div>
            </div>
          @endif
        </x-form>
      </x-box>

      @if ($invitations->isNotEmpty())
        <x-box :title="__('Pending invitations')" padding="p-0">
          @foreach ($invitations as $invitation)
            <div class="flex items-center justify-between border-b border-hairline-soft px-4 py-3 last:border-b-0">
              <p class="text-sm text-ink">{{ $invitation->email }}</p>
              <x-badge>{{ __(ucfirst($invitation->role)) }}</x-badge>
            </div>
          @endforeach
        </x-box>
      @endif
      </div>
    </div>
  </div>
</x-app-layout>
