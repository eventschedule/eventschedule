{{--
    The organizer photo gallery editor: a Vue component (window.EsGallery.component) and the store
    it edits (window.EsGallery.createStore()), shared by the event form (registered on its #app)
    and the schedule form (mounted as its own island). Included once per page, after the markup
    and before the script that creates the Vue app using it.

    How it saves: every photo is uploaded the moment it is added, as a DRAFT (GalleryController),
    and the form posts the gallery's final order as JSON in `gallery_images` with the rest of its
    fields. The form's Save commits that list (GalleryUtils::sync()); nothing is published before.

    The template is a JS string with no server data in it. Captions, credits and fan-photo names
    are the owner's and guests' text, so they arrive as JSON and are only ever bound (:value,
    {{ }}), never compiled.

    Photos are shrunk in the browser (2000px on the long edge, re-encoded as JPEG unless a PNG)
    before upload, so a phone photo takes seconds and fits under PHP's upload limit. Previews are
    data: URLs, since the CSP's img-src has no blob:.
--}}
@php
    $galleryLabels = [
        'add_photos' => __('messages.gallery_add_photos'),
        'drop_hint' => __('messages.gallery_drop_hint', ['max' => \App\Utils\GalleryUtils::maxImages()]),
        'count' => __('messages.gallery_count'),
        'full' => __('messages.gallery_full'),
        'order_hint' => __('messages.gallery_order_hint'),
        'finishing' => __('messages.gallery_finishing'),
        'uploading' => __('messages.gallery_uploading'),
        'preparing' => __('messages.gallery_preparing'),
        'too_large' => __('messages.gallery_too_large', ['size' => round(\App\Utils\GalleryUtils::maxUploadBytes() / 1048576, 1).' MB']),
        'upload_failed' => __('messages.gallery_upload_failed'),
        'invalid_type' => __('messages.gallery_invalid_type'),
        'heic' => __('messages.gallery_heic'),
        'retry' => __('messages.retry'),
        'retry_all' => __('messages.gallery_retry_all'),
        'over_limit' => __('messages.gallery_over_limit'),
        'duplicates_one' => trans_choice('messages.gallery_duplicates', 1, ['count' => 1]),
        'duplicates_many' => __('messages.gallery_duplicates_many'),
        'photo_n' => __('messages.gallery_photo_n'),
        'edit_photo' => __('messages.gallery_edit_photo'),
        'caption' => __('messages.gallery_caption'),
        'caption_placeholder' => __('messages.gallery_caption_placeholder'),
        'credit' => __('messages.gallery_credit'),
        'credit_placeholder' => __('messages.gallery_credit_placeholder'),
        'apply_credit_all' => __('messages.gallery_apply_credit_all'),
        'credit_applied' => __('messages.gallery_credit_applied'),
        'move_earlier' => __('messages.gallery_move_earlier'),
        'move_later' => __('messages.gallery_move_later'),
        'move_to_start' => __('messages.gallery_move_to_start'),
        'moved' => __('messages.gallery_moved'),
        'remove' => __('messages.gallery_remove'),
        'removed' => __('messages.gallery_removed'),
        'removed_many' => __('messages.gallery_removed_many'),
        'undo' => __('messages.gallery_undo'),
        'remove_all' => __('messages.gallery_remove_all'),
        'more_actions' => __('messages.gallery_more_actions'),
        'from_fan' => __('messages.gallery_from_fan'),
        'fan_ready' => __('messages.gallery_fan_ready'),
        'fan_title' => __('messages.gallery_fan_title'),
        'fan_add_selected' => __('messages.gallery_fan_add_selected'),
        'photo_by' => __('messages.gallery_photo_by'),
        'cancel' => __('messages.cancel'),
        'close' => __('messages.close'),
        'done' => __('messages.done'),
        'previous' => __('messages.previous'),
        'next' => __('messages.next'),
        'has_caption' => __('messages.gallery_has_caption'),
        'failed_before_save' => __('messages.gallery_failed_before_save'),
    ];
@endphp



<script {!! nonce_attr() !!}>
(function () {
    if (window.EsGallery) {
        return;
    }


    // The editor's few rules that Tailwind classes cannot express. Added from here rather than a
    // style element in the markup: on the event form this partial sits inside the Vue app's
    // template, and Vue drops style elements when it compiles one.
    var css = [
        // The remove button is always visible where there is no hover (touch), and on hover or
        // focus elsewhere, so a phone never has to guess where it is.
        '@media (hover: hover) {',
        '  .es-gallery-tile .es-gallery-remove { opacity: 0; }',
        '  .es-gallery-tile:hover .es-gallery-remove, .es-gallery-tile:focus-within .es-gallery-remove { opacity: 1; }',
        '}',
        '.es-gallery-tile.sortable-ghost { opacity: 0.35; }',
        '.es-gallery-tile.sortable-chosen { box-shadow: 0 10px 25px -5px rgb(0 0 0 / 0.35); }',
        // The event form sizes every button in it for its text buttons (min-width 100px), which
        // would stretch the round remove buttons and the square tiles out of shape.
        '.es-gallery button { min-width: 0; min-height: 0; }',
    ].join('\n');
    var styleEl = document.createElement('style');
    styleEl.textContent = css;
    document.head.appendChild(styleEl);

    var L = @json($galleryLabels, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG);
    var MAX_DIMENSION = {{ \App\Utils\GalleryUtils::MAX_DIMENSION }};
    var CONCURRENCY = 3;

    function t(text, params) {
        var out = String(text || '');
        Object.keys(params || {}).forEach(function (key) {
            out = out.split(':' + key).join(String(params[key]));
        });
        return out;
    }

    function uid() {
        return 'g' + Math.random().toString(36).slice(2, 10) + Date.now().toString(36);
    }

    function isHeic(file) {
        return /\.(heic|heif)$/i.test(file.name || '') || /image\/hei[cf]/i.test(file.type || '');
    }

    function fromServer(image) {
        return {
            key: uid(),
            id: image.id,
            url: image.url,
            thumb: image.thumb_url || image.url,
            width: image.width || null,
            height: image.height || null,
            color: image.color || null,
            caption: image.caption || '',
            credit: image.credit || '',
            status: 'done',
            progress: 100,
            error: '',
            file: null,
            blob: null,
            name: '',
        };
    }

    // ---- Client-side preparation -------------------------------------------------------------

    function decode(file) {
        var viaBitmap = typeof createImageBitmap === 'function'
            ? createImageBitmap(file, { imageOrientation: 'from-image' }).catch(function () { return createImageBitmap(file); })
            : Promise.reject(new Error('no bitmap'));

        return viaBitmap.catch(function () {
            return new Promise(function (resolve, reject) {
                var reader = new FileReader();
                reader.onload = function () {
                    var img = new Image();
                    img.onload = function () { resolve(img); };
                    img.onerror = reject;
                    img.src = reader.result;
                };
                reader.onerror = reject;
                reader.readAsDataURL(file);
            });
        });
    }

    function drawTo(source, maxSide, opaque) {
        var width = source.naturalWidth || source.width;
        var height = source.naturalHeight || source.height;
        var scale = Math.min(1, maxSide / Math.max(width, height));
        var canvas = document.createElement('canvas');
        canvas.width = Math.max(1, Math.round(width * scale));
        canvas.height = Math.max(1, Math.round(height * scale));
        var ctx = canvas.getContext('2d');
        if (opaque) {
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
        }
        ctx.imageSmoothingQuality = 'high';
        ctx.drawImage(source, 0, 0, canvas.width, canvas.height);
        return canvas;
    }

    function toBlob(canvas, type, quality) {
        return new Promise(function (resolve, reject) {
            canvas.toBlob(function (blob) { blob ? resolve(blob) : reject(new Error('encode')); }, type, quality);
        });
    }

    function averageColor(source) {
        try {
            var canvas = drawTo(source, 1, true);
            var d = canvas.getContext('2d').getImageData(0, 0, 1, 1).data;
            return '#' + [d[0], d[1], d[2]].map(function (v) { return ('0' + v.toString(16)).slice(-2); }).join('');
        } catch (e) {
            return null;
        }
    }

    function prepare(file, maxBytes) {
        return decode(file).then(function (source) {
            var width = source.naturalWidth || source.width;
            var height = source.naturalHeight || source.height;
            var preview = drawTo(source, 480, true).toDataURL('image/jpeg', 0.75);
            var color = averageColor(source);
            var base = (file.name || 'photo').replace(/\.[^.]+$/, '') || 'photo';
            var done = function (blob, ext) {
                if (source.close) { source.close(); }
                if (blob.size > maxBytes) { throw new Error(L.too_large); }
                return { blob: blob, name: base + '.' + ext, preview: preview, width: width, height: height, color: color };
            };

            // An animated GIF would lose its frames to a canvas, so it goes as it is.
            if (file.type === 'image/gif') {
                return done(file, 'gif');
            }

            var png = file.type === 'image/png';
            var canvas = drawTo(source, MAX_DIMENSION, ! png);
            var scaledW = canvas.width;
            var scaledH = canvas.height;
            width = scaledW;
            height = scaledH;

            var encode = png
                ? toBlob(canvas, 'image/png').then(function (blob) {
                    return blob.size <= maxBytes ? { blob: blob, ext: 'png' } : toBlob(drawTo(canvas, MAX_DIMENSION, true), 'image/jpeg', 0.85).then(function (jpg) { return { blob: jpg, ext: 'jpg' }; });
                })
                : toBlob(canvas, 'image/jpeg', 0.85).then(function (blob) {
                    return blob.size <= maxBytes ? { blob: blob, ext: 'jpg' } : toBlob(canvas, 'image/jpeg', 0.7).then(function (smaller) { return { blob: smaller, ext: 'jpg' }; });
                });

            return encode.then(function (result) { return done(result.blob, result.ext); });
        }, function () {
            throw new Error(isHeic(file) ? L.heic : L.invalid_type);
        });
    }

    // ---- The store -----------------------------------------------------------------------------

    /**
     * cfg: uploadUrl, fanPhotosUrl, target ('event' | 'new_event' | 'schedule'), eventHash, draftToken,
     * csrf, max, maxBytes, initialImages, onChange.
     */
    function createStore(cfg) {
        var reactive = Vue.reactive;
        var markRaw = Vue.markRaw;
        var queue = [];
        var idleWaiters = [];
        var seen = {};
        var removedTimer = null;
        var noticeTimer = null;

        var state = reactive({
            images: (cfg.initialImages || []).map(fromServer),
            active: 0,
            batchTotal: 0,
            batchDone: 0,
            removed: null,
            notice: '',
            live: '',
            importing: false,
            max: cfg.max,
            initialSignature: '',
        });

        function changed() {
            if (typeof cfg.onChange === 'function') {
                cfg.onChange();
            }
        }

        function announce(text) {
            state.live = '';
            setTimeout(function () { state.live = text; }, 30);
        }

        function notify(text) {
            state.notice = text;
            clearTimeout(noticeTimer);
            noticeTimer = setTimeout(function () { state.notice = ''; }, 8000);
        }

        function pendingCount() {
            return state.images.filter(function (image) {
                return image.status === 'queued' || image.status === 'processing' || image.status === 'uploading';
            }).length;
        }

        // Releases a waiting Save once nothing the gallery shows is still on its way - after an
        // upload finishes, and after a pending photo is removed, which may be the one it waited on.
        function settle() {
            if (pendingCount() > 0) {
                return;
            }
            state.batchTotal = 0;
            state.batchDone = 0;
            var waiters = idleWaiters;
            idleWaiters = [];
            waiters.forEach(function (resolve) { resolve(); });
        }

        function isShown(item) {
            return state.images.indexOf(item) !== -1;
        }

        function upload(item) {
            return new Promise(function (resolve) {
                var xhr = new XMLHttpRequest();
                item.xhr = markRaw(xhr);
                xhr.open('POST', cfg.uploadUrl);
                // A stalled request must end somewhere, or a Save waiting on it never goes.
                xhr.timeout = 120000;
                xhr.setRequestHeader('X-CSRF-TOKEN', cfg.csrf);
                xhr.setRequestHeader('Accept', 'application/json');
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                xhr.upload.onprogress = function (e) {
                    if (e.lengthComputable) {
                        item.progress = Math.max(2, Math.round(e.loaded / e.total * 100));
                    }
                };
                xhr.onload = function () {
                    item.xhr = null;
                    var data = null;
                    try { data = JSON.parse(xhr.responseText); } catch (e) {}
                    if (xhr.status >= 200 && xhr.status < 300 && data && data.success && data.image) {
                        item.id = data.image.id;
                        item.url = data.image.url;
                        item.width = data.image.width || item.width;
                        item.height = data.image.height || item.height;
                        item.color = data.image.color || item.color;
                        item.status = 'done';
                        item.progress = 100;
                        item.blob = null;
                    } else {
                        item.status = 'error';
                        item.error = xhr.status === 413 ? L.too_large : ((data && data.error) || L.upload_failed);
                    }
                    resolve();
                };
                xhr.onerror = xhr.ontimeout = xhr.onabort = function () {
                    item.xhr = null;
                    item.status = 'error';
                    item.error = L.upload_failed;
                    resolve();
                };

                var form = new FormData();
                form.append('photo', item.blob, item.name);
                form.append('target', cfg.target);
                form.append('draft_token', cfg.draftToken);
                if (cfg.eventHash) {
                    form.append('event', cfg.eventHash);
                }
                xhr.send(form);
            });
        }

        function process(item) {
            var ready = item.blob
                ? Promise.resolve()
                : (function () {
                    item.status = 'processing';
                    return prepare(item.file, cfg.maxBytes).then(function (prepared) {
                        item.blob = markRaw(prepared.blob);
                        item.name = prepared.name;
                        item.thumb = prepared.preview;
                        item.width = prepared.width;
                        item.height = prepared.height;
                        item.color = prepared.color;
                    });
                })();

            return ready.then(function () {
                // Removed while it was being prepared: back to waiting, so an Undo picks it up
                // again, and nothing is uploaded for a photo that is no longer in the gallery.
                if (! isShown(item)) {
                    item.status = 'queued';
                    return;
                }
                item.status = 'uploading';
                item.progress = 2;
                return upload(item);
            }).catch(function (e) {
                item.status = 'error';
                item.error = (e && e.message) || L.upload_failed;
            });
        }

        function pump() {
            while (state.active < CONCURRENCY && queue.length) {
                var item = queue.shift();
                // Only a photo still waiting and still in the gallery. A queue can hold one twice
                // (removed, then brought back by Undo) and the second copy must not upload again.
                if (item.status !== 'queued' || ! isShown(item)) {
                    continue;
                }
                state.active++;
                process(item).then(function () {
                    state.active--;
                    state.batchDone++;
                    settle();
                    changed();
                    pump();
                });
            }
        }

        function enqueue(item) {
            item.status = 'queued';
            item.error = '';
            item.progress = 0;
            queue.push(item);
            state.batchTotal++;
        }

        // Every tile counts, failed ones included: a failed photo can be retried, and retrying
        // must never take the gallery past its limit.
        function roomLeft() {
            return state.max - state.images.length;
        }

        function forget(image) {
            if (image.sig) {
                delete seen[image.sig];
            }
            var at = queue.indexOf(image);
            if (at !== -1) {
                queue.splice(at, 1);
            }
            if (image.xhr) {
                try { image.xhr.abort(); } catch (e) {}
            }
        }

        function remember(image) {
            if (image.sig) {
                seen[image.sig] = true;
            }
            if (image.status === 'queued' && queue.indexOf(image) === -1) {
                queue.push(image);
            }
        }

        var store = {
            state: state,
            labels: L,
            cfg: cfg,
            t: t,

            pendingCount: pendingCount,

            payload: function () {
                return JSON.stringify(state.images.filter(function (image) {
                    return image.status === 'done' && image.id;
                }).map(function (image) {
                    return { id: image.id, caption: image.caption || '', credit: image.credit || '' };
                }));
            },

            isUnsaved: function () {
                return pendingCount() > 0 || store.payload() !== state.initialSignature;
            },

            notify: notify,

            whenIdle: function () {
                return pendingCount() === 0 ? Promise.resolve() : new Promise(function (resolve) { idleWaiters.push(resolve); });
            },

            addFiles: function (files) {
                var list = Array.prototype.slice.call(files || []).filter(function (file) {
                    return file && ((file.type || '').indexOf('image/') === 0 || isHeic(file));
                });
                var duplicates = 0;
                var fresh = [];

                list.forEach(function (file) {
                    var signature = [file.name, file.size, file.lastModified].join('|');
                    if (seen[signature] || fresh.some(function (f) { return f.sig === signature; })) {
                        duplicates++;
                        return;
                    }
                    fresh.push({ file: file, sig: signature });
                });

                var room = Math.max(0, roomLeft());
                var over = Math.max(0, fresh.length - room);
                // Only the files actually added are remembered: one turned away by the limit can
                // be added again once there is room.
                fresh.slice(0, room).forEach(function (entry) {
                    var item = fromServer({});
                    item.id = null;
                    item.status = 'queued';
                    item.progress = 0;
                    item.file = markRaw(entry.file);
                    item.sig = entry.sig;
                    item.thumb = '';
                    seen[entry.sig] = true;
                    state.images.push(item);
                    enqueue(state.images[state.images.length - 1]);
                });

                if (over > 0) {
                    notify(t(L.over_limit, { count: over, max: state.max }));
                } else if (duplicates > 0) {
                    notify(duplicates === 1 ? L.duplicates_one : t(L.duplicates_many, { count: duplicates }));
                }

                if (fresh.length) {
                    changed();
                    pump();
                }

                return Math.min(fresh.length, room);
            },

            retry: function (key) {
                var item = state.images.find(function (image) { return image.key === key; });
                if (item && item.status === 'error' && (item.file || item.blob)) {
                    enqueue(item);
                    pump();
                }
            },

            retryAll: function () {
                state.images.forEach(function (image) {
                    if (image.status === 'error' && (image.file || image.blob)) {
                        enqueue(image);
                    }
                });
                pump();
            },

            failedCount: function () {
                return state.images.filter(function (image) { return image.status === 'error'; }).length;
            },

            // Said by a form whose Save waited for uploads and found some had failed: the Save is
            // not sent, because those photos would be left out of it without a word.
            reportFailedBeforeSave: function () {
                notify(t(L.failed_before_save, { count: store.failedCount() }));
            },

            remove: function (key) {
                var index = state.images.findIndex(function (image) { return image.key === key; });
                if (index === -1) {
                    return;
                }
                var image = state.images.splice(index, 1)[0];
                forget(image);
                store.offerUndo([{ image: image, index: index }], L.removed);
                settle();
                changed();
                pump();
            },

            removeAll: function () {
                if (! state.images.length) {
                    return;
                }
                var entries = state.images.map(function (image, index) { return { image: image, index: index }; });
                state.images.splice(0, state.images.length);
                entries.forEach(function (entry) { forget(entry.image); });
                store.offerUndo(entries, t(L.removed_many, { count: entries.length }));
                settle();
                changed();
            },

            offerUndo: function (entries, text) {
                state.removed = { entries: entries, text: text };
                announce(text);
                clearTimeout(removedTimer);
                removedTimer = setTimeout(function () { state.removed = null; }, 6000);
            },

            undo: function () {
                if (! state.removed) {
                    return;
                }
                state.removed.entries.slice().sort(function (a, b) { return a.index - b.index; }).forEach(function (entry) {
                    state.images.splice(Math.min(entry.index, state.images.length), 0, entry.image);
                    remember(entry.image);
                });
                state.removed = null;
                clearTimeout(removedTimer);
                changed();
                pump();
            },

            move: function (from, to) {
                if (from === to || from < 0 || to < 0 || from >= state.images.length || to >= state.images.length) {
                    return;
                }
                var image = state.images.splice(from, 1)[0];
                state.images.splice(to, 0, image);
                announce(t(L.moved, { position: to + 1, total: state.images.length }));
                changed();
            },

            importFanPhotos: function (ids) {
                var room = Math.max(0, roomLeft());
                if (! ids.length || ! cfg.fanPhotosUrl) {
                    return Promise.resolve();
                }
                if (room === 0) {
                    notify(t(L.over_limit, { count: ids.length, max: state.max }));
                    return Promise.resolve();
                }
                state.importing = true;
                return fetch(cfg.fanPhotosUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': cfg.csrf,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ target: 'event', event: cfg.eventHash, draft_token: cfg.draftToken, photo_ids: ids.slice(0, room) }),
                }).then(function (response) {
                    return response.json().then(function (data) { return { ok: response.ok, data: data }; });
                }).then(function (result) {
                    if (! result.ok || ! result.data || ! result.data.success) {
                        notify((result.data && result.data.error) || L.upload_failed);
                        return;
                    }
                    (result.data.images || []).forEach(function (image) { state.images.push(fromServer(image)); });
                    if (ids.length > room) {
                        notify(t(L.over_limit, { count: ids.length - room, max: state.max }));
                    }
                    changed();
                }).catch(function () {
                    notify(L.upload_failed);
                }).finally(function () {
                    state.importing = false;
                });
            },
        };

        state.initialSignature = store.payload();

        return store;
    }

    // ---- The component -------------------------------------------------------------------------

    var component = {
        props: {
            store: { type: Object, required: true },
            // edit: everything. downgraded: the plan no longer includes the gallery, so only
            // removing is offered. readonly: nothing changes.
            mode: { type: String, default: 'edit' },
            fanPhotos: { type: Array, default: function () { return []; } },
            unsavedLabel: { type: String, default: '' },
            recurringHint: { type: String, default: '' },
        },
        data: function () {
            return {
                dialogIndex: null,
                dragDepth: 0,
                menuOpen: false,
                fanOpen: false,
                fanSelected: [],
                creditApplied: false,
            };
        },
        computed: {
            L: function () { return this.store.labels; },
            s: function () { return this.store.state; },
            images: function () { return this.store.state.images; },
            canEdit: function () { return this.mode === 'edit'; },
            canRemove: function () { return this.mode === 'edit' || this.mode === 'downgraded'; },
            canAdd: function () { return this.canEdit && this.images.length < this.s.max; },
            countText: function () { return this.store.t(this.L.count, { count: this.images.length, max: this.s.max }); },
            nearFull: function () { return this.images.length >= Math.ceil(this.s.max * 0.9); },
            pending: function () { return this.store.pendingCount(); },
            failed: function () { return this.store.failedCount(); },
            unsaved: function () { return this.canRemove && this.store.isUnsaved(); },
            uploadingText: function () {
                return this.store.t(this.L.uploading, { done: Math.min(this.s.batchDone + 1, this.s.batchTotal), total: this.s.batchTotal });
            },
            dialogImage: function () { return this.dialogIndex === null ? null : this.images[this.dialogIndex] || null; },
            dialogTitle: function () { return this.store.t(this.L.photo_n, { n: (this.dialogIndex || 0) + 1, total: this.images.length }); },
            fanAvailable: function () {
                return this.canEdit && this.fanPhotos.length > 0;
            },
            payload: function () {
                return this.store.payload();
            },
            knownIds: function () {
                return JSON.stringify(this.store.cfg.knownIds || []);
            },
        },
        watch: {
            'images.length': function () {
                this.$nextTick(this.initSortable);
            },
        },
        mounted: function () {
            this.initSortable();
            this._onPaste = this.onPaste.bind(this);
            document.addEventListener('paste', this._onPaste);
            this._onKey = this.onKey.bind(this);
            document.addEventListener('keydown', this._onKey);
            this._onDocClick = this.onDocumentClick.bind(this);
            document.addEventListener('click', this._onDocClick);
        },
        beforeUnmount: function () {
            document.removeEventListener('paste', this._onPaste);
            document.removeEventListener('keydown', this._onKey);
            document.removeEventListener('click', this._onDocClick);
            if (this._sortable) { this._sortable.destroy(); }
        },
        methods: {
            tileClass: function (index) {
                return index === 0 && this.images.length >= 3 ? 'col-span-2 row-span-2' : '';
            },
            tileStyle: function (image) {
                var style = {};
                if (image.color) { style.backgroundColor = image.color; }
                return style;
            },
            imageStyle: function (image) {
                // Portrait photos are cropped from near the top, where faces usually are.
                return image.height && image.width && image.height > image.width ? { objectPosition: '50% 25%' } : {};
            },
            ring: function (image) {
                var circumference = 2 * Math.PI * 16;
                return { strokeDasharray: circumference, strokeDashoffset: circumference * (1 - (image.progress || 0) / 100) };
            },
            pick: function () {
                if (this.$refs.fileInput) { this.$refs.fileInput.click(); }
            },
            onPicked: function (e) {
                this.store.addFiles(e.target.files);
                e.target.value = '';
            },
            onDragEnter: function (e) {
                if (! this.hasFiles(e)) { return; }
                e.preventDefault();
                this.dragDepth++;
            },
            onDragOver: function (e) {
                // Always, not only over the drop zone: a file let go a few pixels off it would
                // otherwise make the browser leave the unsaved form to show the image.
                if (this.hasFiles(e)) { e.preventDefault(); }
            },
            onDragLeave: function () {
                this.dragDepth = Math.max(0, this.dragDepth - 1);
            },
            onDrop: function (e) {
                if (! this.hasFiles(e)) { return; }
                e.preventDefault();
                this.dragDepth = 0;
                if (this.canAdd) { this.store.addFiles(e.dataTransfer.files); }
            },
            hasFiles: function (e) {
                return e.dataTransfer && Array.prototype.indexOf.call(e.dataTransfer.types || [], 'Files') !== -1;
            },
            onPaste: function (e) {
                if (! this.canAdd || ! this.$el || this.$el.offsetParent === null) { return; }
                var target = e.target;
                if (target && (target.closest('input, textarea, select, [contenteditable="true"], .CodeMirror, .EasyMDEContainer'))) { return; }
                var files = e.clipboardData && e.clipboardData.files;
                if (files && files.length) {
                    e.preventDefault();
                    this.store.addFiles(files);
                }
            },
            onKey: function (e) {
                if (e.key !== 'Escape') { return; }
                if (this.dialogIndex !== null) { this.closeDialog(); }
                else if (this.fanOpen) { this.fanOpen = false; }
                else if (this.menuOpen) { this.menuOpen = false; }
            },
            onDocumentClick: function (e) {
                if (this.menuOpen && this.$refs.menu && ! this.$refs.menu.contains(e.target)) {
                    this.menuOpen = false;
                }
            },
            initSortable: function () {
                if (! this.canEdit || typeof Sortable === 'undefined') { return; }
                // The grid is re-created whenever the gallery goes from empty to not, so an
                // instance bound to the old element is thrown away. Kept off data(): a Sortable
                // must not be wrapped in a reactive proxy.
                if (this._sortable && this._sortable.el === this.$refs.grid) { return; }
                if (this._sortable) { this._sortable.destroy(); this._sortable = null; }
                if (! this.$refs.grid) { return; }
                var self = this;
                this._sortable = Sortable.create(this.$refs.grid, {
                    animation: 150,
                    draggable: '.es-gallery-tile',
                    filter: '.es-gallery-remove',
                    preventOnFilter: false,
                    delay: 200,
                    delayOnTouchOnly: true,
                    onEnd: function (evt) {
                        var from = evt.oldDraggableIndex;
                        var to = evt.newDraggableIndex;
                        if (from === undefined || to === undefined || from === to) { return; }
                        // Put the DOM back where Vue left it, then let Vue re-render from the data.
                        var parent = evt.from;
                        parent.removeChild(evt.item);
                        parent.insertBefore(evt.item, parent.children[evt.oldIndex] || null);
                        self.store.move(from, to);
                    },
                });
            },
            openDialog: function (index) {
                if (! this.canEdit) { return; }
                this.dialogIndex = index;
                this.creditApplied = false;
                this._returnFocus = document.activeElement;
                this.$nextTick(function () {
                    if (this.$refs.caption) { this.$refs.caption.focus(); }
                });
            },
            closeDialog: function () {
                this.dialogIndex = null;
                var back = this._returnFocus;
                this.$nextTick(function () { if (back && back.focus) { try { back.focus(); } catch (e) {} } });
            },
            step: function (delta) {
                var next = this.dialogIndex + delta;
                if (next < 0 || next >= this.images.length) { return; }
                this.dialogIndex = next;
                this.creditApplied = false;
                this.$nextTick(function () {
                    if (this.$refs.caption) { this.$refs.caption.focus(); this.$refs.caption.select(); }
                });
            },
            onCaptionEnter: function (e) {
                e.preventDefault();
                if (this.dialogIndex < this.images.length - 1) { this.step(1); } else { this.closeDialog(); }
            },
            onField: function (field, value) {
                if (! this.dialogImage) { return; }
                this.dialogImage[field] = value;
                this.creditApplied = false;
                this.store.cfg.onChange && this.store.cfg.onChange();
            },
            applyCreditToAll: function () {
                var credit = this.dialogImage ? this.dialogImage.credit : '';
                this.images.forEach(function (image) { image.credit = credit; });
                this.creditApplied = true;
                this.store.cfg.onChange && this.store.cfg.onChange();
            },
            moveDialog: function (to) {
                var from = this.dialogIndex;
                if (to < 0 || to >= this.images.length || to === from) { return; }
                this.store.move(from, to);
                this.dialogIndex = to;
            },
            removeFromDialog: function () {
                var image = this.dialogImage;
                if (! image) { return; }
                var index = this.dialogIndex;
                this.store.remove(image.key);
                if (! this.images.length) {
                    this.closeDialog();
                } else {
                    this.dialogIndex = Math.min(index, this.images.length - 1);
                }
            },
            tileLabel: function (image, index) {
                var label = this.store.t(this.L.photo_n, { n: index + 1, total: this.images.length });
                return image.caption ? label + ': ' + image.caption : label;
            },
            toggleFan: function (id) {
                var at = this.fanSelected.indexOf(id);
                if (at === -1) { this.fanSelected.push(id); } else { this.fanSelected.splice(at, 1); }
            },
            importFan: function () {
                var ids = this.fanSelected.slice();
                var self = this;
                this.store.importFanPhotos(ids).then(function () {
                    self.fanOpen = false;
                    self.fanSelected = [];
                });
            },
        },
        template: `
<div class="es-gallery relative" @dragenter="onDragEnter" @dragover="onDragOver" @dragleave="onDragLeave" @drop="onDrop">
    <input ref="fileInput" type="file" accept="image/*" multiple class="hidden" @change="onPicked">
    <input v-if="canRemove" type="hidden" name="gallery_images" :value="payload">
    <input v-if="canRemove" type="hidden" name="gallery_draft_token" :value="store.cfg.draftToken">
    <input v-if="canRemove" type="hidden" name="gallery_known_ids" :value="knownIds">

    <p class="sr-only" aria-live="polite">@{{ s.live }}</p>

    <div v-if="pending > 0 && s.batchTotal > 0" class="sticky top-2 z-10 mb-3 flex items-center gap-3 rounded-lg bg-white/95 dark:bg-gray-800/95 px-3 py-2 text-sm text-gray-700 dark:text-gray-200 shadow-sm ring-1 ring-black/5 dark:ring-white/10" aria-live="polite">
        <svg class="h-4 w-4 animate-spin text-[var(--brand-blue)]" fill="none" viewBox="0 0 24 24" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
        <span>@{{ uploadingText }}</span>
    </div>

    <div v-if="s.removed" class="mb-3 flex items-center justify-between gap-3 rounded-lg bg-gray-900 dark:bg-gray-700 px-3 py-2 text-sm text-white shadow-sm">
        <span>@{{ s.removed.text }}</span>
        <button type="button" @click="store.undo()" class="font-semibold text-[var(--brand-blue-light)] hover:underline focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] rounded px-1">@{{ L.undo }}</button>
    </div>

    <div v-if="s.notice" class="mb-3 flex items-start gap-2 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3 text-sm text-amber-800 dark:text-amber-200">
        <svg class="w-5 h-5 shrink-0 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
        <span>@{{ s.notice }}</span>
    </div>

    <template v-if="!images.length">
        <div v-if="canEdit"
            class="flex items-center gap-4 rounded-xl border-2 border-dashed px-4 py-5 transition-all duration-200"
            :class="dragDepth > 0 ? 'border-[var(--brand-blue)] bg-blue-50/60 dark:bg-blue-500/10' : 'border-gray-300 dark:border-gray-600'">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-700/60 text-gray-500 dark:text-gray-300">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg>
            </div>
            <div class="min-w-0">
                <button type="button" @click="pick" class="font-semibold text-[var(--brand-blue)] hover:underline focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] rounded">@{{ L.add_photos }}</button>
                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">@{{ L.drop_hint }}</p>
            </div>
        </div>
        <div v-if="fanAvailable" class="mt-3 flex flex-wrap items-center gap-3 text-sm text-gray-600 dark:text-gray-300">
            <span>@{{ store.t(L.fan_ready, { count: fanPhotos.length }) }}</span>
            <button type="button" @click="fanOpen = true" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-1.5 font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)]">
                @{{ store.t(L.from_fan, { count: fanPhotos.length }) }}
            </button>
        </div>
    </template>

    <template v-else>
        <div class="mb-3 flex items-center justify-between gap-3">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                <span v-if="canEdit">@{{ L.order_hint }}</span>
            </p>
            <div class="flex items-center gap-2 shrink-0">
                <span class="text-sm tabular-nums" :class="nearFull ? 'text-amber-600 dark:text-amber-400 font-medium' : 'text-gray-500 dark:text-gray-400'">@{{ countText }}</span>
                <div v-if="canRemove" ref="menu" class="relative">
                    <button type="button" @click="menuOpen = !menuOpen" :aria-expanded="menuOpen ? 'true' : 'false'" :aria-label="L.more_actions"
                        class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)]">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM12.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM18.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" /></svg>
                    </button>
                    <div v-if="menuOpen" class="absolute end-0 z-20 mt-1 w-56 overflow-hidden rounded-xl bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black/5 dark:ring-white/10">
                        <button v-if="fanAvailable" type="button" @click="menuOpen = false; fanOpen = true" class="block w-full px-4 py-2.5 text-start text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">@{{ store.t(L.from_fan, { count: fanPhotos.length }) }}</button>
                        <button v-if="failed > 1" type="button" @click="menuOpen = false; store.retryAll()" class="block w-full px-4 py-2.5 text-start text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">@{{ L.retry_all }}</button>
                        <button type="button" @click="menuOpen = false; store.removeAll()" class="block w-full px-4 py-2.5 text-start text-sm text-red-600 dark:text-red-400 hover:bg-gray-50 dark:hover:bg-gray-700">@{{ L.remove_all }}</button>
                    </div>
                </div>
            </div>
        </div>

        <div ref="grid" class="grid grid-cols-3 sm:grid-cols-4 gap-2" :class="dragDepth > 0 ? 'rounded-xl ring-2 ring-[var(--brand-blue)] ring-offset-4 ring-offset-white dark:ring-offset-gray-800' : ''">
            <div v-for="(image, index) in images" :key="image.key"
                class="es-gallery-tile group relative aspect-square overflow-hidden rounded-lg bg-gray-100 dark:bg-gray-700 shadow-sm transition-shadow duration-200"
                :class="[tileClass(index), canEdit ? 'cursor-grab active:cursor-grabbing' : '', mode === 'downgraded' ? 'opacity-60' : '']"
                :style="tileStyle(image)">
                <button type="button" class="block h-full w-full focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)] focus-visible:ring-inset" :class="canEdit ? '' : 'cursor-default'"
                    @click="openDialog(index)" :aria-label="tileLabel(image, index)" :tabindex="canEdit ? 0 : -1">
                    <img v-if="image.thumb" :src="image.thumb" alt="" draggable="false" loading="lazy" class="h-full w-full object-cover" :style="imageStyle(image)">
                </button>

                <div v-if="image.status === 'queued' || image.status === 'processing' || image.status === 'uploading'" class="pointer-events-none absolute inset-0 flex items-center justify-center bg-black/35">
                    <svg class="h-10 w-10 -rotate-90" viewBox="0 0 40 40" aria-hidden="true">
                        <circle cx="20" cy="20" r="16" fill="none" stroke="rgba(255,255,255,0.3)" stroke-width="4"></circle>
                        <circle cx="20" cy="20" r="16" fill="none" stroke="white" stroke-width="4" stroke-linecap="round" :style="ring(image)" class="transition-all duration-200"></circle>
                    </svg>
                </div>

                <div v-if="image.status === 'error'" class="absolute inset-0 flex flex-col items-center justify-center gap-1.5 bg-red-900/80 p-2 text-center text-xs text-white">
                    <span class="line-clamp-3">@{{ image.error }}</span>
                    <button v-if="image.file || image.blob" type="button" @click.stop="store.retry(image.key)" class="rounded-md bg-white/20 px-2 py-1 font-semibold hover:bg-white/30 focus:outline-none focus:ring-2 focus:ring-white">@{{ L.retry }}</button>
                </div>

                <span v-if="image.caption && image.status === 'done'" class="pointer-events-none absolute bottom-1.5 start-1.5 flex h-6 w-6 items-center justify-center rounded-md bg-black/55 text-white" :title="L.has_caption">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" /></svg>
                </span>

                <button v-if="canRemove" type="button" @click.stop="store.remove(image.key)" :aria-label="L.remove + ': ' + tileLabel(image, index)"
                    class="es-gallery-remove absolute top-1 end-1 flex h-9 w-9 items-center justify-center rounded-full bg-black/60 text-white transition-opacity duration-200 hover:bg-black/80 focus:outline-none focus:ring-2 focus:ring-white">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <button v-if="canAdd" type="button" @click="pick"
                class="flex aspect-square flex-col items-center justify-center gap-1 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 text-sm font-medium text-gray-500 dark:text-gray-400 transition-all duration-200 hover:border-[var(--brand-blue)] hover:text-[var(--brand-blue)] focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)]">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                <span class="px-1 text-center leading-tight">@{{ L.add_photos }}</span>
            </button>
        </div>

        <p v-if="canEdit && !canAdd" class="mt-2 text-sm text-gray-500 dark:text-gray-400">@{{ L.full }}</p>
    </template>

    <p v-if="recurringHint && images.length" class="mt-3 text-sm text-gray-500 dark:text-gray-400">@{{ recurringHint }}</p>
    <p v-if="unsaved && unsavedLabel" class="mt-3 flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400">
        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" /></svg>
        @{{ unsavedLabel }}
    </p>

    <teleport to="body">
        <div v-if="dialogImage" class="fixed inset-0 z-[80] flex items-end sm:items-center justify-center bg-black/60 p-0 sm:p-4" @click.self="closeDialog" role="dialog" aria-modal="true" :aria-label="dialogTitle">
            <div class="ap-card flex max-h-[92vh] w-full max-w-2xl flex-col overflow-hidden rounded-t-2xl sm:rounded-2xl">
                <div class="flex items-center justify-between gap-3 border-b border-gray-200 dark:border-gray-700 px-5 py-3">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">@{{ dialogTitle }}</h3>
                    <button type="button" @click="closeDialog" :aria-label="L.close" class="flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)]">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <div class="overflow-y-auto">
                    <div class="flex items-center justify-center bg-gray-100 dark:bg-gray-900" :style="{ backgroundColor: dialogImage.color || null }">
                        <img :src="dialogImage.status === 'done' && dialogImage.url ? dialogImage.url : dialogImage.thumb" alt="" class="max-h-[45vh] w-auto max-w-full object-contain">
                    </div>
                    <div class="space-y-4 px-5 py-4">
                        <div>
                            <label for="es-gallery-caption" class="block text-sm font-medium text-gray-700 dark:text-gray-300">@{{ L.caption }}</label>
                            <input id="es-gallery-caption" ref="caption" type="text" dir="auto" maxlength="255" :value="dialogImage.caption" :placeholder="L.caption_placeholder"
                                @input="onField('caption', $event.target.value)" @keydown.enter="onCaptionEnter"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                        </div>
                        <div>
                            <label for="es-gallery-credit" class="block text-sm font-medium text-gray-700 dark:text-gray-300">@{{ L.credit }}</label>
                            <input id="es-gallery-credit" type="text" dir="auto" maxlength="100" :value="dialogImage.credit" :placeholder="L.credit_placeholder"
                                @input="onField('credit', $event.target.value)" @keydown.enter="onCaptionEnter"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                            <button v-if="dialogImage.credit && images.length > 1" type="button" @click="applyCreditToAll" class="mt-1.5 text-sm text-[var(--brand-blue)] hover:underline focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] rounded">
                                @{{ creditApplied ? L.credit_applied : L.apply_credit_all }}
                            </button>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" @click="moveDialog(dialogIndex - 1)" :disabled="dialogIndex === 0" class="rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-1.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-40 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)]">@{{ L.move_earlier }}</button>
                            <button type="button" @click="moveDialog(dialogIndex + 1)" :disabled="dialogIndex === images.length - 1" class="rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-1.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-40 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)]">@{{ L.move_later }}</button>
                            <button type="button" @click="moveDialog(0)" :disabled="dialogIndex === 0" class="rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-1.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-40 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)]">@{{ L.move_to_start }}</button>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between gap-3 border-t border-gray-200 dark:border-gray-700 px-5 py-3">
                    <button type="button" @click="removeFromDialog" class="rounded-lg px-3 py-2 text-sm font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)]">@{{ L.remove }}</button>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="step(-1)" :disabled="dialogIndex === 0" :aria-label="L.previous" class="flex h-10 w-10 items-center justify-center rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-40 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)]">
                            <svg class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
                        </button>
                        <button type="button" @click="step(1)" :disabled="dialogIndex === images.length - 1" :aria-label="L.next" class="flex h-10 w-10 items-center justify-center rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-40 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)]">
                            <svg class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
                        </button>
                        <button type="button" @click="closeDialog" class="rounded-lg bg-[var(--brand-button-bg)] px-4 py-3 text-base font-semibold text-white hover:bg-[var(--brand-button-bg-hover)] transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800">@{{ L.done }}</button>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="fanOpen" class="fixed inset-0 z-[80] flex items-end sm:items-center justify-center bg-black/60 p-0 sm:p-4" @click.self="fanOpen = false" role="dialog" aria-modal="true" :aria-label="L.fan_title">
            <div class="ap-card flex max-h-[92vh] w-full max-w-2xl flex-col overflow-hidden rounded-t-2xl sm:rounded-2xl">
                <div class="flex items-center justify-between gap-3 border-b border-gray-200 dark:border-gray-700 px-5 py-3">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">@{{ L.fan_title }}</h3>
                    <button type="button" @click="fanOpen = false" :aria-label="L.close" class="flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)]">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <div class="grid grid-cols-3 sm:grid-cols-4 gap-2 overflow-y-auto p-4">
                    <button v-for="photo in fanPhotos" :key="photo.id" type="button" @click="toggleFan(photo.id)" :aria-pressed="fanSelected.includes(photo.id) ? 'true' : 'false'"
                        class="group relative aspect-square overflow-hidden rounded-lg bg-gray-100 dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)]"
                        :class="fanSelected.includes(photo.id) ? 'ring-2 ring-[var(--brand-blue)]' : ''">
                        <img :src="photo.url" alt="" loading="lazy" class="h-full w-full object-cover">
                        <span class="absolute inset-x-0 bottom-0 truncate bg-gradient-to-t from-black/70 to-transparent px-2 pb-1 pt-4 text-start text-xs text-white">@{{ store.t(L.photo_by, { name: photo.name }) }}</span>
                        <span class="absolute top-1.5 end-1.5 flex h-6 w-6 items-center justify-center rounded-full border-2 border-white shadow" :class="fanSelected.includes(photo.id) ? 'bg-[var(--brand-button-bg)]' : 'bg-black/30'">
                            <svg v-if="fanSelected.includes(photo.id)" class="h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                        </span>
                    </button>
                </div>
                <div class="flex items-center justify-end gap-3 border-t border-gray-200 dark:border-gray-700 px-5 py-3">
                    <button type="button" @click="fanOpen = false" class="rounded-lg border border-gray-300 dark:border-gray-600 px-4 py-3 text-base text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)]">@{{ L.cancel }}</button>
                    <button type="button" @click="importFan" :disabled="!fanSelected.length || s.importing" class="rounded-lg bg-[var(--brand-button-bg)] px-4 py-3 text-base font-semibold text-white hover:bg-[var(--brand-button-bg-hover)] disabled:opacity-50 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800">@{{ store.t(L.fan_add_selected, { count: fanSelected.length }) }}</button>
                </div>
            </div>
        </div>
    </teleport>
</div>`,
    };

    window.EsGallery = { createStore: createStore, component: component };
})();
</script>
