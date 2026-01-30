<x-app-admin-layout>

    {{-- Admin Navigation Tabs --}}
    <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
        <div class="flex justify-between items-center">
            <nav class="-mb-px flex space-x-8">
                <a href="{{ route('admin.dashboard') }}"
                    class="whitespace-nowrap border-b-2 border-transparent px-1 pb-4 text-base font-medium text-gray-500 dark:text-gray-400 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-700 dark:hover:text-gray-300">
                    @lang('messages.dashboard')
                </a>
                @if (config('app.hosted') || config('app.is_nexus'))
                <a href="{{ route('admin.plans') }}"
                    class="whitespace-nowrap border-b-2 border-[#4E81FA] px-1 pb-4 text-base font-medium text-[#4E81FA]">
                    @lang('messages.plans')
                </a>
                @endif
                @if (!config('app.hosted') || config('app.is_nexus'))
                <a href="{{ route('blog.admin.index') }}"
                    class="whitespace-nowrap border-b-2 border-transparent px-1 pb-4 text-base font-medium text-gray-500 dark:text-gray-400 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-700 dark:hover:text-gray-300">
                    @lang('messages.blog')
                </a>
                @endif
            </nav>
            <button onclick="window.location.reload()" class="mb-4 inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                @lang('messages.refresh')
            </button>
        </div>
    </div>

    <div class="max-w-3xl mx-auto space-y-6">
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
            <a href="{{ route('admin.plans') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                @lang('messages.back_to_plans')
            </a>
        </div>

        {{-- Current Status Info Box --}}
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
            <h3 class="text-sm font-medium text-blue-800 dark:text-blue-300 mb-3">@lang('messages.current_subscription_status')</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-gray-600 dark:text-gray-400">@lang('messages.status'):</span>
                    <span class="ml-2 font-medium text-gray-900 dark:text-white capitalize">{{ str_replace('_', ' ', $role->subscriptionStatusLabel()) }}</span>
                </div>
                <div>
                    <span class="text-gray-600 dark:text-gray-400">@lang('messages.stripe_customer'):</span>
                    <span class="ml-2 font-medium text-gray-900 dark:text-white">{{ $role->stripe_id ?: __('messages.none') }}</span>
                </div>
                @if ($role->trial_ends_at)
                <div>
                    <span class="text-gray-600 dark:text-gray-400">@lang('messages.trial_ends'):</span>
                    <span class="ml-2 font-medium text-gray-900 dark:text-white">{{ $role->trial_ends_at->format('M d, Y') }}</span>
                </div>
                @endif
                @if ($role->hasActiveSubscription())
                <div>
                    <span class="text-gray-600 dark:text-gray-400">@lang('messages.active_subscription'):</span>
                    <span class="ml-2 font-medium text-green-600 dark:text-green-400">@lang('messages.yes')</span>
                </div>
                @endif
            </div>
        </div>

        {{-- Edit Form --}}
        <form method="POST" action="{{ route('admin.plans.update', ['role' => $role->encodeId()]) }}" class="bg-white dark:bg-gray-800 rounded-lg shadow">
            @csrf
            @method('PUT')

            <div class="p-6 space-y-6">
                {{-- Plan Type --}}
                <div>
                    <label for="plan_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">@lang('messages.plan_type')</label>
                    <select name="plan_type" id="plan_type" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
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
                    <select name="plan_term" id="plan_term" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
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
                    <input type="date" name="plan_expires" id="plan_expires" value="{{ $role->plan_expires }}"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('plan_expires')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror

                    {{-- Quick Action Buttons --}}
                    <div class="mt-3 flex flex-wrap gap-2">
                        <button type="button" onclick="addDays(30)" class="inline-flex items-center px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md text-xs font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            @lang('messages.add_30_days')
                        </button>
                        <button type="button" onclick="addDays(90)" class="inline-flex items-center px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md text-xs font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            @lang('messages.add_90_days')
                        </button>
                        <button type="button" onclick="addDays(365)" class="inline-flex items-center px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md text-xs font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            @lang('messages.add_1_year')
                        </button>
                        <button type="button" onclick="clearExpiration()" class="inline-flex items-center px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md text-xs font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            @lang('messages.clear')
                        </button>
                    </div>
                </div>
            </div>

            {{-- Form Actions --}}
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 rounded-b-lg flex items-center justify-end gap-4">
                <a href="{{ route('admin.plans') }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    @lang('messages.cancel')
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    @lang('messages.save_changes')
                </button>
            </div>
        </form>
    </div>

    <script {!! nonce_attr() !!}>
        function addDays(days) {
            const input = document.getElementById('plan_expires');
            let startDate;

            if (input.value) {
                startDate = new Date(input.value);
            } else {
                startDate = new Date();
            }

            startDate.setDate(startDate.getDate() + days);
            input.value = startDate.toISOString().split('T')[0];
        }

        function clearExpiration() {
            document.getElementById('plan_expires').value = '';
        }
    </script>

</x-app-admin-layout>
