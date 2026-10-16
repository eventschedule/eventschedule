{{-- The schedule's ticket presets as chips above the Tickets tab's tiles, for anyone the owner's switch
     does not bind (App\Services\TicketPresets). A chip fills the tab (applyPreset()); it stays lit while
     the tab still matches and goes out on the first hand edit. Resting once the event has tickets or a
     seating plan: a preset's types would replace the ones people hold. --}}
<input type="hidden" name="ticket_panel" value="full">
<input type="hidden" name="ticket_preset_id" :value="presetLit || ''">

<div v-if="presets.length" class="event-chips" role="group" aria-label="{{ __('messages.ticket_presets') }}" id="ticket-preset-chips">
    <span class="event-chips-label">{{ __('messages.ticket_preset_label') }}</span>
    <button v-for="preset in presets" :key="preset.id" type="button" class="event-chip" :data-preset-chip="preset.id"
        :class="{ 'is-on': presetLit === preset.id }" :aria-pressed="presetLit === preset.id ? 'true' : 'false'"
        :disabled="presetFrozen" @click="applyPreset(preset)"><bdi v-text="preset.chip"></bdi></button>
    @if ($presetState['may_edit'])
    <a href="{{ $presetState['manage_url'] }}" class="event-row-link ms-auto" style="margin-inline-end: 0">{{ __('messages.ticket_preset_manage') }}</a>
    @endif
</div>
@if (! $presetState['presets'] && $presetState['is_owner'] && $presetState['has_team'])
<div class="event-chips">
    <span class="event-chips-label">{{ __('messages.ticket_preset_label') }}</span>
    <span class="text-sm text-gray-500 dark:text-gray-400">{{ $presetNoneYet[0] }}<a href="{{ $presetState['manage_url'] }}" class="event-row-link" style="margin-inline-end: 0">{{ __('messages.ticket_preset_set_up') }}</a>{{ $presetNoneYet[1] }}</span>
</div>
@endif
{{-- The preset the event was saved with changed since: its chip stays lit (the event is still on
     it, and the team is told the same), and this says so, with the press that takes it as it is now. --}}
<div v-if="presetStaleShown && presetChosen" class="-mt-1 mb-4 preset-note" id="ticket-preset-stale">
    <span v-text="presetWords.changed"></span>
    <button type="button" class="event-link" @click="applyPreset(presetChosen)" v-text="presetWords.use_current"></button>
</div>
@if ($presetState['presets'] && $presetState['frozen'])
<p class="-mt-1 mb-4 text-xs text-gray-500 dark:text-gray-400" v-pre>{{ $presetFrozenText }}</p>
@endif
<div v-if="presetMethodNote" class="mb-4 flex items-start gap-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3">
    <svg class="w-5 h-5 flex-shrink-0 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
    <p class="text-sm text-amber-800 dark:text-amber-200"><bdi v-text="presetMethodNote"></bdi></p>
</div>
