<script src="{{ asset('js/vue.global.prod.js') }}"></script>

<style>
    #event-import-app {
        visibility: hidden;
    }
    
    #event-import-app.loaded {
        visibility: visible;
    }
</style>

<form method="post"
    action="{{ isset($isGuest) && $isGuest ? route('event.guest_import', ['subdomain' => $role->subdomain]) : route('event.import', ['subdomain' => $role->subdomain]) }}"
    enctype="multipart/form-data"
    id="event-import-app">

    @csrf

        <div v-if="!preview || !preview.parsed || preview.parsed.length === 0">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md rounded-lg">
            <div class="max-w-3xl mx-auto">
                <!-- Centered single column layout -->
                <div class="flex justify-center">
                    <div class="w-full max-w-2xl md:max-w-xl lg:max-w-2xl">
                        <!-- Combined textarea and image section -->
                        <div class="mb-1">
                            <div class="relative">
                                <textarea id="event_details" 
                                    ref="eventDetails"
                                    name="event_details" 
                                    rows="7"
                                    v-model="eventDetails"
                                    v-bind:readonly="savedEvent"
                                    @input="handleInputChange"
                                    @paste="handlePaste" 
                                    @keydown="handleKeydown"
                                    @dragenter.prevent="dragEnterDetails"
                                    @dragover.prevent="dragOverDetails"
                                    @dragleave.prevent="dragLeaveDetails"
                                    @drop.prevent="handleDetailsImageDrop"
                                    @dragend="dragEndDetails"
                                    autofocus {{ config('services.google.gemini_key') ? '' : 'disabled' }}
                                    :class="['mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm transition-all duration-200', 
                                        isDraggingDetails ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30 ring-2 ring-blue-200 dark:ring-blue-800' : '']"
                                    :dir="{{ is_rtl() ? "'rtl'" : "'ltr'" }}"
                                    :style="{{ is_rtl() ? "'text-align: right;'" : "'text-align: left;'" }}"
                                    placeholder="{{ __('messages.drag_drop_image_or_type_text') }}"></textarea>
                                
                                <!-- Drop message overlay for textarea -->
                                <div v-show="isDraggingDetails" 
                                     @dragenter.prevent="dragEnterDetails"
                                     @dragover.prevent="dragOverDetails"
                                     @dragleave.prevent="dragLeaveDetails"
                                     @drop.prevent="handleDetailsImageDrop"
                                     @dragend="dragEndDetails"
                                     class="absolute inset-0 flex items-center justify-center bg-blue-50 dark:bg-blue-900/30 border-2 border-blue-500 rounded-md z-10 transition-all duration-200 ease-in-out"
                                     :class="{ 'opacity-100 scale-100': isDraggingDetails, 'opacity-0 scale-95': !isDraggingDetails }">
                                    <div class="text-center">
                                        <svg class="mx-auto h-8 w-8 text-blue-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                        </svg>
                                        <p class="text-blue-700 dark:text-blue-300 font-medium">Drop files here</p>
                                    </div>
                                </div>
                                
                                <!-- Image preview overlay -->
                                <div v-if="detailsImage" 
                                     :class="['absolute bottom-3 w-16 h-16 rounded-md overflow-hidden border-2 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 shadow-sm', 
                                         {{ is_rtl() ? "'right-3'" : "'left-3'" }}]">
                                    <img v-if="detailsImageUrl" 
                                         :src="detailsImageUrl" 
                                         class="object-cover w-full h-full" 
                                         alt="Event details image preview">
                                    <div v-else class="flex items-center justify-center h-full text-gray-400">
                                        <svg class="w-6 h-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                            <path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </div>
                                    
                                    <!-- Remove image button -->
                                    <button 
                                        @click="removeDetailsImage"
                                        type="button"
                                        :class="['absolute -top-1 w-5 h-5 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center text-xs transition-colors', 
                                            {{ is_rtl() ? "'-left-1'" : "'-right-1'" }}]"
                                        title="{{ __('messages.remove_image') }}">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                                
                                <!-- Plus icon button for file picker -->
                                <button 
                                    type="button"
                                    @click="openDetailsFileSelector"
                                    :disabled="isLoading || detailsImage"
                                    :class="['absolute p-2 rounded-md transition-all duration-200 shadow-md', 
                                        {{ is_rtl() ? "'left-16'" : "'right-16'" }} + ' bottom-3',
                                        (isLoading || detailsImage)
                                            ? 'bg-gray-300 dark:bg-gray-600 text-gray-500 dark:text-gray-400 cursor-not-allowed border border-gray-400 dark:border-gray-500' 
                                            : 'bg-gradient-to-br from-blue-500 via-blue-600 to-blue-700 hover:from-blue-600 hover:via-blue-700 hover:to-blue-800 text-white cursor-pointer border border-blue-400/30 hover:border-blue-300/50 shadow-lg hover:shadow-xl']"
                                    title="{{ __('messages.add_image') }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                </button>
                                
                                <!-- Submit button with up arrow -->
                                <button 
                                    type="button"
                                    @click="handleSubmit"
                                    :disabled="!canSubmit || isLoading"
                                    :class="['absolute p-2 rounded-md transition-all duration-200 shadow-md', 
                                        {{ is_rtl() ? "'left-5'" : "'right-5'" }} + ' bottom-3',
                                        canSubmit && !isLoading
                                            ? 'bg-gradient-to-br from-blue-500 via-blue-600 to-blue-700 hover:from-blue-600 hover:via-blue-700 hover:to-blue-800 text-white cursor-pointer border border-blue-400/30 hover:border-blue-300/50 shadow-lg hover:shadow-xl' 
                                            : 'bg-gray-300 dark:bg-gray-600 text-gray-500 dark:text-gray-400 cursor-not-allowed border border-gray-400 dark:border-gray-500']"
                                    title="{{ __('messages.submit') }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                                    </svg>
                                </button>
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('event_details')" />

                            @if (! config('services.google.gemini_key'))
                                <div class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                    {{ __('messages.gemini_key_required') }}
                                </div>
                            @endif

                            <!-- Error message display -->
                            <div v-if="errorMessage" class="mt-4 p-3 text-sm text-red-600 dark:text-red-400 bg-red-100 dark:bg-red-900/30 rounded-md">
                                @{{ errorMessage }}
                            </div>

                            <div v-if="isLoading" class="mt-4 flex items-center gap-3 text-sm text-gray-600 dark:text-gray-400">
                                <div class="relative">
                                    <div class="w-4 h-4 rounded-full bg-blue-500/30"></div>
                                    <div class="absolute top-0 left-0 w-4 h-4 rounded-full border-2 border-blue-500 border-t-transparent animate-spin"></div>
                                </div>
                                <div class="inline-flex items-center">
                                    <span class="animate-pulse">
                                        {{ __('messages.loading') }}
                                    </span>
                                    <span class="ml-1 inline-flex animate-[ellipsis_1.5s_steps(4,end)_infinite]">...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Show All Fields and Save All buttons when events are parsed -->
        <div v-if="preview && preview.parsed && preview.parsed.length > 0" class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md rounded-lg mb-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                @if (auth()->user() && auth()->user()->isAdmin())
                <div class="flex items-center mb-3 sm:mb-0">
                    <input type="checkbox" 
                            id="show_all_fields" 
                            v-model="showAllFields" 
                            @change="saveShowAllFieldsPreference"
                            class="rounded border-gray-300 text-blue-500 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                    <label for="show_all_fields" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                        {{ __('messages.show_all_fields') }}
                    </label>
                </div>
                @else
                <div></div>
                @endif

                <!-- Action buttons - now includes Save All -->
                <div class="flex gap-2 self-end sm:self-auto">
                    <button @click="handleSaveAll" v-if="({{ request()->has('automate') ? 'true' : 'false' }} || preview.parsed.length > 1) && !{{ isset($isGuest) && $isGuest ? 'true' : 'false' }}" type="button" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-colors">
                        {{ __('messages.save_all') }}
                    </button>
                </div>
            </div>
        </div>
        </div>

        @if (auth()->user() && auth()->user()->isAdmin())
        <div class="flex items-center my-4">
            <input type="checkbox" 
                    id="show_all_fields" 
                    v-model="showAllFields" 
                    @change="saveShowAllFieldsPreference"
                    class="rounded border-gray-300 text-blue-500 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
            <label for="show_all_fields" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                {{ __('messages.show_all_fields') }}
            </label>
        </div>
        @endif

        <!-- Hidden file input for details image -->
        <input type="file"
                ref="detailsFileInput"
                @change="handleDetailsFileSelect"
                accept="image/*"
                class="hidden">

        <!-- Events cards - Moved outside the main div -->
        <div v-if="preview && preview.parsed && preview.parsed.length > 0" class="space-y-6">
            <div v-for="(event, idx) in preview.parsed" :key="idx" 
                    :class="['p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg mt-4', 
                            savedEvents[idx] ? 'border-2 border-green-500 dark:border-green-600' : '']">
                
                <!-- Card header -->
                @if (auth()->user())
                <div v-if="savedEvents[idx] || saveErrors[idx]" :class="['px-4 py-3 -m-4 sm:-m-8 mt-4 sm:mb-4 flex justify-between items-center rounded-t-lg', 
                                savedEvents[idx] ? 'bg-green-50 dark:bg-green-900/30' : 'bg-red-50 dark:bg-red-900/30']">
                    <h3 class="font-medium text-lg">
                        <span v-if="savedEvents[idx]" class="ml-2 text-sm text-green-600 dark:text-green-400">
                            <svg class="inline-block w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            {{ __('messages.saved') }}
                        </span>
                        <span v-else-if="saveErrors[idx]" class="ml-2 text-sm text-red-600 dark:text-red-400">
                            <svg class="inline-block w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            {{ __('messages.error') }}: @{{ saveErrors[idx] }}
                        </span>
                    </h3>                            
                </div>
                @endif
                
                <!-- Card body -->
                <div class="grid md:grid-cols-2 gap-6">
                    <!-- Left column: Form fields -->
                    <div class="space-y-4">
                        <!-- Show matching event if found for this specific event -->
                        <div v-if="preview.parsed[idx].event_url" class="p-3 text-sm bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-200 rounded-md">
                            {{ __('messages.similar_event_found') }} - 
                            <a :href="preview.parsed[idx].event_url" 
                                target="_blank" 
                                class="underline hover:text-yellow-600 dark:hover:text-yellow-300">
                                {{ __('messages.view_event') }}
                            </a>
                        </div>
                        
                        <div>
                            <x-input-label for="name_@{{ idx }}" :value="__('messages.event_name')" />
                            <x-text-input id="name_@{{ idx }}" 
                                name="name_@{{ idx }}" 
                                type="text" 
                                class="mt-1 block w-full" 
                                v-model="preview.parsed[idx].event_name"
                                v-bind:readonly="savedEvents[idx]"
                                required />
                        </div>

                        <div>
                            <x-input-label for="venue_address1_@{{ idx }}" :value="__('messages.address')" />
                            <x-text-input id="venue_address1_@{{ idx }}" 
                                name="venue_address1_@{{ idx }}" 
                                type="text" 
                                class="mt-1 block w-full" 
                                v-model="preview.parsed[idx].event_address"
                                v-bind:readonly="preview.parsed[idx].venue_id || savedEvents[idx]"
                                placeholder="{{ $role->isCurator() ? $role->city : '' }}"
                                required
                                autocomplete="off" />
                        </div>

                        <div>
                            <label for="starts_at_@{{ idx }}" class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                                {{ __('messages.date_and_time') }}
                            </label>
                            <input id="starts_at_@{{ idx }}" 
                                    name="starts_at_@{{ idx }}" 
                                    type="text" 
                                    :class="'mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm datepicker_' + idx"
                                    v-bind:readonly="savedEvents[idx]"
                                    v-model="preview.parsed[idx].event_date_time"
                                    required 
                                    autocomplete="off" />
                        </div>

                        <!-- Account creation checkbox for guest users -->
                        @if (isset($isGuest) && $isGuest && ! auth()->check())
                        <div class="flex items-center">
                            <input type="checkbox" 
                                    id="create_account_@{{ idx }}" 
                                    name="create_account_@{{ idx }}" 
                                    v-model="createAccount"
                                    class="rounded border-gray-300 text-blue-500 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                            <label for="create_account_@{{ idx }}" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('messages.create_account') }}
                            </label>
                        </div>
                        
                        <div v-if="createAccount" class="space-y-4">
                            <div>
                                <x-input-label for="name_@{{ idx }}" :value="__('messages.name')" />
                                <x-text-input id="name_@{{ idx }}" 
                                    name="name_@{{ idx }}" 
                                    type="text" 
                                    class="mt-1 block w-full" 
                                    v-model="userName"
                                    required />
                            </div>
                            
                            <div>
                                <x-input-label for="email_@{{ idx }}" :value="__('messages.email')" />
                                <x-text-input id="email_@{{ idx }}" 
                                    name="email_@{{ idx }}" 
                                    type="email" 
                                    class="mt-1 block w-full" 
                                    v-model="userEmail"
                                    required />
                            </div>
                            
                            <div>
                                <x-input-label for="password_@{{ idx }}" :value="__('messages.password')" />
                                <x-text-input id="password_@{{ idx }}" 
                                    name="password_@{{ idx }}" 
                                    type="password" 
                                    class="mt-1 block w-full" 
                                    v-model="userPassword"
                                    required />
                            </div>
                        </div>
                        @endif

                        <!-- Add buttons at the bottom of the left column -->
                        <div class="mt-12 flex justify-end gap-2">
                            <template v-if="savedEvents[idx]">
                                <button v-if="!savedEventData[idx]?.is_curated && !{{ isset($isGuest) && $isGuest ? 'true' : 'false' }}" @click="handleEdit(idx)" type="button" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-colors">
                                    {{ __('messages.edit') }}
                                </button>
                                <button v-if="{{ auth()->check() ? 'true' : 'false' }}" @click="handleView(idx)" type="button" class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 transition-colors">
                                    {{ __('messages.view') }}
                                </button>
                            </template>
                            <template v-else>
                                <button @click="handleRemoveEvent(idx)" v-if="preview.parsed.length > 1" type="button" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                                    {{ __('messages.remove') }}
                                </button>
                                <button @click="handleClear" type="button" v-if="preview.parsed.length == 1" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                                    {{ __('messages.clear') }}
                                </button>
                                <button @click="handleSave(idx)" 
                                        type="button" 
                                        :disabled="savingEvents[idx] || !canCreateAccount"
                                        :class="['px-4 py-2 rounded-md transition-colors', 
                                            (savingEvents[idx] || !canCreateAccount)
                                                ? 'bg-gray-400 text-gray-200 cursor-not-allowed' 
                                                : 'bg-blue-500 text-white hover:bg-blue-600']">
                                    <span v-if="savingEvents[idx]" class="inline-flex items-center">
                                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        {{ __('messages.saving') }}
                                    </span>
                                    <span v-else>{{ __('messages.save') }}</span>
                                </button>
                                <!--
                                <button v-if="isCurator && preview.parsed[idx].event_url && !preview.parsed[idx].is_curated && !{{ isset($isGuest) && $isGuest ? 'true' : 'false' }}" 
                                        @click="handleCurate(idx)" 
                                        type="button" 
                                        class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 transition-colors">
                                    {{ __('messages.curate') }}
                                </button>
                                -->
                            </template>
                        </div>
                        


                        <!-- JSON preview with border matching textarea -->
                        <div v-if="showAllFields && !{{ isset($isGuest) && $isGuest ? 'true' : 'false' }}" class="mt-4 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm overflow-auto bg-gray-50 dark:bg-gray-900">
                            <pre class="p-4 text-xs text-gray-800 dark:text-gray-200">@{{ JSON.stringify(preview.parsed[idx], null, 2) }}</pre>
                        </div>
                        

                    </div>
                    
                    <!-- Right column: Image -->
                    <div class="flex flex-col">
                        <div class="relative h-full flex flex-col">
                            <!-- Image preview -->
                            <div v-if="preview.parsed[idx].social_image" 
                                    class="flex-grow rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-800">
                                <img v-bind:src="getSocialImageUrl(preview.parsed[idx].social_image)" 
                                        class="object-contain w-full h-full" 
                                        alt="Event preview image">
                                
                                <!-- Remove image button -->
                                <button v-if="!isLoading"
                                        @click="removeImage(idx)" 
                                        type="button"
                                        v-bind:disabled="savedEvents[idx]"
                                        class="absolute top-2 right-2 p-1 bg-red-500 text-white rounded-full hover:bg-red-600 focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Drop zone -->
                            <div v-else-if="!savedEvents[idx]"
                                    @dragover.prevent="dragOver"
                                    @dragleave.prevent="dragLeave"
                                    @drop.prevent="(e) => handleDrop(e, idx)"
                                    @click="() => openFileSelector(idx)"
                                    v-bind:class="['flex-grow flex items-center justify-center rounded-lg border-2 border-dashed cursor-pointer', 
                                            isDragging ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30' : 'border-gray-300 dark:border-gray-600']">
                                <div class="text-center py-10">
                                    <!-- Show loading spinner when uploading -->
                                    <template v-if="isUploadingImage === idx">
                                        <div class="relative mx-auto w-12 h-12">
                                            <div class="w-12 h-12 rounded-full bg-blue-500/30"></div>
                                            <div class="absolute top-0 left-0 w-12 h-12 rounded-full border-4 border-blue-500 border-t-transparent animate-spin"></div>
                                        </div>
                                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                            {{ __('messages.uploading') }}...
                                        </p>
                                    </template>
                                    <!-- Default upload icon and text -->
                                    <template v-else>
                                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                            {{ __('messages.drag_drop_image') }}
                                        </p>

                                    </template>
                                </div>
                            </div>

                            <!-- Hidden file input -->
                            <input type="file" 
                                    v-bind:ref="'fileInput_' + idx"
                                    @change="(e) => handleFileSelect(e, idx)"
                                    accept="image/*"
                                    class="hidden">
                        </div>
                    </div>
                </div>

                <!-- YouTube Videos Section for Talent - Now below the form and image -->
                <div v-if="preview.parsed[idx].performers && preview.parsed[idx].performers.length > 0" class="mt-6">
                    <div v-for="(performer, performerIdx) in preview.parsed[idx].performers" :key="performerIdx" class="my-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        <!-- Only show YouTube video selection for the first performer when there are multiple performers -->
                        <div v-if="performerIdx === 0 && ! performer.talent_id">

                            <!-- Loading state -->
                            <div v-if="performer.searching" class="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400">
                                <div class="animate-spin rounded-full h-3 w-3 border-b-2 border-blue-500"></div>
                                <span>{{ __('messages.searching_youtube') }}</span>
                            </div>

                            <!-- Videos grid - Now in two columns if there's room -->
                            <div v-else-if="performer.videos && performer.videos.length > 0" class="space-y-3">
                                <div class="text-xs text-gray-600 dark:text-gray-400 mb-2">
                                    {{ __('messages.results_for') }} "@{{ performer.name }}"
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 -mx-3 p-3">
                                    <div v-for="video in performer.videos.slice(0, 6)" :key="video.id" 
                                            class="border rounded-lg p-2 cursor-pointer hover:border-blue-300 transition-colors relative"
                                            :class="isVideoSelected(idx, performerIdx, video) ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30' : 'border-gray-200 dark:border-gray-600'"
                                            @click="selectVideo(idx, performerIdx, video)">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-16 h-12 bg-gray-100 dark:bg-gray-700 rounded flex items-center justify-center relative flex-shrink-0">
                                                <img v-if="video.thumbnail" :src="video.thumbnail" :alt="video.title" class="w-full h-full object-cover rounded">
                                                <svg v-else class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                            
                                            <div class="flex-1 min-w-0">
                                                <h6 class="text-xs font-medium text-gray-900 dark:text-gray-100 truncate">@{{ video.title }}</h6>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">@{{ video.channelTitle }}</p>
                                                <div class="flex items-center space-x-3 text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                    <div class="flex items-center space-x-1">
                                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                                        </svg>
                                                        <span>@{{ formatNumber(video.viewCount) }}</span>
                                                    </div>
                                                    <div class="flex items-center space-x-1">
                                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                                                        </svg>
                                                        <span>@{{ formatNumber(video.likeCount) }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Watch button -->
                                            <a :href="video.url" target="_blank" 
                                                class="inline-flex items-center text-xs text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 font-medium transition-colors flex-shrink-0"
                                                @click.stop>
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                                                </svg>
                                                Watch
                                            </a>
                                        </div>
                                        
                                        <!-- Selection indicator -->
                                        <div v-if="isVideoSelected(idx, performerIdx, video)" class="absolute top-2 right-2">
                                            <div class="bg-blue-500 text-white rounded-full w-5 h-5 flex items-center justify-center">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Error state -->
                            @if (auth()->user() && auth()->user()->isAdmin())
                            <div v-else-if="performer.error" class="text-xs text-red-600 dark:text-red-400">
                                @{{ performer.error }}
                            </div>
                            @endif

                        </div>
                    </div>
                </div>
            </div>
        </div>

</form>

<script {!! nonce_attr() !!}>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize flatpickr for any existing datepickers on page load
        initializeFlatpickr();
    });

    // Function to initialize flatpickr on datepicker elements
    function initializeFlatpickr() {
        // Select all elements with datepicker_X class
        document.querySelectorAll('[class*="datepicker_"]').forEach(element => {
            // Destroy existing flatpickr instance if it exists
            if (element._flatpickr) {
                element._flatpickr.destroy();
            }
            
            // Create new flatpickr instance with EXACT same configuration as edit.blade.php
            var f = flatpickr(element, {
                allowInput: true,
                enableTime: true,
                altInput: true,
                time_24hr: "{{ $role && $role->use_24_hour_time ? 'true' : 'false' }}",
                altFormat: "{{ $role && $role->use_24_hour_time ? 'M j, Y • H:i' : 'M j, Y • h:i K' }}",
                dateFormat: "Y-m-d H:i:S",
            });
            
            // Prevent keyboard input as per edit view
            if (f && f._input) {
                f._input.onkeydown = () => false;
            }
        });
    }

    const { createApp } = Vue



    createApp({
        data() {
            return {
                eventDetails: '',
                preview: null,
                isLoading: false,
                isUploadingImage: null,
                isUploadingDetailsImage: false,
                errorMessage: null,
                savedEvents: [],
                savedEventData: [],
                saveErrors: [],
                isDragging: false,
                isDraggingDetails: false,
                dragCounter: 0,
                dragTimeout: null,
                dragStartTime: 0,
                isDragActive: false,
                showAllFields: false,
                isCurator: {{ isset($isGuest) && $isGuest ? 'false' : ($role->isCurator() ? 'true' : 'false') }},
                detailsImage: null,
                detailsImageUrl: null,
                currentRequestId: null,
                savingEvents: [], // Track which events are currently being saved
                createAccount: false, // New data property for guest user account creation
                userName: '',
                userEmail: '',
                userPassword: '',
            }
        },

        mounted() {
            // Component is mounted and ready
            // Show the form now that Vue.js has loaded
            this.$nextTick(() => {
                document.getElementById('event-import-app').classList.add('loaded');
            });
        },

        computed: {
            canSubmit() {
                return this.eventDetails.trim() || this.detailsImage;
            },
            
            canCreateAccount() {
                // Always check event fields regardless of createAccount status
                const eventFieldsValid = this.preview?.parsed?.every(event => {
                    const hasName = event.event_name?.trim();
                    const hasDate = event.event_date_time;
                    
                    // For address validation: if it's empty but there's a placeholder (city), consider it valid
                    const hasAddress = event.event_address?.trim() || 
                                     ({{ $role->isCurator() ? 'true' : 'false' }} && '{{ $role->city }}');
                    
                    return hasName && hasAddress && hasDate;
                }) || false;
                
                // If createAccount is checked, also validate user fields
                if (this.createAccount) {
                    // Email validation regex
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    const isEmailValid = emailRegex.test(this.userEmail.trim());
                    
                    return eventFieldsValid && 
                           this.userName.trim() && 
                           this.userEmail.trim() && 
                           isEmailValid &&
                           this.userPassword;
                }
                
                // If createAccount is not checked, only validate event fields
                return eventFieldsValid;
            }
        },

                    created() {
                this.loadShowAllFieldsPreference()
                
                // Add global drag event listeners
                document.addEventListener('dragend', this.handleGlobalDragEnd)
                document.addEventListener('dragstart', this.handleGlobalDragStart)
                
                // Add mouse move listener to track when we're actually outside the drop zone
                document.addEventListener('mousemove', this.handleMouseMove)
            },
        
        beforeUnmount() {
            // Clean up event listeners when component is destroyed
            document.removeEventListener('dragend', this.handleGlobalDragEnd)
            document.removeEventListener('dragstart', this.handleGlobalDragStart)
            document.removeEventListener('mousemove', this.handleMouseMove)
            
            // Clear any pending timeouts
            if (this.dragTimeout) {
                clearTimeout(this.dragTimeout);
                this.dragTimeout = null;
            }
        },

        updated() {
            this.$nextTick(() => {
                // Call the global function to initialize flatpickr
                initializeFlatpickr();
            });
        },

        methods: {
            handleKeydown(event) {
                // Handle Enter key for form submission
                if (event.key === 'Enter') {
                    if (event.shiftKey) {
                        // Shift+Enter: allow default behavior (new line)
                        return;
                    } else {
                        // Enter: submit the form
                        event.preventDefault();
                        this.handleSubmit();
                    }
                }
            },

            handleInputChange() {
                // Just update the model, don't auto-submit
                // The submit button will be enabled/disabled based on canSubmit computed property
            },

            handleSubmit() {
                if (this.canSubmit) {
                    this.fetchPreview();
                }
            },

            async fetchPreview() {
                if (!this.eventDetails.trim() && !this.detailsImage) {
                    this.preview = null;
                    return;
                }

                this.isLoading = true;
                this.preview = null;
                this.errorMessage = null;
                this.savedEvents = [];
                this.savedEventData = [];
                this.saveErrors = [];
                
                // Create a unique request ID to track the latest request
                const requestId = Date.now();
                this.currentRequestId = requestId;
                
                // Don't clear preview immediately - we'll only update it if this is still the latest request
                // when the response comes back
                
                try {
                    const formData = new FormData();
                    formData.append('event_details', this.eventDetails);
                    if (this.detailsImage) {
                        formData.append('details_image', this.detailsImage);
                    }

                    const response = await fetch('{{ isset($isGuest) && $isGuest ? route("event.guest_parse", ["subdomain" => $role->subdomain]) : route("event.parse", ["subdomain" => $role->subdomain]) }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: formData
                    });

                    // If this is no longer the latest request, ignore the response
                    if (this.currentRequestId !== requestId) {
                        return;
                    }

                    // Handle HTTP error responses before trying to parse JSON
                    if (!response.ok) {
                        if (response.status === 405) {
                            throw new Error('Invalid request method');
                        }
                        if (response.status === 404) {
                            throw new Error('Resource not found');
                        }
                        if (response.status === 403) {
                            throw new Error('Permission denied');
                        }
                        if (response.status === 401) {
                            throw new Error('Unauthorized');
                        }
                        if (response.status === 500) {
                            throw new Error('Server error');
                        }
                    }

                    let data;
                    try {
                        data = await response.json();
                    } catch (e) {
                        throw new Error('Invalid response from server');
                    }

                    if (!response.ok) {
                        // Handle validation errors
                        if (data.errors) {
                            const errorMessages = Object.values(data.errors).flat();
                            throw new Error(errorMessages.join('\n'));
                        }
                        // Handle other types of errors
                        throw new Error(data.message || data.error || 'An unexpected error occurred');
                    }

                    // Ensure preview.parsed is always an array            
                    // TODO: remove this, it shouldn't be needed
                    if (data && data.parsed && !Array.isArray(data.parsed)) {
                        data.parsed = [data.parsed];
                    } else if (data && Array.isArray(data)) {
                        data = { parsed: data };
                    }

                    // For guest users, only show the first event if multiple are parsed
                    if ({{ isset($isGuest) && $isGuest ? 'true' : 'false' }} && data.parsed && data.parsed.length > 1) {
                        data.parsed = [data.parsed[0]];
                    }

                    // Now that we have valid data and this is still the latest request, update the preview
                    this.preview = data;
                    
                    // Initialize arrays to track saved events and errors
                    if (Array.isArray(this.preview.parsed)) {
                        this.savedEvents = new Array(this.preview.parsed.length).fill(false);
                        this.savedEventData = new Array(this.preview.parsed.length).fill(null);
                        this.saveErrors = new Array(this.preview.parsed.length).fill(false);
                        this.savingEvents = new Array(this.preview.parsed.length).fill(false);
                        
                    // Initialize video properties for performers and automatically search for videos
                    this.preview.parsed.forEach((event, eventIdx) => {
                        if (event.performers && Array.isArray(event.performers)) {
                            event.performers.forEach((performer, performerIdx) => {
                                // Initialize video-related properties using Object.assign for reactivity
                                Object.assign(performer, {
                                    videos: null,
                                    selectedVideos: [], // Will contain at most one video
                                    searching: false,
                                    error: null
                                });
                                
                                // Only search for videos for the first performer when there are multiple performers
                                if (performerIdx === 0 && ! performer.talent_id) {
                                    this.$nextTick(() => {
                                        this.searchVideos(eventIdx, performerIdx);
                                    });
                                }
                            });
                        }
                    });
                    }
                    
                    // Initialize datepickers after preview is loaded
                    this.$nextTick(() => {
                        initializeFlatpickr();
                    });
                } catch (error) {
                    // Only show error if this is still the latest request
                    if (this.currentRequestId === requestId) {
                        console.error('Error fetching preview:', error)
                        this.errorMessage = error.message || 'An error occurred while fetching the preview';
                    }
                } finally {
                    // Only update loading state if this is still the latest request
                    if (this.currentRequestId === requestId) {
                        this.isLoading = false;
                    }
                }
            },
            
            handlePaste(event) {

                // If no image data, handle as text paste
                event.preventDefault();
                // Get the pasted text
                const pastedText = event.clipboardData.getData('text');
                // Update the model manually
                this.eventDetails = pastedText;
                // Don't auto-submit - user must click the submit button
            },

            handleEdit(idx) {
                // For guest users, don't allow editing events
                if ({{ isset($isGuest) && $isGuest ? 'true' : 'false' }}) {
                    return;
                }
                
                if (this.savedEvents[idx] && this.savedEventData[idx]) {
                    window.open(this.savedEventData[idx].edit_url, '_blank');
                }
            },

            handleView(idx) {
                if (this.savedEvents[idx] && this.savedEventData[idx]) {
                    if ({{ isset($isGuest) && $isGuest ? 'true' : 'false' }}) {
                        // For guest users, redirect to the view URL
                        window.location.href = this.savedEventData[idx].view_url;
                    } else {
                        // For authenticated users, open in new tab
                        window.open(this.savedEventData[idx].view_url, '_blank');
                    }
                }
            },

            async handleSave(idx) {
                this.errorMessage = null;
                // Reset error state for this event
                this.saveErrors[idx] = false;
                // Set saving state for this event
                this.savingEvents[idx] = true;
                
                try {
                    // Get data from the Vue model
                    if (!this.preview?.parsed?.[idx]) {
                        throw new Error('Event data not found');
                    }
                    
                    const parsed = this.preview.parsed[idx];
                    
                    let dateValue = null; // Declare dateValue variable here                        
                    let dateElement = document.querySelector(`.datepicker_${idx}`);
                    dateValue = dateElement.value;
                    
                    // Ensure the date has seconds
                    if (dateValue && dateValue.length === 16) { // Format: "YYYY-MM-DD HH:MM"
                        dateValue += ":00"; // Add seconds
                    }
                    
                    if (!dateValue) {
                        throw new Error('Date and time are required');
                    }
                    
                    // Prepare members data
                    const members = {};
                    
                    if (parsed.performers && parsed.performers.length > 0) {
                        parsed.performers.forEach((performer, index) => {
                            const memberData = {
                                name: performer.name,
                                name_en: performer.name_en || '',
                                email: performer.email || '',
                                website: performer.website || '',
                                language_code: '{{ $role->language_code }}',
                            };
                            
                            // Add selected videos if any
                            if (performer.selectedVideos && performer.selectedVideos.length > 0) {
                                memberData.youtube_url = performer.selectedVideos[0].url; // Only send one video URL
                            }
                            
                            members[`new_talent_${index}`] = memberData;
                        });
                    } else if (parsed.talent_id) {
                        members[parsed.talent_id] = {
                            name: parsed.performer_name,
                            name_en: parsed.performer_name_en || '',
                            email: parsed.performer_email || '',
                            youtube_url: parsed.performer_youtube_url || '',
                            language_code: '{{ $role->language_code }}',
                        };
                    }
                    
                    // Get venue address from VueJS model
                    const venueAddress = parsed.event_address || "{{ $role->isCurator() ? $role->city : '' }}";

                    // Get event name from VueJS model 
                    const eventName = parsed.event_name;
                    
                    // Send request to server
                    const response = await fetch('{{ isset($isGuest) && $isGuest ? route("event.guest_import", ["subdomain" => $role->subdomain]) : route("event.import", ["subdomain" => $role->subdomain]) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            venue_name: parsed.venue_name,
                            venue_name_en: parsed.venue_name_en,
                            venue_address1: venueAddress,
                            venue_address1_en: parsed.venue_address1_en,
                            venue_city: parsed.event_city,
                            venue_city_en: parsed.event_city_en,
                            venue_state: parsed.event_state,
                            venue_state_en: parsed.event_state_en,
                            venue_postal_code: parsed.event_postal_code,
                            venue_country_code: parsed.event_country_code,
                            venue_id: parsed.venue_id,
                            venue_language_code: '{{ $role->language_code }}',
                            members: members,
                            name: eventName,
                            name_en: parsed.event_name_en,
                            starts_at: dateValue,
                            duration: parsed.event_duration,
                            description: this.eventDetails ? this.eventDetails : parsed.event_details,
                            social_image: parsed.social_image,
                            registration_url: parsed.registration_url,
                            @if ($role->isCurator() && !isset($isGuest))
                                curators: ['{{ \App\Utils\UrlUtils::encodeId($role->id) }}'],
                            @endif
                            // User creation data for guest users
                            ...({{ isset($isGuest) && $isGuest ? 'true' : 'false' }} && this.createAccount ? {
                                create_account: true,
                                name: this.userName,
                                email: this.userEmail,
                                password: this.userPassword
                            } : {})
                        })
                    });
                    
                    // Handle response
                    if (!response.ok) {
                        const errorData = await response.json();
                        throw new Error(errorData.message || 'Failed to save event');
                    }
                    
                    const data = await response.json();
                    
                    // Store the response data in savedEventData array
                    this.savedEvents[idx] = true;
                    this.savedEventData[idx] = data.event; // Store the event object with view_url and edit_url
                    
                    // For guest users, automatically redirect to view the event
                    if ({{ isset($isGuest) && $isGuest ? 'true' : 'false' }} && data.event.view_url) {
                        window.location.href = data.event.view_url;
                    } else {
                        // Show success message for non-guest users
                        Toastify({
                            text: '{{ __("messages.event_created") }}',
                            duration: 3000,
                            position: 'center',
                            stopOnFocus: true,
                            style: {
                                background: '#4BB543',
                            }
                        }).showToast();
                    }
                    
                } catch (error) {
                    console.error('Error saving event:', error);
                    this.errorMessage = error.message;
                    // Set error state for this event
                    this.saveErrors[idx] = error.message || 'An error occurred while saving the event';
                } finally {
                    // Clear saving state for this event
                    this.savingEvents[idx] = false;
                }
            },

            getYouTubeEmbedUrl(url) {
                // Extract video ID from various YouTube URL formats
                const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
                const match = url.match(regExp);
                const videoId = match && match[2].length === 11 ? match[2] : null;
                
                return videoId ? `https://www.youtube.com/embed/${videoId}` : '';
            },

            getSocialImageUrl(path) {
                // Extract filename from /tmp/event_XXXXX.jpg path
                const filename = path.split('/').pop();
                return `{{ route('event.tmp_image', ['filename' => '']) }}/${filename}`;
            },

            handleClear() {
                this.eventDetails = '';
                this.detailsImage = null;
                this.detailsImageUrl = null;
                this.preview = null;
                this.savedEvents = [];
                this.savedEventData = [];
                this.savingEvents = [];
                this.errorMessage = null;
                // Clear user creation fields
                this.createAccount = false;
                this.userName = '';
                this.userEmail = '';
                this.userPassword = '';
                this.$nextTick(() => {
                    document.getElementById('event_details').focus();
                });
            },

            dragOver(e) {
                this.isDragging = true
            },

            dragLeave(e) {
                this.isDragging = false
            },

            async handleDrop(e, idx) {
                this.isDragging = false
                const files = e.dataTransfer.files
                if (files.length > 0) {
                    await this.uploadImage(files[0], idx)
                }
            },

            openFileSelector(idx) {
                const fileInput = this.$refs[`fileInput_${idx}`];
                if (fileInput) {
                    if (Array.isArray(fileInput)) {
                        fileInput[0].click();
                    } else {
                        fileInput.click();
                    }
                }
            },

            async handleFileSelect(e, idx) {
                const files = e.target.files
                if (files.length > 0) {
                    await this.uploadImage(files[0], idx)
                }
            },

            async uploadImage(file, idx) {
                if (!file.type.startsWith('image/')) {
                    this.errorMessage = '{{ __("messages.invalid_image_type") }}'
                    return
                }

                // Check file size (2.5 MB = 2.5 * 1024 * 1024 bytes)
                const maxSize = 2.5 * 1024 * 1024;
                if (file.size > maxSize) {
                    this.errorMessage = '{{ __("messages.image_size_warning") }}';
                    return;
                }

                this.isUploadingImage = idx;
                
                try {
                    // Create a FormData object to send the file
                    const formData = new FormData();
                    formData.append('image', file);
                    
                    // Upload the image to get a temporary URL
                    const response = await fetch('{{ isset($isGuest) && $isGuest ? route("event.guest_upload_image", ["subdomain" => $role->subdomain]) : route("event.upload_image", ["subdomain" => $role->subdomain]) }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    if (data.success && data.filename) {
                        // Update the social_image property for the specific event
                        this.preview.parsed[idx].social_image = data.filename;
                    } else {
                        throw new Error(data.message || '{{ __("messages.error_uploading_image") }}');
                    }
                } catch (error) {
                    console.error('Error uploading image:', error);
                    this.errorMessage = error.message || '{{ __("messages.error_uploading_image") }}';
                } finally {
                    this.isUploadingImage = null;
                }
            },

            removeImage(idx) {
                if (this.preview && this.preview.parsed && this.preview.parsed[idx]) {
                    this.preview.parsed[idx].social_image = null;
                }
            },

            saveShowAllFieldsPreference() {
                localStorage.setItem('event_import_show_all_fields', this.showAllFields)
            },

            loadShowAllFieldsPreference() {
                const savedPreference = localStorage.getItem('event_import_show_all_fields')
                if (savedPreference !== null) {
                    this.showAllFields = savedPreference === 'true'
                }
            },

            handleRemoveEvent(idx) {
                // For guest users, don't allow removing events since they only see one
                if ({{ isset($isGuest) && $isGuest ? 'true' : 'false' }}) {
                    return;
                }
                
                if (confirm('{{ __("messages.confirm_remove_event") }}')) {
                    // Remove the event from the parsed array
                    this.preview.parsed.splice(idx, 1);
                    // Remove the corresponding entry in savedEvents array
                    this.savedEvents.splice(idx, 1);
                    this.savedEventData.splice(idx, 1);
                    // Remove the corresponding entry in savingEvents array
                    this.savingEvents.splice(idx, 1);
                    
                    // If no events left, clear the preview
                    if (this.preview.parsed.length === 0) {
                        this.preview = null;
                    }
                    
                    // Re-initialize datepickers after removing an event
                    this.$nextTick(() => {
                        initializeFlatpickr();
                    });
                    
                    // Show success message
                    Toastify({
                        text: '{{ __("messages.event_removed") }}',
                        duration: 3000,
                        position: 'center',
                        stopOnFocus: true,
                        style: {
                            background: '#4BB543',
                        }
                    }).showToast();
                }
            },

            async handleCurate(idx) {
                // For guest users, don't allow curating events
                if ({{ isset($isGuest) && $isGuest ? 'true' : 'false' }}) {
                    return;
                }
                
                // Reset error state for this event
                this.saveErrors[idx] = false;
                // Set saving state for this event
                this.savingEvents[idx] = true;
                
                if (!this.preview?.parsed?.[idx]?.event_url) {
                    return;
                }

                const hash = this.preview.parsed[idx].event_id;

                try {
                    const url = @json(route('event.curate', ['subdomain' => $role->subdomain, 'hash' => '--hash--'])).replace('--hash--', hash);
                    const response = await fetch(url, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    const data = await response.json();
                    
                    if (data.success) {
                        this.savedEvents[idx] = true;
                        this.savedEventData[idx] = {
                            view_url: data.event_url || this.preview.parsed[idx].event_url,
                            is_curated: true
                        };
                        
                        Toastify({
                            text: '{{ __("messages.curate_event") }}',
                            duration: 3000,
                            position: 'center',
                            stopOnFocus: true,
                            style: {
                                background: '#4BB543',
                            }
                        }).showToast();
                    } else {
                        throw new Error(data.message || '{{ __("messages.error_curating_event") }}');
                    }
                } catch (error) {
                    console.error('Error curating event:', error);
                    this.errorMessage = error.message || '{{ __("messages.error_curating_event") }}';
                    // Set error state for this event
                    this.saveErrors[idx] = error.message || '{{ __("messages.error_curating_event") }}';
                } finally {
                    // Clear saving state for this event
                    this.savingEvents[idx] = false;
                }
            },

            async handleSaveAll() {
                // For guest users, don't allow save all since they only see one event
                if ({{ isset($isGuest) && $isGuest ? 'true' : 'false' }}) {
                    return;
                }
                
                // Check if there are any events to save
                if (!this.preview?.parsed || this.preview.parsed.length === 0) {
                    return;
                }
                
                // Prevent multiple clicks by disabling the button
                const saveAllButton = event.target;
                if (saveAllButton) {
                    saveAllButton.disabled = true;
                    saveAllButton.classList.add('opacity-50', 'cursor-not-allowed');
                }
                
                let successCount = 0;
                let errorCount = 0;
                let skippedCount = 0;
                
                // Set saving state for all events that will be processed
                for (let idx = 0; idx < this.preview.parsed.length; idx++) {
                    if (!this.savedEvents[idx] && !this.preview.parsed[idx].event_url) {
                        this.savingEvents[idx] = true;
                    }
                }
                
                // Loop through all events
                for (let idx = 0; idx < this.preview.parsed.length; idx++) {
                    // Skip already saved events
                    if (this.savedEvents[idx]) {
                        skippedCount++;
                        continue;
                    }
                    
                    // Skip events that have a matching one (indicated by event_url)
                    if (this.preview.parsed[idx].event_url) {
                        skippedCount++;
                        continue;
                    }
                    
                    try {
                        // If event has a curate button and is not already curated, curate it
                        if (this.isCurator && 
                            this.preview.parsed[idx].event_url && 
                            !this.preview.parsed[idx].is_curated &&
                            !{{ isset($isGuest) && $isGuest ? 'true' : 'false' }}) {
                            await this.handleCurate(idx);
                        } else {
                            // Otherwise save it normally
                            await this.handleSave(idx);
                        }
                        
                        // Check if the operation was successful
                        if (this.savedEvents[idx]) {
                            successCount++;
                        } else if (this.saveErrors[idx]) {
                            errorCount++;
                        }
                        
                        // Add a small delay between saves to prevent overwhelming the server
                        await new Promise(resolve => setTimeout(resolve, 500));
                    } catch (error) {
                        errorCount++;
                        console.error('Error processing event ' + idx + ':', error);
                    }
                }
                
                // Show appropriate message after all events are processed
                let message = '';
                if (errorCount === 0 && skippedCount === 0) {
                    message = '{{ __("messages.all_events_processed") }}';
                } else {
                    message = `{{ __("messages.events_processed_with_errors") }}`.replace('{success}', successCount).replace('{errors}', errorCount);
                    if (skippedCount > 0) {
                        message += ` ({{ __("messages.events_skipped") }}`.replace('{skipped}', skippedCount) + ')';
                    }
                }

                Toastify({
                    text: message,
                    duration: 3000,
                    position: 'center',
                    stopOnFocus: true,
                    style: {
                        background: errorCount > 0 && successCount === 0 ? '#FF0000' : 
                                    skippedCount > 0 && successCount === 0 ? '#FF9800' : '#4BB543',
                    }
                }).showToast();
                
                // Re-enable the button after processing is complete
                if (saveAllButton) {
                    saveAllButton.disabled = false;
                    saveAllButton.classList.remove('opacity-50', 'cursor-not-allowed');
                }
                
                // Clear saving state for all events
                for (let idx = 0; idx < this.preview.parsed.length; idx++) {
                    this.savingEvents[idx] = false;
                }
            },



            dragOverDetails(e) {
                e.preventDefault();
                // Don't change state here, just prevent default
            },

            dragEnterDetails(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Only handle drag enter on the main textarea
                if (e.target === this.$refs.eventDetails) {
                    // Clear any existing timeout when re-entering
                    if (this.dragTimeout) {
                        clearTimeout(this.dragTimeout);
                        this.dragTimeout = null;
                    }
                    this.isDraggingDetails = true;
                    this.isDragActive = true;
                }
            },

            dragLeaveDetails(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Don't immediately hide - let the timeout handle it
                // This prevents flickering when moving over child elements
            },

            dragOverDetails(e) {
                e.preventDefault();
                // Keep the drop zone visible while dragging over
                if (!this.isDraggingDetails) {
                    this.isDraggingDetails = true;
                }
            },

            dragEndDetails(e) {
                // Reset drag state when drag operation ends
                this.resetDragState();
            },

            handleGlobalDragEnd(e) {
                // Reset drag state when any drag operation ends globally
                this.resetDragState();
            },

            resetDragState() {
                this.isDraggingDetails = false;
                this.isDragActive = false;
                this.dragStartTime = 0;
                if (this.dragTimeout) {
                    clearTimeout(this.dragTimeout);
                    this.dragTimeout = null;
                }
            },

            handleGlobalDragStart(e) {
                // Track when a global drag operation starts
                this.isDragActive = true;
            },

            handleMouseMove(e) {
                // Only track mouse movement if we're in a drag operation
                if (!this.isDragActive || !this.isDraggingDetails) {
                    return;
                }

                // Check if mouse is outside the drop zone with some tolerance
                const dropZone = this.$refs.eventDetails;
                if (dropZone) {
                    const rect = dropZone.getBoundingClientRect();
                    const tolerance = 10; // 10px tolerance to prevent edge flickering
                    const isOutside = e.clientX < (rect.left - tolerance) || 
                                    e.clientX > (rect.right + tolerance) || 
                                    e.clientY < (rect.top - tolerance) || 
                                    e.clientY > (rect.bottom + tolerance);
                    
                    if (isOutside) {
                        // Use a longer delay to prevent flickering
                        if (this.dragTimeout) {
                            clearTimeout(this.dragTimeout);
                        }
                        this.dragTimeout = setTimeout(() => {
                            // Double-check that we're still outside before hiding
                            const currentRect = dropZone.getBoundingClientRect();
                            const stillOutside = e.clientX < (currentRect.left - tolerance) || 
                                              e.clientX > (currentRect.right + tolerance) || 
                                              e.clientY < (currentRect.top - tolerance) || 
                                              e.clientY > (currentRect.bottom + tolerance);
                            
                            if (stillOutside) {
                                this.isDraggingDetails = false;
                            }
                        }, 300); // Increased delay for more stability
                    }
                }
            },

            openDetailsFileSelector() {
                this.$refs.detailsFileInput.click();
            },

            async handleDetailsFileSelect(e) {
                const files = e.target.files;
                if (files.length > 0) {
                    await this.uploadDetailsImage(files[0]);
                }
                
                // Reset the file input to allow selecting the same file again
                e.target.value = '';
            },

            async handleDetailsImageDrop(e) {
                this.resetDragState();
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    await this.uploadDetailsImage(files[0]);
                }
            },

            async uploadDetailsImage(file) {
                if (!file.type.startsWith('image/')) {
                    this.errorMessage = '{{ __("messages.invalid_image_type") }}';
                    return;
                }

                // Check file size (2.5 MB = 2.5 * 1024 * 1024 bytes)
                const maxSize = 2.5 * 1024 * 1024;
                if (file.size > maxSize) {
                    this.errorMessage = '{{ __("messages.image_size_warning") }}';
                    return;
                }

                this.isUploadingDetailsImage = true;
                
                try {
                    this.detailsImage = file;
                    
                    // Use FileReader to create a data URL for preview
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.detailsImageUrl = e.target.result; // This will be a data URL
                    };
                    reader.readAsDataURL(file);
                    
                    // Clear any previous error messages
                    this.errorMessage = null;
                    
                    // Don't auto-submit - user must click the submit button
                } catch (error) {
                    console.error('Error uploading details image:', error);
                    this.errorMessage = error.message || '{{ __("messages.error_uploading_image") }}';
                    // Reset the image state on error
                    this.detailsImage = null;
                    this.detailsImageUrl = null;
                } finally {
                    this.isUploadingDetailsImage = false;
                }
            },

            removeDetailsImage() {
                // No need to revoke anything with data URLs
                this.detailsImage = null;
                this.detailsImageUrl = null;
                this.errorMessage = null; // Clear any error messages when removing the image
                
                // Reset the file input to allow selecting the same file again
                if (this.$refs.detailsFileInput) {
                    this.$refs.detailsFileInput.value = '';
                }
                
                // Don't auto-submit - user must click the submit button
            },

            getDetailsImageUrl() {
                if (!this.detailsImage) return '';
                
                try {
                    // Create a new URL object each time to avoid caching issues
                    return URL.createObjectURL(this.detailsImage);
                } catch (e) {
                    console.error('Error creating object URL:', e);
                    return '';
                }
            },

            // YouTube video search methods
            formatNumber(num) {
                if (!num) return '0';
                if (num >= 1000000) {
                    return (num / 1000000).toFixed(1) + 'M';
                } else if (num >= 1000) {
                    return (num / 1000).toFixed(1) + 'K';
                }
                return num.toString();
            },

            async searchVideos(eventIdx, performerIdx) {
                const event = this.preview.parsed[eventIdx];
                const performer = event.performers[performerIdx];
                
                if (!performer || !performer.name) return;
                
                // Initialize performer properties if they don't exist
                if (!performer.videos) {
                    Object.assign(performer, { videos: [] });
                }
                if (!performer.selectedVideos) {
                    Object.assign(performer, { selectedVideos: [] }); // Will contain at most one video
                }
                
                performer.searching = true;
                performer.error = null;
                
                try {
                    const endpoint = {{ isset($isGuest) && $isGuest ? 'true' : 'false' }} 
                        ? `{{ route('role.guest_search_youtube', ['subdomain' => $role->subdomain]) }}`
                        : `{{ route('role.search_youtube', ['subdomain' => $role->subdomain]) }}`;
                    
                    
                    const response = await fetch(`${endpoint}?q=${encodeURIComponent(performer.name)}`);                    
                    const data = await response.json();
                    
                    if (data.success && data.videos) {
                        performer.videos = data.videos;
                        // Don't auto-select videos - let user choose
                        performer.selectedVideos = []; // Reset to empty array
                    } else {
                        performer.error = data.message || '{{ __("messages.no_videos_found") }}';
                    }
                } catch (error) {
                    performer.error = '{{ __("messages.error_searching_videos") }}';
                    console.error('Error searching videos:', error);
                } finally {
                    performer.searching = false;
                }
            },

            isVideoSelected(eventIdx, performerIdx, video) {
                const event = this.preview.parsed[eventIdx];
                const performer = event.performers[performerIdx];
                return performer && performer.selectedVideos && performer.selectedVideos.length > 0 && performer.selectedVideos[0].id === video.id;
            },

            selectVideo(eventIdx, performerIdx, video) {
                const event = this.preview.parsed[eventIdx];
                const performer = event.performers[performerIdx];
                
                if (!performer) return;
                
                if (!performer.selectedVideos) {
                    Object.assign(performer, { selectedVideos: [] });
                }
                
                const index = performer.selectedVideos.findIndex(v => v.id === video.id);
                if (index > -1) {
                    // Remove video if already selected
                    performer.selectedVideos.splice(index, 1);
                } else {
                    // Replace any existing video with the new one (only allow one)
                    performer.selectedVideos = [video];
                }
            },
        }
    }).mount('#event-import-app')
</script>