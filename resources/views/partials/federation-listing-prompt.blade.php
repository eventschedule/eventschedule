{{-- Asks a schedule owner to list their schedules on the Event Schedule network, once the
     install has joined it. Every schedule starts undecided and nothing is shared until someone
     says yes, so without this an approved install sends nothing at all.

     $listingSchedules: from FederationService::listingPromptSchedules() - undecided, not
     dismissed, and each with at least one event that would be shared (shareable_count). On the
     dashboard these are the viewer's OWN schedules, a batch at a time; on a schedule page, that
     one schedule if they can edit it. Named so it cannot collide with the dashboard's own
     $schedules, which an include would otherwise inherit.
     $padded: bottom spacing for pages that are not a space-y stack. Always passed explicitly.

     Every schedule the button would list is named, and each can be unticked: nothing goes onto
     the network that the viewer was not shown. Dismissing leaves the schedules undecided (an
     explicit "Not listed" would veto co-listed events) and is remembered per schedule.

     Same blue invitation panel as the adoption prompt (partials/federation-prompt.blade.php),
     and v-pre for the same reason: schedule names are user data inside a Vue-mounted page. --}}
@php
    $listingCount = $listingSchedules->count();
    $listingHashes = $listingSchedules->map(fn ($schedule) => \App\Utils\UrlUtils::encodeId($schedule->id));
@endphp
<div class="{{ ! empty($padded) ? 'pb-4' : '' }}">
    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg p-3" v-pre>
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-blue-500 dark:text-blue-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
            </svg>

            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                    {{ trans_choice('messages.federation_listing_prompt_title', $listingCount, ['count' => $listingCount, 'name' => $listingSchedules->first()->name]) }}
                </p>
                <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">
                    {{ __('messages.federation_listing_prompt_body') }}
                </p>

                <form id="federation-list-form" method="POST" action="{{ route('home.federation_list') }}">
                    @csrf

                    @if ($listingCount > 1)
                        {{-- No max-height: every row is ticked, so every row has to be in view.
                             The service offers a batch small enough for that. --}}
                        <ul class="mt-3 space-y-1.5">
                            @foreach ($listingSchedules as $index => $schedule)
                                <li>
                                    <label class="flex items-start gap-2 text-sm cursor-pointer">
                                        <input type="checkbox" name="schedules[]" value="{{ $listingHashes[$index] }}" checked
                                               class="mt-0.5 rounded border-gray-300 dark:border-gray-600 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                                        <span class="min-w-0">
                                            <span class="font-medium text-gray-900 dark:text-gray-100 break-words">{{ $schedule->name }}</span>
                                            <span class="text-gray-500 dark:text-gray-400">
                                                &middot; {{ trans_choice('messages.federation_would_share_count', $schedule->shareable_count, ['count' => number_format($schedule->shareable_count)]) }}
                                            </span>
                                        </span>
                                    </label>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <input type="hidden" name="schedules[]" value="{{ $listingHashes->first() }}">
                    @endif
                </form>

                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    {{ __('messages.federation_listing_prompt_reassurance', [
                        'edit' => __('messages.edit_schedule'),
                        'schedule_settings' => __('messages.schedule_settings'),
                        'advanced' => __('messages.advanced'),
                    ]) }}
                </p>

                {{-- Wraps on mobile. Forward action last. --}}
                <div class="mt-3 flex flex-wrap items-center gap-4">
                    {{-- Its own form, carrying exactly the schedules offered above, so "Not now"
                         answers for what was shown and nothing created later. --}}
                    <form method="POST" action="{{ route('home.federation_list_dismiss') }}">
                        @csrf
                        @foreach ($listingHashes as $hash)
                            <input type="hidden" name="schedules[]" value="{{ $hash }}">
                        @endforeach
                        <button type="submit"
                                aria-label="{{ __('messages.federation_listing_prompt_dismiss_label') }}"
                                class="text-xs text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 underline">
                            {{ __('messages.dismiss') }}
                        </button>
                    </form>

                    {{-- nexus_url, not marketing_url: the docs only exist on the nexus. --}}
                    <x-link href="{{ rtrim(config('app.nexus_url'), '/') }}/docs/selfhost/federation#per-schedule" target="_blank"
                            class="text-xs font-medium">
                        {{ __('messages.learn_more') }}
                    </x-link>

                    {{-- type="submit" is not the component's default. form= because the button
                         sits after the dismiss form rather than inside its own. --}}
                    <x-brand-button type="submit" form="federation-list-form">
                        {{ __('messages.federation_listing_prompt_button') }}
                    </x-brand-button>
                </div>
            </div>
        </div>
    </div>
</div>
