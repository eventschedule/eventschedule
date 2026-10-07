<x-app-admin-layout>
    <x-slot name="head">
        @include('newsletter.partials._styles')
        @include('newsletter.partials._builder-styles')
    </x-slot>

    {{-- A template in the builder: its name, then the same editor a newsletter has, without the
         subject and the recipients a template does not carry. --}}
    <div class="page-shell">
        @php $roleParam = \App\Utils\UrlUtils::encodeId($role->id); @endphp

        <x-page-header
            :title="$newsletterTemplate ? __('messages.edit_template') : __('messages.create_template')"
            :back="route('newsletter.templates', ['role_id' => $roleParam])"
            :back-label="__('messages.templates')" />

        @include('newsletter.partials._notices')

        <form method="POST" action="{{ $newsletterTemplate
            ? route('newsletter.template.update', ['role_id' => $roleParam, 'hash' => \App\Utils\UrlUtils::encodeId($newsletterTemplate->id)])
            : route('newsletter.template.store', ['role_id' => $roleParam]) }}">
            @csrf
            @if ($newsletterTemplate)
                @method('PUT')
            @endif

            <div class="news-template-name-field">
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
