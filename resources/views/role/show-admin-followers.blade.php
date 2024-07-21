@if(count($followers) == 0)

<div class="text-center pt-20">
    <svg class="mx-auto h-12 w-12 text-gray-400" fill="#ccc" viewBox="0 0 24 24" stroke="currentColor"
        aria-hidden="true">
        <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M21,19V20H3V19L5,17V11C5,7.9 7.03,5.17 10,4.29C10,4.19 10,4.1 10,4A2,2 0 0,1 12,2A2,2 0 0,1 14,4C14,4.1 14,4.19 14,4.29C16.97,5.17 19,7.9 19,11V17L21,19M14,21A2,2 0 0,1 12,23A2,2 0 0,1 10,21" />
    </svg>
    <h3 class="mt-2 text-sm font-semibold text-gray-900">{{ __('No followers') }}</h3>
    <p class="mt-1 text-sm text-gray-500">{{ __('Share your schedule link to get more followers') }}</p>
    <div class="mt-3">
        <a href="{{ 'https://' . $role->domain . 'eventschedule.com' }}" target="_blank">
            {{ $role->domain . 'eventschedule.com' }}
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
                                {{ __('Name') }}
                            </th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                {{ __('Email') }}
                            </th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                {{ __('Date') }}
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
                                {{ __(ucwords($follower->pivot->created_at)) }}
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