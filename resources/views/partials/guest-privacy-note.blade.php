{{-- Art. 13 at the point of collection, for a form that takes a buyer's or guest's details with
     no account and so no terms box: who receives them, and the privacy policy. The schedule is the
     controller of these details and we process them for it (privacy policy, clause 01).
     v-pre: the schedule name is owner-written text inside a Vue mount. --}}
@php($privacyNoteRole = $privacyNoteRole ?? null)
@if ($privacyNoteRole)
<p class="mt-4 text-xs text-gray-500 dark:text-gray-400" v-pre>
    {{ __('messages.guest_privacy_note', ['schedule' => $privacyNoteRole->translatedName()]) }}
    <x-link href="{{ policy_url('privacy') }}" target="_blank">{{ __('messages.privacy_policy') }}</x-link>
</p>
@endif
