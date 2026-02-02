<x-app-admin-layout>

<x-slot name="head">
    <script src="{{ asset('js/vue.global.prod.js') }}"></script>
</x-slot>

<div class="flex justify-between items-center gap-6 pb-6">
    @if (is_rtl())
        <div class="flex items-center gap-3">
            <button onclick="history.back()" type="button" class="inline-flex items-center justify-center rounded-md bg-gray-200 dark:bg-gray-700 px-5 py-3 text-base font-semibold text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                {{ __('messages.back') }}
            </button>
        </div>

        <div class="flex items-center text-right">
            @if ($role->profile_image_url)
                <div class="pe-4">
                    <img src="{{ $role->profile_image_url }}" class="rounded-lg h-14 w-14 flex-none">
                </div>
            @endif
            <h2 class="text-xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:truncate sm:text-2xl sm:tracking-tight">
                {{ __('messages.scan_agenda') }}
            </h2>
        </div>
    @else
        <div class="flex items-center">
            @if ($role->profile_image_url)
                <div class="pe-4">
                    <img src="{{ $role->profile_image_url }}" class="rounded-lg h-14 w-14 flex-none">
                </div>
            @endif
            <h2 class="text-xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:truncate sm:text-2xl sm:tracking-tight">
                {{ __('messages.scan_agenda') }}
            </h2>
        </div>

        <div class="flex items-center gap-3">
            <button onclick="history.back()" type="button" class="inline-flex items-center justify-center rounded-md bg-gray-200 dark:bg-gray-700 px-5 py-3 text-base font-semibold text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                {{ __('messages.back') }}
            </button>
        </div>
    @endif
</div>

<div id="scan-agenda-app">
    <scan-agenda-app></scan-agenda-app>
</div>

<script {!! nonce_attr() !!}>
document.addEventListener('DOMContentLoaded', function() {
    const { createApp, ref, computed, nextTick } = Vue;

    const app = createApp({
        setup() {
            const state = ref('camera'); // camera, loading, editing, success
            const events = ref(@json($eventsData));
            const selectedEventId = ref(@json($selectedEventId));
            if (!selectedEventId.value && events.value.length > 0) {
                selectedEventId.value = events.value[0].id;
            }
            const aiPrompt = ref(@json($aiPrompt));
            const saveAsDefault = ref(false);
            const editingPrompt = ref(false);
            const showAllFields = ref(localStorage.getItem('scanAgendaShowAllFields') === '1');
            const parts = ref([]);
            const errorMessage = ref('');
            const successEditUrl = ref('');
            const cameraError = ref('');
            const videoEl = ref(null);
            const canvasEl = ref(null);
            const cameras = ref([]);
            const selectedCameraId = ref('');
            const cameraStarted = ref(false);
            const selectCameraLabel = '{{ __("messages.select_camera") }}';
            let stream = null;

            const selectedEventName = computed(() => {
                const ev = events.value.find(e => e.id === selectedEventId.value);
                return ev ? ev.name + (ev.starts_at ? ' - ' + ev.starts_at : '') : '';
            });

            const hasEvents = computed(() => events.value.length > 0);

            function requestCameraAccess() {
                cameraError.value = '';
                navigator.mediaDevices.getUserMedia({ video: true })
                    .then(tempStream => {
                        tempStream.getTracks().forEach(t => t.stop());
                        return navigator.mediaDevices.enumerateDevices();
                    })
                    .then(devices => {
                        cameras.value = devices.filter(d => d.kind === 'videoinput');
                        if (cameras.value.length === 0) {
                            cameraError.value = '{{ __("messages.camera_error") }}';
                            return;
                        }
                        const savedCameraId = localStorage.getItem('scanAgendaSelectedCamera');
                        const savedCamera = savedCameraId ? cameras.value.find(c => c.deviceId === savedCameraId) : null;
                        const rear = cameras.value.find(c => c.label.toLowerCase().includes('back') || c.label.toLowerCase().includes('rear') || c.label.toLowerCase().includes('environment'));
                        const chosen = savedCamera || rear || cameras.value[0];
                        selectedCameraId.value = chosen.deviceId;
                        startCamera(chosen.deviceId);
                        localStorage.setItem('scanAgendaCameraGranted', '1');
                    })
                    .catch(err => {
                        console.error('Camera error:', err);
                        cameraError.value = '{{ __("messages.camera_error") }}';
                        localStorage.removeItem('scanAgendaCameraGranted');
                    });
            }

            function startCamera(deviceId) {
                cameraError.value = '';
                const constraints = deviceId
                    ? { video: { deviceId: { exact: deviceId } } }
                    : { video: { facingMode: 'environment' } };
                navigator.mediaDevices.getUserMedia(constraints).then(s => {
                    stream = s;
                    cameraStarted.value = true;
                    nextTick(() => {
                        if (videoEl.value) {
                            videoEl.value.srcObject = stream;
                        }
                    });
                }).catch(err => {
                    console.error('Camera error:', err);
                    cameraError.value = '{{ __("messages.camera_error") }}';
                });
            }

            function switchCamera(deviceId) {
                selectedCameraId.value = deviceId;
                localStorage.setItem('scanAgendaSelectedCamera', deviceId);
                stopCamera();
                startCamera(deviceId);
            }

            function stopCamera() {
                if (stream) {
                    stream.getTracks().forEach(t => t.stop());
                    stream = null;
                }
            }

            function captureAndParse() {
                if (!selectedEventId.value) return;

                const video = videoEl.value;
                const canvas = canvasEl.value;
                if (!video || !canvas) return;

                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                canvas.getContext('2d').drawImage(video, 0, 0);

                stopCamera();
                state.value = 'loading';
                errorMessage.value = '';

                canvas.toBlob(function(blob) {
                    const formData = new FormData();
                    formData.append('parts_image', blob, 'agenda.jpg');
                    if (aiPrompt.value) {
                        formData.append('ai_prompt', aiPrompt.value);
                    }
                    if (saveAsDefault.value) {
                        formData.append('save_ai_prompt_default', '1');
                    }
                    formData.append('event_id', selectedEventId.value);

                    fetch('{{ route("event.parse_parts", ["subdomain" => $subdomain]) }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: formData,
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.error) {
                            errorMessage.value = data.error;
                            state.value = 'camera';
                            cameraStarted.value = false;
                            return;
                        }
                        parts.value = (Array.isArray(data) ? data : []).map(p => ({
                            name: p.name || '',
                            description: p.description || '',
                            start_time: p.start_time || '',
                            end_time: p.end_time || '',
                        }));
                        if (parts.value.length === 0) {
                            errorMessage.value = '{{ __("messages.no_events_found") }}';
                            state.value = 'camera';
                            cameraStarted.value = false;
                            return;
                        }
                        state.value = 'editing';
                    })
                    .catch(err => {
                        console.error('Parse error:', err);
                        errorMessage.value = '{{ __("messages.event_parsing_failed") }}';
                        state.value = 'camera';
                        cameraStarted.value = false;
                    });
                }, 'image/jpeg', 0.85);
            }

            function addPart() {
                parts.value.push({ name: '', description: '', start_time: '', end_time: '' });
            }

            function removePart(index) {
                parts.value.splice(index, 1);
            }

            const dragIndex = ref(null);

            function onDragStart(index) {
                dragIndex.value = index;
            }

            function onDragOver(index, event) {
                event.preventDefault();
            }

            function onDrop(index) {
                if (dragIndex.value === null || dragIndex.value === index) return;
                const item = parts.value.splice(dragIndex.value, 1)[0];
                parts.value.splice(index, 0, item);
                dragIndex.value = null;
            }

            function onDragEnd() {
                dragIndex.value = null;
            }

            function toggleShowAllFields() {
                showAllFields.value = !showAllFields.value;
                localStorage.setItem('scanAgendaShowAllFields', showAllFields.value ? '1' : '0');
            }

            function saveParts() {
                if (parts.value.length === 0) return;

                state.value = 'loading';
                errorMessage.value = '';

                const partsToSend = showAllFields.value
                    ? parts.value
                    : parts.value.map(p => ({ name: p.name, description: '', start_time: '', end_time: '' }));

                fetch('{{ route("event.save_parts", ["subdomain" => $subdomain]) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        event_id: selectedEventId.value,
                        parts: partsToSend,
                    }),
                })
                .then(r => r.json())
                .then(data => {
                    if (data.error) {
                        errorMessage.value = data.error;
                        state.value = 'editing';
                        return;
                    }
                    successEditUrl.value = data.edit_url;
                    state.value = 'success';
                })
                .catch(err => {
                    console.error('Save error:', err);
                    errorMessage.value = '{{ __("messages.an_error_occurred") }}';
                    state.value = 'editing';
                });
            }

            function cancelEditing() {
                parts.value = [];
                state.value = 'camera';
                cameraStarted.value = false;
            }

            function scanAnother() {
                parts.value = [];
                successEditUrl.value = '';
                state.value = 'camera';
                cameraStarted.value = false;
            }

            function onEventChange(e) {
                selectedEventId.value = e.target.value;
            }

            // Auto-start camera if permission was previously granted
            if (localStorage.getItem('scanAgendaCameraGranted')) {
                requestCameraAccess();
            }

            return {
                state, events, selectedEventId, aiPrompt, saveAsDefault,
                editingPrompt, showAllFields,
                parts, errorMessage, successEditUrl, cameraError,
                videoEl, canvasEl, selectedEventName, hasEvents,
                cameras, selectedCameraId, cameraStarted, selectCameraLabel,
                dragIndex, onDragStart, onDragOver, onDrop, onDragEnd,
                requestCameraAccess, startCamera, switchCamera,
                captureAndParse, addPart, removePart,
                saveParts, cancelEditing, scanAnother, onEventChange,
                toggleShowAllFields,
            };
        },
        template: `
<div class="max-w-2xl mx-auto">
    <!-- No events message -->
    <div v-if="!hasEvents" class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <p class="mt-4 text-gray-500 dark:text-gray-400">{{ __('messages.no_suitable_events') }}</p>
    </div>

    <div v-else>
        <!-- Event selector -->
        <div class="mb-4">
            <select :value="selectedEventId" @change="onEventChange" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-[#4E81FA] focus:ring-[#4E81FA]">
                <option v-for="event in events" :key="event.id" :value="event.id">
                    @{{ event.name }} <span v-if="event.starts_at">- @{{ event.starts_at }}</span>
                </option>
            </select>
        </div>

        <!-- Error message -->
        <div v-if="errorMessage" class="mb-4 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md text-red-700 dark:text-red-400 text-sm">
            @{{ errorMessage }}
        </div>

        <!-- Camera view -->
        <div v-if="state === 'camera'">
            <div v-if="cameraError" class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <p class="mt-4 text-gray-500 dark:text-gray-400">@{{ cameraError }}</p>
            </div>
            <div v-else-if="!cameraStarted" class="text-center py-12">
                <button @click="requestCameraAccess" class="inline-flex items-center gap-2 px-6 py-3 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-sm text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-[#4E81FA] focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    {{ __('messages.start_camera') }}
                </button>
            </div>
            <div v-else class="relative">
                <div v-if="cameras.length > 1" class="mb-2">
                    <select :value="selectedCameraId" @change="switchCamera($event.target.value)" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-[#4E81FA] focus:ring-[#4E81FA] text-sm">
                        <option v-for="cam in cameras" :key="cam.deviceId" :value="cam.deviceId">
                            @{{ cam.label || selectCameraLabel }}
                        </option>
                    </select>
                </div>
                <video ref="videoEl" autoplay playsinline class="w-full rounded-lg bg-black"></video>
                <canvas ref="canvasEl" class="hidden"></canvas>
                <div class="absolute bottom-4 left-0 right-0 flex justify-center">
                    <button @click="captureAndParse" :disabled="!selectedEventId"
                        class="w-16 h-16 bg-white rounded-full border-4 border-gray-300 shadow-lg hover:bg-gray-100 focus:outline-none focus:ring-4 focus:ring-[#4E81FA] disabled:opacity-50 transition-colors">
                        <span class="sr-only">{{ __('messages.capture_photo') }}</span>
                    </button>
                </div>
            </div>

            <!-- AI Prompt (compact) -->
            <div class="mt-4">
                <div v-if="!editingPrompt" class="flex items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            <span v-if="aiPrompt">@{{ aiPrompt }}</span>
                            <span v-else class="italic">{{ __('messages.no_prompt_set') }}</span>
                        </p>
                    </div>
                    <button @click="editingPrompt = true" class="text-sm font-medium text-[#4E81FA] hover:text-[#3a6de0] whitespace-nowrap border border-[#4E81FA] rounded-md px-3 py-1">{{ __('messages.edit') }}</button>
                </div>
                <div v-else>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('messages.ai_agenda_prompt') }}</label>
                    <textarea v-model="aiPrompt" rows="2" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-[#4E81FA] focus:ring-[#4E81FA] text-sm" placeholder="{{ __('messages.ai_agenda_prompt_placeholder') }}"></textarea>
                    <div class="flex items-center justify-between mt-2">
                        <label class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                            <input type="checkbox" v-model="saveAsDefault" class="rounded border-gray-300 dark:border-gray-600 text-[#4E81FA] shadow-sm focus:ring-[#4E81FA] me-2">
                            {{ __('messages.save_as_default') }}
                        </label>
                        <button @click="editingPrompt = false" class="text-sm font-medium text-[#4E81FA] hover:text-[#3a6de0] whitespace-nowrap border border-[#4E81FA] rounded-md px-3 py-1">{{ __('messages.done') }}</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading state -->
        <div v-if="state === 'loading'" class="text-center py-16">
            <svg class="animate-spin mx-auto h-10 w-10 text-[#4E81FA]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <p class="mt-4 text-gray-500 dark:text-gray-400">{{ __('messages.parsing_image') }}</p>
        </div>

        <!-- Editing parts -->
        <div v-if="state === 'editing'">
            <div :class="showAllFields ? 'space-y-3' : 'space-y-1'" class="mb-24">
                <!-- Compact layout (name only) -->
                <template v-if="!showAllFields">
                    <div v-for="(part, index) in parts" :key="'compact-' + index"
                        draggable="true"
                        @dragstart="onDragStart(index)"
                        @dragover="onDragOver(index, $event)"
                        @drop="onDrop(index)"
                        @dragend="onDragEnd"
                        :class="{ 'opacity-50': dragIndex === index }"
                        class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-2 shadow-sm">
                        <div class="flex items-center gap-2">
                            <div class="cursor-grab text-gray-400 dark:text-gray-500">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 16 16">
                                    <circle cx="5.5" cy="3.5" r="1.5"/>
                                    <circle cx="10.5" cy="3.5" r="1.5"/>
                                    <circle cx="5.5" cy="8" r="1.5"/>
                                    <circle cx="10.5" cy="8" r="1.5"/>
                                    <circle cx="5.5" cy="12.5" r="1.5"/>
                                    <circle cx="10.5" cy="12.5" r="1.5"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <input v-model="part.name" type="text" placeholder="{{ __('messages.part_name') }}" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-[#4E81FA] focus:ring-[#4E81FA] text-sm font-medium">
                            </div>
                            <button @click="removePart(index)" class="rounded-md border border-red-300 dark:border-red-700 p-1.5 text-red-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors" title="{{ __('messages.remove') }}">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>
                </template>

                <!-- Full layout (all fields) -->
                <template v-else>
                    <div v-for="(part, index) in parts" :key="'full-' + index"
                        draggable="true"
                        @dragstart="onDragStart(index)"
                        @dragover="onDragOver(index, $event)"
                        @drop="onDrop(index)"
                        @dragend="onDragEnd"
                        :class="{ 'opacity-50': dragIndex === index }"
                        class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 shadow-sm">
                        <div class="flex items-start gap-2">
                            <div class="cursor-grab text-gray-400 dark:text-gray-500 pt-2">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 16 16">
                                    <circle cx="5.5" cy="3.5" r="1.5"/>
                                    <circle cx="10.5" cy="3.5" r="1.5"/>
                                    <circle cx="5.5" cy="8" r="1.5"/>
                                    <circle cx="10.5" cy="8" r="1.5"/>
                                    <circle cx="5.5" cy="12.5" r="1.5"/>
                                    <circle cx="10.5" cy="12.5" r="1.5"/>
                                </svg>
                            </div>
                            <div class="flex-1 space-y-2">
                                <input v-model="part.name" type="text" placeholder="{{ __('messages.part_name') }}" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-[#4E81FA] focus:ring-[#4E81FA] text-sm font-medium">
                                <textarea v-model="part.description" rows="2" placeholder="{{ __('messages.description') }}" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-[#4E81FA] focus:ring-[#4E81FA] text-sm"></textarea>
                                <div class="flex gap-2">
                                    <input v-model="part.start_time" type="text" placeholder="{{ __('messages.start_time') }}" class="w-1/2 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-[#4E81FA] focus:ring-[#4E81FA] text-sm">
                                    <input v-model="part.end_time" type="text" placeholder="{{ __('messages.end_time') }}" class="w-1/2 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-[#4E81FA] focus:ring-[#4E81FA] text-sm">
                                </div>
                            </div>
                            <button @click="removePart(index)" class="rounded-md border border-red-300 dark:border-red-700 p-1.5 text-red-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors" title="{{ __('messages.remove') }}">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>
                </template>

                <button @click="addPart" class="w-full py-2 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-500 dark:text-gray-400 hover:border-gray-400 dark:hover:border-gray-500 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                    + {{ __('messages.add_part') }}
                </button>

                <label class="flex items-center text-sm text-gray-600 dark:text-gray-400 pt-2">
                    <input type="checkbox" :checked="showAllFields" @change="toggleShowAllFields" class="rounded border-gray-300 dark:border-gray-600 text-[#4E81FA] shadow-sm focus:ring-[#4E81FA] me-2">
                    {{ __('messages.show_all_fields') }}
                </label>
            </div>

            <!-- Floating save/cancel bar -->
            <div class="fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 p-4 z-40 shadow-lg" style="padding-bottom: max(1rem, env(safe-area-inset-bottom));">
                <div class="flex gap-3 justify-center max-w-lg mx-auto">
                    <button @click="saveParts" class="inline-flex items-center justify-center px-6 py-3 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-sm text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-[#4E81FA] focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                        {{ __('messages.save') }}
                    </button>
                    <button @click="cancelEditing" class="inline-flex items-center justify-center px-6 py-3 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-sm text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-[#4E81FA] focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                        {{ __('messages.retake') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Success state -->
        <div v-if="state === 'success'" class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <p class="mt-4 text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('messages.agenda_saved') }}</p>
            <div class="mt-6 flex flex-col gap-3 items-center">
                <a :href="successEditUrl" class="inline-flex items-center justify-center px-6 py-3 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-sm text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white transition ease-in-out duration-150">
                    {{ __('messages.edit_event') }}
                </a>
                <button @click="scanAnother" class="inline-flex items-center justify-center px-6 py-3 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-sm text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition ease-in-out duration-150">
                    {{ __('messages.scan_agenda') }}
                </button>
            </div>
        </div>
    </div>
</div>
`
    });

    app.mount('#scan-agenda-app');
});
</script>

</x-app-admin-layout>
