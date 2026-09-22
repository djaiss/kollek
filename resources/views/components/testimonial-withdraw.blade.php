{{-- The panel letting a member take their testimonial back at any time. --}}
<div class="flex flex-col gap-3 rounded-lg border border-hairline bg-sidebar px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
  <div class="flex items-start gap-2.5">
    @svg('lucide-shield-check', 'mt-0.5 size-4 shrink-0 text-muted')
    <p class="text-[13px] leading-relaxed text-muted">
      {{ __('This is always yours to take back. You can revoke your testimonial at any time, and it is removed from the marketing site right away.') }}
    </p>
  </div>
  <x-form
    method="delete"
    :action="route('settings.testimonials.destroy')"
    class="shrink-0"
    onsubmit="return confirm('{{ __('Remove your testimonial? This takes it off the marketing site and cannot be undone.') }}')"
  >
    <x-button.secondary type="submit">
      @svg('lucide-trash-2', 'size-4')
      {{ __('Remove my testimonial') }}
    </x-button.secondary>
  </x-form>
</div>
