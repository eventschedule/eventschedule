@php
    use App\Utils\UrlUtils;

    // Allocated seating is Enterprise. The tab still renders below the plan so a locked schedule
    // sees what it buys rather than a dead link - the same call the promo-codes tab makes.
    $canSeat = $role->seatingEnabled();
    $plans = $seatingPlans;
    // viewAdmin() only checks isMember, so a viewer can reach ?tab=seating by URL even though the
    // nav entry is isEditor-gated. Every write here 403s, but showing the buttons anyway is a
    // trap - matches how show-admin-appointments guards on the same variable.
    $canEdit = ! $isViewer;
@endphp

<div class="space-y-4">

    @if (! $canSeat)
        <x-plan-gate
            tier="enterprise"
            :role="$role"
            :subdomain="$role->subdomain"
            :title="__('messages.seating_plans')"
            :bullets="[
                __('messages.seating_gate_designer'),
                __('messages.seating_gate_pick'),
                __('messages.seating_gate_boxoffice'),
                __('messages.seating_gate_reuse'),
            ]"
            :learnMoreUrl="marketing_url('/docs/allocated-seating')" />
    @else

        <div class="ap-card rounded-xl p-6">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('messages.seating_plans') }}</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 max-w-2xl">{{ __('messages.seating_plans_help') }}</p>
                </div>
                @if ($canEdit)
                <form method="POST" action="{{ route('seating.store', ['subdomain' => $role->subdomain]) }}"
                      class="flex items-end gap-2 shrink-0">
                    @csrf
                    <div>
                        <x-input-label for="new_seating_plan_name" :value="__('messages.name')" class="sr-only" />
                        <x-text-input id="new_seating_plan_name" name="name" type="text" required
                                      maxlength="255"
                                      placeholder="{{ __('messages.seating_plan_name_placeholder') }}"
                                      class="w-56" />
                    </div>
                    <x-brand-button type="submit">{{ __('messages.seating_new_plan') }}</x-brand-button>
                </form>
                @endif
            </div>
        </div>

        @if ($plans->isEmpty())
            <div class="ap-card rounded-xl p-10 text-center">
                <svg class="mx-auto h-10 w-10 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24"
                     stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
                <h3 class="mt-3 text-base font-medium text-gray-900 dark:text-gray-100">{{ __('messages.seating_no_plans') }}</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.seating_no_plans_help') }}</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                @foreach ($plans as $plan)
                    @php
                        $hash = UrlUtils::encodeId($plan->id);
                        $seats = $plan->seatCount();
                        $standing = $plan->standingCapacity();
                        // What the plan is committed to, so Delete and Duplicate are decisions
                        // rather than guesses. Per-row for the same reason seatCount() is.
                        $usage = $plan->usage();
                    @endphp
                    <div class="ap-card rounded-xl p-6 flex flex-col">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 break-words">{{ $plan->name }}</h3>

                        <dl class="mt-3 flex flex-wrap gap-x-6 gap-y-1 text-sm text-gray-500 dark:text-gray-400">
                            <div class="flex gap-1">
                                <dt>{{ __('messages.seating_seats') }}:</dt>
                                <dd class="font-medium text-gray-700 dark:text-gray-300">{{ number_format($seats) }}</dd>
                            </div>
                            @if ($standing)
                                <div class="flex gap-1">
                                    <dt>{{ __('messages.seating_standing') }}:</dt>
                                    <dd class="font-medium text-gray-700 dark:text-gray-300">{{ number_format($standing) }}</dd>
                                </div>
                            @endif
                            {{-- A dt/dd pair rather than a sentence: the number never sits next to
                                 a count-noun, so no language needs singular/plural agreement with
                                 it - which is what produced "1 events" the first time round. --}}
                            @if ($usage['events'])
                                <div class="flex gap-1">
                                    <dt>{{ __('messages.events') }}:</dt>
                                    <dd class="font-medium text-gray-700 dark:text-gray-300">{{ number_format($usage['events']) }}</dd>
                                </div>
                            @endif
                            @if ($usage['sold'])
                                <div class="flex gap-1">
                                    <dt>{{ __('messages.seating_count_sold') }}:</dt>
                                    <dd class="font-medium text-gray-700 dark:text-gray-300">{{ number_format($usage['sold']) }}</dd>
                                </div>
                            @endif
                        </dl>

                        @if ($plan->description)
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $plan->description }}</p>
                        @endif

                        <div class="mt-auto pt-4 flex flex-wrap items-center gap-2">
                            @if ($canEdit)
                            <x-brand-link :href="route('seating.design', ['subdomain' => $role->subdomain, 'hash' => $hash])">
                                {{ __('messages.seating_open_designer') }}
                            </x-brand-link>
                            @endif

                            @if ($canEdit)
                            <form method="POST" action="{{ route('seating.duplicate', ['subdomain' => $role->subdomain, 'hash' => $hash]) }}">
                                @csrf
                                <button type="submit" class="px-4 py-3 text-base rounded-md font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--brand-blue)] dark:focus:ring-offset-gray-800">
                                    {{ __('messages.seating_duplicate') }}
                                </button>
                            </form>

                            <form method="POST" class="form-confirm ms-auto"
                                  data-confirm="{{ __('messages.are_you_sure') }}"
                                  action="{{ route('seating.destroy', ['subdomain' => $role->subdomain, 'hash' => $hash]) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm font-medium text-red-600 dark:text-red-400 hover:underline">
                                    {{ __('messages.delete') }}
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @endif
</div>
