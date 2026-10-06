{{-- The save bar of a form that is not a Vue app: one for every width, fixed to the bottom of a
     phone and sticky at the bottom of the form on a desktop, as on the event form. Its status line
     says what is unsaved (by tab, each a link to it), what Save will remove, or which tabs a
     refused save wants looked at. Cancel asks before it throws anything away.

     It is a Vue island of its own: there is nothing server-rendered inside it to re-create, and
     no text of anyone's in its markup. The page tells it what happened through events
     (formkit:dirty, formkit:removes, formkit:errors, formkit:saving), which partials/form-kit-script
     and the page's own script dispatch.

     Must sit inside the <form> it saves; partials/form-save-bar-script goes with the page's other
     scripts, after partials/form-kit-script. --}}
<div class="event-save-spacer"></div>
<div class="event-save-bar" id="form-save-bar">
    <div class="event-save-bar-inner">
        <div class="event-save-status" aria-live="polite">
            <span v-cloak v-if="confirmingDiscard">{{ __('messages.discard_unsaved_changes') }}</span>
            <span v-cloak v-else-if="status.kind === 'tabs'" :class="{ 'event-save-strong': status.strong }">
                <span v-text="status.label + ':'"></span>
                <template v-for="(tab, index) in status.tabs" :key="tab.id + ':' + index">
                    <span v-if="index > 0" aria-hidden="true">&middot;</span>
                    <button type="button" class="event-link" @click="goToTab(tab.id)" v-text="tab.label"></button>
                </template>
            </span>
            <span v-cloak v-else :class="{ 'event-save-quiet': status.quiet }" v-text="status.text"></span>
        </div>

        <div class="event-save-actions" v-show="! confirmingDiscard">
            <button type="button" class="event-bar-text" @click="cancel">{{ __('messages.cancel') }}</button>
            <x-brand-button type="submit" class="event-bar-save" v-bind:disabled="saving" v-bind:class="{ 'is-idle': ! dirty }">
                <span v-if="waiting" v-cloak>@{{ waiting }}</span>
                <span v-else-if="saving" v-cloak>{{ __('messages.saving') }}</span>
                <span v-else>{{ __('messages.save') }}</span>
            </x-brand-button>
        </div>
        <div class="event-save-actions" v-cloak v-show="confirmingDiscard">
            <button type="button" class="event-bar-text" @click="confirmingDiscard = false">{{ __('messages.keep_editing') }}</button>
            <button type="button" class="event-bar-quiet" @click="discard">{{ __('messages.discard') }}</button>
        </div>
        {{-- The page's own cancel (layouts/app-admin: back, or the fallback URL), which the bar's
             Cancel calls once it knows nothing unsaved is being thrown away. --}}
        <x-cancel-button id="form-cancel-real" class="hidden" tabindex="-1" aria-hidden="true" />
    </div>
</div>
