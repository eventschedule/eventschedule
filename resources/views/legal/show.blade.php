{{--
    An operator-authored legal document (issue #116). The body is markdown the
    super-admin wrote at /admin/legal, already run through
    MarkdownUtils::convertToHtml() - CommonMark plus HTML Purifier - when it was
    saved, which is also what puts ids on the headings so an in-document table of
    contents works. This layout mounts no Vue, so the rendered HTML is not
    compiled as a template.
--}}
<x-legal-layout :title="$title">
    <article>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $title }}</h1>

        @if ($lastUpdated)
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                {{ __('messages.legal_last_updated', ['date' => $lastUpdated->isoFormat('LL')]) }}
            </p>
        @endif

        {{-- The document keeps its OWN direction. Without this it inherits <html dir>,
             which is the viewer's UI locale, so a Hebrew policy read by an anonymous
             visitor would lay out LTR - and the table, blockquote and list rules in
             this layout are all logical properties that resolve from it. --}}
        <div dir="{{ detect_content_dir($html) ?: (is_rtl() ? 'rtl' : 'ltr') }}"
            class="custom-content legal-prose mt-8 text-gray-700 dark:text-gray-300 leading-relaxed">
            {!! $html !!}
        </div>
    </article>

    {{-- The sibling documents. This is how a cookie policy is discoverable at all:
         the WP footer's links are manually curated and the consent banner only
         appears when this install has something to consent to. Only the operator's own
         documents are listed - though one of them may well be an external URL. --}}
    @php
        $siblings = collect(\App\Models\LegalDocument::TYPES)
            ->reject(fn ($sibling) => $sibling === $type)
            ->filter(function ($sibling) {
                $document = \App\Models\LegalDocument::index()[$sibling] ?? null;

                return $document && ($document['url'] || $document['has_content']);
            });
    @endphp

    @if ($siblings->isNotEmpty())
        <nav aria-label="{{ __('messages.legal_pages') }}" class="mt-12 pt-6 border-t border-gray-200 dark:border-gray-800 flex flex-wrap gap-x-6 gap-y-2 text-sm">
            @foreach ($siblings as $sibling)
                <x-link :href="policy_url($sibling)">@lang('messages.legal_'.$sibling.'_title')</x-link>
            @endforeach
        </nav>
    @endif
</x-legal-layout>
