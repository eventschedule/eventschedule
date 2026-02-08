<x-app-admin-layout>
    <x-slot name="head">
        <script {!! nonce_attr() !!}>
            function previewPost(title, content, excerpt, author, tags) {
                closePreview();
                const modal = document.createElement('div');
                modal.className = 'fixed inset-0 bg-gray-600/50 dark:bg-gray-900/75 overflow-y-auto h-full w-full z-50';
                modal.id = 'preview-modal';
                const tagsArray = tags ? tags.split(', ') : [];
                const tagsHtml = tagsArray.map(tag => `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/50 text-blue-800 dark:text-blue-300 mr-2">${tag}</span>`).join('');
                modal.innerHTML = `
                    <div class="relative top-20 mx-auto p-5 border border-gray-200 dark:border-gray-700 w-full max-w-4xl shadow-lg rounded-lg bg-white dark:bg-gray-800 overflow-hidden">
                        <div class="mt-3">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Preview: ${title}</h3>
                                <button class="js-close-preview text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                            <div class="prose prose-lg dark:prose-invert max-w-none overflow-x-auto">
                                <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-4">${title}</h1>
                                <div class="flex items-center gap-x-4 text-sm text-gray-500 dark:text-gray-400 mb-6">
                                    <span>By ${author}</span>
                                    <span>•</span>
                                    <span class="text-yellow-600 dark:text-yellow-500 font-medium">{{ __('messages.draft_preview') }}</span>
                                </div>
                                ${excerpt ? `<p class="text-xl text-gray-600 dark:text-gray-300 leading-relaxed mb-6">${excerpt}</p>` : ''}
                                ${tagsHtml ? `<div class="flex flex-wrap gap-2 mb-6">${tagsHtml}</div>` : ''}
                                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                                    ${content}
                                </div>
                            </div>
                            <div class="mt-6 flex justify-end">
                                <button class="js-close-preview bg-gray-600 dark:bg-gray-700 text-white px-4 py-2 rounded-md hover:bg-gray-700 dark:hover:bg-gray-600 transition-colors">
                                    {{ __('messages.close_preview') }}
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                document.getElementById('preview-modal-root').appendChild(modal);
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        closePreview();
                    }
                });
            }
            function closePreview() {
                const modal = document.getElementById('preview-modal');
                if (modal) {
                    modal.remove();
                }
            }
        </script>
    </x-slot>

    @include('admin.partials._navigation', ['active' => 'blog'])

    <div class="mb-6"></div>

    <div class="flow-root">
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-base font-semibold leading-6 text-gray-900 dark:text-gray-100">{{ __('messages.blog_posts') }}</h1>
                <p class="mt-2 text-sm text-gray-700 dark:text-gray-400">
                    {{ __('messages.blog_posts_description') }}
                </p>
            </div>
            @if (config('services.google.gemini_key'))
            <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                <x-brand-link href="{{ route('blog.create') }}">
                    <svg class="-ml-0.5 mr-2 h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    {{ __('messages.create_post') }}
                </x-brand-link>
            </div>
            @endif
        </div>

        <div class="mt-8">
            <x-gemini-setup-guide />
        </div>

        @if (config('services.google.gemini_key'))
        <div class="mt-8 flow-root">
            <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                    <div class="overflow-hidden shadow ring-1 ring-black/5 dark:ring-white/10 rounded-lg">
                        <div class="overflow-x-auto scrollbar-thin">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-800">
                                    <tr>
                                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100 sm:pl-6 w-1/3">{{ __('messages.title') }}</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('messages.status') }}</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('messages.published') }}</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('messages.views') }}</th>
                                        <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                            <span class="sr-only">{{ __('messages.actions') }}</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                                    @forelse($posts as $post)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                            <td class="py-4 pl-4 pr-3 text-sm font-medium text-gray-900 dark:text-gray-100 sm:pl-6 w-1/3">
                                                <div class="max-w-xs">
                                                    <div class="font-medium text-gray-900 dark:text-gray-100 truncate">{{ $post->title }}</div>
                                                    <div class="text-gray-500 dark:text-gray-400 truncate">{{ Str::limit($post->excerpt, 60) }}</div>
                                                </div>
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                                @if($post->is_published && $post->published_at && $post->published_at <= now())
                                                    <span class="inline-flex items-center rounded-md bg-green-50 dark:bg-green-900/30 px-2 py-1 text-xs font-medium text-green-700 dark:text-green-400 ring-1 ring-inset ring-green-600/20 dark:ring-green-500/30">
                                                        {{ __('messages.published') }}
                                                    </span>
                                                @elseif($post->is_published && $post->published_at && $post->published_at > now())
                                                    <span class="inline-flex items-center rounded-md bg-yellow-50 dark:bg-yellow-900/30 px-2 py-1 text-xs font-medium text-yellow-700 dark:text-yellow-400 ring-1 ring-inset ring-yellow-600/20 dark:ring-yellow-500/30">
                                                        {{ __('messages.scheduled') }}
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center rounded-md bg-gray-50 dark:bg-gray-700 px-2 py-1 text-xs font-medium text-gray-700 dark:text-gray-300 ring-1 ring-inset ring-gray-600/20 dark:ring-gray-500/30">
                                                        {{ __('messages.draft') }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                                @if($post->published_at)
                                                    {{ $post->formatted_published_at }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                                {{ number_format($post->view_count) }}
                                            </td>
                                            <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                                <div class="flex items-center justify-end gap-3">
                                                    @if($post->is_published && $post->published_at && $post->published_at <= now())
                                                        <a href="{{ route('blog.show', $post->slug) }}"
                                                           target="_blank"
                                                           class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 transition-colors"
                                                           title="{{ __('messages.view') }}">
                                                            {{ __('messages.view') }}
                                                        </a>
                                                    @elseif($post->is_published && $post->published_at && $post->published_at > now())
                                                        <span class="text-gray-400 dark:text-gray-500" title="{{ __('messages.scheduled') }}">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                            </svg>
                                                        </span>
                                                    @else
                                                        <a href="{{ route('blog.show', [$post->slug]) }}?preview=1"
                                                           target="_blank"
                                                           class="text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-300 transition-colors"
                                                           title="{{ __('messages.preview') }}">
                                                            {{ __('messages.preview') }}
                                                        </a>
                                                    @endif
                                                    <a href="{{ route('blog.edit', $post->encodeId()) }}"
                                                       class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 transition-colors">
                                                        {{ __('messages.edit') }}
                                                    </a>
                                                    <form method="POST" action="{{ route('blog.destroy', $post->encodeId()) }}"
                                                          class="js-confirm-form inline" data-confirm="{{ __('messages.confirm_delete_post') }}"
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 transition-colors">
                                                            {{ __('messages.delete') }}
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                                <div class="flex flex-col items-center">
                                                    <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                    </svg>
                                                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('messages.no_posts') }}</h3>
                                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.no_posts_description') }}</p>
                                                    <div class="mt-6">
                                                        <x-brand-link href="{{ route('blog.create') }}" class="text-sm px-3 py-2">
                                                            {{ __('messages.create_post') }}
                                                        </x-brand-link>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($posts->hasPages())
            <div class="mt-8">
                {{ $posts->links() }}
            </div>
        @endif
        @endif
    </div>
</x-app-admin-layout>

{{-- Preview Modal Root --}}
<div id="preview-modal-root"></div>

<script {!! nonce_attr() !!}>
    document.addEventListener('click', function(e) {
        if (e.target.closest('.js-close-preview')) {
            closePreview();
        }
    });

    document.addEventListener('submit', function(e) {
        var form = e.target.closest('.js-confirm-form');
        if (form) {
            if (!confirm(form.getAttribute('data-confirm'))) {
                e.preventDefault();
            }
        }
    });
</script>
