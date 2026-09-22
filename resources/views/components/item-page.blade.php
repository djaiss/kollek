@props(['catalog', 'item', 'tags', 'tab'])

@php
    $user = auth()->user();
    $canManage = $user->account->allowsManagementBy($user);
    $type = $item->catalogType;

    // Every tab is its own page, so the active one is decided here rather than
    // in the browser. Overview is the item's own url, the rest hang off it.
    $navigation = [
        ['overview', __('Overview'), null, route('items.show', [$catalog, $item])],
        ['copies', __('Copies'), $item->copies->count(), route('items.copies.index', [$catalog, $item])],
        ['history', __('History'), null, route('items.history.index', [$catalog, $item])],
        ['activities', __('Activity'), null, route('items.activities.index', [$catalog, $item])],
    ];
@endphp

<x-app-layout :catalog="$catalog">
  <x-slot:title>
    {{ $item->name }}
  </x-slot>

  <div class="px-6 py-8 lg:px-10">
    <div class="mx-auto w-full max-w-5xl">
      <div class="mb-5 flex flex-wrap items-center gap-1.5 text-[13px]">
        <a href="{{ route('collections.index') }}" data-turbo="true" class="font-medium text-muted-soft transition-colors hover:text-ink">{{ __('Collections') }}</a>
        <span class="text-muted-soft">/</span>
        <a href="{{ route('collections.show', $catalog) }}" data-turbo="true" class="font-medium text-muted-soft transition-colors hover:text-ink">{{ $catalog->name }}</a>
        @if ($item->category)
          <span class="text-muted-soft">/</span>
          <a href="{{ route('categories.show', [$catalog, $item->category]) }}" data-turbo="true" class="font-medium text-muted-soft transition-colors hover:text-ink" data-test="breadcrumb-category">{{ $item->category->name }}</a>
        @endif
        <span class="text-muted-soft">/</span>
        <span class="truncate font-medium text-ink">{{ $item->name }}</span>
      </div>

      <div class="mb-3.5 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
          <div class="mb-2 flex flex-wrap items-center gap-2.5">
            @if ($type)
              <span class="inline-flex items-center gap-2 rounded-full bg-card px-3 py-1 text-xs font-semibold text-ink">
                <span class="size-2 shrink-0 rounded-full" style="background-color: {{ $type->color }}"></span>
                {{ $type->name }}
              </span>
            @endif

            @if ($item->category)
              <span class="text-xs font-medium text-muted-soft">{{ __('in :name', ['name' => $item->category->name]) }}</span>
            @endif
          </div>

          <h1 class="text-[28px] leading-tight font-semibold tracking-tight text-ink" data-test="item-name">{{ $item->name }}</h1>
        </div>

        <div class="flex shrink-0 flex-wrap items-center gap-2">
          @if ($canManage)
            <x-form method="delete" :action="route('items.destroy', [$catalog, $item])" id="delete-item-form" data-turbo="true" class="hidden" onsubmit="return confirm('{{ __('Delete :name? The item and all of its copies will no longer be accessible.', ['name' => $item->name]) }}')"></x-form>

            <x-button.split :href="route('items.edit', [$catalog, $item])" :label="__('Edit')" turbo data-test="edit-item-button">
              <x-slot:icon>@svg('lucide-pencil', 'size-4')</x-slot>

              <x-menu-item type="submit" form="delete-item-form" danger data-test="delete-item-button">
                <x-slot:icon>@svg('lucide-trash-2', 'size-4')</x-slot>
                {{ __('Delete item') }}
              </x-menu-item>
            </x-button.split>
          @endif
        </div>
      </div>

      @include('app.items.partials._tags')

      <div class="mb-7 flex items-center gap-1 overflow-x-auto overflow-y-hidden border-b border-hairline">
        @foreach ($navigation as [$key, $label, $count, $url])
          <a
            href="{{ $url }}"
            data-turbo="true"
            @class([
                '-mb-px flex items-center gap-2 border-b-2 px-3.5 py-3 text-sm whitespace-nowrap transition-colors',
                'border-ink font-semibold text-ink' => $tab === $key,
                'border-transparent font-medium text-muted hover:text-ink' => $tab !== $key,
            ])
            @if ($tab === $key) aria-current="page" @endif
            data-test="item-tab-{{ $key }}"
          >
            {{ $label }}
            @if ($count !== null)
              <span class="rounded-full bg-card px-1.5 py-px text-[11px] font-semibold text-muted">{{ $count }}</span>
            @endif
          </a>
        @endforeach
      </div>

      {{ $slot }}
    </div>
  </div>
</x-app-layout>
