@props([
    'name',
    'label' => null,
    'value' => null,
    'previewUrl' => null,
    'required' => false,
    'help' => null,
])

@php
    $componentId = 'image-picker-' . (string) \Illuminate\Support\Str::uuid();
    $initialId = old($name, $value);
    $initialUrl = old($name . '_preview', $previewUrl);
    $labelId = $componentId . '-trigger';
@endphp

<div id="{{ $componentId }}" class="space-y-2" data-component="image-picker"
     data-fetch-url="{{ route('images.index') }}"
     data-upload-url="{{ route('images.store') }}"
     data-destroy-url="{{ route('images.destroy', ['image' => '__IMAGE__']) }}">
    @if ($label)
        <x-input-label :for="$labelId" :value="$label" />
    @endif

    <div class="flex items-start gap-4">
        <div class="w-24 h-24 rounded-md border border-dashed border-gray-300 flex items-center justify-center overflow-hidden bg-white dark:bg-gray-800">
            <img data-role="preview" src="{{ $initialUrl }}" alt="" class="max-w-full max-h-full {{ $initialUrl ? '' : 'hidden' }}">
            <span data-role="placeholder" class="text-xs text-gray-500 {{ $initialUrl ? 'hidden' : '' }}">{{ __('messages.no_image_selected') }}</span>
        </div>

        <div class="flex-1 space-y-2">
            <input type="hidden" name="{{ $name }}" value="{{ $initialId }}" data-role="value">
            <div class="flex flex-wrap gap-2">
                <button type="button" id="{{ $labelId }}" data-role="open"
                        class="inline-flex items-center rounded-md border border-transparent bg-[#4E81FA] px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-[#365fcc] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#4E81FA]">
                    {{ __('messages.choose_image') }}
                </button>
                <button type="button" data-role="upload-trigger"
                        class="inline-flex items-center rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#4E81FA]">
                    {{ __('messages.upload_new') }}
                </button>
                <button type="button" data-role="clear"
                        class="inline-flex items-center rounded-md border border-transparent bg-red-500 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 {{ $initialId ? '' : 'hidden' }}">
                    {{ __('messages.remove_image') }}
                </button>
            </div>
            <input type="file" accept="image/*" class="hidden" data-role="upload-input">
            <p data-role="status" class="text-sm text-gray-500"></p>
            @if ($help)
                <p class="text-sm text-gray-500">{{ $help }}</p>
            @endif
            <x-input-error class="mt-2" :messages="$errors->get($name)" />
        </div>
    </div>

    <div data-role="modal" class="fixed inset-0 hidden z-30">
        <div class="absolute inset-0 bg-black/40" data-role="backdrop"></div>
        <div class="relative mx-auto mt-20 w-full max-w-3xl rounded-lg bg-white shadow-lg dark:bg-gray-900">
            <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('messages.choose_image') }}</h2>
                <button type="button" data-role="close"
                        class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">&times;</button>
            </div>
            <div class="px-4 py-3">
                <div data-role="modal-status" class="mb-3 text-sm text-gray-500"></div>
                <div data-role="image-list" class="grid max-h-80 grid-cols-2 gap-3 overflow-y-auto sm:grid-cols-3"></div>
            </div>
            <div class="flex justify-end border-t border-gray-200 px-4 py-3 dark:border-gray-700">
                <button type="button" data-role="close"
                        class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#4E81FA] dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-800">
                    {{ __('messages.close') }}
                </button>
            </div>
        </div>
    </div>
</div>

@pushOnce('image-picker-scripts')
    <script {!! nonce_attr() !!}>
        window.__imagePickerInit = window.__imagePickerInit || function (root) {
            if (!root || root.__imagePickerReady) {
                return;
            }

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const fetchUrl = root.dataset.fetchUrl;
            const uploadUrl = root.dataset.uploadUrl;
            const destroyUrlTemplate = root.dataset.destroyUrl;

            const preview = root.querySelector('[data-role="preview"]');
            const placeholder = root.querySelector('[data-role="placeholder"]');
            const valueInput = root.querySelector('[data-role="value"]');
            const openButton = root.querySelector('[data-role="open"]');
            const uploadTrigger = root.querySelector('[data-role="upload-trigger"]');
            const clearButton = root.querySelector('[data-role="clear"]');
            const uploadInput = root.querySelector('[data-role="upload-input"]');
            const status = root.querySelector('[data-role="status"]');
            const modal = root.querySelector('[data-role="modal"]');
            const modalStatus = modal.querySelector('[data-role="modal-status"]');
            const imageList = modal.querySelector('[data-role="image-list"]');
            const closeButtons = modal.querySelectorAll('[data-role="close"], [data-role="backdrop"]');

            let loaded = false;
            let loading = false;

            function setSelected(id, url) {
                valueInput.value = id || '';
                root.dataset.currentId = valueInput.value;
                root.dataset.currentUrl = url || '';

                if (url) {
                    preview.src = url;
                    preview.classList.remove('hidden');
                    placeholder.classList.add('hidden');
                    clearButton.classList.remove('hidden');
                } else {
                    preview.src = '';
                    preview.classList.add('hidden');
                    placeholder.classList.remove('hidden');
                    clearButton.classList.add('hidden');
                }

                const changeEvent = new CustomEvent('image-picker:change', {
                    bubbles: true,
                    detail: {
                        id: valueInput.value ? Number(valueInput.value) : null,
                        url: url || null,
                    },
                });

                root.dispatchEvent(changeEvent);
                valueInput.dispatchEvent(new Event('change', { bubbles: true }));
            }

            function setStatus(message, isError = false) {
                status.textContent = message || '';
                status.classList.toggle('text-red-500', Boolean(message) && isError);
                status.classList.toggle('text-gray-500', Boolean(message) && !isError);
            }

            function setModalStatus(message) {
                modalStatus.textContent = message || '';
            }

            function buildImageButton(image) {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'relative flex h-28 w-full items-center justify-center overflow-hidden rounded-md border border-transparent bg-gray-50 hover:border-[#4E81FA] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#4E81FA] dark:bg-gray-800';
                button.innerHTML = `<img src="${image.url}" alt="${image.name}" class="h-full w-full object-cover" />`;
                button.addEventListener('click', () => {
                    setSelected(image.id, image.url);
                    closeModal();
                });
                return button;
            }

            function openModal() {
                modal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');

                if (!loaded && !loading) {
                    loading = true;
                    setModalStatus('{{ __('messages.loading') }}');
                    fetch(fetchUrl, { headers: { 'Accept': 'application/json' } })
                        .then(response => response.json())
                        .then(data => {
                            imageList.innerHTML = '';
                            (data.data || []).forEach(image => {
                                imageList.appendChild(buildImageButton(image));
                            });
                            if ((data.data || []).length === 0) {
                                setModalStatus('{{ __('messages.no_images_available') }}');
                            } else {
                                setModalStatus('');
                            }
                            loaded = true;
                        })
                        .catch(() => {
                            setModalStatus('{{ __('messages.error_loading_images') }}');
                        })
                        .finally(() => {
                            loading = false;
                        });
                }
            }

            function closeModal() {
                modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }

            openButton.addEventListener('click', openModal);
            closeButtons.forEach(button => button.addEventListener('click', closeModal));

            uploadTrigger.addEventListener('click', () => uploadInput.click());

            uploadInput.addEventListener('change', event => {
                const file = event.target.files?.[0];
                if (!file) {
                    return;
                }

                const formData = new FormData();
                formData.append('image', file);

                setStatus('{{ __('messages.uploading') }}');

                fetch(uploadUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: formData,
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Upload failed');
                        }
                        return response.json();
                    })
                    .then(data => {
                        const image = data.data;
                        if (image) {
                            if (loaded) {
                                imageList.prepend(buildImageButton(image));
                            }
                            setSelected(image.id, image.url);
                            setStatus('{{ __('messages.image_uploaded') }}');
                        }
                    })
                    .catch(() => {
                        setStatus('{{ __('messages.upload_failed') }}', true);
                    })
                    .finally(() => {
                        uploadInput.value = '';
                        setTimeout(() => setStatus(''), 3000);
                    });
            });

            clearButton.addEventListener('click', () => {
                setSelected(null, null);
            });

            root.dataset.currentId = valueInput.value || '';
            root.dataset.currentUrl = preview.src || '';

            if (valueInput.value && preview.src) {
                preview.classList.remove('hidden');
                placeholder.classList.add('hidden');
                clearButton.classList.remove('hidden');
            }

            root.__imagePickerReady = true;
        };

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-component="image-picker"]').forEach(root => {
                window.__imagePickerInit(root);
            });
        });
    </script>
@endpushOnce
