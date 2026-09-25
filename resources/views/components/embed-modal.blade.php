<!-- Embed Modal -->
@php
    $embedUrl = route('role.view_guest', ['subdomain' => $role->subdomain, 'embed' => 'true']);
    // For preview, use current request's protocol to avoid HTTPS cert issues in local dev
    $previewUrl = preg_replace('/^https?:/', request()->getScheme() . ':', $embedUrl);
    // The Layout picker appends &layout= to both of the above. Blank means "leave it off",
    // which keeps the schedule's own Default Layout in charge - the pre-existing behaviour.
    $embedLayoutDefaultLabel = __('messages.embed_layout_default', ['layout' => __('messages.' . $role->eventLayout())]);
    // Built here and handed to the script below so the server-rendered snippet and the one
    // the Layout picker rebuilds cannot drift apart.
    $embedBrandingLine = $role->showBranding()
        ? "\n".'<p style="font-size: 12px; text-align: right; margin-top: 4px; opacity: 0.6;"><a href="https://eventschedule.com" target="_blank" rel="noopener" style="color: inherit; text-decoration: none;">Powered by Event Schedule</a></p>'
        : '';

    // The Widget picker's second option: the signup form (issue #125), served by the same guest
    // route with &form=subscribe. The iframe title names the schedule for screen readers; it is
    // escaped once here, for the attribute it lands in, and @json below makes it safe for the JS.
    $embedSubscribeFrameId = 'es-subscribe-'.$role->subdomain;
    $embedSubscribeTitle = e(__('messages.embed_subscribe_iframe_title', ['schedule' => $role->name]));

    // Whether a signup would actually hear about anything. Announcements are the only automatic
    // mail a subscriber gets, and both of these silence them without telling the owner.
    $embedAnnouncementsOff = ! $role->announce_new_events;
    $embedAudienceLimit = (int) config('usage.audience_mail_unverified_max_recipients', 50);
    $embedNeedsVerification = config('app.hosted') && ! config('app.is_testing')
        && ! $role->canSendAudienceMail($embedAudienceLimit + 1);
@endphp
<div id="embed-modal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/75 transition-opacity"></div>

    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-xl bg-white dark:bg-gray-800 px-4 pb-4 pt-5 text-left shadow-xl dark:shadow-gray-900/50 transition-all sm:my-8 sm:w-full sm:max-w-2xl sm:p-6">
                <div class="absolute end-0 top-0 pe-4 pt-4">
                    <button type="button" class="js-close-embed-modal rounded-lg bg-white dark:bg-gray-800 text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                        <span class="sr-only">{{ __('messages.close_modal') }}</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-[var(--brand-button-bg)]/10 dark:bg-[var(--brand-button-bg)]/20 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-[var(--brand-blue)]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12.89,3L14.85,3.4L11.11,21L9.15,20.6L12.89,3M19.59,12L16,8.41V5.58L22.42,12L16,18.41V15.58L19.59,12M1.58,12L8,5.58V8.41L4.41,12L8,15.58V18.41L1.58,12Z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:ms-4 sm:mt-0 sm:text-start w-full">
                        <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-gray-100" id="modal-title">
                            <span data-embed-for="calendar">{{ __('messages.embed_schedule') }}</span>
                            <span data-embed-for="subscribe" hidden>{{ __('messages.embed_subscribe_form') }}</span>
                        </h3>
                        <div class="mt-4">
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                <span data-embed-for="calendar">{{ __('messages.embed_description') }}</span>
                                <span data-embed-for="subscribe" hidden>{{ __('messages.embed_subscribe_description') }}</span>
                            </p>

                            <!-- Widget -->
                            <div class="mb-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="embed-widget" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        {{ __('messages.embed_widget') }}
                                    </label>
                                    <select id="embed-widget"
                                            class="block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] sm:text-sm">
                                        <option value="calendar">{{ __('messages.embed_widget_calendar') }}</option>
                                        <option value="subscribe">{{ __('messages.embed_widget_subscribe') }}</option>
                                    </select>
                                </div>
                                {{-- Auto follows each visitor's OS, which on a light website means a
                                     dark-mode visitor gets a dark widget. The owner knows their
                                     site's colours, so they can pin either one. --}}
                                <div>
                                    <label for="embed-theme" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        {{ __('messages.embed_theme') }}
                                    </label>
                                    <select id="embed-theme"
                                            class="block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] sm:text-sm">
                                        <option value="">{{ __('messages.embed_theme_auto') }}</option>
                                        <option value="light">{{ __('messages.embed_theme_light') }}</option>
                                        <option value="dark">{{ __('messages.embed_theme_dark') }}</option>
                                    </select>
                                </div>
                            </div>

                            @if ($embedAnnouncementsOff || $embedNeedsVerification)
                            <div data-embed-for="subscribe" hidden class="mb-4 space-y-3">
                                @if ($embedAnnouncementsOff)
                                <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3">
                                    <div class="flex">
                                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                                        </svg>
                                        <div class="ms-3 text-sm text-amber-700 dark:text-amber-300">
                                            <p class="font-medium">{{ __('messages.embed_subscribe_announcements_off') }}</p>
                                            <p class="mt-1">
                                                <a href="{{ route('role.edit', ['subdomain' => $role->subdomain]) }}#section-settings" class="underline font-medium">{{ __('messages.embed_subscribe_turn_on_announcements') }}</a>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                @if ($embedNeedsVerification)
                                {{-- The digest goes through the same trust gate as a newsletter
                                     (SendEventAnnouncements), so past the limit it stops, silently. --}}
                                <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3">
                                    <div class="flex">
                                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                                        </svg>
                                        <div class="ms-3 text-sm text-amber-700 dark:text-amber-300">
                                            <p class="font-medium">{{ __('messages.embed_subscribe_verification_title', ['limit' => $embedAudienceLimit]) }}</p>
                                            <p class="mt-1">
                                                {!! __('messages.newsletter_verification_required_body', [
                                                    'smtp_link' => route('role.edit', ['subdomain' => $role->subdomain]) . '?tab=email#section-integrations',
                                                    'phone_link' => route('profile.edit') . '?highlight=phone#section-profile',
                                                    'limit' => $embedAudienceLimit,
                                                ]) !!}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                            @endif

                            <!-- Layout -->
                            <div class="mb-4" data-embed-for="calendar">
                                <label for="embed-layout" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    {{ __('messages.embed_layout') }}
                                </label>
                                <select id="embed-layout"
                                        class="block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] sm:text-sm">
                                    <option value="">{{ $embedLayoutDefaultLabel }}</option>
                                    <option value="calendar">{{ __('messages.calendar') }}</option>
                                    <option value="list">{{ __('messages.list') }}</option>
                                </select>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('messages.embed_layout_help') }}</p>
                            </div>

                            <!-- Embed URL -->
                            <div class="mb-4">
                                <label for="embed-url" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    {{ __('messages.embed_url') }}
                                </label>
                                <div class="flex">
                                    <input type="text" id="embed-url" readonly
                                           value="{{ $embedUrl }}"
                                           class="block w-full rounded-s-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] sm:text-sm">
                                    <button type="button" id="embed-url-btn" class="js-copy-embed-url inline-flex items-center rounded-e-lg border border-s-0 border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-700 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 focus:border-[var(--brand-blue)] focus:outline-none focus:ring-1 focus:ring-[var(--brand-blue)]">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0013.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 01-.75.75H9a.75.75 0 01-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 011.927-.184" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Example iframe code -->
                            <div class="mb-4">
                                <label for="iframe-code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    {{ __('messages.iframe_code') }}
                                </label>
                                <div class="flex">
                                    <textarea id="iframe-code" readonly rows="4"
                                              class="block w-full rounded-s-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] sm:text-sm font-mono text-xs"
                                              style="resize: vertical;">{{ '<iframe src="'.$embedUrl.'" width="100%" height="800" frameborder="0" style="border: none;"></iframe>'.$embedBrandingLine }}</textarea>
                                    <button type="button" id="iframe-code-btn" class="js-copy-iframe-code inline-flex items-center rounded-e-lg border border-s-0 border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-700 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 focus:border-[var(--brand-blue)] focus:outline-none focus:ring-1 focus:ring-[var(--brand-blue)]">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0013.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 01-.75.75H9a.75.75 0 01-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 011.927-.184" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Preview -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    {{ __('messages.preview') }}
                                </label>                                
                                <div id="embed-preview-box" class="border border-gray-300 dark:border-gray-700 rounded-lg p-2 bg-gray-50 dark:bg-gray-900" style="height: 300px; overflow: auto;">
                                    {{-- No src until the modal opens; the script below sets it. --}}
                                    <iframe id="embed-preview-iframe"
                                            width="100%" height="800" frameborder="0"
                                            style="border: none; border-radius: 4px;"></iframe>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                    <button type="button" class="js-close-embed-modal inline-flex w-full items-center justify-center rounded-lg bg-gray-200 dark:bg-gray-700 px-5 py-3 text-base font-semibold text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors sm:ms-3 sm:w-auto">
                        {{ __('messages.close') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script {!! nonce_attr() !!}>
const embedBaseUrl = @json($embedUrl);
const embedPreviewBaseUrl = @json($previewUrl);
const embedBrandingLine = @json($embedBrandingLine);
const embedSubscribeFrameId = @json($embedSubscribeFrameId);
const embedSubscribeTitle = @json($embedSubscribeTitle);
// The fallback height for sites that strip the resize listener: fits the form on a desktop-width
// page. The listener then sizes it exactly, whatever state the form is in.
const embedSubscribeHeight = 450;
let embedPreviewLoaded = '';

function selectedEmbedValue(id) {
    const select = document.getElementById(id);

    return select ? select.value : '';
}

// Blank layout and theme mean "leave it off": the schedule's own Default Layout, and each
// visitor's OS preference, stay in charge - the pre-existing behaviour.
function buildEmbedUrl(base) {
    const params = [];

    if (selectedEmbedValue('embed-widget') === 'subscribe') {
        params.push('form=subscribe');
    } else if (selectedEmbedValue('embed-layout')) {
        params.push('layout=' + encodeURIComponent(selectedEmbedValue('embed-layout')));
    }

    const theme = selectedEmbedValue('embed-theme');
    if (theme) {
        params.push('dark=' + (theme === 'dark' ? 'true' : 'false'));
    }

    if (! params.length) {
        return base;
    }

    return base + (base.includes('?') ? '&' : '?') + params.join('&');
}

function buildEmbedSnippet(url) {
    if (selectedEmbedValue('embed-widget') !== 'subscribe') {
        return '<iframe src="' + url + '" width="100%" height="800" frameborder="0" style="border: none;"></iframe>' + embedBrandingLine;
    }

    // The listener checks e.source, so only this iframe can resize it, and only a number is read.
    // The closing tag is split so it cannot end THIS script block.
    return '<iframe id="' + embedSubscribeFrameId + '" src="' + url + '" title="' + embedSubscribeTitle + '" width="100%" height="' + embedSubscribeHeight + '" frameborder="0" style="border: none;"></iframe>'
        + '\n<script>window.addEventListener("message",function(e){var f=document.getElementById("' + embedSubscribeFrameId + '");'
        + 'if(f&&e.source===f.contentWindow&&e.data&&e.data.type==="eventschedule:resize"&&e.data.height>0){f.style.height=Math.ceil(e.data.height)+"px";}});<' + '/script>'
        + embedBrandingLine;
}

function updateEmbedSnippets() {
    const isSubscribe = selectedEmbedValue('embed-widget') === 'subscribe';

    document.querySelectorAll('#embed-modal [data-embed-for]').forEach(function (el) {
        el.hidden = el.getAttribute('data-embed-for') !== (isSubscribe ? 'subscribe' : 'calendar');
    });

    const url = buildEmbedUrl(embedBaseUrl);

    const urlInput = document.getElementById('embed-url');
    if (urlInput) {
        urlInput.value = url;
    }

    const codeTextarea = document.getElementById('iframe-code');
    if (codeTextarea) {
        codeTextarea.value = buildEmbedSnippet(url);
    }

    refreshEmbedPreview();
}

// The preview is deliberately not loaded until the modal is opened, and is reloaded only
// when the URL actually changes so re-opening the modal does not refetch the schedule.
function refreshEmbedPreview() {
    const iframe = document.getElementById('embed-preview-iframe');
    if (! iframe) {
        return;
    }

    const url = buildEmbedUrl(embedPreviewBaseUrl);
    if (embedPreviewLoaded === url) {
        return;
    }

    // The calendar keeps its tall frame in a scrolling box; the signup form is short, so its box
    // fits the frame, which the form itself sizes through the message listener below.
    const box = document.getElementById('embed-preview-box');
    const isSubscribe = selectedEmbedValue('embed-widget') === 'subscribe';
    if (box) {
        box.style.height = isSubscribe ? 'auto' : '300px';
    }
    iframe.style.height = '';
    iframe.setAttribute('height', isSubscribe ? embedSubscribeHeight : 800);

    embedPreviewLoaded = url;
    iframe.src = url;
}

// The preview answers the same resize message the copied snippet listens for.
window.addEventListener('message', function (e) {
    const iframe = document.getElementById('embed-preview-iframe');
    if (iframe && e.source === iframe.contentWindow && e.data && e.data.type === 'eventschedule:resize' && e.data.height > 0) {
        iframe.style.height = Math.ceil(e.data.height) + 'px';
    }
});

// widget: optional preset ('calendar' or 'subscribe'), e.g. from the Followers tab.
function openEmbedModal(widget) {
    if (widget) {
        const select = document.getElementById('embed-widget');
        if (select) {
            select.value = widget;
        }
    }

    document.getElementById('embed-modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    // Rebuild everything, not just the preview: browsers restore a <select>'s value across a
    // soft reload, so the picker can already read "list" while the server-rendered URL and
    // snippet still carry no layout. Copying then hands over something the preview disproves.
    updateEmbedSnippets();
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
    }).catch(() => {});
}

function copyIframeCode() {
    const codeTextarea = document.getElementById('iframe-code');
    codeTextarea.select();
    codeTextarea.setSelectionRange(0, 99999); // For mobile devices
    navigator.clipboard.writeText(codeTextarea.value).then(() => {
        showCopySuccess('iframe-code-btn');
    }).catch(() => {});
}

function showCopySuccess(buttonId) {
    const button = document.getElementById(buttonId);
    if (button) {
        const originalContent = button.innerHTML;
        button.innerHTML = `
            <svg class="h-4 w-4 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
            </svg>
        `;
        setTimeout(() => {
            button.innerHTML = originalContent;
        }, 2000);
    }
}

document.addEventListener('click', function(event) {
    // Close buttons
    if (event.target.closest('.js-close-embed-modal')) {
        closeEmbedModal();
        return;
    }
    // Copy buttons
    if (event.target.closest('.js-copy-embed-url')) {
        copyEmbedUrl();
        return;
    }
    if (event.target.closest('.js-copy-iframe-code')) {
        copyIframeCode();
        return;
    }
    // Close modal when clicking outside
    const modal = document.getElementById('embed-modal');
    if (! modal.classList.contains('hidden') && ! event.target.closest('.relative.transform')) {
        closeEmbedModal();
    }
});

document.addEventListener('change', function(event) {
    if (['embed-layout', 'embed-widget', 'embed-theme'].includes(event.target.id)) {
        updateEmbedSnippets();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape' && ! document.getElementById('embed-modal').classList.contains('hidden')) {
        closeEmbedModal();
    }
});
</script> 