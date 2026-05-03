<x-marketing-layout>
    <x-slot name="title">{{ __('accessibility.page_title') }}</x-slot>
    <x-slot name="description">{{ __('accessibility.meta_description') }}</x-slot>
    <x-slot name="breadcrumbTitle">{{ __('accessibility.breadcrumb') }}</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "WebPage",
        "name": @json(__('accessibility.page_title')),
        "description": @json(__('accessibility.meta_description')),
        "url": "{{ url()->current() }}",
        "isPartOf": {
            "@type": "WebSite",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        }
    }
    </script>
    </x-slot>

    <section class="py-16 bg-gray-50 dark:bg-gray-800/50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">{{ __('accessibility.h1') }}</h1>
            <p class="text-gray-600 dark:text-gray-400">{{ __('accessibility.company_lead') }}</p>
        </div>
    </section>

    <section class="py-16 bg-white dark:bg-[#0a0a0f]">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="rounded-xl p-4 mb-8 border border-amber-200 dark:border-amber-700 bg-amber-50 dark:bg-amber-900/20">
                <div class="flex gap-3">
                    <svg class="w-5 h-5 shrink-0 text-amber-600 dark:text-amber-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    <p class="text-sm text-gray-800 dark:text-gray-200">{{ __('accessibility.counsel_notice') }}</p>
                </div>
            </div>

            <div class="prose prose-lg dark:prose-invert max-w-none space-y-10">

                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-0 mb-3">{{ __('accessibility.section_scope_title') }}</h2>
                    <p class="text-gray-600 dark:text-gray-300">{{ __('accessibility.section_scope_body') }}</p>
                </div>

                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">{{ __('accessibility.section_commitment_title') }}</h2>
                    <p class="text-gray-600 dark:text-gray-300">{{ __('accessibility.section_commitment_body', ['wcag_target' => config('accessibility.wcag_target_label')]) }}</p>
                    @if (config('accessibility.reference_israeli_standard_5568'))
                    <p class="text-gray-600 dark:text-gray-300 mt-3">{{ __('accessibility.section_commitment_is5568_note') }}</p>
                    @endif
                </div>

                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">{{ __('accessibility.section_status_title') }}</h2>
                    <p class="text-gray-600 dark:text-gray-300">{{ __('accessibility.section_status_body') }}</p>
                </div>

                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">{{ __('accessibility.section_measures_title') }}</h2>
                    <p class="text-gray-600 dark:text-gray-300">{{ __('accessibility.section_measures_body') }}</p>
                </div>

                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">{{ __('accessibility.section_third_party_title') }}</h2>
                    <p class="text-gray-600 dark:text-gray-300">{{ __('accessibility.section_third_party_body') }}</p>
                </div>

                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">{{ __('accessibility.section_feedback_title') }}</h2>
                    <p class="text-gray-600 dark:text-gray-300">{{ __('accessibility.section_feedback_body', ['email' => config('accessibility.contact_email'), 'sla' => config('accessibility.response_sla_business_days')]) }}</p>
                    <p class="text-gray-600 dark:text-gray-300 mt-3">
                        <x-link href="mailto:{{ config('accessibility.contact_email') }}?subject={{ rawurlencode(__('accessibility.breadcrumb')) }}">{{ config('accessibility.contact_email') }}</x-link>
                    </p>
                </div>

                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">{{ __('accessibility.section_updates_title') }}</h2>
                    <p class="text-gray-600 dark:text-gray-300">{{ __('accessibility.section_updates_body', ['date' => config('accessibility.declaration_last_reviewed')]) }}</p>
                </div>

                <div class="border border-gray-200 dark:border-white/10 rounded-xl p-6 bg-gray-50 dark:bg-[#1A1A1A]">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mt-0 mb-4">{{ __('accessibility.known_gaps_title') }}</h2>
                    <ul class="list-disc ps-5 space-y-2 text-gray-600 dark:text-gray-300">
                        <li>{{ __('accessibility.gap_calendar') }}</li>
                        <li>{{ __('accessibility.gap_legacy_ui') }}</li>
                        <li>{{ __('accessibility.gap_embeds') }}</li>
                        <li>{{ __('accessibility.gap_video') }}</li>
                    </ul>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-4">{{ __('accessibility.audit_backlog_note') }}</p>
                </div>

            </div>
        </div>
    </section>
</x-marketing-layout>
