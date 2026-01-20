<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>{{ url('/') }}</loc>
        <lastmod>{{ $lastmod }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>1.0</priority>
    </url>
    @if(config('app.hosted'))
    <url>
        <loc>{{ url('/features') }}</loc>
        <lastmod>{{ $lastmod }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.9</priority>
    </url>
    <url>
        <loc>{{ url('/pricing') }}</loc>
        <lastmod>{{ $lastmod }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.9</priority>
    </url>
    <url>
        <loc>{{ url('/about') }}</loc>
        <lastmod>{{ $lastmod }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc>{{ url('/ticketing') }}</loc>
        <lastmod>{{ $lastmod }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc>{{ url('/integrations') }}</loc>
        <lastmod>{{ $lastmod }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc>{{ url('/selfhost') }}</loc>
        <lastmod>{{ $lastmod }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc>{{ url('/privacy') }}</loc>
        <lastmod>{{ $lastmod }}</lastmod>
        <changefreq>yearly</changefreq>
        <priority>0.5</priority>
    </url>
    <url>
        <loc>{{ url('/terms-of-service') }}</loc>
        <lastmod>{{ $lastmod }}</lastmod>
        <changefreq>yearly</changefreq>
        <priority>0.5</priority>
    </url>
    <url>
        <loc>{{ url('/self-hosting-terms-of-service') }}</loc>
        <lastmod>{{ $lastmod }}</lastmod>
        <changefreq>yearly</changefreq>
        <priority>0.5</priority>
    </url>
    @endif
    @foreach($blogPosts as $post)
        <url>
            <loc>{{ route('blog.show', $post->slug) }}</loc>
            <lastmod>{{ $post->published_at->toIso8601String() }}</lastmod>
            <changefreq>monthly</changefreq>
            <priority>0.7</priority>
        </url>
    @endforeach
    @foreach($roles as $role)
        <url>
            <loc>{{ url($role->getGuestUrl()) }}</loc>
            <lastmod>{{ $role->updated_at->toIso8601String() }}</lastmod>
            <changefreq>daily</changefreq>
            <priority>0.8</priority>
        </url>
        @foreach($role->groups as $group)
            @if($group->slug)
                <url>
                    <loc>{{ url($role->getGuestUrl() . '/' . $group->slug) }}</loc>
                    <lastmod>{{ $group->updated_at->toIso8601String() }}</lastmod>
                    <changefreq>daily</changefreq>
                    <priority>0.7</priority>
                </url>
            @endif
        @endforeach
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