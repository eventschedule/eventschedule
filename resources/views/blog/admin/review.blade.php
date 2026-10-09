<x-app-admin-layout>
    @php
        $labels = [
            \App\Services\Blog\BlogReview::KEEP => __('messages.blog_review_keep'),
            \App\Services\Blog\BlogReview::REWRITE => __('messages.blog_review_rewrite'),
            \App\Services\Blog\BlogReview::MERGE => __('messages.blog_review_merge_into'),
            \App\Services\Blog\BlogReview::HIDE => __('messages.blog_noindex'),
        ];
        $marks = [
            \App\Services\Blog\BlogReview::KEEP => 'is-on',
            \App\Services\Blog\BlogReview::REWRITE => 'is-info',
            \App\Services\Blog\BlogReview::MERGE => 'is-warn',
            \App\Services\Blog\BlogReview::HIDE => 'is-warn',
        ];
    @endphp

    @include('admin.partials._navigation', ['active' => 'blog'])

    {{-- The review of the posts already published (App\Services\Blog\BlogReview). The page
         proposes; each change is a button on a post's own row, pressed by a person. --}}
    <div class="page-shell">
        <div class="page-head">
            <div class="min-w-0">
                <a href="{{ route('blog.admin.index') }}" class="page-back">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
                    <span>{{ __('messages.blog_posts') }}</span>
                </a>
                <p class="page-lead">{{ __('messages.blog_review_lead') }}</p>
            </div>
            <div class="page-actions">
                @if ($posts->count() > 0 && $unchecked > 0 && (config('services.google.gemini_key') || config('services.openai.api_key')))
                <form method="POST" action="{{ route('blog.review.claims') }}">
                    @csrf
                    <x-secondary-button type="submit">{{ __('messages.blog_review_check_claims', ['count' => min($unchecked, \App\Http\Controllers\BlogController::CLAIMS_PER_PRESS)]) }}</x-secondary-button>
                </form>
                @endif
                <form method="POST" action="{{ route('blog.review.run') }}">
                    @csrf
                    <x-brand-button type="submit">{{ __('messages.blog_review_run') }}</x-brand-button>
                </form>
            </div>
        </div>

        <div class="page-stack">
            <x-page-flash :keys="['message' => 'success', 'error' => 'error']" />

            @if ($posts->count() > 0)
            <div class="ap-card rounded-xl page-stats is-auto">
                @foreach ($labels as $action => $label)
                <div class="page-stat">
                    <div class="page-stat-value">{{ number_format($counts[$action] ?? 0) }}</div>
                    <div class="page-stat-label">{{ $label }}</div>
                </div>
                @endforeach
            </div>

            <div class="ap-card rounded-xl overflow-hidden">
                <div class="page-scroll">
                    <table class="page-table is-wide">
                        <thead>
                            <tr>
                                <th scope="col">{{ __('messages.title') }}</th>
                                <th scope="col" class="c-num">{{ __('messages.views') }}</th>
                                <th scope="col" class="c-num">{{ __('messages.blog_word_count') }}</th>
                                <th scope="col">{{ __('messages.blog_review_findings') }}</th>
                                <th scope="col">{{ __('messages.blog_review_proposed') }}</th>
                                <th scope="col"><span class="sr-only">{{ __('messages.actions') }}</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($posts as $post)
                            @php
                                $found = $post->review;
                                $action = $found['action'] ?? \App\Services\Blog\BlogReview::KEEP;
                            @endphp
                            <tr>
                                <td class="c-main c-wrap">
                                    <a href="{{ route('blog.show', $post->slug) }}" target="_blank" rel="noopener" class="event-link"><bdi>{{ $post->title }}</bdi></a>
                                    <span class="c-sub"><bdi>{{ $post->kicker() }}</bdi></span>
                                </td>
                                <td class="c-num" data-label="{{ __('messages.views') }}">{{ number_format($post->view_count) }}</td>
                                <td class="c-num" data-label="{{ __('messages.blog_word_count') }}">{{ number_format($found['words'] ?? 0) }}</td>
                                <td class="c-wrap" data-label="{{ __('messages.blog_review_findings') }}">
                                    @foreach (array_slice($found['findings'] ?? [], 0, 6) as $finding)
                                    <span class="c-sub"><bdi>{{ Str::limit($finding, 120) }}</bdi></span>
                                    @endforeach
                                    @if (! empty($found['same_as']))
                                    <span class="c-sub"><bdi>{{ __('messages.blog_review_same_subject') }}: {{ Str::limit(implode('; ', $found['same_as']), 160) }}</bdi></span>
                                    @endif
                                    @if (! empty($found['unsupported']))
                                    <span class="c-sub text-amber-700 dark:text-amber-400"><bdi>{{ __('messages.blog_review_unsupported') }}: {{ Str::limit(implode(' / ', $found['unsupported']), 260) }}</bdi></span>
                                    @endif
                                </td>
                                <td data-label="{{ __('messages.blog_review_proposed') }}">
                                    <span class="event-status {{ $marks[$action] ?? '' }}">{{ $labels[$action] ?? $action }}</span>
                                    @if (! empty($found['merge_into']))
                                    <span class="c-sub"><bdi>{{ $found['merge_into'] }}</bdi></span>
                                    @endif
                                </td>
                                <td class="c-actions">
                                    <a href="{{ route('blog.edit', $post->encodeId()) }}" class="event-link">{{ $action === \App\Services\Blog\BlogReview::REWRITE ? __('messages.blog_review_rewrite') : __('messages.edit') }}</a>
                                    @if ($action === \App\Services\Blog\BlogReview::MERGE && ! empty($found['merge_into']))
                                    <form method="POST" action="{{ route('blog.merge', $post->encodeId()) }}" data-confirm="{{ __('messages.blog_review_merge_into') }} {{ $found['merge_into'] }}?">
                                        @csrf
                                        <input type="hidden" name="into" value="{{ $found['merge_into'] }}">
                                        <button type="submit" class="event-link">{{ __('messages.blog_merge') }}</button>
                                    </form>
                                    @endif
                                    @if ($action === \App\Services\Blog\BlogReview::HIDE && ! $post->noindex)
                                    <form method="POST" action="{{ route('blog.noindex', $post->encodeId()) }}">
                                        @csrf
                                        <input type="hidden" name="noindex" value="1">
                                        <button type="submit" class="event-link">{{ __('messages.blog_noindex') }}</button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @else
            <x-page-empty :title="__('messages.blog_review_none')" />
            @endif
        </div>
    </div>
</x-app-admin-layout>
