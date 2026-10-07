{{-- The schedule picker under a search or subdomain box: type two letters and it offers the
     schedules that match. Included by three pages (schedules, domains, boost), each of which
     writes the box ([data-subdomain-autocomplete]) and an empty list beside it
     ([data-subdomain-dropdown]) inside one positioned wrapper.

     The list's look lives here, on the attribute, so the three pages cannot drift: the one on
     /admin/boost had no background at all and the campaigns showed through it. --}}
<style {!! nonce_attr() !!}>
    [data-subdomain-dropdown] {
      z-index: 50;
      max-height: 15rem;
      overflow-y: auto;
      border: 1px solid rgb(var(--ap-border));
      border-radius: 0.75rem;
      padding: 0.25rem;
      background: rgb(var(--ap-surface));
      box-shadow: var(--ap-shadow-dropdown);
      text-align: start;
    }
    .subdomain-option {
      display: block;
      width: 100%;
      border: 0;
      border-radius: 0.5rem;
      padding: 0.4375rem 0.625rem;
      background: none;
      text-align: start;
      cursor: pointer;
    }
    .subdomain-option:hover,
    .subdomain-option:focus-visible {
      background: var(--ap-tint-1);
      outline: none;
    }
    .subdomain-option-name {
      display: block;
      font-size: 0.875rem;
      font-weight: 500;
      color: rgb(var(--ap-ink));
      overflow-wrap: anywhere;
    }
    .subdomain-option-city {
      margin-inline-start: 0.375rem;
      font-size: 0.75rem;
      font-weight: 400;
      color: rgb(var(--ap-ink-4));
    }
    .subdomain-option-sub {
      display: block;
      font-size: 0.75rem;
      color: rgb(var(--ap-ink-3));
    }
</style>

<script {!! nonce_attr() !!}>
    // Kept for any page script that still calls it; the list below is built from text nodes.
    function escapeHtml(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    document.querySelectorAll('[data-subdomain-autocomplete]').forEach(function(input) {
        var dropdown = input.parentElement.querySelector('[data-subdomain-dropdown]');
        if (!dropdown) {
            return;
        }
        var debounceTimer = null;
        // Extra query string for this one input. This partial is included by three pages
        // (schedules, domains, boost), so the params cannot be hardcoded in the fetch below:
        // /admin/schedules forwards its own owner and status filters here so the dropdown offers
        // exactly what its table can return, while the other two keep the plain default.
        var extraParams = input.getAttribute('data-subdomain-params') || 'admin_listable=1';

        function close() {
            dropdown.classList.add('hidden');
            dropdown.textContent = '';
        }

        input.addEventListener('input', function() {
            var q = this.value.trim();
            clearTimeout(debounceTimer);

            if (q.length < 2) {
                close();
                return;
            }

            debounceTimer = setTimeout(function() {
                // admin_listable keeps the dropdown to the same set the calling table can
                // render. Without it, unclaimed auto-created schedules show up here and then
                // filter to nothing.
                fetch('{{ route("role.search-subdomains") }}' + '?q=' + encodeURIComponent(q) + '&' + extraParams, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(function(res) { return res.json(); })
                .then(function(results) {
                    dropdown.textContent = '';
                    if (results.length === 0) {
                        dropdown.classList.add('hidden');
                        return;
                    }
                    results.forEach(function(item) {
                        // A button, so the list can be reached and chosen from with the keyboard.
                        var row = document.createElement('button');
                        row.type = 'button';
                        row.className = 'subdomain-option';

                        var name = document.createElement('span');
                        name.className = 'subdomain-option-name';
                        name.dir = 'auto';
                        name.appendChild(document.createTextNode(item.name || item.subdomain));
                        if (item.city) {
                            var city = document.createElement('span');
                            city.className = 'subdomain-option-city';
                            city.appendChild(document.createTextNode(item.city));
                            name.appendChild(city);
                        }

                        var sub = document.createElement('span');
                        sub.className = 'subdomain-option-sub';
                        sub.dir = 'ltr';
                        sub.appendChild(document.createTextNode(item.subdomain));

                        row.appendChild(name);
                        row.appendChild(sub);
                        row.addEventListener('click', function() {
                            input.value = item.subdomain;
                            close();
                            input.focus();
                        });
                        dropdown.appendChild(row);
                    });
                    dropdown.classList.remove('hidden');
                })
                .catch(function() {
                    close();
                });
            }, 300);
        });

        input.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                close();
            } else if (e.key === 'ArrowDown' && dropdown.firstElementChild) {
                e.preventDefault();
                dropdown.firstElementChild.focus();
            }
        });

        dropdown.addEventListener('keydown', function(e) {
            var current = document.activeElement;
            if (e.key === 'Escape') {
                close();
                input.focus();
            } else if (e.key === 'ArrowDown' && current.nextElementSibling) {
                e.preventDefault();
                current.nextElementSibling.focus();
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                (current.previousElementSibling || input).focus();
            }
        });
    });

    document.addEventListener('click', function(e) {
        document.querySelectorAll('[data-subdomain-dropdown]').forEach(function(dropdown) {
            if (!dropdown.parentElement.contains(e.target)) {
                dropdown.classList.add('hidden');
                dropdown.textContent = '';
            }
        });
    });
</script>
