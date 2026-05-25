@if ($role->isCurator() && !empty($venueDuplicateGroupCount))
<div class="pb-4">
    <a href="{{ route('role.merge_venues', ['subdomain' => $role->subdomain]) }}"
       class="block bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3 hover:bg-amber-100 dark:hover:bg-amber-900/30 transition-colors">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
            <div class="text-sm text-gray-800 dark:text-gray-200 flex-1">
                {{ str_replace(':count', $venueDuplicateGroupCount, __('messages.merge_venues_banner')) }}
            </div>
        </div>
    </a>
</div>
@endif

@include('role/partials/calendar', ['route' => 'admin', 'tab' => 'schedule'])

@if (count($unscheduled))
<div class="lg:flex lg:h-full lg:flex-col pt-5">
    <header class="flex items-center justify-between ps-6 py-4 lg:flex-none">
        <h1 class="text-base font-semibold leading-6 text-gray-900 dark:text-gray-100">
            {{ __('messages.unscheduled') }}
        </h1>
    </header>
    <ul role="list" class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 pt-5">
        @foreach($unscheduled as $event)
        @if(! $event->starts_at)
        <li class="ap-card col-span-1 flex flex-col divide-y divide-gray-200 dark:divide-gray-700 rounded-lg text-center">
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
            @if (!$isViewer)
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
                    <form method="POST" action="{{ route('event.decline', ['subdomain' => $role->subdomain, 'hash' => App\Utils\UrlUtils::encodeId($event->id)]) }}"
                        class="form-confirm -ms-px flex w-0 flex-1"
                        data-confirm="{{ __('messages.are_you_sure') }}">
                        @csrf
                        <input type="hidden" name="redirect_to" value="schedule">
                        <button type="submit" class="relative inline-flex w-0 flex-1 items-center justify-center gap-x-3 rounded-ee-lg border border-transparent py-4 text-sm font-semibold text-gray-900 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                            <svg class="h-5 w-5 text-gray-400 dark:text-gray-500" viewBox="0 0 24 24" fill="currentColor"
                                aria-hidden="true">
                                <path
                                    d="M12,2C17.53,2 22,6.47 22,12C22,17.53 17.53,22 12,22C6.47,22 2,17.53 2,12C2,6.47 6.47,2 12,2M15.59,7L12,10.59L8.41,7L7,8.41L10.59,12L7,15.59L8.41,17L12,13.41L15.59,17L17,15.59L13.41,12L17,8.41L15.59,7Z" />
                            </svg>
                            {{ __('messages.decline') }}
                        </button>
                    </form>
                </div>
            </div>
            @endif
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

});
</script>
