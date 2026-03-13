<x-app-admin-layout>

    <div class="space-y-4">
        @include('admin.partials._navigation', ['active' => 'plans'])

    <div class="max-w-3xl mx-auto space-y-4">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">@lang('messages.edit_plan')</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    <a href="{{ route('role.view_guest', ['subdomain' => $role->subdomain]) }}" target="_blank" class="hover:text-indigo-600 dark:hover:text-indigo-400">
                        {{ $role->name }}
                    </a>
                    <span class="mx-2">&bull;</span>
                    <span>{{ $role->subdomain }}</span>
                </p>
            </div>
            <x-secondary-link :href="route('admin.plans')">
                @lang('messages.back_to_plans')
            </x-secondary-link>
        </div>

        {{-- Current Status Info Box --}}
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
            <h3 class="text-sm font-medium text-blue-800 dark:text-blue-300 mb-3">@lang('messages.current_subscription_status')</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-gray-600 dark:text-gray-400">@lang('messages.status'):</span>
                    <span class="ms-2 font-medium text-gray-900 dark:text-white capitalize">{{ str_replace('_', ' ', $role->subscriptionStatusLabel()) }}</span>
                </div>
                <div>
                    <span class="text-gray-600 dark:text-gray-400">@lang('messages.stripe_customer'):</span>
                    <span class="ms-2 font-medium text-gray-900 dark:text-white">{{ $role->stripe_id ?: __('messages.none') }}</span>
                </div>
                @if ($role->trial_ends_at)
                <div>
                    <span class="text-gray-600 dark:text-gray-400">@lang('messages.trial_ends'):</span>
                    <span class="ms-2 font-medium text-gray-900 dark:text-white">{{ $role->trial_ends_at->format('M d, Y') }}</span>
                </div>
                @endif
                @if ($role->hasActiveSubscription())
                <div>
                    <span class="text-gray-600 dark:text-gray-400">@lang('messages.active_subscription'):</span>
                    <span class="ms-2 font-medium text-green-600 dark:text-green-400">@lang('messages.yes')</span>
                </div>
                @endif
            </div>
        </div>

        {{-- Edit Form --}}
        <form method="POST" action="{{ route('admin.plans.update', ['role' => $role->encodeId()]) }}" class="ap-card rounded-xl shadow">
            @csrf
            @method('PUT')

            <div class="p-6 space-y-6">
                {{-- Plan Type --}}
                <div>
                    <label for="plan_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">@lang('messages.plan_type')</label>
                    <select name="plan_type" id="plan_type" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                        <option value="free" {{ ($role->plan_type ?? 'free') === 'free' ? 'selected' : '' }}>@lang('messages.free')</option>
                        <option value="pro" {{ $role->plan_type === 'pro' ? 'selected' : '' }}>@lang('messages.pro')</option>
                        <option value="enterprise" {{ $role->plan_type === 'enterprise' ? 'selected' : '' }}>@lang('messages.enterprise')</option>
                    </select>
                    @error('plan_type')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Plan Term --}}
                <div>
                    <label for="plan_term" class="block text-sm font-medium text-gray-700 dark:text-gray-300">@lang('messages.plan_term')</label>
                    <select name="plan_term" id="plan_term" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                        <option value="">@lang('messages.none')</option>
                        <option value="month" {{ $role->plan_term === 'month' ? 'selected' : '' }}>@lang('messages.monthly')</option>
                        <option value="year" {{ $role->plan_term === 'year' ? 'selected' : '' }}>@lang('messages.yearly')</option>
                    </select>
                    @error('plan_term')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Plan Expires --}}
                <div>
                    <label for="plan_expires" class="block text-sm font-medium text-gray-700 dark:text-gray-300">@lang('messages.plan_expires')</label>
                    <input type="text" name="plan_expires" id="plan_expires" value="{{ $role->plan_expires }}"
                        class="datepicker-date mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                    @error('plan_expires')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror

                    {{-- Quick Action Buttons --}}
                    <div class="mt-3 flex flex-wrap gap-2">
                        <button type="button" data-add-days="30" class="js-add-days inline-flex items-center px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg text-xs font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 transition-all duration-200">
                            @lang('messages.add_30_days')
                        </button>
                        <button type="button" data-add-days="90" class="js-add-days inline-flex items-center px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg text-xs font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 transition-all duration-200">
                            @lang('messages.add_90_days')
                        </button>
                        <button type="button" data-add-days="365" class="js-add-days inline-flex items-center px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg text-xs font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 transition-all duration-200">
                            @lang('messages.add_1_year')
                        </button>
                        <button type="button" id="clear-expiration-btn" class="inline-flex items-center px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg text-xs font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 transition-all duration-200">
                            @lang('messages.clear')
                        </button>
                    </div>
                </div>
            </div>

            {{-- Form Actions --}}
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 rounded-b-lg flex items-center justify-end gap-4">
                <x-secondary-link :href="route('admin.plans')">
                    @lang('messages.cancel')
                </x-secondary-link>
                <x-brand-button type="submit">
                    @lang('messages.save_changes')
                </x-brand-button>
            </div>
        </form>
    </div>
    </div>

    <script {!! nonce_attr() !!}>
        document.addEventListener('DOMContentLoaded', function() {
            var fp = flatpickr('#plan_expires', {
                allowInput: true,
                enableTime: false,
                altInput: true,
                altFormat: 'M j, Y',
                dateFormat: 'Y-m-d',
            });

            document.querySelectorAll('.js-add-days').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var days = parseInt(this.getAttribute('data-add-days'));
                    var startDate;

                    if (fp.selectedDates.length) {
                        startDate = new Date(fp.selectedDates[0]);
                    } else {
                        startDate = new Date();
                    }

                    startDate.setDate(startDate.getDate() + days);
                    fp.setDate(startDate);
                });
            });

            document.getElementById('clear-expiration-btn').addEventListener('click', function() {
                fp.clear();
            });
        });
    </script>

</x-app-admin-layout>
