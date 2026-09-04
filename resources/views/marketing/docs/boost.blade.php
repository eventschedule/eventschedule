<x-docs-page
    key="boost"
    plan="pro"
    description="Learn how to promote your events with Boost: Facebook and Instagram ad campaigns, and promoted cards on other schedules on the same site."
    lede="Promote your events two ways from one page: Facebook and Instagram ads bought through Meta, and promoted cards shown on other schedules on the same site."
>
    <x-slot:toc>
        <x-doc-nav-link href="#overview">Overview</x-doc-nav-link>
        <x-doc-nav-link href="#on-network">On-Network Promotions</x-doc-nav-link>
        <x-doc-nav-link href="#quick-mode">Quick Mode</x-doc-nav-link>
        <x-doc-nav-link href="#advanced-mode">Advanced Mode</x-doc-nav-link>
        <x-doc-nav-link href="#smart-defaults">Smart Defaults</x-doc-nav-link>
        <x-doc-nav-link href="#managing-campaigns">Managing Campaigns</x-doc-nav-link>
        <x-doc-nav-link href="#spending-limits">Spending Limits</x-doc-nav-link>
        <x-doc-nav-link href="#analytics">Analytics</x-doc-nav-link>
        <x-doc-nav-link href="#billing">Billing &amp; Refunds</x-doc-nav-link>
        <x-doc-nav-link href="#tips">Tips</x-doc-nav-link>
        <x-doc-nav-link href="#see-also">See Also</x-doc-nav-link>
    </x-slot:toc>

    <!-- Overview -->
    <section id="overview" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Overview
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Boost turns an event you have already published into a paid promotion, without needing ad manager experience. It sells two different things from the same page, and each one is set up, priced and reported separately.
        </p>

        <x-doc-screenshot id="boost--page" alt="Boost event creation form" loading="eager" />

        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Channel</th>
                        <th>Where the promotion appears</th>
                        <th>What you pay</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Facebook &amp; Instagram</span></td>
                        <td>A real Meta ad campaign, created and managed for you on Facebook and Instagram</td>
                        <td>Your ad budget, plus a service fee on hosted sites</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">On this site</span></td>
                        <td>A promoted card on other schedules' public pages on the same Event Schedule site</td>
                        <td>Per 1,000 views or per click, with no service fee</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Both are bought the same way: open <strong class="text-gray-900 dark:text-white">Boost</strong> in the admin panel, press <strong class="text-gray-900 dark:text-white">Boost Event</strong>, pick one of your upcoming events, then choose a channel. A channel button only appears if the site you are on has that channel configured, so you may see one, both, or neither.
        </p>

        <p class="text-gray-600 dark:text-gray-300 mb-4">
            With Boost you can:
        </p>
        <ul class="doc-list mb-6">
            <li>Promote any published upcoming or in-progress event on a Pro schedule. Draft events cannot be boosted.</li>
            <li>Set your own budget, from the site minimum up to your current <a href="#spending-limits" class="doc-link">spending limit</a>.</li>
            <li>Run Meta ads on Facebook and Instagram, with the audience built automatically from the event.</li>
            <li>Refine the audience by age and interests, and rewrite the ad copy, in Advanced Mode.</li>
            <li>Track impressions, reach, clicks and conversions, refreshed every 15 minutes.</li>
            <li>Get unspent budget back automatically when the campaign ends.</li>
        </ul>

        <div class="doc-callout doc-callout-plan">
            <div class="doc-callout-title">Boost is a Pro feature</div>
            <p>
                <x-doc-badge plan="pro" link /> The schedule that pays for the campaign must be on a <a href="{{ marketing_url('/pricing') }}" class="doc-link">Pro plan</a> or higher. Selfhosted installations count as Enterprise, so the plan gate never blocks anything there.
            </p>
            <p class="mt-2">
                On hosted sites you also need a verified phone number on your account before you can buy. If yours is missing, Boost sends you to <a href="{{ route('marketing.docs.account_settings') }}#profile" class="doc-link">Account Settings</a> to add and verify it.
            </p>
        </div>

        <p class="text-gray-600 dark:text-gray-300">
            The Facebook and Instagram channel offers two modes: <strong class="text-gray-900 dark:text-white">Quick Mode</strong>, which fills everything in for you, and <strong class="text-gray-900 dark:text-white">Advanced Mode</strong>, which exposes the budget type, schedule, audience and creative. On-network promotions have a single form and no advanced mode.
        </p>
    </section>

    <!-- On-Network Promotions -->
    <section id="on-network" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 110-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 01-1.44-4.282m3.102.069a18.03 18.03 0 01-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 018.835 2.535M10.34 6.66a23.847 23.847 0 008.835-2.535m0 0A23.74 23.74 0 0018.795 3m.38 1.125a23.91 23.91 0 011.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 001.014-5.395m0-3.46c.495.413.811 1.035.811 1.73 0 .695-.316 1.317-.811 1.73m0-3.46a24.347 24.347 0 010 3.46" />
            </svg>
            On-Network Promotions
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Alongside Facebook and Instagram, some Event Schedule sites run their own promotions network. Your event appears as a promoted card on other schedules' public pages, in front of people already browsing events on the same site. The channel is called <strong class="text-gray-900 dark:text-white">On this site</strong> in the Boost Event dialog.
        </p>
        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Availability</div>
            <p>This is a per-site feature: it only appears if the operator of your Event Schedule site has switched it on. If you do not see an <strong>On this site</strong> button when you press Boost Event, the site is not running a promotions network.</p>
            <p class="mt-2">Promoted cards are only ever shown on <strong>free-plan</strong> schedules' public pages. Pro and Enterprise schedules never carry them, so the pool of pages your promotion can appear on is smaller than the site's total traffic.</p>
        </div>

        <h3 class="doc-subheading">What you can promote</h3>
        <ul class="doc-list mb-6">
            <li>The schedule buying the promotion must be on a <strong class="text-gray-900 dark:text-white">Pro plan</strong> or higher, and you must be a member of it.</li>
            <li>The event must be <strong class="text-gray-900 dark:text-white">publicly visible</strong>. Draft (internal) and unlisted events are rejected, because nobody clicking the card could open them.</li>
            <li>If another schedule added your schedule to their event, the invitation has to be accepted before you can promote it.</li>
            <li>You can run up to <strong class="text-gray-900 dark:text-white">2</strong> on-network campaigns at a time by default. This cap is separate from the Facebook and Instagram one, so neither channel uses up the other's slots.</li>
        </ul>

        <h3 class="doc-subheading">Writing the card</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The form asks for two pieces of copy and previews the result using the exact card component visitors will see, so what you approve is what ships:
        </p>
        <ul class="doc-list mb-6">
            <li><strong>Headline</strong> - up to 80 characters. Leave it empty and the event name is used.</li>
            <li><strong>Short description</strong> - up to 180 characters, shown under the headline.</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-6">
            The image is your event's flyer, and the card is labelled <strong class="text-gray-900 dark:text-white">Promoted</strong> with your schedule name underneath. There is nothing else to design.
        </p>

        <h3 class="doc-subheading">Choosing how you pay</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            You prepay a fixed budget and pick one of two pricing models. The rates are set by the site operator and are shown next to each option:
        </p>
        <ul class="doc-list mb-6">
            <li><strong>Per 1,000 views (CPM)</strong> - you pay for every thousand times your promotion is shown. Best when you want maximum visibility for an event with broad appeal.</li>
            <li><strong>Per click (CPC)</strong> - you pay only when someone clicks through to your event. Views are free, so this is the safer choice when you care about visits rather than awareness.</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The budget starts at the site minimum, $5 by default, and is capped by your current <a href="#spending-limits" class="doc-link">per-campaign spending limit</a>. The rate you buy at is stored on the campaign, so a later rate change never re-prices something you have already paid for.
        </p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Two lines under the budget field tell you different things, and both matter. The first is what your budget buys at the current rate. The second is how much traffic the site's eligible schedules have actually been getting recently, and for CPM it estimates how many days delivering your budget in full would take. A large budget cannot deliver more views than the site has visitors.
        </p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">
            Start and end dates are optional. With no end date the campaign runs until the budget is exhausted or the event is over.
        </p>

        <h3 class="doc-subheading">Targeting</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Targeting is optional, and leaving it empty reaches the widest audience:
        </p>
        <ul class="doc-list mb-6">
            <li><strong>Show to visitors in these countries</strong> - your promotion is only shown to visitors in the countries you tick. Useful for a local event on a site with international traffic.</li>
            <li><strong>Show on these kinds of schedules</strong> - restrict to talent, venue or curator schedules, so a gig promotion can run on venue pages rather than everywhere.</li>
        </ul>
        <div class="doc-callout doc-callout-warning">
            <div class="doc-callout-title">Narrow targeting can stall a campaign</div>
            <p>Every filter you add reduces the pages your promotion can appear on. If you target a country the site gets little traffic from, the campaign may spend slowly or not at all. Your budget is not lost: unspent money is refunded when the campaign ends.</p>
        </div>

        <h3 class="doc-subheading">Review</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Because your promotion appears on other people's schedules, your first campaigns are checked by the site operator before they go live. You will see <strong class="text-gray-900 dark:text-white">Awaiting review</strong> on the campaign until then, usually for about one business day, and you get an email either way. A rejected campaign is refunded in full, and the reason the operator gave is shown on the campaign page.
        </p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">
            Once you have a track record of approved campaigns that actually delivered, three by default, later ones start immediately. A single rejection puts your schedule back into the review queue for good.
        </p>

        <h3 class="doc-subheading">Results</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The campaign page reports impressions, clicks, click-through rate and spend, a delivery bar showing how much of the budget is left, effective cost per click and per thousand views, unique visitors, and any ticket sales attributed to the campaign along with their revenue. A daily chart appears once there is more than one day of data.
        </p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">
            It also breaks results down by country, with impressions and clicks for each, and by the kind of schedule your promotion ran on. Individual host schedules are never named: placement is reported by type and by count, not by page.
        </p>

        <h3 class="doc-subheading">When a campaign pauses itself</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">
            Two housekeeping rules can pause a live promotion. If the event stops being publicly visible, for example it is switched to draft or deleted, the campaign is paused rather than left looking live while it silently cannot serve. And a per-click campaign whose click-through rate stays extremely low after several thousand impressions is paused too, so weak creative does not consume host pages indefinitely without ever billing. In both cases the budget is still yours, and is settled and refunded if the campaign stays paused.
        </p>

        <h3 class="doc-subheading">Cancelling</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">
            You can cancel at any time, including while a campaign is awaiting review. You keep what has already been delivered and the unspent remainder of your budget is returned, to your card if you paid by card, or to your account balance if you paid from credit. The same applies when a campaign ends on its own, whether it ran out of budget, reached its end date, or its event finished.
        </p>

        <h3 class="doc-subheading">Hosting other schedules' promotions</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The network works both ways: if promotions can appear on other schedules' pages, they can appear on yours. If you would rather they did not, turn on <strong class="text-gray-900 dark:text-white">Do not show other schedules' promotions</strong> under <a href="{{ route('marketing.docs.creating_schedules') }}#settings-advanced" class="doc-link">Settings &rarr; Advanced</a>. It is free on every plan, including Free, and it does not stop you buying promotions of your own.
        </p>
        <p class="text-gray-600 dark:text-gray-300">
            The one toggle covers everything a site might place on your pages, so it also turns off <a href="{{ route('marketing.docs.managing_schedules') }}#plan" class="doc-link">ads</a> if the site you are on runs those as well. Paid schedules never carry either, so the toggle only changes anything while you are on the Free plan.
        </p>
    </section>

    <!-- Quick Mode -->
    <section id="quick-mode" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
            </svg>
            Quick Mode
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Quick Mode is the fastest way to launch a Facebook and Instagram campaign. It is what you get by default when you choose that channel.
        </p>

        <ul class="doc-list doc-list-numbered mb-6">
            <li><strong>Select an event</strong> - press <strong class="text-gray-900 dark:text-white">Boost Event</strong> on the Boost page and pick an upcoming event, or start from the event itself. Boost pulls in the name, date, venue and image automatically.</li>
            <li><strong>Read the warnings</strong> - if the event has no image, no description, no location, starts within 24 hours or is more than 90 days away, Boost says so before you spend anything.</li>
            <li><strong>Set your budget</strong> - drag the slider. It starts at the site minimum, $10 by default, and stops at whichever is lower, your current spending limit or $500. The cost breakdown and the amount on the button update as you drag.</li>
            <li><strong>Check the run dates</strong> - a line under the slider tells you how many days the ad will run and the date it ends. Boost picks that window from the event date; use Advanced Mode to change it.</li>
            <li><strong>Preview the ad</strong> - a mockup shows the headline, text, image and call to action exactly as Meta will render them.</li>
            <li><strong>Pay and launch</strong> - pay by card, or straight from your boost credit if the balance covers the whole total. The campaign is created on Meta in the background and appears on your Boost page.</li>
        </ul>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">Tip</div>
            <p>Quick Mode uses <a href="#smart-defaults" class="doc-link">smart defaults</a> based on your event's details. For most events this is all you need. If you want to change the audience or the wording, use the <strong>Customize targeting &amp; creative</strong> link at the bottom of the form to switch to Advanced Mode.</p>
        </div>
    </section>

    <!-- Advanced Mode -->
    <section id="advanced-mode" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75" />
            </svg>
            Advanced Mode
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Advanced Mode gives you control over the Facebook and Instagram campaign. Open it with the <strong class="text-gray-900 dark:text-white">Customize targeting &amp; creative</strong> link at the bottom of the Quick Mode form; <strong class="text-gray-900 dark:text-white">Use simple boost</strong> takes you back. It walks through four numbered steps: Budget &amp; Duration, Targeting, Creative, then Review &amp; Pay.
        </p>

        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Setting</th>
                        <th>Step</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Ad budget</span></td>
                        <td>1</td>
                        <td>Typed rather than dragged, so the full range up to your spending limit is available</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Budget type</span></td>
                        <td>1</td>
                        <td>Lifetime budget (total spend over the campaign) or daily budget (spend per day). Lifetime is the default</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Start and end date</span></td>
                        <td>1</td>
                        <td>Replace the dates Boost worked out from the event</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Objective</span></td>
                        <td>1</td>
                        <td>Awareness, Traffic or Engagement. Awareness is the default</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Age range</span></td>
                        <td>2</td>
                        <td>Narrow the audience within Meta's 18 to 65 range</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Interests</span></td>
                        <td>2</td>
                        <td>Search Meta's interest catalogue and add or remove categories. Boost pre-fills one based on the event category</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Placements</span></td>
                        <td>2</td>
                        <td>Facebook Feed and Instagram, either or both. Meta chooses the exact positions within each platform</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Translate ad copy to English</span></td>
                        <td>3</td>
                        <td>Offered when the schedule is not in English. Regenerates the three text fields in English; unticking restores what you had</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Headline</span></td>
                        <td>3</td>
                        <td>Up to 40 characters, replacing the generated headline</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Primary text</span></td>
                        <td>3</td>
                        <td>Up to 125 characters, the main body of the ad</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Description</span></td>
                        <td>3</td>
                        <td>Up to 30 characters, the link description shown below the headline</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Call to action</span></td>
                        <td>3</td>
                        <td>Learn More, Get Tickets, Sign Up or Book Now</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">The location is not editable</div>
            <p>Advanced Mode shows the geographic targeting Boost derived from the event, but it is read-only. To change where the ad runs, change the event's venue or its online URL and start a new boost.</p>
        </div>
    </section>

    <!-- Smart Defaults -->
    <section id="smart-defaults" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.517 0c.85.493 1.509 1.333 1.509 2.316V18" />
            </svg>
            Smart Defaults
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Boost classifies every event before it proposes anything. An event with a venue and no online URL is <strong class="text-gray-900 dark:text-white">in person</strong>; one with an online URL and no venue is <strong class="text-gray-900 dark:text-white">online</strong>; one with both is <strong class="text-gray-900 dark:text-white">hybrid</strong>. The classification decides the targeting and the wording:
        </p>

        <div class="doc-fields">
            <div class="doc-field">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-2">In-person events</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Targeting is a 25 mile radius around the venue's coordinates. If the venue has no coordinates, its country is used instead. The copy names the event, venue and city.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Online events</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Targeting is your schedule's country, or the United States, United Kingdom, Canada and Australia if the schedule is in English and has no country set. The copy leads on watching or streaming from anywhere, and names YouTube or Zoom when the link points there.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Hybrid events</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Targeting is the same venue radius as an in-person event, because that is where the room has to be filled. Only the copy changes: it offers both attending in person and watching online.</p>
            </div>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-4">
            On top of the location, Boost fills in:
        </p>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Setting</th>
                        <th>How Boost picks it</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Age range</span></td>
                        <td>18 to 65, the full range Meta allows</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Interests</span></td>
                        <td>One interest matched to the event's category, for example Music, Comedy, Theater or Nightlife</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Budget</span></td>
                        <td>Suggested from the cheapest ticket price where the event is ticketed, then capped by your spending limit</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Run dates</span></td>
                        <td>Starts now and ends with the event, clamped to between 3 and 14 days. Events with no date get 7 days</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Call to action</span></td>
                        <td>Get Tickets for a ticketed in-person event, Sign Up for a ticketed online event, otherwise Learn More</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Anything Boost cannot work out well becomes a warning at the top of the form rather than a silent guess. You will be told when the event has no location, no image or no description, when it starts within 24 hours or is more than 90 days away, and when an online event has no tickets so there is nothing to track as a conversion.
        </p>
        <p class="text-gray-600 dark:text-gray-300">
            Every default can be overridden in Advanced Mode, except the location, which comes from the event itself.
        </p>
    </section>

    <!-- Managing Campaigns -->
    <section id="managing-campaigns" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.429 9.75L2.25 12l4.179 2.25m0-4.5l5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L21.75 12l-4.179 2.25m0 0l4.179 2.25L12 21.75 2.25 16.5l4.179-2.25m11.142 0l-5.571 3-5.571-3" />
            </svg>
            Managing Campaigns
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Active and past campaigns from both channels are listed together on the <strong class="text-gray-900 dark:text-white">Boost</strong> page in your admin panel, newest first. If you run more than one schedule, use the schedule dropdown at the top to filter. Open a campaign to pause, resume or cancel it.
        </p>

        <h3 class="doc-subheading">Campaign Statuses</h3>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Pending Payment</span></td>
                        <td>The campaign was created but payment has not been confirmed yet. It resolves itself within about half an hour, either activating or failing</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Awaiting review</span></td>
                        <td>On-network only. Paid for and waiting on the site operator to approve it</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Active</span></td>
                        <td>The campaign is running and being delivered</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Paused</span></td>
                        <td>Delivery is temporarily stopped. Resume at any time. On-network campaigns can also be paused automatically, see above</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Completed</span></td>
                        <td>The campaign has finished: the budget was spent, the end date was reached, or the event is over</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Cancelled</span></td>
                        <td>You cancelled the campaign. Undelivered budget is refunded</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Rejected</span></td>
                        <td>Meta disapproved the ad, or the site operator rejected the on-network promotion. Either way a full refund is issued automatically and the reason is shown on the campaign page</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Failed</span></td>
                        <td>A technical or payment error prevented the campaign from running. Any payment that was taken is released or refunded</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">How many campaigns can run at once</div>
            <p>On hosted sites, the number of concurrent Facebook and Instagram campaigns per schedule depends on your history: new schedules start with 1, rising to 2 after 3 completed campaigns and 3 after 10. On-network promotions are capped separately, at 2 by default, so one channel never uses up the other's slots. Selfhosted installations use a single fixed limit instead.</p>
        </div>
    </section>

    <!-- Spending Limits -->
    <section id="spending-limits" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
            </svg>
            Spending Limits
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            To build trust on both sides, hosted sites cap how much you can put into a single campaign. The cap starts low and grows automatically as you complete campaigns:
        </p>

        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Completed Campaigns</th>
                        <th>Max Budget per Campaign</th>
                        <th>Max Concurrent</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">0 (new)</span></td>
                        <td>$10</td>
                        <td>1</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">1</span></td>
                        <td>$25</td>
                        <td>1</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">3</span></td>
                        <td>$50</td>
                        <td>2</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">5</span></td>
                        <td>$100</td>
                        <td>2</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">10</span></td>
                        <td>$250</td>
                        <td>3</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">20</span></td>
                        <td>$500</td>
                        <td>3</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">50+</span></td>
                        <td>$1,000</td>
                        <td>3</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The limit is per schedule, and your current one is shown at the top of every boost form. Only completed <strong class="text-gray-900 dark:text-white">Facebook and Instagram</strong> campaigns count towards it, and the increase lands when the campaign is settled, about a day after it finishes. On-network promotions are capped by the same ceiling but never raise it. The limit only ever goes up.
        </p>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">Tip</div>
            <p>Start with a small campaign to build your history. After just one completed campaign, your limit increases to $25.</p>
        </div>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Selfhosted installations</div>
            <p>There is no trust ladder when you run your own instance. The per-campaign maximum and the number of concurrent campaigns are fixed values in your configuration, and every schedule gets them from the start.</p>
        </div>
    </section>

    <!-- Analytics -->
    <section id="analytics" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
            </svg>
            Analytics
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            A Facebook and Instagram campaign reports the following. Figures come from Meta and are pulled in every 15 minutes; the campaign page shows when they were last updated.
        </p>

        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Metric</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Impressions</span></td>
                        <td>Total number of times your ad was shown</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Reach</span></td>
                        <td>Number of unique people who saw your ad</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Clicks</span></td>
                        <td>Number of clicks on your ad</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Conversions</span></td>
                        <td>Actions Meta attributed to the ad, which requires a Meta Pixel to be configured for the site</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">CTR</span></td>
                        <td>Click-through rate (clicks divided by impressions)</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">CPC</span></td>
                        <td>Cost per click</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">CPM</span></td>
                        <td>Cost per 1,000 impressions</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Budget utilization</span></td>
                        <td>How much of your budget has been delivered so far, as a bar and as an amount</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Once the campaign has run for more than a day, a daily performance chart plots impressions against clicks. On-network promotions report a different set of numbers, described under <a href="#on-network" class="doc-link">On-Network Promotions</a>.
        </p>
        <p class="text-gray-600 dark:text-gray-300">
            Every link in a boosted ad or promoted card is tagged, so traffic and ticket sales from a campaign are also visible in your own <a href="{{ route('marketing.docs.analytics') }}#revenue" class="doc-link">Analytics</a>, where boost views, attributed sales, cost per view and cost per sale are reported alongside the rest of your traffic.
        </p>
    </section>

    <!-- Billing & Refunds -->
    <section id="billing" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
            </svg>
            Billing &amp; Refunds
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Facebook and Instagram campaigns are billed as your ad budget plus a service fee, 20% on eventschedule.com. The fee covers running the campaign on your behalf; the rest is real ad spend on Meta. The full total is shown before you commit.
        </p>

        <h3 class="doc-subheading">How Pricing Works</h3>
        <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-5 border border-gray-200 dark:border-white/10 mb-6">
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Ad budget (you choose)</span>
                    <span class="font-semibold text-gray-900 dark:text-white">$75.00</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Service fee (20%)</span>
                    <span class="font-semibold text-gray-900 dark:text-white">$15.00</span>
                </div>
                <div class="border-t border-gray-200 dark:border-white/10 pt-3 flex justify-between">
                    <span class="font-bold text-gray-900 dark:text-white">Total charged</span>
                    <span class="font-bold text-gray-900 dark:text-white">$90.00</span>
                </div>
            </div>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-4">
            On-network promotions carry <strong class="text-gray-900 dark:text-white">no service fee</strong>: there is no outside ad platform to buy from, so your whole budget is the price. Selfhosted installations add no fee to either channel.
        </p>

        <h3 class="doc-subheading">How you pay</h3>
        <ul class="doc-list mb-6">
            <li><strong>Card</strong> - paid through Stripe on the boost form. A card already saved on the schedule is offered so you do not have to re-enter it.</li>
            <li><strong>Boost credit</strong> - if the schedule's credit balance covers the entire total, the campaign is paid from it and no card step appears. A partial balance is not used; the whole purchase goes on the card instead.</li>
        </ul>

        <h3 class="doc-subheading">Refund Policy</h3>
        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Rejected</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Whether Meta disapproved the ad or the site operator rejected an on-network promotion, the entire amount is refunded, ad budget and service fee alike.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Cancelled before any spend</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Full refund of the entire amount.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Cancelled part way through</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">You keep what was delivered. The undelivered budget, plus its share of the service fee, is refunded.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Completed with unspent budget</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Settled automatically about a day after the campaign ends, so late-arriving delivery is counted first. The remaining budget and the proportional service fee are refunded.</p>
            </div>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-6">
            Refunds go back the way you paid: to the card that was charged, or to the schedule's boost credit balance if the campaign was paid from credit.
        </p>

        <h3 class="doc-subheading">Email Notifications</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            You are emailed at each stage of a campaign, and get a matching web push notification if you have those switched on:
        </p>
        <ul class="doc-list mb-6">
            <li><strong>Campaign created</strong> - confirmation that the ad is live on Meta</li>
            <li><strong>Promotion reviewed</strong> - for on-network promotions, when the operator approves or rejects it</li>
            <li><strong>75% budget alert</strong> - a heads-up that most of your budget has been spent</li>
            <li><strong>Ad rejected</strong> - sent with the rejection reason and confirmation of the refund</li>
            <li><strong>Campaign completed</strong> - final stats summary with impressions, reach, clicks, and any refund details</li>
        </ul>
    </section>

    <!-- Tips -->
    <section id="tips" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.517 0c.85.493 1.509 1.333 1.509 2.316V18" />
            </svg>
            Tips
        </h2>
        <ul class="doc-list mb-6">
            <li><strong>Add an image to the event</strong> - the event image becomes the ad image and the promoted card's picture. Without one Boost warns you, and the ad has nothing to show.</li>
            <li><strong>Fill in the venue</strong> - the venue's coordinates are what produce a tight local audience. An event with neither a venue nor an online URL falls back to a very broad default.</li>
            <li><strong>Write your own headline for anything unusual</strong> - the generated copy is built from the event name, venue and city. If the appeal of your event is not in its name, say so yourself in Advanced Mode.</li>
            <li><strong>Boost 3 or more days before the event</strong> - campaigns run for between 3 and 14 days, and one that starts within 24 hours of the doors opening may not have time to deliver.</li>
            <li><strong>Start with a smaller budget</strong> - a new schedule is capped at $10 anyway, and completing that first campaign is what unlocks the next tier.</li>
            <li><strong>Pick the pricing model that matches your goal</strong> - for on-network promotions, per-click costs nothing until someone actually visits, while per-1,000-views buys reach whether or not anyone clicks.</li>
        </ul>
    </section>

    <!-- See Also -->
    <section id="see-also" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
            </svg>
            See Also
        </h2>
        <ul class="doc-list">
            <li><a href="{{ route('marketing.docs.analytics') }}#revenue" class="doc-link">Analytics</a> - see boost views, attributed sales and cost per sale alongside the rest of your traffic</li>
            <li><a href="{{ route('marketing.docs.tickets') }}" class="doc-link">Selling Tickets</a> - set up ticketing so a boosted event has something to convert</li>
            <li><a href="{{ route('marketing.docs.newsletters') }}" class="doc-link">Newsletters</a> - reach the audience you already have, for free</li>
            <li><a href="{{ route('marketing.docs.sharing') }}" class="doc-link">Sharing Your Schedule</a> - more ways to grow your audience</li>
            <li><a href="{{ route('marketing.docs.creating_schedules') }}#settings-advanced" class="doc-link">Schedule Settings</a> - decline other schedules' promotions on your own pages</li>
        </ul>
    </section>


    <x-slot:schema>
        <script type="application/ld+json" {!! nonce_attr() !!}>
        {
            "@context": "https://schema.org",
            "@type": "HowTo",
            "name": "How to Boost Events with Event Schedule",
            "description": "Promote your events with Facebook and Instagram ad campaigns, or promoted cards on other schedules, using Event Schedule's Boost feature.",
            "totalTime": "PT5M",
            "step": [
                {
                    "@type": "HowToStep",
                    "name": "Select an Event",
                    "text": "Open Boost, press Boost Event and choose an upcoming event, then pick a channel. Boost pulls in the name, date, venue and image automatically.",
                    "url": "{{ url(route('marketing.docs.boost')) }}#quick-mode"
                },
                {
                    "@type": "HowToStep",
                    "name": "Set Your Budget",
                    "text": "Drag the budget slider between the site minimum and your current spending limit. The cost breakdown updates as you drag.",
                    "url": "{{ url(route('marketing.docs.boost')) }}#quick-mode"
                },
                {
                    "@type": "HowToStep",
                    "name": "Launch Your Campaign",
                    "text": "Pay by card or from boost credit and the campaign goes live. Track impressions, reach, clicks and conversions from the campaign page.",
                    "url": "{{ url(route('marketing.docs.boost')) }}#analytics"
                }
            ]
        }
        </script>
    </x-slot:schema>
</x-docs-page>
