{{--
    Admin legal-page manager (issue #116).

    Three stacked cards, one per document, each posting to its own endpoint.

    NO VUE MOUNT ON THIS PAGE, deliberately. app.js only auto-initialises the
    `.html-editor` textareas that sit OUTSIDE #app, because Vue's runtime compiler
    captures the container's innerHTML and would destroy the EasyMDE wrapper - and
    a <textarea> inside a Vue mount also gets its contents compiled as a template,
    so a policy containing {{ ... }} would execute rather than render. That is also
    why both fields are always visible instead of being toggled by a radio group:
    precedence is stated in the help text, and each card says which of the three
    (built-in page, external URL, own document) is in force right now.
--}}
<x-app-admin-layout>
    <x-slot name="head">
        <style {!! nonce_attr() !!}>
            .sys-help {
              margin: 0.375rem 0 0;
              font-size: 0.75rem;
              color: rgb(var(--ap-ink-3));
            }
            .page-card .page-form-actions {
              margin-top: 1.25rem;
            }
            .sys-off {
              opacity: 0.5;
              pointer-events: none;
            }
        </style>
    </x-slot>

    {{-- Navigation --}}
    @include('admin.partials._navigation', ['active' => 'legal'])

    <div class="page-head">
        <p class="page-lead">{{ __('messages.legal_pages_intro') }}</p>
    </div>

    <div class="page-shell page-stack">
        <x-page-flash :keys="['success' => 'success']" />

        <x-page-notice tone="warn">{{ __('messages.legal_pages_warning') }}</x-page-notice>

        @foreach (\App\Models\LegalDocument::TYPES as $type)
            @php
                $document = $documents[$type] ?? null;
                // content_html, matching LegalDocument::index(): a draft that HTML
                // Purifier strips to nothing is not a published document.
                $hasContent = $document && filled($document->content_html);
                $hasUrl = $document && filled($document->url);
                // Which of the three is in force: the URL wins over the document, and the
                // built-in page is what is left. It used to be told only by the line of help
                // under the editor.
                // The three forms share their field names, so a refused save has to say which
                // card it came from: the value that was typed and the reason it was refused
                // used to come back on all three cards, and Save on a neighbour would then
                // have written one document's text into another.
                $refused = old('_card', $type) === $type;
                [$inForceTone, $inForce] = match (true) {
                    $hasUrl => ['is-info', __('messages.legal_document_url')],
                    $hasContent => ['is-on', __('messages.legal_status_own')],
                    default => ['', __('messages.legal_status_builtin')],
                };
            @endphp
            <x-page-card beside :id="$type" class="scroll-mt-24" :title="__('messages.legal_'.$type.'_title')"
                :lead="$hasContent || $hasUrl ? __('messages.legal_last_updated', ['date' => $document->updated_at->isoFormat('LL')]) : __('messages.legal_using_builtin')">
                <x-slot name="aside">
                    <span class="event-status {{ $inForceTone }}">{{ $inForce }}</span>
                    @if ($hasContent || $hasUrl)
                        <x-link :href="policy_url($type)" target="_blank">{{ __('messages.legal_view_page') }}</x-link>
                    @endif
                </x-slot>

                <form method="POST" action="{{ route('admin.legal.update', ['type' => $type]) }}" class="{{ is_demo_mode() ? 'sys-off' : '' }}">
                    @csrf
                    <input type="hidden" name="_card" value="{{ $type }}">

                    <div class="page-form-fields">
                        <div>
                            <x-input-label :for="$type.'_url'" :value="__('messages.legal_document_url')" />
                            <x-text-input :id="$type.'_url'" name="url" type="url" dir="ltr"
                                class="mt-1 block w-full text-sm"
                                placeholder="https://example.com{{ \App\Models\LegalDocument::PATHS[$type] }}"
                                :value="$refused ? old('url', $document->url ?? '') : ($document->url ?? '')"
                                :disabled="is_demo_mode()" />
                            <p class="sys-help">{{ __('messages.legal_document_url_help') }}</p>
                            <x-input-error class="mt-2" :messages="$refused ? $errors->get('url') : []" />
                        </div>

                        <div>
                            <x-input-label :for="$type.'_content'" :value="__('messages.legal_document_content')" />
                            <textarea id="{{ $type }}_content" name="content" rows="18" {{ is_demo_mode() ? 'disabled' : '' }}
                                class="html-editor mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">{{ $refused ? old('content', $document->content ?? '') : ($document->content ?? '') }}</textarea>
                            <p class="sys-help">{{ __('messages.legal_document_content_help') }}</p>
                            <x-input-error class="mt-2" :messages="$refused ? $errors->get('content') : []" />
                        </div>

                        {{-- A document nobody is shown: said as a notice, where it was a line of
                             amber text at the end of the help. --}}
                        @if ($hasUrl && $hasContent)
                        <x-page-notice tone="warn">{{ __('messages.legal_document_url_in_use') }}</x-page-notice>
                        @endif

                        @if (is_demo_mode())
                        <x-page-notice tone="warn">{{ __('messages.demo_mode_settings_disabled') }}</x-page-notice>
                        @endif
                    </div>

                    <div class="page-form-actions">
                        <x-brand-button type="submit">{{ __('messages.save') }}</x-brand-button>
                    </div>
                </form>
            </x-page-card>
        @endforeach
    </div>
</x-app-admin-layout>
