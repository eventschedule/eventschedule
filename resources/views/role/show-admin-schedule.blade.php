@include('role/partials/calendar', ['showAdd' => true, 'route' => 'admin'])

@if (count($unscheduled))
<div class="lg:flex lg:h-full lg:flex-col pt-5">
    <header class="flex items-center justify-between pl-6 py-4 lg:flex-none">
        <h1 class="text-base font-semibold leading-6 text-gray-900">
            {{ __('messages.unscheduled') }}
        </h1>
    </header>
    <ul role="list" class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 pt-5">
        @foreach($unscheduled as $event)
        @if(! $event->starts_at)
        <li class="col-span-1 flex flex-col divide-y divide-gray-200 rounded-lg bg-white text-center shadow">
            <a href="{{ route('role.view_guest', ['subdomain' => $role->subdomain]) }}" target="_blank">
                <div class="flex flex-1 flex-col p-8">
                    @if ($event->role->profile_image_url)
                    <img class="mx-auto h-32 w-32 flex-shrink-0 rounded-full object-cover"
                        src="{{ $event->role->profile_image_url }}"
                        alt="Profile Image">
                    @endif
                    <h3 class="mt-6 text-sm font-medium text-gray-900">{{ $event->role->name }}</h3>
                    <dl class="mt-1 flex flex-grow flex-col justify-between">
                        <dd class="text-sm text-gray-500 line-clamp-3">{{ $event->role->description }}</dd>
                    </dl>
                </div>
            </a>
            <div>
                <div class="-mt-px flex divide-x divide-gray-200">
                    <div class="flex w-0 flex-1 cursor-pointer"
                        onclick="location.href = '{{ route('event.edit', ['subdomain' => $role->subdomain, 'hash' => App\Utils\UrlUtils::encodeId($event->id)]) }}'; return false;">
                        <div
                            class="relative -mr-px inline-flex w-0 flex-1 items-center justify-center gap-x-3 rounded-bl-lg border border-transparent py-4 text-sm font-semibold text-gray-900">
                            <svg class="h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="currentColor"
                                aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z"
                                    clip-rule="evenodd" />
                            </svg>
                            {{ __('messages.schedule') }}
                        </div>
                    </div>
                    <div class="-ml-px flex w-0 flex-1 cursor-pointer"
                        onclick="var confirmed = confirm('{{ __('messages.are_you_sure') }}'); if (confirmed) { location.href = '{{ route('event.decline', ['subdomain' => $role->subdomain, 'hash' => App\Utils\UrlUtils::encodeId($event->id), 'redirect_to' => 'schedule']) }}'; return false; }">
                        <div
                            class="relative inline-flex w-0 flex-1 items-center justify-center gap-x-3 rounded-br-lg border border-transparent py-4 text-sm font-semibold text-gray-900">
                            <svg class="h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="currentColor"
                                aria-hidden="true">
                                <path
                                    d="M12,2C17.53,2 22,6.47 22,12C22,17.53 17.53,22 12,22C6.47,22 2,17.53 2,12C2,6.47 6.47,2 12,2M15.59,7L12,10.59L8.41,7L7,8.41L10.59,12L7,15.59L8.41,17L12,13.41L15.59,17L17,15.59L13.41,12L17,8.41L15.59,7Z" />
                            </svg>
                            {{ __('messages.decline') }}
                        </div>
                    </div>
                </div>
            </div>
        </li>
        @endif
        @endforeach
    </ul>
</div>
@endif
