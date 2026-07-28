/**
 * Documentation behaviour.
 *
 * Loaded only by the docs layout (see the `docs` prop on MarketingLayout).
 * Replaces the 148-line <script> partial that was inlined on 30 of the 41 doc
 * pages - gift-cards and subscriptions never included it, so their copy-links
 * and scroll-spy were simply dead.
 *
 * Vanilla JS on purpose. The marketing bundle exposes Vue's RUNTIME-ONLY
 * build (`window.Vue = { createApp }` with no compiler), so an in-DOM Vue
 * template renders empty here.
 *
 * Everything is null-guarded and scoped: in particular the scroll-spy is
 * scoped to the "On this page" rail, because the left rail now shares the
 * .doc-nav-link class and an unscoped querySelectorAll would strip its state
 * on every scroll tick.
 */

const HEADER_OFFSET = 80;
const REDUCED_MOTION = window.matchMedia('(prefers-reduced-motion: reduce)');

const COPY_ICON =
    '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>';
const CHECK_ICON =
    '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>';

function scrollToTarget(target, behavior) {
    const top = target.getBoundingClientRect().top + window.scrollY - HEADER_OFFSET;
    window.scrollTo({ top, behavior: behavior || 'smooth' });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;

    return div.innerHTML;
}

/* -------------------------------------------------------------------------
 * Copy buttons
 * ---------------------------------------------------------------------- */

function initHeadingCopy() {
    document.querySelectorAll('.doc-heading').forEach((heading) => {
        const section = heading.closest('.doc-section');
        const targetId = heading.id || (section && section.id);

        if (!targetId) {
            return;
        }

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'doc-heading-copy';
        btn.title = 'Copy link to section';
        btn.setAttribute('aria-label', 'Copy link to this section');
        btn.innerHTML = COPY_ICON;

        btn.addEventListener('click', () => {
            const url = window.location.origin + window.location.pathname + '#' + targetId;

            navigator.clipboard.writeText(url).then(() => {
                btn.innerHTML = CHECK_ICON;
                btn.style.color = '#10b981';
                btn.style.opacity = '1';

                setTimeout(() => {
                    btn.innerHTML = COPY_ICON;
                    btn.style.color = '';
                    btn.style.opacity = '';
                }, 2000);
            }).catch(() => {});
        });

        heading.appendChild(btn);
    });
}

function initCodeCopy() {
    document.addEventListener('click', (e) => {
        const button = e.target.closest('.doc-copy-btn');

        if (!button) {
            return;
        }

        const codeBlock = button.closest('.doc-code-block');
        const code = codeBlock && codeBlock.querySelector('code');

        if (!code) {
            return;
        }

        navigator.clipboard.writeText(code.innerText).then(() => {
            const originalText = button.textContent;
            button.textContent = 'Copied!';
            button.classList.add('text-green-400');

            setTimeout(() => {
                button.textContent = originalText;
                button.classList.remove('text-green-400');
            }, 2000);
        }).catch(() => {});
    });
}

/* -------------------------------------------------------------------------
 * Smooth scroll
 *
 * Delegated rather than bound per element, so anchors inside the drawers and
 * the search dropdown work too. [data-no-smooth] opts out.
 * ---------------------------------------------------------------------- */

function initSmoothScroll() {
    document.addEventListener('click', (e) => {
        const anchor = e.target.closest('a[href^="#"]');

        if (!anchor || anchor.hasAttribute('data-no-smooth')) {
            return;
        }

        const href = anchor.getAttribute('href');

        if (!href || href === '#') {
            return;
        }

        const target = document.querySelector(href);

        if (!target) {
            return;
        }

        e.preventDefault();
        scrollToTarget(target);
        history.pushState(null, '', href);
        closeDrawers();
    });
}

/* -------------------------------------------------------------------------
 * Right rail: scroll-spy + the moving indicator
 *
 * An IntersectionObserver rather than a scroll listener. The old version
 * compared each section's bounding box against the header offset, which could
 * never activate the final section - which is why .prose-dark carried a
 * `padding-bottom: 20vh` spacer to fake enough runway.
 * ---------------------------------------------------------------------- */

function initScrollSpy() {
    const toc = document.getElementById('docs-toc');

    if (!toc) {
        return;
    }

    const links = Array.from(toc.querySelectorAll('.doc-toc-link'));
    const list = toc.querySelector('.doc-toc-list');
    const sections = Array.from(document.querySelectorAll('.doc-section[id]'));

    if (!links.length || !sections.length) {
        return;
    }

    const byId = new Map();
    links.forEach((link) => {
        const id = (link.getAttribute('href') || '').replace(/^#/, '');
        if (id) {
            byId.set(id, link);
        }
    });

    const visible = new Set();

    function paint() {
        let active = null;

        // The topmost visible section wins.
        for (const section of sections) {
            if (visible.has(section.id) && byId.has(section.id)) {
                active = byId.get(section.id);
                break;
            }
        }

        links.forEach((link) => link.classList.toggle('active', link === active));
        moveRail(list, active);
        expandGroupFor(toc, active);
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                visible.add(entry.target.id);
            } else {
                visible.delete(entry.target.id);
            }
        });

        paint();
    }, { rootMargin: '-88px 0px -55% 0px', threshold: 0 });

    sections.forEach((section) => observer.observe(section));

    // Keep the indicator correct when the rail itself scrolls or the viewport
    // resizes, since both change offsetTop-relative geometry.
    window.addEventListener('resize', () => moveRail(list, toc.querySelector('.doc-toc-link.active')));
}

function moveRail(list, active) {
    if (!list) {
        return;
    }

    if (!active) {
        list.style.setProperty('--toc-o', '0');

        return;
    }

    list.style.setProperty('--toc-y', active.offsetTop + 'px');
    list.style.setProperty('--toc-h', active.offsetHeight + 'px');
    list.style.setProperty('--toc-o', '1');
}

/**
 * Expand the accordion group holding the active link, collapsing its
 * siblings. Scoped to the given root - the left rail must never be touched.
 */
function expandGroupFor(root, active) {
    if (!active) {
        return;
    }

    const group = active.closest('.doc-nav-group');

    if (!group) {
        return;
    }

    root.querySelectorAll('.doc-nav-group.expanded').forEach((other) => {
        if (other !== group) {
            other.classList.remove('expanded');
        }
    });

    group.classList.add('expanded');
}

/* -------------------------------------------------------------------------
 * Accordion groups (right rail only)
 * ---------------------------------------------------------------------- */

function initAccordions() {
    const toc = document.getElementById('docs-toc');

    if (!toc) {
        return;
    }

    // Non-navigable headers toggle on click.
    toc.querySelectorAll('button.doc-nav-group-header').forEach((btn) => {
        btn.addEventListener('click', () => {
            btn.closest('.doc-nav-group').classList.toggle('expanded');
        });
    });

    // Navigable headers keep their link; only the chevron toggles.
    toc.querySelectorAll('a.doc-nav-group-header .doc-nav-chevron').forEach((chevron) => {
        chevron.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            chevron.closest('.doc-nav-group').classList.toggle('expanded');
        });
    });
}

/* -------------------------------------------------------------------------
 * Landing on an anchor
 * ---------------------------------------------------------------------- */

function initHashLanding() {
    if (!window.location.hash) {
        return;
    }

    const target = document.querySelector(window.location.hash);

    if (!target) {
        return;
    }

    // Suppress CSS smooth scroll so it does not fight the instant jump.
    document.documentElement.style.scrollBehavior = 'auto';

    setTimeout(() => {
        scrollToTarget(target, 'instant');
        requestAnimationFrame(() => {
            document.documentElement.style.scrollBehavior = '';
        });
    }, 100);
}

/* -------------------------------------------------------------------------
 * Mobile bar and drawers
 * ---------------------------------------------------------------------- */

const FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled]), select, textarea, [tabindex]:not([tabindex="-1"])';

let openDrawer = null;
let lastFocused = null;
let trapHandler = null;

function lockScroll() {
    const gap = window.innerWidth - document.documentElement.clientWidth;
    document.body.style.overflow = 'hidden';

    if (gap > 0) {
        document.body.style.paddingRight = gap + 'px';
    }
}

function unlockScroll() {
    document.body.style.overflow = '';
    document.body.style.paddingRight = '';
}

function closeDrawers() {
    if (!openDrawer) {
        return;
    }

    const backdrop = document.getElementById('docs-drawer-backdrop');

    openDrawer.classList.remove('is-open', 'doc-drawer-active');
    openDrawer.removeAttribute('role');
    openDrawer.removeAttribute('aria-modal');

    if (backdrop) {
        backdrop.classList.remove('is-open');
    }

    document.querySelectorAll('[data-docs-drawer-toggle]').forEach((btn) => {
        btn.setAttribute('aria-expanded', 'false');
    });

    if (trapHandler) {
        document.removeEventListener('keydown', trapHandler);
        trapHandler = null;
    }

    unlockScroll();
    openDrawer = null;

    if (lastFocused && document.contains(lastFocused)) {
        lastFocused.focus();
    }

    lastFocused = null;
}

function openDrawerPanel(panel, toggle) {
    if (!panel) {
        return;
    }

    if (openDrawer === panel) {
        closeDrawers();

        return;
    }

    closeDrawers();

    const backdrop = document.getElementById('docs-drawer-backdrop');

    lastFocused = toggle || document.activeElement;
    openDrawer = panel;

    panel.classList.add('is-open');
    panel.setAttribute('role', 'dialog');
    panel.setAttribute('aria-modal', 'true');

    if (backdrop) {
        backdrop.classList.add('is-open');
    }

    if (toggle) {
        toggle.setAttribute('aria-expanded', 'true');
    }

    lockScroll();

    const focusables = panel.querySelectorAll(FOCUSABLE);

    if (focusables.length) {
        focusables[0].focus();
    } else {
        panel.setAttribute('tabindex', '-1');
        panel.focus();
    }

    // Trap Tab inside the panel. Deliberately NOT `inert` on <main>: the
    // drawers live inside it, and inert-ing an ancestor of the focused element
    // is a well-known footgun.
    trapHandler = (e) => {
        if (e.key === 'Escape') {
            e.preventDefault();
            closeDrawers();

            return;
        }

        if (e.key !== 'Tab') {
            return;
        }

        const items = Array.from(panel.querySelectorAll(FOCUSABLE)).filter((el) => el.offsetParent !== null);

        if (!items.length) {
            return;
        }

        const first = items[0];
        const last = items[items.length - 1];

        if (e.shiftKey && document.activeElement === first) {
            e.preventDefault();
            last.focus();
        } else if (!e.shiftKey && document.activeElement === last) {
            e.preventDefault();
            first.focus();
        }
    };

    document.addEventListener('keydown', trapHandler);
}

function initDrawers() {
    const backdrop = document.getElementById('docs-drawer-backdrop');

    document.querySelectorAll('[data-docs-drawer-toggle]').forEach((toggle) => {
        toggle.addEventListener('click', () => {
            const panel = document.getElementById(toggle.getAttribute('data-docs-drawer-toggle'));
            openDrawerPanel(panel, toggle);
        });
    });

    if (backdrop) {
        backdrop.addEventListener('click', closeDrawers);
    }

    // A drawer is a mobile affordance; if the viewport grows past the
    // breakpoint the rails become columns again and the drawer must go.
    const desktop = window.matchMedia('(min-width: 1024px)');
    const onChange = (e) => {
        if (e.matches) {
            closeDrawers();
        }
    };

    if (desktop.addEventListener) {
        desktop.addEventListener('change', onChange);
    } else if (desktop.addListener) {
        desktop.addListener(onChange);
    }
}

function initMobileBar() {
    const bar = document.querySelector('.doc-mobilebar');

    if (!bar) {
        return;
    }

    let lastY = window.scrollY;
    let ticking = false;

    window.addEventListener('scroll', () => {
        if (ticking) {
            return;
        }

        ticking = true;

        requestAnimationFrame(() => {
            const y = window.scrollY;
            const delta = y - lastY;

            // Ignore jitter and the rubber-band region at the top.
            if (Math.abs(delta) > 6 && y > 160 && !openDrawer) {
                bar.classList.toggle('is-hidden', delta > 0);
            } else if (y <= 160) {
                bar.classList.remove('is-hidden');
            }

            lastY = y;
            ticking = false;
        });
    }, { passive: true });
}

/* -------------------------------------------------------------------------
 * Rail filter (API reference: filter the endpoint list in place)
 * ---------------------------------------------------------------------- */

function initRailFilter() {
    document.querySelectorAll('[data-docs-filter]').forEach((input) => {
        const scope = document.getElementById(input.getAttribute('data-docs-filter'));

        if (!scope) {
            return;
        }

        input.addEventListener('input', () => {
            const q = input.value.trim().toLowerCase();

            scope.querySelectorAll('.doc-nav-link').forEach((link) => {
                const haystack = ((link.getAttribute('data-search') || '') + ' ' + link.textContent).toLowerCase();
                link.style.display = !q || haystack.includes(q) ? '' : 'none';
            });

            // While filtering, open every group so matches are never hidden
            // behind a collapsed header.
            scope.querySelectorAll('.doc-nav-group').forEach((group) => {
                if (q) {
                    group.classList.add('expanded');
                } else {
                    group.classList.remove('expanded');
                }

                const hasVisible = Array.from(group.querySelectorAll('.doc-nav-link'))
                    .some((link) => link.style.display !== 'none');

                group.style.display = !q || hasVisible ? '' : 'none';
            });
        });
    });
}

/* -------------------------------------------------------------------------
 * Search
 *
 * The 364-entry index is ~97 KB raw / ~18 KB gzipped, so it is fetched from a
 * cached endpoint on first use rather than inlined into all 41 doc pages.
 * ---------------------------------------------------------------------- */

const CATEGORY_CLASS = {
    'User Guide': 'bg-cyan-100 text-cyan-700 dark:bg-cyan-500/20 dark:text-cyan-300',
    Selfhost: 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300',
    SaaS: 'bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300',
    Developer: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300',
};

let indexPromise = null;

function loadIndex(url) {
    if (!indexPromise) {
        indexPromise = fetch(url, { headers: { Accept: 'application/json' } })
            .then((r) => (r.ok ? r.json() : []))
            .catch(() => []);
    }

    return indexPromise;
}

function scoreIndex(index, query) {
    const q = query.trim().toLowerCase();

    if (q.length < 2) {
        return [];
    }

    const terms = q.split(/\s+/);
    const scored = [];

    for (const item of index) {
        const section = (item.section || '').toLowerCase();
        const page = (item.page || '').toLowerCase();
        const desc = (item.description || '').toLowerCase();
        const keywords = (item.keywords || '').toLowerCase();
        const all = section + ' ' + page + ' ' + desc + ' ' + keywords;

        if (!terms.every((term) => all.includes(term))) {
            continue;
        }

        let score = 0;

        for (const term of terms) {
            if (section.includes(term)) score += 10;
            if (page.includes(term)) score += 5;
            if (desc.includes(term)) score += 3;
            if (keywords.includes(term)) score += 1;
            if (section === q) score += 20;
        }

        if (item.category === 'User Guide') score += 2;
        else if (item.category === 'Developer') score += 1;

        scored.push({ item, score });
    }

    scored.sort((a, b) => b.score - a.score);

    return scored.slice(0, 8).map((s) => s.item);
}

function highlight(text, query) {
    const q = query.trim().toLowerCase();
    const escaped = escapeHtml(text || '');

    if (q.length < 2) {
        return escaped;
    }

    const terms = q.split(/\s+/).filter(Boolean);

    if (!terms.length) {
        return escaped;
    }

    const pattern = terms.map((t) => t.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')).join('|');

    return escaped.replace(
        new RegExp('(' + pattern + ')', 'gi'),
        '<mark class="bg-yellow-200/80 dark:bg-yellow-500/30 text-inherit rounded px-0.5">$1</mark>'
    );
}

function initSearch(root) {
    const input = root.querySelector('[data-role="input"]');
    const panel = root.querySelector('[data-role="results"]');
    const clear = root.querySelector('[data-role="clear"]');
    const hint = root.querySelector('[data-role="hint"]');
    const endpoint = root.getAttribute('data-docs-search');

    if (!input || !panel || !endpoint) {
        return;
    }

    let results = [];
    let selected = -1;
    let index = null;
    let debounce = null;

    function close() {
        panel.hidden = true;
        input.setAttribute('aria-expanded', 'false');
        selected = -1;
    }

    function render() {
        if (!results.length) {
            panel.innerHTML = input.value.trim().length >= 2
                ? '<p class="px-4 py-3 text-center text-sm text-gray-500 dark:text-gray-400">No results found.</p>'
                : '';
            panel.hidden = input.value.trim().length < 2;
            input.setAttribute('aria-expanded', panel.hidden ? 'false' : 'true');

            return;
        }

        const q = input.value;

        panel.innerHTML = results.map((item, i) => {
            const cat = CATEGORY_CLASS[item.category] || 'bg-gray-100 text-gray-700 dark:bg-gray-500/20 dark:text-gray-300';
            const active = i === selected ? ' bg-gray-50 dark:bg-white/5' : '';
            const icon = item.icon
                ? '<svg class="h-3.5 w-3.5 flex-shrink-0 text-gray-400 dark:text-gray-500" aria-hidden="true"><use href="#docs-icon-' + escapeHtml(item.icon) + '"></use></svg>'
                : '';

            return '<a href="' + escapeHtml(item.url) + '" role="option" data-i="' + i + '" aria-selected="' + (i === selected) + '"' +
                ' class="block border-b border-gray-100 px-4 py-3 last:border-0 hover:bg-gray-50 dark:border-white/5 dark:hover:bg-white/5' + active + '">' +
                '<span class="mb-0.5 flex items-center gap-2">' + icon +
                '<span class="rounded px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wider ' + cat + '">' + escapeHtml(item.category) + '</span>' +
                '<span class="truncate text-xs text-gray-400 dark:text-gray-500">' + escapeHtml(item.page) + '</span>' +
                '</span>' +
                '<span class="block text-left text-sm font-medium text-gray-900 dark:text-white">' + highlight(item.section, q) + '</span>' +
                '<span class="mt-0.5 block text-left text-xs text-gray-500 dark:text-gray-400">' + highlight(item.description, q) + '</span>' +
                '</a>';
        }).join('');

        panel.hidden = false;
        input.setAttribute('aria-expanded', 'true');
    }

    function run() {
        const value = input.value;

        if (clear) {
            clear.hidden = value.length === 0;
        }

        if (hint) {
            hint.hidden = value.length > 0;
        }

        if (value.trim().length < 2) {
            results = [];
            close();

            return;
        }

        loadIndex(endpoint).then((data) => {
            index = data;
            results = scoreIndex(index, input.value);
            selected = -1;
            render();
        });
    }

    input.addEventListener('input', () => {
        clearTimeout(debounce);
        debounce = setTimeout(run, 150);
    });

    // Warm the index on focus so the first keystroke does not wait on a fetch.
    input.addEventListener('focus', () => {
        loadIndex(endpoint);

        if (input.value.trim().length >= 2) {
            run();
        }
    });

    input.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            close();
            input.blur();

            return;
        }

        if (!results.length) {
            return;
        }

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            selected = (selected + 1) % results.length;
            render();
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            selected = selected <= 0 ? results.length - 1 : selected - 1;
            render();
        } else if (e.key === 'Enter' && selected >= 0) {
            e.preventDefault();
            window.location.href = results[selected].url;
        }
    });

    if (clear) {
        clear.addEventListener('click', () => {
            input.value = '';
            results = [];
            close();
            run();
            input.focus();
        });
    }

    document.addEventListener('click', (e) => {
        if (!root.contains(e.target)) {
            close();
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key !== '/' || openDrawer) {
            return;
        }

        const el = document.activeElement;

        if (['INPUT', 'TEXTAREA', 'SELECT'].includes(el.tagName) || el.isContentEditable) {
            return;
        }

        // Only the visible search box claims the shortcut: a leaf page renders
        // one in the rail, the index renders one in the hero.
        if (root.offsetParent === null) {
            return;
        }

        e.preventDefault();
        input.focus();
    });
}

/* -------------------------------------------------------------------------
 * Boot
 * ---------------------------------------------------------------------- */

function boot() {
    initHeadingCopy();
    initCodeCopy();
    initSmoothScroll();
    initAccordions();
    initScrollSpy();
    initHashLanding();
    initDrawers();
    initRailFilter();

    if (!REDUCED_MOTION.matches) {
        initMobileBar();
    }

    document.querySelectorAll('[data-docs-search]').forEach(initSearch);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
} else {
    boot();
}
