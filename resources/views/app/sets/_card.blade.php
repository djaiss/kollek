@php
  // Sets have no colour of their own, so the dot is derived from the id. It only has to be
  // stable and varied, never meaningful.
  $dots = ['bg-badge-orange', 'bg-badge-violet', 'bg-badge-emerald', 'bg-brand', 'bg-badge-pink', 'bg-warning'];
  $dot = $dots[$set->id % count($dots)];

  // A set without a target has nothing to be complete against, so it shows a plain item count.
  $hasTarget = $set->target_count !== null && $set->target_count > 0;
  $percent = $hasTarget ? min(100, (int) round($set->items_count / $set->target_count * 100)) : 0;
  $isComplete = $hasTarget && $set->items_count >= $set->target_count;
  $missing = $hasTarget ? max(0, $set->target_count - $set->items_count) : 0;
@endphp

<div
  x-data="{
    editing: false,
    editName: @js($set->name),
    name: @js(Str::lower($set->name)),
    get visible() {
      const needle = search.trim().toLowerCase()
      return needle === '' || this.name.includes(needle)
    },
  }"
  x-show="visible"
  data-set-card
  id="set-{{ $set->id }}"
  class="scroll-mt-24 rounded-xl border border-hairline bg-canvas px-5 py-4"
  data-test="set-card-{{ $set->id }}"
>
  <div class="flex items-start gap-3">
    <span class="mt-1 size-3 shrink-0 rounded-sm {{ $dot }}"></span>

    <div class="min-w-0 flex-1">
      <div class="truncate text-base font-semibold text-ink" data-test="set-name-{{ $set->id }}">{{ $set->name }}</div>
      @if ($set->description)
        <div class="mt-0.5 truncate text-[13px] text-muted">{{ $set->description }}</div>
      @endif
      <div class="mt-0.5 text-[13px] text-muted-soft">{{ __('Updated :time', ['time' => $set->updated_at?->diffForHumans() ?? '—']) }}</div>
    </div>

    <div class="flex shrink-0 items-center gap-1.5">
      <button type="button" x-on:click="editing = !editing" class="flex size-8 items-center justify-center rounded-md border border-hairline text-muted hover:bg-card" aria-label="{{ __('Rename set') }}" title="{{ __('Rename set') }}" data-test="edit-set-{{ $set->id }}">
        @svg('lucide-pencil', 'size-3.5')
      </button>

      <x-form method="delete" :action="route('sets.destroy', [$catalog->id, $set->id])" x-target="sets-panel notifications" x-on:ajax:before="confirm('{{ __('Delete this set? This removes the set and its completion tracking. The items themselves stay in your collection. This cannot be undone.') }}') || $event.preventDefault()">
        <button type="submit" class="flex size-8 items-center justify-center rounded-md border border-hairline text-muted hover:bg-card" aria-label="{{ __('Delete set') }}" data-test="delete-set-{{ $set->id }}">
          @svg('lucide-trash-2', 'size-3.5')
        </button>
      </x-form>
    </div>
  </div>

  <div class="mt-3.5 flex items-center gap-4">
    @if ($hasTarget)
      <div class="min-w-0 flex-1">
        <div class="mb-1.5 flex justify-between text-[13px]">
          <span class="text-muted" data-test="set-progress-{{ $set->id }}">{{ __(':owned of :target owned', ['owned' => $set->items_count, 'target' => $set->target_count]) }}</span>
          <span class="font-semibold {{ $isComplete ? 'text-success' : 'text-ink' }}">{{ __(':percent% complete', ['percent' => $percent]) }}</span>
        </div>

        <div class="h-2 overflow-hidden rounded-full bg-hairline-soft">
          <div class="h-full {{ $dot }} transition-all" style="width: {{ $percent }}%"></div>
        </div>
      </div>

      <span class="shrink-0 rounded-full px-3 py-1 text-xs font-medium {{ $isComplete ? 'bg-success/10 text-success' : 'bg-card text-muted' }}" data-test="set-status-{{ $set->id }}">
        {{ $isComplete ? __('Complete') : __(':count missing', ['count' => $missing]) }}
      </span>
    @else
      <span class="text-[13px] text-muted" data-test="set-progress-{{ $set->id }}">{{ trans_choice(':count item|:count items', $set->items_count, ['count' => $set->items_count]) }}</span>
      <span class="shrink-0 rounded-full bg-card px-3 py-1 text-xs font-medium text-muted" data-test="set-status-{{ $set->id }}">{{ __('No target') }}</span>
    @endif
  </div>

  <div x-show="editing" x-cloak class="mt-4 border-t border-hairline-soft pt-4">
    <x-form method="put" :action="route('sets.update', [$catalog->id, $set->id])" data-test="edit-set-form-{{ $set->id }}" x-target="sets-panel notifications" x-on:ajax:after="editing = document.querySelector('[data-test=&quot;edit-set-form-{{ $set->id }}&quot;] .text-error') !== null">
      <div class="mb-3.5 flex flex-wrap gap-3.5">
        <div class="min-w-[180px] flex-1">
          <x-label>{{ __('Name') }}</x-label>
          <input name="name" x-model="editName" placeholder="{{ __('Set name') }}" class="mt-1.5 h-9 w-full rounded-md border border-hairline bg-input px-3 text-sm text-ink" data-test="set-name-input-{{ $set->id }}" />
          <x-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="w-[120px]">
          <div class="flex items-center gap-1.5">
            <x-label>{{ __('Target') }}</x-label>
            <x-help id="sets.target" align="right" />
          </div>
          <input name="target_count" type="number" min="1" value="{{ $set->target_count }}" placeholder="10" class="mt-1.5 h-9 w-full rounded-md border border-hairline bg-input px-3 text-sm text-ink" data-test="set-target-input-{{ $set->id }}" />
          <x-error :messages="$errors->get('target_count')" class="mt-2" />
        </div>
      </div>

      <div class="mb-3.5">
        <x-label>{{ __('Description') }}</x-label>
        <textarea name="description" rows="2" placeholder="{{ __('Optional. What belongs in this set?') }}" class="mt-1.5 w-full rounded-md border border-hairline bg-input px-3 py-2 text-sm text-ink">{{ $set->description }}</textarea>
        <x-error :messages="$errors->get('description')" class="mt-2" />
      </div>

      <div class="flex justify-end gap-2.5">
        <x-button.secondary type="button" x-on:click="editing = false" class="text-[13px]">
          {{ __('Cancel') }}
        </x-button.secondary>

        <x-button type="submit" class="text-[13px]" data-test="save-set-{{ $set->id }}">
          {{ __('Save') }}
        </x-button>
      </div>
    </x-form>
  </div>
</div>
