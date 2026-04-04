<x-app-admin-layout>
    <div class="space-y-4">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ __('messages.create_newsletter') }}</h2>
            <a href="{{ route('newsletter.index', ['role_id' => \App\Utils\UrlUtils::encodeId($role->id)]) }}"
                class="inline-flex items-center justify-center rounded-lg bg-gray-200 dark:bg-gray-700 px-5 py-3 text-base font-semibold text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                {{ __('messages.back') }}
            </a>
        </div>

        @if (session('status'))
        <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-lg text-green-700 dark:text-green-300">
            {{ session('status') }}
        </div>
        @endif

        @include('newsletter.partials._verification-warning')

        @if ($role->newsletterLimit() !== null)
        @php
            $newsletterLimit = $role->newsletterLimit();
            $newsletterUsed = $role->newslettersSentThisMonth();
        @endphp
        <div class="mb-4 flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
            <span>{{ __('messages.newsletters_used', ['used' => $newsletterUsed, 'limit' => $newsletterLimit]) }}</span>
            @if (config('cashier.key') && $role->actualPlanTier() !== 'enterprise' && $newsletterLimit < 1000)
            <a href="{{ route('role.subscribe', ['subdomain' => $role->subdomain]) }}" class="text-[var(--brand-blue)] hover:underline text-xs font-medium">
                {{ __('messages.newsletter_upgrade_plan') }}
            </a>
            @endif
        </div>
        @endif

        {{-- Template Picker --}}
        @if (!request('template_id') && ($savedTemplates ?? collect())->count())
        <div class="ap-card sm:rounded-xl p-6 mb-2">
            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ __('messages.start_from_template') }}</h3>

            @if (($savedTemplates ?? collect())->count())
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-wider">{{ __('messages.your_templates') }}</p>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                @foreach ($savedTemplates as $tmpl)
                <a href="{{ route('newsletter.create', ['role_id' => \App\Utils\UrlUtils::encodeId($role->id), 'template_id' => \App\Utils\UrlUtils::encodeId($tmpl->id)]) }}"
                    class="border border-gray-200 dark:border-gray-600 rounded-lg p-4 text-center hover:border-[var(--brand-blue)] hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors">
                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $tmpl->name }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 capitalize">{{ $tmpl->template }}</div>
                </a>
                @endforeach
            </div>
            @endif
        </div>
        @endif

        <form method="POST" action="{{ route('newsletter.store', ['role_id' => \App\Utils\UrlUtils::encodeId($role->id)]) }}">
            @csrf
            @php
                $newsletter = new \App\Models\Newsletter(['template' => $defaultTemplate, 'style_settings' => $defaultStyleSettings, 'segment_ids' => $defaultSegmentIds]);
            @endphp
            @include('newsletter.partials._builder')
        </form>
    </div>
</x-app-admin-layout>
