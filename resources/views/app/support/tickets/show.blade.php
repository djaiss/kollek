@use('App\Enums\SupportTicketStatus')

@php
  $statusClasses = match ($ticket->status) {
      SupportTicketStatus::Open => ['text-success', 'bg-success/10', 'border-success/20', 'bg-success'],
      SupportTicketStatus::Answered => ['text-badge-violet', 'bg-badge-violet/10', 'border-badge-violet/20', 'bg-badge-violet'],
      SupportTicketStatus::Closed => ['text-muted', 'bg-card', 'border-hairline', 'bg-muted-soft'],
  };
@endphp

<x-app-layout>
  <x-slot:title>
    {{ $ticket->subject }}
  </x-slot>

  <div class="px-6 py-8 lg:px-12 lg:py-10">
    <div class="mx-auto w-full max-w-3xl">
      <a href="{{ route('support.tickets.index') }}" data-turbo="true" class="mb-5 inline-flex items-center gap-2 text-[13px] font-medium text-muted transition-colors hover:text-ink">
        @svg('lucide-arrow-left', 'size-4')
        {{ __('Back to conversations') }}
      </a>

      <div class="overflow-hidden rounded-2xl border border-hairline bg-canvas shadow-xs">
        <div class="border-b border-hairline-soft px-6 py-5">
          <div class="flex items-start justify-between gap-4">
            <div class="min-w-0 flex-1">
              <h1 class="text-xl font-semibold tracking-tight text-ink break-words">{{ $ticket->subject }}</h1>

              <div class="mt-2 flex flex-wrap items-center gap-2 text-[13px] text-muted-soft">
                <span class="inline-flex items-center gap-1.5 font-medium text-muted">
                  @svg('lucide-'.$ticket->category->icon(), 'size-3.5')
                  {{ $ticket->category->label() }}
                </span>
                <span class="size-[3px] rounded-full bg-muted-soft"></span>
                <span>{{ __('Opened :time', ['time' => $ticket->created_at->diffForHumans()]) }}</span>
              </div>
            </div>

            <span @class(['inline-flex shrink-0 items-center gap-2 rounded-full border px-3 py-1 text-xs font-semibold', $statusClasses[0], $statusClasses[1], $statusClasses[2]])>
              <span @class(['size-1.5 rounded-full', $statusClasses[3]])></span>
              {{ $ticket->status->label() }}
            </span>
          </div>

          <div class="mt-4 flex flex-wrap items-center gap-2">
            @if ($ticket->status !== SupportTicketStatus::Closed)
              <x-form method="put" action="{{ route('support.tickets.update', $ticket) }}">
                <button type="submit" data-test="close-conversation-button" class="inline-flex items-center gap-1.5 rounded-lg border border-hairline bg-canvas px-3 py-1.5 text-[13px] font-semibold text-body transition-colors hover:border-hairline hover:bg-card hover:text-ink">
                  @svg('lucide-check', 'size-4 text-muted')
                  {{ __('Close conversation') }}
                </button>
              </x-form>
            @endif

            <x-form
              method="delete"
              action="{{ route('support.tickets.destroy', $ticket) }}"
              onsubmit="return confirm('{{ __('Are you sure you want to delete this conversation? This can not be undone.') }}')"
            >
              <button type="submit" data-test="delete-conversation-button" class="inline-flex items-center gap-1.5 rounded-lg border border-error/25 bg-canvas px-3 py-1.5 text-[13px] font-semibold text-error transition-colors hover:border-error/40 hover:bg-error/5">
                @svg('lucide-trash-2', 'size-4')
                {{ __('Delete conversation') }}
              </button>
            </x-form>
          </div>
        </div>

        <div class="px-6 pt-6">
          @foreach ($ticket->messages->sortBy('created_at') as $message)
            <div class="flex gap-3.5 pb-6">
              @if ($message->is_from_team)
                <x-avatar :name="__('Support')" :size="32" class="size-9 shrink-0 bg-ink text-sm text-canvas" />
              @else
                <x-avatar :user="$message->user" :size="32" class="size-9 shrink-0 text-sm" />
              @endif

              <div class="min-w-0 flex-1">
                <div class="mb-1.5 flex flex-wrap items-center gap-2">
                  <span class="text-sm font-semibold text-ink">{{ $message->is_from_team ? __('Support') : $message->user->getFullName() }}</span>
                  @if ($message->is_from_team)
                    <span class="rounded-full border border-success/30 bg-success/10 px-2 py-px text-[11px] font-semibold text-success">{{ __('Support team') }}</span>
                  @endif
                  <span class="text-xs text-muted-soft">{{ $message->created_at->diffForHumans() }}</span>
                </div>

                <div @class([
                  'rounded-tr-2xl rounded-b-2xl rounded-tl-sm border p-4 text-sm leading-relaxed whitespace-pre-line',
                  'border-hairline-soft bg-card text-body' => $message->is_from_team,
                  'border-hairline bg-canvas text-body' => ! $message->is_from_team,
                ])>{{ $message->body }}</div>
              </div>
            </div>
          @endforeach
        </div>

        <div class="px-6 pt-1 pb-6">
          @if ($ticket->status === SupportTicketStatus::Closed)
            <div class="ml-[52px] flex items-start gap-3 rounded-xl border border-hairline bg-card p-4 text-sm text-muted">
              <x-lucide-check class="mt-0.5 size-4 shrink-0" />
              <div class="space-y-0.5">
                @if ($ticket->closed_by)
                  <p>
                    {{ __('Closed by') }}
                    <span class="font-semibold text-ink">{{ $ticket->closed_by->label() }}</span>
                    @if ($ticket->closed_at)
                      <span class="text-muted-soft">&middot; {{ $ticket->closed_at->diffForHumans() }}</span>
                    @endif
                  </p>
                @else
                  <p>{{ __('This conversation is closed.') }}</p>
                @endif
                <p>{{ __('Need more help?') }} <a href="{{ route('support.tickets.new') }}" data-turbo="true" class="font-semibold text-ink hover:underline">{{ __('Start a new one') }}</a></p>
              </div>
            </div>
          @else
            <div x-data="{ sent: {{ session('reply_sent') ? 'true' : 'false' }}, draft: @js(old('body', '')) }" class="ml-[52px]">
              <x-form x-show="!sent" method="post" action="{{ route('support.tickets.messages.create', $ticket) }}" class="rounded-2xl border border-hairline bg-canvas focus-within:border-accent">
                <textarea
                  id="body"
                  name="body"
                  x-model="draft"
                  rows="4"
                  placeholder="{{ __('Write a reply…') }}"
                  class="block w-full resize-y border-none bg-transparent px-4 pt-4 pb-1 text-sm leading-relaxed text-ink placeholder-muted-soft focus:ring-0"
                >{{ old('body') }}</textarea>

                <div class="px-4 pb-3">
                  <x-error :messages="$errors->get('body')" />
                </div>

                <div class="flex justify-end px-3 pb-3">
                  <x-button type="submit" data-test="reply-button" x-bind:disabled="draft.trim().length === 0">
                    <x-slot:icon>
                      <x-lucide-send class="size-4" />
                    </x-slot>
                    {{ __('Reply') }}
                  </x-button>
                </div>
              </x-form>

              <div x-show="sent" x-cloak>
                <div class="flex items-center gap-4 rounded-2xl border border-success/30 bg-success/5 px-6 py-5">
                  <div class="flex size-12 shrink-0 items-center justify-center rounded-full border border-success/30 bg-success/10">
                    <x-lucide-check class="size-6 text-success" />
                  </div>
                  <div class="min-w-0 flex-1">
                    <p class="text-base font-semibold text-ink">{{ __('Thanks for your message!') }}</p>
                    <p class="mt-0.5 text-sm text-muted">{{ __('I\'ll reply to your email as soon as I can, usually within a few hours.') }}</p>
                  </div>
                </div>

                <button type="button" x-on:click="sent = false" class="mt-3.5 inline-flex items-center gap-2 text-sm font-semibold text-accent transition-colors hover:text-ink">
                  <x-lucide-pencil class="size-4" />
                  {{ __('Add another reply') }}
                </button>
              </div>
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</x-app-layout>
