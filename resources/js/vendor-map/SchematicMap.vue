<!--
  The market as blocks of equal stalls (VendorMapSchematic::build() on the server), for the
  visitors' map page. The same classes as resources/views/vendor-map/schematic.blade.php, which
  draws it before this starts, so nothing moves when the app takes over.

  Props: schematic (the server's build), selectedId, highlightIds (null, or the ids that match a
  search: the rest dim), showHere, hereLabel, rtl, spotLabel(id) the spoken name of a stall,
  selectable(id) whether a stall opens a card. Emits select(id).

  The frame is dir="ltr" whatever the page: it is a place, not a sentence.
-->
<template>
  <div class="vms" dir="ltr">
    <template v-for="(section, si) in schematic.sections" :key="si">
      <template v-if="section.type === 'full'">
        <Zone :z="section.zone" />
      </template>
      <template v-else-if="section.type === 'stack'">
        <Zone v-for="(z, zi) in section.zones" :key="zi" :z="z" />
      </template>
      <div v-else class="vms-cols" :style="{ gridTemplateColumns: `minmax(0, ${section.fa}fr) minmax(0, ${section.fb}fr)` }">
        <div class="vms-col"><Zone v-for="(z, zi) in section.a" :key="'a' + zi" :z="z" /></div>
        <div class="vms-col"><Zone v-for="(z, zi) in section.b" :key="'b' + zi" :z="z" /></div>
      </div>
    </template>

    <div v-if="schematic.amenities.length || (showHere && schematic.here)" class="vms-foot" :dir="rtl ? 'rtl' : 'ltr'">
      <p v-if="showHere && schematic.here" class="vms-here"><i aria-hidden="true"></i><b>{{ hereLabel }}</b><span v-if="schematic.entrance">· {{ schematic.entrance }}</span></p>
      <ul v-if="schematic.amenities.length" class="vms-amen">
        <li v-for="(a, ai) in schematic.amenities" :key="ai"><bdi>{{ a.label }}</bdi></li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { defineComponent, h } from 'vue';

const props = defineProps({
    schematic: { type: Object, required: true },
    selectedId: { type: [Number, String], default: null },
    highlightIds: { type: Array, default: null },
    showHere: { type: Boolean, default: false },
    hereLabel: { type: String, default: '' },
    rtl: { type: Boolean, default: false },
    spotLabel: { type: Function, default: (id) => String(id) },
    selectable: { type: Function, default: () => true },
    // A stall that stays chosen (the shoppers' map) says so; one that goes on to the next screen
    // (the vendor link) is a plain button.
    pressable: { type: Boolean, default: true },
});

const emit = defineEmits(['select']);

const safeColor = (c) => (/^#[0-9a-fA-F]{6}$/.test(c || '') ? c : '#64748B');

/** One block: its heading, then its stalls; a stall with somebody on it is a button. */
const Zone = defineComponent({
    props: { z: { type: Object, required: true } },
    setup(p) {
        return () => {
            const z = p.z;
            const cells = z.cells.map((c, i) => {
                if (!c) return h('span', { key: 'g' + i, class: 'vms-c is-gap', 'aria-hidden': 'true' });
                const on = props.selectedId != null && String(props.selectedId) === String(c.id);
                const dim = Array.isArray(props.highlightIds) && !props.highlightIds.some((id) => String(id) === String(c.id));
                const cls = ['vms-c', 'is-' + c.state, { 'is-on': on, 'is-dim': dim }];
                if (!props.selectable(c.id)) {
                    return h('span', { key: c.id, class: cls, 'aria-hidden': 'true' }, c.label);
                }
                return h('button', {
                    key: c.id,
                    type: 'button',
                    class: cls,
                    'aria-label': props.spotLabel(c.id),
                    'aria-pressed': props.pressable ? (on ? 'true' : 'false') : null,
                    onClick: () => emit('select', c.id),
                }, c.label);
            });
            return h('section', { class: 'vms-zone', style: { '--zc': safeColor(z.color) } }, [
                h('div', { class: 'vms-zh', dir: props.rtl ? 'rtl' : 'ltr' }, [
                    z.name ? h('b', [h('bdi', z.name)]) : null,
                    z.range ? h('span', { class: 'vms-range' }, z.range) : null,
                    z.sells ? h('span', { class: 'vms-sells' }, [h('bdi', z.sells)]) : null,
                ]),
                h('div', { class: 'vms-grid', style: { gridTemplateColumns: `repeat(${z.cols}, minmax(0, 1fr))` } }, cells),
            ]);
        };
    },
});
</script>
