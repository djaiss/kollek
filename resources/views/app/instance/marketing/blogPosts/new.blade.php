<x-app-layout>
  <x-slot:title>
    New blog post
  </x-slot:title>

  <div class="px-6 py-8 lg:px-12 lg:py-10">
    <div class="mx-auto w-full max-w-3xl space-y-6">
      <a href="{{ route('instanceAdmin.marketing.blogPosts.index') }}" data-turbo="true" class="inline-flex items-center gap-2 text-sm font-medium text-muted transition-colors hover:text-ink">
        @svg ('lucide-arrow-left', 'size-4')
        All blog posts
      </a>

      <div>
        <h1 class="text-[22px] font-semibold tracking-tight text-ink">New blog post</h1>
        <p class="mt-1 text-sm text-muted">You are writing the English source. Every other language is translated from it, and falls back to it until somebody has. The post is a draft until you publish it, and its reference number is assigned now and never changes.</p>
      </div>

      <x-form method="post" :action="route('instanceAdmin.marketing.blogPosts.create')">
        <x-box>
          <div class="space-y-5">
            <x-input id="title" label="Title" :value="old('title')" :error="$errors->get('title')" required autofocus />

            <x-input id="slug" label="Slug" :value="old('slug')" :error="$errors->get('slug')" placeholder="Leave empty to build it from the title" help="The last segment of the public URL, in this language. Changing it after publishing leaves a redirect behind." />

            <x-select id="shelf" label="Shelf" :options="$shelves" :selected="old('shelf')" :error="$errors->get('shelf')" required />

            <x-textarea id="excerpt" label="Excerpt" :value="old('excerpt')" :error="$errors->get('excerpt')" rows="3" help="The standfirst under the headline. Also the fallback meta description, so aim for 70 to 160 characters." required />

            <x-textarea id="body" label="Body" :value="old('body')" :error="$errors->get('body')" rows="18" help="Markdown. Headings become the contents list, and footnotes work." required />
          </div>

          <div class="mt-6 flex items-center gap-2">
            <x-button type="submit">Create draft</x-button>
            <a href="{{ route('instanceAdmin.marketing.blogPosts.index') }}" data-turbo="true" class="text-sm text-muted hover:text-ink">Cancel</a>
          </div>
        </x-box>
      </x-form>
    </div>
  </div>
</x-app-layout>
