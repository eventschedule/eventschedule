{{-- Admin Navigation Tabs --}}
@props(['active' => 'dashboard'])

@php
    $insightsActive = in_array($active, ['users', 'revenue', 'analytics', 'usage', 'growth']);
    $manageKeys = ['boost', 'newsletters'];
    if (config('app.hosted')) {
        $manageKeys = array_merge($manageKeys, ['schedules', 'domains', 'referrals', 'blog']);
    }
    $manageActive = in_array($active, $manageKeys);
    $systemActive = in_array($active, ['audit-log', 'queue', 'logs', 'support', 'settings', 'translations', 'federation']);

    // Pending-work badges, shared by the AdminAlertService composer. Queues sit
    // unnoticed without them, and operators then conclude the feature is broken.
    // Keyed ['nav' => dropdown totals, 'tab' => per-item counts]; every count is
    // already gated by install type, so a section that cannot exist here reads 0.
    $navBadges = $adminAlertBadges['nav'] ?? [];
    $tabBadges = $adminAlertBadges['tab'] ?? [];

    $tabActive = 'border-[var(--brand-blue)] text-[var(--brand-blue)]';
    $tabInactive = 'border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-700 dark:hover:text-gray-300';

    $dropdownItem = 'ap-dropdown-link block w-full px-4 py-2 text-start text-sm text-gray-700 dark:text-gray-300';
    $dropdownItemActive = 'ap-dropdown-link block w-full px-4 py-2 text-start text-sm text-[var(--brand-blue)] bg-[var(--brand-blue-a10)]';
@endphp

<div class="ap-tab-container border-b border-gray-200 dark:border-gray-700">
    <div class="flex justify-between items-center">
        <nav class="-mb-px flex gap-8" x-data="{ openDropdown: null }">
            {{-- Dashboard --}}
            <a href="{{ route('admin.dashboard') }}"
                class="whitespace-nowrap border-b-2 {{ $active === 'dashboard' ? $tabActive : $tabInactive }} px-1 pb-4 text-base font-medium">
                @lang('messages.dashboard')
            </a>

            {{-- Insights Dropdown --}}
            <div class="relative" @click.outside="openDropdown = openDropdown === 'insights' ? null : openDropdown">
                <button @click="openDropdown = openDropdown === 'insights' ? null : 'insights'"
                    class="whitespace-nowrap border-b-2 {{ $insightsActive ? $tabActive : $tabInactive }} px-1 pb-4 text-base font-medium inline-flex items-center">
                    @lang('messages.insights')
                    <x-nav-badge :badge="$navBadges['insights'] ?? null" />
                    <svg class="ms-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="openDropdown === 'insights'"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="absolute z-50 mt-0 w-48 rounded-lg shadow-lg ltr:origin-top-left rtl:origin-top-right start-0"
                    style="display: none;"
                    @click="openDropdown = null">
                    <div class="admin-dropdown rounded-lg ring-1 ring-black/5 py-1">
                        <a href="{{ route('admin.users') }}" class="{{ $active === 'users' ? $dropdownItemActive : $dropdownItem }}">
                            @lang('messages.users')
                        </a>
                        <a href="{{ route('admin.revenue') }}" class="{{ $active === 'revenue' ? $dropdownItemActive : $dropdownItem }}">
                            @lang('messages.revenue')
                            <x-nav-badge :badge="$tabBadges['revenue'] ?? null" />
                        </a>
                        <a href="{{ route('admin.analytics') }}" class="{{ $active === 'analytics' ? $dropdownItemActive : $dropdownItem }}">
                            @lang('messages.analytics')
                        </a>
                        <a href="{{ route('admin.usage') }}" class="{{ $active === 'usage' ? $dropdownItemActive : $dropdownItem }}">
                            @lang('messages.usage')
                        </a>
                        @if (config('app.hosted'))
                            <a href="{{ route('admin.growth') }}" class="{{ $active === 'growth' ? $dropdownItemActive : $dropdownItem }}">
                                @lang('messages.growth')
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Manage Dropdown --}}
            <div class="relative" @click.outside="openDropdown = openDropdown === 'manage' ? null : openDropdown">
                <button @click="openDropdown = openDropdown === 'manage' ? null : 'manage'"
                    class="whitespace-nowrap border-b-2 {{ $manageActive ? $tabActive : $tabInactive }} px-1 pb-4 text-base font-medium inline-flex items-center">
                    @lang('messages.manage')
                    <x-nav-badge :badge="$navBadges['manage'] ?? null" />
                    <svg class="ms-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="openDropdown === 'manage'"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="absolute z-50 mt-0 w-48 rounded-lg shadow-lg ltr:origin-top-left rtl:origin-top-right start-0"
                    style="display: none;"
                    @click="openDropdown = null">
                    <div class="admin-dropdown rounded-lg ring-1 ring-black/5 py-1">
                        <a href="{{ route('admin.boost') }}" class="{{ $active === 'boost' ? $dropdownItemActive : $dropdownItem }}">
                            Boost
                            <x-nav-badge :badge="$tabBadges['boost'] ?? null" />
                        </a>
                        @if (config('app.hosted'))
                        <a href="{{ route('admin.schedules') }}" class="{{ $active === 'schedules' ? $dropdownItemActive : $dropdownItem }}">
                            @lang('messages.schedules')
                        </a>
                        <a href="{{ route('admin.domains') }}" class="{{ $active === 'domains' ? $dropdownItemActive : $dropdownItem }}">
                            @lang('messages.domains')
                            <x-nav-badge :badge="$tabBadges['domains'] ?? null" />
                        </a>
                        <a href="{{ route('admin.referrals') }}" class="{{ $active === 'referrals' ? $dropdownItemActive : $dropdownItem }}">
                            @lang('messages.referrals')
                        </a>
                        @endif
                        <a href="{{ route('admin.newsletters.index') }}" class="{{ $active === 'newsletters' ? $dropdownItemActive : $dropdownItem }}">
                            @lang('messages.newsletters')
                        </a>
                        @if (config('app.hosted'))
                        <a href="{{ route('blog.admin.index') }}" class="{{ $active === 'blog' ? $dropdownItemActive : $dropdownItem }}">
                            @lang('messages.blog')
                        </a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- System Dropdown --}}
            <div class="relative" @click.outside="openDropdown = openDropdown === 'system' ? null : openDropdown">
                <button @click="openDropdown = openDropdown === 'system' ? null : 'system'"
                    class="whitespace-nowrap border-b-2 {{ $systemActive ? $tabActive : $tabInactive }} px-1 pb-4 text-base font-medium inline-flex items-center">
                    @lang('messages.system')
                    <x-nav-badge :badge="$navBadges['system'] ?? null" />
                    <svg class="ms-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="openDropdown === 'system'"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="absolute z-50 mt-0 w-48 rounded-lg shadow-lg ltr:origin-top-left rtl:origin-top-right start-0"
                    style="display: none;"
                    @click="openDropdown = null">
                    <div class="admin-dropdown rounded-lg ring-1 ring-black/5 py-1">
                        <a href="{{ route('admin.audit_log') }}" class="{{ $active === 'audit-log' ? $dropdownItemActive : $dropdownItem }}">
                            @lang('messages.audit_log')
                        </a>
                        <a href="{{ route('admin.queue') }}" class="{{ $active === 'queue' ? $dropdownItemActive : $dropdownItem }}">
                            @lang('messages.queue')
                            <x-nav-badge :badge="$tabBadges['queue'] ?? null" />
                        </a>
                        <a href="{{ route('admin.logs') }}" class="{{ $active === 'logs' ? $dropdownItemActive : $dropdownItem }}">
                            Logs
                        </a>
                        <a href="{{ route('admin.settings') }}" class="{{ $active === 'settings' ? $dropdownItemActive : $dropdownItem }}">
                            @lang('messages.settings')
                        </a>
                        <a href="{{ route('admin.translations') }}" class="{{ $active === 'translations' ? $dropdownItemActive : $dropdownItem }}">
                            @lang('messages.translations')
                            <x-nav-badge :badge="$tabBadges['translations'] ?? null" />
                        </a>
                        @if (config('app.is_nexus'))
                        <a href="{{ route('admin.federation') }}" class="{{ $active === 'federation' ? $dropdownItemActive : $dropdownItem }}">
                            @lang('messages.federation')
                            <x-nav-badge :badge="$tabBadges['federation'] ?? null" />
                        </a>
                        @endif
                        @if (config('app.hosted'))
                        <a href="{{ route('admin.support') }}" class="{{ $active === 'support' ? $dropdownItemActive : $dropdownItem }}">
                            Support
                            <x-nav-badge :badge="$tabBadges['support'] ?? null" />
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </nav>
        <button id="admin-nav-refresh-btn" class="mb-4 inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
            <svg class="w-4 h-4 me-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            @lang('messages.refresh')
        </button>
    </div>
</div>

<script {!! nonce_attr() !!}>
    document.getElementById('admin-nav-refresh-btn').addEventListener('click', function() {
        window.location.reload();
    });
</script>
