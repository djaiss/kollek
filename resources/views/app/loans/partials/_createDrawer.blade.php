{{-- The drawer for creating a loan, picking the collection, item, copy, then the loan details. --}}
@use('App\Enums\LoanDirection')

@php
  $closeUrl = route('loans.show', ['direction' => $direction->slug(), 'tab' => $tab]);
  $labelClasses = 'block text-[11px] font-semibold tracking-wide text-muted-soft uppercase';
  $inputClasses = 'mt-1.5 h-10 w-full rounded-md border border-hairline bg-input px-3 text-sm text-ink placeholder-muted-soft';
@endphp

<div
  class="fixed inset-0 z-40"
  x-data="loanCreate({ catalog: @js($createCatalog), direction: '{{ $direction === LoanDirection::Outgoing ? 'outgoing' : 'incoming' }}' })"
  data-test="loan-create-drawer"
>
  <a href="{{ $closeUrl }}" data-turbo="true" class="absolute inset-0 bg-black/30" aria-label="{{ __('Close') }}"></a>

  <div class="absolute inset-y-0 right-0 flex w-full max-w-[520px] flex-col overflow-y-auto border-l border-hairline bg-page shadow-xl">
    <div class="flex items-start justify-between gap-3 border-b border-hairline px-5 py-4">
      <div>
        <h2 class="text-base font-semibold text-ink">{{ __('New loan') }}</h2>
        <p class="text-[13px] text-muted">{{ __('Pick the object, then the loan details.') }}</p>
      </div>
      <a href="{{ $closeUrl }}" data-turbo="true" class="text-muted hover:text-ink"><x-lucide-x class="size-5" /></a>
    </div>

    <form method="post" x-bind:action="actionUrl" class="flex flex-col gap-4 px-5 py-5" data-test="create-loan-form">
      @csrf
      <input type="hidden" name="from" value="loans" />
      <input type="hidden" name="direction" x-model="direction" />
      <input type="hidden" name="status" value="active" />

      <div>
        <span class="{{ $labelClasses }}">{{ __('Direction') }}</span>
        <div class="mt-1.5 inline-flex rounded-lg border border-hairline p-1">
          <button type="button" @click="direction = 'outgoing'" :class="direction === 'outgoing' ? 'bg-card text-ink' : 'text-muted'" class="rounded-md px-3 py-1.5 text-sm font-medium">{{ __('Lend out') }}</button>
          <button type="button" @click="direction = 'incoming'" :class="direction === 'incoming' ? 'bg-card text-ink' : 'text-muted'" class="rounded-md px-3 py-1.5 text-sm font-medium">{{ __('Borrow in') }}</button>
        </div>
      </div>

      <div>
        <label class="{{ $labelClasses }}">{{ __('Collection') }}</label>
        <select x-model="collectionId" class="{{ $inputClasses }}" data-test="create-collection">
          <option value="">{{ __('Choose a collection…') }}</option>
          <template x-for="c in catalog" :key="c.id">
            <option :value="c.id" x-text="c.name"></option>
          </template>
        </select>
      </div>

      <div>
        <label class="{{ $labelClasses }}">{{ __('Item') }}</label>
        <select x-model="itemId" :disabled="!collectionId" class="{{ $inputClasses }} disabled:opacity-50" data-test="create-item">
          <option value="">{{ __('Choose an item…') }}</option>
          <template x-for="i in items" :key="i.id">
            <option :value="i.id" x-text="i.name"></option>
          </template>
        </select>
      </div>

      <div>
        <label class="{{ $labelClasses }}">{{ __('Copy') }}</label>
        <select x-model="copyId" :disabled="!itemId" class="{{ $inputClasses }} disabled:opacity-50" data-test="create-copy">
          <option value="">{{ __('Choose a copy…') }}</option>
          <template x-for="cp in copies" :key="cp.id">
            <option :value="cp.id" x-text="cp.label + (cp.openOut ? ' {{ __('(on loan)') }}' : '')"></option>
          </template>
        </select>
      </div>

      <div x-show="overlap" x-cloak class="rounded-lg border border-error/30 bg-error/10 px-3.5 py-3 text-[13px] text-error">
        {{ __('This copy already has an open outgoing loan. :name blocks a second one, so return the current loan first.', ['name' => config('app.name')]) }}
      </div>

      <div>
        <label class="{{ $labelClasses }}" x-text="direction === 'outgoing' ? '{{ __('Lend to') }}' : '{{ __('Borrow from') }}'"></label>
        <input name="party" required maxlength="255" placeholder="{{ __('Person, gallery, museum…') }}" class="{{ $inputClasses }}" data-test="create-party" />
        <x-error :messages="$errors->get('party')" class="mt-2" />
        <x-error :messages="$errors->get('copy')" class="mt-2" />
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="{{ $labelClasses }}">{{ __('Loaned on') }}</label>
          <input name="loaned_at" type="date" value="{{ now()->toDateString() }}" class="{{ $inputClasses }}" />
        </div>
        <div>
          <label class="{{ $labelClasses }}">{{ __('Due on') }}</label>
          <input name="due_at" type="date" x-model="dueAt" :disabled="openEnded" class="{{ $inputClasses }} disabled:opacity-50" />
        </div>
      </div>
      <label class="flex items-center gap-2 text-[13px] text-muted">
        <input type="checkbox" x-model="openEnded" @change="if (openEnded) dueAt = ''" class="rounded border-hairline" />
        {{ __('Open-ended loan (no due date)') }}
      </label>

      <div>
        <label class="{{ $labelClasses }}">{{ __('Condition out') }}</label>
        <select name="item_condition_out_id" class="{{ $inputClasses }}">
          <option value="">{{ __('Not recorded') }}</option>
          @foreach ($conditions as $id => $name)
            <option value="{{ $id }}">{{ $name }}</option>
          @endforeach
        </select>
      </div>

      <div>
        <label class="{{ $labelClasses }}">{{ __('Deposit') }}</label>
        <div class="mt-1.5 flex gap-2">
          <select name="currency" class="h-10 rounded-md border border-hairline bg-input px-2 text-sm text-ink">
            @foreach ($currencies as $code => $label)
              <option value="{{ $code }}">{{ $label }}</option>
            @endforeach
          </select>
          <input name="deposit_amount" type="number" step="0.01" min="0" placeholder="0.00" class="h-10 flex-1 rounded-md border border-hairline bg-input px-3 text-sm text-ink" />
        </div>
      </div>

      <label class="flex items-start gap-2 text-[13px] text-muted">
        <input type="checkbox" name="include_in_provenance" value="1" class="mt-0.5 rounded border-hairline" />
        {{ __('Include in the object\'s provenance (institutional loan / exhibition)') }}
      </label>

      <div class="flex justify-end gap-2 border-t border-hairline pt-4">
        <x-button.secondary :href="$closeUrl">{{ __('Cancel') }}</x-button.secondary>
        <x-button type="submit" x-bind:disabled="!canSubmit" data-test="submit-create-loan">{{ __('Create loan') }}</x-button>
      </div>
    </form>
  </div>
</div>

<script>
  function loanCreate(config) {
    return {
      catalog: config.catalog,
      direction: config.direction,
      collectionId: '',
      itemId: '',
      copyId: '',
      dueAt: '',
      openEnded: false,
      get items() {
        const c = this.catalog.find(c => String(c.id) === String(this.collectionId));
        return c ? c.items : [];
      },
      get copies() {
        const i = this.items.find(i => String(i.id) === String(this.itemId));
        return i ? i.copies : [];
      },
      get selectedCopy() {
        return this.copies.find(cp => String(cp.id) === String(this.copyId)) || null;
      },
      get overlap() {
        return this.direction === 'outgoing' && this.selectedCopy !== null && this.selectedCopy.openOut;
      },
      get canSubmit() {
        return this.copyId !== '' && !this.overlap;
      },
      get actionUrl() {
        if (!this.copyId) return '';
        return `/collections/${this.collectionId}/items/${this.itemId}/copies/${this.copyId}/loans`;
      },
    };
  }
</script>
