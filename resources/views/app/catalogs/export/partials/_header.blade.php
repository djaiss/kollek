{{-- Breadcrumb, title, and the two shortcuts that tick or untick everything. --}}
<div class="mb-5 flex items-center gap-1.5 text-[13px]">
  <a href="{{ route('collections.index') }}" data-turbo="true" class="font-medium text-muted-soft transition-colors hover:text-ink">{{ __('Collections') }}</a>
  <span class="text-muted-soft">/</span>
  <a href="{{ route('collections.show', $catalog->id) }}" data-turbo="true" class="truncate font-medium text-muted-soft transition-colors hover:text-ink">{{ $catalog->name }}</a>
  <span class="text-muted-soft">/</span>
  <span class="font-medium text-ink">{{ __('Export') }}</span>
</div>

<div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
  <div>
    <h1 class="text-[28px] font-semibold tracking-tight text-ink">{{ __('Export the collection') }}</h1>
    <p class="mt-1 max-w-xl text-[15px] text-muted">{{ __('Choose a format, then tick the sections and fields to include.') }}</p>
  </div>

  <div class="flex shrink-0 gap-2">
    <x-button.secondary type="button" @click="setAll(true)" data-test="check-all-button">{{ __('Check all') }}</x-button.secondary>
    <x-button.secondary type="button" @click="setAll(false)" data-test="uncheck-all-button">{{ __('Uncheck all') }}</x-button.secondary>
  </div>
</div>
