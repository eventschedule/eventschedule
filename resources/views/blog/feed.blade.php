<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>

<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:dc="http://purl.org/dc/elements/1.1/">
    <channel>
        <title>Event Schedule Blog</title>
        <link>{{ route('blog.index') }}</link>
        <description>News, tips, and insights about event scheduling and ticketing from the Event Schedule team.</description>
        <language>{{ app()->getLocale() }}</language>
        @if($posts->first()?->published_at)
        <lastBuildDate>{{ $posts->first()->published_at->toRssString() }}</lastBuildDate>
        @endif
        <atom:link href="{{ route('blog.feed') }}" rel="self" type="application/rss+xml" />
        <image>
            <url>{{ config('app.url') }}/images/dark_logo.png</url>
            <title>Event Schedule Blog</title>
            <link>{{ route('blog.index') }}</link>
        </image>
        @foreach($posts as $post)
        <item>
            {{-- Blade's own escaping is XML-safe; wrapping it in htmlspecialchars() escaped every title twice. --}}
            <title>{{ $post->title }}</title>
            <link>{{ route('blog.show', $post->slug) }}</link>
            <guid isPermaLink="true">{{ route('blog.show', $post->slug) }}</guid>
            <description>{{ $post->excerpt }}</description>
            @if($post->published_at)
            <pubDate>{{ $post->published_at->toRssString() }}</pubDate>
            @endif
            @if($post->author_name)
            <dc:creator>{{ $post->author_name }}</dc:creator>
            @endif
            @if($post->tags)
            @foreach($post->tags as $tag)
            <category>{{ $tag }}</category>
            @endforeach
            @endif
            <content:encoded><![CDATA[{!! str_replace(']]>', ']]]]><![CDATA[>', $post->renderedContent()) !!}]]></content:encoded>
        </item>
        @endforeach
    </channel>
</rss>
