<x-docs-page
    key="allocated-seating"
    title="Allocated Seating and Seat Maps Guide - Event Schedule"
    description="Sell reserved seats from a plan of your venue: build a plan of levels, sections, rows and tables, and let buyers choose their own seat."
    lede="Draw your room once, attach it to an event, and sell the seats in it. Buyers pick where they sit; your box office holds seats back, books by phone and moves people around."
    article-description="How to sell allocated (reserved) seating: build a seating plan, price each band, let buyers choose their seats, and manage the room from the box office console."
    plan="enterprise"
>
    <x-slot:toc>
        <x-doc-nav-link href="#overview">What allocated seating is</x-doc-nav-link>
        <x-doc-nav-link href="#build">Step 1 - Build the plan</x-doc-nav-link>
        <x-doc-nav-link href="#plans-tab" sub>The Seating plans tab</x-doc-nav-link>
        <x-doc-nav-link href="#build-designer" sub>The designer</x-doc-nav-link>
        <x-doc-nav-link href="#sell">Step 2 - Put it on sale</x-doc-nav-link>
        <x-doc-nav-link href="#one-date">Change a single date</x-doc-nav-link>
        <x-doc-nav-link href="#date-copy" sub>When a date stops following the plan</x-doc-nav-link>
        <x-doc-nav-link href="#buying">What the buyer sees</x-doc-nav-link>
        <x-doc-nav-link href="#rules">Rules that refuse a selection</x-doc-nav-link>
        <x-doc-nav-link href="#box-office">Run the door</x-doc-nav-link>
        <x-doc-nav-link href="#report">Seating plan report</x-doc-nav-link>
        <x-doc-nav-link href="#reference">Every option and limit</x-doc-nav-link>
        <x-doc-nav-link href="#limits">What it does not do</x-doc-nav-link>
        <x-doc-nav-link href="#see-also">See also</x-doc-nav-link>
    </x-slot:toc>

    <!-- Overview -->
    <section id="overview" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
            </svg>
            What allocated seating is
        </h2>

        <p class="text-gray-600 dark:text-gray-300 mb-6">Ordinary ticketing sells by the number: ten left, take three. Allocated seating sells the seats themselves. You draw your room once as a <strong class="text-gray-900 dark:text-white">seating plan</strong>, attach it to an event, and the buyer takes row C seat 14 off a map of your actual venue. Nobody can be sold a seat twice.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-6">It lives in three places, and this page follows them in order: you draw plans on the <strong class="text-gray-900 dark:text-white">Seating plans</strong> tab of a venue schedule, attach one to an event on the event's <strong class="text-gray-900 dark:text-white">Tickets</strong> tab, and run the night from the <strong class="text-gray-900 dark:text-white">Box office</strong> console. Allocated seating needs the Enterprise plan <x-doc-badge plan="enterprise" /> on eventschedule.com and is included in every selfhosted install.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-6">The most important thing to understand is what happens to <strong class="text-gray-900 dark:text-white">quantity</strong>. An ordinary ticket type has a number you set. An allocated one does not: its inventory is the seats in its part of the plan, so the plan decides how many exist. A band that runs out is sold out even while another band is wide open.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-6">A plan is a drawing of a <em>room</em>, not of one night. Attach it to a recurring event and every performance of the run sells from the same drawing, each date keeping its own bookings. A single date can be changed on its own when the piano takes out the front row.</p>

        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">Seated and standing in one room</div>
            <p>One plan can hold both. A <strong>seated</strong> section carries individual seats a buyer chooses. A <strong>standing</strong> section carries a capacity and sells by the number, exactly like an ordinary ticket type - it never appears in the seat map. Rows at the front and a standing floor at the back is one plan with two price bands.</p>
        </div>
    </section>

    <!-- Step 1 -->
    <section id="build" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
            </svg>
            Step 1 - Build the plan
        </h2>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Open your venue schedule in the admin panel, go to the <strong class="text-gray-900 dark:text-white">Seating plans</strong> tab and choose <strong class="text-gray-900 dark:text-white">New plan</strong>, at the end of the line under the tabs. A plan is a drawing of a room, so the tab appears on venue schedules only, and only for the schedule's owner and admins, not for viewers. Plans belong to the schedule, not to an event, so one plan serves every show you put in that room.</p>

        <x-doc-screenshot id="allocated-seating--plans" alt="The Seating plans tab of a venue schedule, with one plan card and the New plan button" loading="eager" />

        <h3 id="plans-tab" class="doc-subheading">The Seating plans tab</h3>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Each plan is a card. Under its name is a small drawing of the room, one dot per seat on the plan's first level, and then the figures that tell two plans apart: <strong class="text-gray-900 dark:text-white">Seats</strong>, <strong class="text-gray-900 dark:text-white">Standing</strong> capacity if the plan has any, and the date it was <strong class="text-gray-900 dark:text-white">Last updated</strong>. Once a plan is in use the card also counts the <strong class="text-gray-900 dark:text-white">Events</strong> attached to it and the seats <strong class="text-gray-900 dark:text-white">Sold</strong> across them. A plan that is still empty has no drawing yet.</p>

        <div class="doc-fields">
            <div class="doc-field">
                <h4>Open designer</h4>
                <p>Opens the plan for editing. The designer warns you at the top when the plan is in use and how many seats are already sold, because a sold seat cannot be removed.</p>
            </div>
            <div class="doc-field">
                <h4>Duplicate</h4>
                <p>Makes a full copy named "Copy of" followed by the plan's name and opens it in the designer. Use it for a second layout of the same room, such as the cabaret set-up of a hall you usually seat in rows.</p>
            </div>
            <div class="doc-field">
                <h4>Delete</h4>
                <p>Asks first, naming the plan and how many events use it. Events still using a deleted plan fall back to selling by quantity. Seats already sold keep their places.</p>
            </div>
        </div>

        <h3 id="build-designer" class="doc-subheading">The designer</h3>

        <p class="text-gray-600 dark:text-gray-300 mb-4">New plan takes you straight into the designer. Name the plan in the box at the top left, whenever you like - a new plan starts out called <strong class="text-gray-900 dark:text-white">Untitled plan</strong> and the name saves along with the layout.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-4">The room is on the left and everything that edits it is on the right. Switch levels and zoom from the strip above the map, pick a section to edit it, and click a seat to change its type - seat controls appear under the map, beside the seats themselves. <strong class="text-gray-900 dark:text-white">Undo</strong> (Ctrl+Z, or Cmd+Z on a Mac) covers every change you make here, and <strong class="text-gray-900 dark:text-white">Ctrl+S</strong> saves. Nothing is stored until you save: the toolbar reads <strong class="text-gray-900 dark:text-white">Unsaved changes</strong> until you do, and <strong class="text-gray-900 dark:text-white">Back</strong> returns to the Seating plans tab.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Start from one of four layouts and adjust it, or start from a blank canvas:</p>

        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Theatre with balcony</strong> - curved stalls plus a smaller upper level</li>
            <li><strong class="text-gray-900 dark:text-white">Cabaret tables</strong> - round tables of eight on one floor</li>
            <li><strong class="text-gray-900 dark:text-white">Straight rows</strong> - a simple grid of numbered rows</li>
            <li><strong class="text-gray-900 dark:text-white">Seated and standing</strong> - rows of seats in front of a standing area</li>
        </ul>

        <x-doc-screenshot id="allocated-seating--designer" alt="The seating plan designer" />

        <h3 id="build-structure" class="doc-subheading">Levels, sections and rows</h3>

        <p class="text-gray-600 dark:text-gray-300 mb-4">A plan is built from three things:</p>

        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Levels</strong> are floors - stalls, circle, balcony. A ground-floor room needs only one.</li>
            <li><strong class="text-gray-900 dark:text-white">Sections</strong> are blocks within a level, each with its own colour and price band.</li>
            <li><strong class="text-gray-900 dark:text-white">Rows and tables</strong> fill a section. For rows you give the number of rows, the seats in each, and how they are labelled.</li>
        </ul>

        <p class="text-gray-600 dark:text-gray-300 mb-6">Drag a section to move it. Click a seat to select it, or shift-click for several, then set what those seats are and whether a gangway follows them.</p>

        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">Generate rows adds, it does not replace</div>
            <p>Both <strong>Generate rows</strong> and <strong>Generate tables</strong> append to what the section already has, so you can build a block at a time - six rows of ten at the front, then four of twelve behind. Press either button twice by accident and you have double the seats - press <strong>Undo</strong> in the toolbar, or Ctrl+Z (Cmd+Z on a Mac).</p>
        </div>

        <h3 id="build-numbering" class="doc-subheading">How rows and seats are numbered</h3>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Select a seated section and open <strong class="text-gray-900 dark:text-white">Add rows</strong> in the panel on the right. <strong class="text-gray-900 dark:text-white">Row labels</strong> can be lettered <strong class="text-gray-900 dark:text-white">A, B, C</strong> or numbered <strong class="text-gray-900 dark:text-white">1, 2, 3</strong>. The rest sits under <strong class="text-gray-900 dark:text-white">Numbering</strong>, which is closed until you open it:</p>

        <ul class="doc-list mb-4">
            <li><strong class="text-gray-900 dark:text-white">Seat numbers</strong> run left to right (1, 2, 3), right to left (3, 2, 1), or <strong class="text-gray-900 dark:text-white">odds one side and evens the other</strong> (5, 3, 1 &middot; 2, 4, 6), which is how most older theatres are numbered from the centre aisle out.</li>
            <li><strong class="text-gray-900 dark:text-white">Skip letters</strong> leaves letters out of the row lettering. Nothing is skipped until you fill it in; most houses enter I and O, which are misread as 1 and 0 on a printed ticket.</li>
            <li><strong class="text-gray-900 dark:text-white">First seat number</strong>, <strong class="text-gray-900 dark:text-white">First row</strong> and <strong class="text-gray-900 dark:text-white">Row prefix</strong> let a block continue where the last one stopped.</li>
            <li><strong class="text-gray-900 dark:text-white">Will generate</strong> previews the first seats of the first row before anything is created.</li>
        </ul>

        <p class="text-gray-600 dark:text-gray-300 mb-6">Whatever you choose, a seat's label is what the buyer sees on their ticket and what the door staff read off the sheet, so it is worth matching the numbers already painted on the seats. You can rename any row or seat afterwards by selecting it and editing the label.</p>

        <h3 id="build-decorations" class="doc-subheading">The stage, and labels on the map</h3>

        <p class="text-gray-600 dark:text-gray-300 mb-4">A room full of seats and nothing else does not tell a buyer which way they will be facing. Add a <strong class="text-gray-900 dark:text-white">stage</strong> marker and put it where the stage actually is - buyers use it to orient the whole map, and it appears on the guest picker, the box office console and the printed sheet.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-6">Add <strong class="text-gray-900 dark:text-white">text</strong> labels for anything else worth naming: BAR, ENTRANCE, the door a latecomer should use. Both can be dragged, resized and rotated, so a stage along one side of the room reads the right way round. They are decoration only - nothing is sold from them and they never affect a seat.</p>

        <h3 id="build-accessible" class="doc-subheading">Wheelchair spaces and companion seats</h3>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Mark a seat as a <strong class="text-gray-900 dark:text-white">wheelchair space</strong> and the seat beside it as a <strong class="text-gray-900 dark:text-white">companion</strong> seat. A companion seat cannot be bought on its own while the wheelchair space next to it is still free - only together with it, or once it has gone. That is what stops a wheelchair user ending up three rows from the person they came with.</p>

        <div class="doc-callout doc-callout-warning mb-6">
            <div class="doc-callout-title">A wheelchair space needs an Accessibility only section</div>
            <p>Wheelchair spaces are only sellable from a section with <strong>Accessibility only</strong> switched on. That is deliberate - it keeps them from being handed out as the next available seat. But it also means a wheelchair space marked inside an ordinary section is bookable by <em>nobody at all</em>. If you mark wheelchair seats, put them in their own section and turn the switch on. The <a href="#build-issues" class="doc-link">issues panel</a> lists any section that has it wrong.</p>
        </div>

        <h3 id="build-issues" class="doc-subheading">The issues panel</h3>

        <p class="text-gray-600 dark:text-gray-300 mb-6">An amber panel at the foot of the right-hand column lists anything that will stop part of the plan selling: a section with no name, no seats, no price band, a standing section with no capacity, wheelchair spaces outside an Accessibility only section, or the same row and seat number used twice. Click a line and the designer takes you to that section; the count in the toolbar, such as <strong class="text-gray-900 dark:text-white">2 issues</strong>, jumps to the first. These are <strong class="text-gray-900 dark:text-white">advisory</strong> - you can save with them outstanding, and nothing breaks. A section with no band never becomes sellable.</p>
    </section>

    <!-- Step 2 -->
    <section id="sell" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
            </svg>
            Step 2 - Put it on sale
        </h2>

        <ol class="doc-list doc-list-numbered mb-6">
            <li>Open the event from the venue schedule that owns the plan and go to its <strong class="text-gray-900 dark:text-white">Tickets</strong> tab.</li>
            <li>Choose <strong class="text-gray-900 dark:text-white">Sell tickets</strong>. The <strong class="text-gray-900 dark:text-white">Seating plan</strong> field appears above the ticket types.</li>
            <li>Pick the plan. A small drawing of the room and its seat count appear under the field. <strong class="text-gray-900 dark:text-white">No seating plan (sell by quantity)</strong> is the ordinary, unseated choice.</li>
            <li>On each ticket type, choose the <strong class="text-gray-900 dark:text-white">Price band</strong> it sells. Stalls at 40 and Circle at 25 are two ticket types pointing at two bands of the same plan.</li>
            <li>Save the event.</li>
        </ol>

        <p class="text-gray-600 dark:text-gray-300 mb-4">If the schedule has no plan yet, the Tickets tab shows a short note in place of the field, with a <strong class="text-gray-900 dark:text-white">New plan</strong> link to the Seating plans tab.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-6">Once a ticket names a band, its <strong class="text-gray-900 dark:text-white">Quantity</strong> field goes read-only and reads "Set by the seating plan". The count comes from the seats in that band - or, for a standing band, from the section's capacity. A ticket type left on <strong class="text-gray-900 dark:text-white">Not seat allocated</strong> keeps an ordinary quantity.</p>

        <div class="doc-callout doc-callout-warning mb-6">
            <div class="doc-callout-title">Renaming a band strands the ticket that used it</div>
            <p>A ticket type remembers its band by <em>name</em>. Rename a section's band in the plan and the ticket no longer matches anything, so it stops selling seats - and its stored quantity is left as it was rather than dropping to zero, which makes it look like stock still exists. If you rename a band, open each event using that plan and re-pick the band on its ticket types.</p>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-6">A section whose band matches no ticket type is not sellable, and buyers never see those seats. That is the usual reason a freshly attached plan shows fewer seats than expected. The Tickets tab says so while you set the prices: an amber notice under the plan names the bands no ticket prices and how many seats that leaves unsold.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-6">Once the event has been saved with a plan, two buttons appear under the Seating plan field: <strong class="text-gray-900 dark:text-white">Box office</strong>, which opens the <a href="#box-office" class="doc-link">console</a>, and <strong class="text-gray-900 dark:text-white">Modify this date only</strong>, covered next.</p>
    </section>

    <!-- One date -->
    <section id="one-date" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
            </svg>
            Change a single date
        </h2>

        <p class="text-gray-600 dark:text-gray-300 mb-4"><strong class="text-gray-900 dark:text-white">Modify this date only</strong>, under the Seating plan field on a saved event's Tickets tab, opens the same designer pointed at one performance. On a recurring event it opens on today's date or the next one in the run. An amber banner names the date you are editing, and on a run its <strong class="text-gray-900 dark:text-white">Date</strong> picker moves you straight to another night. Changes there affect that date alone; the plan and every other date are untouched. <strong class="text-gray-900 dark:text-white">Back</strong> returns to the event.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-6"><strong class="text-gray-900 dark:text-white">Revert this date</strong> throws the date's own layout away and puts it back on the plan. It is refused while any seat is sold or a customer is mid-checkout.</p>

        <div class="doc-callout doc-callout-warning mb-6">
            <div class="doc-callout-title">Two screens that look identical</div>
            <p>The one-date designer and the plan designer are the same screen. When you are on one date the heading names the event and the night, and an amber banner says so as well; on the template the heading is the plan name and there is no banner. Reverting a date discards any seats your box office had <em>held back</em> for it, not just layout changes - the confirmation says so before you commit.</p>
        </div>

        <h3 id="date-copy" class="doc-subheading">When a date stops following the plan</h3>

        <p class="text-gray-600 dark:text-gray-300 mb-4">A date does not read the plan live. The first time anyone opens it - a buyer looking at the seat map, your box office, or the one-date designer - that date takes its own copy of the plan, and later changes to the plan no longer reach it. The banner in the one-date designer says when the copy was taken.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-6">So editing a plan changes the dates nobody has opened yet, and the events you attach it to afterwards. To change a date that already has its copy, make the change on that date with <strong class="text-gray-900 dark:text-white">Modify this date only</strong>, or use <strong class="text-gray-900 dark:text-white">Revert this date</strong> to make it take a fresh copy, which is only possible before a seat is sold.</p>
    </section>

    <!-- Buying -->
    <section id="buying" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
            </svg>
            What the buyer sees
        </h2>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Under <strong class="text-gray-900 dark:text-white">Choose your seats</strong>, a buyer enters <strong class="text-gray-900 dark:text-white">How many seats?</strong> and presses <strong class="text-gray-900 dark:text-white">Find best seats</strong> to get the best available together - the earliest section, then the earliest row, then as close to the centre of the row as it can. Most people never need the map. Those who want to choose click seats on the map instead, or use the <strong class="text-gray-900 dark:text-white">List</strong> view.</p>

        <x-doc-screenshot id="allocated-seating--picker" alt="A buyer choosing seats from the map" />

        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Seats are held for 12 minutes</strong> while they check out, with the time left on screen. An <strong class="text-gray-900 dark:text-white">I need more time</strong> link appears in the last two minutes.</li>
            <li><strong class="text-gray-900 dark:text-white">The map is live.</strong> Seats other people take grey out while a buyer is looking.</li>
            <li><strong class="text-gray-900 dark:text-white">There is a list view</strong> as well as the map, and it is a complete alternative - a purchase can be finished without the map at all.</li>
            <li><strong class="text-gray-900 dark:text-white">The map is keyboard operable.</strong> Tab into it once, then move with the arrow keys and select with Enter.</li>
        </ul>

        <p class="text-gray-600 dark:text-gray-300 mb-6">Seat numbers follow the booking everywhere afterwards: on the ticket, in the confirmation email, on a wallet pass, on the door scanner, in the check-in feed and in the sales export.</p>
    </section>

    <!-- Rules -->
    <section id="rules" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Rules that refuse a selection
        </h2>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Three rules can turn a buyer's selection down. All of them apply to buyers only - your box office is exempt from every one, because staff can see the whole room.</p>

        <h3 id="rules-orphan" class="doc-subheading">The single-seat rule</h3>

        <p class="text-gray-600 dark:text-gray-300 mb-4">A selection is refused if it would strand one seat on its own between two bookings, because a lone seat mid-row rarely sells. A gap that already existed is not blamed on the buyer, and a gangway counts as the end of a run.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-6">The rule <strong class="text-gray-900 dark:text-white">lifts automatically once the house is about 90% sold</strong>, when one more seat sold matters more than a tidy row. It is on by default, and the <strong class="text-gray-900 dark:text-white">Selling rules</strong> panel in the designer turns it off (<strong class="text-gray-900 dark:text-white">Do not leave a single seat on its own</strong>) or tunes it: <strong class="text-gray-900 dark:text-white">Smallest run to protect</strong>, from 1 to 4 seats, and <strong class="text-gray-900 dark:text-white">Stop applying at</strong>, the percentage sold at which it lifts. Set it on the plan and every date inherits it when it <a href="#date-copy" class="doc-link">takes its copy</a>; change it on one date and only that night differs. A room where single seats are normal, a bar or a comedy club, should turn it off.</p>

        <h3 id="rules-accessible" class="doc-subheading">Accessible seating</h3>

        <p class="text-gray-600 dark:text-gray-300 mb-6">A wheelchair space can only be bought from an <strong class="text-gray-900 dark:text-white">Accessibility only</strong> section. A companion seat cannot be taken alone while the wheelchair space beside it is free. See <a href="#build-accessible" class="doc-link">Wheelchair spaces and companion seats</a>.</p>

        <h3 id="rules-tables" class="doc-subheading">Whole tables</h3>

        <p class="text-gray-600 dark:text-gray-300 mb-6">A table set to <strong class="text-gray-900 dark:text-white">Whole table only</strong> is sold as a unit: clicking any chair selects the table, and a partial booking is refused. If part of such a table has already gone, the rest is not offered to anyone - which is what you want for a fundraising dinner, and why <strong class="text-gray-900 dark:text-white">Single seats or whole table</strong> is the better setting for cabaret nights.</p>
    </section>

    <!-- Box office -->
    <section id="box-office" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z" />
            </svg>
            Run the door
        </h2>

        <p class="text-gray-600 dark:text-gray-300 mb-4">The box office console is the same map with the names on. Open it with the <strong class="text-gray-900 dark:text-white">Box office</strong> button under the Seating plan field on the event's Tickets tab. On a run it opens on today's date or the next one, and the <strong class="text-gray-900 dark:text-white">Date</strong> picker at the top right moves between nights. The bar across the top shows how much of the house is sold, with counts for <strong class="text-gray-900 dark:text-white">Sold</strong>, <strong class="text-gray-900 dark:text-white">Held back</strong>, <strong class="text-gray-900 dark:text-white">In a basket</strong> and <strong class="text-gray-900 dark:text-white">Available</strong>, and carries the link to the <a href="#report" class="doc-link">Seating plan report</a>.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Click a seat to see who has it, turn on <strong class="text-gray-900 dark:text-white">Pick several</strong> to add more by tapping (a tablet has no shift key), or take a whole section from the sidebar - and <strong class="text-gray-900 dark:text-white">Select the whole row</strong> takes the row of the seat you have selected.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-4">The lookup box jumps to a seat typed as <code class="doc-inline-code">C14</code> or <code class="doc-inline-code">row C seat 14</code>, a range typed as <code class="doc-inline-code">C1-C12</code>, or a customer by name or email. It selects <strong class="text-gray-900 dark:text-white">every</strong> match, so searching a name finds the whole party rather than one seat of it, and the bulk actions then apply to all of them.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-4">A sold seat shows who holds it, what they bought, when they bought it, and whether they have come through the door yet (<strong class="text-gray-900 dark:text-white">Arrived</strong>) - with their email and phone as links, and a way through to the order in Sales. Releasing takes seats off a booking and can do a whole party at once. It moves no money and the order stays paid: the note below says how to give the money back. Every hold, release, exchange and counter booking is recorded in the schedule's audit log, with who did it.</p>

        <x-doc-screenshot id="allocated-seating--box-office" alt="The box office console" />

        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Hold back</strong> takes the selected seats off sale with a reason - house seats, production, accessibility or box office - and an internal note only your team sees. A staff hold never expires on its own; <strong class="text-gray-900 dark:text-white">Put back on sale</strong> lifts it.</li>
            <li><strong class="text-gray-900 dark:text-white">Book by phone</strong> sells the selected seats to a caller, marked paid or awaiting payment. Leave the amount blank for the list price, or enter zero to comp them.</li>
            <li><strong class="text-gray-900 dark:text-white">Move to another seat</strong> takes one booking to a different seat without touching the rest of the order.</li>
            <li><strong class="text-gray-900 dark:text-white">Release this seat</strong> puts one seat of a booking back on sale. With several seats selected the button reads <strong class="text-gray-900 dark:text-white">Release 3 seats</strong> (or however many) and frees them in one go.</li>
        </ul>

        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">Releasing a seat does not move any money</div>
            <p>Release frees the seat and takes it off the booking, and anyone on the waitlist is offered it. The order itself stays paid. To give the money back on a Stripe or PayPal order, open it on the <a href="{{ route('marketing.docs.tickets') }}#refunds" class="doc-link">Sales page</a>, choose <strong>Refund Ticket</strong> and enter the released seats' share as a partial refund. On any other payment method, return the money in your provider's own dashboard and leave the sale alone: <strong>Mark as Refunded</strong> marks the whole order refunded and puts every one of its seats back on sale.</p>
            <p class="mt-2">If the whole party is leaving, you do not need to release anything first. Refunding the whole order from the Sales page puts all of its seats back on sale by itself.</p>
        </div>
    </section>

    <!-- Report -->
    <section id="report" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            Seating plan report
        </h2>

        <p class="text-gray-600 dark:text-gray-300 mb-4">The report is the sheet front of house carries on the night: every seat, its status and who holds it, section by section, with a summary at the top. Open it from the <strong class="text-gray-900 dark:text-white">Seating plan report</strong> link at the top of the box office console; <strong class="text-gray-900 dark:text-white">Box office</strong> on the report takes you back. Status is drawn as a <strong class="text-gray-900 dark:text-white">shape as well as a colour</strong>, so it survives the black and white printer you actually have, and <strong class="text-gray-900 dark:text-white">Print</strong> sends the sheet to it without the page's own buttons.</p>

        <x-doc-screenshot id="allocated-seating--report" alt="The printable seating plan report" />

        <p class="text-gray-600 dark:text-gray-300 mb-4">Three views, because a full house and a door list are different sheets. <strong class="text-gray-900 dark:text-white">Taken seats</strong> lists only what is sold, held back or in a basket - on a half-sold room that is half the paper. <strong class="text-gray-900 dark:text-white">By name</strong> orders the same rows alphabetically by the booker's name as it was entered, which is how you look up somebody standing in front of you. <strong class="text-gray-900 dark:text-white">Every seat</strong> is the whole house. The map always draws the whole room whichever you pick.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Each sold seat carries a box you can tick, and it arrives already ticked for anybody the scanner has let in - so the sheet works whether or not you are scanning. The header shows <strong class="text-gray-900 dark:text-white">how full the house is</strong>, section by section, and on a run a <strong class="text-gray-900 dark:text-white">How the run is selling</strong> panel lists every night beside it, so you can see which dates are soft without opening them one at a time. Click a night there, or use the <strong class="text-gray-900 dark:text-white">Date</strong> picker at the top, to switch the sheet to it.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-6">The figures cover <strong class="text-gray-900 dark:text-white">one date</strong>; the run summary is the exception. <strong class="text-gray-900 dark:text-white">Download as CSV</strong> gives you the same rows for a spreadsheet, and the CSV additionally carries each booker's email, which the printed sheet leaves out.</p>
    </section>

    <!-- Reference -->
    <section id="reference" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
            </svg>
            Every option and limit
        </h2>

        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Setting</th>
                        <th>Choices, and what they mean</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Section kind</td>
                        <td><strong>Seating</strong> (individual seats buyers pick), <strong>Tables</strong>, or <strong>Standing</strong> (a capacity, sold by the number, never shown on the map).</td>
                    </tr>
                    <tr>
                        <td>Price band</td>
                        <td>The name matched to a ticket type. Sections sharing a band are priced together.</td>
                    </tr>
                    <tr>
                        <td>Accessibility only</td>
                        <td>Required before a wheelchair space in that section can be sold.</td>
                    </tr>
                    <tr>
                        <td>Seat kind</td>
                        <td><strong>Standard</strong>, <strong>Wheelchair space</strong>, <strong>Companion</strong>, <strong>Restricted view</strong>.</td>
                    </tr>
                    <tr>
                        <td>Row labels</td>
                        <td><code class="doc-inline-code">A, B, C</code> or <code class="doc-inline-code">1, 2, 3</code>. Letters continue past Z as AA, AB. <strong>Skip letters</strong> leaves out the ones you list; none are skipped by default.</td>
                    </tr>
                    <tr>
                        <td>Seat numbers</td>
                        <td>Left to right, right to left, or <strong>odds and evens</strong> from the centre out.</td>
                    </tr>
                    <tr>
                        <td>Rows and tables per block</td>
                        <td>One press of Generate rows adds up to 60 rows of up to 80 seats. One press of Generate tables adds up to 60 tables of up to 24 seats.</td>
                    </tr>
                    <tr>
                        <td>Decorations</td>
                        <td><strong>Stage</strong> marker and free <strong>text</strong> labels. Draggable, resizable and rotatable; never sold.</td>
                    </tr>
                    <tr>
                        <td>Curve</td>
                        <td>0-120. Lifts the outside of each row, for a room that faces a stage.</td>
                    </tr>
                    <tr>
                        <td>Aisle after seat</td>
                        <td>A comma list, e.g. <code class="doc-inline-code">6, 14</code>. Marks a gangway - seats either side are not neighbours, so best-available will not seat a couple across one.</td>
                    </tr>
                    <tr>
                        <td>Table shape</td>
                        <td><strong>Round</strong> or <strong>Rectangular</strong>.</td>
                    </tr>
                    <tr>
                        <td>Table booking</td>
                        <td><strong>Single seats only</strong>, <strong>Whole table only</strong>, or <strong>Single seats or whole table</strong>.</td>
                    </tr>
                    <tr>
                        <td>Number the seats at each table</td>
                        <td>Off means a chair at that table rather than a numbered seat.</td>
                    </tr>
                    <tr>
                        <td>Hold reason</td>
                        <td><strong>House seats</strong>, <strong>Production</strong>, <strong>Accessibility</strong>, <strong>Box office</strong>. Shown only to your team.</td>
                    </tr>
                    <tr>
                        <td>Plan size</td>
                        <td>Up to 12 levels, 200 sections, 500 tables, 6,000 seats, and 200 stages and labels.</td>
                    </tr>
                    <tr>
                        <td>Seat hold</td>
                        <td>12 minutes, extendable by the buyer in the last two.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Limits -->
    <section id="limits" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
            </svg>
            What it does not do
        </h2>

        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Decorations are not seats.</strong> A stage marker or text label is never sold, counted or reported on - it only tells the buyer which way the room faces.</li>
            <li><strong class="text-gray-900 dark:text-white">Invoice Ninja payment links</strong> cannot be used with allocated seating, because quantities are chosen after checkout there rather than before. The refusal happens at checkout, not when you attach the plan.</li>
            <li><strong class="text-gray-900 dark:text-white">Passes are house-wide.</strong> A pass holder booking an allocated date gets a real seat assigned automatically - best available, with no picker - but a pass cannot be restricted to one band.</li>
        </ul>

        <div class="doc-callout doc-callout-plan mb-6">
            <div class="doc-callout-title">If your plan lapses <x-doc-badge plan="enterprise" /></div>
            <p>Allocated seating is an Enterprise feature. An event that is already selling allocated seats keeps doing so if the schedule drops off Enterprise - deliberately, so nobody's sold seats vanish - but the designer and the box office console stop being reachable, and the Seating plans tab shows an upgrade notice in place of your plans. The plans themselves are kept. You would be selling seats you can no longer manage, so re-subscribe before the next performance.</p>
        </div>
    </section>

    <!-- See also -->
    <section id="see-also" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
            </svg>
            See also
        </h2>
        <ul class="doc-list mb-6">
            <li><a href="{{ route('marketing.docs.tickets') }}" class="doc-link">Selling Tickets</a> - ticket types, payment, check-in and the sales export</li>
            <li><a href="{{ route('marketing.docs.subscriptions') }}" class="doc-link">Subscriptions &amp; Passes</a> - how a pass books a seat in advance</li>
            <li><a href="{{ route('marketing.docs.creating_events') }}" class="doc-link">Creating Events</a> - setting up the event a plan attaches to</li>
        </ul>
    </section>
</x-docs-page>
