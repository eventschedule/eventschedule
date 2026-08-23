<x-docs-page
    key="allocated-seating"
    description="Sell reserved seats from a plan of your venue: build a seating plan of levels, sections, rows and tables, let buyers choose their own seat, and run the door from a box office console."
    lede="Draw your room once, attach it to an event, and sell the seats in it. Buyers pick where they sit; your box office holds seats back, books by phone and moves people around."
    article-description="How to sell allocated (reserved) seating: build a seating plan, price each band, let buyers choose their seats, and manage the room from the box office console."
    plan="enterprise"
>
    <x-slot:toc>
        <x-doc-nav-link href="#overview">What allocated seating is</x-doc-nav-link>
        <x-doc-nav-link href="#build">Step 1 - Build the plan</x-doc-nav-link>
        <x-doc-nav-link href="#sell">Step 2 - Put it on sale</x-doc-nav-link>
        <x-doc-nav-link href="#one-date">Change a single date</x-doc-nav-link>
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
        <h2 class="doc-heading">What allocated seating is</h2>

        <p class="text-gray-600 dark:text-gray-300 mb-6">Ordinary ticketing sells by the number: ten left, take three. Allocated seating sells the seats themselves. You draw your room once as a <strong class="text-gray-900 dark:text-white">seating plan</strong>, attach it to an event, and the buyer takes row C seat 14 off a map of your actual venue. Nobody can be sold a seat twice.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-6">The most important thing to understand is what happens to <strong class="text-gray-900 dark:text-white">quantity</strong>. An ordinary ticket type has a number you set. An allocated one does not: its inventory is the seats in its part of the plan, so the plan decides how many exist. A band that runs out is sold out even while another band is wide open.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-6">A plan is a drawing of a <em>room</em>, not of one night. Attach it to a recurring event and every performance of the run sells from the same drawing, each date keeping its own bookings. A single date can be changed on its own when the piano takes out the front row.</p>

        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">Seated and standing in one room</div>
            <p>One plan can hold both. A <strong>seated</strong> section carries individual seats a buyer chooses. A <strong>standing</strong> section carries a capacity and sells by the number, exactly like an ordinary ticket type - it never appears in the seat map. Rows at the front and a standing floor at the back is one plan with two price bands.</p>
        </div>
    </section>

    <!-- Step 1 -->
    <section id="build" class="doc-section">
        <h2 class="doc-heading">Step 1 - Build the plan</h2>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Open <strong class="text-gray-900 dark:text-white">Seating</strong> on your schedule and choose <strong class="text-gray-900 dark:text-white">New plan</strong>. Plans belong to the schedule, not to an event, so one plan serves every show you put in that room.</p>

        <x-doc-screenshot id="allocated-seating--plans" alt="The Seating tab listing seating plans" loading="eager" />

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

        <div class="doc-callout doc-callout-warning mb-6">
            <div class="doc-callout-title">Generate rows adds, it does not replace</div>
            <p>Both <strong>Generate rows</strong> and <strong>Generate tables</strong> append to what the section already has, so you can build a block at a time - six rows of ten at the front, then four of twelve behind. Press either button twice by accident and you have double the seats. Delete the extras rather than pressing it again.</p>
        </div>

        <h3 id="build-accessible" class="doc-subheading">Wheelchair spaces and companion seats</h3>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Mark a seat as a <strong class="text-gray-900 dark:text-white">wheelchair space</strong> and the seat beside it as a <strong class="text-gray-900 dark:text-white">companion</strong> seat. A companion seat cannot be bought on its own while the wheelchair space next to it is still free - only together with it, or once it has gone. That is what stops a wheelchair user ending up three rows from the person they came with.</p>

        <div class="doc-callout doc-callout-warning mb-6">
            <div class="doc-callout-title">A wheelchair space needs an Accessibility only section</div>
            <p>Wheelchair spaces are only sellable from a section ticked <strong>Accessibility only</strong>. That is deliberate - it keeps them from being handed out as the next available seat. But it also means a wheelchair space marked inside an ordinary section is bookable by <em>nobody at all</em>. If you mark wheelchair seats, put them in their own section and tick the box.</p>
        </div>

        <h3 id="build-issues" class="doc-subheading">The issues panel</h3>

        <p class="text-gray-600 dark:text-gray-300 mb-6">An amber panel lists anything that will stop part of the plan selling: a section with no name, no seats, no price band, a standing section with no capacity, or the same row and seat number used twice. These are <strong class="text-gray-900 dark:text-white">advisory</strong> - you can save with them outstanding, and nothing breaks. A section with no band simply never becomes sellable.</p>
    </section>

    <!-- Step 2 -->
    <section id="sell" class="doc-section">
        <h2 class="doc-heading">Step 2 - Put it on sale</h2>

        <p class="text-gray-600 dark:text-gray-300 mb-4">On the event's <strong class="text-gray-900 dark:text-white">Tickets</strong> tab, choose a <strong class="text-gray-900 dark:text-white">Seating plan</strong>. Each ticket type then names the <strong class="text-gray-900 dark:text-white">price band</strong> it sells. Stalls at 40 and Circle at 25 are two ticket types pointing at two bands of the same plan.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-6">Once a ticket names a band, its <strong class="text-gray-900 dark:text-white">Quantity</strong> field goes read-only and reads "Set by the seating plan". The count comes from the seats in that band - or, for a standing band, from the section's capacity.</p>

        <div class="doc-callout doc-callout-warning mb-6">
            <div class="doc-callout-title">Renaming a band strands the ticket that used it</div>
            <p>A ticket type remembers its band by <em>name</em>. Rename a section's band in the plan and the ticket no longer matches anything, so it stops selling seats - and its stored quantity is left as it was rather than dropping to zero, which makes it look like stock still exists. If you rename a band, open each event using that plan and re-pick the band on its ticket types.</p>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-6">A section whose band matches no ticket type is not sellable, and buyers never see those seats. That is the usual reason a freshly attached plan shows fewer seats than expected.</p>
    </section>

    <!-- One date -->
    <section id="one-date" class="doc-section">
        <h2 class="doc-heading">Change a single date</h2>

        <p class="text-gray-600 dark:text-gray-300 mb-4">On a recurring event, <strong class="text-gray-900 dark:text-white">Modify this date only</strong> opens the same designer pointed at one performance. An amber banner names the date you are editing. Changes there affect that date alone; the plan and every other date are untouched.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-6"><strong class="text-gray-900 dark:text-white">Revert this date</strong> throws the date's own layout away and puts it back on the plan. It is refused while any seat is sold or a customer is mid-checkout.</p>

        <div class="doc-callout doc-callout-warning mb-6">
            <div class="doc-callout-title">Two screens that look identical</div>
            <p>The one-date designer and the plan designer are the same screen; only the amber banner tells them apart. Check for it before you start editing, or you will change every date of the run instead of one. Note also that reverting a date discards any seats your box office had <em>held back</em> for it, not just layout changes.</p>
        </div>
    </section>

    <!-- Buying -->
    <section id="buying" class="doc-section">
        <h2 class="doc-heading">What the buyer sees</h2>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Buyers choose how many seats they want and, by default, get the best available together - the earliest section, the earliest row, closest to the middle of what is free. Most people never open a map. Those who want to choose select <strong class="text-gray-900 dark:text-white">Choose your own seats</strong>.</p>

        <x-doc-screenshot id="allocated-seating--picker" alt="A buyer choosing seats from the map" />

        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Seats are held for 12 minutes</strong> while they check out, with the time left on screen. An <strong class="text-gray-900 dark:text-white">I need more time</strong> link appears in the last two minutes.</li>
            <li><strong class="text-gray-900 dark:text-white">The map is live.</strong> Seats other people take grey out while a buyer is looking.</li>
            <li><strong class="text-gray-900 dark:text-white">There is a list view</strong> as well as the map, and it is a complete alternative - a purchase can be finished without the map at all.</li>
            <li><strong class="text-gray-900 dark:text-white">The map is keyboard operable.</strong> Tab into it once, then move with the arrow keys and select with Enter.</li>
        </ul>

        <p class="text-gray-600 dark:text-gray-300 mb-6">Seat numbers follow the booking everywhere afterwards: on the ticket, in the confirmation email, on the door scanner, in the check-in feed and in the sales export.</p>
    </section>

    <!-- Rules -->
    <section id="rules" class="doc-section">
        <h2 class="doc-heading">Rules that refuse a selection</h2>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Three rules can turn a buyer's selection down. All of them apply to buyers only - your box office is exempt from every one, because staff can see the whole room.</p>

        <h3 id="rules-orphan" class="doc-subheading">The single-seat rule</h3>

        <p class="text-gray-600 dark:text-gray-300 mb-4">A selection is refused if it would strand one seat on its own between two bookings, because a lone seat mid-row rarely sells. A gap that already existed is not blamed on the buyer, and a gangway counts as the end of a run.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-6">The rule <strong class="text-gray-900 dark:text-white">lifts automatically once the house is about 90% sold</strong>, when one more seat sold matters more than a tidy row. It is on for every allocated event and there is currently no setting to turn it off or tune it.</p>

        <h3 id="rules-accessible" class="doc-subheading">Accessible seating</h3>

        <p class="text-gray-600 dark:text-gray-300 mb-6">A wheelchair space can only be bought from an <strong class="text-gray-900 dark:text-white">Accessibility only</strong> section. A companion seat cannot be taken alone while the wheelchair space beside it is free. See <a href="#build-accessible" class="doc-link">Wheelchair spaces and companion seats</a>.</p>

        <h3 id="rules-tables" class="doc-subheading">Whole tables</h3>

        <p class="text-gray-600 dark:text-gray-300 mb-6">A table set to <strong class="text-gray-900 dark:text-white">Whole table only</strong> is sold as a unit: clicking any chair selects the table, and a partial booking is refused. If part of such a table has already gone, the rest is not offered to anyone - which is what you want for a fundraising dinner, and why <strong class="text-gray-900 dark:text-white">Single seats or whole table</strong> is the better setting for cabaret nights.</p>
    </section>

    <!-- Box office -->
    <section id="box-office" class="doc-section">
        <h2 class="doc-heading">Run the door</h2>

        <p class="text-gray-600 dark:text-gray-300 mb-4">The box office console is the same map with the names on. Open it from the event. Click a seat to see who has it, shift-click for several, or take a whole section from the sidebar. The lookup box jumps to a seat typed as <code class="doc-inline-code">C14</code> or <code class="doc-inline-code">row C seat 14</code>, or to a customer by name or email.</p>

        <x-doc-screenshot id="allocated-seating--box-office" alt="The box office console" />

        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Hold seats back</strong> with a reason - house seats, production, accessibility or box office - and an internal note only your team sees. A staff hold never expires on its own.</li>
            <li><strong class="text-gray-900 dark:text-white">Book by phone</strong> sells the selected seats to a caller, marked paid or awaiting payment. Leave the amount blank for the list price, or enter zero to comp them.</li>
            <li><strong class="text-gray-900 dark:text-white">Move to another seat</strong> takes one booking to a different seat without touching the rest of the order.</li>
            <li><strong class="text-gray-900 dark:text-white">Release this seat</strong> puts one seat of a booking back on sale.</li>
        </ul>

        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">Releasing a seat does not move any money</div>
            <p>Release frees the seat and takes it off the booking, and anyone on the waitlist is offered it. The sale itself stays as it was - refund the customer in your payment provider as usual.</p>
        </div>
    </section>

    <!-- Report -->
    <section id="report" class="doc-section">
        <h2 class="doc-heading">Seating plan report</h2>

        <p class="text-gray-600 dark:text-gray-300 mb-4">The report is the sheet front of house carries on the night: every seat, its status and who holds it, section by section, with a summary at the top. Status is drawn as a <strong class="text-gray-900 dark:text-white">shape as well as a colour</strong>, so it survives the black and white printer you actually have.</p>

        <x-doc-screenshot id="allocated-seating--report" alt="The printable seating plan report" />

        <p class="text-gray-600 dark:text-gray-300 mb-6">It covers <strong class="text-gray-900 dark:text-white">one date</strong>, not the whole run. <strong class="text-gray-900 dark:text-white">Download as CSV</strong> gives you the same rows for a spreadsheet, and the CSV additionally carries each booker's email, which the printed sheet leaves out.</p>
    </section>

    <!-- Reference -->
    <section id="reference" class="doc-section">
        <h2 class="doc-heading">Every option and limit</h2>

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
                        <td><code class="doc-inline-code">A, B, C</code> or <code class="doc-inline-code">1, 2, 3</code>. Letters continue past Z as AA, AB.</td>
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
                        <td>Up to 12 levels, 200 sections, 500 tables and 6,000 seats.</td>
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
        <h2 class="doc-heading">What it does not do</h2>

        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Invoice Ninja payment links</strong> cannot be used with allocated seating, because quantities are chosen after checkout there rather than before. The refusal happens at checkout, not when you attach the plan.</li>
            <li><strong class="text-gray-900 dark:text-white">Passes are house-wide.</strong> A pass holder booking an allocated date gets a real seat assigned automatically - best available, with no picker - but a pass cannot be restricted to one band.</li>
            <li><strong class="text-gray-900 dark:text-white">The single-seat rule has no setting.</strong> It is on for every allocated event.</li>
            <li><strong class="text-gray-900 dark:text-white">The report is per date</strong>, not per run.</li>
        </ul>

        <div class="doc-callout doc-callout-warning mb-6">
            <div class="doc-callout-title">If your plan lapses</div>
            <p>Allocated seating is an Enterprise feature. An event that is already selling allocated seats keeps doing so if the schedule drops off Enterprise - deliberately, so nobody's sold seats vanish - but the designer and the box office console stop being reachable. You would be selling seats you can no longer manage, so re-subscribe before the next performance.</p>
        </div>
    </section>

    <!-- See also -->
    <section id="see-also" class="doc-section">
        <h2 class="doc-heading">See also</h2>
        <ul class="doc-list mb-6">
            <li><a href="{{ route('marketing.docs.tickets') }}" class="doc-link">Selling Tickets</a> - ticket types, payment, check-in and the sales export</li>
            <li><a href="{{ route('marketing.docs.subscriptions') }}" class="doc-link">Subscriptions &amp; Passes</a> - how a pass books a seat in advance</li>
            <li><a href="{{ route('marketing.docs.creating_events') }}" class="doc-link">Creating Events</a> - setting up the event a plan attaches to</li>
        </ul>
    </section>
</x-docs-page>
