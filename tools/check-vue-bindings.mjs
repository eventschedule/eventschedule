/**
 * Fail on any identifier a <script setup> template references but does not define.
 *
 * Vue resolves template identifiers at RUNTIME, so deleting a function a template calls builds
 * cleanly, passes every PHP test, and only fails when someone drags the thing it was wired to.
 * That is exactly how startSectionDrag/startTableDrag were removed by an over-greedy edit and
 * survived a build, a full test suite and a screenshot of a different component.
 *
 * In <script setup>, an identifier that is not a setup binding compiles to `_ctx.<name>`. This
 * project uses no Options API and no mixins in these components, so `_ctx.` is always a hole.
 *
 * Usage: node tools/check-vue-bindings.mjs resources/js/components/*.vue
 *
 * This runs from `npm run build`, so a false positive BLOCKS A RELEASE. If one shows up and the
 * identifier really is fine:
 *
 *   1. A browser or library global the script calls without importing -> add it to GLOBALS below.
 *      That is the usual answer, and it keeps the check honest for everything else.
 *   2. Something genuinely outside both passes -> run `vite build` directly to ship, and open an
 *      issue. Do not delete the check to get a release out; it has caught two real bugs, one of
 *      them pre-existing.
 *
 * It is deliberately a list rather than a heuristic, so anything it cannot account for is reported
 * for a human to decide rather than quietly assumed to be fine.
 */
import { parse, compileScript } from '@vue/compiler-sfc';
import fs from 'node:fs';

const ALLOWED = new Set(['$slots', '$attrs', '$props', '$emit']);

/**
 * Globals and built-ins a <script setup> block may call without declaring.
 *
 * Deliberately a list rather than a heuristic: the point of the script pass below is to notice a
 * function that no longer exists, and anything it cannot account for should be reported so a human
 * decides, not quietly assumed to be fine.
 */
const GLOBALS = new Set([
    'Array', 'Boolean', 'Date', 'Error', 'JSON', 'Map', 'Math', 'Number', 'Object', 'Promise',
    'RegExp', 'Set', 'String', 'Symbol', 'WeakMap', 'WeakSet',
    'alert', 'atob', 'btoa', 'clearInterval', 'clearTimeout', 'confirm', 'console',
    'decodeURIComponent', 'encodeURIComponent', 'document', 'fetch', 'isFinite', 'isNaN',
    'localStorage', 'navigator', 'parseFloat', 'parseInt', 'queueMicrotask',
    'requestAnimationFrame', 'sessionStorage', 'setInterval', 'setTimeout', 'structuredClone',
    'window', 'CustomEvent', 'Event', 'KeyboardEvent', 'MouseEvent', 'PointerEvent', 'WheelEvent',
    'FormData', 'URL', 'URLSearchParams', 'Intl', 'ResizeObserver', 'IntersectionObserver',
    'MutationObserver', 'AbortController', 'Blob', 'FileReader', 'Image',
    'if', 'for', 'while', 'switch', 'catch', 'return', 'typeof', 'function', 'await', 'super',
    // Vue compiler macros: available in <script setup> without an import.
    'defineProps', 'defineEmits', 'defineExpose', 'defineOptions', 'defineModel', 'withDefaults',
]);

let failed = false;

for (const file of process.argv.slice(2)) {
    const src = fs.readFileSync(file, 'utf8');
    const { descriptor } = parse(src, { filename: file });

    if (! descriptor.scriptSetup) {
        console.log(`skip ${file} (no <script setup>)`);
        continue;
    }

    const out = compileScript(descriptor, { id: file, inlineTemplate: true });

    // Strip comments first. A doc comment explaining this very check would otherwise report itself,
    // which is exactly what happened the first time this ran.
    const code = out.content
        .replace(/\/\*[\s\S]*?\*\//g, '')
        .replace(/(^|[^:])\/\/[^\n]*/g, '$1');

    const missing = [...new Set([...code.matchAll(/_ctx\.([A-Za-z_$][\w$]*)/g)].map((m) => m[1]))]
        .filter((name) => ! ALLOWED.has(name));

    // The SCRIPT pass. The template pass above cannot see a function that is only ever called from
    // script - which is exactly how svgPoint() was deleted here and went unnoticed through five
    // phases of work, a clean build and a full test suite, because every drag failed silently.
    const script = descriptor.scriptSetup.content;
    const bare = script
        .replace(/\/\*[\s\S]*?\*\//g, '')
        .replace(/(^|[^:])\/\/[^\n]*/g, '$1')
        .replace(/(['"`])(?:\\.|(?!\1)[^\\])*\1/g, "''");

    const declared = new Set();
    for (const m of bare.matchAll(/\b(?:function|const|let|var|class)\s+([A-Za-z_$][\w$]*)/g)) declared.add(m[1]);
    // Imports, destructuring and parameter lists, taken loosely: a name that appears on the left of
    // an `=`, inside `{ }` or `( )`, is something this file can see.
    for (const m of bare.matchAll(/(?:import|const|let|var)\s*\{([^}]*)\}/g)) {
        for (const part of m[1].split(',')) {
            const name = part.split(':').pop().trim().replace(/^\.\.\./, '');
            if (name) declared.add(name);
        }
    }
    for (const m of bare.matchAll(/import\s+([A-Za-z_$][\w$]*)/g)) declared.add(m[1]);
    for (const m of bare.matchAll(/\(([^)\n]*)\)\s*=>/g)) {
        for (const part of m[1].split(',')) {
            const name = part.trim().replace(/^\.\.\./, '').split('=')[0].trim();
            if (/^[A-Za-z_$][\w$]*$/.test(name)) declared.add(name);
        }
    }
    // Object method shorthand - `{ onAdd() {...} }` - and any name the file itself guards with a
    // typeof check, which is how an optional vendor global is used here.
    for (const m of bare.matchAll(/([A-Za-z_$][\w$]*)\s*\([^)\n]*\)\s*\{/g)) declared.add(m[1]);
    for (const m of bare.matchAll(/typeof\s+([A-Za-z_$][\w$]*)/g)) declared.add(m[1]);
    for (const m of bare.matchAll(/function[^(\n]*\(([^)\n]*)\)/g)) {
        for (const part of m[1].split(',')) {
            const name = part.trim().replace(/^\.\.\./, '').split('=')[0].trim();
            if (/^[A-Za-z_$][\w$]*$/.test(name)) declared.add(name);
        }
    }

    const uncalled = [...new Set([...bare.matchAll(/(^|[^.\w$'"`])([a-zA-Z_$][\w$]*)\s*\(/g)].map((m) => m[2]))]
        .filter((name) => ! declared.has(name) && ! GLOBALS.has(name) && ! ALLOWED.has(name));

    const problems = [
        ...missing.map((n) => `template: ${n}`),
        ...uncalled.map((n) => `script: ${n}()`),
    ];

    if (problems.length) {
        failed = true;
        console.error(`UNDEFINED in ${file}: ${problems.join(', ')}`);
    } else {
        console.log(`ok ${file}`);
    }
}

process.exit(failed ? 1 : 0);
