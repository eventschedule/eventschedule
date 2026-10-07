<x-app-admin-layout>
    <x-slot name="head">
        @include('newsletter.partials._styles')
    </x-slot>

    @include('admin.partials._navigation', ['active' => 'newsletters'])

    {{-- The platform's saved designs. This page used to render through the bare shell: it had no
         sidebar and no admin navigation, and the only way out of it was a "Back" button. It goes
         through app-admin like its siblings now, which is also what opts it into the six
         palettes (ThemeVariantGatingTest). --}}
    <div class="page-shell">
        @include('admin.newsletters.partials._section', ['tab' => 'templates'])

        <div class="page-head">
            <p class="page-lead">{{ __('messages.newsletter_templates_lead') }}</p>
            {{-- With nothing saved yet the empty state below carries this button. --}}
            @if ($userTemplates->count())
            <div class="page-actions">
                <x-secondary-link href="{{ route('admin.newsletters.template.create') }}">
                    {{ __('messages.create_template') }}
                </x-secondary-link>
            </div>
            @endif
        </div>

        @if ($userTemplates->count())
        @include('newsletter.partials._template-cards', [
            'templates' => $userTemplates,
            'useUrl' => fn ($template) => route('admin.newsletters.create', ['template_id' => \App\Utils\UrlUtils::encodeId($template->id)]),
            'editUrl' => fn ($template) => route('admin.newsletters.template.edit', ['hash' => \App\Utils\UrlUtils::encodeId($template->id)]),
            'deleteUrl' => fn ($template) => route('admin.newsletters.template.delete', ['hash' => \App\Utils\UrlUtils::encodeId($template->id)]),
        ])
        @else
        <div class="ap-card rounded-xl">
            <x-page-empty
                :title="__('messages.no_templates')"
                :text="__('messages.no_templates_description')"
                icon="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z">
                <x-secondary-link href="{{ route('admin.newsletters.template.create') }}">{{ __('messages.create_template') }}</x-secondary-link>
            </x-page-empty>
        </div>
        @endif
    </div>

    @include('newsletter.partials._list-script')
</x-app-admin-layout>
