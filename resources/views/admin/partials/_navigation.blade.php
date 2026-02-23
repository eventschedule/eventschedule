{{-- Admin Navigation Tabs --}}
@props(['active' => 'dashboard'])

@php
    $insightsActive = in_array($active, ['users', 'revenue', 'analytics', 'usage']);
    $manageActive = in_array($active, ['boost', 'plans', 'newsletters', 'blog']);
    $systemActive = in_array($active, ['audit-log', 'queue']);

    $tabActive = 'border-[#4E81FA] text-[#4E81FA]';
    $tabInactive = 'border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-700 dark:hover:text-gray-300';

    $dropdownItem = 'block w-full px-4 py-2 text-start text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600';
    $dropdownItemActive = 'block w-full px-4 py-2 text-start text-sm text-[#4E81FA] bg-gray-50 dark:bg-gray-600';
@endphp

<div class="border-b border-gray-200 dark:border-gray-700">
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
                    class="absolute z-50 mt-0 w-48 rounded-md shadow-lg ltr:origin-top-left rtl:origin-top-right start-0"
                    style="display: none;"
                    @click="openDropdown = null">
                    <div class="rounded-md ring-1 ring-black ring-opacity-5 py-1 bg-white dark:bg-gray-700">
                        <a href="{{ route('admin.users') }}" class="{{ $active === 'users' ? $dropdownItemActive : $dropdownItem }}">
                            @lang('messages.users')
                        </a>
                        <a href="{{ route('admin.revenue') }}" class="{{ $active === 'revenue' ? $dropdownItemActive : $dropdownItem }}">
                            @lang('messages.revenue')
                        </a>
                        <a href="{{ route('admin.analytics') }}" class="{{ $active === 'analytics' ? $dropdownItemActive : $dropdownItem }}">
                            @lang('messages.analytics')
                        </a>
                        <a href="{{ route('admin.usage') }}" class="{{ $active === 'usage' ? $dropdownItemActive : $dropdownItem }}">
                            @lang('messages.usage')
                        </a>
                    </div>
                </div>
            </div>

            {{-- Manage Dropdown --}}
            <div class="relative" @click.outside="openDropdown = openDropdown === 'manage' ? null : openDropdown">
                <button @click="openDropdown = openDropdown === 'manage' ? null : 'manage'"
                    class="whitespace-nowrap border-b-2 {{ $manageActive ? $tabActive : $tabInactive }} px-1 pb-4 text-base font-medium inline-flex items-center">
                    @lang('messages.manage')
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
                    class="absolute z-50 mt-0 w-48 rounded-md shadow-lg ltr:origin-top-left rtl:origin-top-right start-0"
                    style="display: none;"
                    @click="openDropdown = null">
                    <div class="rounded-md ring-1 ring-black ring-opacity-5 py-1 bg-white dark:bg-gray-700">
                        <a href="{{ route('admin.boost') }}" class="{{ $active === 'boost' ? $dropdownItemActive : $dropdownItem }}">
                            Boost
                        </a>
                        <a href="{{ route('admin.plans') }}" class="{{ $active === 'plans' ? $dropdownItemActive : $dropdownItem }}">
                            @lang('messages.plans')
                        </a>
                        <a href="{{ route('admin.newsletters.index') }}" class="{{ $active === 'newsletters' ? $dropdownItemActive : $dropdownItem }}">
                            @lang('messages.newsletters')
                        </a>
                        <a href="{{ route('blog.admin.index') }}" class="{{ $active === 'blog' ? $dropdownItemActive : $dropdownItem }}">
                            @lang('messages.blog')
                        </a>
                    </div>
                </div>
            </div>

            {{-- System Dropdown --}}
            <div class="relative" @click.outside="openDropdown = openDropdown === 'system' ? null : openDropdown">
                <button @click="openDropdown = openDropdown === 'system' ? null : 'system'"
                    class="whitespace-nowrap border-b-2 {{ $systemActive ? $tabActive : $tabInactive }} px-1 pb-4 text-base font-medium inline-flex items-center">
                    @lang('messages.system')
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
                    class="absolute z-50 mt-0 w-48 rounded-md shadow-lg ltr:origin-top-left rtl:origin-top-right start-0"
                    style="display: none;"
                    @click="openDropdown = null">
                    <div class="rounded-md ring-1 ring-black ring-opacity-5 py-1 bg-white dark:bg-gray-700">
                        <a href="{{ route('admin.audit_log') }}" class="{{ $active === 'audit-log' ? $dropdownItemActive : $dropdownItem }}">
                            @lang('messages.audit_log')
                        </a>
                        <a href="{{ route('admin.queue') }}" class="{{ $active === 'queue' ? $dropdownItemActive : $dropdownItem }}">
                            @lang('messages.queue')
                        </a>
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
