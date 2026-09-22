@use('App\Enums\CopyStatus')

@php
    // The add and the edit screens share this body. On the add screen there is no
    // item, so every field falls back to an empty value.
    $item ??= null;

    $typesData = $types->map(fn ($type) => [
      'id' => $type->id,
      'fields' => $type->customFields->map(fn ($field) => [
        'id' => $field->id,
        'name' => $field->name,
        'type' => $field->field_type->value,
        'options' => $field->options ?? [],
      ])->values(),
    ])->values();

    $existingValues = $item?->customFieldValues
        ->mapWithKeys(fn ($value) => [(string) $value->custom_field_id => $value->value])
        ->all() ?? [];

    $copyStatuses = CopyStatus::options();

    // A valuation stores money in cents, so it is brought back to currency units
    // for the form, which is what the controller converts again on the way in.
    $existingCopies = $item?->copies->map(fn ($copy) => [
        'id' => $copy->id,
        'identifier' => $copy->identifier ?? '',
        'item_condition_id' => (string) $copy->item_condition_id,
        'current_location_id' => (string) $copy->current_location_id,
        'location_name' => $copy->currentLocation?->name ?? '',
        'status' => $copy->status->value,
        'quantity' => $copy->quantity,
        'note' => $copy->note ?? '',
        'estimated_value' => $copy->estimatedValue() === null ? '' : number_format($copy->estimatedValue() / 100, 2, '.', ''),
    ])->values()->all() ?? [];

    $blankCopy = ['id' => null, 'identifier' => '', 'item_condition_id' => '', 'current_location_id' => '', 'location_name' => '', 'status' => CopyStatus::Owned->value, 'quantity' => 1, 'note' => '', 'estimated_value' => ''];

    $selectedCategoryId = old('category_id', $item?->category_id);
    $selectedSetId = old('set_id', $item?->set_id);
    $selectedSeriesId = old('series_id', $item?->series_id);
    // The photos the item already has, in the order the item screen shows them.
    $existingPhotos = ($item?->photos ?? collect())
        ->map(fn ($photo): array => ['id' => $photo->id, 'url' => $photo->url()])
        ->values()
        ->all();

    $mainPhotoId = $item?->mainPhoto?->id;
@endphp

<div
  class="mx-auto w-full max-w-2xl"
  x-data="{
    types: @js($typesData),
    name: @js(old('name', $item?->name ?? '')),
    typeId: @js((string) old('type_id', $item?->type_id ?? '')),
    customValues: @js(old('custom_fields', $existingValues)),
    selectedTagIds: @js(collect(old('tag_ids', $item?->tags->pluck('id')->all() ?? []))->map(fn ($id) => (int) $id)->all()),
    newTags: [],
    tagDraft: '',
    copies: @js($existingCopies === [] ? [$blankCopy] : $existingCopies),
    existingPhotos: @js($existingPhotos),
    newPhotos: [],
    deletedPhotoIds: [],
    mainPhotoId: @js($mainPhotoId),
    nextPhotoKey: 0,
    dragging: false,
    sizeError: '',
    get selectedType() { return this.types.find(t => String(t.id) === String(this.typeId)) || null },
    get customFields() { return this.selectedType ? this.selectedType.fields : [] },
    get canSubmit() { return this.name.trim().length > 0 },
    toggleType(id) { this.typeId = String(this.typeId) === String(id) ? '' : String(id) },
    isTypeActive(id) { return String(this.typeId) === String(id) },
    toggleTag(id) { const i = this.selectedTagIds.indexOf(id); i === -1 ? this.selectedTagIds.push(id) : this.selectedTagIds.splice(i, 1) },
    addTag() { const v = this.tagDraft.trim(); if (v && !this.newTags.includes(v)) this.newTags.push(v); this.tagDraft = '' },
    removeNewTag(name) { this.newTags = this.newTags.filter(t => t !== name) },
    addCopy() { this.copies.push(@js($blankCopy)) },
    removeCopy(i) {
      if (this.copies.length <= 1) return
      // A copy that is already saved disappears for good once the form is sent.
      if (this.copies[i].id && !confirm(@js(__('Remove this copy? It will be deleted when you save.')))) return
      this.copies.splice(i, 1)
    },
    isMain(id) { return this.mainPhotoId === id },
    isDeleted(id) { return this.deletedPhotoIds.includes(id) },
    deletePhoto(id) {
      this.deletedPhotoIds.push(id)
      // The cover cannot be a photo that is on its way out. Whichever photo the
      // item keeps first takes over, which is what the server does anyway.
      if (this.mainPhotoId !== id) return
      const kept = this.existingPhotos.find(p => !this.isDeleted(p.id))
      this.mainPhotoId = kept ? kept.id : null
    },
    restorePhoto(id) {
      this.deletedPhotoIds = this.deletedPhotoIds.filter(x => x !== id)
      if (this.mainPhotoId === null) this.mainPhotoId = id
    },
    addFiles(fileList) {
      this.sizeError = ''
      for (const file of fileList) {
        if (!file.type.startsWith('image/')) continue
        // Reject here so an oversized photo never leaves the browser: the server
        // caps a photo at 10 MB, and a bigger request would bounce off nginx.
        if (window.oversizedFiles([file], 10240).length) {
          // The semicolon is load bearing. A blade echo compiles to a php echo, and php
          // eats the newline after the closing tag, gluing the next statement onto this one.
          this.sizeError = @js(__('Each photo must be under 10 MB. Larger ones were skipped.'));
          continue
        }
        this.newPhotos.push({ key: this.nextPhotoKey++, file, preview: URL.createObjectURL(file) })
      }
      this.syncPhotoInput()
    },
    removeNewPhoto(index) {
      URL.revokeObjectURL(this.newPhotos[index].preview)
      this.newPhotos.splice(index, 1)
      this.syncPhotoInput()
    },
    // A file input replaces its whole list every time it is used, so the files
    // picked over several goes are collected here and written back to it.
    syncPhotoInput() {
      const data = new DataTransfer()
      this.newPhotos.forEach(p => data.items.add(p.file))
      this.$refs.photoInput.files = data.files
    },
    onPickPhotos(e) { this.addFiles(e.target.files) },
    onDropPhotos(e) {
      this.dragging = false
      this.addFiles(e.dataTransfer.files)
    },
  }"
>
  <div class="mb-5 flex items-center gap-1.5 text-[13px]">
    <a href="{{ route('collections.show', $catalog) }}" data-turbo="true" class="font-medium text-muted-soft transition-colors hover:text-ink">{{ $catalog->name }}</a>
    <span class="text-muted-soft">/</span>
    @if ($item)
      <a href="{{ route('items.show', [$catalog, $item]) }}" data-turbo="true" class="truncate font-medium text-muted-soft transition-colors hover:text-ink">{{ $item->name }}</a>
      <span class="text-muted-soft">/</span>
      <span class="font-medium text-ink">{{ __('Edit') }}</span>
    @else
      <span class="font-medium text-ink">{{ __('New item') }}</span>
    @endif
  </div>

  <div class="mb-8">
    <h1 class="text-[28px] font-semibold tracking-tight text-ink">{{ $item ? __('Edit item') : __('Add an item') }}</h1>
    <p class="mt-1.5 text-[15px] text-muted">{{ __('Catalog details apply to the item; each physical copy tracks its own condition and location.') }}</p>
  </div>

  <x-form
    :method="$item ? 'put' : 'post'"
    :action="$item ? route('items.update', [$catalog, $item]) : route('items.create', $catalog)"
    :upload="true"
    data-turbo="true"
    data-test="{{ $item ? 'edit-item-form' : 'add-item-form' }}"
    class="space-y-6"
  >
    <div>
      <x-label>{{ __('Photos') }}</x-label>
      <p class="mt-1 text-[13px] text-muted-soft">{{ __('The cover is the one shown wherever the item is listed.') }}</p>

      <div class="mt-2 flex flex-wrap items-start gap-3" data-test="item-photos">
        <template x-for="photo in existingPhotos" :key="photo.id">
          <div class="relative size-24 shrink-0" :class="isDeleted(photo.id) && 'opacity-40'">
            <img :src="photo.url" alt="" class="size-full rounded-xl border border-hairline object-cover" />

            <span
              x-show="isMain(photo.id) && !isDeleted(photo.id)"
              x-cloak
              class="absolute bottom-1 left-1 rounded-full bg-ink/80 px-1.5 py-px text-[10px] font-semibold text-page"
            >{{ __('Cover') }}</span>

            <button
              type="button"
              x-show="!isMain(photo.id) && !isDeleted(photo.id)"
              x-cloak
              x-on:click="mainPhotoId = photo.id"
              class="absolute bottom-1 left-1 cursor-pointer rounded-full bg-ink/60 px-1.5 py-px text-[10px] font-semibold text-page transition-colors hover:bg-ink/80"
              :data-test="'make-cover-' + photo.id"
              :title="@js(__('Make this the cover'))"
            >{{ __('Make cover') }}</button>

            <button
              type="button"
              x-show="!isDeleted(photo.id)"
              x-on:click="deletePhoto(photo.id)"
              class="absolute -top-1.5 -right-1.5 flex size-6 cursor-pointer items-center justify-center rounded-full border border-hairline bg-page text-muted transition-colors hover:text-ink"
              :data-test="'remove-photo-' + photo.id"
              :title="@js(__('Remove this photo'))"
            >@svg('lucide-x', 'size-3.5')</button>

            <button
              type="button"
              x-show="isDeleted(photo.id)"
              x-cloak
              x-on:click="restorePhoto(photo.id)"
              class="absolute inset-0 flex cursor-pointer items-center justify-center rounded-xl text-[11px] font-semibold text-ink"
              :data-test="'restore-photo-' + photo.id"
            >{{ __('Undo') }}</button>
          </div>
        </template>

        <template x-for="(file, index) in newPhotos" :key="file.key">
          <div class="relative size-24 shrink-0">
            <img :src="file.preview" alt="" class="size-full rounded-xl border border-hairline object-cover" />
            <span class="absolute bottom-1 left-1 rounded-full bg-ink/70 px-1.5 py-px text-[10px] font-semibold text-page">{{ __('New') }}</span>
            <button
              type="button"
              x-on:click="removeNewPhoto(index)"
              class="absolute -top-1.5 -right-1.5 flex size-6 cursor-pointer items-center justify-center rounded-full border border-hairline bg-page text-muted transition-colors hover:text-ink"
              :data-test="'remove-new-photo-' + index"
              :title="@js(__('Remove this photo'))"
            >@svg('lucide-x', 'size-3.5')</button>
          </div>
        </template>

        <label
          class="relative flex size-24 shrink-0 cursor-pointer items-center justify-center overflow-hidden rounded-xl border border-dashed bg-card text-center transition-colors"
          :class="dragging ? 'border-muted-soft ring-2 ring-[var(--color-accent)]/40' : 'border-hairline hover:border-muted-soft'"
          x-on:dragover.prevent="dragging = true"
          x-on:dragleave.prevent="dragging = false"
          x-on:drop.prevent="onDropPhotos($event)"
          data-test="add-photos"
        >
          <input x-ref="photoInput" type="file" name="photos[]" accept="image/*" multiple class="sr-only" x-on:change="onPickPhotos($event)" />
          <span class="pointer-events-none px-2 font-mono text-[10px] leading-tight text-muted-soft">{{ __('Drop or click') }}</span>
        </label>
      </div>

      <template x-for="id in deletedPhotoIds" :key="id">
        <input type="hidden" name="deleted_photos[]" :value="id" />
      </template>
      <input type="hidden" name="main_photo_id" :value="mainPhotoId ?? ''" />

      <p x-show="sizeError" x-cloak x-text="sizeError" class="mt-2 text-sm text-error"></p>
      <x-error :messages="$errors->get('photos')" class="mt-2" />
      <x-error :messages="$errors->get('photos.*')" class="mt-2" />
    </div>

    <x-input id="name" :label="__('Name')" :placeholder="__('e.g. Amazing Spider-Man #1')" x-model="name" :error="$errors->get('name')" required autofocus data-test="item-name-input" />

    <x-textarea id="description" :label="__('Description')" :placeholder="__('Notes about this item…')" rows="3" :value="old('description', $item?->description)" :error="$errors->get('description')" />

    <div>
      <div class="flex items-center gap-2">
        <x-label>{{ __('Type') }} <span class="font-normal text-muted-soft">({{ __('Optional') }})</span></x-label>
        <x-help id="items.type" />
      </div>
      <p class="mt-0.5 mb-2.5 text-[13px] text-muted-soft">{{ __('Choosing a type unlocks its custom fields below.') }}</p>
      <input type="hidden" name="type_id" :value="typeId" />
      <div class="flex flex-wrap gap-2">
        @foreach ($types as $type)
          <div
            x-on:click="toggleType('{{ $type->id }}')"
            class="flex cursor-pointer items-center gap-2 rounded-full border px-3.5 py-2 text-sm font-medium text-ink transition-colors"
            :class="isTypeActive('{{ $type->id }}') ? 'border-ink bg-card' : 'border-hairline'"
          >
            <span class="size-2 shrink-0 rounded-full" style="background-color: {{ $type->color }}"></span>
            {{ $type->name }}
          </div>
        @endforeach
      </div>
    </div>

    <div x-show="customFields.length > 0" x-cloak class="rounded-xl border border-hairline bg-canvas p-5">
      <p class="mb-4 text-[13px] font-semibold tracking-wide text-muted-soft uppercase">{{ __('Type fields') }}</p>
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <template x-for="field in customFields" :key="field.id">
          <div>
            <label class="mb-2 block text-[13px] font-semibold text-ink" x-text="field.name"></label>

            <template x-if="field.type === 'boolean'">
              <label class="flex h-11 cursor-pointer items-center gap-2.5">
                <input type="hidden" :name="`custom_fields[${field.id}]`" value="" />
                <input type="checkbox" value="1" class="sr-only" :name="`custom_fields[${field.id}]`" x-model="customValues[field.id]" />
                <span class="relative h-6 w-10 shrink-0 rounded-full transition-colors" :class="customValues[field.id] ? 'bg-ink' : 'bg-hairline'">
                  <span class="absolute top-[3px] size-[18px] rounded-full bg-white transition-all" :class="customValues[field.id] ? 'left-[19px]' : 'left-[3px]'"></span>
                </span>
                <span class="text-[13px] text-muted" x-text="customValues[field.id] ? '{{ __('Yes') }}' : '{{ __('No') }}'"></span>
              </label>
            </template>

            <template x-if="field.type === 'select'">
              <select :name="`custom_fields[${field.id}]`" x-model="customValues[field.id]" class="h-11 w-full appearance-none rounded-md border border-hairline bg-input pl-3 pr-9 text-sm text-ink">
                <option value="">{{ __('Select…') }}</option>
                <template x-for="option in field.options" :key="option">
                  <option :value="option" x-text="option"></option>
                </template>
              </select>
            </template>

            <template x-if="field.type === 'rating'">
              <div class="flex h-11 items-center gap-1">
                <input type="hidden" :name="`custom_fields[${field.id}]`" x-model="customValues[field.id]" />

                <template x-for="star in {{ App\Enums\FieldTypeEnum::MAX_RATING }}" :key="star">
                  <button
                    type="button"
                    x-on:click="customValues[field.id] = customValues[field.id] == star ? '' : star"
                    class="cursor-pointer text-xl leading-none"
                    :class="customValues[field.id] >= star ? 'text-ink' : 'text-hairline'"
                    :aria-label="`${star}`"
                  >
                    ★
                  </button>
                </template>

                <button
                  type="button"
                  x-show="customValues[field.id]"
                  x-cloak
                  x-on:click="customValues[field.id] = ''"
                  class="ml-2 cursor-pointer text-[13px] text-muted hover:text-ink"
                >
                  {{ __('Clear') }}
                </button>
              </div>
            </template>

            <template x-if="field.type !== 'boolean' && field.type !== 'select' && field.type !== 'rating'">
              <input
                :type="field.type === 'number' ? 'number' : field.type === 'date' ? 'date' : 'text'"
                :name="`custom_fields[${field.id}]`"
                x-model="customValues[field.id]"
                class="h-11 w-full rounded-md border border-hairline bg-input px-3.5 text-sm text-ink placeholder-muted-soft"
              />
            </template>
          </div>
        </template>
      </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
      <div>
        <div class="flex items-center gap-1.5">
          <x-label for="category_id">{{ __('Category') }} <span class="font-normal text-muted-soft">({{ __('Optional') }})</span></x-label>
          <x-help id="items.category" />
        </div>
        <select id="category_id" name="category_id" class="mt-2 h-11 w-full appearance-none rounded-md border border-hairline bg-input pl-3 pr-9 text-sm text-ink">
          <option value="">{{ __('No category') }}</option>
          @foreach ($categories as $category)
            <option value="{{ $category['id'] }}" @selected($selectedCategoryId == $category['id'])>{{ str_repeat("\u{00A0}\u{00A0}\u{00A0}", $category['depth']) . $category['name'] }}</option>
          @endforeach
        </select>
        @if ($categories === [])
          <p class="mt-2 text-[13px] text-muted-soft">
            {{ __('This collection has no categories yet.') }}
            <a href="{{ route('categories.index', $catalog->id) }}" class="font-medium text-ink underline underline-offset-2">{{ __('Create one') }}</a>
          </p>
        @endif
      </div>
      <div>
        <div class="flex items-center gap-1.5">
          <x-label for="set_id">{{ __('Set') }} <span class="font-normal text-muted-soft">({{ __('Optional') }})</span></x-label>
          <x-help id="items.set" />
        </div>
        <select id="set_id" name="set_id" class="mt-2 h-11 w-full appearance-none rounded-md border border-hairline bg-input pl-3 pr-9 text-sm text-ink">
          <option value="">{{ __('No set') }}</option>
          @foreach ($sets as $set)
            <option value="{{ $set->id }}" @selected($selectedSetId == $set->id)>{{ $set->name }}</option>
          @endforeach
        </select>
        @if ($sets->isEmpty())
          <p class="mt-2 text-[13px] text-muted-soft">
            {{ __('This collection has no sets yet.') }}
            <a href="{{ route('sets.index', $catalog->id) }}" class="font-medium text-ink underline underline-offset-2">{{ __('Create one') }}</a>
          </p>
        @endif
      </div>
    </div>

    <div>
      <div class="flex items-center gap-1.5">
        <x-label for="series_id">{{ __('Series') }} <span class="font-normal text-muted-soft">({{ __('Optional') }})</span></x-label>
        <x-help id="items.series" />
      </div>
      <select id="series_id" name="series_id" class="mt-2 h-11 w-full appearance-none rounded-md border border-hairline bg-input pl-3 pr-9 text-sm text-ink">
        <option value="">{{ __('No series') }}</option>
        @foreach ($series as $one)
          <option value="{{ $one->id }}" @selected($selectedSeriesId == $one->id)>{{ $one->name }}</option>
        @endforeach
      </select>
      @if ($series->isEmpty())
        <p class="mt-2 text-[13px] text-muted-soft">
          {{ __('This account has no series yet.') }}
          <a href="{{ route('series.index') }}" class="font-medium text-ink underline underline-offset-2">{{ __('Create one') }}</a>
        </p>
      @endif
    </div>

    <div>
      <div class="flex items-center gap-1.5">
        <x-label>{{ __('Tags') }} <span class="font-normal text-muted-soft">({{ __('Optional') }})</span></x-label>
        <x-help id="items.tags" />
      </div>
      <div class="mt-2.5 flex flex-wrap items-center gap-2">
        @foreach ($tags as $tag)
          <div
            x-on:click="toggleTag({{ $tag->id }})"
            class="flex cursor-pointer items-center gap-1.5 rounded-full border px-3.5 py-2 text-[13px] font-medium text-ink transition-colors"
            :class="selectedTagIds.includes({{ $tag->id }}) ? 'border-ink bg-card' : 'border-hairline'"
          >{{ $tag->name }}</div>
        @endforeach

        <template x-for="name in newTags" :key="name">
          <div x-on:click="removeNewTag(name)" class="flex cursor-pointer items-center gap-1.5 rounded-full border border-ink bg-card px-3.5 py-2 text-[13px] font-medium text-ink">
            <span x-text="name"></span>
            <span class="text-muted-soft">&times;</span>
          </div>
        </template>

        <input
          x-model="tagDraft"
          x-on:keydown.enter.prevent="addTag()"
          placeholder="{{ __('Add tag + Enter') }}"
          class="h-9 w-36 rounded-full border border-dashed border-hairline bg-transparent px-3.5 text-[13px] text-ink placeholder-muted-soft"
        />
      </div>

      <template x-for="id in selectedTagIds" :key="id">
        <input type="hidden" name="tag_ids[]" :value="id" />
      </template>
      <template x-for="name in newTags" :key="name">
        <input type="hidden" name="new_tags[]" :value="name" />
      </template>
    </div>

    <div class="h-px bg-hairline-soft"></div>

    <div>
      <div class="flex items-center gap-2">
        <h2 class="text-lg font-semibold text-ink">{{ __('Copies') }}</h2>
        <x-help id="items.copies" />
      </div>
      <p class="mt-0.5 text-[13px] text-muted-soft">{{ __('Each copy is a physical instance you own. Add one row per copy.') }}</p>
    </div>

    <div class="space-y-3">
      <template x-for="(copy, index) in copies" :key="index">
        <div class="rounded-xl border border-hairline bg-canvas p-4">
          <template x-if="copy.id">
            <input type="hidden" :name="`copies[${index}][id]`" :value="copy.id" />
          </template>

          <div class="mb-3.5 flex items-center justify-between">
            <span class="text-[13px] font-semibold text-muted-soft">{{ __('Copy') }} <span x-text="index + 1"></span></span>
            <button
              type="button"
              x-show="copies.length > 1"
              x-on:click="removeCopy(index)"
              class="flex size-7 items-center justify-center rounded-lg border border-hairline text-muted transition-colors hover:bg-card"
              aria-label="{{ __('Remove copy') }}"
            >&times;</button>
          </div>

          <div class="grid grid-cols-1 gap-3.5 sm:grid-cols-2">
            <div>
              <label class="mb-1.5 block text-xs font-semibold text-muted-soft">{{ __('Condition') }}</label>
              <select :name="`copies[${index}][item_condition_id]`" x-model="copy.item_condition_id" class="h-10 w-full appearance-none rounded-md border border-hairline bg-input pl-3 pr-9 text-sm text-ink">
                <option value="">{{ __('Not set') }}</option>
                @foreach ($conditions as $condition)
                  <option value="{{ $condition->id }}">{{ $condition->name }}</option>
                @endforeach
              </select>
            </div>
            <div>
              <label class="mb-1.5 block text-xs font-semibold text-muted-soft">{{ __('Location') }}</label>
              <template x-if="!copy.id">
                <select :name="`copies[${index}][current_location_id]`" x-model="copy.current_location_id" class="h-10 w-full appearance-none rounded-md border border-hairline bg-input pl-3 pr-9 text-sm text-ink">
                  <option value="">{{ __('Not set') }}</option>
                  @foreach ($locations as $location)
                    <option value="{{ $location->id }}">{{ $location->name }}</option>
                  @endforeach
                </select>
              </template>
              <template x-if="copy.id">
                <div class="flex h-10 items-center rounded-md border border-dashed border-hairline bg-canvas px-3 text-sm text-muted">
                  <span x-text="copy.location_name || '{{ __('Not set') }}'"></span>
                  <span class="ml-auto text-[11px] text-muted-soft">{{ __('Move from history') }}</span>
                </div>
              </template>
            </div>
          </div>

          <div class="mt-3.5 grid grid-cols-1 gap-3.5 sm:grid-cols-3">
            <div>
              <label class="mb-1.5 block text-xs font-semibold text-muted-soft">{{ __('Status') }}</label>
              <select :name="`copies[${index}][status]`" x-model="copy.status" class="h-10 w-full appearance-none rounded-md border border-hairline bg-input pl-3 pr-9 text-sm text-ink">
                @foreach ($copyStatuses as $value => $label)
                  <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
              </select>
            </div>
            <div>
              <label class="mb-1.5 block text-xs font-semibold text-muted-soft">{{ __('Quantity') }}</label>
              <input type="number" step="1" min="1" :name="`copies[${index}][quantity]`" x-model="copy.quantity" class="h-10 w-full rounded-md border border-hairline bg-input px-3 text-sm text-ink" />
            </div>
            <div>
              <label class="mb-1.5 block text-xs font-semibold text-muted-soft">{{ __('Est. value') }}</label>
              <input type="number" step="0.01" min="0" placeholder="0.00" :name="`copies[${index}][estimated_value]`" x-model="copy.estimated_value" class="h-10 w-full rounded-md border border-hairline bg-input px-3 text-sm text-ink placeholder-muted-soft" />
            </div>
          </div>

          <div class="mt-3.5">
            <label class="mb-1.5 block text-xs font-semibold text-muted-soft">{{ __('Identifier') }}</label>
            <input type="text" placeholder="{{ __('e.g. CGC 9.8 serial') }}" :name="`copies[${index}][identifier]`" x-model="copy.identifier" class="h-10 w-full rounded-md border border-hairline bg-input px-3 text-sm text-ink placeholder-muted-soft" />
          </div>

          <div class="mt-3.5">
            <label class="mb-1.5 block text-xs font-semibold text-muted-soft">{{ __('Note') }}</label>
            <textarea rows="2" placeholder="{{ __('Anything specific to this copy…') }}" :name="`copies[${index}][note]`" x-model="copy.note" class="w-full rounded-md border border-hairline bg-input px-3 py-2 text-sm text-ink placeholder-muted-soft"></textarea>
          </div>
        </div>
      </template>
    </div>

    <button type="button" x-on:click="addCopy()" class="inline-flex items-center gap-1.5 rounded-md border border-dashed border-hairline px-3.5 py-2 text-[13px] font-semibold text-muted transition-colors hover:text-ink">
      @svg('lucide-plus', 'size-3.5')
      {{ __('Add another copy') }}
    </button>

    <div class="flex items-center justify-end gap-3 pt-2">
      <x-button.secondary href="{{ $item ? route('items.show', [$catalog, $item]) : route('collections.show', $catalog) }}" turbo="true">
        {{ __('Cancel') }}
      </x-button.secondary>

      <x-button type="submit" x-bind:disabled="!canSubmit" data-test="{{ $item ? 'save-item-button' : 'add-item-button' }}">
        {{ $item ? __('Save changes') : __('Add item') }}
      </x-button>
    </div>
  </x-form>
</div>
