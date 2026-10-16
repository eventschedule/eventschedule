<template>
  <!-- Mounted ON the list the schedule form watches (#ticket-preset-items), so each preset is a direct
       child of it: the form's list watcher reads a row that is gone as one Save will remove, by the
       text of its [data-row-name]. No wrapper element for that reason. -->
  <!-- Says the list was on the page. Drawn here and not by the page: a save sent before this app
       had loaded (or with its file gone after a deploy) would otherwise post the marker with no
       rows, and RoleController::update() would remove every preset. The empty-list note is the
       page's own (#ticket-presets-empty), outside the list the watcher counts rows in. -->
  <!-- And which presets it showed, with the default it opened on: one saved since from an event
       was not on this page and is not removed by it (TicketPresets::saveFromSchedule()). -->
  <template v-if="canEdit">
    <input type="hidden" name="ticket_presets_submitted" value="1">
    <input v-for="id in seenIds" :key="'seen-' + id" type="hidden" name="ticket_presets_seen[]" :value="id">
    <input type="hidden" name="ticket_presets_default_seen" :value="defaultSeen">
  </template>
  <div v-for="(row, index) in rows" :key="row.key" class="event-list-row preset-row" data-list-row :data-preset-key="row.key">
    <!-- What Save posts, whether the editor is open or not; nothing for someone who may only read. -->
    <template v-if="canEdit">
    <input type="hidden" :name="field(index, 'id')" :value="row.id || ''">
    <input type="hidden" :name="field(index, 'name')" :value="row.name">
    <input type="hidden" :name="field(index, 'kind')" :value="row.kind || ''">
    <input type="hidden" :name="field(index, 'is_default')" :value="row.is_default ? 1 : 0">
    <template v-if="row.kind === 'tickets'">
      <input type="hidden" :name="field(index, 'currency_code')" :value="row.currency_code">
      <input type="hidden" :name="field(index, 'payment_method')" :value="row.payment_method">
      <input type="hidden" :name="field(index, 'payment_instructions')" :value="row.payment_instructions || ''">
      <template v-for="(ticket, j) in row.tickets" :key="ticket.key">
        <input type="hidden" :name="field(index, 'tickets][' + j + '][src')" :value="ticket.src === null || ticket.src === undefined ? '' : ticket.src">
        <input type="hidden" :name="field(index, 'tickets][' + j + '][type')" :value="ticket.type || ''">
        <input type="hidden" :name="field(index, 'tickets][' + j + '][price')" :value="ticket.price === null || ticket.price === undefined ? '' : ticket.price">
        <input type="hidden" :name="field(index, 'tickets][' + j + '][quantity')" :value="ticket.quantity || ''">
      </template>
    </template>
    <template v-else>
      <input type="hidden" :name="field(index, 'currency_code')" :value="row.currency_code">
      <input type="hidden" :name="field(index, 'payment_method')" :value="row.payment_method">
    </template>
    <input v-if="row.kind === 'rsvp'" type="hidden" :name="field(index, 'rsvp_limit')" :value="row.rsvp_limit || ''">
    <input v-if="row.copy_event" type="hidden" :name="field(index, 'copy_event')" :value="row.copy_event">
    </template>

    <div class="min-w-0 flex-1">
      <div class="preset-row-line">
        <span class="event-list-name" data-row-name><bdi>{{ row.name || row.label || t.untitled }}</bdi></span>
        <span v-if="row.is_default" class="event-chip">{{ t.default }}</span>
      </div>
      <div class="event-list-sub"><bdi>{{ summary(row) }}</bdi></div>
    </div>
    <div v-if="canEdit" class="event-list-actions">
      <button v-if="!row.is_default" type="button" class="event-link" @click="makeDefault(index)">{{ t.make_default }}</button>
      <button v-if="open !== row.key" type="button" class="event-link" data-preset-edit-button @click="edit(row)">{{ t.edit }}</button>
      <button type="button" class="event-link is-danger" @click="remove(index)">{{ t.remove }}</button>
    </div>

    <div v-if="canEdit && open === row.key" class="preset-editor">
      <p v-if="row.copied_note" class="mb-3 text-sm text-gray-500 dark:text-gray-400">{{ row.copied_note }}</p>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block font-medium text-sm text-gray-700 dark:text-gray-300" :for="'preset-name-' + row.key">{{ t.name }}</label>
          <input :id="'preset-name-' + row.key" ref="nameInput" v-model="row.name" type="text" maxlength="100" :placeholder="row.label || ''"
            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
        </div>
      </div>

      <div class="mt-4">
        <span class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{ t.how }}</span>
        <div class="mt-2 event-pills flex-wrap" role="radiogroup" :aria-label="t.how">
          <label v-for="kind in kinds" :key="kind.value" class="event-pill">
            <input type="radio" class="sr-only" :name="'preset-kind-' + row.key" :value="kind.value" v-model="row.kind" @change="kindChanged(row)">
            <span>{{ kind.label }}</span>
          </label>
        </div>
      </div>

      <div v-if="row.kind === 'tickets'" class="mt-4">
        <div class="preset-types">
          <span class="h">{{ t.price }} <span class="font-normal text-gray-500 dark:text-gray-400">{{ row.currency_code }}</span></span>
          <span class="h">{{ t.quantity }}</span>
          <span class="h h-type">{{ t.type }}</span>
          <span></span>
          <div v-for="(ticket, j) in row.tickets" :key="ticket.key" class="preset-type">
            <div class="cell-price">
              <span class="lbl">{{ t.price }} {{ row.currency_code }}</span>
              <input v-model="ticket.price" type="number" step="0.01" min="0" :placeholder="t.required" :aria-label="t.price"
                class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
            </div>
            <div class="cell-qty">
              <span class="lbl">{{ t.quantity }}</span>
              <input v-model="ticket.quantity" type="number" min="1" :placeholder="t.unlimited" :aria-label="t.quantity"
                class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
            </div>
            <div class="cell-type">
              <span class="lbl">{{ t.type }}</span>
              <input v-model="ticket.type" type="text" maxlength="255" :placeholder="t.type_placeholder" :aria-label="t.type"
                class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
            </div>
            <button type="button" class="event-icon-btn is-remove" :title="t.remove" :aria-label="t.remove" :disabled="row.tickets.length === 1" @click="removeType(row, j)">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
          </div>
        </div>
        <button type="button" class="event-link mt-2" @click="addType(row)">+ {{ t.add_type }}</button>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ t.price_help }}</p>
        <p v-if="planNote && priced(row)" class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ planNote }}</p>

        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300" :for="'preset-method-' + row.key">{{ t.payment }}</label>
            <select :id="'preset-method-' + row.key" v-model="row.payment_method"
              class="preset-select mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
              <option v-for="method in methodsFor(row)" :key="method.key" :value="method.key">{{ method.label }}</option>
            </select>
          </div>
          <div>
            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300" :for="'preset-currency-' + row.key">{{ t.currency }}</label>
            <select :id="'preset-currency-' + row.key" v-model="row.currency_code"
              class="preset-select mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
              <option v-for="currency in currencies" :key="currency.code" :value="currency.code">{{ currency.code }} - {{ currency.label }}</option>
            </select>
          </div>
        </div>
        <div v-if="usesInstructions(row)" class="mt-4">
          <label class="block font-medium text-sm text-gray-700 dark:text-gray-300" :for="'preset-instructions-' + row.key">{{ t.instructions }}</label>
          <textarea :id="'preset-instructions-' + row.key" v-model="row.payment_instructions" rows="2" maxlength="5000"
            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm"></textarea>
        </div>
      </div>

      <div v-else-if="row.kind === 'rsvp'" class="mt-4 max-w-xs">
        <label class="block font-medium text-sm text-gray-700 dark:text-gray-300" :for="'preset-limit-' + row.key">{{ t.limit }}</label>
        <input :id="'preset-limit-' + row.key" v-model="row.rsvp_limit" type="number" min="1" :placeholder="t.unlimited"
          class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
      </div>

      <div class="mt-4 flex items-center justify-between gap-3 flex-wrap">
        <span class="text-xs text-gray-500 dark:text-gray-400">{{ footnote(row) }}</span>
        <button type="button" class="event-link" @click="done(row)">{{ t.done }}</button>
      </div>
    </div>
  </div>
</template>

<script>
let nextKey = 1;

function ticketRow(ticket) {
  return {
    key: 't' + (nextKey++),
    // Where the type came from in the stored preset, so it keeps what this editor does not show.
    src: ticket && ticket.src !== undefined && ticket.src !== null && ticket.src !== '' ? ticket.src : null,
    type: ticket && ticket.type ? String(ticket.type) : '',
    price: ticket && ticket.price !== null && ticket.price !== undefined && ticket.price !== '' ? ticket.price : '',
    quantity: ticket && ticket.quantity ? ticket.quantity : '',
  };
}

export default {
  props: {
    presets: { type: Array, default: () => [] },
    canEdit: { type: Boolean, default: false },
    copyFrom: { type: Object, default: null },
    defaults: { type: Object, default: () => ({}) },
    currencies: { type: Array, default: () => [] },
    methods: { type: Array, default: () => [] },
    instructionMethods: { type: Array, default: () => [] },
    symbols: { type: Object, default: () => ({}) },
    zeroDecimal: { type: Array, default: () => [] },
    planNote: { type: String, default: '' },
    switchOffered: { type: Boolean, default: false },
    switchSaved: { type: Boolean, default: false },
    // After a refused save: what the page showed when it was first opened.
    seen: { type: Array, default: null },
    defaultSeenId: { type: String, default: null },
    t: { type: Object, default: () => ({}) },
  },
  data() {
    return {
      rows: this.presets.map((preset) => this.toRow(preset)),
      open: null,
      seenIds: this.seen !== null ? this.seen : this.presets.map((preset) => preset.id).filter(Boolean),
      defaultSeen: this.defaultSeenId !== null ? this.defaultSeenId : ((this.presets.find((preset) => preset.is_default) || {}).id || ''),
    };
  },
  computed: {
    kinds() {
      return [
        { value: 'rsvp', label: this.t.kind_rsvp },
        { value: 'tickets', label: this.t.kind_tickets },
        { value: 'none', label: this.t.kind_none },
      ];
    },
  },
  watch: {
    rows: {
      deep: true,
      handler() {
        this.paintSwitch();
        this.paintEmpty();
        // The form's summaries are refreshed on the input event, which comes before this app has
        // drawn what was typed: the row's line said the name one keystroke late.
        this.$nextTick(() => {
          if (window.FormKit) {
            window.FormKit.refresh();
          }
        });
      },
    },
  },
  mounted() {
    this.paintSwitch();
    this.paintEmpty();
    // What turning the switch does to the team is said in the save bar before Save does it
    // (partials/form-save-bar-script, formkit:says), measured against what is stored.
    const toggle = this.switchOffered && document.getElementById('ticket_presets_team');
    if (toggle) {
      const say = () => {
        let text = '';
        if (toggle.checked !== this.switchSaved) {
          text = toggle.checked ? this.t.switch_bar_on : this.t.switch_bar_off;
        }
        document.dispatchEvent(new CustomEvent('formkit:says', { detail: { key: 'ticket-presets-team', text } }));
      };
      toggle.addEventListener('change', say);
      say();
    }
    // A refused save brings the page back with the row that was being edited still open.
    const failed = this.rows.find((row) => row.failed);
    if (failed) {
      this.open = failed.key;
    }
  },
  methods: {
    toRow(preset) {
      return {
        key: 'p' + (nextKey++),
        id: preset.id || '',
        name: preset.name || '',
        label: preset.label || '',
        kind: preset.kind || '',
        is_default: !!preset.is_default,
        currency_code: preset.currency_code || this.defaults.currency_code || 'USD',
        payment_method: preset.payment_method || this.defaults.payment_method || 'cash',
        payment_instructions: preset.payment_instructions || '',
        rsvp_limit: preset.rsvp_limit || '',
        // A saved preset whose only types are passes posts no rows, so its passes are kept; any
        // other starts with one row to fill in.
        tickets: (preset.tickets && preset.tickets.length ? preset.tickets : (preset.id && preset.passes ? [] : [{}])).map(ticketRow),
        passes: preset.passes || 0,
        uses: preset.uses || 0,
        uses_text: preset.uses_text || '',
        copy_event: preset.copy_event || '',
        copied_note: preset.copied_note || '',
        failed: !!preset.failed,
      };
    },
    field(index, name) {
      return 'ticket_presets[' + index + '][' + name + ']';
    },
    money(amount, code) {
      const decimals = this.zeroDecimal.includes(code) ? 0 : 2;
      let text = Number(amount || 0).toFixed(decimals).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
      if (decimals === 2 && text.endsWith('.00')) {
        text = text.slice(0, -3);
      }
      const symbol = this.symbols[code];
      return symbol && symbol !== code ? symbol + text : text + ' ' + code;
    },
    typesLine(row) {
      const parts = row.tickets.map((ticket) => {
        // A blank price is not free: it is a price still to be typed, which Save refuses.
        if (ticket.price === '' || ticket.price === null || ticket.price === undefined) {
          return (ticket.type ? ticket.type + ' ' : '') + this.t.price_needed;
        }
        const price = Number(ticket.price) > 0 ? this.money(ticket.price, row.currency_code) : this.t.free;
        return ticket.type ? ticket.type + ' ' + price : price;
      });
      if (!parts.length && row.passes) {
        return this.t.passes_only;
      }
      return parts.length ? parts.join(', ') : this.t.free;
    },
    methodLabel(key) {
      const method = this.methods.find((m) => m.key === key);
      return method ? method.label : key;
    },
    summary(row) {
      if (row.kind === 'rsvp') {
        return row.rsvp_limit ? this.t.kind_rsvp + ' · ' + this.places(row.rsvp_limit) : this.t.kind_rsvp;
      }
      if (row.kind === 'none') {
        return this.t.kind_none;
      }
      if (!row.kind) {
        return this.t.no_kind + ' · ' + this.typesLine(row);
      }
      return [this.t.kind_tickets, this.typesLine(row), this.methodLabel(row.payment_method)].filter(Boolean).join(' · ');
    },
    // Said without a plural, which a number typed here would need in every language's own rules.
    places(count) {
      return this.t.limit + ': ' + count;
    },
    // The server's sentence, with the plural its language needs for the count it knew.
    footnote(row) {
      return row.id && row.uses_text ? row.uses_text + ' ' + this.t.saved_with : this.t.saved_with;
    },
    done(row) {
      this.open = null;
      // The keyboard goes back to the row, not to the top of the page.
      this.$nextTick(() => {
        const host = this.$el && this.$el.parentNode;
        const button = host && host.querySelector('[data-preset-key="' + row.key + '"] [data-preset-edit-button]');
        if (button) {
          button.focus();
        }
      });
    },
    priced(row) {
      return row.tickets.some((ticket) => Number(ticket.price) > 0);
    },
    methodsFor(row) {
      // The stored method stays listed even when the owner's account no longer offers it, or the
      // select would quietly show another one.
      if (row.payment_method && !this.methods.some((m) => m.key === row.payment_method)) {
        return this.methods.concat([{ key: row.payment_method, label: row.payment_method }]);
      }
      return this.methods;
    },
    usesInstructions(row) {
      return this.instructionMethods.includes(row.payment_method);
    },
    edit(row) {
      this.open = row.key;
      this.$nextTick(() => {
        const input = document.getElementById('preset-name-' + row.key);
        if (input) {
          input.focus();
        }
      });
    },
    // Called by the "+ Add preset" link under the list (role/edit), which sits outside this app.
    add() {
      const first = this.rows.length === 0;
      const base = this.rows.find((row) => row.is_default) || this.rows[0] || null;
      let preset;

      if (first && this.copyFrom) {
        // The first preset starts from the tickets the schedule already uses.
        preset = Object.assign({}, this.copyFrom, { id: '', name: '', is_default: true });
      } else {
        preset = {
          kind: 'tickets',
          currency_code: base ? base.currency_code : this.defaults.currency_code,
          payment_method: base ? base.payment_method : this.defaults.payment_method,
          payment_instructions: base ? base.payment_instructions : '',
          tickets: [{}],
          is_default: first,
        };
      }

      const row = this.toRow(preset);
      this.rows.push(row);
      this.edit(row);
      this.changed();
    },
    makeDefault(index) {
      this.rows.forEach((row, i) => { row.is_default = i === index; });
      this.changed();
    },
    remove(index) {
      const [removed] = this.rows.splice(index, 1);
      if (removed && removed.is_default && this.rows.length) {
        this.rows[0].is_default = true;
      }
      if (removed && this.open === removed.key) {
        this.open = null;
      }
    },
    kindChanged(row) {
      if (row.kind === 'tickets' && !row.tickets.length) {
        row.tickets.push(ticketRow({}));
      }
    },
    addType(row) {
      row.tickets.push(ticketRow({}));
      this.changed();
    },
    removeType(row, j) {
      if (row.tickets.length > 1) {
        row.tickets.splice(j, 1);
        this.changed();
      }
    },
    // A change made by a button fires no input event, which is how the form notices one.
    changed() {
      this.$nextTick(() => {
        const host = this.$el && this.$el.parentNode;
        if (host) {
          host.dispatchEvent(new Event('change', { bubbles: true }));
        }
      });
    },
    paintEmpty() {
      const note = document.getElementById('ticket-presets-empty');
      if (note) {
        note.hidden = this.rows.length > 0;
      }
    },
    // The owner's switch under the list waits for a preset, and for every preset to say how people
    // sign up. Painted here because the list is what decides it.
    paintSwitch() {
      if (!this.switchOffered) {
        return;
      }
      const toggle = document.getElementById('ticket_presets_team');
      const blocker = document.getElementById('ticket-presets-team-blocker');
      if (!toggle) {
        return;
      }
      const missing = this.rows.find((row) => !row.kind);
      let reason = '';
      if (!this.rows.length) {
        reason = this.t.switch_needs_preset;
      } else if (missing) {
        reason = this.t.switch_needs_kind.split(':preset').join(missing.name || missing.label || this.t.untitled);
      }
      toggle.disabled = reason !== '';
      if (reason && toggle.checked) {
        toggle.checked = false;
        toggle.dispatchEvent(new Event('change', { bubbles: true }));
      }
      const wrap = toggle.closest('.preset-switch');
      if (wrap) {
        wrap.classList.toggle('is-off', reason !== '');
      }
      if (blocker) {
        blocker.textContent = reason;
        blocker.hidden = reason === '';
      }
    },
  },
};
</script>
