{{-- The Tickets tab for a team member the owner's switch binds (App\Services\TicketPresets::lockedFor()).
     The presets, and what the event will have; no price, no mode, no Payment, Checkout or Sales rules rows. Their
     save posts the preset and whether it was pressed, never ticket fields (EventRepo::saveEvent()
     would drop them). Inside the form's Vue mount: names reach the page through v-text, and every
     sentence that carries the owner's name is v-pre. --}}
<div class="preset-team-view">
    <input type="hidden" name="ticket_panel" value="presets">
    <input type="hidden" name="ticket_preset_id" :value="presetLit || ''">
    <input type="hidden" name="ticket_preset_apply" :value="presetApply ? 1 : 0">

    @if ($presetState['copied_from'])
    <p class="mb-3 text-sm text-gray-500 dark:text-gray-400" v-pre>{{ $presetCopyNote }}</p>
    @endif
    @if (! $presetState['frozen'])
    <p class="mb-4 text-sm text-gray-500 dark:text-gray-400" v-pre>{{ $presetLeadText }}</p>
    @endif

    <div class="event-tiles" :class="{ 'is-frozen': presetFrozen }" role="group" aria-label="{{ __('messages.ticket_presets') }}">
        <button v-if="presetShowsCustom" type="button" class="event-tile" id="ticket_preset_custom"
            :class="{ 'is-on': presetLit === '' }" :aria-pressed="presetLit === '' ? 'true' : 'false'"
            :disabled="presetFrozen && presetLit !== ''" @click="pickPreset(null)">
            <span class="event-tile-title">{{ __('messages.ticket_preset_custom') }}</span>
            <span class="event-tile-help">{{ __('messages.ticket_preset_custom_help') }}</span>
        </button>
        <button v-for="preset in presets" :key="preset.id" type="button" class="event-tile" :data-preset-tile="preset.id"
            :class="{ 'is-on': presetLit === preset.id }" :aria-pressed="presetLit === preset.id ? 'true' : 'false'"
            :disabled="(presetFrozen && presetLit !== preset.id) || ! preset.usable" @click="pickPreset(preset)">
            <span class="event-tile-title"><bdi v-text="presetTileTitle(preset)"></bdi></span>
            <span class="event-tile-help"><bdi v-text="presetTileHelp(preset)"></bdi></span>
        </button>
    </div>

    <div v-for="preset in presetsUnusable" :key="'unusable-' + preset.id" class="mt-3 flex items-start gap-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3">
        <svg class="w-5 h-5 flex-shrink-0 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
        <p class="text-sm text-amber-800 dark:text-amber-200"><bdi v-text="preset.reason"></bdi></p>
    </div>

    <div v-if="presetStaleShown" class="mt-3 preset-note" id="ticket-preset-stale">
        <span v-text="presetWords.changed"></span>
        <button type="button" class="event-link" @click="refreshPreset()" v-text="presetWords.use_current"></button>
    </div>

    @if ($presetState['frozen'])
    <div class="mt-3 preset-note">
        <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>
        <span v-pre>{{ $presetFrozenText }}</span>
    </div>
    @endif

    <div class="preset-picked" id="ticket-preset-picked">
        <dl>
            <dt>{{ __('messages.tickets') }}</dt>
            <dd><bdi v-text="presetPicked.tickets"></bdi></dd>
            <template v-if="presetPicked.payment">
                <dt>{{ __('messages.payment') }}</dt>
                <dd><bdi v-text="presetPicked.payment"></bdi></dd>
            </template>
        </dl>
    </div>

    @if (! $presetState['frozen'])
    <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
        <span v-pre>{{ $presetNeedOther[0] }}</span><button type="button" class="event-link" style="margin-inline-end: 0" @click="goToTab('section-listing')">{{ __('messages.listing') }}</button><span v-pre>{{ $presetNeedOther[1] }}</span>
    </p>
    @endif
</div>
