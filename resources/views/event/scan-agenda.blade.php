<x-app-admin-layout>

<x-slot name="head">
    <script src="{{ asset('js/vue.global.prod.js') }}" {!! nonce_attr() !!}></script>
</x-slot>

<div class="flex justify-between items-center gap-6 pb-6">
    @if (is_rtl())
        <div class="flex items-center gap-3">
            <button type="button" class="js-back-btn inline-flex items-center justify-center rounded-md bg-gray-200 dark:bg-gray-700 px-5 py-3 text-base font-semibold text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                {{ __('messages.back') }}
            </button>
        </div>

        <div class="flex items-center text-end">
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
            <button type="button" class="js-back-btn inline-flex items-center justify-center rounded-md bg-gray-200 dark:bg-gray-700 px-5 py-3 text-base font-semibold text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                {{ __('messages.back') }}
            </button>
        </div>
    @endif
</div>

<script {!! nonce_attr() !!}>
    document.addEventListener('click', function(e) {
        if (e.target.closest('.js-back-btn')) {
            history.back();
        }
    });
</script>

<div id="scan-agenda-app">
    <scan-agenda-app></scan-agenda-app>
</div>

<script {!! nonce_attr() !!}>
document.addEventListener('DOMContentLoaded', function() {
    const { createApp, ref, computed, nextTick } = Vue;

    const app = createApp({
        setup() {
            const state = ref('camera'); // camera, loading, editing
            const events = ref(@json($eventsData));
            const selectedEventId = ref(@json($selectedEventId));
            if (!selectedEventId.value && events.value.length > 0) {
                selectedEventId.value = events.value[0].id;
            }
            const aiPrompt = ref(@json($aiPrompt));
            const saveAsDefault = ref(false);
            const editingPrompt = ref(false);
            const showTimes = ref({{ ($role->agenda_show_times ?? true) ? 'true' : 'false' }});
            const showDescription = ref({{ ($role->agenda_show_description ?? true) ? 'true' : 'false' }});
            const parts = ref([]);
            const errorMessage = ref('');

            const cameraError = ref('');
            const cameraErrorType = ref(''); // 'permission', 'not_found', 'in_use', 'unknown'
            const isMobile = ref(/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent));
            const videoEl = ref(null);
            const canvasEl = ref(null);
            const cameras = ref([]);
            const selectedCameraId = ref('');
            const cameraStarted = ref(false);
            const showCameraModal = ref(false);
            const selectCameraLabel = '{{ __("messages.select_camera") }}';
            let stream = null;

            const selectedCameraLabel = computed(() => {
                const cam = cameras.value.find(c => c.deviceId === selectedCameraId.value);
                return cam ? (cam.label || selectCameraLabel) : '';
            });

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
                        localStorage.setItem('scanAgendaCameraGranted', '1');
                        if (cameras.value.length === 1) {
                            selectedCameraId.value = cameras.value[0].deviceId;
                            startCamera(cameras.value[0].deviceId);
                        } else if (savedCamera) {
                            selectedCameraId.value = savedCamera.deviceId;
                            startCamera(savedCamera.deviceId);
                        } else {
                            showCameraModal.value = true;
                        }
                    })
                    .catch(err => {
                        console.error('Camera error:', err);
                        handleCameraError(err);
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
                    handleCameraError(err);
                });
            }

            function handleCameraError(err) {
                const errorName = err.name || '';
                if (errorName === 'NotAllowedError' || errorName === 'PermissionDeniedError') {
                    cameraErrorType.value = 'permission';
                    cameraError.value = '{{ __("messages.camera_need_access") }}';
                } else if (errorName === 'NotFoundError' || errorName === 'DevicesNotFoundError') {
                    cameraErrorType.value = 'not_found';
                    cameraError.value = '{{ __("messages.camera_not_found") }}';
                } else if (errorName === 'NotReadableError' || errorName === 'TrackStartError') {
                    cameraErrorType.value = 'in_use';
                    cameraError.value = '{{ __("messages.camera_in_use") }}';
                } else {
                    cameraErrorType.value = 'unknown';
                    cameraError.value = '{{ __("messages.camera_something_wrong") }}';
                }
            }

            function retryCameraAccess() {
                cameraError.value = '';
                cameraErrorType.value = '';
                requestCameraAccess();
            }

            function switchCamera(deviceId) {
                selectedCameraId.value = deviceId;
                localStorage.setItem('scanAgendaSelectedCamera', deviceId);
                stopCamera();
                startCamera(deviceId);
            }

            function selectCameraFromModal(deviceId) {
                showCameraModal.value = false;
                if (deviceId === selectedCameraId.value) return;
                selectedCameraId.value = deviceId;
                localStorage.setItem('scanAgendaSelectedCamera', deviceId);
                stopCamera();
                startCamera(deviceId);
            }

            function changeCamera() {
                showCameraModal.value = true;
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
            const dropTargetIndex = ref(null);

            function onDragStart(index) {
                dragIndex.value = index;
            }

            function onDragOver(index, event) {
                event.preventDefault();
                if (dragIndex.value === null) return;
                const rect = event.currentTarget.getBoundingClientRect();
                const midpoint = rect.top + rect.height / 2;
                const target = event.clientY < midpoint ? index : index + 1;
                if (target === dragIndex.value || target === dragIndex.value + 1) {
                    dropTargetIndex.value = null;
                } else {
                    dropTargetIndex.value = target;
                }
            }

            function onDrop() {
                if (dragIndex.value === null || dropTargetIndex.value === null) {
                    dragIndex.value = null;
                    dropTargetIndex.value = null;
                    return;
                }
                const item = parts.value.splice(dragIndex.value, 1)[0];
                const insertAt = dropTargetIndex.value > dragIndex.value ? dropTargetIndex.value - 1 : dropTargetIndex.value;
                parts.value.splice(insertAt, 0, item);
                dragIndex.value = null;
                dropTargetIndex.value = null;
            }

            function onDragEnd() {
                dragIndex.value = null;
                dropTargetIndex.value = null;
            }

            function onContainerDragOver(event) {
                if (dragIndex.value === null || parts.value.length === 0) return;
                const container = event.currentTarget;
                const firstChild = container.children[0];
                if (firstChild) {
                    const rect = firstChild.getBoundingClientRect();
                    if (event.clientY < rect.top + rect.height / 2) {
                        if (0 !== dragIndex.value && 0 !== dragIndex.value + 1) {
                            dropTargetIndex.value = 0;
                        } else {
                            dropTargetIndex.value = null;
                        }
                    }
                }
            }

            function saveParts() {
                if (parts.value.length === 0) return;

                state.value = 'loading';
                errorMessage.value = '';

                const partsToSend = parts.value.map(p => ({
                    name: p.name,
                    description: showDescription.value ? p.description : '',
                    start_time: showTimes.value ? p.start_time : '',
                    end_time: showTimes.value ? p.end_time : ''
                }));

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
                    if (data.view_url) {
                        window.location.href = data.view_url + '#agenda';
                    } else {
                        window.location.href = data.edit_url;
                    }
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
                state.value = 'camera';
                cameraStarted.value = false;
            }

            const dropdownOpen = ref(false);

            function toggleDropdown() {
                dropdownOpen.value = !dropdownOpen.value;
            }

            function closeDropdown() {
                dropdownOpen.value = false;
            }

            function onEventChange(eventId) {
                selectedEventId.value = eventId;
                closeDropdown();
            }

            function handleClickOutside(e) {
                const el = document.getElementById('event-dropdown');
                if (el && !el.contains(e.target)) {
                    closeDropdown();
                }
            }

            function handleEscape(e) {
                if (e.key === 'Escape') {
                    closeDropdown();
                }
            }

            document.addEventListener('click', handleClickOutside);
            document.addEventListener('keydown', handleEscape);

            const selectedEvent = computed(() => {
                return events.value.find(e => e.id === selectedEventId.value) || null;
            });

            // Auto-start camera if permission was previously granted
            if (localStorage.getItem('scanAgendaCameraGranted')) {
                requestCameraAccess();
            }

            return {
                state, events, selectedEventId, selectedEvent, aiPrompt, saveAsDefault,
                editingPrompt, showTimes, showDescription,
                parts, errorMessage, cameraError, cameraErrorType, isMobile,
                videoEl, canvasEl, selectedEventName, hasEvents,
                cameras, selectedCameraId, cameraStarted, selectCameraLabel,
                showCameraModal, selectedCameraLabel,
                dragIndex, dropTargetIndex, onDragStart, onDragOver, onDrop, onDragEnd, onContainerDragOver,
                dropdownOpen, toggleDropdown, closeDropdown,
                requestCameraAccess, startCamera, switchCamera, retryCameraAccess,
                captureAndParse, addPart, removePart,
                saveParts, cancelEditing, scanAnother, onEventChange,
                selectCameraFromModal, changeCamera,
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
        <div class="mb-4 relative" id="event-dropdown">
            <button @click="toggleDropdown" type="button" tabindex="0"
                class="w-full flex items-center gap-3 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 shadow-sm focus:border-[#4E81FA] focus:ring-2 focus:ring-[#4E81FA] focus:outline-none text-start">
                <template v-if="selectedEvent">
                    <img v-if="selectedEvent.image_url" :src="selectedEvent.image_url" class="w-10 h-10 rounded object-cover flex-shrink-0">
                    <span v-else class="w-10 h-10 rounded bg-gray-100 dark:bg-gray-600 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </span>
                    <span class="flex-1 min-w-0">
                        <span class="block truncate text-gray-900 dark:text-gray-100 text-sm font-medium">@{{ selectedEvent.name }}</span>
                        <span v-if="selectedEvent.starts_at" class="block truncate text-gray-500 dark:text-gray-400 text-xs">@{{ selectedEvent.starts_at }}</span>
                    </span>
                </template>
                <svg class="w-5 h-5 text-gray-400 flex-shrink-0 ms-auto" fill="none" viewBox="0 0 20 20">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 8l4 4 4-4" />
                </svg>
            </button>
            <div v-if="dropdownOpen" class="absolute z-50 mt-1 w-full rounded-md border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 shadow-lg max-h-72 overflow-y-auto">
                <button v-for="event in events" :key="event.id" @click="onEventChange(event.id)" type="button"
                    class="w-full flex items-center gap-3 px-3 py-2 text-start hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors"
                    :class="event.id === selectedEventId ? 'bg-gray-50 dark:bg-gray-600/50' : ''">
                    <img v-if="event.image_url" :src="event.image_url" class="w-10 h-10 rounded object-cover flex-shrink-0">
                    <span v-else class="w-10 h-10 rounded bg-gray-100 dark:bg-gray-600 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </span>
                    <span class="flex-1 min-w-0">
                        <span class="block truncate text-gray-900 dark:text-gray-100 text-sm font-medium">@{{ event.name }}</span>
                        <span v-if="event.starts_at" class="block truncate text-gray-500 dark:text-gray-400 text-xs">@{{ event.starts_at }}</span>
                    </span>
                    <svg v-if="event.id === selectedEventId" class="w-5 h-5 text-[#4E81FA] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Error message -->
        <div v-if="errorMessage" class="mb-4 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md text-red-700 dark:text-red-400 text-sm">
            @{{ errorMessage }}
        </div>

        <!-- Camera view -->
        <div v-if="state === 'camera'">
            <!-- Camera Error Card -->
            <div v-if="cameraError" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm">
                <div class="text-center">
                    <!-- Camera icon with X overlay -->
                    <div class="relative inline-block mb-4">
                        <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                            <svg class="w-8 h-8 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div style="width: 20px; height: 20px; min-width: 20px; min-height: 20px;" class="absolute -bottom-1 -right-1 bg-red-500 rounded-full flex items-center justify-center">
                            <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </div>
                    </div>

                    <!-- Heading -->
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">@{{ cameraError }}</h3>

                    <!-- Help text based on error type -->
                    <p v-if="cameraErrorType === 'permission'" class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        {{ __('messages.camera_need_access_help') }}
                    </p>
                    <p v-else-if="cameraErrorType === 'not_found'" class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        {{ __('messages.camera_not_found_help') }}
                    </p>
                    <p v-else-if="cameraErrorType === 'in_use'" class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        {{ __('messages.camera_in_use_help') }}
                    </p>
                    <p v-else class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        {{ __('messages.camera_something_wrong_help') }}
                    </p>
                </div>

                <!-- Fix steps (only for permission error) -->
                <div v-if="cameraErrorType === 'permission'" class="mt-6 space-y-0">
                    <!-- Desktop instructions -->
                    <div v-if="!isMobile" class="divide-y divide-gray-200 dark:divide-gray-700 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                        <div class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-700/50">
                            <span class="flex-shrink-0 w-6 h-6 bg-[#4E81FA] text-white rounded-full flex items-center justify-center text-xs font-bold">1</span>
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('messages.camera_fix_step1') }}</span>
                        </div>
                        <div class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-700/50">
                            <span class="flex-shrink-0 w-6 h-6 bg-[#4E81FA] text-white rounded-full flex items-center justify-center text-xs font-bold">2</span>
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('messages.camera_fix_step2') }}</span>
                        </div>
                        <div class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-700/50">
                            <span class="flex-shrink-0 w-6 h-6 bg-[#4E81FA] text-white rounded-full flex items-center justify-center text-xs font-bold">3</span>
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('messages.camera_fix_step3') }}</span>
                        </div>
                    </div>
                    <!-- Mobile instructions -->
                    <div v-else class="divide-y divide-gray-200 dark:divide-gray-700 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                        <div class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-700/50">
                            <span class="flex-shrink-0 w-6 h-6 bg-[#4E81FA] text-white rounded-full flex items-center justify-center text-xs font-bold">1</span>
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('messages.camera_fix_step1_mobile') }}</span>
                        </div>
                        <div class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-700/50">
                            <span class="flex-shrink-0 w-6 h-6 bg-[#4E81FA] text-white rounded-full flex items-center justify-center text-xs font-bold">2</span>
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('messages.camera_fix_step2_mobile') }}</span>
                        </div>
                        <div class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-700/50">
                            <span class="flex-shrink-0 w-6 h-6 bg-[#4E81FA] text-white rounded-full flex items-center justify-center text-xs font-bold">3</span>
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('messages.camera_fix_step3_mobile') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Try Again button -->
                <div class="mt-6 text-center">
                    <button @click="retryCameraAccess" class="inline-flex items-center gap-2 px-6 py-3 bg-[#4E81FA] hover:bg-[#3a6de0] border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-[#4E81FA] focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        {{ __('messages.camera_try_again') }}
                    </button>
                </div>
            </div>
            <div v-else-if="!cameraStarted" class="text-center py-12">
                <button v-if="!showCameraModal" @click="requestCameraAccess" class="inline-flex items-center gap-2 px-6 py-3 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-sm text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-[#4E81FA] focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    {{ __('messages.start_camera') }}
                </button>

                <!-- Camera selection modal (before camera starts) -->
                <div v-if="showCameraModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" @click.self="showCameraModal = false">
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl mx-4 w-full max-w-sm overflow-hidden">
                        <div class="px-5 pt-5 pb-3">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('messages.select_camera') }}</h3>
                        </div>
                        <div class="px-3 pb-4">
                            <button v-for="cam in cameras" :key="cam.deviceId" @click="selectCameraFromModal(cam.deviceId)"
                                class="w-full flex items-center gap-3 px-3 py-3 text-start rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                <svg class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span class="flex-1 text-sm text-gray-900 dark:text-gray-100">@{{ cam.label || selectCameraLabel }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else class="relative">
                <video ref="videoEl" autoplay playsinline class="w-full min-h-[40vh] object-cover rounded-lg bg-black"></video>
                <canvas ref="canvasEl" class="hidden"></canvas>
                <div class="absolute bottom-4 left-0 right-0 flex justify-center">
                    <button @click="captureAndParse" :disabled="!selectedEventId"
                        class="w-16 h-16 bg-white rounded-full border-4 border-gray-300 shadow-lg hover:bg-gray-100 focus:outline-none focus:ring-4 focus:ring-[#4E81FA] disabled:opacity-50 transition-colors">
                        <span class="sr-only">{{ __('messages.capture_photo') }}</span>
                    </button>
                </div>

                <!-- Camera selection modal -->
                <div v-if="showCameraModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" @click.self="showCameraModal = false">
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl mx-4 w-full max-w-sm overflow-hidden">
                        <div class="px-5 pt-5 pb-3">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('messages.select_camera') }}</h3>
                        </div>
                        <div class="px-3 pb-4">
                            <button v-for="cam in cameras" :key="cam.deviceId" @click="selectCameraFromModal(cam.deviceId)"
                                class="w-full flex items-center gap-3 px-3 py-3 text-start rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                :class="cam.deviceId === selectedCameraId ? 'bg-gray-50 dark:bg-gray-700/50' : ''">
                                <svg class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span class="flex-1 text-sm text-gray-900 dark:text-gray-100">@{{ cam.label || selectCameraLabel }}</span>
                                <svg v-if="cam.deviceId === selectedCameraId" class="w-5 h-5 text-[#4E81FA] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Buttons row below preview -->
            <div v-if="cameraStarted" class="mt-3 grid gap-2" :class="cameras.length > 1 ? 'grid-cols-2' : 'grid-cols-1'">
                <button @click="editingPrompt = !editingPrompt" class="text-sm font-medium text-[#4E81FA] hover:text-[#3a6de0] border border-[#4E81FA] rounded-md px-3 py-2">
                    {{ __('messages.edit_prompt') }}
                </button>
                <button v-if="cameras.length > 1" @click="changeCamera" class="text-sm font-medium text-[#4E81FA] hover:text-[#3a6de0] border border-[#4E81FA] rounded-md px-3 py-2">
                    {{ __('messages.change_camera') }}
                </button>
            </div>

            <!-- AI Prompt (always visible under buttons) -->
            <div v-if="cameraStarted" class="mt-3">
                <div v-if="!editingPrompt">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        <span v-if="aiPrompt">@{{ aiPrompt }}</span>
                        <span v-else class="italic">No AI prompt set</span>
                    </p>
                </div>
                <div v-else>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('messages.ai_agenda_prompt') }}</label>
                    <textarea v-model="aiPrompt" rows="2" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-[#4E81FA] focus:ring-[#4E81FA] text-sm" placeholder="{{ __('messages.ai_agenda_prompt_placeholder') }}"></textarea>
                    <div class="flex items-center justify-between mt-2">
                        <label class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                            <input type="checkbox" v-model="saveAsDefault" class="rounded border-gray-300 dark:border-gray-600 text-[#4E81FA] shadow-sm focus:ring-[#4E81FA] me-2">
                            {{ __('messages.save_as_default') }}
                        </label>
                        <button @click="editingPrompt = false" class="text-sm font-medium text-[#4E81FA] hover:text-[#3a6de0] whitespace-nowrap border border-[#4E81FA] rounded-md px-4 py-2">{{ __('messages.done') }}</button>
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
                <div @dragover.prevent="onContainerDragOver($event)" @drop="onDrop()" class="space-y-3">
                    <div v-for="(part, index) in parts" :key="index"
                        draggable="true"
                        @dragstart="onDragStart(index)"
                        @dragover="onDragOver(index, $event)"
                        @drop="onDrop()"
                        @dragend="onDragEnd"
                        :class="{ 'opacity-50': dragIndex === index }"
                        :style="{ marginTop: dropTargetIndex === index && dragIndex !== null && dragIndex !== index ? '3rem' : '', transition: 'margin 150ms ease' }"
                        class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 shadow-sm">
                        <div class="flex items-start gap-2">
                            <div class="cursor-grab text-gray-400 dark:text-gray-500 pt-2">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 16 16">
                                    <circle cx="5.5" cy="3.5" r="1.5"/>
                                    <circle cx="10.5" cy="3.5" r="1.5"/>
                                    <circle cx="5.5" cy="8" r="1.5"/>
                                    <circle cx="10.5" cy="8" r="1.5"/>
                                    <circle cx="5.5" cy="12.5" r="1.5"/>
                                    <circle cx="10.5" cy="12.5" r="1.5"/>
                                </svg>
                            </div>
                            <div class="flex-1 space-y-2">
                                <input v-model="part.name" type="text" placeholder="{{ __('messages.name') }}" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-[#4E81FA] focus:ring-[#4E81FA] text-sm font-medium">
                                <textarea v-if="showDescription" v-model="part.description" rows="2" placeholder="{{ __('messages.description') }}" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-[#4E81FA] focus:ring-[#4E81FA] text-sm"></textarea>
                                <div v-if="showTimes" class="flex gap-2">
                                    <input v-model="part.start_time" type="text" placeholder="{{ __('messages.start_time') }}" class="w-1/2 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-[#4E81FA] focus:ring-[#4E81FA] text-sm">
                                    <input v-model="part.end_time" type="text" placeholder="{{ __('messages.end_time') }}" class="w-1/2 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-[#4E81FA] focus:ring-[#4E81FA] text-sm">
                                </div>
                            </div>
                            <button @click="removePart(index)" class="self-stretch rounded-md border border-red-300 dark:border-red-700 px-3 text-red-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors" title="{{ __('messages.remove') }}">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <button @click="addPart" class="w-full py-3 px-2 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-500 dark:text-gray-400 hover:border-gray-400 dark:hover:border-gray-500 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                    + {{ __('messages.add') }}
                </button>

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

    </div>
</div>
`
    });

    app.mount('#scan-agenda-app');
});
</script>

</x-app-admin-layout>
