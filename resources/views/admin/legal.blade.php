{{--
    Admin legal-page manager (issue #116).

    Three stacked cards, one per document, each posting to its own endpoint.

    NO VUE MOUNT ON THIS PAGE, deliberately. app.js only auto-initialises the
    `.html-editor` textareas that sit OUTSIDE #app, because Vue's runtime compiler
    captures the container's innerHTML and would destroy the EasyMDE wrapper - and
    a <textarea> inside a Vue mount also gets its contents compiled as a template,
    so a policy containing {{ ... }} would execute rather than render. That is also
    why both fields are always visible instead of being toggled by a radio group:
    precedence is stated in the help text.
--}}
<x-app-admin-layout>
    <div class="space-y-4">

        {{-- Navigation --}}
        @include('admin.partials._navigation', ['active' => 'legal'])

        @if (session('success'))
        <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-lg p-4">
            <p class="text-sm text-green-800 dark:text-green-200">{{ session('success') }}</p>
        </div>
        @endif

        <div class="ap-card rounded-xl p-6">
            <div class="mb-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">@lang('messages.legal_pages')</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">@lang('messages.legal_pages_intro')</p>
            </div>

            <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3">
                <p class="text-sm text-amber-800 dark:text-amber-200 flex items-start gap-2">
                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>@lang('messages.legal_pages_warning')</span>
                </p>
            </div>
        </div>

        @foreach (\App\Models\LegalDocument::TYPES as $type)
            @php
                $document = $documents[$type] ?? null;
                // content_html, matching LegalDocument::index(): a draft that HTML
                // Purifier strips to nothing is not a published document.
                $hasContent = $document && filled($document->content_html);
                $hasUrl = $document && filled($document->url);
            @endphp
            <div id="{{ $type }}" class="ap-card rounded-xl p-6 scroll-mt-24">
                <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">@lang('messages.legal_'.$type.'_title')</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            @if ($hasContent || $hasUrl)
                                {{ __('messages.legal_last_updated', ['date' => $document->updated_at->isoFormat('LL')]) }}
                            @else
                                @lang('messages.legal_using_builtin')
                            @endif
                        </p>
                    </div>
                    @if ($hasContent || $hasUrl)
                        <x-link :href="policy_url($type)" target="_blank">@lang('messages.legal_view_page')</x-link>
                    @endif
                </div>

                <form method="POST" action="{{ route('admin.legal.update', ['type' => $type]) }}" class="{{ is_demo_mode() ? 'opacity-50 pointer-events-none' : '' }}">
                    @csrf

                    <div class="mb-6">
                        <x-input-label :for="$type.'_url'" :value="__('messages.legal_document_url')" />
                        <x-text-input :id="$type.'_url'" name="url" type="url"
                            class="mt-1 block w-full text-sm"
                            placeholder="https://example.com{{ \App\Models\LegalDocument::PATHS[$type] }}"
                            :value="old('url', $document->url ?? '')"
                            :disabled="is_demo_mode()" />
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">@lang('messages.legal_document_url_help')</p>
                        <x-input-error class="mt-2" :messages="$errors->get('url')" />
                    </div>

                    <div class="mb-6">
                        <x-input-label :for="$type.'_content'" :value="__('messages.legal_document_content')" />
                        <textarea id="{{ $type }}_content" name="content" rows="18" {{ is_demo_mode() ? 'disabled' : '' }}
                            class="html-editor mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">{{ old('content', $document->content ?? '') }}</textarea>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            @lang('messages.legal_document_content_help')
                            @if ($hasUrl && $hasContent)
                                <span class="text-amber-700 dark:text-amber-300">@lang('messages.legal_document_url_in_use')</span>
                            @endif
                        </p>
                        <x-input-error class="mt-2" :messages="$errors->get('content')" />
                    </div>

                    @if (is_demo_mode())
                    <div class="mb-6 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg">
                        <p class="text-sm text-amber-800 dark:text-amber-200 flex items-start gap-2">
                            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span>@lang('messages.demo_mode_settings_disabled')</span>
                        </p>
                    </div>
                    @endif

                    <div class="flex justify-end">
                        <x-brand-button type="submit">@lang('messages.save')</x-brand-button>
                    </div>
                </form>
            </div>
        @endforeach
    </div>
</x-app-admin-layout>
