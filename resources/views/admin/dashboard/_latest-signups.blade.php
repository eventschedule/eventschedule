{{-- The newest organizers, where each came from, and how far each has got: schedule, event,
     ticket type. Someone still at the first step is the one in amber. --}}
@php
    $stages = [
        __('messages.realtime_no_schedule_yet'),
        __('messages.funnel_stage_saved_schedule'),
        __('messages.funnel_stage_saved_event'),
        __('messages.funnel_stage_saved_ticket'),
    ];
    $latest = $signups['latest'] ?? [];
@endphp

<section class="ap-card rounded-xl flex flex-col">
    <div class="px-4 sm:px-5 pt-4 sm:pt-5 pb-3 flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white">@lang('messages.admin_dash_latest_signups')</h2>
        <span class="text-xs text-gray-500 dark:text-gray-400">@lang('messages.admin_dash_organizers')</span>
    </div>

    @if (! $signups)
        @include('admin.dashboard._failed')
    @elseif (empty($latest))
        <p class="px-5 pb-5 text-sm text-gray-500 dark:text-gray-400">@lang('messages.admin_dash_no_signups')</p>
    @else
        <ul class="divide-y divide-gray-100 dark:divide-white/[0.06]" style="border-top: 1px solid var(--ap-hairline)">
            @foreach ($latest as $person)
                <li class="flex items-center gap-3 px-4 sm:px-5 py-2.5">
                    <span class="w-8 h-8 shrink-0 rounded-full flex items-center justify-center text-xs font-semibold bg-[var(--brand-blue-a10)] text-[var(--brand-blue)]" aria-hidden="true">{{ $person['initials'] }}</span>

                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate"><bdi>{{ $person['name'] }}</bdi></p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                            @if ($person['source'])
                                <bdi>{{ $person['source']['primary'] }}</bdi>
                                @if ($person['source']['secondary'])
                                    &middot; <bdi dir="ltr">{{ $person['source']['secondary'] }}</bdi>
                                @endif
                            @else
                                @lang('messages.realtime_source_unknown')
                            @endif
                        </p>
                        <p class="sm:hidden mt-1 flex items-center gap-2">
                            @include('admin.dashboard._stage', ['stage' => $person['stage'], 'label' => $stages[$person['stage']]])
                        </p>
                    </div>

                    <div class="hidden sm:block shrink-0 w-[12.5rem] min-w-0">
                        <p class="flex items-center gap-2">
                            @include('admin.dashboard._stage', ['stage' => $person['stage'], 'label' => $stages[$person['stage']]])
                        </p>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400 truncate">
                            @if ($person['schedule'])
                                <x-link :href="$person['schedule']['url']" class="text-gray-500 dark:text-gray-400"><bdi>{{ $person['schedule']['name'] }}</bdi></x-link>
                            @else
                                &nbsp;
                            @endif
                        </p>
                    </div>

                    <p class="shrink-0 w-14 text-end text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ $person['created_at']->shortRelativeDiffForHumans() }}</p>
                </li>
            @endforeach
        </ul>

        @if (\Illuminate\Support\Facades\Route::has('admin.realtime'))
            <div class="mt-auto px-4 sm:px-5 py-3 flex items-center justify-end gap-3" style="border-top: 1px solid var(--ap-hairline)">
                <x-link :href="route('admin.realtime')" class="text-sm font-medium whitespace-nowrap">@lang('messages.realtime_view')</x-link>
            </div>
        @endif
    @endif
</section>
