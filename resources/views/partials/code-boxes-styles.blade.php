{{-- The six-box code field: six drawn boxes and ONE real input lying transparent over them. Rules only,
     no style tag: included inside the <style> of each page that has the field (the sign-up
     page, and the guest "Submit your event" page's code step). The markup each page writes is
     #code-boxes > .code-slots > six [data-code-slot] (a .code-slots-gap after the third) + input.code-input. --}}
    /* The six code boxes. Surface and border follow the other inputs through the palette
       classes on each slot; states key off classes renderCodeSlots() sets. */
    .code-slots {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }
    .code-slots-gap {
        flex: 0 0 0.25rem;
    }
    .code-slot {
        position: relative;
        display: flex;
        flex: 1 1 0;
        align-items: center;
        justify-content: center;
        min-width: 0;
        height: 3.5rem;
        border-width: 1px;
        border-radius: 0.75rem;
        font-size: 1.5rem;
        font-weight: 600;
        font-variant-numeric: tabular-nums;
        color: rgb(var(--ap-ink));
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        transition: border-color 150ms, box-shadow 150ms, opacity 150ms;
    }
    .code-slot.is-filled {
        border-color: rgb(var(--ap-ink-4));
    }
    .code-slot.is-active {
        border-color: var(--brand-blue);
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--brand-blue) 25%, transparent);
    }
    .code-slot [data-caret] {
        display: none;
        position: absolute;
        width: 2px;
        height: 1.5rem;
        border-radius: 1px;
        background-color: var(--brand-blue);
        animation: code-caret 1s step-end infinite;
    }
    .code-slot.is-active:not(.is-filled) [data-caret] {
        display: block;
    }
    .code-slot.is-new [data-digit] {
        display: inline-block;
        animation: code-digit-in 120ms ease-out;
    }
    #code-boxes.is-invalid .code-slot {
        border-color: rgb(239 68 68);
    }
    #code-boxes.is-invalid .code-slot.is-active {
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.25);
    }
    #code-boxes.is-invalid .code-slots {
        animation: code-shake 240ms ease-in-out;
    }
    #code-boxes.is-valid .code-slot {
        border-color: rgb(22 163 74);
    }
    #code-boxes.is-valid .code-slot.is-active {
        box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.2);
    }
    #code-boxes.is-busy .code-slot {
        opacity: 0.6;
    }
    @keyframes code-caret {
        50% { opacity: 0; }
    }
    @keyframes code-digit-in {
        from { transform: scale(0.8); opacity: 0.4; }
        to { transform: none; opacity: 1; }
    }
    @keyframes code-shake {
        20%, 60% { transform: translateX(-4px); }
        40%, 80% { transform: translateX(4px); }
    }

    /* The real input lies over the boxes, invisible but present: transparent, never
       opacity:0 or hidden, which some browsers skip for autofill and focus. 16px, or iOS
       zooms the page on focus. The autofill rule stops Chrome painting its own background
       over the boxes when a code is filled in from Mail.

       44px wider than the boxes, with that strip clipped away: a password manager that
       ignores the data-*ignore attributes positions its badge from the input's right
       edge, so it lands in the clipped strip beyond the sixth box instead of on it. */
    #code-boxes .code-input {
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        width: calc(100% + 44px);
        clip-path: inset(0 44px 0 0);
        height: 100%;
        margin: 0;
        padding: 0;
        border: 0;
        outline: none;
        background: transparent;
        box-shadow: none;
        color: transparent;
        -webkit-text-fill-color: transparent;
        caret-color: transparent;
        font-size: 16px;
        cursor: text;
    }
    #code-boxes .code-input:focus {
        outline: none;
        box-shadow: none;
    }
    #code-boxes .code-input::selection {
        background: transparent;
    }
    #code-boxes .code-input:-webkit-autofill {
        -webkit-text-fill-color: transparent;
        transition: background-color 600000s 0s;
    }
    @media (prefers-reduced-motion: reduce) {
        .code-slot [data-caret] { animation: none; }
        .code-slot.is-new [data-digit],
        #code-boxes.is-invalid .code-slots { animation: none; }
    }
