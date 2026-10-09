<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Services\Blog\BlogReview;
use App\Services\Blog\BlogWriter;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class BlogController extends Controller
{
    /** Posts whose claims one press of "Check the claims" asks the model about. */
    public const CLAIMS_PER_PRESS = 3;

    /**
     * The blog's front page, a section of it, or a search of it.
     *
     * One query for the page of posts and one grouped count for the section chips. The old
     * version read every post twice a request: once for 641 tags, once for an archive list the
     * page never printed.
     */
    public function index(Request $request, ?string $category = null)
    {
        if ($category !== null && ! isset(BlogPost::CATEGORIES[$category])) {
            abort(404);
        }

        $query = BlogPost::published()->orderBy('published_at', 'desc');

        // A tag is a string or it is nothing: ?tag[]=x used to reach the query as an array.
        $tag = $request->query('tag');
        $tag = is_string($tag) && mb_check_encoding($tag, 'UTF-8') && mb_strlen($tag) <= 100 && trim($tag) !== '' ? $tag : null;
        if ($tag !== null) {
            $query->byTag($tag);
        }

        $search = $request->query('q');
        $search = is_string($search) && mb_check_encoding($search, 'UTF-8') ? trim(mb_substr($search, 0, 80)) : '';
        if ($search !== '') {
            $like = '%'.addcslashes($search, '%_\\').'%';
            $query->where(fn ($q) => $q->where('title', 'like', $like)->orWhere('excerpt', 'like', $like));
        }

        if ($category !== null) {
            $query->inCategory($category);
        }

        $monthLabel = null;
        $year = $request->query('year');
        $month = $request->query('month');
        if (is_string($year) && is_string($month) && ctype_digit($year) && ctype_digit($month)
            && $month >= 1 && $month <= 12 && $year >= 2000 && $year <= 2100) {
            $query->byMonth((int) $year, (int) $month);
            $monthLabel = Carbon::create((int) $year, (int) $month)->format('F Y');
        }

        // The audience posts are a directory, not a feed: 150 of them, read by kind of event.
        $directory = $category === 'by-event' && $search === '' ? $this->audienceDirectory() : null;

        // No content column: a card needs none of it.
        $columns = ['id', 'title', 'slug', 'excerpt', 'tags', 'category', 'featured_image', 'published_at', 'updated_at'];

        // The front page leads with the newest post, set apart from the grid. It is taken out of
        // the paged list on every page, so the grid is always whole rows of twelve.
        $lead = null;
        if ($category === null && $tag === null && $search === '' && $monthLabel === null) {
            $lead = (clone $query)->first($columns);
            if ($lead) {
                $query->where('id', '!=', $lead->id);
            }
        }

        $posts = $query->select($columns)->paginate(12)->withQueryString();

        $counts = BlogPost::published()
            ->selectRaw('category, count(*) as posts')
            ->groupBy('category')
            ->pluck('posts', 'category');

        return view('blog.index', [
            'posts' => $posts,
            'lead' => $posts->currentPage() === 1 ? $lead : null,
            'category' => $category,
            'categories' => BlogPost::CATEGORIES,
            'counts' => $counts,
            'total' => (int) $counts->sum(),
            'tag' => $tag,
            'search' => $search,
            'monthLabel' => $monthLabel,
            'directory' => $directory,
        ]);
    }

    /**
     * The audience posts grouped under the page they belong to, in the order of
     * config/sub_audiences.php: [['title' => 'Museums', 'page' => 'for-museums', 'posts' => [...]]].
     */
    private function audienceDirectory(): array
    {
        $titles = BlogPost::published()->where('category', 'by-event')->pluck('title', 'slug');
        $groups = [];

        foreach (config('sub_audiences', []) as $audience) {
            $posts = [];
            foreach ($audience['sub_audiences'] as $sub) {
                if (isset($titles[$sub['slug']])) {
                    $posts[] = ['slug' => $sub['slug'], 'name' => $sub['name'], 'title' => $titles[$sub['slug']]];
                }
            }
            if ($posts) {
                $groups[] = ['title' => $audience['title'], 'page' => $audience['page'], 'posts' => $posts];
            }
        }

        usort($groups, fn ($a, $b) => strcasecmp($a['title'], $b['title']));

        return $groups;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        return view('blog.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'tags' => 'nullable|string',
            'published_at' => 'nullable|date',
            'meta_title' => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:160',
            'featured_image' => 'nullable|string|in:'.implode(',', array_keys(BlogPost::getAvailableHeaderImages(false))),
            'author_name' => 'nullable|string|max:255',
            'is_published' => 'boolean',
            'category' => 'nullable|string|in:'.implode(',', array_keys(BlogPost::CATEGORIES)),
            'primary_query' => 'nullable|string|max:255',
            'faq' => 'nullable|json',
        ]);

        if (array_key_exists('faq', $data)) {
            $data['faq'] = $data['faq'] ? json_decode($data['faq'], true) : null;
        }

        // Handle tags
        if ($request->has('tags')) {
            $tags = array_map('trim', explode(',', $request->tags));
            $tags = array_filter($tags);
            $data['tags'] = $tags;
        }

        // Set published_at if not provided but is_published is true
        if ($request->is_published && ! $request->published_at) {
            $data['published_at'] = now()->addSeconds(rand(-60 * 60, 0));
        }

        BlogPost::create($data);

        return redirect()->route('blog.admin.index')->with('message', 'Blog post created successfully.');
    }

    /**
     * RSS feed of recent blog posts.
     */
    public function feed()
    {
        $posts = BlogPost::published()
            ->orderBy('published_at', 'desc')
            ->take(20)
            ->get();

        return response()
            ->view('blog.feed', compact('posts'))
            ->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }

    /**
     * Display the specified resource.
     */
    public function show($slug)
    {
        // A post merged into another (the review of the existing posts) keeps its address alive.
        $movedTo = BlogPost::where('slug', $slug)->whereNotNull('redirect_slug')->value('redirect_slug');
        if ($movedTo && strcasecmp($movedTo, $slug) !== 0 && BlogPost::published()->where('slug', $movedTo)->exists()) {
            return redirect()->to(route('blog.show', $movedTo), 301);
        }

        $isPreview = request()->query('preview') == 1;
        if ($isPreview && auth()->check() && auth()->user()->isAdmin()) {
            $post = \App\Models\BlogPost::where('slug', $slug)->firstOrFail();
        } else {
            $post = \App\Models\BlogPost::published()->where('slug', $slug)->firstOrFail();
        }

        // A read by a person, counted once a request: not a preview, not an admin, and not a
        // crawler. Until 2026-10 every fetch counted, Googlebot's included, so "views" in the
        // admin list and in the review said how often a post was crawled. The filters are the
        // ones the marketing pages' own counter uses (TrackMarketingVisit).
        if (! $isPreview
            && (! auth()->user() || ! auth()->user()->isAdmin())
            && ! \App\Models\PageView::isBot(request()->userAgent())
            && ! \App\Models\PageView::isSuspiciousRequest(request())) {
            $post->incrementViewCount();
        }

        $relatedPosts = $post->relatedPosts();

        // Check if this is a sub-audience blog post
        $subAudienceInfo = get_sub_audience_info($post->slug);

        return view('blog.show', compact('post', 'relatedPosts', 'subAudienceInfo'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $blogPostId)
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $blogPost = BlogPost::findOrFail(UrlUtils::decodeId($blogPostId));

        return view('blog.edit', compact('blogPost'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $blogPostId)
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $blogPost = BlogPost::findOrFail(UrlUtils::decodeId($blogPostId));

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'tags' => 'nullable|string',
            'published_at' => 'nullable|date',
            'meta_title' => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:160',
            'featured_image' => 'nullable|string|in:'.implode(',', array_keys(BlogPost::getAvailableHeaderImages(false))),
            'author_name' => 'nullable|string|max:255',
            'is_published' => 'boolean',
            'category' => 'nullable|string|in:'.implode(',', array_keys(BlogPost::CATEGORIES)),
            'primary_query' => 'nullable|string|max:255',
            'faq' => 'nullable|json',
        ]);

        if (array_key_exists('faq', $data)) {
            $data['faq'] = $data['faq'] ? json_decode($data['faq'], true) : null;
        }

        // Handle tags
        if ($request->has('tags')) {
            $tags = array_map('trim', explode(',', $request->tags));
            $tags = array_filter($tags);
            $data['tags'] = $tags;
        }

        // Set published_at if not provided but is_published is true
        if ($request->is_published && ! $request->published_at && ! $blogPost->published_at) {
            $data['published_at'] = now();
        }

        // Publishing a post the check held is the admin's answer to it.
        if ($request->boolean('is_published')) {
            $data['held_reason'] = null;
        }

        $blogPost->update($data);

        return redirect()->route('blog.admin.index')->with('message', 'Blog post updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $blogPostId)
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $blogPost = BlogPost::findOrFail(UrlUtils::decodeId($blogPostId));
        $blogPost->delete();
        Cache::forget('sub_audience_blog_'.$blogPost->slug);

        return redirect()->route('blog.admin.index')->with('message', 'Blog post deleted successfully.');
    }

    /**
     * Keep a post out of the search index (or put it back) without unpublishing it.
     *
     * Deliberately its own action rather than a field on update(): noindex is not fillable, so
     * neither the edit form nor the AI generators can set it by accident.
     */
    public function toggleNoindex(Request $request, string $blogPostId)
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $blogPost = BlogPost::findOrFail(UrlUtils::decodeId($blogPostId));

        // Not a content edit, so it must not move updated_at, which feeds <lastmod> and
        // dateModified (see BlogPost::incrementViewCount()).
        BlogPost::withoutTimestamps(fn () => $blogPost->forceFill(['noindex' => $request->boolean('noindex')])->save());

        return redirect()->back()->with('message', __('messages.settings_saved'));
    }

    /**
     * Admin index - show all posts (published and unpublished)
     */
    public function adminIndex()
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $heldOnly = request()->boolean('held');
        $held = BlogPost::heldForReview()->count();

        $posts = BlogPost::query()
            ->when($heldOnly, fn ($query) => $query->heldForReview())
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('blog.admin.index', compact('posts', 'held', 'heldOnly'));
    }

    /**
     * The review of the posts already published: what the check finds in each, the posts on the
     * same subject, and what is proposed. Reading this page changes nothing.
     */
    public function review()
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $order = [BlogReview::MERGE => 0, BlogReview::HIDE => 1, BlogReview::REWRITE => 2, BlogReview::KEEP => 3];

        $posts = BlogPost::published()
            ->whereNotNull('review')
            ->get(['id', 'title', 'slug', 'category', 'view_count', 'noindex', 'review', 'published_at'])
            ->sortBy(fn ($post) => [$order[$post->review['action'] ?? BlogReview::KEEP] ?? 3, -(int) $post->view_count])
            ->values();

        return view('blog.admin.review', [
            'posts' => $posts,
            'total' => BlogPost::published()->count(),
            'counts' => $posts->countBy(fn ($post) => $post->review['action'] ?? BlogReview::KEEP),
            'unchecked' => $posts->filter(fn ($post) => ! isset($post->review['unsupported']))->count(),
        ]);
    }

    public function runReview()
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        @set_time_limit(180);

        return redirect()->route('blog.review')->with('message', __('messages.blog_review_done', ['count' => BlogReview::run()]));
    }

    /**
     * Ask the model about the product claims of the next few posts, the most read first. A few
     * a press: each is one model call, and a request has only so long.
     */
    public function checkClaims()
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        @set_time_limit(180);

        $checked = 0;
        $started = microtime(true);

        $posts = BlogPost::published()->whereNotNull('review')->orderByDesc('view_count')->get()
            ->filter(fn ($post) => ! isset($post->review['unsupported']))
            ->take(self::CLAIMS_PER_PRESS);

        foreach ($posts as $post) {
            if (microtime(true) - $started > 70) {
                break;
            }

            $checked += BlogReview::checkClaims($post) ? 1 : 0;
        }

        return redirect()->route('blog.review')->with('message', __('messages.blog_claims_checked', ['count' => $checked]));
    }

    /**
     * Merge a post into another: its address answers with a redirect to the other from now on,
     * and it leaves every list, the feed and the sitemap. Its text is kept. An empty `into`
     * undoes it.
     */
    public function merge(Request $request, string $blogPostId)
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $post = BlogPost::findOrFail(UrlUtils::decodeId($blogPostId));
        $into = trim((string) $request->input('into'));

        if ($into === '') {
            BlogPost::withoutTimestamps(fn () => $post->forceFill(['redirect_slug' => null])->save());

            return redirect()->back()->with('message', __('messages.blog_unmerged'));
        }

        // Only into a post a visitor can read, and never into itself or into a post that is
        // itself merged away: a redirect has to end somewhere.
        $target = BlogPost::published()->where('slug', $into)->where('id', '!=', $post->id)->first();

        if (! $target) {
            return redirect()->back()->with('error', __('messages.invalid_request'));
        }

        BlogPost::withoutTimestamps(fn () => $post->forceFill(['redirect_slug' => $target->slug])->save());

        // Whatever was merged into this post now ends at the same place.
        BlogPost::where('redirect_slug', $post->slug)->update(['redirect_slug' => $target->slug]);

        return redirect()->back()->with('message', __('messages.blog_merged'));
    }

    /**
     * Write a post for the create form, one step a request.
     *
     * The writer makes three model calls (a brief, a draft, an edit) that together take a minute
     * and a half: longer than one web request may, and a queued job would hold every ticket
     * email behind it. So the page asks for each step in turn and hands back what the last one
     * returned. Nothing is saved here: the form is filled, with the check's verdict above it,
     * and saving stays the admin's own decision.
     */
    public function generateContent(Request $request, BlogWriter $writer)
    {
        if (! auth()->user()->isAdmin()) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        $request->validate([
            'topic' => 'required|string|max:255',
            'step' => 'nullable|in:brief,draft,edit',
            'brief' => 'nullable|array',
            'draft' => 'nullable|array',
            // The post being rewritten, so it is not offered to itself as "already on the blog"
            // and the check does not call it a duplicate of itself.
            'except' => 'nullable|string|max:255',
        ]);

        $except = $request->filled('except') ? (string) $request->input('except') : null;

        @set_time_limit(180);

        $topic = (string) $request->input('topic');
        $step = $request->input('step', 'brief');
        $failed = response()->json(['error' => __('messages.failed_to_generate_content')], 500);

        try {
            if ($step === 'brief') {
                $state = $writer->begin(array_filter(['topic' => $topic, 'except' => $except]));

                return $state ? response()->json(['brief' => $state['brief']]) : $failed;
            }

            $brief = (array) $request->input('brief');
            if (! is_string($brief['primary_query'] ?? null) || ! is_array($brief['questions'] ?? null)) {
                return $failed;
            }

            $state = ['topic' => $topic, 'brief' => $brief, 'audience' => null, 'except' => $except, 'rewriting' => $except !== null];

            if ($step === 'draft') {
                $draft = $writer->draft($state);

                return $draft ? response()->json(['draft' => $draft]) : $failed;
            }

            $draft = (array) $request->input('draft');
            if (! is_string($draft['content'] ?? null) || ! is_string($draft['title'] ?? null)) {
                return $failed;
            }

            $finished = $writer->finish($state, $draft);

            if (! $finished) {
                return $failed;
            }

            \App\Services\UsageTrackingService::track(\App\Services\UsageTrackingService::GEMINI_BLOG);

            $post = $finished['post'];

            return response()->json([
                'title' => $post['title'],
                'content' => $post['content'],
                'excerpt' => $post['excerpt'] ?? '',
                'meta_title' => $post['title'],
                'meta_description' => $post['description'] ?? '',
                'category' => $post['category'],
                'primary_query' => $post['primary_query'],
                'faq' => $post['faq'] ?? [],
                'failures' => $finished['failures'],
            ]);
        } catch (\Exception $e) {
            report($e);

            return $failed;
        }
    }
}
