<x-app-admin-layout>

    {{-- Handing a schedule to someone else: one small form that hangs from the Team tab. What
         changes is said before the field, and the button that does it is the last thing and red. --}}
    @php $teamUrl = route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'team']); @endphp

    <div class="page-shell page-col is-narrow">
        <x-page-header :title="$title" :lead="__('messages.transfer_ownership_intro')" :back="$teamUrl" :back-label="$role->name" />

        {{-- data-confirm goes on the FORM: layouts/app.blade.php delegates on form[data-confirm], --}}
        {{-- and the CSP blocks inline on* attributes, so a button-level handler would never fire. --}}
        <form method="post" action="{{ route('role.transfer.store', ['subdomain' => $role->subdomain]) }}"
            data-confirm="{{ __('messages.transfer_ownership_confirm', ['name' => $role->name]) }}">
            @csrf
            @method('post')

            <div class="ap-card rounded-xl page-card">
                <div class="page-form-fields">
                    @if ($openTransfer)
                    <x-page-notice>
                        {{ __('messages.transfer_ownership_replaces_open', ['email' => $openTransfer->to_email]) }}
                    </x-page-notice>
                    @endif

                    <x-page-notice :title="__('messages.transfer_ownership_warning_title')">
                        <ul class="mt-2 space-y-1 list-disc ms-4">
                            <li>{{ __('messages.transfer_ownership_warning_access') }}</li>
                            <li>{{ __('messages.transfer_ownership_warning_events') }}</li>
                            @if (config('app.hosted'))
                            <li>{{ __('messages.transfer_ownership_warning_billing') }}</li>
                            @endif
                            <li>{{ __('messages.transfer_ownership_warning_calendar') }}</li>
                        </ul>
                    </x-page-notice>

                    <div>
                        <x-input-label for="email" :value="__('messages.email') . ' *'" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                            :value="old('email')" required autofocus autocomplete="off" />
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            {{ __('messages.transfer_ownership_email_help') }}
                        </p>
                        <x-input-error class="mt-2" :messages="$errors->get('email')" />
                    </div>

                    @if ($role->isEnterprise())
                    <div>
                        <x-toggle
                            name="remove_me"
                            :label="__('messages.transfer_ownership_remove_me')"
                            :help="__('messages.transfer_ownership_remove_me_help')"
                            :checked="old('remove_me', '1') == '1'" />
                    </div>
                    @endif
                </div>
            </div>

            <div class="page-form-actions">
                <x-secondary-link :href="$teamUrl">{{ __('messages.cancel') }}</x-secondary-link>
                <x-danger-button>{{ __('messages.transfer_ownership_send') }}</x-danger-button>
            </div>
        </form>
    </div>

</x-app-admin-layout>
