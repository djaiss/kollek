<x-app-layout>
  <x-slot:title>
    Support tickets
  </x-slot>

  <div class="px-6 py-8 lg:px-12 lg:py-10">
    <div class="mx-auto w-full max-w-6xl space-y-6">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
          <h1 class="text-[22px] font-semibold tracking-tight text-ink">Support tickets</h1>
          <p class="mt-1 text-sm text-muted">Messages sent to support from every account on this instance.</p>
        </div>

        <form method="get" action="{{ route('instanceAdmin.support.index', ['status' => $status]) }}" class="w-full sm:max-w-xs">
          <div class="flex items-center gap-2 rounded-lg border border-hairline bg-input px-3 py-2 shadow-xs dark:shadow-none">
            @svg('lucide-search', 'size-4 shrink-0 text-muted-soft')
            <input
              type="search"
              name="search"
              value="{{ $search }}"
              placeholder="Search subject, requester, account…"
              class="min-w-0 flex-1 border-none bg-transparent p-0 text-[13px] text-ink placeholder-muted-soft focus:ring-0"
            />
          </div>
        </form>
      </div>

      @php
        $tabs = [
          ['key' => 'open', 'label' => 'Open', 'count' => $openCount],
          ['key' => 'all', 'label' => 'All', 'count' => $allCount],
          ['key' => 'closed', 'label' => 'Closed', 'count' => $closedCount],
        ];
      @endphp
      <div class="flex flex-wrap items-center gap-2">
        @foreach ($tabs as $tab)
          @php($active = $status === $tab['key'])
          <a
            href="{{ route('instanceAdmin.support.index', ['status' => $tab['key']]) }}"
            data-turbo="true"
            @class([
              'inline-flex items-center gap-2 rounded-full border px-3.5 py-1.5 text-[13px] font-medium transition-colors',
              'border-hairline bg-card text-ink' => $active,
              'border-transparent text-muted hover:text-ink' => ! $active,
            ])
          >
            {{ $tab['label'] }}
            <span
              @class([
                'inline-flex min-w-5 items-center justify-center rounded-full px-1.5 py-0.5 text-[11px] font-semibold',
                'bg-ink text-canvas' => $active,
                'bg-card text-muted-soft' => ! $active,
              ])
            >{{ $tab['count'] }}</span>
          </a>
        @endforeach
      </div>

      @if ($tickets->isEmpty())
        <x-box padding="p-0">
          <x-empty-state>
            <x-slot:icon>
              @svg('lucide-message-square', 'size-5 text-muted')
            </x-slot>
            @if ($search !== '')
              No conversations match "{{ $search }}".
            @else
            @switch($status)
              @case('closed')
                No closed conversations yet.
                @break
              @case('all')
                No support conversations yet. When someone writes to support, their message lands here.
                @break
              @default
                No open conversations. When someone writes to support, their message lands here.
            @endswitch
            @endif
          </x-empty-state>
        </x-box>
      @else
        <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.4fr)]">
          <div class="flex flex-col gap-2.5">
            @foreach ($tickets as $ticket)
              @php($isSelected = $selected?->id === $ticket->id)
              <a
                href="{{ route('instanceAdmin.support.index', ['status' => $status, 'ticket' => $ticket->id]) }}"
                data-turbo="true"
                @class([
                  'block rounded-xl border bg-canvas p-4 transition-colors',
                  'border-ink' => $isSelected,
                  'border-hairline hover:border-muted-soft' => ! $isSelected,
                ])
              >
                <div class="flex items-center justify-between gap-2">
                  <span class="text-xs text-muted-soft">{{ $ticket->category->label() }}</span>
                  <x-badge :color="$ticket->status->badgeColor()">{{ $ticket->status->label() }}</x-badge>
                </div>

                <p class="mt-2 truncate text-sm font-semibold text-ink">{{ $ticket->subject }}</p>

                <div class="mt-3 flex items-center gap-2">
                  <x-avatar :user="$ticket->user" :size="32" class="size-6 shrink-0 text-[10px]" />
                  <span class="min-w-0 flex-1 truncate text-xs text-muted">{{ $ticket->user->getFullName() }}</span>
                  <span class="shrink-0 text-xs text-muted-soft">{{ $ticket->created_at->diffForHumans() }}</span>
                </div>

                <div class="mt-3 flex items-center gap-1.5 border-t border-dashed border-hairline pt-3">
                  @svg('lucide-folder', 'size-3 shrink-0 text-badge-orange')
                  <span class="min-w-0 truncate font-mono text-[11px] font-medium text-muted">{{ $ticket->user->account->name }}</span>
                </div>
              </a>
            @endforeach
          </div>

          <div class="flex min-h-0 flex-col rounded-xl border border-hairline bg-canvas">
            @if ($selected)
              <div class="border-b border-hairline p-5">
                <div class="flex items-start justify-between gap-3">
                  <div class="min-w-0">
                    <h2 class="text-base font-semibold text-ink">{{ $selected->subject }}</h2>
                    <p class="mt-1 text-xs text-muted-soft">
                      Ticket #{{ $selected->id }}
                      &middot;
                      opened {{ $selected->created_at->diffForHumans() }}
                      &middot;
                      {{ $selected->category->label() }}
                    </p>
                  </div>
                  <x-badge :color="$selected->status->badgeColor()">{{ $selected->status->label() }}</x-badge>
                </div>

                <div class="mt-4 flex items-center gap-3 rounded-xl border border-hairline bg-card/60 p-3">
                  <x-avatar :user="$selected->user" :size="32" class="size-8 shrink-0 text-xs" />
                  <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-ink">{{ $selected->user->getFullName() }}</p>
                    <p class="truncate text-xs text-muted-soft">{{ $selected->user->email }}</p>
                  </div>

                  <a
                    href="{{ route('instanceAdmin.accounts.show', $selected->user->account) }}"
                    data-turbo="true"
                    class="flex shrink-0 items-center gap-1.5 rounded-lg border border-hairline bg-canvas px-2.5 py-1.5 text-xs font-semibold text-ink transition-colors hover:border-muted-soft"
                  >
                    @svg('lucide-folder', 'size-3.5 text-badge-orange')
                    <span class="max-w-32 truncate">{{ $selected->user->account->name }}</span>
                  </a>
                </div>
              </div>

              <div class="flex flex-col gap-5 p-5">
                @foreach ($selected->messages->sortBy('created_at') as $message)
                  <div @class(['flex gap-3', 'flex-row-reverse' => $message->is_from_team])>
                    <x-avatar :user="$message->user" :size="32" class="size-7 shrink-0 text-[10px]" />
                    <div class="min-w-0 flex-1">
                      <div @class(['flex flex-wrap items-center gap-2', 'justify-end' => $message->is_from_team])>
                        <span class="text-[13px] font-semibold text-ink">{{ $message->user->getFullName() }}</span>
                        @if ($message->is_from_team)
                          <x-badge color="violet" class="!px-2 !py-0.5 !text-xs">Team</x-badge>
                        @endif
                        <span class="text-xs text-muted-soft">{{ $message->created_at->diffForHumans() }}</span>
                      </div>
                      <div @class([
                        'mt-1.5 rounded-xl border p-3 text-[13px] leading-relaxed whitespace-pre-line',
                        'border-hairline bg-card text-body' => ! $message->is_from_team,
                        'border-transparent bg-accent/10 text-ink' => $message->is_from_team,
                      ])>{{ $message->body }}</div>
                    </div>
                  </div>
                @endforeach
              </div>

              <div class="mt-auto space-y-3 border-t border-hairline p-5">
                <x-form id="team-reply-form" method="post" :action="route('instanceAdmin.support.messages.create', $selected->id)" class="space-y-2">
                  <textarea
                    name="body"
                    rows="3"
                    placeholder="Write a reply…"
                    form="team-reply-form"
                    class="block w-full resize-y rounded-lg border border-hairline bg-input px-3 py-2 text-[13px] leading-relaxed text-ink placeholder-muted-soft shadow-xs aria-invalid:border-error dark:shadow-none"
                  >{{ old('body') }}</textarea>
                  <x-error :messages="$errors->get('body')" />
                </x-form>

                @php($isClosed = $selected->status === \App\Enums\SupportTicketStatus::Closed)

                <x-form id="team-status-form" method="put" :action="route('instanceAdmin.support.update', $selected->id)">
                  <input type="hidden" name="status" value="{{ $isClosed ? 'open' : 'closed' }}" />
                </x-form>

                <div class="flex flex-wrap items-center gap-2">
                  <x-button type="submit" form="team-reply-form" data-test="team-reply-button">
                    <x-slot:icon>
                      <x-lucide-send class="size-4" />
                    </x-slot>
                    Send reply
                  </x-button>

                  @if ($isClosed)
                    <x-button.secondary type="submit" form="team-status-form" data-test="reopen-button">Reopen conversation</x-button.secondary>
                    <span class="ml-auto inline-flex items-center gap-1.5 text-xs text-muted-soft">
                      @svg('lucide-check', 'size-4 shrink-0')
                      Closed {{ $selected->closed_at?->diffForHumans() }}
                    </span>
                  @else
                    <x-button.secondary type="submit" form="team-status-form" data-test="close-button" class="ml-auto">Close conversation</x-button.secondary>
                  @endif
                </div>
              </div>

              <div class="flex items-center gap-2 border-t border-hairline bg-card/40 px-5 py-3">
                @svg('lucide-clock', 'size-3.5 shrink-0 text-muted-soft')
                <span class="text-xs text-muted">
                  @if ($selected->status === \App\Enums\SupportTicketStatus::Closed)
                    Closed. Reopen to continue the conversation with the requester.
                  @else
                    Replying from here lands in the requester's support inbox and notifies them by email.
                  @endif
                </span>
              </div>
            @endif
          </div>
        </div>
      @endif
    </div>
  </div>
</x-app-layout>
