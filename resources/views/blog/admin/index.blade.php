<x-app-admin-layout>
    <x-slot name="head">
        <script {!! nonce_attr() !!}>
            function previewPost(title, content, excerpt, author, tags) {
                // Remove any existing modal
                closePreview();
                const modal = document.createElement('div');
                modal.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50';
                modal.id = 'preview-modal';
                const tagsArray = tags ? tags.split(', ') : [];
                const tagsHtml = tagsArray.map(tag => `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-2">${tag}</span>`).join('');
                modal.innerHTML = `
                    <div class="relative top-20 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-white overflow-hidden">
                        <div class="mt-3">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Preview: ${title}</h3>
                                <button onclick="closePreview()" class="text-gray-400 hover:text-gray-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                            <div class="prose prose-lg max-w-none overflow-x-auto">
                                <h1 class="text-3xl font-bold text-gray-900 mb-4">${title}</h1>
                                <div class="flex items-center gap-x-4 text-sm text-gray-500 mb-6">
                                    <span>By ${author}</span>
                                    <span>•</span>
                                    <span class="text-yellow-600 font-medium">Draft Preview</span>
                                </div>
                                ${excerpt ? `<p class="text-xl text-gray-600 leading-relaxed mb-6">${excerpt}</p>` : ''}
                                ${tagsHtml ? `<div class="flex flex-wrap gap-2 mb-6">${tagsHtml}</div>` : ''}
                                <div class="border-t border-gray-200 pt-6">
                                    ${content}
                                </div>
                            </div>
                            <div class="mt-6 flex justify-end">
                                <button onclick="closePreview()" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                                    Close Preview
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

    <div class="px-4 sm:px-6 lg:px-8">
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-base font-semibold leading-6 text-gray-900">Blog Posts</h1>
                <p class="mt-2 text-sm text-gray-700">
                    Manage your blog posts. Only published posts are visible to the public.
                </p>
            </div>
            <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                <a href="{{ route('blog.create') }}" 
                   class="block rounded-md bg-blue-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                    Create Post
                </a>
            </div>
        </div>
        
        <div class="mt-8 flow-root">
            <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                        <div class="overflow-x-auto" style="overflow-x: auto; scrollbar-width: thin;">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Title</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Published</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Views</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Tags</th>
                                        <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                            <span class="sr-only">Actions</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    @forelse($posts as $post)
                                        <tr>
                                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                                <div>
                                                    <div class="font-medium text-gray-900">{{ $post->title }}</div>
                                                    <div class="text-gray-500">{{ Str::limit($post->excerpt, 60) }}</div>
                                                </div>
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                @if($post->is_published && $post->published_at && $post->published_at <= now())
                                                    <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">
                                                        Published
                                                    </span>
                                                @elseif($post->is_published && $post->published_at && $post->published_at > now())
                                                    <span class="inline-flex items-center rounded-md bg-yellow-50 px-2 py-1 text-xs font-medium text-yellow-700 ring-1 ring-inset ring-yellow-600/20">
                                                        Scheduled
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-700 ring-1 ring-inset ring-gray-600/20">
                                                        Draft
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                @if($post->published_at)
                                                    {{ $post->formatted_published_at }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                {{ number_format($post->view_count) }}
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                @if($post->tags)
                                                    <div class="flex flex-wrap gap-1">
                                                        @foreach(array_slice($post->tags, 0, 3) as $tag)
                                                            <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-600/20">
                                                                {{ $tag }}
                                                            </span>
                                                        @endforeach
                                                        @if(count($post->tags) > 3)
                                                            <span class="text-gray-400">+{{ count($post->tags) - 3 }}</span>
                                                        @endif
                                                    </div>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                                <div class="flex items-center justify-end gap-2">
                                                    @if($post->is_published && $post->published_at && $post->published_at <= now())
                                                        <a href="{{ route('blog.show', $post->slug) }}" 
                                                           target="_blank"
                                                           class="text-blue-600 hover:text-blue-900"
                                                           title="View published post">
                                                            View
                                                        </a>
                                                    @elseif($post->is_published && $post->published_at && $post->published_at > now())
                                                        <span class="text-gray-400" title="Scheduled for {{ $post->formatted_published_at }}">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                            </svg>
                                                        </span>
                                                    @else
                                                        <a href="{{ route('blog.show', [$post->slug]) }}?preview=1" 
                                                           target="_blank"
                                                           class="text-gray-600 hover:text-gray-900"
                                                           title="Preview draft post">
                                                            Preview
                                                        </a>
                                                    @endif
                                                    <a href="{{ route('blog.edit', $post) }}" 
                                                       class="text-indigo-600 hover:text-indigo-900">
                                                        Edit
                                                    </a>
                                                    <form method="POST" action="{{ route('blog.destroy', $post) }}" 
                                                          onsubmit="return confirm('Are you sure you want to delete this post?')"
                                                          class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-900">
                                                            Delete
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500">
                                                <div class="flex flex-col items-center">
                                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                    </svg>
                                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No posts</h3>
                                                    <p class="mt-1 text-sm text-gray-500">Get started by creating a new blog post.</p>
                                                    <div class="mt-6">
                                                        <a href="{{ route('blog.create') }}" 
                                                           class="inline-flex items-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                                                            Create Post
                                                        </a>
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
    </div>
</x-app-admin-layout>

{{-- Preview Modal and Script --}}
<div id="preview-modal-root"></div> 