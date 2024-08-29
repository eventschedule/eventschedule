<div class="sm:flex sm:items-center">
    <div class="sm:flex-auto">
    </div>
    <div class="mt-6 sm:ml-16 sm:mt-0 sm:flex-none">
        <a href="{{ route('role.qr_code', ['subdomain' => $role->subdomain]) }}">
            <button type="button" {{ ! $role->email_verified_at ? 'disabled' : '' }}
                class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 {{ ! $role->email_verified_at ? 'disabled:bg-gray-400 disabled:cursor-not-allowed' : '' }}">
                <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path
                        d="M3,11H5V13H3V11M11,5H13V9H11V5M9,11H13V15H11V13H9V11M15,11H17V13H19V11H21V13H19V15H21V19H19V21H17V19H13V21H11V17H15V15H17V13H15V11M19,19V15H17V19H19M15,3H21V9H15V3M17,5V7H19V5H17M3,3H9V9H3V3M5,5V7H7V5H5M3,15H9V21H3V15M5,17V19H7V17H5Z" />
                </svg>
                {{ __('messages.qr_code') }}
            </button>
        </a>
    </div>
</div>


@if(count($followers) == 0 || ! $role->email_verified_at)

<div class="text-center pt-20">
    <svg class="mx-auto h-12 w-12 text-gray-400" fill="#ccc" viewBox="0 0 24 24" stroke="currentColor"
        aria-hidden="true">
        <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M21,19V20H3V19L5,17V11C5,7.9 7.03,5.17 10,4.29C10,4.19 10,4.1 10,4A2,2 0 0,1 12,2A2,2 0 0,1 14,4C14,4.1 14,4.19 14,4.29C16.97,5.17 19,7.9 19,11V17L21,19M14,21A2,2 0 0,1 12,23A2,2 0 0,1 10,21" />
    </svg>
    <h3 class="mt-2 text-sm font-semibold text-gray-900">{{ __('messages.no_followers') }}</h3>
    <p class="mt-1 text-sm text-gray-500">{{ __('messages.share_your_event_schedule_link') }}</p>
    <div class="mt-3">
        <a href="{{ 'https://' . $role->subdomain . 'eventschedule.com' }}" target="_blank" class="hover:underline">
            {{ $role->subdomain . '.eventschedule.com' }}
        </a>
    </div>
</div>

@else

<div class="mt-8 flow-root">
    <div class="overflow-x-auto px-4 sm:px-6 lg:px-8 bg-white">
        <div class="inline-block min-w-full py-2 align-middle">
            <div class="overflow-hidden min-w-full">
                <table class="min-w-full divide-y divide-gray-300">
                    <thead>
                        <tr>
                            <th scope="col"
                                class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-0">
                                {{ __('messages.name') }}
                            </th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                {{ __('messages.email') }}
                            </th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                {{ __('messages.date') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach ($followers as $follower)
                        <tr class="bg-white">
                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-0">
                                {{ $follower->name }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                <a href="mailto:{{ $follower->email }}">{{ $follower->email }}</a>
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endif