<x-app-admin-layout>

    {{-- One small form that hangs from the schedule's Team tab: the way back names the schedule,
         and the form ends with Cancel and then the button that goes on. --}}
    @php $teamUrl = route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'team']); @endphp

    <div class="page-shell page-col is-narrow">
        <x-page-header :title="$title" :lead="__('messages.team_lead')" :back="$teamUrl" :back-label="$role->name" />

        <form method="post" action="{{ route('role.store_member', ['subdomain' => $role->subdomain]) }}">
            @csrf
            @method('post')

            <div class="ap-card rounded-xl page-card">
                <div class="page-form-fields">
                    <div>
                        <x-input-label for="name" :value="__('messages.name') . ' *'" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                            :value="old('name')" required autofocus autocomplete="name" />
                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                    </div>

                    <div>
                        <x-input-label for="email" :value="__('messages.email') . ' *'" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                            :value="old('email')" required autocomplete="username" />
                        <x-input-error class="mt-2" :messages="$errors->get('email')" />
                    </div>

                    <div>
                        <x-input-label for="phone" :value="__('messages.phone_number')" />
                        <x-phone-input name="phone" :value="old('phone')" :country="$role->country_code ?? 'us'" />
                        <x-input-error class="mt-2" :messages="$errors->get('phone')" />
                    </div>

                    <div>
                        <x-input-label for="level" :value="__('messages.role')" />
                        <select id="level" name="level" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                            <option value="admin" {{ old('level', 'admin') == 'admin' ? 'selected' : '' }}>{{ __('messages.admin') }}</option>
                            <option value="viewer" {{ old('level') == 'viewer' ? 'selected' : '' }}>{{ __('messages.viewer') }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="page-form-actions">
                <x-secondary-link :href="$teamUrl">{{ __('messages.cancel') }}</x-secondary-link>
                <x-brand-button type="submit">{{ __('messages.save') }}</x-brand-button>
            </div>
        </form>
    </div>

</x-app-admin-layout>
