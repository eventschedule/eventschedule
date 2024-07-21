@if(count($events) == 0)

<div class="text-center pt-20">
    <svg class="mx-auto h-12 w-12 text-gray-400" fill="#ccc" viewBox="0 0 24 24" stroke="currentColor"
        aria-hidden="true">
        <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M21,19V20H3V19L5,17V11C5,7.9 7.03,5.17 10,4.29C10,4.19 10,4.1 10,4A2,2 0 0,1 12,2A2,2 0 0,1 14,4C14,4.1 14,4.19 14,4.29C16.97,5.17 19,7.9 19,11V17L21,19M14,21A2,2 0 0,1 12,23A2,2 0 0,1 10,21" />
    </svg>
    <h3 class="mt-2 text-sm font-semibold text-gray-900">{{ __('No requests') }}</h3>
    <p class="mt-1 text-sm text-gray-500">{{ __('Share your sign up link to get more requests') }}</p>
    <div class="mt-3">
        <a href="{{ url($role->subdomain . '/sign_up') }}" target="_blank">
            {{ \App\Utils\UrlUtils::clean(url($role->subdomain . '/sign_up')) }}
        </a>
    </div>
</div>

@else

<ul role="list" class="divide-y divide-gray-100">
    @foreach($events as $event)
    <a href="{{ url('/' . $role->subdomain . '/view') }}" target="_blank">
        <li class="relative flex justify-between gap-x-6 px-5 py-5 bg-white hover:bg-gray-100 hover:border-gray-300 cursor-pointer">
            <div class="flex min-w-0 gap-x-4">
                <img class="h-12 w-12 flex-none rounded-full bg-gray-50"
                    src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80"
                    alt="">
                <div class="min-w-0 flex-auto">
                    <p class="text-sm font-semibold leading-6 text-gray-900">
                        {{ $event->role->name }}
                    </p>
                    <p class="line-clamp-3">
                        {{ $event->role->description }}
                    </p>
                </div>
            </div>
            <div class="flex shrink-0 items-center gap-x-4">

                <div onclick="location.href = '{{ url('/' . $role->subdomain . '/accept_event/' . base64_encode($event->id)) }}'; return false;"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                    <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd"
                            d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z"
                            clip-rule="evenodd" />
                    </svg>
                    {{ __('Accept') }}
                </div>

                <div onclick="location.href = '{{ url('/' . $role->subdomain . '/decline_event/' . base64_encode($event->id)) }}'; return false;"
                    class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                    <svg class="-ml-0.5 mr-1.5 h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="currentColor"
                        aria-hidden="true">
                        <path
                            d="M12,2C17.53,2 22,6.47 22,12C22,17.53 17.53,22 12,22C6.47,22 2,17.53 2,12C2,6.47 6.47,2 12,2M15.59,7L12,10.59L8.41,7L7,8.41L10.59,12L7,15.59L8.41,17L12,13.41L15.59,17L17,15.59L13.41,12L17,8.41L15.59,7Z" />
                    </svg>
                    {{ __('Decline') }}
                </div>

            </div>
        </li>
    </a>
    @endforeach
</ul>

@endif