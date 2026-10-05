import { computed, reactive, ref } from 'vue';

/*
 * The dashboard's suggestions ("Add your next date", "Connect a payment method"...), as the
 * setup guide's card and the list for an account without a guide both hold them: what each
 * row's state is, and the four requests behind them (dismiss one, dismiss the list, take
 * dismissals back, and the account-wide switch).
 *
 * A ROW is one of:
 *   open       the suggestion, as a link, with its X
 *   dismissed  a stub at the row's own height: "Suggestion dismissed" and Undo where the X was.
 *              Nothing moves, so several can be cleared down one column without the next X
 *              sliding out from under the pointer
 *   gone       not drawn. Stubs go together, ten seconds after the last dismissal
 * and may also be `held`: not drawn, and not dismissed either - the guide speaks for it (its
 * own schedule's rows while the guide is hidden, its tickets row once the guide was answered).
 *
 * WHAT THE SERVER KNOWS AND A PAGE WITHOUT A RELOAD HAS TO REPEAT: a payments dismissal answers
 * that ask for the whole account (DismissedNextStep::ACCOUNT_WIDE_STEP_TYPES), so every payments
 * row goes with the one that was dismissed, the guide's own line included, and comes back with
 * its Undo.
 *
 * On a touch screen nothing here is timed: a stub or an Undo row stays until the page is left.
 * Ten seconds is short for a thumb that has just scrolled.
 */
export function useSuggestions(source, csrf) {
  const given = source || {};
  const urls = given.urls || {};
  const touch = window.matchMedia('(hover: none)').matches;

  const make = (row, own) => ({ ...row, own, state: 'open', held: false, written: null });

  const rows = reactive([
    ...(given.own || []).map((row) => make(row, true)),
    ...(given.others || []).map((row) => make(row, false)),
  ]);

  const live = computed(() => rows.filter((row) => row.state !== 'gone' && ! row.held));
  const open = computed(() => live.value.filter((row) => row.state === 'open'));

  /* ---- Requests ---- */

  const post = (url, body) => fetch(url, {
    method: 'POST',
    // keepalive: a dismissal is often the last thing done before leaving the page.
    keepalive: true,
    credentials: 'same-origin',
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json',
      'X-CSRF-TOKEN': csrf,
      'X-Requested-With': 'XMLHttpRequest',
    },
    body: JSON.stringify(body),
  }).then((response) => (response.ok ? response.json() : Promise.reject(response)));

  // One at a time, in the order they happened: a dismissal and its Undo sent side by side can
  // land the other way round, and then the row is dismissed after all.
  let writes = Promise.resolve();

  const send = (url, body) => {
    const run = writes.then(() => post(url, body));

    writes = run.catch(() => {});

    return run;
  };

  /* ---- One row ---- */

  const pair = (row) => ({ schedule: row.schedule, type: row.type });
  const same = (a, b) => a && b && a.schedule === b.schedule && a.type === b.type;
  const sameAsk = (a, b) => a.key === b.key || (a.type === 'next_step_payments' && b.type === 'next_step_payments');

  let stubTimer = 0;

  const sweep = () => {
    rows.forEach((row) => {
      if (row.state === 'dismissed') {
        row.state = 'gone';
        row.written = null;
      }
    });
  };

  const holdStubs = () => clearTimeout(stubTimer);

  const startStubs = () => {
    clearTimeout(stubTimer);

    if (! touch) {
      stubTimer = setTimeout(sweep, 10000);
    }
  };

  const dismiss = (row) => {
    const affected = rows.filter((other) => other.state === 'open' && sameAsk(other, row));
    const written = pair(row);

    affected.forEach((other) => {
      other.state = 'dismissed';
      other.written = written;
    });
    startStubs();

    // It claims to be permanent, so a request that failed must not look as if it worked: the
    // row comes back.
    send(urls.dismiss, written).catch(() => {
      affected.forEach((other) => {
        if (same(other.written, written)) {
          other.state = 'open';
          other.written = null;
        }
      });
    });
  };

  const undo = (row) => {
    const written = row.written;

    if (! written) {
      return;
    }

    rows.forEach((other) => {
      if (same(other.written, written)) {
        other.state = 'open';
        other.written = null;
      }
    });
    send(urls.restore, { rows: [written] }).catch(() => {});
  };

  /* ---- The whole list ---- */

  // What "Dismiss all" wrote, for as long as it can be taken back.
  const batch = ref(null);
  let batchTimer = 0;

  const holdBatch = () => clearTimeout(batchTimer);

  const startBatch = () => {
    clearTimeout(batchTimer);

    if (! touch) {
      batchTimer = setTimeout(() => {
        batch.value = null;
      }, 10000);
    }
  };

  // `listed` is what the list on screen holds: the server writes those rows and no others
  // (HomeController::dismissAllNextSteps()), so the line inside an open guide is never swept
  // up by a button that sits under the other schedules.
  const dismissAll = (listed) => {
    const asked = listed.filter((row) => row.state === 'open');

    if (! asked.length) {
      return;
    }

    const payments = asked.some((row) => row.type === 'next_step_payments');
    const affected = rows.filter((row) => row.state === 'open'
      && (asked.includes(row) || (payments && row.type === 'next_step_payments')));

    // Stubs already in the list had their own Undo; their dismissals stand.
    sweep();
    affected.forEach((row) => {
      row.state = 'gone';
    });

    const done = { rows: asked.map(pair), affected };

    batch.value = done;
    startBatch();

    send(urls.dismiss_all, { rows: done.rows }).then((answer) => {
      // Undo takes back what was WRITTEN, which the server reports: a row that stopped
      // applying between the page load and the click was skipped.
      if (answer && Array.isArray(answer.rows)) {
        done.rows = answer.rows;
      }
    }).catch(() => {
      affected.forEach((row) => {
        row.state = 'open';
      });

      if (batch.value === done) {
        batch.value = null;
      }
    });
  };

  const undoAll = () => {
    const done = batch.value;

    if (! done) {
      return;
    }

    clearTimeout(batchTimer);
    batch.value = null;
    done.affected.forEach((row) => {
      row.state = 'open';
    });

    // After the dismissal it follows has been answered, so `done.rows` is what was written.
    writes = writes.then(() => (done.rows.length ? post(urls.restore, { rows: done.rows }) : null)).catch(() => {});
  };

  /* ---- The switch ---- */

  const off = ref(false);
  // The line that says so, with its Undo. When it leaves, nothing of the card is left.
  const offNotice = ref(false);
  let offTimer = 0;

  const holdOff = () => clearTimeout(offTimer);

  const startOff = () => {
    clearTimeout(offTimer);

    if (! touch) {
      offTimer = setTimeout(() => {
        offNotice.value = false;
      }, 10000);
    }
  };

  // The switch takes the place of whatever on screen could still be undone: left dismissed,
  // turning suggestions back on later would show nothing, and the person who reached the
  // switch through "Dismiss all" would conclude it does not work.
  const takeBack = () => {
    const pending = [];
    const add = (written) => {
      if (written && ! pending.some((other) => same(other, written))) {
        pending.push(written);
      }
    };

    rows.forEach((row) => {
      if (row.state === 'dismissed') {
        add(row.written);
        row.state = 'open';
        row.written = null;
      }
    });

    const done = batch.value;

    if (done) {
      clearTimeout(batchTimer);
      batch.value = null;
      done.affected.forEach((row) => {
        row.state = 'open';
      });
    }

    clearTimeout(stubTimer);
    writes = writes.then(() => {
      (done ? done.rows : []).forEach(add);

      return pending.length ? post(urls.restore, { rows: pending }) : null;
    }).catch(() => {});
  };

  const turnOff = () => {
    takeBack();
    off.value = true;
    offNotice.value = true;
    startOff();

    send(urls.switch, { on: false }).catch(() => {
      off.value = false;
      offNotice.value = false;
    });
  };

  const turnOn = () => {
    clearTimeout(offTimer);
    off.value = false;
    offNotice.value = false;
    send(urls.switch, { on: true }).catch(() => {});
  };

  /* ---- What the guide does to its own schedule's rows ---- */

  // Hidden, the guide holds everything about its schedule (SetupGuide::withoutCoveredSteps()).
  const holdOwn = (held) => {
    rows.forEach((row) => {
      if (row.own) {
        row.held = held;
      }
    });
  };

  // "No tickets needed": that schedule is not asked for tickets again, by a row either.
  const holdOwnTickets = (held) => {
    rows.forEach((row) => {
      if (row.own && row.type === 'next_step_tickets') {
        row.held = held;
      }
    });
  };

  // A finished guide that was dismissed takes only itself: its own line is then a row like any
  // other, which is what the next page load would show.
  const release = () => {
    rows.forEach((row) => {
      row.own = false;
    });
  };

  const stop = () => {
    clearTimeout(stubTimer);
    clearTimeout(batchTimer);
    clearTimeout(offTimer);
  };

  return {
    rows, live, open, urls, touch,
    strings: given.t || {},
    dismissedBefore: !! given.dismissed_before,
    dismiss, undo, holdStubs, startStubs,
    batch, dismissAll, undoAll, holdBatch, startBatch,
    off, offNotice, turnOff, turnOn, holdOff, startOff,
    holdOwn, holdOwnTickets, release, stop,
  };
}
