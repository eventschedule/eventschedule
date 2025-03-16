<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>{{ url('/') }}</loc>
        <lastmod>{{ $lastmod }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>1.0</priority>
    </url>
    @foreach($roles as $role)
        <url>
            <loc>{{ url($role->getGuestUrl()) }}</loc>
            <lastmod>{{ $role->updated_at->toIso8601String() }}</lastmod>
            <changefreq>daily</changefreq>
            <priority>0.8</priority>
        </url>
    @endforeach
    @foreach($events as $event)
        <url>
            <loc>{{ url($event->getGuestUrl()) }}</loc>
            <lastmod>{{ $event->updated_at->toIso8601String() }}</lastmod>
            <changefreq>daily</changefreq>
            <priority>0.8</priority>
        </url>
    @endforeach
</urlset>