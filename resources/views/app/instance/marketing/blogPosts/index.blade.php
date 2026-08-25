<x-app-layout>
  <x-slot:title>
    Blog posts
  </x-slot:title>

  @php
    $tabs = [
      'all' => 'All',
      'published' => 'Published',
      'draft' => 'Drafts',
      'archived' => 'Archived',
    ];
  @endphp

  <div class="px-6 py-8 lg:px-12 lg:py-10">
    <div class="mx-auto w-full max-w-6xl space-y-6">
      <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
          <h1 class="text-[22px] font-semibold tracking-tight text-ink">Blog posts</h1>
          <p class="mt-1 text-sm text-muted">Every entry in the public catalogue, across {{ count(config('docs.locales')) }} languages. Publishing here goes live on the marketing site.</p>
        </div>

        <div class="flex items-center gap-2">
          <form method="GET" action="{{ route('instanceAdmin.marketing.blogPosts.index', ['status' => $status]) }}" class="flex items-center gap-2">
            <x-input id="search" type="search" :value="$search" placeholder="Search title or slug…" class="w-56" />
            <x-button.secondary type="submit">
              @svg ('lucide-search', 'size-4')
              Search
            </x-button.secondary>
          </form>

          <x-button :href="route('instanceAdmin.marketing.blogPosts.new')" turbo>
            @svg ('lucide-plus', 'size-4')
            New post
          </x-button>
        </div>
      </div>

      <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($summary as $figure)
          <x-box>
            <p class="text-[11px] font-semibold tracking-wide text-muted-soft uppercase">{{ $figure['label'] }}</p>
            <p class="mt-1 text-[22px] font-semibold tracking-tight text-ink">{{ $figure['value'] }}</p>
            <p class="mt-0.5 text-xs text-muted">{{ $figure['note'] }}</p>
          </x-box>
        @endforeach
      </div>

      {{-- Filter tabs. The bucket lives in the path, so each is its own URL. --}}
      <div class="flex flex-wrap items-center gap-1.5">
        @foreach ($tabs as $key => $label)
          <a href="{{ route('instanceAdmin.marketing.blogPosts.index', ['status' => $key]) }}" data-turbo="true" class="inline-flex items-center gap-2 rounded-full border border-hairline px-3 py-1 text-xs font-medium {{ $status === $key ? 'bg-card text-ink' : 'text-muted hover:text-ink' }}">
            {{ $label }}
            <span class="rounded-full bg-canvas px-1.5 text-[11px] font-semibold text-muted-soft">{{ $counts[$key] ?? 0 }}</span>
          </a>
        @endforeach
      </div>

      <div class="space-y-3">
        @forelse ($rows as $row)
          <x-box>
            <div class="flex flex-wrap items-start justify-between gap-3">
              <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                  <span class="text-xs tracking-wide text-muted-soft">{{ $row['reference'] }}</span>
                  <x-badge :color="$row['status']->color()">{{ $row['status']->label() }}</x-badge>
                  <span class="text-xs text-muted-soft">{{ $row['shelf'] }}</span>
                </div>
                <a href="{{ route('instanceAdmin.marketing.blogPosts.translations.edit', ['blogPost' => $row['id'], 'locale' => config('docs.default_locale')]) }}" data-turbo="true" class="mt-1.5 block text-[15px] font-semibold text-ink hover:underline">{{ $row['title'] }}</a>
                <p class="mt-0.5 truncate font-mono text-xs text-muted-soft">{{ $row['slug'] }}</p>
              </div>

              <div class="text-right">
                <p class="text-xs text-muted-soft">updated {{ $row['updatedAt'] }}</p>
                <p class="mt-1 text-xs font-semibold text-muted">{{ $row['liveCount'] }}/{{ $row['localeCount'] }} live</p>
              </div>
            </div>

            {{-- Which languages a reader can actually read this in. A filled chip
                 is live; a hollow one is written but not yet public; a dashed one
                 has not been written at all. --}}
            <div class="mt-3 flex flex-wrap items-center gap-1.5">
              @foreach ($row['languages'] as $language)
                @php
                  $isLive = $language['state']?->isPublic() === true;
                  $classes = match (true) {
                    $isLive => 'border-ink bg-ink text-white',
                    $language['state'] === null => 'border-dashed border-hairline text-muted-soft',
                    default => 'border-hairline bg-card text-muted',
                  };
                @endphp
                <span title="{{ $language['label'] }} — {{ $language['state']?->label() ?? 'Not translated' }}" class="rounded border px-1.5 py-0.5 text-[10px] font-semibold tracking-wide {{ $classes }}">{{ $language['code'] }}</span>
              @endforeach
            </div>
          </x-box>
        @empty
          <x-box padding="p-0">
            <x-empty-state>
              <x-slot:icon>
                @svg ('lucide-notebook-pen', 'size-5 text-muted')
              </x-slot:icon>
              No posts match this filter.
            </x-empty-state>
          </x-box>
        @endforelse
      </div>
    </div>
  </div>
</x-app-layout>
