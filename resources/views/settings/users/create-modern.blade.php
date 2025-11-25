<x-app-admin-layout>
    <div class="py-10">
        <div class="mx-auto max-w-6xl space-y-6">
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100 dark:bg-gray-900 dark:ring-gray-800">
                @php
                    $isEdit = isset($managedUser);
                    $managedUser = $managedUser ?? null;
                @endphp
                <div class="flex flex-col gap-4 border-b border-gray-100 pb-4 md:flex-row md:items-center md:justify-between dark:border-gray-800">
                    <div class="space-y-2">
                        <x-breadcrumbs
                            :items="[
                                ['label' => __('messages.settings'), 'url' => route('settings.index')],
                                ['label' => __('messages.user_management'), 'url' => route('settings.users.index')],
                                ['label' => $isEdit ? __('messages.edit_user') : __('messages.add_user'), 'current' => true],
                            ]"
                            class="text-xs text-gray-500 dark:text-gray-400"
                        />
                        <div class="flex items-center gap-3">
                            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                                {{ $isEdit ? __('messages.edit_user') : __('messages.create_user') }}
                            </h1>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ $isEdit
                                ? __('messages.edit_user_description', ['name' => $managedUser->name])
                                : 'Create a platform account with roles, scopes, and access permissions.' }}
                        </p>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="mt-4 rounded-lg border border-red-100 bg-red-50 p-4 text-sm text-red-800 dark:border-red-900 dark:bg-red-950 dark:text-red-100">
                        <div class="font-semibold">{{ __('Whoops! Something went wrong.') }}</div>
                        <ul class="mt-2 space-y-1 list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @php
                    $passwordMode = old('password_mode', $isEdit ? 'defer' : 'set');
                    $venueScope = old('venue_scope', $isEdit ? ($managedUser->venue_scope ?? 'all') : 'all');
                    $curatorScope = old('curator_scope', $isEdit ? ($managedUser->curator_scope ?? 'all') : 'all');
                    $talentScope = old('talent_scope', $isEdit ? ($managedUser->talent_scope ?? 'all') : 'all');
                    $timezoneDefault = old('timezone', $isEdit ? ($managedUser->timezone ?? config('app.timezone')) : config('app.timezone'));
                    $languageDefault = old('language_code', $isEdit ? ($managedUser->language_code ?? app()->getLocale()) : app()->getLocale());
                    $assignedRoles = old('roles', $isEdit ? $managedUser->systemRoles->pluck('id')->all() : []);
                    $selectedVenues = old('venue_ids', $isEdit ? (array) ($managedUser->venue_ids ?? []) : []);
                    $selectedCurators = old('curator_ids', $isEdit ? (array) ($managedUser->curator_ids ?? []) : []);
                    $selectedTalent = old('talent_ids', $isEdit ? (array) ($managedUser->talent_ids ?? []) : []);
                @endphp

                <form method="POST" action="{{ $isEdit ? route('settings.users.update', $managedUser) : route('settings.users.store') }}" class="mt-6 grid gap-6 lg:grid-cols-[2fr,1fr]">
                    @csrf
                    @if ($isEdit)
                        @method('PATCH')
                    @endif
                    <div class="space-y-6">
                        <section class="rounded-xl border border-gray-100 bg-gray-50/60 p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900/50">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Account basics</h2>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Name, email, localization, and status.</p>
                                </div>
                                <span class="rounded-full bg-gray-200 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-gray-700 dark:bg-gray-800 dark:text-gray-200">Step 1</span>
                            </div>
                            <div class="mt-4 grid gap-4 md:grid-cols-2">
                                <div class="space-y-2">
                                    <x-input-label for="name" :value="__('messages.name')" />
                                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" value="{{ old('name', $managedUser->name ?? '') }}" required autocomplete="name" />
                                    <x-input-error class="mt-1" :messages="$errors->get('name')" />
                                </div>
                                <div class="space-y-2">
                                    <x-input-label for="email" :value="__('messages.email')" />
                                    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" value="{{ old('email', $managedUser->email ?? '') }}" required autocomplete="username" />
                                    <x-input-error class="mt-1" :messages="$errors->get('email')" />
                                </div>
                                <div class="space-y-2">
                                    <x-input-label for="timezone" :value="__('messages.timezone')" />
                                    <select id="timezone" name="timezone" class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900/70" required>
                                        @foreach ($timezones as $timezone)
                                            <option value="{{ $timezone }}" @selected($timezoneDefault === $timezone)>{{ $timezone }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error class="mt-1" :messages="$errors->get('timezone')" />
                                </div>
                                <div class="space-y-2">
                                    <x-input-label for="language_code" :value="__('messages.language')" />
                                    <select id="language_code" name="language_code" class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900/70" required>
                                        @foreach ($languageOptions as $code => $label)
                                            <option value="{{ $code }}" @selected($languageDefault === $code)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error class="mt-1" :messages="$errors->get('language_code')" />
                                </div>
                                <div class="space-y-2">
                                    <x-input-label for="status" value="Status" />
                                    <select id="status" name="status" class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900/70">
                                        @foreach ($statusOptions as $value => $label)
                                            <option value="{{ $value }}" @selected(old('status', $isEdit ? ($managedUser->status ?? 'active') : 'active') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </section>

                        <section class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Authentication</h2>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Set credentials or plan an invite email.</p>
                                </div>
                                <span class="rounded-full bg-gray-200 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-gray-700 dark:bg-gray-800 dark:text-gray-200">Step 2</span>
                            </div>
                            <div class="mt-4 grid gap-4 md:grid-cols-3" data-auth-mode>
                                <label class="relative flex cursor-pointer flex-col gap-2 rounded-lg border border-indigo-200 bg-indigo-50 p-4 text-sm font-medium text-indigo-900 shadow-sm ring-2 ring-indigo-200 transition dark:border-indigo-900/60 dark:bg-indigo-950/40 dark:text-indigo-100" data-auth-option>
                                    <input type="radio" name="password_mode" value="set" class="peer sr-only" @checked($passwordMode === 'set') />
                                    <span class="flex items-center justify-between">
                                        <span>Set password now</span>
                                        <span class="rounded-full bg-white px-2 py-0.5 text-xs font-semibold text-indigo-700 shadow-sm ring-1 ring-indigo-200 dark:bg-indigo-900 dark:text-indigo-100">Default</span>
                                    </span>
                                    <span class="text-xs font-normal text-indigo-800/80 dark:text-indigo-200/80">Create a password immediately. This matches today’s flow.</span>
                                    <span class="pointer-events-none absolute inset-0 rounded-lg ring-1 ring-inset ring-indigo-400/40 peer-checked:ring-indigo-600/70"></span>
                                </label>
                                <label class="flex cursor-pointer flex-col gap-2 rounded-lg border border-dashed border-gray-300 bg-gray-50 p-4 text-sm font-medium text-gray-800 shadow-sm transition hover:border-indigo-300 dark:border-gray-700 dark:bg-gray-900" data-auth-option>
                                    <input type="radio" name="password_mode" value="invite" class="peer sr-only" @checked($passwordMode === 'invite') />
                                    <span>Send invite email</span>
                                    <span class="text-xs font-normal text-gray-600 dark:text-gray-400">User sets their own password from a secure email link. (Coming soon)</span>
                                </label>
                                <label class="flex cursor-pointer flex-col gap-2 rounded-lg border border-dashed border-gray-300 bg-gray-50 p-4 text-sm font-medium text-gray-800 shadow-sm transition hover:border-indigo-300 dark:border-gray-700 dark:bg-gray-900" data-auth-option>
                                    <input type="radio" name="password_mode" value="defer" class="peer sr-only" @checked($passwordMode === 'defer') />
                                    <span>Defer password</span>
                                    <span class="text-xs font-normal text-gray-600 dark:text-gray-400">Create the account now and return to set credentials later.</span>
                                </label>
                            </div>
                            <div class="mt-3 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-900 dark:border-amber-800 dark:bg-amber-950/30 dark:text-amber-100">
                                Invitation and deferred password flows are being designed. Until they ship, new users still need a password on creation.
                            </div>
                            <div class="mt-5 grid gap-4 md:grid-cols-2" data-password-fields>
                                <div class="space-y-2">
                                    <x-input-label for="password" :value="__('messages.password')" />
                                    <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" data-password-required="true" />
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Minimum 8 characters. Required unless you select invite or defer.</p>
                                    <x-input-error class="mt-1" :messages="$errors->get('password')" />
                                </div>
                                <div class="space-y-2">
                                    <x-input-label for="password_confirmation" :value="__('messages.confirm_password')" />
                                    <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" data-password-required="true" />
                                    <x-input-error class="mt-1" :messages="$errors->get('password_confirmation')" />
                                </div>
                            </div>
                        </section>

                        <section class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Role & permissions</h2>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Assign platform roles and creation privileges.</p>
                                </div>
                                <span class="rounded-full bg-gray-200 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-gray-700 dark:bg-gray-800 dark:text-gray-200">Step 3</span>
                            </div>
                            <div class="mt-4 space-y-4">
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200">System roles</h3>
                                    <p class="text-xs text-gray-600 dark:text-gray-400">Choose Super Admin, Admin, or Viewer.</p>
                                    @if ($canManageRoles)
                                        <div class="mt-3 grid gap-3 md:grid-cols-2">
                                            @foreach ($availableRoles as $role)
                                                <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-gray-200 bg-white p-3 shadow-sm transition hover:border-indigo-300 dark:border-gray-700 dark:bg-gray-900">
                                                    <input type="checkbox" name="roles[]" value="{{ $role->id }}" class="mt-1 h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600" @checked(in_array($role->id, $assignedRoles, true))>
                                                    <div>
                                                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $role->name }}</div>
                                                        <div class="text-xs text-gray-600 dark:text-gray-400">{{ $role->description ?? 'Permissions defined in authorization config.' }}</div>
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>
                                        <x-input-error class="mt-2" :messages="$errors->get('roles')" />
                                        <x-input-error class="mt-1" :messages="$errors->get('roles.*')" />
                                    @else
                                        <div class="mt-3 rounded-lg border border-dashed border-amber-300 bg-amber-50 p-3 text-sm text-amber-900 dark:border-amber-800 dark:bg-amber-950/50 dark:text-amber-100">
                                            You don’t have permission to assign roles. An administrator can update them later.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </section>

                        <section class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Access scope</h2>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Choose whether this user can work with all resources or a curated list.</p>
                                </div>
                                <span class="rounded-full bg-gray-200 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-gray-700 dark:bg-gray-800 dark:text-gray-200">Step 4</span>
                            </div>
                            <div class="mt-4 space-y-6">
                                <div data-scope-block>
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Venues</h3>
                                            <p class="text-xs text-gray-600 dark:text-gray-400">Control which venues appear in dropdowns and reports.</p>
                                        </div>
                                        <div class="flex gap-3 text-xs font-medium text-gray-700 dark:text-gray-200">
                                            <label class="flex items-center gap-2">
                                                <input type="radio" name="venue_scope" value="all" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500" data-scope-toggle="venues" @checked($venueScope === 'all')>
                                                <span>All venues</span>
                                            </label>
                                            <label class="flex items-center gap-2">
                                                <input type="radio" name="venue_scope" value="selected" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500" data-scope-toggle="venues" @checked($venueScope === 'selected')>
                                                <span>Specific list</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="mt-3 grid gap-2 md:grid-cols-2" data-scope-target="venues">
                                        @forelse ($resourceOptions['venues'] as $venue)
                                            <label class="flex items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm font-medium text-gray-800 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                                                <input type="checkbox" name="venue_ids[]" value="{{ $venue->id }}" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600" @checked(in_array($venue->id, $selectedVenues, true))>
                                                <div>
                                                    <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $venue->name }}</div>
                                                    <div class="text-xs text-gray-500">{{ $venue->subdomain }}</div>
                                                </div>
                                            </label>
                                        @empty
                                            <p class="text-sm text-gray-500">No venues available yet.</p>
                                        @endforelse
                                    </div>
                                </div>

                                <div class="h-px bg-gray-100 dark:bg-gray-800"></div>

                                <div data-scope-block>
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Curators</h3>
                                            <p class="text-xs text-gray-600 dark:text-gray-400">Limit curator access to a focused list.</p>
                                        </div>
                                        <div class="flex gap-3 text-xs font-medium text-gray-700 dark:text-gray-200">
                                            <label class="flex items-center gap-2">
                                                <input type="radio" name="curator_scope" value="all" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500" data-scope-toggle="curators" @checked($curatorScope === 'all')>
                                                <span>All curators</span>
                                            </label>
                                            <label class="flex items-center gap-2">
                                                <input type="radio" name="curator_scope" value="selected" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500" data-scope-toggle="curators" @checked($curatorScope === 'selected')>
                                                <span>Specific list</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="mt-3 grid gap-2 md:grid-cols-2" data-scope-target="curators">
                                        @forelse ($resourceOptions['curators'] as $curator)
                                            <label class="flex items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm font-medium text-gray-800 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                                                <input type="checkbox" name="curator_ids[]" value="{{ $curator->id }}" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600" @checked(in_array($curator->id, $selectedCurators, true))>
                                                <div>
                                                    <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $curator->name }}</div>
                                                    <div class="text-xs text-gray-500">{{ $curator->subdomain }}</div>
                                                </div>
                                            </label>
                                        @empty
                                            <p class="text-sm text-gray-500">No curators available yet.</p>
                                        @endforelse
                                    </div>
                                </div>

                                <div class="h-px bg-gray-100 dark:bg-gray-800"></div>

                                <div data-scope-block>
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Talent</h3>
                                            <p class="text-xs text-gray-600 dark:text-gray-400">Restrict talent visibility to specific records.</p>
                                        </div>
                                        <div class="flex gap-3 text-xs font-medium text-gray-700 dark:text-gray-200">
                                            <label class="flex items-center gap-2">
                                                <input type="radio" name="talent_scope" value="all" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500" data-scope-toggle="talent" @checked($talentScope === 'all')>
                                                <span>All talent</span>
                                            </label>
                                            <label class="flex items-center gap-2">
                                                <input type="radio" name="talent_scope" value="selected" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500" data-scope-toggle="talent" @checked($talentScope === 'selected')>
                                                <span>Specific list</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="mt-3 grid gap-2 md:grid-cols-2" data-scope-target="talent">
                                        @forelse ($resourceOptions['talent'] as $talent)
                                            <label class="flex items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm font-medium text-gray-800 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                                                <input type="checkbox" name="talent_ids[]" value="{{ $talent->id }}" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600" @checked(in_array($talent->id, $selectedTalent, true))>
                                                <div>
                                                    <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $talent->name }}</div>
                                                    <div class="text-xs text-gray-500">{{ $talent->subdomain }}</div>
                                                </div>
                                            </label>
                                        @empty
                                            <p class="text-sm text-gray-500">No talent records available yet.</p>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>

                    <aside class="space-y-4">
                        <div class="rounded-xl border border-indigo-100 bg-indigo-50 p-5 text-sm text-indigo-900 shadow-sm dark:border-indigo-900/60 dark:bg-indigo-950/40 dark:text-indigo-100">
                            <div class="flex items-center justify-between gap-2">
                                <div>
                                    <div class="text-xs font-semibold uppercase tracking-wide text-indigo-700 dark:text-indigo-200">Summary</div>
                                    <div class="text-base font-semibold text-indigo-900 dark:text-indigo-50">New user details</div>
                                </div>
                                <svg class="h-6 w-6 text-indigo-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l3 3" />
                                </svg>
                            </div>
                            <ul class="mt-4 space-y-2 text-sm">
                                <li class="flex items-start gap-2">
                                    <span class="mt-0.5 h-2 w-2 rounded-full bg-indigo-500"></span>
                                    <span>Collects name, email, timezone, language, and status.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="mt-0.5 h-2 w-2 rounded-full bg-indigo-500"></span>
                                    <span>Supports password set now, invite flow, or deferring setup.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="mt-0.5 h-2 w-2 rounded-full bg-indigo-500"></span>
                                    <span>Captures resource scopes for venues, curators, and talent.</span>
                                </li>
                            </ul>
                            <p class="mt-4 text-xs text-indigo-800/80 dark:text-indigo-200/80">Keep roles, permissions, and scopes aligned with how this person should use the platform.</p>
                        </div>

                        <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Save</h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">All required fields must be filled before creating the user.</p>
                            <div class="mt-4 flex flex-col gap-3">
                                <x-primary-button class="justify-center">{{ __('messages.save') }}</x-primary-button>
                                <a href="{{ route('settings.users.index') }}" class="text-center text-sm font-medium text-gray-600 transition hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200">{{ __('messages.cancel') }}</a>
                            </div>
                        </div>
                    </aside>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const passwordRadios = document.querySelectorAll('input[name="password_mode"]');
            const passwordFields = document.querySelectorAll('[data-password-required]');

            function syncPasswordRequirements() {
                const mode = document.querySelector('input[name="password_mode"]:checked')?.value || 'set';
                const shouldRequire = mode === 'set';

                passwordFields.forEach((field) => {
                    field.required = shouldRequire;
                    field.closest('[data-password-fields]')?.classList.toggle('opacity-50', !shouldRequire);
                });
            }

            passwordRadios.forEach((radio) => {
                radio.addEventListener('change', syncPasswordRequirements);
            });
            syncPasswordRequirements();

            document.querySelectorAll('[data-scope-toggle]').forEach((radio) => {
                radio.addEventListener('change', (event) => {
                    const group = event.target.getAttribute('data-scope-toggle');
                    const target = document.querySelector(`[data-scope-target="${group}"]`);
                    if (!target) return;

                    const showList = event.target.value === 'selected';
                    target.classList.toggle('hidden', !showList);
                });
            });

            // Initialize scope visibility on page load
            ['venues', 'curators', 'talent'].forEach((group) => {
                const selectedRadio = document.querySelector(`input[data-scope-toggle="${group}"]:checked`);
                const target = document.querySelector(`[data-scope-target="${group}"]`);
                if (selectedRadio && target) {
                    target.classList.toggle('hidden', selectedRadio.value !== 'selected');
                }
            });
        });
    </script>
</x-app-admin-layout>
