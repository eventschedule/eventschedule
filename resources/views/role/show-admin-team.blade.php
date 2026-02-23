<div class="sm:flex sm:items-center">
    <div class="sm:flex-auto">
        <!--
        <h1 class="text-base font-semibold leading-6 text-gray-900">Users</h1>
        <p class="mt-2 text-sm text-gray-700">A list of all the users in your account including their name, title,
            email and role.</p>
        -->
    </div>
    <div class="mt-6 sm:ms-16 sm:mt-0 sm:flex-none">
        @if ($role->isEnterprise())
        <x-brand-link href="{{ route('role.create_member', ['subdomain' => $role->subdomain]) }}" class="w-full md:w-auto">
            <svg class="-ms-0.5 me-1.5 h-6 w-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path
                    d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
            </svg>
            {{ __('messages.add_member') }}
        </x-brand-link>
        @elseif (config('app.hosted'))
        <button type="button" x-data x-on:click.prevent="$dispatch('open-modal', 'upgrade-members')"
                class="w-full md:w-auto inline-flex items-center justify-center px-4 py-3 bg-[#4E81FA] border border-transparent rounded-md font-semibold text-base text-white shadow-sm transition-all duration-200 hover:bg-[#3D6FE8] hover:scale-105 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-[#4E81FA] focus:ring-offset-2 dark:focus:ring-offset-gray-800">
            <svg class="-ms-0.5 me-1.5 h-6 w-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path
                    d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
            </svg>
            {{ __('messages.add_member') }}
        </button>
        <x-upgrade-modal name="upgrade-members" tier="enterprise" :subdomain="$role->subdomain" docsUrl="{{ route('marketing.docs.creating_schedules') }}#team-members">
            {{ __('messages.upgrade_feature_description_members') }}
        </x-upgrade-modal>
        @endif
    </div>
</div>
<div class="mt-8 flow-root">
    <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
            <div class="overflow-hidden shadow ring-1 ring-black dark:ring-gray-700 ring-opacity-5 md:rounded-lg">
                <div class="overflow-x-auto" style="overflow-x: auto; scrollbar-width: thin;">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col"
                                    class="py-3.5 ps-4 pe-3 text-start text-sm font-semibold text-gray-900 dark:text-gray-100 sm:ps-6">
                                    {{ __('messages.name') }}
                                </th>
                                <th scope="col" class="px-3 py-3.5 text-start text-sm font-semibold text-gray-900 dark:text-gray-100">
                                    {{ __('messages.email') }}
                                </th>
                                <th scope="col" class="px-3 py-3.5 text-start text-sm font-semibold text-gray-900 dark:text-gray-100">
                                    {{ __('messages.role') }}
                                </th>
                                <th scope="col" class="relative py-3.5 ps-3 pe-4 sm:pe-6">
                                    <span class="sr-only">{{ __('messages.actions') }}</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                            @foreach ($members as $member)
                            <tr class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150">
                                <td class="whitespace-nowrap py-4 ps-4 pe-3 text-sm font-medium text-gray-900 dark:text-gray-100 sm:ps-6">
                                    {{ $member->name }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    <a href="mailto:{{ $member->email }}" class="hover:text-gray-700 dark:hover:text-gray-300">{{ $member->email }}</a>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    @if ($member->email_verified_at)
                                        {{ __('messages.' . strtolower($member->pivot->level)) }}
                                    @else      
                                        <a href="{{ route('role.resend_invite', ['subdomain' => $role->subdomain, 'hash' => App\Utils\UrlUtils::encodeId($member->id)]) }}">    
                                            <button type="button"
                                                class="inline-flex items-center rounded-md bg-white dark:bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600">
                                                {{ __('messages.resend_invite') }}
                                            </button>
                                        </a>
                                    @endif
                                </td>
                                <td class="relative whitespace-nowrap py-4 ps-3 pe-4 text-end text-sm font-medium sm:pe-6">
                                    @if ($member->pivot->level != 'owner')
                                        <form method="POST" action="{{ route('role.remove_member', ['subdomain' => $role->subdomain, 'hash' => App\Utils\UrlUtils::encodeId($member->id)]) }}" data-confirm="{{ __('messages.are_you_sure') }}" class="inline form-confirm">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-[#4E81FA] hover:text-[#4E81FA]">{{ __('messages.remove') }}</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

