<x-app-admin-layout>
    <x-slot name="head">
        @include('newsletter.partials._styles')
        @include('newsletter.partials._builder-styles')
    </x-slot>

    @include('admin.partials._navigation', ['active' => 'newsletters'])

    {{-- A new platform newsletter, in the builder a schedule's owner uses. --}}
    <div class="page-shell">
        @include('admin.newsletters.partials._subpage-head', [
            'title' => __('messages.create_admin_newsletter'),
            'back' => route('admin.newsletters.index'),
            'backLabel' => __('messages.admin_newsletters'),
        ])

        @include('newsletter.partials._notices')

        {{-- Template picker --}}
        @if (! request('template_id') && ($savedTemplates ?? collect())->count())
        <x-page-card class="news-notice" :title="__('messages.start_from_template')">
            <div class="news-picks">
                @foreach ($savedTemplates as $tmpl)
                <a href="{{ route('admin.newsletters.create', ['template_id' => \App\Utils\UrlUtils::encodeId($tmpl->id)]) }}" class="news-pick">
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

        <form method="POST" action="{{ route('admin.newsletters.store') }}">
            @csrf
            @php
                $newsletter = new \App\Models\Newsletter(['template' => $defaultTemplate, 'style_settings' => $defaultStyleSettings, 'segment_ids' => $defaultSegmentIds]);
                $isAdmin = true;
                $events = collect();
            @endphp
            @include('newsletter.partials._builder')
        </form>
    </div>
</x-app-admin-layout>
