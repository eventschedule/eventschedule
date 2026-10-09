{{--
    The AI writer on the blog's create and edit forms: a topic, one button, and what the check
    made of the result. The form's own fields are filled; saving stays the admin's decision.

    $writerTopic   what the topic box starts with (a post's own title, on the edit form)
    $writerExcept  the slug of the post being rewritten, or null for a new post
    Named $writer* so nothing here reads a variable the including form happens to hold.
--}}
            @if ($hasAi)
            <x-page-card :title="__('messages.ai_content_generation')" :lead="__('messages.ai_content_generation_description')">
                <div class="flex flex-wrap items-end gap-3">
                    <div class="min-w-[240px] flex-1">
                        <x-input-label for="ai_topic" :value="__('messages.topic')" />
                        <textarea id="ai_topic" rows="2" dir="auto" placeholder="{{ __('messages.blog_topic_placeholder') }}" class="{{ $field }}">{{ $writerTopic }}</textarea>
                    </div>
                    <x-brand-button type="button" id="generate_btn">
                        <svg class="w-5 h-5 -ms-0.5 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        {{ __('messages.generate_content') }}
                    </x-brand-button>
                </div>
            </x-page-card>
            @else
            <x-gemini-setup-guide optional :text="__('messages.blog_ai_needs_key')" />
            @endif

            {{-- What the check made of a generated post, filled by the script below. Saving
                 stays the admin's decision either way. --}}
            <div id="check_passed" hidden>
                <x-page-notice tone="success">{{ __('messages.blog_check_passed') }}</x-page-notice>
            </div>
            <div id="check_failed" hidden>
                <x-page-notice tone="warn" :title="__('messages.blog_check_failed')">
                    <ul id="check_failures" class="mt-1 list-disc ps-5 space-y-1"></ul>
                </x-page-notice>
            </div>


            @if ($hasAi)
            <script {!! nonce_attr() !!}>
                document.addEventListener('DOMContentLoaded', function () {
                    document.getElementById('generate_btn').addEventListener('click', generateContent);
                });

        function generateContent() {
            const topic = document.getElementById('ai_topic').value;

            if (!topic.trim()) {
                alert(@json(__('messages.please_enter_topic')));
                return;
            }

            const generateBtn = document.getElementById('generate_btn');
            const originalText = generateBtn.innerHTML;
            const working = @json(__('messages.generating'));
            generateBtn.disabled = true;
            document.getElementById('check_passed').hidden = true;
            document.getElementById('check_failed').hidden = true;

            // A brief, a draft and an edit: three model calls that together take longer than one
            // request may, so each is its own request and hands the next what it returned.
            const ask = function (step, body) {
                generateBtn.textContent = working + ' ' + step + '/3';

                return fetch('{{ route("blog.generate-content") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(Object.assign({ topic: topic, except: @json($writerExcept) }, body))
                }).then(function (response) {
                    if (!response.ok) throw new Error('Request failed');
                    return response.json();
                });
            };

            let brief;

            ask(1, { step: 'brief' })
                .then(function (data) { brief = data.brief; return ask(2, { step: 'draft', brief: brief }); })
                .then(function (data) { return ask(3, { step: 'edit', brief: brief, draft: data.draft }); })
                .then(function (data) {
                    document.getElementById('title').value = data.title || '';
                    document.getElementById('content').value = data.content || '';
                    document.getElementById('excerpt').value = data.excerpt || '';
                    document.getElementById('meta_title').value = (data.meta_title || '').slice(0, 60);
                    document.getElementById('meta_description').value = (data.meta_description || '').slice(0, 160);
                    document.getElementById('primary_query').value = data.primary_query || '';
                    document.getElementById('faq').value = JSON.stringify(data.faq || []);
                    if (data.category) {
                        document.getElementById('category').value = data.category;
                    }

                    const failures = data.failures || [];
                    const list = document.getElementById('check_failures');
                    list.textContent = '';
                    failures.forEach(function (failure) {
                        const item = document.createElement('li');
                        item.textContent = failure;
                        list.appendChild(item);
                    });
                    document.getElementById('check_failed').hidden = failures.length === 0;
                    document.getElementById('check_passed').hidden = failures.length !== 0;

                    Toastify({
                        text: @json(__('messages.content_generated')),
                        duration: 3000,
                        position: 'center',
                        style: {
                            background: '#4BB543',
                        }
                    }).showToast();
                })
                .catch(function (error) {
                    console.error('Error:', error);
                    alert(@json(__('messages.failed_to_generate_content')));
                })
                .finally(function () {
                    generateBtn.innerHTML = originalText;
                    generateBtn.disabled = false;
                });
        }
            </script>
            @endif
