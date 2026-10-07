<x-app-admin-layout>
    <x-slot name="head">
        @include('newsletter.partials._styles')
        @include('newsletter.partials._builder-styles')
    </x-slot>

    {{-- A new newsletter: the builder, under a title row that leads back to the list. The
         month's allowance sits at the end of that row, where it can be read without standing
         between the title and the work. --}}
    <div class="page-shell">
        @php $roleParam = \App\Utils\UrlUtils::encodeId($role->id); @endphp

        <x-page-header
            :title="__('messages.create_newsletter')"
            :back="route('newsletter.index', ['role_id' => $roleParam])"
            :back-label="__('messages.newsletters')">
            <x-slot name="actions">@include('newsletter.partials._usage-meter')</x-slot>
        </x-page-header>

        @include('newsletter.partials._notices')
        @include('newsletter.partials._verification-warning')

        {{-- Template picker --}}
        @if (! request('template_id') && ($savedTemplates ?? collect())->count())
        <x-page-card class="news-notice" :title="__('messages.start_from_template')" :lead="__('messages.your_templates')">
            <div class="news-picks">
                @foreach ($savedTemplates as $tmpl)
                <a href="{{ route('newsletter.create', ['role_id' => $roleParam, 'template_id' => \App\Utils\UrlUtils::encodeId($tmpl->id)]) }}" class="news-pick">
                    @include('newsletter.partials._template-swatch', ['swatchOf' => $tmpl])
                    <span>
                        <span class="news-pick-name"><bdi>{{ $tmpl->name }}</bdi></span>
                        <span class="news-pick-meta block">{{ $tmpl->template }}</span>
                    </span>
                </a>
                @endforeach
            </div>
        </x-page-card>
        @endif

        <form method="POST" action="{{ route('newsletter.store', ['role_id' => $roleParam]) }}">
            @csrf
            @php
                $newsletter = new \App\Models\Newsletter(['template' => $defaultTemplate, 'style_settings' => $defaultStyleSettings, 'segment_ids' => $defaultSegmentIds]);
            @endphp
            @include('newsletter.partials._builder')
        </form>
    </div>
</x-app-admin-layout>
