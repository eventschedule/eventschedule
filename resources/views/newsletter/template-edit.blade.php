<x-app-admin-layout>
    <div class="space-y-4">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                {{ $newsletterTemplate ? __('messages.edit_template') : __('messages.create_template') }}
            </h2>
            <a href="{{ route('newsletter.templates', ['role_id' => \App\Utils\UrlUtils::encodeId($role->id)]) }}"
                class="inline-flex items-center justify-center rounded-lg bg-gray-200 dark:bg-gray-700 px-5 py-3 text-base font-semibold text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                {{ __('messages.back') }}
            </a>
        </div>

        @if (session('status'))
        <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-lg text-green-700 dark:text-green-300">
            {{ session('status') }}
        </div>
        @endif

        @if (session('error'))
        <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg text-red-700 dark:text-red-300">
            {{ session('error') }}
        </div>
        @endif

        <form method="POST" action="{{ $newsletterTemplate
            ? route('newsletter.template.update', ['role_id' => \App\Utils\UrlUtils::encodeId($role->id), 'hash' => \App\Utils\UrlUtils::encodeId($newsletterTemplate->id)])
            : route('newsletter.template.store', ['role_id' => \App\Utils\UrlUtils::encodeId($role->id)]) }}">
            @csrf
            @if ($newsletterTemplate)
                @method('PUT')
            @endif

            <div class="mb-4">
                <x-input-label for="template_name" :value="__('messages.template_name')" />
                <x-text-input id="template_name" name="name" type="text" class="mt-1 block w-full"
                    :value="$newsletterTemplate->name ?? ''" required
                    :placeholder="__('messages.template_name_placeholder')" />
            </div>

            @php
                $isTemplateMode = true;
                $newsletter = new \App\Models\Newsletter([
                    'template' => $newsletterTemplate->template ?? 'modern',
                    'style_settings' => $newsletterTemplate->style_settings ?? \App\Models\Newsletter::defaultStyleSettingsForRole($role),
                    'subject' => '',
                ]);
                $defaultBlocks = $newsletterTemplate->blocks ?? ($defaultBlocks ?? \App\Models\Newsletter::defaultBlocks($role));
            @endphp
            @include('newsletter.partials._builder')
        </form>
    </div>
</x-app-admin-layout>
