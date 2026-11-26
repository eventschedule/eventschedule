<template>
    <section class="vmp" :aria-busy="phase === 'reading' ? 'true' : 'false'">
        <!-- Choosing photos. -->
        <template v-if="phase === 'pick'">
            <div class="vmp-head">
                <p v-if="kicker" class="vmp-kicker"><bdi>{{ kicker }}</bdi></p>
                <h2>{{ t.photos_title }}</h2>
                <p>{{ t.photos_lead }}</p>
            </div>

            <ol class="vmp-tips">
                <li><span class="vmp-num" aria-hidden="true">1</span><span>{{ t.tip_high }}</span></li>
                <li><span class="vmp-num" aria-hidden="true">2</span><span>{{ t.tip_row }}</span></li>
                <li><span class="vmp-num" aria-hidden="true">3</span><span>{{ t.tip_signs }}</span></li>
            </ol>

            <!-- Area by area: an indoor hall and each outdoor section get their own photos, the AI
                 maps each one, and the designer puts the blocks together. -->
            <template v-if="photos.length">
                <p class="vmp-areas-lead">{{ t.areas_lead }}</p>
                <div v-for="(a, ai) in areas" :key="a.id" class="vmp-area">
                    <div class="vmp-area-head">
                        <input v-model="a.name" type="text" maxlength="80" dir="auto" :placeholder="t.area_placeholder" :aria-label="t.area_name" />
                        <button v-if="areas.length > 1" type="button" class="vmp-x" :aria-label="t.remove_area" @click="removeArea(ai)">&times;</button>
                    </div>
                    <div class="vmp-strip">
                        <figure v-for="(p, i) in a.photos" :key="p.id" class="vmp-thumb">
                            <img v-if="p.preview" :src="p.preview" :alt="''" />
                            <span v-else class="vmp-pdf">PDF</span>
                            <button type="button" class="vmp-remove" :aria-label="t.remove || 'Remove'" @click="remove(a, i)">&times;</button>
                        </figure>
                        <label v-if="a.photos.length < maxPhotos" class="vmp-add">
                            <input type="file" accept="image/*,application/pdf" multiple class="sr-only" @change="pick($event, a)" />
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M12 5v14M5 12h14" stroke-linecap="round" /></svg>
                            <span>{{ t.add_photo }}</span>
                        </label>
                    </div>
                </div>
                <button v-if="areas.length < maxAreas" type="button" class="vmp-link vmp-add-area" @click="addArea">+ {{ t.add_area }}</button>
            </template>
            <p v-if="note" class="vmp-error" role="alert">{{ note }}</p>

            <!-- Nothing chosen yet: one big way in, and the other two quietly under it. -->
            <div v-if="!photos.length" class="vmp-start">
                <label class="vmp-primary vmp-big">
                    <!-- No `capture`: with it Android opens the camera for one photo and nothing else. -->
                    <input type="file" accept="image/*" multiple class="sr-only" @change="pick" />
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 8.5A2.5 2.5 0 015.5 6h1.9l1.3-2h6.6l1.3 2h1.9A2.5 2.5 0 0121 8.5v9a2.5 2.5 0 01-2.5 2.5h-13A2.5 2.5 0 013 17.5v-9z" /><circle cx="12" cy="13" r="3.5" /></svg>
                    {{ isPhone ? t.take_photos : t.upload_photos }}
                </label>
                <label class="vmp-link">
                    <input type="file" accept="image/*,application/pdf" class="sr-only" @change="pick" />
                    {{ t.upload_map }}
                </label>
                <a :href="designUrl" class="vmp-quiet">{{ t.draw_myself }}</a>
            </div>

            <template v-else>
                <details class="vmp-notes">
                    <summary>{{ t.notes_label }}</summary>
                    <textarea v-model="notes" rows="2" maxlength="500" dir="auto" :placeholder="t.notes_placeholder"></textarea>
                </details>

                <div class="vmp-actions">
                    <a :href="designUrl" class="vmp-quiet">{{ t.draw_myself }}</a>
                    <button type="button" class="vmp-primary" @click="start">
                        {{ t.make_my_map }}
                        <svg class="w-4 h-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                    </button>
                </div>
            </template>
            <p class="vmp-honest">{{ t.photos_honest }}</p>
        </template>

        <!-- Uploading, then reading. -->
        <template v-else-if="phase === 'uploading' || phase === 'reading'">
            <div class="vmp-reading">
                <div class="vmp-scan">
                    <div v-for="p in photos" :key="'s' + p.id" class="vmp-scan-img">
                        <img v-if="p.preview" :src="p.preview" alt="" />
                        <span v-else class="vmp-pdf">PDF</span>
                        <i class="vmp-shimmer" aria-hidden="true"></i>
                    </div>
                </div>
                <div class="vmp-progress" v-if="phase === 'uploading'">
                    <div class="vmp-bar"><div :style="{ width: Math.round(progress * 100) + '%' }"></div></div>
                    <p>{{ t.uploading }}</p>
                </div>
                <p v-else-if="areaProgress" class="vmp-area-progress" aria-live="polite">{{ areaProgress }}</p>
                <ol v-if="phase === 'reading'" class="vmp-stages" aria-live="polite">
                    <li v-for="(s, i) in stages" :key="s" :class="{ 'is-done': stage > i, 'is-now': stage === i }">
                        <span class="vmp-dot" aria-hidden="true"></span>{{ s }}
                    </li>
                </ol>
                <p class="vmp-wait">{{ t.reading_wait }} <span>{{ t.can_leave }}</span></p>
            </div>
        </template>

        <!-- It failed: the photos are kept. -->
        <template v-else-if="phase === 'failed'">
            <div class="vmp-head">
                <h2>{{ note || t.ai_failed }}</h2>
            </div>
            <div class="vmp-actions">
                <a :href="designUrl" class="vmp-quiet">{{ t.draw_myself }}</a>
                <button type="button" class="vmp-primary" @click="parse">{{ t.try_again }}</button>
            </div>
        </template>
    </section>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { shrinkImage } from '../image-shrink';

const props = defineProps({
    t: { type: Object, required: true },
    urls: { type: Object, required: true },
    csrfToken: { type: String, required: true },
    resume: { type: Object, default: null },
    kicker: { type: String, default: '' },
});

// A big market is photographed row by row. They go up four at a time, so no single request
// is too large for the server.
const maxPhotos = 20;
const maxAreas = 10;
const maxTotal = 60;
const BATCH = 4;
const phase = ref('pick');
let areaSeq = 0;
const newArea = () => ({ id: ++areaSeq, name: '', photos: [] });
const areas = ref([newArea()]);
const photos = computed(() => areas.value.flatMap((a) => a.photos));
// Named, or more than one: the photos go up and are read area by area.
const byArea = computed(() => areas.value.length > 1 || areas.value[0].name.trim() !== '');
const areaProgress = ref('');
const progress = ref(0);
const stage = ref(0);
const note = ref('');
const notes = ref('');
const draftId = ref(null);
let stageTimer = null;
let seq = 0;

const isPhone = computed(() => window.matchMedia('(max-width: 640px)').matches);
const stages = computed(() => [props.t.stage_finding, props.t.stage_reading, props.t.stage_placing]);
const designUrl = computed(() => props.urls.design);

async function pick(evt, area = areas.value[0]) {
    note.value = '';
    const files = Array.from(evt.target.files || []);
    evt.target.value = '';
    for (const file of files) {
        if (area.photos.length >= maxPhotos || photos.value.length >= maxTotal) {
            note.value = props.t.max_photos;
            break;
        }
        const isPdf = file.type === 'application/pdf';
        if (/heic|heif/i.test(file.type) || /\.hei[cf]$/i.test(file.name)) {
            note.value = props.t.heic;
            continue;
        }
        if (isPdf && photos.value.some((p) => p.pdf)) {
            continue;
        }
        try {
            // Phone photos are 5 to 8 MB; a market's 4G will not carry six of them. Redrawn at about
            // 1600px, which also drops the EXIF block and the GPS position in it.
            const blob = isPdf ? file : await shrinkImage(file, 1600, 0.85);
            if (blob.size > 8 * 1024 * 1024) {
                note.value = props.t.photo_too_big;
                continue;
            }
            area.photos.push({ id: ++seq, blob, pdf: isPdf, name: isPdf ? 'map.pdf' : 'photo.jpg', preview: isPdf ? null : URL.createObjectURL(blob) });
        } catch (e) {
            note.value = props.t.photo_too_big;
        }
    }
}

function remove(area, i) {
    const [p] = area.photos.splice(i, 1);
    if (p?.preview) URL.revokeObjectURL(p.preview);
}

function addArea() {
    areas.value.push(newArea());
}

function removeArea(ai) {
    const [a] = areas.value.splice(ai, 1);
    (a?.photos || []).forEach((p) => { if (p.preview) URL.revokeObjectURL(p.preview); });
}

async function start() {
    if (photos.value.some((p) => !p.blob)) {
        // Photos shown from a draft already on the server: read that draft.
        parse();
        return;
    }
    phase.value = 'uploading';
    progress.value = 0;
    draftId.value = null;
    const grouped = byArea.value;
    // Each batch is one area's photos (four at most), so an area's photos stay together.
    const batches = [];
    areas.value.filter((a) => a.photos.length).forEach((a, ai) => {
        for (let k = 0; k < a.photos.length; k += BATCH) batches.push({ area: ai, name: a.name.trim(), photos: a.photos.slice(k, k + BATCH) });
    });
    const total = photos.value.reduce((n, p) => n + (p.blob?.size || 1), 0);
    let sent = 0;
    for (const b of batches) {
        const batch = b.photos;
        const form = new FormData();
        batch.forEach((p) => form.append('photos[]', p.blob, p.name));
        if (draftId.value) form.append('draft', draftId.value);
        if (grouped) {
            form.append('area', String(b.area));
            form.append('area_name', b.name);
        }
        const before = sent;
        const result = await new Promise((resolve) => {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', props.urls.photos);
            xhr.setRequestHeader('X-CSRF-TOKEN', props.csrfToken);
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.upload.onprogress = (e) => { if (e.lengthComputable) progress.value = (before + (e.loaded / e.total) * batch.reduce((n, p) => n + (p.blob?.size || 1), 0)) / total; };
            xhr.onload = () => {
                let data = {};
                try { data = JSON.parse(xhr.responseText); } catch (e) { /* below */ }
                resolve(xhr.status >= 200 && xhr.status < 300 && data.draft ? { draft: data.draft } : { error: Object.values(data.errors || {})[0]?.[0] || props.t.ai_failed });
            };
            xhr.onerror = () => resolve({ error: props.t.ai_failed });
            xhr.send(form);
        });
        if (result.error) {
            phase.value = 'pick';
            note.value = result.error;
            return;
        }
        draftId.value = result.draft;
        sent += batch.reduce((n, p) => n + (p.blob?.size || 1), 0);
    }
    progress.value = 1;
    parse();
}

async function parse() {
    phase.value = 'reading';
    stage.value = 0;
    clearInterval(stageTimer);
    // The stages are the honest order of the work; they move on a clock because the answer is one.
    stageTimer = setInterval(() => { if (stage.value < stages.value.length - 1) stage.value++; }, 9000);

    try {
        let data = {};
        let res;
        // One area per request when the market was photographed area by area: ask again until
        // every area is read.
        for (let guard = 0; guard <= maxAreas + 40; guard++) {
            res = await fetch(props.urls.parse.replace('__DRAFT__', draftId.value), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': props.csrfToken },
                body: JSON.stringify({ notes: notes.value }),
            });
            data = await res.json().catch(() => ({}));
            if (!(res.ok && data.ok && data.pending)) break;
            note.value = '';
            // Another request is still reading this draft (a Try again): ask again shortly.
            if (data.busy) { await new Promise((r) => setTimeout(r, 4000)); continue; }
            if (data.total) areaProgress.value = (props.t.mapping_area || '').replace(':name', data.next || '').replace(':current', String(data.done + 1)).replace(':total', String(data.total));
        }
        clearInterval(stageTimer);
        if (res.ok && data.ok) {
            stage.value = stages.value.length;
            window.location.href = props.urls.design + '?draft=' + encodeURIComponent(draftId.value);
            return;
        }
        note.value = data.message || Object.values(data.errors || {})[0]?.[0] || '';
        phase.value = 'failed';
    } catch (e) {
        clearInterval(stageTimer);
        // The request may still finish on the server; the tab shows "Your map is ready" when it does.
        phase.value = 'failed';
    }
}

onMounted(() => {
    // Back after the phone slept or the tab closed: the draft is on the server.
    if (props.resume && props.resume.draft) {
        draftId.value = props.resume.draft;
        areas.value[0].photos = (props.resume.images || []).map((i) => ({ id: ++seq, pdf: i.pdf, preview: i.pdf ? null : i.url }));
        // The photos are on the server: Try again reads them (and carries on area by area);
        // there is nothing in this browser to upload again.
        if (['failed', 'uploaded', 'reading'].includes(props.resume.status)) phase.value = 'failed';
    }
});

onBeforeUnmount(() => {
    clearInterval(stageTimer);
    photos.value.forEach((p) => { if (p.preview && p.preview.startsWith('blob:')) URL.revokeObjectURL(p.preview); });
});
</script>

<style scoped>
.vmp { display: flex; flex-direction: column; gap: 1.1rem; }
.vmp-head h2 { font-size: 1.35rem; font-weight: 700; color: rgb(var(--ap-ink)); }
.vmp-head p { margin-top: 0.3rem; color: rgb(var(--ap-ink-2)); }
.vmp-head .vmp-kicker { font-size: 0.8125rem; color: rgb(var(--ap-ink-3)); margin-bottom: 0.2rem; }
.vmp-head h2 { font-size: 1.5rem; font-weight: 800; letter-spacing: -0.015em; line-height: 1.2; }
.vmp-tips { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0.6rem; counter-reset: none; }
.vmp-tips li { display: flex; align-items: center; gap: 0.75rem; padding: 0.85rem 1rem; border-radius: 0.8rem; background: rgb(var(--ap-surface)); border: 1px solid rgb(var(--ap-border)); font-size: 0.9375rem; font-weight: 500; color: rgb(var(--ap-ink)); }
.vmp-num { display: inline-flex; align-items: center; justify-content: center; width: 1.6rem; height: 1.6rem; flex-shrink: 0; border-radius: 999px; background: rgba(78, 129, 250, 0.12); color: var(--brand-blue); font-size: 0.8125rem; font-weight: 700; }
@media (max-width: 640px) { .vmp-tips { grid-template-columns: 1fr; } }
.vmp-start { display: flex; flex-direction: column; align-items: center; gap: 0.35rem; margin-top: 0.5rem; }
.vmp-big { width: 100%; justify-content: center; padding: 1rem 1.25rem; font-size: 1.0625rem; cursor: pointer; box-shadow: 0 8px 20px -8px rgba(37, 99, 235, 0.55); }
.vmp-big svg { width: 1.35rem; height: 1.35rem; }
.vmp-big:focus-within, .vmp-link:focus-within { outline: 3px solid var(--brand-blue); outline-offset: 2px; }
.vmp-link { padding: 0.7rem 0.5rem; font-weight: 600; color: var(--brand-blue); cursor: pointer; }
.vmp-link:hover { text-decoration: underline; text-underline-offset: 3px; }
.vmp-strip { display: flex; flex-wrap: wrap; gap: 0.65rem; }
.vmp-areas-lead { font-size: 0.875rem; color: rgb(var(--ap-ink-2)); }
.vmp-area { display: flex; flex-direction: column; gap: 0.6rem; padding: 0.85rem; border-radius: 0.9rem; background: rgb(var(--ap-surface)); border: 1px solid rgb(var(--ap-border)); }
.vmp-area-head { display: flex; align-items: center; gap: 0.5rem; }
.vmp-area-head input { flex: 1; min-width: 0; padding: 0.55rem 0.75rem !important; border-radius: 0.6rem; border: 1px solid rgb(var(--ap-border-strong)); background: rgb(var(--ap-bg)); color: rgb(var(--ap-ink)); font-size: 0.95rem !important; font-weight: 600; }
.vmp-x { width: 2rem; height: 2rem; flex-shrink: 0; border-radius: 999px; color: rgb(var(--ap-ink-2)); font-size: 1.25rem; line-height: 1; }
.vmp-x:hover { background: rgb(var(--ap-surface-hover)); }
.vmp-add-area { align-self: flex-start; padding-inline: 0; }
.vmp-area-progress { font-size: 0.95rem; font-weight: 600; color: rgb(var(--ap-ink)); }
.vmp-thumb { position: relative; width: 6.5rem; height: 6.5rem; border-radius: 0.9rem; overflow: hidden; background: rgb(var(--ap-bg)); border: 1px solid rgb(var(--ap-border)); animation: vmp-in 0.3s ease backwards; }
.vmp-thumb img { width: 100%; height: 100%; object-fit: cover; }
.vmp-pdf { display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; font-weight: 700; color: rgb(var(--ap-ink-2)); }
.vmp-remove { position: absolute; top: 0.3rem; inset-inline-end: 0.3rem; width: 1.6rem; height: 1.6rem; border-radius: 999px; background: rgba(17, 24, 39, 0.7); color: #fff; line-height: 1; }
.vmp-add { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.35rem; width: 6.5rem; height: 6.5rem; border-radius: 0.9rem; border: 1.5px dashed rgb(var(--ap-border-strong)); color: var(--brand-blue); font-size: 0.8rem; font-weight: 600; text-align: center; cursor: pointer; transition: all 0.2s; padding: 0.4rem; }
.vmp-add:hover { border-color: var(--brand-blue); background: rgba(78, 129, 250, 0.06); }
.vmp-add svg { width: 1.6rem; height: 1.6rem; }
.vmp-honest { font-size: 0.8125rem; color: rgb(var(--ap-ink-3)); }
.vmp-error { font-size: 0.875rem; color: #b91c1c; }
:global(.dark) .vmp-error { color: #fca5a5; }
.vmp-notes summary { cursor: pointer; font-size: 0.875rem; color: rgb(var(--ap-ink-2)); }
.vmp-notes textarea { margin-top: 0.5rem; width: 100%; border-radius: 0.7rem; border: 1px solid rgb(var(--ap-border-strong)); background: rgb(var(--ap-bg)); color: rgb(var(--ap-ink)); padding: 0.6rem 0.75rem; }
.vmp-actions { display: flex; align-items: center; justify-content: flex-end; gap: 1rem; }
.vmp-quiet { font-size: 0.875rem; color: rgb(var(--ap-ink-2)); text-decoration: underline; text-underline-offset: 3px; }
.vmp-primary { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.1rem; border-radius: 0.8rem; font-weight: 600; font-size: 1rem; color: #fff; background: linear-gradient(135deg, var(--brand-button-bg-light), var(--brand-button-bg)); transition: all 0.2s; }
.vmp-primary:hover:not(:disabled) { background: var(--brand-button-bg-hover); transform: translateY(-1px); }
.vmp-primary:disabled { opacity: 0.5; }
.vmp-reading { display: flex; flex-direction: column; align-items: center; gap: 1.25rem; padding: 1rem 0; }
.vmp-scan { display: flex; flex-wrap: wrap; justify-content: center; gap: 0.6rem; }
.vmp-scan-img { position: relative; width: 7rem; height: 7rem; border-radius: 1rem; overflow: hidden; background: rgb(var(--ap-bg)); }
.vmp-scan-img img { width: 100%; height: 100%; object-fit: cover; filter: saturate(0.85); }
.vmp-shimmer { position: absolute; inset: 0; background: linear-gradient(180deg, transparent 0%, rgba(78, 129, 250, 0.55) 48%, rgba(34, 211, 238, 0.4) 52%, transparent 100%); background-size: 100% 220%; animation: vmp-scan 2.2s ease-in-out infinite; mix-blend-mode: screen; }
@keyframes vmp-scan { from { background-position: 0 -120%; } to { background-position: 0 120%; } }
@keyframes vmp-in { from { opacity: 0; transform: scale(0.92); } }
.vmp-progress { width: min(22rem, 100%); text-align: center; color: rgb(var(--ap-ink-2)); font-size: 0.875rem; }
.vmp-bar { height: 0.45rem; border-radius: 999px; background: rgb(var(--ap-border)); overflow: hidden; margin-bottom: 0.5rem; }
.vmp-bar div { height: 100%; background: linear-gradient(90deg, #4E81FA, #0EA5E9, #22D3EE); transition: width 0.2s; }
.vmp-stages { display: flex; flex-direction: column; gap: 0.6rem; }
.vmp-stages li { display: flex; align-items: center; gap: 0.6rem; color: rgb(var(--ap-ink-3)); font-size: 1rem; transition: color 0.3s; }
.vmp-stages li.is-now { color: rgb(var(--ap-ink)); font-weight: 600; }
.vmp-stages li.is-done { color: rgb(var(--ap-ink-2)); }
.vmp-dot { width: 0.7rem; height: 0.7rem; border-radius: 999px; background: rgb(var(--ap-border-strong)); }
.vmp-stages li.is-now .vmp-dot { background: var(--brand-blue); box-shadow: 0 0 0 5px rgba(78, 129, 250, 0.2); animation: vmp-pulse 1.4s ease-in-out infinite; }
.vmp-stages li.is-done .vmp-dot { background: #10b981; }
@keyframes vmp-pulse { 50% { box-shadow: 0 0 0 9px rgba(78, 129, 250, 0.08); } }
.vmp-wait { font-size: 0.875rem; color: rgb(var(--ap-ink-2)); text-align: center; }
.vmp-wait span { display: block; color: rgb(var(--ap-ink-3)); }
@media (max-width: 640px) {
    .vmp-tips { grid-template-columns: 1fr; }
    .vmp-actions { flex-direction: column-reverse; align-items: stretch; }
    .vmp-primary { justify-content: center; }
    .vmp-quiet { text-align: center; }
}
@media (prefers-reduced-motion: reduce) {
    .vmp-shimmer, .vmp-stages li.is-now .vmp-dot, .vmp-thumb { animation: none; }
}
</style>
