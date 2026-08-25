<x-app-admin-layout>

    <h2 class="text-xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:truncate sm:text-2xl sm:tracking-tight">
        {{ $title }}
    </h2>

    {{-- data-confirm goes on the FORM: layouts/app.blade.php delegates on form[data-confirm], --}}
    {{-- and the CSP blocks inline on* attributes, so a button-level handler would never fire. --}}
    <form method="post" action="{{ route('role.transfer.store', ['subdomain' => $role->subdomain]) }}"
        class="mt-6 space-y-4"
        data-confirm="{{ __('messages.transfer_ownership_confirm', ['name' => $role->name]) }}">
        @csrf
        @method('post')

        <div class="py-5">
            <div class="max-w-7xl mx-auto space-y-4">
                <div class="ap-card p-4 sm:p-8 sm:rounded-xl">
                    <div class="max-w-xl">

                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                            {{ $role->name }}
                        </h2>

                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                            {{ __('messages.transfer_ownership_intro') }}
                        </p>

                        @if ($openTransfer)
                        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3 mb-6">
                            <div class="flex gap-3">
                                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                </svg>
                                <p class="text-sm text-amber-800 dark:text-amber-200">
                                    {{ __('messages.transfer_ownership_replaces_open', ['email' => $openTransfer->to_email]) }}
                                </p>
                            </div>
                        </div>
                        @endif

                        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3 mb-6">
                            <div class="flex gap-3">
                                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                </svg>
                                <div class="text-sm text-amber-800 dark:text-amber-200">
                                    <p class="font-semibold">{{ __('messages.transfer_ownership_warning_title') }}</p>
                                    <ul class="mt-2 space-y-1 list-disc ms-4">
                                        <li>{{ __('messages.transfer_ownership_warning_access') }}</li>
                                        <li>{{ __('messages.transfer_ownership_warning_events') }}</li>
                                        @if (config('app.hosted'))
                                        <li>{{ __('messages.transfer_ownership_warning_billing') }}</li>
                                        @endif
                                        <li>{{ __('messages.transfer_ownership_warning_calendar') }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="mb-6">
                            <x-input-label for="email" :value="__('messages.email') . ' *'" />
                            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                                :value="old('email')" required autofocus autocomplete="off" />
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                {{ __('messages.transfer_ownership_email_help') }}
                            </p>
                            <x-input-error class="mt-2" :messages="$errors->get('email')" />
                        </div>

                        @if ($role->isEnterprise())
                        <div class="mb-6">
                            <x-toggle
                                name="remove_me"
                                :label="__('messages.transfer_ownership_remove_me')"
                                :help="__('messages.transfer_ownership_remove_me_help')"
                                :checked="old('remove_me', '1') == '1'" />
                        </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto space-y-4">
            <div class="flex items-center gap-4">
                <x-secondary-link href="{{ route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'team']) }}">
                    {{ __('messages.cancel') }}
                </x-secondary-link>

                <x-danger-button>
                    {{ __('messages.transfer_ownership_send') }}
                </x-danger-button>
            </div>
        </div>

    </form>

</x-app-admin-layout>
