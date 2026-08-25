<x-app-layout>
  <x-slot:title>
    {{ $post->reference() }}
  </x-slot:title>

  @php
    $localeMeta = config('docs.locales.' . $locale);
    $state = $translation?->state;
    $publicUrl = $translation === null
      ? null
      : route('marketing.blog.show', ['locale' => $localeMeta['url'], 'slug' => $translation->slug]);
    $passing = collect($checks)->where('passes', true)->count();
  @endphp

  <div class="px-6 py-8 lg:px-12 lg:py-10">
    <div class="mx-auto w-full max-w-6xl space-y-6">
      <a href="{{ route('instanceAdmin.marketing.blogPosts.index') }}" data-turbo="true" class="inline-flex items-center gap-2 text-sm font-medium text-muted transition-colors hover:text-ink">
        @svg ('lucide-arrow-left', 'size-4')
        All blog posts
      </a>

      <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="min-w-0">
          <div class="flex flex-wrap items-center gap-2">
            <span class="text-xs tracking-wide text-muted-soft">{{ $post->reference() }}</span>
            <x-badge :color="$post->status->color()">{{ $post->status->label() }}</x-badge>
            <span class="text-xs text-muted-soft">saved {{ $post->updated_at?->diffForHumans() }}</span>
          </div>
          <h1 class="mt-1.5 text-[22px] font-semibold tracking-tight text-ink">{{ $post->source()?->title ?? 'Untitled' }}</h1>
        </div>

        <div class="flex flex-wrap items-center gap-2">
          @if ($publicUrl && $post->status->isReadable())
            <a href="{{ $publicUrl }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 text-sm font-medium text-muted hover:text-ink">
              @svg ('lucide-external-link', 'size-4')
              View
            </a>
          @endif

          @if ($post->status === \App\Enums\BlogPostStatus::Published)
            <x-form method="put" :action="route('instanceAdmin.marketing.blogPosts.update', $post->id)" onsubmit="return confirm('Archive this post? It leaves the catalogue, the feed and the sitemap. Its URL keeps answering, so existing links do not break.');">
              <input type="hidden" name="intent" value="archive" />
              <x-button.secondary type="submit">Archive</x-button.secondary>
            </x-form>
          @else
            <x-form method="put" :action="route('instanceAdmin.marketing.blogPosts.update', $post->id)">
              <input type="hidden" name="intent" value="publish" />
              <x-button type="submit">Publish</x-button>
            </x-form>
          @endif
        </div>
      </div>

      {{-- Which language is being written. Each has its own URL, so the browser
           back button walks the languages the way a writer expects. --}}
      <x-box>
        <div class="flex flex-wrap items-center gap-2">
          <p class="mr-2 inline-flex items-center gap-1.5 text-[11px] font-semibold tracking-wide text-muted-soft uppercase">
            @svg ('lucide-globe', 'size-3.5')
            Translations
          </p>

          @foreach ($languages as $language)
            @php
              $isCurrent = $language['locale'] === $locale;
              $dot = match (true) {
                $language['state'] === null => 'bg-hairline',
                $language['state']->isPublic() => 'bg-success',
                $language['state'] === \App\Enums\BlogTranslationState::Outdated => 'bg-error',
                default => 'bg-badge-orange',
              };
            @endphp
            <a href="{{ route('instanceAdmin.marketing.blogPosts.translations.edit', ['blogPost' => $post->id, 'locale' => $language['locale']]) }}" data-turbo="true" title="{{ $language['label'] }} — {{ $language['note'] }}" class="inline-flex items-center gap-2 rounded-md border px-2.5 py-1.5 text-xs font-medium {{ $isCurrent ? 'border-ink bg-ink text-white' : 'border-hairline text-muted hover:text-ink' }}">
              <span class="font-semibold">{{ $language['code'] }}</span>
              <span class="{{ $isCurrent ? 'text-white/70' : '' }}">{{ $language['label'] }}</span>
              <span class="size-1.5 rounded-full {{ $dot }}"></span>
            </a>
          @endforeach
        </div>
      </x-box>

      {{-- Why this language is not on the public site, and what to do about it. --}}
      @if ($state === null || ! $state->isPublic())
        <x-box>
          <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="flex items-start gap-3">
              @svg ('lucide-info', 'size-4 mt-0.5 shrink-0 text-muted')
              <p class="text-sm text-muted">
                @if ($state === null)
                  {{ $localeMeta['label'] }} has not been written yet, so this locale falls back to English.
                @elseif ($state === \App\Enums\BlogTranslationState::Outdated)
                  The English source changed after this {{ $localeMeta['label'] }} translation was written, so it has come off the public site. Re-check it, then mark it proofread.
                @else
                  This {{ $localeMeta['label'] }} translation is waiting on a proofread. It is not visible on the public site yet.
                @endif
              </p>
            </div>

            @if ($state === null && $locale !== config('docs.default_locale'))
              <x-form method="put" :action="route('instanceAdmin.marketing.blogPosts.translations.update', ['blogPost' => $post->id, 'locale' => $locale])">
                <input type="hidden" name="intent" value="copy_source" />
                <x-button.secondary type="submit">Copy English as a base</x-button.secondary>
              </x-form>
            @elseif ($state !== null && $locale !== config('docs.default_locale'))
              <x-form method="put" :action="route('instanceAdmin.marketing.blogPosts.translations.update', ['blogPost' => $post->id, 'locale' => $locale])">
                <input type="hidden" name="intent" value="publish" />
                <x-button.secondary type="submit">Mark proofread</x-button.secondary>
              </x-form>
            @endif
          </div>
        </x-box>
      @elseif ($state === \App\Enums\BlogTranslationState::Live)
        <x-box>
          <div class="flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-muted">This {{ $localeMeta['label'] }} translation is live. Readers in this language see it instead of the English.</p>
            <x-form method="put" :action="route('instanceAdmin.marketing.blogPosts.translations.update', ['blogPost' => $post->id, 'locale' => $locale])">
              <input type="hidden" name="intent" value="withdraw" />
              <x-button.secondary type="submit">Withdraw</x-button.secondary>
            </x-form>
          </div>
        </x-box>
      @endif

      <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
        {{-- The text, and everything about how it is found --}}
        <div class="space-y-6">
          <x-form method="put" :action="route('instanceAdmin.marketing.blogPosts.translations.update', ['blogPost' => $post->id, 'locale' => $locale])">
            <input type="hidden" name="intent" value="save" />

            <x-box title="Content · {{ $localeMeta['label'] }}">
              <div class="space-y-5">
                <x-input id="title" label="Title" :value="old('title', $translation?->title)" :error="$errors->get('title')" required />

                <x-textarea id="excerpt" label="Excerpt" :value="old('excerpt', $translation?->excerpt)" :error="$errors->get('excerpt')" rows="3" help="Aim for 70 to 160 characters: it is the standfirst and the fallback meta description." required />

                <x-textarea id="body" label="Body" :value="old('body', $translation?->body)" :error="$errors->get('body')" rows="20" help="Markdown. Headings become the contents list, and footnotes work." required />
              </div>
            </x-box>

            <div class="mt-6">
              <x-box title="URL &amp; routing" description="The slug is per language, so a reader gets a URL in their own language rather than an English one behind a translated prefix.">
                <div class="space-y-5">
                  <x-input id="slug" label="Slug ({{ $localeMeta['label'] }})" :value="old('slug', $translation?->slug)" :error="$errors->get('slug')" :help="url('/' . $localeMeta['url'] . '/blog') . '/…'" required />

                  <div class="flex items-start gap-2.5 rounded-md border border-hairline bg-canvas p-3">
                    @svg ('lucide-triangle-alert', 'size-4 mt-0.5 shrink-0 text-badge-orange')
                    <p class="text-xs text-muted">Changing the slug of a published post creates a permanent redirect from the old URL automatically, so existing links keep working. The reference
                    <span class="font-semibold text-ink">{{ $post->reference() }}</span>
                    never changes.</p>
                  </div>

                  @if ($post->redirects->isNotEmpty())
                    <div>
                      <p class="mb-2 text-[11px] font-semibold tracking-wide text-muted-soft uppercase">Redirecting from</p>
                      <div class="flex flex-wrap gap-1.5">
                        @foreach ($post->redirects->where('locale', $locale) as $redirect)
                          <span class="rounded border border-hairline bg-card px-2 py-0.5 font-mono text-[11px] text-muted">/blog/{{ $redirect->slug }}</span>
                        @endforeach
                      </div>
                    </div>
                  @endif

                  <div>
                    <p class="mb-2 text-[11px] font-semibold tracking-wide text-muted-soft uppercase">hreflang map</p>
                    <div class="space-y-1">
                      @foreach ($languages as $language)
                        <div class="flex items-center justify-between gap-3 border-b border-hairline-soft py-1 text-xs last:border-0">
                          <span class="font-mono text-muted-soft">{{ $language['locale'] }}</span>
                          @if ($language['state']?->isPublic())
                            <span class="truncate font-mono text-muted">/{{ config('docs.locales.' . $language['locale'] . '.url') }}/blog/{{ $language['slug'] }}</span>
                          @else
                            <span class="text-muted-soft">falls back to English</span>
                          @endif
                        </div>
                      @endforeach
                    </div>
                  </div>
                </div>
              </x-box>
            </div>

            <div class="mt-6">
              <x-box title="Metadata · {{ $localeMeta['label'] }}" :description="$passing . ' of ' . count($checks) . ' checks passing'">
                <div class="space-y-5">
                  <x-input id="meta_title" label="Meta title" :value="old('meta_title', $translation?->meta_title)" :error="$errors->get('meta_title')" help="30 to 60 characters. Falls back to the headline when empty." />

                  <x-textarea id="meta_description" label="Meta description" :value="old('meta_description', $translation?->meta_description)" :error="$errors->get('meta_description')" rows="3" help="70 to 160 characters. Falls back to the excerpt when empty." />

                  <x-input id="focus_keyword" label="Focus keyword" :value="old('focus_keyword', $translation?->focus_keyword)" :error="$errors->get('focus_keyword')" help="A writing aid only. Nothing is done with it beyond the check below." />

                  <div>
                    <p class="mb-2 text-[11px] font-semibold tracking-wide text-muted-soft uppercase">Checks</p>
                    <div class="space-y-1.5">
                      @foreach ($checks as $check)
                        <div class="flex items-start gap-2 text-xs">
                          <span class="mt-px shrink-0 {{ $check['passes'] ? 'text-success' : 'text-badge-orange' }}">
                            @svg ($check['passes'] ? 'lucide-check' : 'lucide-alert-circle', 'size-3.5')
                          </span>
                          <span class="{{ $check['passes'] ? 'text-muted' : 'text-ink' }}">{{ $check['text'] }}</span>
                        </div>
                      @endforeach
                    </div>
                    <p class="mt-2 text-[11px] text-muted-soft">These are advice, not a gate. Nothing here stops you publishing.</p>
                  </div>
                </div>
              </x-box>
            </div>

            <div class="mt-6">
              <x-button type="submit">Save {{ $localeMeta['label'] }}</x-button>
            </div>
          </x-form>

          {{-- Its own form: a file upload cannot share the one above. --}}
          @if ($translation)
            <x-box title="Social card · {{ $localeMeta['label'] }}" description="Shown when the post is shared. Cropped to 1200 by 630. Falls back to the site wide card when empty.">
              <div class="flex flex-wrap items-center gap-3">
                @if ($translation->og_image_path)
                  <img src="{{ route('marketing.blog.ogImage.show', ['locale' => $localeMeta['url'], 'slug' => $translation->slug]) }}" alt="" class="h-20 w-auto rounded-md border border-hairline" />
                @else
                  <div class="flex h-20 w-38 items-center justify-center rounded-md border border-dashed border-hairline text-xs text-muted-soft">No card</div>
                @endif

                <x-form method="post" :action="route('instanceAdmin.marketing.blogPosts.translations.ogImage.create', ['blogPost' => $post->id, 'locale' => $locale])" upload>
                  <div class="flex items-center gap-2">
                    <x-input id="og_image" type="file" :error="$errors->get('og_image')" required />
                    <x-button.secondary type="submit">Upload</x-button.secondary>
                  </div>
                </x-form>

                @if ($translation->og_image_path)
                  <x-form method="delete" :action="route('instanceAdmin.marketing.blogPosts.translations.ogImage.destroy', ['blogPost' => $post->id, 'locale' => $locale])" onsubmit="return confirm('Remove this social card? The post shares under the site wide card again.');">
                    <x-button.secondary type="submit">Remove</x-button.secondary>
                  </x-form>
                @endif
              </div>
            </x-box>
          @endif
        </div>

        {{-- What the entry is, rather than what it says --}}
        <div class="space-y-6">
          <x-form method="put" :action="route('instanceAdmin.marketing.blogPosts.update', $post->id)">
            <input type="hidden" name="intent" value="save" />

            <x-box title="Filing">
              <div class="space-y-5">
                <x-select id="shelf" label="Shelf" :options="$shelves" :selected="old('shelf', $post->shelf->value)" :error="$errors->get('shelf')" required />

                <x-input id="tags" label="Tags" :value="old('tags', $post->tags->pluck('name')->implode(', '))" :error="$errors->get('tags')" help="Comma separated. Not translated: they read as short English labels in every language." />

                <x-select id="robots" label="Robots" :options="['index,follow' => 'index, follow', 'noindex' => 'noindex', 'nofollow' => 'nofollow']" :selected="old('robots', $post->robots)" :error="$errors->get('robots')" />

                <label class="flex items-center gap-2.5 text-sm text-ink">
                  <input type="hidden" name="is_featured" value="0" />
                  <input type="checkbox" name="is_featured" value="1" @checked (old('is_featured', $post->is_featured)) class="size-4 rounded border-hairline" />
                  Feature on the blog index
                </label>
              </div>

              <div class="mt-6">
                <x-button type="submit">Save filing</x-button>
              </div>
            </x-box>
          </x-form>

          <x-box title="Record">
            <dl class="space-y-2 text-sm">
              @php
                $record = [
                  'Reference' => $post->reference(),
                  'Status' => $post->status->label(),
                  'Published' => $post->published_at?->isoFormat('LL') ?? 'Not yet',
                  'Author' => $post->author_name,
                  'Live locales' => count($post->liveLocales()) . ' of ' . count($languages),
                ];
              @endphp
              @foreach ($record as $label => $value)
                <div class="flex items-baseline justify-between gap-3 border-b border-hairline-soft pb-2 last:border-0">
                  <dt class="text-muted-soft">{{ $label }}</dt>
                  <dd class="text-right font-medium text-ink">{{ $value }}</dd>
                </div>
              @endforeach
            </dl>
          </x-box>

          <x-box title="Danger zone" description="Deleting takes every language of this post off the web for good. Archiving is almost always what you want instead: it keeps the URL answering for the links already out there.">
            <x-form method="delete" :action="route('instanceAdmin.marketing.blogPosts.destroy', $post->id)" onsubmit="return confirm('Delete {{ $post->reference() }} permanently? Every language of it goes, along with every redirect pointing at it. Links to it will start returning 404. This cannot be undone.')">
              <x-button.secondary type="submit">
                @svg ('lucide-trash-2', 'size-4')
                Delete this post
              </x-button.secondary>
            </x-form>
          </x-box>
        </div>
      </div>
    </div>
  </div>
</x-app-layout>
