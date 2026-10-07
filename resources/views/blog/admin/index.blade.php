<x-app-admin-layout>
    <x-slot name="head">
        {{-- The one long heading of the list ("Hide from search engines") takes two lines: on one
             it pushed the row's actions off the edge of a laptop. --}}
        <style {!! nonce_attr() !!}>
            .blog-posts th.is-wrap {
              min-width: 8rem;
              white-space: normal;
            }
        </style>
    </x-slot>

    @include('admin.partials._navigation', ['active' => 'blog'])

    {{-- Every post, published or not. The list and "Create post" no longer wait on an AI key:
         writing, editing, hiding and deleting a post never needed one, only the AI draft on the
         create page does, and that page says so. --}}
    <div class="page-head">
        <p class="page-lead">{{ __('messages.blog_posts_description') }}</p>
        <div class="page-actions">
            <x-brand-link href="{{ route('blog.create') }}">
                <svg class="-ms-0.5 me-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                {{ __('messages.create_post') }}
            </x-brand-link>
        </div>
    </div>

    <div class="page-shell page-stack">
        @if ($posts->count() > 0)
        <div>
        <div class="ap-card rounded-xl overflow-hidden">
            <div class="page-scroll">
                <table class="page-table is-wide blog-posts">
                    <thead>
                        <tr>
                            <th scope="col">{{ __('messages.title') }}</th>
                            <th scope="col">{{ __('messages.status') }}</th>
                            <th scope="col" class="c-num">{{ __('messages.views') }}</th>
                            <th scope="col" class="c-num">{{ __('messages.blog_word_count') }}</th>
                            <th scope="col" class="is-wrap">{{ __('messages.blog_noindex') }}</th>
                            <th scope="col"><span class="sr-only">{{ __('messages.actions') }}</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($posts as $post)
                        @php
                            $live = $post->is_published && $post->published_at && $post->published_at <= now();
                            $scheduled = $post->is_published && $post->published_at && $post->published_at > now();
                            $words = $post->wordCount();
                        @endphp
                        <tr>
                            <td class="c-main c-wrap">
                                <a href="{{ route('blog.edit', $post->encodeId()) }}" class="event-link"><bdi>{{ $post->title }}</bdi></a>
                                @if ($post->excerpt)
                                <span class="c-sub"><bdi>{{ Str::limit($post->excerpt, 90) }}</bdi></span>
                                @endif
                            </td>
                            {{-- What state the post is in, and the day it went or goes out. --}}
                            <td>
                                @if ($live)
                                <span class="event-status is-on">{{ __('messages.published') }}</span>
                                @elseif ($scheduled)
                                <span class="event-status is-info">{{ __('messages.scheduled') }}</span>
                                @else
                                <span class="event-status">{{ __('messages.draft') }}</span>
                                @endif
                                @if ($post->published_at)
                                <span class="c-sub whitespace-nowrap">{{ $post->published_at->translatedFormat('M j, Y') }}</span>
                                @endif
                            </td>
                            <td class="c-num" data-label="{{ __('messages.views') }}">{{ number_format($post->view_count) }}</td>
                            {{-- The triage report: a thin post is the one to hide. Amber below the
                                 generators' own quality floor. --}}
                            <td class="c-num" data-label="{{ __('messages.blog_word_count') }}">
                                @if ($words < \App\Models\BlogPost::QUALITY_MIN_WORDS)
                                <span class="font-semibold text-amber-700 dark:text-amber-400">{{ number_format($words) }}</span>
                                @else
                                {{ number_format($words) }}
                                @endif
                            </td>
                            <td data-label="{{ __('messages.blog_noindex') }}">
                                <form method="POST" action="{{ route('blog.noindex', $post->encodeId()) }}" class="inline-flex align-middle">
                                    @csrf
                                    <x-toggle name="noindex" :checked="$post->noindex" :id="'noindex-'.$post->encodeId()" aria-label="{{ __('messages.blog_noindex') }}" data-auto-submit />
                                </form>
                            </td>
                            <td class="c-actions">
                                @if ($live)
                                <a href="{{ route('blog.show', $post->slug) }}" target="_blank" rel="noopener" class="event-link">{{ __('messages.view') }}</a>
                                @else
                                <a href="{{ route('blog.show', [$post->slug]) }}?preview=1" target="_blank" rel="noopener" class="event-link">{{ __('messages.preview') }}</a>
                                @endif
                                <a href="{{ route('blog.edit', $post->encodeId()) }}" class="event-link">{{ __('messages.edit') }}</a>
                                <form method="POST" action="{{ route('blog.destroy', $post->encodeId()) }}" data-confirm="{{ __('messages.confirm_delete_post') }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="event-link is-danger">{{ __('messages.delete') }}</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if ($posts->hasPages())
        <div class="page-pager">{{ $posts->links() }}</div>
        @endif
        </div>
        @else
        <div class="ap-card rounded-xl">
            <x-page-empty
                :title="__('messages.no_posts')"
                :text="__('messages.no_posts_description')"
                icon="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z">
                <x-brand-link href="{{ route('blog.create') }}">{{ __('messages.create_post') }}</x-brand-link>
            </x-page-empty>
        </div>
        @endif
    </div>
</x-app-admin-layout>
