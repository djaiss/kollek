<x-app-layout>
  <x-slot:title>
    Instance overview
  </x-slot>

  @php
    $peak = collect($signups)->max('count') ?: 1;

    $tiles = [
      ['label' => 'Accounts', 'value' => $accountCount, 'hint' => $accountsThisMonth.' created this month'],
      ['label' => 'Users', 'value' => $userCount, 'hint' => $activeThisMonth.' active this month'],
      ['label' => 'Collections', 'value' => $catalogCount, 'hint' => null],
      ['label' => 'Items tracked', 'value' => $itemCount, 'hint' => null],
    ];
  @endphp

  <div class="px-6 py-8 lg:px-12 lg:py-10">
    <div class="mx-auto w-full max-w-5xl space-y-8">
      <div>
        <h1 class="text-[22px] font-semibold tracking-tight text-ink">Instance overview</h1>
        <p class="mt-1 text-sm text-muted">Activity across every account on this KolleK instance.</p>
      </div>

      <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        @foreach ($tiles as $tile)
          <div class="rounded-lg border border-hairline bg-canvas p-4">
            <p class="text-xs font-medium tracking-wide text-muted-soft uppercase">{{ $tile['label'] }}</p>
            <p class="mt-2 text-2xl font-semibold text-ink">{{ number_format($tile['value']) }}</p>
            @if ($tile['hint'])
              <p class="mt-1 text-xs text-muted">{{ $tile['hint'] }}</p>
            @endif
          </div>
        @endforeach
      </div>

      <x-box title="New accounts">
        <x-slot:description>
          Accounts created per month over the last year.
        </x-slot>

        <div class="flex h-40 items-end gap-1.5">
          @foreach ($signups as $month)
            <div class="flex flex-1 flex-col items-center gap-1.5">
              <span class="text-xs text-muted">{{ $month['count'] ?: '' }}</span>
              <div
                class="w-full rounded-t bg-accent/70"
                style="height: {{ max(2, (int) round(($month['count'] / $peak) * 100)) }}%"
                title="{{ $month['count'] }}"
              ></div>
              <span class="text-[10px] text-muted-soft">{{ $month['label'] }}</span>
            </div>
          @endforeach
        </div>
      </x-box>

      <x-box title="Not tracked yet">
        <x-slot:description>
          KolleK does not have the data behind these yet.
        </x-slot>

        <ul class="space-y-2.5">
          @foreach (['Billing plans', 'Storage and API quotas', 'User reviews'] as $item)
            <li class="flex items-center justify-between text-sm text-muted">
              {{ $item }}
              <x-soon />
            </li>
          @endforeach
        </ul>
      </x-box>
    </div>
  </div>
</x-app-layout>
