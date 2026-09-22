<x-app-layout>
  <x-slot:title>
    {{ __('Collection types') }}
  </x-slot>

  <div class="px-6 py-8 lg:px-12 lg:py-10">
    <div class="mx-auto w-full max-w-3xl space-y-8">
      <div class="flex items-start justify-between gap-4">
        <div>
          <div class="flex items-center gap-2">
            <h1 class="text-[22px] font-semibold tracking-tight text-ink">{{ __('Collection types') }}</h1>
            <x-help id="settings.collection_types" />
          </div>
          <p class="mt-1 max-w-lg text-sm text-muted">{{ __('Types define the custom fields available on items, and which collections can use them.') }}</p>
        </div>

        <div class="shrink-0">
          <x-form method="post" :action="route('settings.types.create')" id="new-type-form" data-turbo="true" class="hidden"></x-form>

          <x-button.split type="submit" form="new-type-form" :label="__('New type')" data-test="new-type-button">
            <x-menu-item :href="route('settings.types.import.new')" turbo data-test="import-type-button">
              <x-slot:icon>
                @svg('lucide-download', 'size-4 text-muted')
              </x-slot>
              {{ __('Import JSON') }}
            </x-menu-item>
          </x-button.split>
        </div>
      </div>

      <x-box padding="p-0">
        @forelse ($types as $type)
          <a
            href="{{ route('settings.types.edit', $type->id) }}"
            data-turbo="true"
            data-test="type-row-{{ $type->id }}"
            class="flex items-center gap-4 border-b border-hairline-soft px-5 py-4 transition-colors first:rounded-t-lg last:rounded-b-lg last:border-b-0 hover:bg-card"
          >
            <span class="size-9 shrink-0 rounded-full" style="background-color: {{ $type->color }}"></span>

            <div class="min-w-0 flex-1">
              <p class="truncate text-sm font-semibold text-ink">{{ $type->name }}</p>
              <p class="mt-0.5 truncate text-xs text-muted">{{ $type->field_summary }}</p>
            </div>

            <div class="hidden w-16 shrink-0 text-right sm:block">
              <p class="text-sm font-semibold text-ink">{{ $type->group_count }}</p>
              <p class="text-xs text-muted-soft">{{ __('groups') }}</p>
            </div>

            <div class="w-24 shrink-0 text-right">
              <p class="text-sm font-semibold text-ink">{{ $type->field_count }}</p>
              <p class="text-xs text-muted-soft">{{ __('custom fields') }}</p>
            </div>

            <div class="hidden w-24 shrink-0 text-right sm:block">
              <p class="text-sm font-semibold text-ink">{{ $type->collection_count }}</p>
              <p class="text-xs text-muted-soft">{{ __('collections') }}</p>
            </div>

            <div class="hidden w-20 shrink-0 text-right text-xs text-muted-soft lg:block">{{ $type->updated_at }}</div>
          </a>
        @empty
          <x-empty-state>
            <x-slot:icon>
              @svg('lucide-boxes', 'size-5 text-muted')
            </x-slot>

            {{ __('No collection types yet. Create your first one to define custom fields.') }}
          </x-empty-state>
        @endforelse
      </x-box>
    </div>
  </div>
</x-app-layout>
