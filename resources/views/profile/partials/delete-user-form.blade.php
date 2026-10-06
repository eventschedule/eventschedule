<section>
    @include('profile.partials.heading')
    <p class="form-kit-lead">{{ __('messages.once_your_account_is_deleted') }}</p>

    @php
        // roles.user_id, matching what ProfileController::destroy() cancels.
        $hasPaidPlan = \App\Models\Role::where('user_id', auth()->id())
            ->whereNotNull('stripe_id')
            ->get()
            ->contains(fn ($ownedRole) => $ownedRole->hasLiveBilling());
    @endphp

    @if (is_demo_mode())
        @include('profile.partials.notice', ['noticeDemo' => true, 'noticeClass' => 'mb-0'])
    @else
    {{-- Said before the dialog, not only inside it: that it is for good, and that a paid plan
         goes with it. --}}
    @include('profile.partials.notice', ['noticeText' => __('messages.settings_delete_account_what_goes').($hasPaidPlan ? ' '.__('messages.delete_account_subscription_warning') : '')])

    {{-- data-modal-open is the layout's plain trigger for the modal below; this button was an
         Alpine island of its own for the same click. --}}
    <x-danger-button type="button" class="settings-danger" data-modal-open="confirm-user-deletion">{{ __('messages.delete_account') }}</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable
        x-on:open-modal.window="if ($event.detail === 'confirm-user-deletion') { $el.querySelector('form')?.reset(); }">
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6" id="delete-account-form" data-no-dirty>
            @csrf
            @method('delete')

            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('messages.are_you_sure_you_want_to_delete_your_account') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {{ __('messages.once_your_account_is_deleted') }}
            </p>

            @if ($hasPaidPlan)
            <div class="mt-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3 flex gap-3">
                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                </svg>
                <p class="text-sm text-amber-800 dark:text-amber-200">{{ __('messages.delete_account_subscription_warning') }}</p>
            </div>
            @endif

            <div class="mt-6">
                <x-input-label for="feedback" :value="__('messages.deletion_feedback_label')" />
                <textarea
                    id="feedback"
                    name="feedback"
                    rows="3"
                    dir="auto"
                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm"
                    placeholder="{{ __('messages.deletion_feedback_placeholder') }}"
                >{{ old('feedback') }}</textarea>
            </div>

            @if(auth()->user()->hasPassword())
            <div class="mt-6">
                <x-input-label for="password" value="{{ __('messages.password') }}" class="sr-only" />

                {{-- w-full, not the w-3/4 this used to carry: x-password-input wraps the field in a
                     full-width relative div and pins its reveal toggle to the wrapper's end edge, so
                     a narrower input left the icon floating in the empty quarter beside it. --}}
                <x-password-input
                    id="password"
                    name="password"
                    class="mt-1 block w-full"
                    placeholder="{{ __('messages.password') }}"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>
            @endif

            <div class="mt-6 flex justify-end" x-data="{ submitting: false }">
                {{-- A plain button in the secondary link's clothes: the shared secondary and danger
                     buttons are capitals with wide tracking, which read as another product beside
                     this page's Save buttons. The red one keeps its component and is recased by
                     the page's styles (.settings-danger). --}}
                <button type="button" x-on:click="$dispatch('close')"
                    class="ap-secondary-btn inline-flex items-center justify-center px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-base text-gray-900 dark:text-gray-100 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                    {{ __('messages.cancel') }}
                </button>

                <x-danger-button class="ms-3 settings-danger" x-on:click="submitting = true; $el.closest('form').submit()" x-bind:disabled="submitting">
                    {{ __('messages.delete_account') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
    @endif
</section>

<script {!! nonce_attr() !!}>
document.addEventListener('DOMContentLoaded', function() {
    var deleteForm = document.getElementById('delete-account-form');
    if (deleteForm) {
        deleteForm.addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                return false;
            }
        });
    }
});
</script>
