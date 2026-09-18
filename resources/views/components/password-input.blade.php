{{--
    A password field with a reveal toggle.

    Inventing a password blind on a phone is a real source of drop-off, and this component backs
    every password field in the app - signup, login, the reset page, the subscriber claim and the
    optional field on the confirm page - so the toggle lands on all of them at once.

    The wrapper is position:relative, so a caller that needs the field to fill its row should keep
    passing `w-full` as it already does; the class merge adds only the end padding that stops the
    text running under the button.

    The click handler lives in resources/js/app.js, delegated from the document. Not a script here:
    the app uses Vue's full build with the runtime template compiler, and several of these fields
    sit inside an element Vue mounts, where a <script> tag is markup Vue tries to compile rather
    than a script. Delegation also covers a field rendered into a modal later.
--}}
@props(['disabled' => false])

<div class="relative">
    <x-text-input
        {{ $attributes->merge(['type' => 'password', 'class' => 'pe-10']) }}
        :disabled="$disabled"
        data-password-field
    />

    {{-- type="button" is load-bearing: the default inside a form is submit, so a bare <button>
         here would submit the form on the first tap.

         Hidden from assistive tech and from the tab order by default is the wrong trade - somebody
         using a screen reader benefits from this most - so it is a real focusable button with an
         aria-label that flips with the state. --}}
    {{-- Focusable, deliberately. It carried tabindex="-1" at first, which contradicted the sentence
         above it and took the toggle away from exactly the people it helps most: a keyboard-only
         user could not reveal what they had typed, and the focus: classes and the flipping
         aria-label below were both dead. --}}
    <button type="button"
        data-password-toggle
        aria-label="{{ __('messages.show_password') }}"
        data-label-show="{{ __('messages.show_password') }}"
        data-label-hide="{{ __('messages.hide_password') }}"
        class="absolute inset-y-0 end-0 flex items-center px-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none focus:text-gray-600 dark:focus:text-gray-300">
        <svg data-password-icon="show" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
        </svg>
        <svg data-password-icon="hide" class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243" />
        </svg>
    </button>
</div>
