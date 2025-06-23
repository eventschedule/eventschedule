<!-- Embed Modal -->
<div id="embed-modal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl sm:p-6">
                <div class="absolute right-0 top-0 pr-4 pt-4">
                    <button type="button" onclick="closeEmbedModal()" class="rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-[#4E81FA] focus:ring-offset-2">
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-[#4E81FA] bg-opacity-10 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-[#4E81FA]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12.89,3L14.85,3.4L11.11,21L9.15,20.6L12.89,3M19.59,12L16,8.41V5.58L22.42,12L16,18.41V15.58L19.59,12M1.58,12L8,5.58V8.41L4.41,12L8,15.58V18.41L1.58,12Z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                        <h3 class="text-base font-semibold leading-6 text-gray-900" id="modal-title">
                            {{ __('messages.embed_schedule') }}
                        </h3>
                        <div class="mt-4">
                            <p class="text-sm text-gray-500 mb-4">
                                {{ __('messages.embed_description') }}
                            </p>
                            
                            <!-- Embed URL -->
                            <div class="mb-4">
                                <label for="embed-url" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('messages.embed_url') }}
                                </label>
                                <div class="flex">
                                    <input type="text" id="embed-url" readonly 
                                           value="{{ route('role.view_guest', ['subdomain' => $role->subdomain, 'embed' => 'true']) }}"
                                           class="block w-full rounded-l-md border-gray-300 shadow-sm focus:border-[#4E81FA] focus:ring-[#4E81FA] sm:text-sm">
                                    <button type="button" id="embed-url-btn" onclick="copyEmbedUrl()" 
                                            class="inline-flex items-center rounded-r-md border border-l-0 border-gray-300 bg-gray-50 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 focus:border-[#4E81FA] focus:outline-none focus:ring-1 focus:ring-[#4E81FA]">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0013.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 01-.75.75H9a.75.75 0 01-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 011.927-.184" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Example iframe code -->
                            <div class="mb-4">
                                <label for="iframe-code" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('messages.iframe_code') }}
                                </label>
                                <div class="flex">
                                    <textarea id="iframe-code" readonly rows="4" 
                                              class="block w-full rounded-l-md border-gray-300 shadow-sm focus:border-[#4E81FA] focus:ring-[#4E81FA] sm:text-sm font-mono text-xs"
                                              style="resize: vertical;"><iframe src="{{ route('role.view_guest', ['subdomain' => $role->subdomain, 'embed' => 'true']) }}" width="100%" height="800" frameborder="0" style="border: none;"></iframe></textarea>
                                    <button type="button" id="iframe-code-btn" onclick="copyIframeCode()" 
                                            class="inline-flex items-center rounded-r-md border border-l-0 border-gray-300 bg-gray-50 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 focus:border-[#4E81FA] focus:outline-none focus:ring-1 focus:ring-[#4E81FA]">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0013.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 01-.75.75H9a.75.75 0 01-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 011.927-.184" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Preview -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('messages.preview') }}
                                </label>                                
                                <div class="border border-gray-300 rounded-md p-2 bg-gray-50" style="height: 300px; overflow: auto;">
                                    <iframe src="{{ route('role.view_guest', ['subdomain' => $role->subdomain, 'embed' => 'true']) }}" 
                                            width="100%" height="800" frameborder="0" 
                                            style="border: none; border-radius: 4px;"></iframe>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                    <button type="button" onclick="closeEmbedModal()" 
                            class="inline-flex w-full justify-center rounded-md bg-[#4E81FA] px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#3A6BE0] sm:ml-3 sm:w-auto">
                        {{ __('messages.close') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function openEmbedModal() {
    document.getElementById('embed-modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeEmbedModal() {
    document.getElementById('embed-modal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function copyEmbedUrl() {
    const urlInput = document.getElementById('embed-url');
    urlInput.select();
    urlInput.setSelectionRange(0, 99999); // For mobile devices
    navigator.clipboard.writeText(urlInput.value).then(() => {
        showCopySuccess('embed-url-btn');
    });
}

function copyIframeCode() {
    const codeTextarea = document.getElementById('iframe-code');
    codeTextarea.select();
    codeTextarea.setSelectionRange(0, 99999); // For mobile devices
    navigator.clipboard.writeText(codeTextarea.value).then(() => {
        showCopySuccess('iframe-code-btn');
    });
}

function showCopySuccess(buttonId) {
    const button = document.getElementById(buttonId);
    if (button) {
        const originalContent = button.innerHTML;
        button.innerHTML = `
            <svg class="h-4 w-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
            </svg>
        `;
        setTimeout(() => {
            button.innerHTML = originalContent;
        }, 2000);
    }
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const modal = document.getElementById('embed-modal');
    if (event.target === modal) {
        closeEmbedModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeEmbedModal();
    }
});
</script> 