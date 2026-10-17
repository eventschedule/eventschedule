{{-- The mail a sent newsletter was, opened from its statistics (#news-view-mail carries the preview
     address). It is drawn again from what was saved, so its events are the ones that stand today,
     which the dialog says. Plain DOM script: the page is server-rendered. --}}
<div id="news-mail-dialog" class="news-mail-dialog" hidden>
    <div class="news-mail-sheet" role="dialog" aria-modal="true" aria-labelledby="news-mail-title">
        <div class="news-mail-head">
            <h2 id="news-mail-title" class="sr-only">{{ __('messages.newsletter_view_email') }}</h2>
            <p>{{ __('messages.newsletter_view_email_note') }}</p>
            <button type="button" class="event-link" data-news-mail-close>{{ __('messages.close') }}</button>
        </div>
        <iframe sandbox="" title="{{ __('messages.newsletter_view_email') }}"></iframe>
    </div>
</div>
<script {!! nonce_attr() !!}>
    document.addEventListener('DOMContentLoaded', function () {
        var button = document.getElementById('news-view-mail');
        var dialog = document.getElementById('news-mail-dialog');
        if (! button || ! dialog) {
            return;
        }
        var frame = dialog.querySelector('iframe');

        function close() {
            dialog.hidden = true;
            button.focus();
        }

        button.addEventListener('click', function () {
            var body = new FormData();
            body.append('_token', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '');
            button.disabled = true;
            fetch(button.getAttribute('data-url'), {
                method: 'POST',
                body: body,
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            })
                .then(function (response) {
                    if (! response.ok) {
                        throw new Error('preview');
                    }
                    return response.json();
                })
                .then(function (data) {
                    frame.srcdoc = data.html;
                    dialog.hidden = false;
                    dialog.querySelector('[data-news-mail-close]').focus();
                })
                .catch(function () {
                    alert(@json(__('messages.something_went_wrong')));
                })
                .finally(function () {
                    button.disabled = false;
                });
        });

        dialog.addEventListener('click', function (e) {
            if (e.target === dialog || e.target.closest('[data-news-mail-close]')) {
                close();
            }
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && ! dialog.hidden) {
                close();
            }
        });
    });
</script>
