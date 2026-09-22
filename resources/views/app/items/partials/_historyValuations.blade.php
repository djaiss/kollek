{{-- The valuations logged against a copy, most recent first. --}}

@use('App\Helpers\Money')

@php
  $user = auth()->user();
  $canManage = $user->account->allowsManagementBy($user);
  $valuations = $selectedCopy->valuations;
  $latestValuation = $valuations->first();
  $maxAmount = (int) $valuations->max('amount');
@endphp

<div x-data="{ adding: false }">
  <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
    <div class="min-w-0">
      <div class="flex items-center gap-2">
        <p class="text-lg font-semibold text-ink">{{ __('Valuations') }}</p>
        <x-help id="history.valuations" />
      </div>
      <p class="mt-1 max-w-xl text-[13px] leading-relaxed text-muted">{{ __('Append-only value estimates over time. The latest is shown as the current estimated value.') }}</p>
    </div>

    @if ($canManage)
      <x-button.secondary type="button" x-on:click="adding = ! adding" class="shrink-0 !h-9 !px-4 text-[13px]" data-test="new-valuation-{{ $selectedCopy->id }}">
        <x-slot:icon>
          <x-lucide-plus class="size-4" />
        </x-slot>
        {{ __('Valuation') }}
      </x-button.secondary>
    @endif
  </div>

  @if ($canManage)
    <div x-show="adding" x-cloak class="mb-5">
      @include('app.items.partials._valuationForm', [
          'formId' => 'add-valuation-'.$selectedCopy->id,
          'action' => route('valuations.create', [$catalog, $item, $selectedCopy]),
          'method' => 'post',
          'openVar' => 'adding',
          'submitLabel' => __('Add valuation'),
          'dataTest' => 'create-valuation-form-'.$selectedCopy->id,
          'valuation' => null,
      ])
    </div>
  @endif

  @if ($latestValuation)
    <div class="mb-5 flex flex-wrap items-start justify-between gap-4 rounded-xl border border-hairline bg-card/40 px-5 py-4" data-test="current-value-{{ $selectedCopy->id }}">
      <div class="min-w-0">
        <p class="mb-1 text-xs text-muted-soft">{{ __('Current estimated value') }}</p>
        <p class="text-4xl font-bold tracking-tight text-ink">{{ Money::format($latestValuation->amount, $latestValuation->currency_code) }}</p>
      </div>

      <div class="text-right">
        <p class="text-sm font-semibold text-ink">{{ $latestValuation->type->label() }}</p>
        <p class="mt-0.5 text-[13px] text-muted-soft">{{ $latestValuation->valuer ? $latestValuation->valuer.' · ' : '' }}{{ $latestValuation->valued_at->isoFormat('MMM YYYY') }}</p>
      </div>
    </div>
  @endif

  @if ($valuations->isNotEmpty())
    <div class="rounded-xl border border-hairline">
      @foreach ($valuations as $valuation)
        @php
          $percent = $maxAmount > 0 ? round(($valuation->amount / $maxAmount) * 100) : 0;
          $isCurrent = $valuation->id === $latestValuation?->id;
        @endphp

        <div class="group border-b border-hairline last:border-b-0" x-data="{ editing: false }" data-test="valuation-{{ $valuation->id }}">
          <div class="px-5 py-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
              <div class="flex min-w-0 flex-wrap items-center gap-2.5">
                <span class="text-xl font-bold tracking-tight text-ink" data-test="valuation-amount-{{ $valuation->id }}">{{ Money::format($valuation->amount, $valuation->currency_code) }}</span>

                <span class="rounded-md bg-card px-2 py-0.5 text-[11px] font-semibold tracking-wide text-muted uppercase" data-test="valuation-type-{{ $valuation->id }}">{{ $valuation->type->label() }}</span>

                @if ($isCurrent)
                  <span class="rounded-md bg-brand/10 px-2 py-0.5 text-[11px] font-semibold tracking-wide text-brand uppercase" data-test="valuation-current-{{ $valuation->id }}">{{ __('Current') }}</span>
                @endif
              </div>

              <div class="flex shrink-0 items-center gap-1.5">
                @if ($canManage)
                  <button type="button" x-on:click="editing = ! editing" class="flex size-7 items-center justify-center rounded-md text-muted-soft opacity-0 transition hover:bg-card hover:text-ink group-hover:opacity-100 focus:opacity-100" aria-label="{{ __('Edit valuation') }}" data-test="edit-valuation-{{ $valuation->id }}">
                    @svg('lucide-pencil', 'size-3.5')
                  </button>
                @endif

                <span class="ml-1 shrink-0 text-[13px] text-muted-soft">{{ $valuation->valued_at->isoFormat('MMM YYYY') }}</span>
              </div>
            </div>

            <div class="mt-3 h-2 overflow-hidden rounded-full bg-hairline-soft">
              <div class="h-full rounded-full bg-brand transition-all" style="width: {{ $percent }}%"></div>
            </div>

            <div class="mt-3 flex flex-wrap items-center gap-x-2 gap-y-1 text-[13px] text-muted">
              <span class="truncate" data-test="valuation-valuer-{{ $valuation->id }}">{{ $valuation->valuer ?? __('No valuer recorded') }}</span>

              @if ($valuation->method)
                <span class="text-muted-soft">·</span>
                <span class="truncate">{{ $valuation->method }}</span>
              @endif

              <span class="text-muted-soft">·</span>

              <span class="flex items-center gap-1.5" data-test="valuation-confidence-{{ $valuation->id }}">
                <span class="size-2 shrink-0 rounded-full" style="background-color: {{ $valuation->confidence->color() }}"></span>
                {{ __(':confidence confidence', ['confidence' => mb_strtolower($valuation->confidence->label())]) }}
              </span>

              @if ($valuation->reference_number)
                <span class="text-muted-soft">·</span>
                <span class="truncate font-mono text-xs">{{ $valuation->reference_number }}</span>
              @endif

              @if ($valuation->source_url)
                <span class="text-muted-soft">·</span>
                <a href="{{ $valuation->source_url }}" target="_blank" rel="noopener noreferrer" class="font-semibold text-brand hover:underline" data-test="valuation-source-{{ $valuation->id }}">{{ __('Source') }}</a>
              @endif
            </div>

            @if ($valuation->note)
              <p class="mt-2 text-[13px] leading-relaxed text-muted-soft">{{ $valuation->note }}</p>
            @endif
          </div>

          @if ($canManage)
            <div x-show="editing" x-cloak class="border-t border-hairline bg-card/40 p-4">
              @include('app.items.partials._valuationForm', [
                  'formId' => 'edit-valuation-'.$valuation->id,
                  'action' => route('valuations.update', [$catalog, $item, $selectedCopy, $valuation]),
                  'deleteAction' => route('valuations.destroy', [$catalog, $item, $selectedCopy, $valuation]),
                  'method' => 'put',
                  'openVar' => 'editing',
                  'submitLabel' => __('Save'),
                  'dataTest' => 'edit-valuation-form-'.$valuation->id,
                  'valuation' => $valuation,
              ])
            </div>
          @endif

          <div class="border-t border-hairline px-5 py-4">
            <p class="mb-2.5 text-[11px] font-semibold tracking-wide text-muted-soft uppercase">{{ __('Documents') }}</p>
            @include('app.items.partials._documentsFor', ['documentable' => $valuation, 'catalog' => $catalog, 'item' => $item, 'selectedCopy' => $selectedCopy, 'canManage' => $canManage])
          </div>
        </div>
      @endforeach
    </div>
  @else
    <div x-show="! adding" class="rounded-xl border border-hairline">
      <x-empty-state data-test="no-valuations-{{ $selectedCopy->id }}">
        <x-slot:icon>
          <x-lucide-clock class="size-6 text-muted" />
        </x-slot>

        {{ __('No valuation has been recorded for this copy yet.') }}
      </x-empty-state>
    </div>
  @endif
</div>
