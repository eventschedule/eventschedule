@php
    $assignedRoles = collect(old('roles', isset($managedUser) ? $managedUser->systemRoles->pluck('id')->all() : []))->map(fn ($id) => (int) $id)->all();
    $passwordMode = $passwordRequired ? 'set' : (in_array(old('password_mode'), ['keep', 'set'], true) ? old('password_mode') : 'keep');
    $passwordInputsShouldBeRequired = $passwordRequired || old('password_mode') === 'set';
@endphp

<div class="space-y-6">
    <div>
        <x-input-label for="name" :value="__('messages.name')" />
        <x-text-input
            id="name"
            name="name"
            type="text"
            class="mt-1 block w-full"
            value="{{ old('name', $managedUser->name ?? '') }}"
            required
            autofocus
        />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div>
        <x-input-label for="email" :value="__('messages.email')" />
        <x-text-input
            id="email"
            name="email"
            type="email"
            class="mt-1 block w-full"
            value="{{ old('email', $managedUser->email ?? '') }}"
            required
        />
        <x-input-error class="mt-2" :messages="$errors->get('email')" />
    </div>

    <div
        class="space-y-4"
        data-user-password-section
        data-user-password-optional="{{ $passwordRequired ? 'false' : 'true' }}"
        data-user-password-default-mode="{{ $passwordMode }}"
    >
        <div>
            <x-input-label for="password" :value="$passwordLabel" />
            @unless ($passwordRequired)
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ __('messages.password_optional_for_existing_user') }}
                </p>
            @endunless
        </div>

        @unless ($passwordRequired)
            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 text-sm text-gray-700 shadow-sm dark:border-gray-700 dark:bg-gray-900/40 dark:text-gray-300">
                <div class="flex flex-col gap-3 md:flex-row" role="radiogroup">
                    <label class="flex flex-1 cursor-pointer items-start gap-3 rounded-lg border border-gray-200 bg-white p-3 text-sm font-medium text-gray-900 shadow-sm transition hover:border-indigo-200 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" data-user-password-toggle-option>
                        <input
                            type="radio"
                            name="password_mode"
                            value="keep"
                            class="mt-1 h-4 w-4 text-indigo-600 focus:ring-indigo-500"
                            data-user-password-toggle="keep"
                            @checked($passwordMode === 'keep')
                        />
                        <span>{{ __('messages.keep_existing_password') }}</span>
                    </label>
                    <label class="flex flex-1 cursor-pointer items-start gap-3 rounded-lg border border-gray-200 bg-white p-3 text-sm font-medium text-gray-900 shadow-sm transition hover:border-indigo-200 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" data-user-password-toggle-option>
                        <input
                            type="radio"
                            name="password_mode"
                            value="set"
                            class="mt-1 h-4 w-4 text-indigo-600 focus:ring-indigo-500"
                            data-user-password-toggle="set"
                            @checked($passwordMode === 'set')
                        />
                        <span>{{ __('messages.set_password') }}</span>
                    </label>
                </div>
            </div>
        @endunless

        <div class="grid gap-6 md:grid-cols-2" data-user-password-fields>
            <div>
                <x-input-label for="password" :value="__('messages.password')" />
                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-full"
                    autocomplete="new-password"
                    data-user-password-input
                    data-password-required="true"
                    @if ($passwordInputsShouldBeRequired) required @endif
                />
                <x-input-error class="mt-2" :messages="$errors->get('password')" />
            </div>
            <div>
                <x-input-label for="password_confirmation" :value="__('messages.confirm_password')" />
                <x-text-input
                    id="password_confirmation"
                    name="password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    autocomplete="new-password"
                    data-user-password-input
                    data-password-required="true"
                    @if ($passwordInputsShouldBeRequired) required @endif
                />
                <x-input-error class="mt-2" :messages="$errors->get('password_confirmation')" />
            </div>
        </div>
    </div>

    <div>
        <x-input-label for="timezone" :value="__('messages.timezone')" />
        <select id="timezone" name="timezone" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            @foreach ($timezones as $timezone)
                <option value="{{ $timezone }}" @selected(old('timezone', $managedUser->timezone ?? config('app.timezone')) === $timezone)>
                    {{ $timezone }}
                </option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('timezone')" />
    </div>

    <div>
        <x-input-label for="language_code" :value="__('messages.language')" />
        <select id="language_code" name="language_code" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            @foreach ($languageOptions as $code => $label)
                <option value="{{ $code }}" @selected(old('language_code', $managedUser->language_code ?? app()->getLocale()) === $code)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('language_code')" />
    </div>

    @if ($canManageRoles)
        <div>
            <x-input-label :value="__('messages.assign_roles')" />
            <p class="mt-1 text-sm text-gray-500">{{ __('messages.assign_roles_help') }}</p>
            <div class="mt-4 grid gap-3 md:grid-cols-2">
                @foreach ($availableRoles as $role)
                    <label class="flex items-start gap-3 rounded-lg border border-gray-200 bg-white p-3 shadow-sm hover:border-indigo-500 dark:border-gray-700 dark:bg-gray-900">
                        <input type="checkbox" name="roles[]" value="{{ $role->id }}" class="mt-1 h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                            @checked(in_array($role->id, $assignedRoles, true))>
                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $role->name }}</span>
                    </label>
                @endforeach
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('roles')" />
            <x-input-error class="mt-2" :messages="$errors->get('roles.*')" />
        </div>
    @else
        <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 p-4 text-sm text-gray-600">
            {{ __('messages.cannot_assign_roles_warning') }}
        </div>
    @endif
</div>

<div class="mt-8 flex items-center justify-end gap-4">
    <a href="{{ route('settings.users.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200">
        {{ __('messages.cancel') }}
    </a>
    <x-primary-button>{{ $submitLabel }}</x-primary-button>
</div>
