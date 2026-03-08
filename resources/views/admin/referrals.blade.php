<x-app-admin-layout>

    <div class="space-y-6">
        @include('admin.partials._navigation', ['active' => 'referrals'])

        {{-- Stats --}}
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <div class="ap-card rounded-lg border border-gray-200 p-4 text-center">
                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $totalReferrals }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.total_referrals') }}</div>
            </div>
            <div class="ap-card rounded-lg border border-gray-200 p-4 text-center">
                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $pending }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.pending') }}</div>
            </div>
            <div class="ap-card rounded-lg border border-gray-200 p-4 text-center">
                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $subscribed }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.status_subscribed') }}</div>
            </div>
            <div class="ap-card rounded-lg border border-gray-200 p-4 text-center">
                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $qualified }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.qualified') }}</div>
            </div>
            <div class="ap-card rounded-lg border border-gray-200 p-4 text-center">
                <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $credited }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.credited') }}</div>
            </div>
            <div class="ap-card rounded-lg border border-gray-200 p-4 text-center">
                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $conversionRate }}%</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.conversion_rate') }}</div>
            </div>
        </div>

        {{-- Filter --}}
        <div class="flex items-center gap-2">
            <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('messages.filter') }}:</span>
            <a href="{{ route('admin.referrals') }}"
                class="px-3 py-1 rounded-full text-sm {{ !$statusFilter ? 'bg-[var(--brand-button-bg)] text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                {{ __('messages.all') }}
            </a>
            @foreach (['pending', 'subscribed', 'qualified', 'credited', 'expired'] as $status)
            <a href="{{ route('admin.referrals', ['status' => $status]) }}"
                class="px-3 py-1 rounded-full text-sm {{ $statusFilter === $status ? 'bg-[var(--brand-button-bg)] text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                {{ __('messages.' . $status) }}
            </a>
            @endforeach
        </div>

        {{-- Table --}}
        <div class="ap-card rounded-lg border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('messages.date') }}</th>
                            <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('messages.referrer') }}</th>
                            <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('messages.referred_user') }}</th>
                            <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('messages.plan_tier') }}</th>
                            <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('messages.status') }}</th>
                            <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('messages.credited_to') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($referrals as $referral)
                        <tr>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ $referral->created_at->format('M j, Y') }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                {{ $referral->referrer->name ?? '-' }}
                                <div class="text-xs text-gray-400">{{ $referral->referrer->email ?? '' }}</div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                {{ $referral->referredUser->name ?? '-' }}
                                <div class="text-xs text-gray-400">{{ $referral->referredUser->email ?? '' }}</div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if ($referral->plan_type)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $referral->plan_type === 'enterprise' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300' }}">
                                    {{ ucfirst($referral->plan_type) }}
                                </span>
                                @else
                                <span class="text-sm text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if ($referral->status === 'pending')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">{{ __('messages.pending') }}</span>
                                @elseif ($referral->status === 'subscribed')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">{{ __('messages.status_subscribed') }}</span>
                                @elseif ($referral->status === 'qualified')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">{{ __('messages.qualified') }}</span>
                                @elseif ($referral->status === 'credited')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-300">{{ __('messages.credited') }}</span>
                                @elseif ($referral->status === 'expired')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">{{ __('messages.expired') }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                {{ $referral->creditedRole->name ?? '-' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                {{ __('messages.no_referrals_found') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($referrals->hasPages())
            <div class="px-4 py-4 border-t border-gray-200">
                {{ $referrals->links() }}
            </div>
            @endif
        </div>
    </div>

</x-app-admin-layout>
