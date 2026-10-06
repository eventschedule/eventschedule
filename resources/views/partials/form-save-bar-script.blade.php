{{-- The script of partials/form-save-bar. $tabs: section id => the tab's name. --}}
@php
    // Built here, not inside the directive: a multi-line array argument does not compile.
    $saveBarWords = [
        'unsaved' => __('messages.unsaved'),
        'check' => __('messages.check_tabs'),
        'removes' => __('messages.saving_removes'),
        'unsaved_changes' => __('messages.unsaved_changes'),
        'no_unsaved' => __('messages.no_unsaved_changes'),
    ];
@endphp
<script {!! nonce_attr() !!}>window.Vue || document.write('<script src="{{ asset('js/vue.global.prod.js') }}" {!! nonce_attr() !!}><\/script>')</script>
<script {!! nonce_attr() !!}>
(function () {
    var root = document.getElementById('form-save-bar');
    if (! root || ! window.Vue) {
        return;
    }
    var form = root.closest('form');
    var tabs = @json($tabs, JSON_UNESCAPED_UNICODE);
    var words = @json($saveBarWords, JSON_UNESCAPED_UNICODE);

    var bar = window.Vue.createApp({
        data: function () {
            return {
                dirtyTabs: [],
                anyDirty: false,
                removes: [],
                removed: [],
                errors: [],
                saving: false,
                waiting: '',
                confirmingDiscard: false,
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
                if (named(this.dirtyTabs).length) {
                    return { kind: 'tabs', label: words.unsaved, tabs: named(this.dirtyTabs) };
                }
                // Unsaved, with no tab to name (a change made by script, or outside the tabs):
                // still never "No unsaved changes".
                if (this.anyDirty) {
                    return { kind: 'text', text: words.unsaved_changes };
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
                this.discard();
            },
            discard: function () {
                document.getElementById('form-cancel-real').click();
            },
        },
    }).mount(root);

    document.addEventListener('formkit:dirty', function (event) {
        bar.dirtyTabs = event.detail.sections || [];
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
    if (form) {
        form.addEventListener('input', function () { bar.anyDirty = true; });
        form.addEventListener('change', function () { bar.anyDirty = true; });
    }
    window._markFormDirty = function () { bar.anyDirty = true; };
    window.addEventListener('beforeunload', function (event) {
        if (bar.dirty && ! window._skipUnsavedWarning) {
            event.preventDefault();
            event.returnValue = '';
        }
    });
    window.FormSaveBar = bar;
})();
</script>
