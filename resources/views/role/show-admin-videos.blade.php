<div class="pt-5">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ __('messages.videos') }}</h2>
        <p class="text-gray-600 dark:text-gray-400 mb-6">{{ __('messages.videos_description') }}</p>
        
        <div id="videos-app">
            <div v-if="loading" class="text-center py-8">
                <div class="inline-flex items-center px-4 py-2 font-semibold leading-6 text-sm shadow rounded-md text-white bg-blue-500 hover:bg-blue-400 transition ease-in-out duration-150">
                    <svg class="animate-spin -ms-1 me-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ __('messages.loading') }}
                </div>
            </div>
            
            <div v-else-if="talentRoles.length === 0" class="text-center py-8">
                <div class="text-gray-500 dark:text-gray-400">
                    <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.all_talent_roles_have_videos') }}</p>
                </div>
            </div>
            
            <div v-else class="space-y-6">
                <div v-for="role in talentRoles" :key="role.id" class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-6">
                    <div class="mb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">@{{ role.name }}</h3>
                        <p v-if="role.description" class="text-sm text-gray-500 dark:text-gray-400 mt-1">@{{ role.description }}</p>
                    </div>
                    
                    <div class="mt-4">
                        <div v-if="role.searching" class="flex items-center gap-x-2">
                            <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-500"></div>
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.searching_youtube') }}</span>
                        </div>
                        
                        <div v-else-if="role.videos && role.videos.length > 0" class="space-y-3">
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('messages.select_video') }}</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                <div v-for="video in role.videos" :key="video.id" 
                                        class="border rounded-lg p-3 cursor-pointer hover:border-blue-300 dark:hover:border-blue-600 transition-colors relative"
                                        :class="isVideoSelected(role, video) ? 'border-blue-500 dark:border-blue-400 bg-blue-50 dark:bg-blue-900/30' : 'border-gray-200 dark:border-gray-600'"
                                        @click="selectVideo(role, video)">
                                    <div class="aspect-video bg-gray-100 dark:bg-gray-600 rounded mb-2 flex items-center justify-center relative">
                                        <img v-if="video.thumbnail" :src="video.thumbnail" :alt="video.title" class="w-full h-full object-cover rounded">
                                        <svg v-else class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    
                                    <h5 class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">@{{ video.title }}</h5>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">@{{ video.channelTitle }}</p>
                                    
                                    <!-- Video stats and watch button -->
                                    <div class="flex items-center justify-between mt-2">
                                        <div class="flex items-center gap-x-4 text-xs text-gray-500 dark:text-gray-400">
                                            <div class="flex items-center gap-x-2">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                                </svg>
                                                <span>@{{ formatNumber(video.viewCount) }}</span>
                                            </div>
                                            <div class="flex items-center gap-x-2">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                                                </svg>
                                                <span>@{{ formatNumber(video.likeCount) }}</span>
                                            </div>
                                        </div>
                                        
                                        <!-- Watch button -->
                                        <a :href="video.url" target="_blank" 
                                            class="inline-flex items-center text-xs text-red-600 hover:text-red-700 font-medium transition-colors"
                                            @click.stop>
                                            <svg class="w-3 h-3 me-1" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                                            </svg>
                                            Watch
                                        </a>
                                    </div>
                                    
                                    <!-- Selection indicator -->
                                    <div v-if="isVideoSelected(role, video)" class="absolute top-2 end-2">
                                        <div class="bg-blue-500 text-white rounded-full w-6 h-6 flex items-center justify-center">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div v-if="role.selectedVideos && role.selectedVideos.length > 0" class="mt-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-x-3">
                                        <button @click="skipRole(role)" 
                                                class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800">
                                            {{ __('messages.skip') }}
                                        </button>
                                        <button @click="saveVideos(role)" 
                                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800">
                                            {{ __('messages.save_videos') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div v-else-if="role.videos && role.videos.length > 0" class="mt-4">
                                <div class="flex items-center justify-between">
                                    <div class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ __('messages.no_videos_selected') }}
                                    </div>
                                    <button @click="skipRole(role)" 
                                            class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800">
                                        {{ __('messages.skip') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div v-else-if="role.error" class="text-sm text-red-600 dark:text-red-400">
                            @{{ role.error }}
                        </div>
                        
                        <div v-else class="text-sm text-gray-500 dark:text-gray-400">
                            {{ __('messages.no_videos_found') }}
                            <button @click="skipRole(role)" 
                                    class="ms-3 inline-flex items-center px-3 py-1 border border-gray-300 dark:border-gray-600 text-xs font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800">
                                {{ __('messages.skip') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/vue.global.prod.js') }}" {!! nonce_attr() !!}></script>
<script {!! nonce_attr() !!}>
document.addEventListener('DOMContentLoaded', function() {
    const { createApp } = Vue;
    
    createApp({
        data() {
            return {
                talentRoles: [],
                loading: true
            }
        },
        mounted() {
            this.loadTalentRoles();
        },
        methods: {
            formatNumber(num) {
                if (!num) return '0';
                if (num >= 1000000) {
                    return (num / 1000000).toFixed(1) + 'M';
                } else if (num >= 1000) {
                    return (num / 1000).toFixed(1) + 'K';
                }
                return num.toString();
            },
            
            async loadTalentRoles() {
                try {
                    const response = await fetch(`{{ url('/' . $role->subdomain . '/match-videos') }}`);
                    const data = await response.json();
                    this.talentRoles = data.map(role => ({
                        ...role,
                        searching: false,
                        videos: null,
                        selectedVideos: [],
                        error: null
                    }));
                    
                    // Start searching for videos for each role
                    this.talentRoles.forEach(role => {
                        this.searchVideos(role);
                    });
                } catch (error) {
                    console.error('Error loading talent roles:', error);
                } finally {
                    this.loading = false;
                }
            },
            
            async searchVideos(role) {
                role.searching = true;
                role.error = null;
                
                try {
                    const response = await fetch(`{{ url('/' . $role->subdomain . '/search-youtube') }}?q=${encodeURIComponent(role.name)}`);
                    const data = await response.json();
                    
                    if (data.success && data.videos) {
                        role.videos = data.videos;
                                                // Auto-select the first video
                        if (data.videos.length >= 1) {
                            role.selectedVideos = [data.videos[0]];
                        }
                    } else {
                        role.error = data.message || '{{ __("messages.no_videos_found") }}';
                    }
                } catch (error) {
                    role.error = '{{ __("messages.error_searching_videos") }}';
                    console.error('Error searching videos:', error);
                } finally {
                    role.searching = false;
                }
            },
            
            isVideoSelected(role, video) {
                return role.selectedVideos && role.selectedVideos.some(v => v.id === video.id);
            },
            
            selectVideo(role, video) {
                if (!role.selectedVideos) {
                    role.selectedVideos = [];
                }

                const isSelected = role.selectedVideos.some(v => v.id === video.id);
                if (isSelected) {
                    // Deselect if clicking the already-selected video
                    role.selectedVideos = [];
                } else {
                    // Select this video (replacing any previous selection)
                    role.selectedVideos = [video];
                }
            },
            
            async saveVideos(role) {
                if (!role.selectedVideos || role.selectedVideos.length === 0) return;
                
                try {
                    const response = await fetch(`{{ url('/' . $role->subdomain . '/save-videos') }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            role_id: role.id,
                            videos: role.selectedVideos.map(video => ({
                                url: video.url,
                                title: video.title
                            }))
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        // Remove the role from the list since it now has videos
                        this.talentRoles = this.talentRoles.filter(r => r.id !== role.id);
                    } else {
                        alert(data.message || '{{ __("messages.error_saving_videos") }}');
                    }
                } catch (error) {
                    console.error('Error saving videos:', error);
                    alert('{{ __("messages.error_saving_videos") }}');
                }
            },
            
            async skipRole(role) {
                try {
                    const response = await fetch(`{{ url('/' . $role->subdomain . '/save-videos') }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            role_id: role.id,
                            videos: []
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        // Remove the role from the list since it has been skipped
                        this.talentRoles = this.talentRoles.filter(r => r.id !== role.id);
                    } else {
                        alert(data.message || '{{ __("messages.error_saving_videos") }}');
                    }
                } catch (error) {
                    console.error('Error skipping role:', error);
                    alert('{{ __("messages.error_saving_videos") }}');
                }
            }
        }
    }).mount('#videos-app');
});
</script> 