{{-- The Style tab of the schedule form (role/edit, #section-style): its grid, its preview and its
     pickers. Plain CSS on the --ap-* tokens, so the six admin palettes follow and no CSS build is
     owed; included inside the page's own style block. The pickers themselves are Vue islands
     (resources/js/style-studio.js), but their looks live here and not in the components: the
     server-rendered parts (the grid, the pinned preview, the logo tile, the fields that are out
     of sight) must be styled before the script has arrived.

     The colours inside .st-pv are literal on purpose: it is a drawing of the PUBLIC page, whose
     panel is white or near-black whatever palette the admin is in.

     Never give anything in this section the max-w-xl class: the setup guide docks beside the
     first one it finds. --}}

{{-- The tab's heading, what the page looks like, and what is set. One column, in that order,
     under 1280px; from there the preview stands beside the fields and holds its place. --}}
#section-style .st-wrap {
    display: grid;
    grid-template-columns: minmax(0, 1fr);
    grid-template-areas: "head" "side" "main";
    gap: 0 2.5rem;
}
#section-style .st-head {
    grid-area: head;
}
{{-- A mark at the foot of the heading. Once it has gone under the top bar the preview is holding
     its place (style-studio.js puts is-stuck on .st-side). --}}
#section-style .st-mark {
    grid-area: head;
    align-self: end;
    width: 1px;
    height: 1px;
    pointer-events: none;
}
#section-style .st-main {
    grid-area: main;
    min-width: 0;
}
#section-style .st-side {
    grid-area: side;
    min-width: 0;
}
@media (min-width: 1280px) {
    #section-style .st-wrap {
        grid-template-columns: minmax(0, 1fr) clamp(15rem, 41%, 23rem);
        grid-template-areas: "head side" "main side";
        grid-template-rows: auto 1fr;
        align-items: start;
    }
    #section-style .st-side {
        position: sticky;
        top: 5.5rem;
    }
    {{-- Short at rest, so its foot clears the save bar before the page has been scrolled; once it
         is holding its place it takes the room there is. Only whole cards are drawn, so growing
         shows one more. --}}
    #section-style .st-side.is-stuck .st-pv {
        height: min(31rem, calc(100vh - 15.5rem));
    }
}
@media (prefers-reduced-motion: no-preference) {
    #section-style .st-pv {
        transition: height 0.2s ease;
    }
}

{{-- The fields that carry the values while a picker stands in for them: in the form, out of
     sight. Only while the islands run (st-js); the page's script takes the class off again if
     they never start, and the fields are then what an owner uses. --}}
#section-style.st-js .st-native {
    position: absolute !important;
    width: 1px !important;
    height: 1px !important;
    {{-- The layout gives every select a least width of its own. --}}
    min-width: 0 !important;
    min-height: 0 !important;
    pointer-events: none;
    overflow: hidden !important;
    clip-path: inset(50%) !important;
    white-space: nowrap !important;
    padding: 0 !important;
    border: 0 !important;
    margin: -1px !important;
}
#section-style:not(.st-js) .vue-style-font,
#section-style:not(.st-js) .vue-style-wall,
#section-style:not(.st-js) .vue-style-gradient {
    display: none;
}

#section-style .st-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    color: rgb(var(--ap-ink-2));
    margin-bottom: 0.375rem;
}
#section-style .st-note {
    font-size: 0.8125rem;
    line-height: 1.4;
    color: rgb(var(--ap-ink-3));
}

{{-- A picker sits a little under its label, as a field does. --}}
#section-style .vue-style-font,
#section-style .vue-style-wall,
#section-style .vue-style-gradient {
    display: block;
    margin-top: 0.375rem;
}
#section-style .st-color,
#section-style .st-logo {
    margin-top: 0.375rem;
}

{{-- The four rows share one column for their names: a longer name (Custom CSS, in Hebrew) used
     to push its line out of the column the others keep. --}}
#section-style .event-subrow .event-row-title {
    min-width: 8rem;
}

#section-style button.st-logo-tile:focus-visible,
#section-style button.st-tile:focus-visible,
#section-style button.st-swatch:focus-visible,
#section-style button.st-grad:focus-visible,
#section-style button.st-font-row:focus-visible,
#section-style button.st-font-btn:focus-visible,
#section-style button.st-pv-toggle:focus-visible,
#section-style .st-seg button:focus-visible,
#section-style input.st-well:focus-visible {
    outline: 2px solid var(--brand-blue);
    outline-offset: 2px;
}

{{-- The logo: its tile, and Change and Remove beside it. --}}
#section-style .st-logo {
    display: flex;
    align-items: center;
    gap: 1rem;
}
#section-style button.st-logo-tile {
    flex: none;
    width: 4.5rem;
    height: 4.5rem;
    border-radius: 1rem;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgb(var(--ap-bg));
    border: 1px solid rgb(var(--ap-border));
    color: rgb(var(--ap-ink-4));
    cursor: pointer;
    transition: border-color 0.2s;
    padding: 0;
}
#section-style button.st-logo-tile.is-empty {
    border-color: rgb(var(--ap-border-strong));
}
#section-style button.st-logo-tile:hover {
    border-color: var(--brand-blue);
}
#section-style .st-logo-tile img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
#section-style .st-logo-tile svg {
    width: 1.5rem;
    height: 1.5rem;
}
#section-style .st-logo-tile:not(.is-empty) svg {
    display: none;
}
#section-style .st-logo-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.25rem 1rem;
    align-items: center;
    min-width: 0;
}
#section-style .st-logo-name {
    flex-basis: 100%;
    font-size: 0.8125rem;
    color: rgb(var(--ap-ink-3));
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
#section-style .st-logo-name:empty {
    display: none;
}

{{-- A colour: the field itself drawn as a square well, what it is in hex, and a few to start
     from. The hex box and the dots are the island's (StyleColorField), which has no box of its
     own: they stand in the row beside the well. --}}
#section-style .st-color {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.625rem 0.5rem;
}
#section-style .vue-style-color {
    display: contents;
}
#section-style input.st-well {
    -webkit-appearance: none;
    appearance: none;
    flex: none;
    display: block;
    width: 2.5rem !important;
    height: 2.5rem !important;
    padding: 0 !important;
    margin: 0 !important;
    border: 0 !important;
    border-radius: 0.75rem;
    background: none;
    box-shadow: none;
    cursor: pointer;
    overflow: hidden;
}
#section-style input.st-well::-webkit-color-swatch-wrapper {
    padding: 0;
}
#section-style input.st-well::-webkit-color-swatch {
    border: 1px solid rgb(var(--ap-border-strong));
    border-radius: 0.75rem;
}
#section-style input.st-well::-moz-color-swatch {
    border: 1px solid rgb(var(--ap-border-strong));
    border-radius: 0.75rem;
}
#section-style input.st-hex {
    width: 5.5rem;
    height: 2.5rem;
    padding: 0 0.5rem !important;
    text-align: center;
    font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
    font-size: 0.875rem !important;
    letter-spacing: 0.02em;
    text-transform: uppercase;
    border-radius: 0.625rem;
    border: 1px solid rgb(var(--ap-border-strong));
    background: rgb(var(--ap-surface));
    color: rgb(var(--ap-ink));
}
#section-style input.st-hex.is-bad {
    border-color: #dc2626;
}
#section-style .st-swatches {
    flex-basis: 100%;
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}
#section-style button.st-swatch {
    width: 1.5rem;
    height: 1.5rem;
    border-radius: 999px;
    border: 0;
    padding: 0;
    cursor: pointer;
    box-shadow: inset 0 0 0 1px rgb(var(--ap-border-strong));
    transition: transform 0.15s;
}
.dark #section-style button.st-swatch {
    box-shadow: inset 0 0 0 1px rgb(255 255 255 / 0.5);
}
#section-style button.st-swatch:hover {
    transform: scale(1.12);
}
#section-style button.st-swatch.is-on,
.dark #section-style button.st-swatch.is-on {
    box-shadow: 0 0 0 2px rgb(var(--ap-surface)), 0 0 0 4px var(--brand-blue);
}
@media (pointer: coarse) {
    #section-style button.st-swatch {
        width: 2rem;
        height: 2rem;
    }
}

{{-- The font: the schedule's name in the chosen face, then in every face the same way. --}}
#section-style button.st-font-btn {
    display: flex;
    width: 100%;
    align-items: center;
    gap: 0.75rem;
    min-height: 3.5rem;
    padding: 0.5rem 0.875rem;
    border-radius: 0.75rem;
    border: 1px solid rgb(var(--ap-border-strong));
    background: rgb(var(--ap-surface));
    color: rgb(var(--ap-ink));
    text-align: start;
    cursor: pointer;
    transition: border-color 0.2s;
}
#section-style button.st-font-btn:hover {
    border-color: var(--brand-blue);
}
#section-style button.st-font-btn[aria-expanded="true"] {
    border-color: var(--brand-blue);
    border-radius: 0.75rem 0.75rem 0 0;
}
#section-style .st-font-sample {
    flex: 1;
    min-width: 0;
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1.2;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-synthesis: none;
}
#section-style .st-font-name {
    flex: none;
    font-size: 0.8125rem;
    color: rgb(var(--ap-ink-3));
    max-width: 40%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
#section-style .st-chev {
    flex: none;
    width: 1rem;
    height: 1rem;
    color: rgb(var(--ap-ink-4));
    transition: transform 0.2s;
}
#section-style [aria-expanded="true"] > .st-chev {
    transform: rotate(180deg);
}
#section-style .st-font-panel {
    border: 1px solid var(--brand-blue);
    border-top: 0;
    border-radius: 0 0 0.75rem 0.75rem;
    background: rgb(var(--ap-surface));
    overflow: hidden;
}
#section-style .st-search {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0.875rem;
    border-bottom: 1px solid rgb(var(--ap-border));
    color: rgb(var(--ap-ink-4));
}
#section-style .st-search svg {
    width: 1rem;
    height: 1rem;
    flex: none;
}
#section-style .st-search input {
    flex: 1;
    min-width: 0;
    border: 0 !important;
    background: transparent !important;
    box-shadow: none !important;
    padding: 0.25rem 0 !important;
    font-size: 0.875rem !important;
    color: rgb(var(--ap-ink));
    outline: none !important;
}
#section-style .st-font-list {
    overflow-y: auto;
    overscroll-behavior: contain;
}
#section-style button.st-font-row {
    display: flex;
    width: 100%;
    align-items: center;
    gap: 0.75rem;
    height: 2.75rem;
    padding: 0 0.875rem;
    border: 0;
    background: transparent;
    color: rgb(var(--ap-ink));
    text-align: start;
    cursor: pointer;
}
#section-style button.st-font-row:hover {
    background: rgb(var(--ap-bg));
}
#section-style button.st-font-row.is-on {
    background: var(--brand-blue-a10, rgb(78 129 250 / 0.1));
}
#section-style button.st-font-row .st-font-sample {
    font-size: 1.25rem;
}
{{-- In the list the face's name is never cut short; the sample gives way. --}}
#section-style button.st-font-row .st-font-name {
    max-width: none;
}
#section-style button.st-font-row.is-on .st-font-name {
    color: var(--brand-blue);
    font-weight: 600;
}
#section-style .st-empty {
    padding: 1rem 0.875rem;
    font-size: 0.875rem;
    color: rgb(var(--ap-ink-3));
}

{{-- A wall of pictures to choose from. --}}
#section-style .st-wall {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    padding: 3px;
    margin: -3px;
}
{{-- The choices that are not a built-in picture: two by two at every width, the mark and the
     words on one line, so nothing wraps into a third line in a longer language. --}}
#section-style .st-choices {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    grid-auto-rows: 2.75rem;
    gap: 0.5rem;
}
#section-style .st-tiles {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(6.25rem, 1fr));
    grid-auto-rows: var(--st-tile-h, 3.5rem);
    gap: 0.5rem;
    overflow: hidden;
    padding: 3px;
    margin: -3px;
}
#section-style .st-tiles.is-folded {
    max-height: calc(var(--st-tile-h, 3.5rem) * 2 + 0.5rem + 6px);
}
#section-style .st-tiles.is-tall {
    --st-tile-h: 4.25rem;
}
#section-style button.st-tile {
    position: relative;
    border: 0;
    padding: 0;
    border-radius: 0.625rem;
    overflow: hidden;
    cursor: pointer;
    background: rgb(var(--ap-bg));
    color: rgb(var(--ap-ink-2));
    box-shadow: inset 0 0 0 1px rgb(var(--ap-border));
    transition: transform 0.15s, box-shadow 0.15s;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    font-size: 0.6875rem;
    font-weight: 500;
    line-height: 1.15;
    text-align: center;
    min-width: 0;
}
#section-style button.st-tile img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}
#section-style button.st-tile svg {
    width: 1.125rem;
    height: 1.125rem;
    color: rgb(var(--ap-ink-2));
}
#section-style button.st-tile:hover {
    transform: translateY(-1px);
    box-shadow: inset 0 0 0 1px rgb(var(--ap-border-strong)), 0 4px 10px rgb(0 0 0 / 0.12);
}
#section-style button.st-tile.is-on {
    box-shadow: 0 0 0 2px rgb(var(--ap-surface)), 0 0 0 4px var(--brand-blue);
}
#section-style button.st-tile.is-on::after,
#section-style button.st-grad.is-on::after {
    content: "";
    position: absolute;
    top: 0.25rem;
    inset-inline-end: 0.25rem;
    width: 1.125rem;
    height: 1.125rem;
    border-radius: 999px;
    background: var(--brand-blue) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke-width='3' stroke='white'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M4.5 12.75l6 6 9-13.5'/%3E%3C/svg%3E") center / 0.625rem no-repeat;
    box-shadow: 0 0 0 1.5px rgb(255 255 255 / 0.9);
}
#section-style .st-choices button.st-tile {
    flex-direction: row;
    justify-content: flex-start;
    gap: 0.5rem;
    padding: 0 0.75rem;
    font-size: 0.8125rem;
    text-align: start;
}
#section-style .st-choices button.st-tile.is-on {
    padding-inline-end: 2.25rem;
}
#section-style .st-choices button.st-tile.is-on::after {
    top: 50%;
    transform: translateY(-50%);
    inset-inline-end: 0.625rem;
}
#section-style .st-tile-over {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    min-width: 0;
}
#section-style .st-tile-over svg {
    flex: none;
}
{{-- Two lines at the most, so a longer label is not cut short. --}}
#section-style .st-tile-cap {
    line-height: 1.2;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}
{{-- The accent gradient's tile is a tile like the others; its mark is a drop of the wash. --}}
#section-style .st-tile-wash {
    flex: none;
    width: 1.125rem;
    height: 1.125rem;
    border-radius: 0.3125rem;
    box-shadow: inset 0 0 0 1px rgb(var(--ap-border-strong));
}
{{-- A picture of the owner's own, in the Upload tile. --}}
#section-style .st-tile-thumb {
    position: relative;
    z-index: 1;
    flex: none;
    width: 2.5rem;
    height: 1.75rem;
    border-radius: 0.3125rem;
    background: center / cover no-repeat;
    box-shadow: inset 0 0 0 1px rgb(var(--ap-border));
}
#section-style .st-own {
    display: flex;
    gap: 1rem;
    font-size: 0.8125rem;
}
#section-style .st-more {
    margin-top: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    font-size: 0.8125rem;
    color: rgb(var(--ap-ink-3));
    min-height: 1.5rem;
}
#section-style .st-picked {
    min-width: 0;
    font-weight: 600;
    color: rgb(var(--ap-ink));
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
#section-style .st-picked:empty {
    display: none;
}
{{-- Show All keeps its place at the end of the line, with or without a name before it. --}}
#section-style .st-more > button.event-link {
    margin-inline-start: auto;
}

{{-- Gradients: all of them, in the order of the rainbow. --}}
#section-style .st-grad-box {
    border: 1px solid rgb(var(--ap-border));
    border-radius: 0.75rem;
    overflow: hidden;
    background: rgb(var(--ap-surface));
}
#section-style .st-hues {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    padding: 0.625rem 0.75rem;
    border-bottom: 1px solid rgb(var(--ap-border));
}
#section-style button.st-hue {
    width: 1.125rem;
    height: 1.125rem;
    border: 0;
    padding: 0;
    border-radius: 999px;
    cursor: pointer;
    box-shadow: inset 0 0 0 1px rgb(0 0 0 / 0.1);
    transition: transform 0.15s;
}
#section-style button.st-hue:hover {
    transform: scale(1.15);
}
#section-style button.st-hue.is-on {
    box-shadow: 0 0 0 2px rgb(var(--ap-surface)), 0 0 0 3.5px rgb(var(--ap-ink-2));
}
@media (pointer: coarse) {
    #section-style button.st-hue {
        width: 1.5rem;
        height: 1.5rem;
    }
}
{{-- Five lines and half of the sixth, which is what says there is more below. Not a positioning
     ground: the picker measures each swatch against the same ancestor it measures the grid
     against, and a position here made its ring land on the wrong dot at every width. --}}
#section-style .st-grads {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(2.25rem, 1fr));
    grid-auto-rows: 2.25rem;
    gap: 0.375rem;
    padding: 0.625rem;
    max-height: calc(2.25rem * 5.5 + 0.375rem * 5 + 0.625rem);
    overflow-y: auto;
    overscroll-behavior: contain;
}
#section-style button.st-grad {
    position: relative;
    border: 0;
    padding: 0;
    border-radius: 0.5rem;
    cursor: pointer;
    box-shadow: inset 0 0 0 1px rgb(0 0 0 / 0.08);
    transition: transform 0.12s;
    min-width: 0;
}
#section-style button.st-grad:hover {
    transform: scale(1.1);
    z-index: 1;
    box-shadow: 0 3px 8px rgb(0 0 0 / 0.25);
}
#section-style button.st-grad.is-on {
    box-shadow: 0 0 0 2px rgb(var(--ap-surface)), 0 0 0 4px var(--brand-blue);
    z-index: 1;
}
#section-style button.st-grad.is-on::after {
    top: 50%;
    inset-inline-end: 50%;
    transform: translate(50%, -50%);
}
[dir="rtl"] #section-style button.st-grad.is-on::after {
    transform: translate(-50%, -50%);
}
{{-- The first swatch: the owner's own two colours, with a plus while it is not the one chosen. --}}
#section-style button.st-grad.is-custom {
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
}
#section-style button.st-grad.is-custom svg {
    width: 1rem;
    height: 1rem;
    filter: drop-shadow(0 0 2px rgb(0 0 0 / 0.55));
}
#section-style button.st-grad.is-custom.is-on svg {
    display: none;
}
#section-style .st-grad-foot {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    padding: 0.5rem 0.75rem;
    border-top: 1px solid rgb(var(--ap-border));
    font-size: 0.8125rem;
    color: rgb(var(--ap-ink-3));
    min-height: 2.25rem;
}
#section-style .st-grad-foot b {
    font-weight: 600;
    color: rgb(var(--ap-ink));
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
#section-style .st-grad-foot .st-credit {
    flex: none;
    font-size: 0.75rem;
    white-space: nowrap;
}
{{-- The two colours of a gradient of the owner's own, with the arrow between them, on one line
     down to a laptop's column. --}}
#section-style .st-custom {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
#section-style .st-custom .st-color {
    flex-wrap: nowrap;
    margin-top: 0;
}
#section-style .st-custom .st-arrow {
    width: 1.125rem;
    height: 1.125rem;
    color: rgb(var(--ap-ink-4));
    flex: none;
}

{{-- The animation's own block shrinks with its column: a fieldset will not, left to itself, and
     on a phone it ran off the edge of the screen. --}}
#section-style #style-content-animation fieldset {
    min-width: 0;
}

{{-- The preview. --}}
#section-style .st-pv-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    margin-bottom: 0.5rem;
    min-height: 2rem;
}
#section-style .st-pv-title {
    font-size: 0.875rem;
    font-weight: 500;
    color: rgb(var(--ap-ink-2));
}
#section-style .st-pv-tools {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}
#section-style .st-seg {
    display: inline-flex;
    padding: 2px;
    border-radius: 0.625rem;
    background: rgb(var(--ap-bg));
    box-shadow: inset 0 0 0 1px rgb(var(--ap-border));
}
#section-style .st-seg button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2rem;
    height: 1.625rem;
    border: 0;
    border-radius: 0.5rem;
    background: transparent;
    color: rgb(var(--ap-ink-3));
    cursor: pointer;
}
#section-style .st-seg button svg {
    width: 0.9375rem;
    height: 0.9375rem;
}
#section-style .st-seg button[aria-pressed="true"] {
    background: rgb(var(--ap-surface));
    color: rgb(var(--ap-ink));
    box-shadow: 0 1px 2px rgb(0 0 0 / 0.14), inset 0 0 0 1px rgb(var(--ap-border));
}
#section-style button.st-pv-toggle {
    display: none;
    align-items: center;
    justify-content: center;
    width: 2rem;
    height: 2rem;
    border: 0;
    border-radius: 0.5rem;
    background: transparent;
    color: rgb(var(--ap-ink-3));
    cursor: pointer;
}
#section-style button.st-pv-toggle svg {
    width: 1.125rem;
    height: 1.125rem;
    transition: transform 0.2s;
}
#section-style button.st-pv-toggle[aria-expanded="true"] svg {
    transform: rotate(180deg);
}
#section-style .st-pv {
    --pv-panel: rgb(255 255 255 / 0.95);
    --pv-ink: #151b26;
    --pv-soft: #5b6472;
    --pv-line: rgb(0 0 0 / 0.08);
    --pv-tile: #ffffff;
    position: relative;
    border-radius: 0.875rem;
    overflow: hidden;
    border: 1px solid rgb(var(--ap-border));
    background-color: #e8eaee;
    height: min(31rem, calc(100vh - 26.5rem));
    min-height: 11rem;
}
#section-style .st-pv[data-mode="dark"] {
    --pv-panel: rgb(30 30 30 / 0.95);
    --pv-ink: #f3f4f6;
    --pv-soft: #a3a9b4;
    --pv-line: rgb(255 255 255 / 0.1);
    --pv-tile: #2a2a2c;
}
#section-style .st-pv-page {
    height: 100%;
    box-sizing: border-box;
    padding: 0.625rem 0.625rem 0;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}
#section-style .st-pv-first {
    flex: none;
}
#section-style .st-pv-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}
{{-- Only whole cards: one that does not fit wraps into a second column, which is out of sight. --}}
#section-style .st-pv-list.is-cards {
    flex: 1 1 0;
    min-height: 0;
    flex-wrap: wrap;
    align-content: flex-start;
    overflow: hidden;
}
#section-style .st-pv-list.is-cards > .st-pv-card {
    width: 100%;
    flex: none;
}
#section-style .st-pv[data-header="compact"] .st-pv-page {
    padding-top: 0;
}
{{-- A month can be taller than the drawing: it fades out at the foot. Cards never need it. --}}
#section-style .st-pv-fade {
    position: absolute;
    inset: auto 0 0;
    height: 2.5rem;
    background: linear-gradient(to bottom, transparent, rgb(0 0 0 / 0.16));
    pointer-events: none;
}
#section-style .st-pv:not([data-layout="calendar"]) .st-pv-fade {
    display: none;
}
#section-style .st-pv-head {
    position: relative;
    border-radius: 0.875rem;
    background: var(--pv-panel);
    overflow: hidden;
    box-shadow: 0 6px 18px rgb(0 0 0 / 0.14);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
}
#section-style .st-pv-stage {
    height: 5rem;
    background: #111 center / cover no-repeat;
}
#section-style .st-pv-wash {
    position: absolute;
    inset: 0 0 auto;
    height: 4.5rem;
    pointer-events: none;
}
#section-style .st-pv-wall {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    align-content: center;
    gap: 0.3125rem;
    padding: 0.625rem 1.5rem 0.25rem;
    min-height: 4rem;
}
#section-style .st-pv-wall span {
    width: 1.375rem;
    height: 1.375rem;
    border-radius: 0.25rem;
    background: #fff center / contain no-repeat;
    box-shadow: inset 0 0 0 1px rgb(127 127 127 / 0.35);
}
#section-style .st-pv-body {
    position: relative;
    padding: 0.75rem 0.875rem 0.875rem;
}
#section-style .st-pv-head.has-stage .st-pv-body {
    padding-top: 0;
}
#section-style .st-pv-row {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 0.5rem;
    min-height: 1.5rem;
}
#section-style .st-pv-logo {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 22%;
    object-fit: cover;
    background: var(--pv-tile);
    box-shadow: 0 0 0 2px var(--pv-tile), 0 2px 6px rgb(0 0 0 / 0.25);
    flex: none;
}
#section-style .st-pv-head.has-stage .st-pv-logo {
    margin-top: -1.375rem;
}
#section-style .st-pv-head.has-stage .st-pv-row {
    padding-top: 0.5rem;
}
#section-style .st-pv-follow {
    display: inline-block;
    border-radius: 0.4375rem;
    padding: 0.3125rem 0.6875rem;
    font-size: 0.6875rem;
    font-weight: 700;
    line-height: 1.2;
    box-shadow: 0 1px 2px rgb(0 0 0 / 0.15);
    white-space: nowrap;
}
#section-style .st-pv-name {
    margin-top: 0.5rem;
    font-size: 1.1875rem;
    font-weight: 700;
    line-height: 1.12;
    color: var(--pv-ink);
    font-synthesis: none;
    overflow-wrap: anywhere;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
#section-style .st-pv-facts {
    margin-top: 0.4375rem;
    display: flex;
    gap: 0.375rem;
}
#section-style .st-pv-facts i,
#section-style .st-pv-lines i {
    display: block;
    height: 0.3125rem;
    border-radius: 999px;
    background: var(--pv-soft);
    opacity: 0.35;
}
{{-- The slim bar of the Compact header. --}}
#section-style .st-pv-bar {
    margin: 0 -0.625rem;
    padding: 0.5625rem 0.75rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: var(--pv-panel);
    border-bottom: 1px solid var(--pv-line);
    box-shadow: 0 2px 8px rgb(0 0 0 / 0.1);
}
#section-style .st-pv-bar .st-pv-logo {
    width: 1.625rem;
    height: 1.625rem;
    box-shadow: none;
}
#section-style .st-pv-bar .st-pv-name {
    margin: 0;
    flex: 1;
    min-width: 0;
    font-size: 0.9375rem;
    display: block;
    white-space: nowrap;
    text-overflow: ellipsis;
}
#section-style .st-pv-day {
    align-self: center;
    margin-top: 0.125rem;
    padding: 0.25rem 0.625rem;
    border-radius: 0.5rem;
    background: var(--pv-panel);
    color: var(--pv-ink);
    font-size: 0.6875rem;
    font-weight: 600;
}
#section-style .st-pv-card {
    display: flex;
    align-items: stretch;
    border-radius: 0.75rem;
    background: var(--pv-panel);
    overflow: hidden;
    box-shadow: 0 4px 12px rgb(0 0 0 / 0.1);
    min-height: 4.5rem;
}
#section-style .st-pv-card-text {
    flex: 1;
    min-width: 0;
    padding: 0.625rem 0.75rem;
}
#section-style .st-pv-card-title {
    font-size: 0.8125rem;
    font-weight: 700;
    line-height: 1.2;
    color: var(--pv-ink);
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
#section-style .st-pv-when {
    margin-top: 0.4375rem;
    display: flex;
    align-items: center;
    gap: 0.4375rem;
}
#section-style .st-pv-date {
    flex: none;
    width: 1.75rem;
    height: 1.75rem;
    border-radius: 0.4375rem;
    background: var(--pv-tile);
    box-shadow: inset 0 0 0 1px var(--pv-line);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    line-height: 1;
    color: var(--pv-ink);
}
#section-style .st-pv-date small {
    font-size: 0.375rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}
#section-style .st-pv-date b {
    font-size: 0.75rem;
    font-weight: 700;
}
#section-style .st-pv-icon {
    flex: none;
    width: 1.75rem;
    height: 1.75rem;
    border-radius: 0.4375rem;
    background: var(--pv-tile);
    box-shadow: inset 0 0 0 1px var(--pv-line);
    display: flex;
    align-items: center;
    justify-content: center;
}
#section-style .st-pv-icon svg {
    width: 0.875rem;
    height: 0.875rem;
}
#section-style .st-pv-lines {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    min-width: 0;
}
#section-style .st-pv-card-img {
    flex: none;
    width: 34%;
    background: #1c1c1e center / cover no-repeat;
}
{{-- The month, where Calendar is the layout. --}}
#section-style .st-pv-month {
    border-radius: 0.75rem;
    background: var(--pv-panel);
    padding: 0.625rem;
    box-shadow: 0 4px 12px rgb(0 0 0 / 0.1);
}
#section-style .st-pv-month-title {
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--pv-ink);
    margin-bottom: 0.4375rem;
}
#section-style .st-pv-grid {
    display: grid;
    grid-template-columns: repeat(7, minmax(0, 1fr));
    gap: 0.1875rem;
}
#section-style .st-pv-cell {
    aspect-ratio: 1 / 1;
    border-radius: 0.3125rem;
    box-shadow: inset 0 0 0 1px var(--pv-line);
    padding: 0.1875rem 0.25rem;
    font-size: 0.5rem;
    font-weight: 600;
    line-height: 1;
    color: var(--pv-soft);
    display: flex;
    flex-direction: column;
    gap: 0.125rem;
    min-width: 0;
}
#section-style .st-pv-cell.is-out {
    box-shadow: none;
}
#section-style .st-pv-cell u {
    display: block;
    height: 0.25rem;
    border-radius: 999px;
    text-decoration: none;
}
#section-style .st-pv-foot {
    margin-top: 0.625rem;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.25rem 0.75rem;
    font-size: 0.8125rem;
    color: rgb(var(--ap-ink-2));
}
#section-style .st-pv-view {
    display: inline-flex;
    align-items: center;
    gap: 0.3125rem;
}
#section-style .st-pv-view svg {
    width: 0.8125rem;
    height: 0.8125rem;
}

{{-- Under 1280px (a laptop with the sidebar open, a tablet, a phone) the preview is a strip
     pinned under the tab's heading, of one height whatever it shows, so nothing under it moves
     when its view changes. It shows the part the open row changes: the header, the event cards
     while Events is open, the header stepped back so the ground shows while Background is open.
     It wears the card's own ground (--ap-card-mid, the middle of the card's own gradient) and
     runs to the card's edges, so it is not a box of another colour inside the card. --}}
@media (max-width: 1279px) {
    #section-style .st-side {
        --st-pad: 1rem;
        position: sticky;
        top: 4rem;
        z-index: 5;
        margin: 0 calc(-1 * var(--st-pad)) 1rem;
        padding: 0.375rem var(--st-pad) 0.625rem;
        background: rgb(var(--ap-card-mid));
    }
    {{-- A soft edge under it, only while it is pinned: not a second hairline over a row's own. --}}
    #section-style .st-side::after {
        content: "";
        position: absolute;
        inset: 100% 0 auto;
        height: 0.5rem;
        background: linear-gradient(to bottom, rgb(0 0 0 / 0.07), transparent);
        pointer-events: none;
        opacity: 0;
        transition: opacity 0.15s;
    }
    #section-style .st-side.is-stuck::after {
        opacity: 1;
    }
    {{-- While something is being typed the strip lets go: the keyboard needs the room. --}}
    #section-style .st-wrap:has(.st-main input[type="text"]:focus, .st-main textarea:focus) .st-side {
        position: static;
    }
    #section-style button.st-pv-toggle {
        display: inline-flex;
    }
    #section-style .st-pv-foot {
        display: none;
    }
    #section-style .st-pv {
        height: 9.5rem;
        min-height: 0;
    }
    #section-style .st-pv-page {
        max-width: 23rem;
        margin-inline: auto;
        padding-bottom: 0.625rem;
    }
    #section-style .st-pv-stage {
        height: 3rem;
    }
    #section-style .st-pv:not([data-open="true"])[data-view="header"] .st-pv-list,
    #section-style .st-pv:not([data-open="true"])[data-view="background"] .st-pv-list,
    #section-style .st-pv:not([data-open="true"])[data-view="events"] .st-pv-first {
        display: none;
    }
    #section-style .st-pv:not([data-open="true"])[data-view="background"] .st-pv-page {
        transform: scale(0.7);
        transform-origin: 50% 0;
    }
    #section-style .st-pv:not([data-open="true"])[data-view="events"][data-header="compact"] .st-pv-page {
        padding-top: 0.625rem;
    }
    {{-- Opened, it is the whole preview and no longer holds its place: pinned at that height it
         left a laptop two lines of the controls to work in. --}}
    #section-style .st-side:has(.st-pv[data-open="true"]) {
        position: static;
    }
    #section-style .st-pv[data-open="true"] {
        height: auto;
    }
    #section-style .st-pv[data-open="true"] .st-pv-page {
        height: auto;
    }
    #section-style .st-pv[data-open="true"] .st-pv-list.is-cards {
        flex: none;
        flex-wrap: nowrap;
        overflow: visible;
    }
}
@media (min-width: 640px) and (max-width: 1279px) {
    #section-style .st-side {
        --st-pad: 2rem;
    }
}
