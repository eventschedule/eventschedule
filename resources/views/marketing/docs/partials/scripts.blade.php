<script {!! nonce_attr() !!}>
    // Add copy-link buttons to section headings
    document.querySelectorAll('.doc-heading').forEach(heading => {
        const section = heading.closest('.doc-section');
        if (section && section.id) {
            const btn = document.createElement('button');
            btn.className = 'doc-heading-copy';
            btn.title = 'Copy link to section';
            btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>';
            btn.addEventListener('click', function() {
                const url = window.location.origin + window.location.pathname + '#' + section.id;
                navigator.clipboard.writeText(url).then(() => {
                    btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>';
                    btn.style.color = '#10b981';
                    btn.style.opacity = '1';
                    setTimeout(() => {
                        btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>';
                        btn.style.color = '';
                        btn.style.opacity = '';
                    }, 2000);
                });
            });
            heading.appendChild(btn);
        }
    });

    // Copy code buttons - event delegation
    document.addEventListener('click', function(e) {
        const button = e.target.closest('.doc-copy-btn');
        if (!button) return;

        const codeBlock = button.closest('.doc-code-block');
        const code = codeBlock.querySelector('code').innerText;

        navigator.clipboard.writeText(code).then(() => {
            const originalText = button.textContent;
            button.textContent = 'Copied!';
            button.classList.add('text-green-400');

            setTimeout(() => {
                button.textContent = originalText;
                button.classList.remove('text-green-400');
            }, 2000);
        }).catch(() => {});
    });

    var headerOffset = 80;

    function scrollToTarget(target, behavior) {
        var top = target.getBoundingClientRect().top + window.scrollY - headerOffset;
        window.scrollTo({ top: top, behavior: behavior || 'smooth' });
    }

    // Accordion toggle for button headers (non-navigable, e.g. API docs)
    document.querySelectorAll('button.doc-nav-group-header').forEach(function(btn) {
        btn.addEventListener('click', function() {
            this.closest('.doc-nav-group').classList.toggle('expanded');
        });
    });

    // Chevron-only toggle for <a> headers (navigable, e.g. doc pages)
    document.querySelectorAll('a.doc-nav-group-header .doc-nav-chevron').forEach(function(chevron) {
        chevron.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.closest('.doc-nav-group').classList.toggle('expanded');
        });
    });

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                scrollToTarget(target);
                history.pushState(null, null, this.getAttribute('href'));
            }
        });
    });

    // Highlight active section in sidebar
    const sections = document.querySelectorAll('.doc-section');
    const navLinks = document.querySelectorAll('.doc-nav-link');

    function highlightNav() {
        let currentSection = '';

        sections.forEach(section => {
            const rect = section.getBoundingClientRect();
            if (rect.top <= headerOffset + 20) {
                currentSection = section.getAttribute('id');
            }
        });

        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === '#' + currentSection) {
                link.classList.add('active');
            }
        });

        // Auto-expand accordion group for active nav link
        var activeNavLink = document.querySelector('.doc-nav-link.active');
        if (activeNavLink) {
            var navGroup = activeNavLink.closest('.doc-nav-group');
            if (navGroup) {
                document.querySelectorAll('.doc-nav-group.expanded').forEach(function(g) {
                    if (g !== navGroup) g.classList.remove('expanded');
                });
                navGroup.classList.add('expanded');
            }
        }
    }

    // Scroll to hash on page load
    if (window.location.hash) {
        // Immediately highlight the matching nav link
        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === window.location.hash) {
                link.classList.add('active');
                // Expand accordion group if applicable
                var navGroup = link.closest('.doc-nav-group');
                if (navGroup) navGroup.classList.add('expanded');
            }
        });
        const target = document.querySelector(window.location.hash);
        if (target) {
            setTimeout(function() {
                scrollToTarget(target, 'instant');
                // Attach scroll listener after hash scroll settles
                setTimeout(function() { window.addEventListener('scroll', highlightNav); }, 50);
            }, 100);
        } else {
            window.addEventListener('scroll', highlightNav);
        }
    } else {
        window.addEventListener('scroll', highlightNav);
        highlightNav();
    }
</script>
