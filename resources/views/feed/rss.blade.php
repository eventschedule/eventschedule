<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>

<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title>{{ htmlspecialchars($role->name, ENT_XML1, 'UTF-8') }}</title>
        <link>{{ $role->getGuestUrl() }}</link>
        <description>{{ htmlspecialchars($role->name . ' - Event Schedule', ENT_XML1, 'UTF-8') }}</description>
        <language>{{ app()->getLocale() }}</language>
        @if($items->first())
        <lastBuildDate>{{ $items->first()['event']->getStartDateTime($items->first()['date'])->toRssString() }}</lastBuildDate>
        @endif
        <atom:link href="{{ route('feed.rss', ['subdomain' => $role->subdomain]) }}" rel="self" type="application/rss+xml" />
        @foreach($items as $item)
        @php
            $event = $item['event'];
            $date = $item['date'];
            $guestUrl = $event->getGuestUrl($role->subdomain, $date);
            $description = $event->short_description ?: (strip_tags($event->description_html ?: '') ? \Illuminate\Support\Str::limit(strip_tags($event->description_html), 300) : '');
        @endphp
        <item>
            <title>{{ htmlspecialchars($event->getTitle(), ENT_XML1, 'UTF-8') }}</title>
            <link>{{ $guestUrl }}</link>
            <guid isPermaLink="true">{{ $guestUrl }}{{ $date ? '?d=' . $date : '' }}</guid>
            @if($description)
            <description>{{ htmlspecialchars($description, ENT_XML1, 'UTF-8') }}</description>
            @endif
            @if($event->starts_at)
            <pubDate>{{ $event->getStartDateTime($date)->toRssString() }}</pubDate>
            @endif
        </item>
        @endforeach
    </channel>
</rss>
