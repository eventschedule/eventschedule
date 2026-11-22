{{-- The pieces the guest portal's smaller pages share on the stage (partials/guest-stage-styles):
     the forms a visitor fills in (a booking request, the submit page, the event password, the
     feedback form, the claim pages) and the pages that only say one thing (an event an embed
     cannot show, an unclaimed schedule). Each is one sheet over the schedule's background, a
     schedule's cover across its top when it has one, its picture small beside its name, a large
     title, and the form as a card raised on the sheet.

     Included by those views only, after the stage styles the layout prints. The kit's rules hold
     here: tokens for every colour (the stage's inks, never a literal), rem sizes, logical
     properties, one class per rule where it can be, no !important, Blade comments only. --}}
<style {!! nonce_attr() !!}>
    {{-- A sheet sized to what it holds: a form is not stretched across a laptop. --}}
    .gk-pg-md { max-width: 50rem; }
    .gk-pg-sm { max-width: 38rem; }
    @media (min-width: 40rem) {
        .gk-pg-sm { margin-top: clamp(1rem, 7vh, 4.5rem); }
        .gk-pg-embed { margin-top: 1rem; }
    }

    {{-- The schedule's cover, across the top of the sheet and to its edges. Never its profile
         picture, which may be a logo: that is .gk-pg-avatar. --}}
    .gk-pg-cover { margin: -1.5rem -1.125rem 1.75rem; overflow: hidden; background: var(--gk-stage-well); }
    .gk-pg-cover img { display: block; width: 100%; height: clamp(8.5rem, 24vw, 15rem); object-fit: cover; }
    @media (min-width: 40rem) {
        .gk-pg-cover { margin: -2rem calc(-1 * clamp(1.5rem, 4vw, 3.5rem)) 2.25rem; border-radius: 1.75rem 1.75rem 0 0; }
    }

    {{-- The head: who the page is about, small, then the title. --}}
    .gk-pg-head { display: flex; flex-direction: column; align-items: flex-start; gap: 1rem; }
    .gk-pg-who { display: inline-flex; align-items: center; gap: .75rem; max-width: 100%; color: var(--gk-ink); font-size: 1rem; font-weight: 600; line-height: 1.3; text-decoration: none; }
    .gk-pg-who-name { min-width: 0; overflow-wrap: anywhere; }
    a.gk-pg-who:hover .gk-pg-who-name { text-decoration: underline; text-underline-offset: 3px; }
    a.gk-pg-who:focus-visible { outline: 2px solid var(--gk-ink); outline-offset: 3px; border-radius: .5rem; }
    .gk-pg-avatar { flex: none; width: 3rem; height: 3rem; border-radius: .875rem; object-fit: cover; background: var(--gk-stage-raised); }
    .gk-pg .gk-title { font-size: clamp(2.125rem, 1.5rem + 2.2vw, 3.5rem); }
    .gk-pg-sm .gk-title { font-size: clamp(1.875rem, 1.45rem + 1.6vw, 2.625rem); }
    .gk-pg-lead { margin: 0; max-width: 62ch; font-size: 1.0625rem; line-height: 1.6; color: var(--gk-ink-2); text-wrap: pretty; }
    .gk-pg-fine { margin: 0; font-size: .875rem; line-height: 1.5; color: var(--gk-ink-3); }

    {{-- A field. On the sheet it is raised; inside a card it is the sheet's colour, so it reads as
         a well either way. layouts/app sets the type size and padding of every text field. --}}
    .gk-pg-label { display: block; margin-bottom: .375rem; font-size: .875rem; font-weight: 500; color: var(--gk-ink-2); }
    .gk-pg-input { display: block; box-sizing: border-box; width: 100%; min-height: 3.125rem; border: 1px solid var(--gk-line); border-radius: .75rem; background: var(--gk-stage-raised); color: var(--gk-ink); box-shadow: none; }
    .gk-card .gk-pg-input { background: var(--gk-stage); }
    .gk-pg-input::placeholder { color: var(--gk-ink-3); }
    .gk-pg-input:focus { outline: none; border-color: var(--es-accent-readable); box-shadow: 0 0 0 3px color-mix(in srgb, var(--es-accent-readable) 24%, transparent); }

    {{-- The buttons at the end of a form: the way forward last, and full width on a phone. --}}
    .gk-pg-actions { display: flex; flex-wrap: wrap; align-items: center; justify-content: flex-end; gap: .75rem 1.25rem; }
    {{-- Its own width here, never the stage's flex basis: in a column that basis is a height. --}}
    .gk-pg .gk-cta-main { flex: none; }
    .gk-pg-actions .gk-cta-main { max-width: none; }
    .gk-pg-actions-start { justify-content: flex-start; }
    .gk-pg-quiet { display: inline-flex; align-items: center; min-height: 2.75rem; padding: 0 .25rem; border: 0; background: none; color: var(--gk-ink-2); font-size: 1rem; font-weight: 550; text-decoration: none; cursor: pointer; }
    .gk-pg-quiet:hover { color: var(--gk-ink); text-decoration: underline; text-underline-offset: 3px; }
    .gk-pg-back { gap: .375rem; margin-inline-start: -.25rem; font-size: .9375rem; }
    .gk-pg-quiet:focus-visible { outline: 2px solid var(--gk-ink); outline-offset: 2px; border-radius: .375rem; }
    .gk-pg-cta-wide { width: 100%; max-width: none; }
    {{-- A destructive answer (taking a page down): the kit's red, as tall as the main one. Its
         label is the sheet's own colour, which reads on that red in both modes. --}}
    .gk-pg-danger { background: var(--gk-bad); color: var(--gk-stage); border-color: var(--gk-bad); }
    .gk-pg-danger:hover { filter: brightness(1.08); }

    {{-- A round mark over a short page's title: a lock, a tick. --}}
    .gk-pg-mark { display: inline-flex; align-items: center; justify-content: center; width: 3.5rem; height: 3.5rem; border-radius: 1.125rem; background: var(--gk-stage-well); color: var(--gk-ink-2); }
    .gk-pg-mark svg { width: 1.75rem; height: 1.75rem; }
    .gk-pg-mark-ok { background: var(--gk-ok-bg); color: var(--gk-ok); }

    {{-- On a phone a form card runs to the sheet's edges: the sheet already has its margins. --}}
    @media (max-width: 39.99rem) {
        .gk-pg-flush { margin-inline: -1.125rem; border-inline: 0; border-radius: 0; }
    }
</style>
