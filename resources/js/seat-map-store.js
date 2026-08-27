/**
 * One seat map per (event, date), shared by every picker on the page.
 *
 * An allocated event has one picker per price band, and all of them render the SAME map filtered to
 * their own sections. Fetching it per picker meant a three-band event pulled a 2,000-seat payload
 * three times - and did it on mount, even though the default flow is the quantity dropdown where
 * the map is never opened.
 *
 * Sharing the object is also what makes the diff poll correct: one poll updates the seats every
 * picker is looking at, instead of N polls racing to mutate N copies.
 */
const maps = new Map();
const pollers = new Map();

function key(eventId, date) {
    return `${eventId}|${date}`;
}

/**
 * The shared map, fetched at most once per key. Returns null if the fetch failed, and forgets the
 * failure so a later attempt can retry.
 */
export function loadMap(stateUrl, eventId, date) {
    const k = key(eventId, date);

    if (!maps.has(k)) {
        const url = `${stateUrl}?event_id=${encodeURIComponent(eventId)}&date=${encodeURIComponent(date)}`;
        maps.set(k, fetch(url, { headers: { Accept: 'application/json' }, credentials: 'same-origin' })
            .then((res) => (res.ok ? res.json() : null))
            .then((data) => {
                if (!data) maps.delete(k);
                return data;
            })
            .catch(() => { maps.delete(k); return null; }));
    }

    return maps.get(k);
}

/**
 * Start the diff poll for this key, once. Subsequent callers just get the same shared map mutated
 * in place. `onChange` fires after each applied diff so a picker can drop seats it has lost.
 */
export function startPolling(stateUrl, eventId, date, map, onChange, intervalMs = 5000) {
    const k = key(eventId, date);
    if (pollers.has(k)) {
        pollers.get(k).listeners.push(onChange);
        return;
    }

    const entry = { listeners: [onChange], timer: null };
    pollers.set(k, entry);

    entry.timer = setInterval(async () => {
        try {
            const url = `${stateUrl}?event_id=${encodeURIComponent(eventId)}&date=${encodeURIComponent(date)}&since=${map.version}`;
            const res = await fetch(url, { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
            if (!res.ok) return;

            const data = await res.json();
            map.version = data.version;
            // The box office's summary bar reads these. Only the box office endpoint sends them,
            // and only on a tick where something moved.
            if (data.counts) map.counts = data.counts;
            if (!data.seats || !data.seats.length) return;

            // The WHOLE seat object, not just its state: the box office diff carries the booker,
            // the hold note and the hold kind, and applying only `state` left a seat that had just
            // sold showing no buyer until the next full load. The guest diff sends {id, state}
            // alone, so this is identical for it.
            const byId = new Map(data.seats.map((s) => [s.id, s]));
            (map.levels || []).forEach((lvl) => (lvl.sections || []).forEach((s) => s.seats.forEach((seat) => {
                if (byId.has(seat.id)) Object.assign(seat, byId.get(seat.id));
            })));

            // The payload, not a bare ping: it carries the seat advisory alongside the diff.
            entry.listeners.forEach((fn) => fn(data));
        } catch (e) {
            // A failed poll is not worth surfacing: the next one is five seconds away.
        }
    }, intervalMs);
}

/** Test seam, and used when a picker is the last one to unmount. */
export function resetSeatMaps() {
    pollers.forEach((entry) => clearInterval(entry.timer));
    pollers.clear();
    maps.clear();
}
