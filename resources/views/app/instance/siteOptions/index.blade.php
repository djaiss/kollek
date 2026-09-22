<x-app-layout>
  <x-slot:title>
    Site options
  </x-slot>

  @php
    // The panel is English only, so these labels are plain strings. They name the
    // locales the marketing site is served in.
    $localeLabels = [
      'en' => 'English',
      'fr_FR' => 'French',
      'es_ES' => 'Spanish',
      'de_DE' => 'German',
      'pt_BR' => 'Portuguese',
      'zh_CN' => 'Chinese',
      'ja_JP' => 'Japanese',
    ];

    $content = [];
    foreach ($locales as $locale) {
      $content[$locale] = [
        'text' => old("banner_content.{$locale}.text", $siteOption->banner_content[$locale]['text'] ?? ''),
        'link_label' => old("banner_content.{$locale}.link_label", $siteOption->banner_content[$locale]['link_label'] ?? ''),
      ];
    }

    $confirmPurge = 'return confirm('.Js::from('Purge the cached marketing pages from Cloudflare? Visitors will get freshly rendered ones, which is slower until the cache fills up again.').')';
  @endphp

  <div class="px-6 py-8 lg:px-12 lg:py-10">
    <div class="mx-auto w-full max-w-4xl space-y-8">
      <div>
        <h1 class="text-[22px] font-semibold tracking-tight text-ink">Site options</h1>
        <p class="mt-1 text-sm text-muted">Settings for the public marketing site. They apply to the whole instance, not to any one account.</p>
      </div>

      <div
        x-data="{
          enabled: {{ old('banner_enabled', $siteOption->banner_enabled ? '1' : '0') === '1' ? 'true' : 'false' }},
          version: @js(old('banner_version', $siteOption->banner_version ?? '')),
          url: @js(old('banner_url', $siteOption->banner_url ?? '')),
          locale: @js($locales[0]),
          content: @js($content),
          get preview() { return this.content[this.locale] ?? { text: '', link_label: '' }; },
        }"
      >
        <x-box title="Announcement banner" padding="p-0">
          <x-slot:description>
            The black bar at the top of every marketing page. Use it for a release or an announcement. The sentence and the link label are written per language; a language you leave empty falls back to English.
          </x-slot>

          <x-form method="put" :action="route('instanceAdmin.siteOptions.update')">
            <div class="border-b border-hairline-soft p-3">
              <p class="mb-2 text-xs font-medium tracking-wide text-muted-soft uppercase">Preview</p>
              <div class="flex flex-col items-center justify-center gap-2 rounded-md bg-[#101010] px-4 py-2 text-center text-[13px] font-medium sm:h-10 sm:flex-row sm:py-0" x-show="enabled && preview.text" x-cloak>
                <div class="flex items-center gap-2">
                  <span class="rounded-full bg-[#1a1a1a] px-2 py-[3px] text-[11px] font-semibold tracking-wide text-badge-emerald" x-show="version" x-text="version"></span>
                  <span class="text-[#a1a1aa]" x-text="preview.text"></span>
                </div>
                <span class="font-semibold text-white" x-show="url" x-text="(preview.link_label || 'Read more') + ' →'"></span>
              </div>
              <p class="text-sm text-muted" x-show="! (enabled && preview.text)" x-cloak>Nothing shows on the marketing site for this language.</p>
            </div>

            <div class="grid gap-4 p-3 sm:grid-cols-3">
              <div>
                <x-select
                  id="banner_enabled"
                  label="Show the banner"
                  required
                  :options="['1' => 'Yes', '0' => 'No']"
                  selected="{{ old('banner_enabled', $siteOption->banner_enabled ? '1' : '0') }}"
                  @change="enabled = $event.target.value === '1'"
                  :error="$errors->get('banner_enabled')"
                />
              </div>

              <div>
                <x-input
                  id="banner_version"
                  label="Version"
                  placeholder="v0.9"
                  :value="old('banner_version', $siteOption->banner_version)"
                  x-model="version"
                  help="The green pill on the left. Leave empty to hide it."
                  :error="$errors->get('banner_version')"
                />
              </div>

              <div>
                <x-input
                  id="banner_url"
                  label="Link"
                  placeholder="https://github.com/djaiss/kollek/releases"
                  :value="old('banner_url', $siteOption->banner_url)"
                  x-model="url"
                  help="Where the banner links to. Leave empty for a banner with no link."
                  :error="$errors->get('banner_url')"
                />
              </div>
            </div>

            <div class="border-t border-hairline-soft p-3">
              <div class="flex flex-wrap items-center gap-1.5">
                @foreach ($locales as $locale)
                  <button
                    type="button"
                    @click="locale = '{{ $locale }}'"
                    class="inline-flex cursor-pointer items-center gap-2 rounded-full border border-hairline px-3 py-1 text-xs font-medium"
                    :class="locale === '{{ $locale }}' ? 'bg-card text-ink' : 'text-muted hover:text-ink'"
                  >
                    {{ $localeLabels[$locale] ?? $locale }}
                    <span class="size-1.5 rounded-full bg-badge-emerald" x-show="content['{{ $locale }}'].text" x-cloak></span>
                  </button>
                @endforeach
              </div>

              @foreach ($locales as $locale)
                <div class="mt-4 space-y-4" x-show="locale === '{{ $locale }}'" x-cloak>
                  <x-input
                    id="banner_content[{{ $locale }}][text]"
                    label="Sentence"
                    placeholder="Custom item types are here. Build a schema for any hobby."
                    :value="$content[$locale]['text']"
                    x-model="content['{{ $locale }}'].text"
                    :error="$errors->get('banner_content.'.$locale.'.text')"
                  />

                  <x-input
                    id="banner_content[{{ $locale }}][link_label]"
                    label="Link label"
                    placeholder="Read the changelog"
                    :value="$content[$locale]['link_label']"
                    x-model="content['{{ $locale }}'].link_label"
                    help="Shown only when a link is set. Defaults to the English label."
                    :error="$errors->get('banner_content.'.$locale.'.link_label')"
                  />
                </div>
              @endforeach
            </div>

            <div class="flex items-center justify-between gap-3 border-t border-hairline-soft p-3">
              <p class="text-xs text-muted">Saving clears the marketing cache, so the change is live straight away.</p>
              <x-button>Save</x-button>
            </div>
          </x-form>
        </x-box>
      </div>

      @if ($cloudflareConfigured)
        <x-box title="Cloudflare cache" padding="p-0">
          <x-slot:description>
            Marketing pages are rendered once and held by Cloudflare for seven days. Purge it after changing anything the public site shows, such as a testimonial or the documentation portal.
          </x-slot>

          <div class="flex items-center justify-between gap-3 p-3">
            <p class="text-sm text-muted">Purging drops every cached page at once. Nothing is lost: the pages are rendered again on the next visit, only slower until the cache fills up. Visitors keep their own copy of a page for five minutes, which no purge can reach.</p>

            <x-form
              method="delete"
              :action="route('instanceAdmin.siteOptions.cloudflareCache.destroy')"
              :onsubmit="$confirmPurge"
            >
              <x-button.secondary type="submit">Purge cache</x-button.secondary>
            </x-form>
          </div>
        </x-box>
      @endif
    </div>
  </div>
</x-app-layout>
