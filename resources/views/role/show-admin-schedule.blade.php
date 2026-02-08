@include('role/partials/calendar', ['route' => 'admin', 'tab' => 'schedule'])

@if (count($unscheduled))
<div class="lg:flex lg:h-full lg:flex-col pt-5">
    <header class="flex items-center justify-between ps-6 py-4 lg:flex-none">
        <h1 class="text-base font-semibold leading-6 text-gray-900 dark:text-gray-100">
            {{ __('messages.unscheduled') }}
        </h1>
    </header>
    <ul role="list" class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 pt-5">
        @foreach($unscheduled as $event)
        @if(! $event->starts_at)
        <li class="col-span-1 flex flex-col divide-y divide-gray-200 dark:divide-gray-700 rounded-lg bg-white dark:bg-gray-800 text-center shadow">
            <x-link href="{{ $event->role()->getGuestUrl() }}" target="_blank" class="block">
                <div class="flex flex-1 flex-col p-8">
                    @if ($event->role()->profile_image_url)
                    <img class="mx-auto rounded-lg h-32 w-32 flex-shrink-0 object-cover"
                        src="{{ $event->role()->profile_image_url }}"
                        alt="Profile Image">
                    @endif
                    <h3 class="mt-6 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $event->role()->name }}</h3>
                    <dl class="mt-1 flex flex-grow flex-col justify-between">
                        <dd class="text-sm text-gray-500 dark:text-gray-400 line-clamp-3">{{ $event->role()->description }}</dd>
                    </dl>
                </div>
            </x-link>
            <div>
                <div class="-mt-px flex divide-x divide-gray-200 dark:divide-gray-700">
                    <div class="flex w-0 flex-1 cursor-pointer btn-navigate"
                        data-href="{{ route('event.edit', ['subdomain' => $role->subdomain, 'hash' => App\Utils\UrlUtils::encodeId($event->id)]) }}">
                        <div
                            class="relative -me-px inline-flex w-0 flex-1 items-center justify-center gap-x-3 rounded-es-lg border border-transparent py-4 text-sm font-semibold text-gray-900 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-700">
                            <svg class="h-5 w-5 text-gray-400 dark:text-gray-500" viewBox="0 0 24 24" fill="currentColor"
                                aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z"
                                    clip-rule="evenodd" />
                            </svg>
                            {{ __('messages.schedule') }}
                        </div>
                    </div>
                    <div class="-ms-px flex w-0 flex-1 cursor-pointer btn-confirm-navigate"
                        data-confirm="{{ __('messages.are_you_sure') }}"
                        data-href="{{ route('event.decline', ['subdomain' => $role->subdomain, 'hash' => App\Utils\UrlUtils::encodeId($event->id), 'redirect_to' => 'schedule']) }}">
                        <div
                            class="relative inline-flex w-0 flex-1 items-center justify-center gap-x-3 rounded-ee-lg border border-transparent py-4 text-sm font-semibold text-gray-900 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-700">
                            <svg class="h-5 w-5 text-gray-400 dark:text-gray-500" viewBox="0 0 24 24" fill="currentColor"
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

<script {!! nonce_attr() !!}>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-navigate').forEach(function(el) {
        el.addEventListener('click', function() {
            location.href = this.getAttribute('data-href');
        });
    });

    document.querySelectorAll('.btn-confirm-navigate').forEach(function(el) {
        el.addEventListener('click', function() {
            if (confirm(this.getAttribute('data-confirm'))) {
                location.href = this.getAttribute('data-href');
            }
        });
    });
});
</script>
