<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('messages.templates') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="flex justify-between items-center mb-6">
                <div></div>
                <div class="flex gap-3">
                    <x-secondary-link href="{{ route('admin.newsletters.index') }}">
                        {{ __('messages.back') }}
                    </x-secondary-link>
                    <x-brand-link href="{{ route('admin.newsletters.template.create') }}">
                        <svg class="-ms-0.5 me-2 h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
                        {{ __('messages.create_template') }}
                    </x-brand-link>
                </div>
            </div>

            @if (session('status'))
            <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-lg text-green-700 dark:text-green-300">
                {{ session('status') }}
            </div>
            @endif

            @if ($userTemplates->count())
            <div class="space-y-4">
                @foreach ($userTemplates as $template)
                <div class="ap-card sm:rounded-xl p-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <h4 class="text-base font-medium text-gray-900 dark:text-gray-100">{{ $template->name }}</h4>
                            <div class="flex items-center gap-3 mt-1">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 capitalize">{{ $template->template }}</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $template->created_at->format('M j, Y') }}</span>
                            </div>
                        </div>
                        <div class="shrink-0 space-x-3">
                            <a href="{{ route('admin.newsletters.create', ['template_id' => \App\Utils\UrlUtils::encodeId($template->id)]) }}"
                                class="text-[var(--brand-blue)] hover:text-[var(--brand-blue-dark)] text-sm">{{ __('messages.use') }}</a>
                            <a href="{{ route('admin.newsletters.template.edit', ['hash' => \App\Utils\UrlUtils::encodeId($template->id)]) }}"
                                class="text-[var(--brand-blue)] hover:text-[var(--brand-blue-dark)] text-sm">{{ __('messages.edit') }}</a>
                            <form method="POST" action="{{ route('admin.newsletters.template.delete', ['hash' => \App\Utils\UrlUtils::encodeId($template->id)]) }}"
                                class="inline js-confirm-form" data-confirm="{{ __('messages.are_you_sure') }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700 text-sm">{{ __('messages.delete') }}</button>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <rect x="3" y="3" width="7" height="7" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <rect x="14" y="3" width="7" height="7" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <rect x="3" y="14" width="7" height="7" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <rect x="14" y="14" width="7" height="7" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('messages.no_templates') }}</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.no_templates_description') }}</p>
            </div>
            @endif
        </div>
    </div>

    <script {!! nonce_attr() !!}>
        document.addEventListener('submit', function(e) {
            var form = e.target.closest('.js-confirm-form');
            if (form) {
                if (!confirm(form.getAttribute('data-confirm'))) {
                    e.preventDefault();
                }
            }
        });
    </script>
</x-app-layout>
