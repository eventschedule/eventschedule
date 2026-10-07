{{-- What every list of the newsletters section is wired with: a heading that sorts (the page-sort
     component carries data-sort) and a form that asks before it deletes (.js-confirm-form). Each
     page carried its own copy. Plain DOM script: these pages are server-rendered.

     $defaultSort is the column the controller sorts by when the address names none. --}}
<script {!! nonce_attr() !!}>
    document.addEventListener('click', function(e) {
        var header = e.target.closest ? e.target.closest('[data-sort]') : null;
        if (! header) {
            return;
        }
        var url = new URL(window.location.href);
        var currentSort = url.searchParams.get('sort_by') || @json($defaultSort ?? 'created_at');
        var currentDir = url.searchParams.get('sort_dir') || 'desc';
        var sortBy = header.getAttribute('data-sort');
        url.searchParams.set('sort_by', sortBy);
        url.searchParams.set('sort_dir', currentSort === sortBy && currentDir === 'asc' ? 'desc' : 'asc');
        url.searchParams.delete('page');
        window.location.href = url.toString();
    });

    document.addEventListener('submit', function(e) {
        var form = e.target.closest ? e.target.closest('.js-confirm-form') : null;
        if (form && ! confirm(form.getAttribute('data-confirm'))) {
            e.preventDefault();
        }
    });
</script>
