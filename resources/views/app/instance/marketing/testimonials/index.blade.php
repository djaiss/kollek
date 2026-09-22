<x-app-layout>
  <x-slot:title>
    Testimonials
  </x-slot>

  @php
    $tabs = [
      'in_review' => 'In review',
      'published' => 'Published',
      'rejected' => 'Rejected',
      'draft' => 'Drafts',
      'all' => 'All',
    ];
    $statusLabels = [
      'in_review' => 'In review',
      'published' => 'Published',
      'rejected' => 'Rejected',
      'draft' => 'Draft',
    ];
  @endphp

  <div class="px-6 py-8 lg:px-12 lg:py-10">
    <div class="mx-auto w-full max-w-4xl space-y-6">
      <div>
        <h1 class="text-[22px] font-semibold tracking-tight text-ink">Testimonials</h1>
        <p class="mt-1 text-sm text-muted">Review member submissions before they appear on the marketing homepage. Approving one publishes it and emails the author.</p>
      </div>

      <div class="flex flex-wrap items-center gap-1.5">
        @foreach ($tabs as $key => $label)
          <a
            href="{{ route('instanceAdmin.marketing.testimonials.index', ['status' => $key]) }}"
            data-turbo="true"
            class="inline-flex items-center gap-2 rounded-full border border-hairline px-3 py-1 text-xs font-medium {{ $status === $key ? 'bg-card text-ink' : 'text-muted hover:text-ink' }}"
          >
            {{ $label }}
            <span class="rounded-full bg-canvas px-1.5 text-[11px] font-semibold text-muted-soft">{{ $counts[$key] }}</span>
          </a>
        @endforeach
      </div>

      <div class="space-y-3.5">
        @forelse ($testimonials as $testimonial)
          @php
            $st = $testimonial->status->value;
            $safeLink = $testimonial->safeLink();
          @endphp
          <x-box>
            <div class="mb-3 flex items-start justify-between gap-3">
              <div class="flex min-w-0 items-center gap-3">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-badge-violet text-sm font-semibold text-white">{{ $testimonial->initial() }}</span>
                <div class="min-w-0">
                  <div class="flex items-center gap-2">
                    @if ($safeLink)
                      <a href="{{ $safeLink }}" target="_blank" rel="nofollow ugc noopener" class="text-[15px] font-semibold text-ink underline decoration-hairline underline-offset-2 hover:decoration-ink">{{ $testimonial->name }}</a>
                      @svg('lucide-external-link', 'size-3 text-muted-soft')
                    @else
                      <span class="text-[15px] font-semibold text-ink">{{ $testimonial->name }}</span>
                      <span class="text-xs text-muted-soft">no link</span>
                    @endif
                  </div>
                  <p class="mt-0.5 text-xs text-muted">
                    {{ $testimonial->user->account->name }}
                    @if ($testimonial->submitted_at)
                      · submitted {{ $testimonial->submitted_at->isoFormat('MMM D, YYYY') }}
                    @endif
                  </p>
                </div>
              </div>
              <x-badge :color="$testimonial->status->color()">{{ $statusLabels[$st] }}</x-badge>
            </div>

            <div class="mb-3">
              <p class="mb-1 text-[11px] font-semibold tracking-wide text-muted-soft uppercase">Link</p>
              @if ($safeLink)
                <a href="{{ $safeLink }}" target="_blank" rel="nofollow ugc noopener" class="inline-flex max-w-full items-center gap-2 rounded-md border border-hairline bg-card px-3 py-2 font-mono text-[13px] text-ink">
                  @svg('lucide-link', 'size-3.5 shrink-0 text-muted-soft')
                  <span class="truncate">{{ $safeLink }}</span>
                </a>
              @else
                <div class="inline-flex items-center gap-2 rounded-md border border-dashed border-hairline bg-card px-3 py-2 text-[13px] text-muted-soft">
                  @svg('lucide-link-2-off', 'size-3.5')
                  No link provided
                </div>
              @endif
            </div>

            <p class="mb-4 text-[14px] leading-relaxed text-ink">{{ $testimonial->body }}</p>

            <div class="flex flex-wrap items-center gap-2">
              @if (in_array($st, ['in_review', 'rejected'], true))
                <x-form method="put" :action="route('instanceAdmin.marketing.testimonials.update', $testimonial->id)" onsubmit="return confirm('Publish this testimonial? It will go live on the marketing site and the author will be emailed.')">
                  <input type="hidden" name="intent" value="publish" />
                  <input type="hidden" name="return" value="{{ $status }}" />
                  <x-button type="submit">
                    @svg('lucide-check', 'size-4')
                    {{ $st === 'rejected' ? 'Publish & notify' : 'Approve & publish' }}
                  </x-button>
                </x-form>
              @endif

              @if (in_array($st, ['in_review', 'published'], true))
                <x-form method="put" :action="route('instanceAdmin.marketing.testimonials.update', $testimonial->id)" onsubmit="return confirm('{{ $st === 'published' ? 'Take this testimonial off the marketing site?' : 'Reject this testimonial?' }}')">
                  <input type="hidden" name="intent" value="reject" />
                  <input type="hidden" name="return" value="{{ $status }}" />
                  <x-button.secondary type="submit">
                    @svg('lucide-x', 'size-4')
                    {{ $st === 'published' ? 'Unpublish' : 'Reject' }}
                  </x-button.secondary>
                </x-form>
              @endif

              @if ($st === 'draft')
                <span class="text-[13px] text-muted-soft">Not submitted yet, waiting on the member.</span>
              @endif
            </div>
          </x-box>
        @empty
          <x-box padding="p-0">
            <x-empty-state>
              <x-slot:icon>
                @svg('lucide-quote', 'size-5 text-muted')
              </x-slot>
              Nothing in this bucket.
            </x-empty-state>
          </x-box>
        @endforelse
      </div>
    </div>
  </div>
</x-app-layout>
