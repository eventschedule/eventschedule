{{-- Admin Navigation Tabs --}}
@props(['active' => 'dashboard'])

<div class="border-b border-gray-200 dark:border-gray-700">
    <div class="flex justify-between items-center">
        <nav class="-mb-px flex gap-8">
            <a href="{{ route('admin.dashboard') }}"
                class="whitespace-nowrap border-b-2 {{ $active === 'dashboard' ? 'border-[#4E81FA] text-[#4E81FA]' : 'border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-700 dark:hover:text-gray-300' }} px-1 pb-4 text-base font-medium">
                @lang('messages.dashboard')
            </a>
            <a href="{{ route('admin.users') }}"
                class="whitespace-nowrap border-b-2 {{ $active === 'users' ? 'border-[#4E81FA] text-[#4E81FA]' : 'border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-700 dark:hover:text-gray-300' }} px-1 pb-4 text-base font-medium">
                @lang('messages.users')
            </a>
            <a href="{{ route('admin.revenue') }}"
                class="whitespace-nowrap border-b-2 {{ $active === 'revenue' ? 'border-[#4E81FA] text-[#4E81FA]' : 'border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-700 dark:hover:text-gray-300' }} px-1 pb-4 text-base font-medium">
                @lang('messages.revenue')
            </a>
            <a href="{{ route('admin.analytics') }}"
                class="whitespace-nowrap border-b-2 {{ $active === 'analytics' ? 'border-[#4E81FA] text-[#4E81FA]' : 'border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-700 dark:hover:text-gray-300' }} px-1 pb-4 text-base font-medium">
                @lang('messages.analytics')
            </a>
            @if (config('app.hosted'))
            <a href="{{ route('admin.usage') }}"
                class="whitespace-nowrap border-b-2 {{ $active === 'usage' ? 'border-[#4E81FA] text-[#4E81FA]' : 'border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-700 dark:hover:text-gray-300' }} px-1 pb-4 text-base font-medium">
                Usage
            </a>
            @endif
            @if (config('app.hosted') || config('app.is_nexus'))
            <a href="{{ route('admin.plans') }}"
                class="whitespace-nowrap border-b-2 {{ $active === 'plans' ? 'border-[#4E81FA] text-[#4E81FA]' : 'border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-700 dark:hover:text-gray-300' }} px-1 pb-4 text-base font-medium">
                @lang('messages.plans')
            </a>
            @endif
            <a href="{{ route('admin.audit_log') }}"
                class="whitespace-nowrap border-b-2 {{ $active === 'audit-log' ? 'border-[#4E81FA] text-[#4E81FA]' : 'border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-700 dark:hover:text-gray-300' }} px-1 pb-4 text-base font-medium">
                Audit Log
            </a>
            <a href="{{ route('admin.queue') }}"
                class="whitespace-nowrap border-b-2 {{ $active === 'queue' ? 'border-[#4E81FA] text-[#4E81FA]' : 'border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-700 dark:hover:text-gray-300' }} px-1 pb-4 text-base font-medium">
                Queue
            </a>
            @if (!config('app.hosted') || config('app.is_nexus'))
            <a href="{{ route('blog.admin.index') }}"
                class="whitespace-nowrap border-b-2 {{ $active === 'blog' ? 'border-[#4E81FA] text-[#4E81FA]' : 'border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-700 dark:hover:text-gray-300' }} px-1 pb-4 text-base font-medium">
                @lang('messages.blog')
            </a>
            @endif
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
