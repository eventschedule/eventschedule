<x-app-admin-layout>

    <div class="max-w-4xl mx-auto">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">{{ __('messages.referral_program') }}</h2>

        @if (session('message'))
            <div class="mb-6 p-4 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700">
                <p class="text-green-800 dark:text-green-200">{{ session('message') }}</p>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 p-4 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700">
                <p class="text-red-800 dark:text-red-200">{{ session('error') }}</p>
            </div>
        @endif

        {{-- Referral Link --}}
        <div id="referral-link" class="mb-8 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ __('messages.your_referral_link') }}</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">{{ __('messages.referral_link_description') }}</p>
            <div class="flex gap-2">
                <input type="text" value="{{ $referralUrl }}" readonly
                    id="referral-url-input"
                    class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm shadow-sm focus:border-[#4E81FA] focus:ring-[#4E81FA]">
                <button type="button" id="copy-referral-link"
                    class="inline-flex items-center rounded-lg bg-[#4E81FA] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 transition-colors">
                    <svg class="w-4 h-4 me-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                    </svg>
                    <span id="copy-btn-text">{{ __('messages.copy_link') }}</span>
                </button>
            </div>
        </div>

        {{-- Stats --}}
        <div id="referral-dashboard" class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 text-center">
                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $totalReferrals }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('messages.total_referrals') }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 text-center">
                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $awaitingSubscription }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('messages.awaiting_subscription') }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 text-center">
                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $awaitingQualification }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('messages.awaiting_qualification') }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 text-center">
                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $creditsEarned }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('messages.credits_earned') }}</div>
            </div>
        </div>

        {{-- Pending Credits --}}
        @if ($qualifiedCredits->isNotEmpty())
        <div id="referral-credits" class="mb-8 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                <svg class="w-5 h-5 inline-block text-green-600 dark:text-green-400 me-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                {{ __('messages.credits_ready_to_apply') }}
            </h3>

            @foreach ($qualifiedCredits as $credit)
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 mb-3 last:mb-0">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $credit->plan_type === 'enterprise' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300' }}">
                            {{ ucfirst($credit->plan_type) }}
                        </span>
                        <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">
                            {{ $credit->plan_type === 'enterprise' ? '$15' : '$5' }} {{ __('messages.credit') }}
                        </span>
                    </div>
                    <form action="{{ route('referrals.apply_credit') }}" method="POST" class="flex items-center gap-2">
                        @csrf
                        <input type="hidden" name="referral_id" value="{{ \App\Utils\UrlUtils::encodeId($credit->id) }}">
                        <select name="role_id" required data-searchable
                            class="rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm shadow-sm focus:border-[#4E81FA] focus:ring-[#4E81FA]">
                            <option value="">{{ __('messages.select_schedule') }}</option>
                            @foreach ($ownedRoles as $role)
                            <option value="{{ \App\Utils\UrlUtils::encodeId($role->id) }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="inline-flex items-center rounded-lg bg-[#4E81FA] px-4 py-3 text-base font-semibold text-white shadow-sm hover:bg-blue-500 transition-colors">
                            {{ __('messages.apply_credit') }}
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- How It Works --}}
        <div id="referral-how-it-works" class="mb-8 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('messages.how_it_works') }}</h3>
            <div class="grid md:grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/50 text-[#4E81FA] flex items-center justify-center mx-auto mb-3 text-lg font-bold">1</div>
                    <h4 class="font-medium text-gray-900 dark:text-white mb-1">{{ __('messages.referral_step_1_title') }}</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('messages.referral_step_1_description') }}</p>
                </div>
                <div class="text-center">
                    <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/50 text-[#4E81FA] flex items-center justify-center mx-auto mb-3 text-lg font-bold">2</div>
                    <h4 class="font-medium text-gray-900 dark:text-white mb-1">{{ __('messages.referral_step_2_title') }}</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('messages.referral_step_2_description') }}</p>
                </div>
                <div class="text-center">
                    <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/50 text-[#4E81FA] flex items-center justify-center mx-auto mb-3 text-lg font-bold">3</div>
                    <h4 class="font-medium text-gray-900 dark:text-white mb-1">{{ __('messages.referral_step_3_title') }}</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('messages.referral_step_3_description') }}</p>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('messages.referral_credit_values') }}
                </p>
            </div>
        </div>

        {{-- Referral History --}}
        @if ($referralHistory->isNotEmpty())
        <div id="referral-history" class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('messages.referral_history') }}</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <x-sortable-header column="created_at" :sortBy="$sortBy" :sortDir="$sortDir" class="px-6 py-3">{{ __('messages.date') }}</x-sortable-header>
                            <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('messages.referred_user') }}</th>
                            <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('messages.plan_tier') }}</th>
                            <x-sortable-header column="status" :sortBy="$sortBy" :sortDir="$sortDir" class="px-6 py-3">{{ __('messages.status') }}</x-sortable-header>
                            <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('messages.credited_to') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($referralHistory as $referral)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ $referral->created_at->format('M j, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                @php
                                    $email = $referral->referredUser->email ?? '';
                                    $parts = explode('@', $email);
                                    $masked = substr($parts[0], 0, 2) . '***@' . ($parts[1] ?? '');
                                @endphp
                                {{ $masked }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($referral->plan_type)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $referral->plan_type === 'enterprise' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300' }}">
                                    {{ ucfirst($referral->plan_type) }}
                                </span>
                                @else
                                <span class="text-sm text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($referral->status === 'pending')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                    {{ __('messages.pending') }}
                                </span>
                                @elseif ($referral->status === 'subscribed')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                    {{ __('messages.status_subscribed') }}
                                </span>
                                @elseif ($referral->status === 'qualified')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                    {{ __('messages.qualified') }}
                                </span>
                                @elseif ($referral->status === 'credited')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-200">
                                    {{ __('messages.credited') }}
                                </span>
                                @elseif ($referral->status === 'expired')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">
                                    {{ __('messages.expired') }}
                                </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                {{ $referral->creditedRole->name ?? '-' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($referralHistory->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $referralHistory->links() }}
            </div>
            @endif
        </div>
        @endif
    </div>

    <script {!! nonce_attr() !!}>
        document.addEventListener('click', function(e) {
            var header = e.target.closest('[data-sort]');
            if (header) {
                var url = new URL(window.location.href);
                var currentSort = url.searchParams.get('sort_by') || 'created_at';
                var currentDir = url.searchParams.get('sort_dir') || 'desc';
                var sortBy = header.getAttribute('data-sort');
                url.searchParams.set('sort_by', sortBy);
                url.searchParams.set('sort_dir', currentSort === sortBy && currentDir === 'asc' ? 'desc' : 'asc');
                url.searchParams.delete('page');
                window.location.href = url.toString();
            }
        });

        document.getElementById('copy-referral-link').addEventListener('click', function() {
            var input = document.getElementById('referral-url-input');
            navigator.clipboard.writeText(input.value).then(function() {
                var btnText = document.getElementById('copy-btn-text');
                var original = btnText.textContent;
                btnText.textContent = '{{ __("messages.link_copied") }}';
                setTimeout(function() {
                    btnText.textContent = original;
                }, 2000);
            });
        });
    </script>

</x-app-admin-layout>
