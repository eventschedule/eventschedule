/**
 * A Laravel plural string for a number, as App\Support\PluralSelector reads it: exact forms
 * ({0}, [2,19]) first, then the plain forms by the language's own rule (Russian and Romanian
 * follow the last digits: 21 is like 1, 101 in Romanian like 1). Strings with only exact forms
 * read as Laravel reads them.
 */
const EXACT = /^\s*[{[]([-?\d|*,.*]*)[}\]](.*)$/s;

function pluralIndex(locale, n) {
    const lang = String(locale || 'en').toLowerCase().split(/[-_]/)[0];
    const i = Math.floor(Math.abs(n));
    switch (lang) {
        case 'ru':
        case 'uk':
            return i % 10 === 1 && i % 100 !== 11 ? 0 : (i % 10 >= 2 && i % 10 <= 4 && (i % 100 < 10 || i % 100 >= 20) ? 1 : 2);
        case 'ro':
            return n === 1 ? 0 : (n === 0 || (i % 100 > 0 && i % 100 < 20) ? 1 : 2);
        case 'fr':
            return n === 0 || n === 1 ? 0 : 1;
        case 'ar':
            return n === 0 ? 0 : (n === 1 ? 1 : (n === 2 ? 2 : (i % 100 >= 3 && i % 100 <= 10 ? 3 : (i % 100 >= 11 && i % 100 <= 99 ? 4 : 5))));
        default:
            return n === 1 ? 0 : 1;
    }
}

function exact(segment, n) {
    const m = segment.match(EXACT);
    if (!m) return null;
    const cond = m[1];
    if (cond.includes(',')) {
        const [from, to] = cond.split(',');
        if ((to === '*' && n >= Number(from)) || (from === '*' && n <= Number(to)) || (n >= Number(from) && n <= Number(to))) return m[2];
        return null;
    }
    return Number(cond) === n ? m[2] : null;
}

export function choose(line, n, locale = (typeof document !== 'undefined' ? document.documentElement.lang : 'en')) {
    const segments = String(line || '').split('|');
    let text = null;
    for (const s of segments) {
        const v = exact(s, n);
        if (v !== null) { text = v; break; }
    }
    if (text === null) {
        const plain = segments.filter((s) => !EXACT.test(s));
        const pool = plain.length ? plain : segments.map((s) => s.replace(/^\s*[{[][-?\d|*,.*]*[}\]]/, ''));
        const idx = pluralIndex(locale, n);
        text = pool[idx] ?? pool[pool.length - 1] ?? '';
    }
    return text.trim().replace(/:count/g, String(n));
}
