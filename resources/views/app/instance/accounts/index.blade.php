@use('Illuminate\Support\Str')

<x-app-layout>
  <x-slot:title>
    Accounts & users
  </x-slot>

  <div class="px-6 py-8 lg:px-12 lg:py-10">
    <div class="mx-auto w-full max-w-5xl space-y-6">
      <div>
        <h1 class="text-[22px] font-semibold tracking-tight text-ink">Accounts & users</h1>
        <p class="mt-1 text-sm text-muted">{{ $accounts->total() }} of {{ $totalCount }} accounts</p>
      </div>

      <form method="GET" action="{{ route('instanceAdmin.accounts.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-center">
        @if ($role !== null)
          <input type="hidden" name="role" value="{{ $role }}" />
        @endif

        <input
          type="search"
          name="search"
          value="{{ $search }}"
          placeholder="Search by email…"
          class="h-9 w-full rounded-md border border-hairline bg-canvas px-3 text-sm text-ink placeholder:text-muted-soft sm:max-w-xs"
        />

        <div class="flex items-center gap-1.5">
          <a
            href="{{ route('instanceAdmin.accounts.index', ['search' => $search]) }}"
            class="rounded-full border border-hairline px-3 py-1 text-xs font-medium {{ $role === null ? 'bg-card text-ink' : 'text-muted' }}"
          >All</a>

          @foreach ($roles as $option)
            <a
              href="{{ route('instanceAdmin.accounts.index', ['search' => $search, 'role' => $option->value]) }}"
              class="rounded-full border border-hairline px-3 py-1 text-xs font-medium capitalize {{ $role === $option->value ? 'bg-card text-ink' : 'text-muted' }}"
            >{{ ucfirst($option->value) }}</a>
          @endforeach
        </div>

        <x-button type="submit">Search</x-button>
      </form>

      <x-box padding="p-0">
        @forelse ($accounts as $account)
          <a
            href="{{ route('instanceAdmin.accounts.show', $account->id) }}"
            data-turbo="true"
            class="flex items-center justify-between gap-3 border-b border-hairline-soft px-4 py-3.5 transition-colors last:border-b-0 hover:bg-card"
          >
            <div class="min-w-0">
              <p class="truncate text-sm font-semibold text-ink">{{ $account->name }}</p>
              <p class="truncate text-xs text-muted">
                {{ $account->users_count }} {{ Str::plural('member', $account->users_count) }}
                ·
                {{ $account->catalogs_count }} {{ Str::plural('collection', $account->catalogs_count) }}
              </p>
            </div>

            <div class="flex shrink-0 items-center gap-4">
              <span class="hidden text-xs text-muted sm:block">{{ $account->created_at->isoFormat('ll') }}</span>
              @svg('lucide-chevron-right', 'size-4 text-muted-soft')
            </div>
          </a>
        @empty
          <x-empty-state>
            <x-slot:icon>
              @svg('lucide-users', 'size-5 text-muted')
            </x-slot>
            No accounts match your search.
          </x-empty-state>
        @endforelse
      </x-box>

      {{ $accounts->links() }}
    </div>
  </div>
</x-app-layout>
