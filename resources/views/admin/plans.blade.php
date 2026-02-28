<x-app-admin-layout>

    <div class="space-y-6">
        @include('admin.partials._navigation', ['active' => 'plans'])

        {{-- Row 1: Plan Breakdown --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            {{-- Free Count --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="p-3 bg-gray-100 dark:bg-gray-700 rounded-full">
                            <svg class="w-6 h-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ms-4 flex-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.free')</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($freeCount) }}</p>
                    </div>
                </div>
            </div>

            {{-- Pro Count --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-full">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ms-4 flex-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.pro')</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($proCount) }}</p>
                    </div>
                </div>
            </div>

            {{-- Enterprise Count --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="p-3 bg-purple-100 dark:bg-purple-900 rounded-full">
                            <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                    </div>
                    <div class="ms-4 flex-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.enterprise')</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($enterpriseCount) }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Row 2: Payment/Status Breakdown --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Stripe Paid --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="p-3 bg-green-100 dark:bg-green-900 rounded-full">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ms-4 flex-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.stripe_paid')</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stripePaidCount) }}</p>
                    </div>
                </div>
            </div>

            {{-- Manual --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="p-3 bg-indigo-100 dark:bg-indigo-900 rounded-full">
                            <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ms-4 flex-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.manual')</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($manualPlanCount) }}</p>
                    </div>
                </div>
            </div>

            {{-- On Trial --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="p-3 bg-yellow-100 dark:bg-yellow-900 rounded-full">
                            <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ms-4 flex-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.on_free_trial')</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($trialCount) }}</p>
                    </div>
                </div>
            </div>

            {{-- Expiring Soon --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="p-3 bg-amber-100 dark:bg-amber-900 rounded-full">
                            <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ms-4 flex-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.expiring_soon')</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($expiringSoon) }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <form method="GET" action="{{ route('admin.plans') }}" class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1 relative">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="{{ __('messages.search_schedules') }}" autocomplete="off" data-subdomain-autocomplete
                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <div data-subdomain-dropdown class="hidden absolute left-0 right-0 mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow-lg max-h-60 overflow-y-auto z-50"></div>
                </div>
                <div class="w-full sm:w-40">
                    <select name="plan_type" class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">@lang('messages.all_plans')</option>
                        <option value="free" {{ request('plan_type') === 'free' ? 'selected' : '' }}>@lang('messages.free')</option>
                        <option value="pro" {{ request('plan_type') === 'pro' ? 'selected' : '' }}>@lang('messages.pro')</option>
                        <option value="enterprise" {{ request('plan_type') === 'enterprise' ? 'selected' : '' }}>@lang('messages.enterprise')</option>
                    </select>
                </div>
                <div class="w-full sm:w-40">
                    <select name="status" class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">@lang('messages.all_status')</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>@lang('messages.active')</option>
                        <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>@lang('messages.expired')</option>
                        <option value="trial" {{ request('status') === 'trial' ? 'selected' : '' }}>@lang('messages.trial')</option>
                    </select>
                </div>
                <div class="w-full sm:w-40">
                    <select name="source" class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">@lang('messages.all_sources')</option>
                        <option value="stripe" {{ request('source') === 'stripe' ? 'selected' : '' }}>@lang('messages.stripe')</option>
                        <option value="manual" {{ request('source') === 'manual' ? 'selected' : '' }}>@lang('messages.manual')</option>
                        <option value="trial" {{ request('source') === 'trial' ? 'selected' : '' }}>@lang('messages.trial')</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        @lang('messages.filter')
                    </button>
                    @if(request('search') || request('plan_type') || request('status') || request('source'))
                        <a href="{{ route('admin.plans') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            @lang('messages.clear')
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Role List Table --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                @lang('messages.schedule')
                            </th>
                            <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                @lang('messages.type')
                            </th>
                            <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                @lang('messages.plan')
                            </th>
                            <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                @lang('messages.term')
                            </th>
                            <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                @lang('messages.expires')
                            </th>
                            <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                @lang('messages.status')
                            </th>
                            <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                @lang('messages.source')
                            </th>
                            <th scope="col" class="px-6 py-3 text-end text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                @lang('messages.actions')
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($roles as $role)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div>
                                            <a href="{{ route('role.view_guest', ['subdomain' => $role->subdomain]) }}" target="_blank" class="text-sm font-medium text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400">
                                                {{ $role->name }}
                                            </a>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $role->subdomain }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-900 dark:text-white capitalize">{{ $role->type }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $planType = $role->plan_type ?? 'free';
                                        $badgeColors = [
                                            'free' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                            'pro' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
                                            'enterprise' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badgeColors[$planType] ?? $badgeColors['free'] }}">
                                        {{ ucfirst($planType) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $role->plan_term ? ucfirst($role->plan_term) : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    @if ($role->trial_ends_at && $role->onGenericTrial())
                                        {{ \Carbon\Carbon::parse($role->trial_ends_at)->format('M d, Y') }}
                                        <div class="text-xs text-yellow-600 dark:text-yellow-400">
                                            @lang('messages.n_days_left', ['count' => now()->diffInDays($role->trial_ends_at)])
                                        </div>
                                    @elseif ($role->plan_expires)
                                        {{ \Carbon\Carbon::parse($role->plan_expires)->format('M d, Y') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $status = $role->subscriptionStatusLabel();
                                        $statusColors = [
                                            'active' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
                                            'trial' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
                                            'cancelled' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
                                            'grace_period' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
                                            'past_due' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
                                            'none' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                            'inactive' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$status] ?? $statusColors['none'] }}">
                                        {{ ucfirst(str_replace('_', ' ', $status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($role->hasActiveSubscription())
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                            @lang('messages.stripe')
                                        </span>
                                    @elseif ($role->onGenericTrial())
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">
                                            @lang('messages.trial')
                                        </span>
                                    @elseif (($role->plan_type ?? 'free') !== 'free' && $role->plan_expires)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300">
                                            @lang('messages.manual')
                                        </span>
                                    @else
                                        <span class="text-sm text-gray-400 dark:text-gray-500">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium">
                                    <a href="{{ route('admin.plans.edit', ['role' => $role->encodeId()]) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                        @lang('messages.edit')
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                    @lang('messages.no_schedules_found')
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($roles->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    {{ $roles->links() }}
                </div>
            @endif
        </div>
    </div>

    @include('admin.partials._subdomain-autocomplete')
</x-app-admin-layout>
