{{-- Subdomain Autocomplete --}}
<script {!! nonce_attr() !!}>
    function escapeHtml(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    document.querySelectorAll('[data-subdomain-autocomplete]').forEach(function(input) {
        var dropdown = input.parentElement.querySelector('[data-subdomain-dropdown]');
        var debounceTimer = null;

        input.addEventListener('input', function() {
            var q = this.value.trim();
            clearTimeout(debounceTimer);

            if (q.length < 2) {
                dropdown.classList.add('hidden');
                dropdown.innerHTML = '';
                return;
            }

            debounceTimer = setTimeout(function() {
                fetch('{{ route("role.search-subdomains") }}' + '?q=' + encodeURIComponent(q), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(function(res) { return res.json(); })
                .then(function(results) {
                    dropdown.innerHTML = '';
                    if (results.length === 0) {
                        dropdown.classList.add('hidden');
                        return;
                    }
                    results.forEach(function(item) {
                        var row = document.createElement('div');
                        row.className = 'px-3 py-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700';
                        var nameText = item.name || item.subdomain;
                        var cityText = item.city ? ' <span class="text-xs text-gray-400">' + escapeHtml(item.city) + '</span>' : '';
                        row.innerHTML = '<div class="font-medium text-sm text-gray-900 dark:text-gray-100">' + escapeHtml(nameText) + cityText + '</div>'
                            + '<div class="text-xs text-gray-500 dark:text-gray-400">' + escapeHtml(item.subdomain) + '</div>';
                        row.addEventListener('click', function() {
                            input.value = item.subdomain;
                            dropdown.classList.add('hidden');
                            dropdown.innerHTML = '';
                        });
                        dropdown.appendChild(row);
                    });
                    dropdown.classList.remove('hidden');
                });
            }, 300);
        });
    });

    document.addEventListener('click', function(e) {
        document.querySelectorAll('[data-subdomain-dropdown]').forEach(function(dropdown) {
            if (!dropdown.parentElement.contains(e.target)) {
                dropdown.classList.add('hidden');
                dropdown.innerHTML = '';
            }
        });
    });
</script>
