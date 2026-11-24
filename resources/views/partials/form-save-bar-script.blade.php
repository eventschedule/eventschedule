{{-- The script of partials/form-save-bar. $tabs: section id => the tab's name. $scoped: the bar
     saves one section of a page whose other sections save on their own (the settings page's
     Profile): it hears only its own tabs' changes, as FormKit reports them once the page has
     armed, says "Unsaved changes" rather than naming the tab, Discard puts the section back by
     reloading the page instead of leaving it, and leaving the page is not asked about. --}}
@php
    // Built here, not inside the directive: a multi-line array argument does not compile.
    $saveBarWords = [
        'unsaved' => __('messages.unsaved'),
        'check' => __('messages.check_tabs'),
        'removes' => __('messages.saving_removes'),
        'unsaved_changes' => __('messages.unsaved_changes'),
        'no_unsaved' => __('messages.no_unsaved_changes'),
        'saved' => __('messages.saved'),
    ];
    $saveBarScoped = ! empty($scoped);
@endphp
<script {!! nonce_attr() !!}>window.Vue || document.write('<script src="{{ asset('js/vue.global.prod.js') }}" {!! nonce_attr() !!}><\/script>')</script>
<script {!! nonce_attr() !!}>
(function () {
    var root = document.getElementById('form-save-bar');
    if (! root || ! window.Vue) {
        return;
    }
    var form = root.closest('form') || (root.dataset.form ? document.getElementById(root.dataset.form) : null);
    var tabs = @json($tabs, JSON_UNESCAPED_UNICODE);
    var words = @json($saveBarWords, JSON_UNESCAPED_UNICODE);
    var scoped = @json($saveBarScoped);

    var bar = window.Vue.createApp({
        data: function () {
            return {
                dirtyTabs: [],
                anyDirty: false,
                removes: [],
                removed: [],
                // Sentences a page has for what Save will do that is not a removal, by key
                // (formkit:says): "Team members will pick from presets".
                says: {},
                errors: [],
                saving: false,
                waiting: '',
                confirmingDiscard: false,
                justSaved: root.dataset.saved === '1',
            };
        },
        computed: {
            dirty: function () {
                return this.anyDirty || this.dirtyTabs.length > 0 || this.removes.length > 0;
            },
            status: function () {
                var named = function (ids) {
                    return ids.filter(function (id) { return tabs[id]; }).map(function (id) { return { id: id, label: tabs[id] }; });
                };
                if (this.errors.length && named(this.errors).length) {
                    return { kind: 'tabs', label: words.check, tabs: named(this.errors) };
                }
                // What saving will remove, said before anything else that is not an error.
                if (this.removes.length && named(this.removes).length) {
                    // By the name of what was taken off, where it had one; each is a link to its tab.
                    var things = this.removed.filter(function (item) { return item.name && tabs[item.section]; }).map(function (item) {
                        return { id: item.section, label: item.name };
                    });
                    var unnamed = this.removes.filter(function (id) {
                        return ! things.some(function (thing) { return thing.id === id; });
                    });

                    return { kind: 'tabs', label: words.removes, tabs: things.concat(named(unnamed)), strong: true };
                }
                var said = Object.keys(this.says).map(function (key) { return this.says[key]; }, this).filter(Boolean);
                if (said.length) {
                    return { kind: 'text', text: said.join(' · '), strong: true };
                }
                if (named(this.dirtyTabs).length) {
                    // A bar for one section has no other tab to point at.
                    if (scoped) {
                        return { kind: 'text', text: words.unsaved_changes, strong: true };
                    }

                    return { kind: 'tabs', label: words.unsaved, tabs: named(this.dirtyTabs) };
                }
                // Unsaved, with no tab to name (a change made by script, or outside the tabs):
                // still never "No unsaved changes".
                if (this.anyDirty) {
                    return { kind: 'text', text: words.unsaved_changes };
                }

                if (this.justSaved) {
                    return { kind: 'text', text: words.saved, quiet: true };
                }

                return { kind: 'text', text: words.no_unsaved, quiet: true };
            },
        },
        methods: {
            goToTab: function (id) {
                if (window.FormKit && window.FormKit.showSection) {
                    window.FormKit.showSection(id);
                }
            },
            cancel: function () {
                if (this.dirty) {
                    this.confirmingDiscard = true;

                    return;
                }
                // Nothing to throw away in a section that saves on its own: the page stays.
                if (scoped) {
                    return;
                }
                this.discard();
            },
            discard: function () {
                if (scoped) {
                    window.location.reload();

                    return;
                }
                document.getElementById('form-cancel-real').click();
            },
        },
    }).mount(root);

    document.addEventListener('formkit:dirty', function (event) {
        bar.dirtyTabs = (event.detail.sections || []).filter(function (id) { return ! scoped || tabs[id]; });
        if (scoped) {
            bar.anyDirty = bar.dirtyTabs.length > 0;

            return;
        }
        if (event.detail.any !== undefined) {
            bar.anyDirty = !! event.detail.any;
        } else if (bar.dirtyTabs.length) {
            bar.anyDirty = true;
        }
    });
    document.addEventListener('formkit:removes', function (event) {
        bar.removes = event.detail.sections || [];
        bar.removed = event.detail.items || [];
    });
    document.addEventListener('formkit:says', function (event) {
        var says = Object.assign({}, bar.says);
        says[event.detail.key] = event.detail.text || '';
        bar.says = says;
    });
    document.addEventListener('formkit:errors', function (event) {
        bar.errors = event.detail.sections || [];
    });
    document.addEventListener('formkit:saving', function (event) {
        bar.saving = event.detail.saving !== false;
        bar.waiting = event.detail.waiting || '';
        if (bar.saving && ! bar.waiting) {
            window._skipUnsavedWarning = true;
        }
    });

    // Leaving with unsaved changes asks first, unless the bar's own Discard is why, or a save
    // that got past the page's checks (which is when the page says formkit:saving).
    // A scoped bar hears its section through FormKit alone, which waits until the page has set
    // itself up and leaves out what is not part of the save (data-no-dirty: the theme picker).
    if (form && ! scoped) {
        form.addEventListener('input', function () { bar.anyDirty = true; });
        form.addEventListener('change', function () { bar.anyDirty = true; });
    }

    window._markFormDirty = function () { bar.anyDirty = true; };
    // Not for a scoped bar: the page's other sections save (and reload it) on their own, and the
    // settings page has never asked before leaving; its dot on the tab says what is unsaved.
    window.addEventListener('beforeunload', function (event) {
        if (bar.dirty && ! scoped && ! window._skipUnsavedWarning) {
            event.preventDefault();
            event.returnValue = '';
        }
    });
    window.FormSaveBar = bar;
})();
</script>
