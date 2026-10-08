<x-app-admin-layout>
    <x-slot name="head">
        @include('newsletter.partials._styles')
        @include('newsletter.partials._builder-styles')
    </x-slot>

    @include('admin.partials._navigation', ['active' => 'newsletters'])

    {{-- One of the platform's templates in the builder: its name, then the editor. --}}
    <div class="page-shell">
        @include('admin.newsletters.partials._subpage-head', [
            'title' => $newsletterTemplate ? __('messages.edit_template') : __('messages.create_template'),
            'back' => route('admin.newsletters.templates'),
            'backLabel' => __('messages.templates'),
        ])

        @include('newsletter.partials._notices')

        <form method="POST" action="{{ $newsletterTemplate
            ? route('admin.newsletters.template.update', ['hash' => \App\Utils\UrlUtils::encodeId($newsletterTemplate->id)])
            : route('admin.newsletters.template.store') }}">
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
                $isAdmin = true;
                $isTemplateMode = true;
                $events = collect();
                $newsletter = new \App\Models\Newsletter([
                    'template' => $newsletterTemplate->template ?? 'modern',
                    'style_settings' => $newsletterTemplate->style_settings ?? \App\Models\Newsletter::templateDefaults('modern'),
                    'subject' => '',
                ]);
                $defaultBlocks = $newsletterTemplate->blocks ?? ($defaultBlocks ?? []);
            @endphp
            @include('newsletter.partials._builder')
        </form>
    </div>
</x-app-admin-layout>
