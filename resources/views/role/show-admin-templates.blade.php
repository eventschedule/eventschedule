<div class="sm:flex sm:items-center">
    <div class="sm:flex-auto">
        <h1 class="text-base font-semibold leading-6 text-gray-900 dark:text-gray-100">{{ __('messages.event_templates') }}</h1>
        <p class="mt-2 text-sm text-gray-700 dark:text-gray-400">{{ __('messages.templates_description') }}</p>
    </div>
</div>

@if (! $role->isPro())
    {{-- Free-tier teaser: discoverable, with an upgrade path --}}
    <div class="mt-8 ap-card rounded-xl p-8 text-center">
        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-blue-50 dark:bg-blue-500/10 mb-4">
            <svg class="h-6 w-6 text-[var(--brand-blue)]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
            </svg>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">{{ __('messages.upgrade_to_pro_plan') }}</h3>
        <p class="mx-auto max-w-md text-sm text-gray-600 dark:text-gray-400 mb-6">{{ __('messages.templates_pro_description') }}</p>
        <x-brand-link href="{{ route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'plan']) }}">
            {{ __('messages.upgrade_to_pro_plan') }}
        </x-brand-link>
    </div>
@elseif ($eventTemplates->isEmpty())
    {{-- Empty state --}}
    <div class="mt-8 ap-card rounded-xl p-8 text-center">
        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-2">{{ __('messages.no_templates_yet') }}</h3>
        <p class="mx-auto max-w-md text-sm text-gray-600 dark:text-gray-400">{{ __('messages.no_templates_yet_description') }}</p>
    </div>
@else
    <div class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($eventTemplates as $template)
            <div class="ap-card rounded-xl p-5 flex flex-col">
                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 break-words">{{ $template->name }}</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $template->created_at->format('M j, Y') }}</p>

                <div class="mt-auto pt-5 flex items-center gap-2">
                    <x-brand-link href="{{ route('event_template.apply', ['subdomain' => $role->subdomain, 'hash' => $template->encodeId()]) }}" class="flex-1 justify-center">
                        <svg class="-ms-0.5 me-1.5 h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                        </svg>
                        {{ __('messages.add_event') }}
                    </x-brand-link>

                    <button type="button"
                        data-rename-url="{{ route('event_template.update', ['subdomain' => $role->subdomain, 'hash' => $template->encodeId()]) }}"
                        data-rename-name="{{ $template->name }}"
                        title="{{ __('messages.rename_template') }}"
                        class="js-rename-template-open inline-flex items-center justify-center p-2.5 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-[#2d2d30] hover:text-gray-700 dark:hover:text-gray-200 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)]">
                        <span class="sr-only">{{ __('messages.rename_template') }}</span>
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                        </svg>
                    </button>

                    <form method="POST" action="{{ route('event_template.destroy', ['subdomain' => $role->subdomain, 'hash' => $template->encodeId()]) }}"
                        data-confirm="{{ __('messages.are_you_sure') }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" title="{{ __('messages.delete_template') }}"
                            class="inline-flex items-center justify-center p-2.5 rounded-lg text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-700 dark:hover:text-red-300 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-red-500">
                            <span class="sr-only">{{ __('messages.delete_template') }}</span>
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Rename modal (plain JS; CSTI-safe: name flows via data attribute -> input value, never compiled) --}}
    <div id="rename-template-modal" class="fixed inset-0 z-50 hidden" aria-labelledby="rename-template-title" role="dialog" aria-modal="true">
        <div class="js-rename-template-close fixed inset-0 bg-gray-500/75 dark:bg-gray-900/75 transition-opacity"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-xl bg-white dark:bg-gray-800 px-4 pb-4 pt-5 text-start shadow-xl dark:shadow-gray-900/50 transition-all sm:my-8 sm:w-full sm:max-w-md sm:p-6">
                    <form id="rename-template-form" method="POST" action="">
                        @csrf
                        @method('PUT')
                        <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-gray-100 mb-4" id="rename-template-title">{{ __('messages.rename_template') }}</h3>
                        <label for="rename-template-input" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('messages.template_name') }}</label>
                        <input type="text" id="rename-template-input" name="name" required maxlength="255"
                            class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] sm:text-sm" />
                        <div class="mt-6 flex flex-row gap-3">
                            <button type="button"
                                class="js-rename-template-close flex-1 inline-flex items-center justify-center px-4 py-3 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-base text-gray-700 dark:text-gray-300 shadow-sm transition-all duration-200 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                                {{ __('messages.cancel') }}
                            </button>
                            <button type="submit"
                                class="flex-1 inline-flex items-center justify-center px-4 py-3 bg-[var(--brand-button-bg)] border border-transparent rounded-lg font-semibold text-base text-white shadow-sm transition-all duration-200 hover:bg-[var(--brand-button-bg-hover)] focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                                {{ __('messages.save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script {!! nonce_attr() !!}>
        function openRenameTemplateModal(url, name) {
            var modal = document.getElementById('rename-template-modal');
            var form = document.getElementById('rename-template-form');
            var input = document.getElementById('rename-template-input');
            form.setAttribute('action', url);
            input.value = name;
            modal.classList.remove('hidden');
            input.focus();
            input.select();
        }
        function closeRenameTemplateModal() {
            document.getElementById('rename-template-modal').classList.add('hidden');
        }
        // Capture-phase delegation (replaces inline onclick; CSP blocks inline handlers).
        document.addEventListener('click', function (e) {
            if (! e.target.closest) return;
            var openBtn = e.target.closest('.js-rename-template-open');
            if (openBtn) {
                openRenameTemplateModal(openBtn.getAttribute('data-rename-url'), openBtn.getAttribute('data-rename-name'));
                return;
            }
            if (e.target.closest('.js-rename-template-close')) closeRenameTemplateModal();
        }, true);
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeRenameTemplateModal();
        });
    </script>
@endif
