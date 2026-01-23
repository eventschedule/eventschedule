<x-marketing-layout>
    <x-slot name="title">Blog Post Deleted | Event Schedule</x-slot>
    <x-slot name="description">The blog post has been successfully deleted.</x-slot>
    <x-slot name="keywords">blog, deleted</x-slot>

    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 flex items-center justify-center py-20">
        <div class="max-w-lg mx-auto px-4 text-center">
            <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-8 border border-white/20">
                <div class="w-16 h-16 bg-green-500/20 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>

                <h1 class="text-2xl font-bold text-white mb-4">Blog Post Deleted</h1>

                <p class="text-gray-300 mb-6">
                    The blog post "<span class="font-semibold text-white">{{ $title }}</span>" has been successfully deleted.
                </p>

                <a href="{{ route('blog.index') }}"
                   class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-500 to-pink-500 text-white font-semibold rounded-lg hover:from-purple-600 hover:to-pink-600 transition-all duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Blog
                </a>
            </div>
        </div>
    </div>
</x-marketing-layout>
