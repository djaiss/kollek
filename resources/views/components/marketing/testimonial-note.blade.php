@props (['testimonial', 'tilt' => ''])

@php
  $safeLink = $testimonial->safeLink();

  /*
   * Each note is pinned to the wall on its own coloured paper, with a matching
   * accent for the avatar and a strip of tape holding the top. The palette entry
   * is picked from the id, so a note keeps the same colour wherever it appears.
   * Papers and tape are theme tokens (resources/css/app.css) so they flip in dark.
   */
  $palette = [
      ['paper' => 'var(--note-1)', 'accent' => '#fb923c', 'tape' => 3],
      ['paper' => 'var(--note-2)', 'accent' => '#ec4899', 'tape' => -4],
      ['paper' => 'var(--note-3)', 'accent' => '#3b82f6', 'tape' => 2],
      ['paper' => 'var(--note-4)', 'accent' => '#34d399', 'tape' => -3],
      ['paper' => 'var(--note-5)', 'accent' => '#8b5cf6', 'tape' => 4],
      ['paper' => 'var(--note-6)', 'accent' => '#f59e0b', 'tape' => -2],
  ];

  $note = $palette[($testimonial->id ?? crc32((string) $testimonial->name)) % count($palette)];
@endphp

<div class="mb-5 break-inside-avoid">
  <div class="relative rounded border p-6 shadow-[0_10px_24px_rgba(17,17,17,0.09),0_2px_6px_rgba(17,17,17,0.05)] transition-transform duration-200 hover:rotate-0 {{ $tilt }}" style="background:{{ $note['paper'] }}; border-color:var(--tape-edge);">
    {{-- A strip of tape holding the note to the wall. --}}
    <div class="absolute -top-[9px] left-1/2 -ml-[30px] h-[18px] w-[60px] rounded-[2px] border backdrop-blur-[1px]" style="background:var(--tape); border-color:var(--tape-edge); transform:rotate({{ $note['tape'] }}deg);" aria-hidden="true"></div>

    <div class="font-serif text-4xl leading-none text-hairline">&ldquo;</div>
    <p class="mt-1.5 text-[15px] leading-relaxed text-ink">{{ $testimonial->body }}</p>
    <div class="mt-4 flex items-center gap-2.5 border-t border-dashed pt-3.5" style="border-color: var(--tape-edge)">
      <span class="flex size-7 shrink-0 items-center justify-center rounded-full text-xs font-semibold text-white" style="background:{{ $note['accent'] }};">{{ $testimonial->initial() }}</span>
      <div class="min-w-0 flex-1">
        @if ($safeLink)
          <a href="{{ $safeLink }}" target="_blank" rel="nofollow ugc noopener" class="text-sm font-semibold text-ink underline decoration-hairline underline-offset-2 hover:decoration-ink">{{ $testimonial->name }}</a>
        @else
          <span class="text-sm font-semibold text-muted">{{ $testimonial->name }}</span>
        @endif
      </div>
      @if ($testimonial->published_at)
        <span class="shrink-0 text-xs font-medium text-muted-soft">{{ $testimonial->published_at->isoFormat('MMM D, YYYY') }}</span>
      @endif
    </div>
  </div>
</div>
