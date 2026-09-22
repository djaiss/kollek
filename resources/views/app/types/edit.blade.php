@use('App\Enums\FieldTypeEnum')

<x-app-layout>
  <x-slot:title>
    {{ $type->name !== '' ? $type->name : __('Untitled type') }}
  </x-slot>

  <x-slot:head>
    <meta name="turbo-refresh-method" content="morph" />
    <meta name="turbo-refresh-scroll" content="preserve" />
  </x-slot>

  <div class="px-6 py-8 lg:px-12 lg:py-10">
    <div class="mx-auto w-full max-w-3xl">
      <div class="mb-6 flex items-center gap-2 text-xs text-muted-soft">
        @if (auth()->user()->isOwner())
          <a href="{{ route('settings.index') }}" data-turbo="true" class="font-medium transition-colors hover:text-ink">{{ __('Account settings') }}</a>
        @else
          <span>{{ __('Account settings') }}</span>
        @endif
        <span>/</span>
        <a href="{{ route('settings.types.index') }}" data-turbo="true" class="font-medium transition-colors hover:text-ink">{{ __('Collection types') }}</a>
        <span>/</span>
        <span class="font-medium text-ink">{{ $type->name !== '' ? $type->name : __('Untitled type') }}</span>
      </div>

      <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:gap-5">
        <div class="flex shrink-0 flex-col items-start gap-2.5 sm:items-center">
          <span class="size-14 rounded-full" style="background-color: {{ $type->color }}"></span>

          <x-form method="put" :action="route('settings.types.update', $type->id)" data-turbo="true" class="flex gap-1.5">
            <input type="hidden" name="name" value="{{ $type->name }}" />
            @foreach ($palette as $swatch)
              <button type="submit" name="color" value="{{ $swatch }}" aria-label="{{ $swatch }}" class="size-4 cursor-pointer rounded-full ring-offset-1 ring-offset-canvas {{ $type->color === $swatch ? 'ring-2 ring-ink' : '' }}" style="background-color: {{ $swatch }}"></button>
            @endforeach
          </x-form>
        </div>

        <div class="min-w-0 flex-1">
          <div id="name-display">
            <h1 class="truncate text-2xl font-semibold tracking-tight text-ink">{{ $type->name !== '' ? $type->name : __('Untitled type') }}</h1>
          </div>

          <x-form id="name-edit" hidden method="put" :action="route('settings.types.update', $type->id)" data-turbo="true" class="flex flex-wrap items-center gap-2">
            <input type="hidden" name="color" value="{{ $type->color }}" />
            <input id="type-name-input" name="name" value="{{ $type->name }}" placeholder="{{ __('Type name') }}" class="w-full max-w-xs rounded-md border border-hairline bg-input px-3 py-2 text-lg font-semibold text-ink" />
            <x-button type="submit" data-test="save-name-button">{{ __('Save') }}</x-button>
            <button
              type="button"
              onclick="
                document.getElementById('name-edit').hidden = true;
                document.getElementById('name-display').hidden = false;
                document.getElementById('type-actions').hidden = false;
              "
              class="cursor-pointer text-sm font-semibold text-muted hover:text-ink">
              {{ __('Cancel') }}
            </button>
          </x-form>

          <p class="mt-2 text-xs text-muted-soft">{{ __('Used by :collections collection(s) · :groups field group(s) · :fields custom field(s)', ['collections' => $type->catalogs->count(), 'groups' => $type->custom_field_groups_count, 'fields' => $type->custom_fields_count]) }}</p>
        </div>

        <div id="type-actions" class="flex shrink-0 items-center gap-3">
          <x-form method="delete" :action="route('settings.types.destroy', $type->id)" id="delete-type-form" data-turbo="true" class="hidden" onsubmit="return confirm('{{ __('Delete this type? This cannot be undone.') }}')"></x-form>

          <x-button.split
            :label="__('Edit name')"
            data-test="edit-name-button"
            onclick="
              document.getElementById('name-display').hidden = true;
              document.getElementById('name-edit').hidden = false;
              document.getElementById('type-name-input').focus();
              document.getElementById('type-actions').hidden = true;
            ">
            <x-menu-item :href="route('settings.types.export.show', $type->id)" turbo data-test="export-type-button">
              <x-slot:icon>@svg('lucide-download', 'size-4 text-muted')</x-slot>
              {{ __('Export as JSON') }}
            </x-menu-item>

            <div class="my-1 h-px bg-hairline"></div>

            <x-menu-item type="submit" form="delete-type-form" danger data-test="delete-type-button">
              <x-slot:icon>@svg('lucide-trash-2', 'size-4')</x-slot>
              {{ __('Delete type') }}
            </x-menu-item>
          </x-button.split>
        </div>
      </div>

      <div class="mb-9 flex items-center gap-2.5 rounded-lg border border-hairline bg-card px-4 py-3 text-sm text-muted">
        @svg('lucide-info', 'size-4 shrink-0 text-muted-soft')
        <span>{{ __('Changes below are saved automatically in real time as you make them. No need to hit save.') }}</span>
      </div>

      <h2 class="text-lg font-semibold text-ink">{{ __('Custom fields') }}</h2>
      <p class="mt-0.5 mb-6 max-w-xl text-xs text-muted-soft">{{ __('Organize related fields into groups (e.g. "Grading", "Purchase info"), or add a field directly to the type if it does not belong to a group.') }}</p>

      <div class="mb-3.5 flex items-center justify-between">
        <h3 class="text-xs font-semibold tracking-wide text-muted-soft uppercase">{{ __('Field groups') }}</h3>

        <x-form method="post" :action="route('settings.types.groups.create', $type->id)" data-turbo="true">
          <button type="submit" data-test="add-group-button" class="cursor-pointer rounded-md border border-dashed border-hairline px-3 py-2 text-xs font-semibold text-ink transition-colors hover:bg-card">+ {{ __('Add group') }}</button>
        </x-form>
      </div>

      <div class="mb-8 flex flex-col gap-4">
        @forelse ($type->customFieldGroups as $group)
          <div class="overflow-hidden rounded-2xl border border-hairline bg-canvas" data-test="group-{{ $group->id }}">
            <div class="flex items-center gap-2.5 border-b border-hairline bg-card px-4 py-3">
              <x-form method="put" :action="route('settings.types.groups.order.update', [$type->id, $group->id])" id="group-up-{{ $group->id }}" data-turbo="true" class="hidden">
                <input type="hidden" name="direction" value="up" />
              </x-form>
              <x-form method="put" :action="route('settings.types.groups.order.update', [$type->id, $group->id])" id="group-down-{{ $group->id }}" data-turbo="true" class="hidden">
                <input type="hidden" name="direction" value="down" />
              </x-form>
              <x-form method="delete" :action="route('settings.types.groups.destroy', [$type->id, $group->id])" id="group-delete-{{ $group->id }}" data-turbo="true" class="hidden" onsubmit="return confirm('{{ __('Delete this group? Its fields are kept and become standalone fields on the type.') }}')"></x-form>

              <div class="flex shrink-0 flex-col">
                <button type="submit" form="group-up-{{ $group->id }}" aria-label="{{ __('Move group up') }}" data-test="move-group-up-{{ $group->id }}" class="flex h-[15px] w-[26px] cursor-pointer items-center justify-center rounded-t-md border border-hairline bg-canvas text-[9px] text-muted hover:bg-card">▲</button>
                <button type="submit" form="group-down-{{ $group->id }}" aria-label="{{ __('Move group down') }}" data-test="move-group-down-{{ $group->id }}" class="flex h-[15px] w-[26px] cursor-pointer items-center justify-center rounded-b-md border border-t-0 border-hairline bg-canvas text-[9px] text-muted hover:bg-card">▼</button>
              </div>

              <x-form method="put" :action="route('settings.types.groups.update', [$type->id, $group->id])" data-turbo="true" onchange="this.requestSubmit()" class="min-w-0 flex-1">
                <input name="name" value="{{ $group->name }}" placeholder="{{ __('Group name, e.g. Grading') }}" data-test="group-name-{{ $group->id }}" class="w-full rounded-md border border-transparent bg-transparent px-1 py-1.5 text-sm font-semibold text-ink hover:border-hairline" />
              </x-form>

              <span class="shrink-0 text-xs whitespace-nowrap text-muted-soft">{{ trans_choice(':count field|:count fields', $group->customFields->count(), ['count' => $group->customFields->count()]) }}</span>

              <x-form method="post" :action="route('settings.types.groups.fields.create', [$type->id, $group->id])" data-turbo="true" class="shrink-0">
                <button type="submit" data-test="add-field-to-group-{{ $group->id }}" class="cursor-pointer rounded-md border border-dashed border-hairline px-2.5 py-1.5 text-xs font-semibold whitespace-nowrap text-ink transition-colors hover:bg-canvas">+ {{ __('Field') }}</button>
              </x-form>

              <button type="submit" form="group-delete-{{ $group->id }}" aria-label="{{ __('Remove group') }}" data-test="delete-group-{{ $group->id }}" class="flex size-8 shrink-0 cursor-pointer items-center justify-center rounded-md border border-hairline bg-canvas text-muted hover:bg-card">×</button>
            </div>

            <div class="flex flex-col gap-3 p-4">
              @forelse ($group->customFields as $field)
                @include('app.types._field-row', ['type' => $type, 'field' => $field, 'placeholder' => __('e.g. Grading company')])
              @empty
                <div class="rounded-lg border border-dashed border-hairline p-5 text-center text-xs text-muted-soft">{{ __('No fields in this group yet.') }}</div>
              @endforelse
            </div>
          </div>
        @empty
          <div class="rounded-xl border border-dashed border-hairline p-6 text-center text-sm text-muted">{{ __('No field groups yet. Add one to organize related fields together.') }}</div>
        @endforelse
      </div>

      <div class="mb-1 flex items-center justify-between">
        <h3 class="text-xs font-semibold tracking-wide text-muted-soft uppercase">{{ __('Standalone fields') }}</h3>

        <x-form method="post" :action="route('settings.types.fields.create', $type->id)" data-turbo="true">
          <button type="submit" data-test="add-field-button" class="cursor-pointer rounded-md border border-dashed border-hairline px-3 py-2 text-xs font-semibold text-ink transition-colors hover:bg-card">+ {{ __('Add field') }}</button>
        </x-form>
      </div>
      <p class="mb-3.5 text-xs text-muted-soft">{{ __('Fields that live directly on the type, outside of any group.') }}</p>

      <div class="mb-10 flex flex-col gap-3">
        @forelse ($type->ungroupedCustomFields as $field)
          @include('app.types._field-row', ['type' => $type, 'field' => $field, 'placeholder' => __('e.g. Notes')])
        @empty
          <div class="rounded-xl border border-dashed border-hairline p-6 text-center text-sm text-muted">{{ __('No standalone fields. Every field on this type lives in a group.') }}</div>
        @endforelse
      </div>

      <h2 class="text-lg font-semibold text-ink">{{ __('Collections using this type') }}</h2>
      <p class="mt-0.5 mb-3.5 text-xs text-muted-soft">{{ __('A collection can use many types; an item picks exactly one.') }}</p>

      @if ($catalogs->isEmpty())
        <p class="text-sm text-muted">{{ __('No collections use this type yet.') }}</p>
      @else
        <div class="flex flex-wrap gap-2">
          @foreach ($catalogs as $catalog)
            <a
              href="{{ route('collections.show', $catalog->id) }}"
              data-turbo="true"
              data-test="collection-chip-{{ $catalog->id }}"
              class="flex items-center gap-2 rounded-full border border-hairline px-3.5 py-2 text-sm font-medium text-ink transition-colors hover:bg-card"
            >
              <span>{{ $catalog->emoji }}</span>
              {{ $catalog->name }}
              @svg('lucide-arrow-up-right', 'size-3.5 shrink-0 text-muted-soft')
            </a>
          @endforeach
        </div>
      @endif
    </div>
  </div>
</x-app-layout>
