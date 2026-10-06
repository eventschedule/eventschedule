{{-- The button that saves one section, and "Saved" beside it once, after the save that just
     happened. In demo mode the button is there and switched off; the panel at the top of the
     section says why (it used to raise an alert, once per section that had bound a handler).
     $saveLabel, $saveShown, $saveRowId and $saveClass: long names for the reason notice gives. --}}
<div @if (! empty($saveRowId)) id="{{ $saveRowId }}" @endif class="{{ $saveClass ?? 'mt-6' }} settings-save">
    {{-- What the save did, at the start of the line; the button at its end, where the other two
         forms keep Save. The status is first in the markup so the forward button stays last. --}}
    <span class="settings-save-status" role="status">
        @if (! empty($saveShown))
        <span class="event-status is-on form-kit-saved">{{ __('messages.saved') }}</span>
        @endif
    </span>
    <x-brand-button type="submit" class="settings-save-button" :disabled="is_demo_mode()" :title="is_demo_mode() ? __('messages.saving_disabled_demo_mode') : null">
        {{ $saveLabel ?? __('messages.save') }}
    </x-brand-button>
</div>
