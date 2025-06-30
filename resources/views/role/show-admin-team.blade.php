<div class="sm:flex sm:items-center">
    <div class="sm:flex-auto">
        <!--
        <h1 class="text-base font-semibold leading-6 text-gray-900">Users</h1>
        <p class="mt-2 text-sm text-gray-700">A list of all the users in your account including their name, title,
            email and role.</p>
        -->
    </div>
    <div class="mt-6 sm:ml-16 sm:mt-0 sm:flex-none">
        <a href="{{ route('role.create_member', ['subdomain' => $role->subdomain]) }}">
            <button type="button" {{ ! $role->email_verified_at || ! $role->isPro() ? 'disabled' : '' }}
                class="inline-flex items-center rounded-md bg-[#4E81FA] px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#3A6BE0] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#4E81FA] {{ ! $role->email_verified_at ? 'disabled:bg-gray-400 disabled:cursor-not-allowed' : '' }}">
                <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path
                        d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                </svg>
                {{ __('messages.add_member') }}
            </button>
        </a>
    </div>
</div>
<div class="mt-8 flow-root">
    <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
            <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                <div class="overflow-x-auto" style="overflow-x: auto; scrollbar-width: thin;">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">
                                    {{ __('messages.name') }}
                                </th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                    {{ __('messages.email') }}
                                </th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                    {{ __('messages.role') }}
                                </th>
                                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                    <span class="sr-only">{{ __('messages.actions') }}</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach ($members as $member)
                            <tr class="bg-white hover:bg-gray-50 transition-colors duration-150">
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                    {{ $member->name }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                    <a href="mailto:{{ $member->email }}">{{ $member->email }}</a>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                    @if ($member->email_verified_at)
                                        {{ __('messages.' . strtolower($member->pivot->level)) }}
                                    @else      
                                        <a href="{{ route('role.resend_invite', ['subdomain' => $role->subdomain, 'hash' => App\Utils\UrlUtils::encodeId($member->id)]) }}">    
                                            <button type="button"
                                                class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                                                {{ __('messages.resend_invite') }}
                                            </button>
                                        </a>
                                    @endif
                                </td>
                                <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                    @if ($member->pivot->level != 'owner')
                                        <a href="{{ route('role.remove_member', ['subdomain' => $role->subdomain, 'hash' => App\Utils\UrlUtils::encodeId($member->id)]) }}"
                                            onclick="return confirm('{{ __('messages.are_you_sure') }}'); return false; "
                                            class="text-[#4E81FA] hover:text-[#4E81FA]">{{ __('messages.remove') }}</a>
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
