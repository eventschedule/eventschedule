<x-docs-page
    key="saas/monetization"
    description="Show Google AdSense on free schedules' public pages and let paid schedules buy promotional placement there. Off by default, and configured entirely by the instance operator."
    lede="Cover your hosting costs by monetizing the free tier, and give your paying customers somewhere to advertise."
>
    <x-slot:toc>
        <x-doc-nav-link href="#overview">Overview</x-doc-nav-link>
        <x-doc-nav-link href="#consent">Consent and privacy</x-doc-nav-link>
        <x-doc-nav-link href="#enable">Turning it on</x-doc-nav-link>
        <x-doc-nav-link href="#adsense">Google AdSense</x-doc-nav-link>
        <x-doc-nav-link href="#promotions">The promotions network</x-doc-nav-link>
        <x-doc-nav-link href="#review">Reviewing promotions</x-doc-nav-link>
        <x-doc-nav-link href="#where">Where ads appear</x-doc-nav-link>
        <x-doc-nav-link href="#accommodation">Accommodation affiliate</x-doc-nav-link>
        <x-doc-nav-link href="#environment">Environment variables</x-doc-nav-link>
    </x-slot:toc>

    <!-- Overview -->
    <section id="overview" class="doc-section">
        <h2 class="doc-heading">Overview</h2>
        <p>
            Monetization lets you earn from the schedules on your instance that are not paying you.
            It has two independent halves, and you can run either one on its own:
        </p>
        <ul class="doc-list">
            <li><strong>Google AdSense.</strong> Ad units on free schedules' public pages, using your own AdSense account.</li>
            <li><strong>The promotions network.</strong> Paid schedules buy placement for their events on free schedules' pages. You set the price and keep all of it.</li>
        </ul>
        <p>
            Paid schedules never carry either, so removing ads becomes a concrete reason for your
            customers to upgrade, alongside removing the footer strip that promotes your own
            marketing site to their visitors.
        </p>
        <p>
            A third earner, the <a href="#accommodation" class="doc-link">accommodation affiliate</a>,
            is documented on this page but is a separate feature with its own switch:
        </p>

        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Feature</th>
                        <th>Which schedules carry it</th>
                        <th>Who earns</th>
                        <th>Master switch</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Google AdSense</span></td>
                        <td>Free only</td>
                        <td>You, through your AdSense account</td>
                        <td><code class="doc-inline-code">ADS_ENABLED</code></td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Promotions network</span></td>
                        <td>Free only</td>
                        <td>You, in full, with no outside network</td>
                        <td><code class="doc-inline-code">ADS_ENABLED</code></td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Accommodation affiliate</span></td>
                        <td>Any plan, opt-in per schedule</td>
                        <td>The schedule owner, or you when they have no affiliate ID</td>
                        <td><code class="doc-inline-code">STAY22_ENABLED</code></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">One credit is not an upsell</div>
            <p>The small "Event Schedule" chip in the corner of public pages is the Attribution
            Assurance License credit, and on any install other than eventschedule.com it stays on
            every schedule you charge for. Do not sell its removal as a paid feature: there is no
            setting that takes it off, and upgrading a customer does not either. Worth knowing which
            way round it runs: a free schedule shows your footer strip and no chip, so an upgrade
            swaps your strip for our chip rather than clearing the page. Say so on your own pricing
            page before a customer finds it.</p>
        </div>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Off by default, and only for multi-tenant hosted installs</div>
            <p>Nothing is enabled until you set <code class="doc-inline-code">ADS_ENABLED=true</code>
            and configure it in the admin panel. The Monetization card is shown only when that
            variable is set <em>and</em> the install runs in hosted mode
            (<code class="doc-inline-code">IS_HOSTED=true</code>) and is not eventschedule.com
            itself. A single-tenant selfhost resolves every schedule to Enterprise, so it has no
            free tier and nothing could ever render.</p>
        </div>
    </section>

    <!-- Consent and privacy -->
    <section id="consent" class="doc-section">
        <h2 class="doc-heading">Consent and privacy</h2>

        <div class="doc-callout doc-callout-warning">
            <div class="doc-callout-title">Read this before enabling AdSense</div>
            <p>Visitors in the EEA, the UK and Switzerland must be asked for consent before they
            are shown personalized ads, and Google requires that this is done through a certified
            Consent Management Platform. <strong>Event Schedule does not ship a CMP and does not
            detect visitors' regions for consent purposes.</strong></p>
        </div>

        <p>
            Google provides a free CMP. Enable the GDPR message under <strong>Privacy &amp;
            messaging</strong> in your AdSense account; it needs no changes here. Once AdSense is
            fully configured, this application's content security policy automatically allows
            Google's ad and consent domains, so no header changes are needed either.
        </p>
        <p>
            Event Schedule defaults to <strong>non-personalized ads</strong>, which is the safer
            setting and the one that requires the least of you. The <strong>Allow personalized
            ads</strong> toggle in the Monetization card is off until you turn it on, and doing so
            is your decision and your legal responsibility. The app also always honours a visitor's
            <code class="doc-inline-code">Sec-GPC</code> (Global Privacy Control) header by forcing
            non-personalized ads for that request, whatever the setting says.
        </p>
        <p>
            Two more things are yours to handle: enabling AdSense means your visitors' browsers
            contact Google, so your privacy policy and cookie notice have to say so; and your
            AdSense account standing is between you and Google. Do not click your own ads, and do
            not ask anyone else to.
        </p>
        <p>
            The promotions network has none of these obligations. It is served entirely by your own
            install, sets no third-party cookies, and makes no external requests. When a promotion
            fills the slot, the page contacts Google not at all, because the AdSense script is
            loaded by the ad unit itself rather than from the page head.
        </p>
        <p>
            The <a href="#accommodation" class="doc-link">accommodation affiliate</a> is a third case.
            It does involve a third party that sets its own cookies, so the same disclosure obligation
            applies, but it is never loaded on page load. A visitor who has not accepted cookies sees
            an explanation and a button, and nothing reaches Stay22 until they click it.
        </p>
        <p>
            The consent banner that all of this hangs off appears as soon as any of these is on, so
            turning on <code class="doc-inline-code">ADS_ENABLED</code> brings it with you. Accepting
            also enables three first-party attribution cookies,
            <code class="doc-inline-code">utm_params</code>,
            <code class="doc-inline-code">utm_referrer_url</code> and
            <code class="doc-inline-code">utm_landing_page</code>, which remember for 30 days which
            campaign brought a visitor in so a later signup or sale can be credited to it. They are
            never written before a visitor accepts, and are cleared if consent is withdrawn. If you
            run no ads and no analytics but still want cross-visit attribution, set
            <code class="doc-inline-code">COOKIE_CONSENT_BANNER=true</code> to show the banner on its
            own. List all of these in your cookie notice.
        </p>
    </section>

    <!-- Turning it on -->
    <section id="enable" class="doc-section">
        <h2 class="doc-heading">Turning it on</h2>
        <ol class="doc-list doc-list-numbered">
            <li>Set <code class="doc-inline-code">ADS_ENABLED=true</code> in your
                <code class="doc-inline-code">.env</code> and deploy. This is a deliberate
                deploy-time gate: it cannot be switched on from the admin panel, so a misclick can
                never start serving ads across your whole instance.</li>
            <li>Sign in as an administrator and open <strong>Admin &rarr; Settings</strong>. A
                <strong>Monetization</strong> card appears once the gate above is on.</li>
            <li>Configure whichever half you want, and save. The card writes to the settings
                table, so these values take effect immediately with no redeploy.</li>
            <li>Confirm your cron entry is running. Promotions are settled, reconciled and
                refunded by a scheduled command that runs every fifteen minutes, so without
                <code class="doc-inline-code">* * * * * php artisan schedule:run</code> campaigns
                never complete and unspent budget is never returned.</li>
        </ol>
        <div class="doc-callout doc-callout-warning">
            <div class="doc-callout-title">Selling promotions needs Stripe</div>
            <p>
                On-network promotions are prepaid, so your instance needs
                <code class="doc-inline-code">STRIPE_PLATFORM_KEY</code> and
                <code class="doc-inline-code">STRIPE_PLATFORM_SECRET</code> set, the same
                platform keys that handle subscriptions and boosts. Without them the purchase form
                offers only promotion credit you have granted by hand, and nobody can pay by card.
                Google AdSense has no such requirement.
            </p>
        </div>
    </section>

    <!-- Google AdSense -->
    <section id="adsense" class="doc-section">
        <h2 class="doc-heading">Google AdSense</h2>
        <ol class="doc-list doc-list-numbered">
            <li>Create an AdSense account and add your instance's domain as a site.</li>
            <li>Create a <strong>display</strong> ad unit. Note its numeric slot ID.</li>
            <li>In <strong>Admin &rarr; Settings &rarr; Monetization</strong>, switch on
                <strong>Show Google AdSense on free schedules</strong> and fill in
                <strong>AdSense publisher ID</strong> (<code class="doc-inline-code">ca-pub-…</code>)
                and <strong>AdSense ad slot ID</strong>.</li>
        </ol>
        <p>
            All three are required. The toggle on its own does nothing, and neither does a
            publisher ID without a slot ID, so a half-finished setup is inert rather than broken:
            no script is loaded, and the content security policy is not widened.
        </p>
        <p>
            The label above the unit reads "Advertisement", which is one of the two wordings
            AdSense policy permits next to a unit. When AdSense has nothing to serve it marks the
            unit as unfilled and the whole block collapses, so an empty band is never left behind.
        </p>
    </section>

    <!-- The promotions network -->
    <section id="promotions" class="doc-section">
        <h2 class="doc-heading">The promotions network</h2>
        <p>
            Switch on <strong>Enable the promotions network</strong> and set
            <strong>Price per 1,000 impressions</strong> and <strong>Price per click</strong>.
            Both rates are copied onto each campaign at the moment it is bought, so changing them
            later never re-prices a campaign someone has already paid for.
        </p>

        <h3 class="doc-subheading">What an advertiser buys</h3>
        <p>
            A schedule on a paid plan promotes one of its public events from
            <strong>Boost &rarr; On this site</strong>. Four things have to be true before the form
            will open:
        </p>
        <ul class="doc-list">
            <li>the schedule is on <strong>Pro or Enterprise</strong>; free schedules host promotions but cannot buy them;</li>
            <li>the event is published and public, so neither a draft nor an unlisted event;</li>
            <li>the schedule has accepted its own place on the event, the same visibility rule the rest of the app uses;</li>
            <li>on a hosted install, the buyer has a verified phone number on their profile.</li>
        </ul>
        <p>
            They then choose a headline and short description, a pricing model (per 1,000 views or
            per click), and a budget between your configured minimum and maximum. Targeting is
            optional: a campaign can be limited to particular kinds of schedule (talent, venue or
            curator) and to visitors in particular countries. Leaving both untouched shows it
            everywhere.
        </p>
        <p>
            The advertiser pays up front. Their budget is drawn down as the promotion actually
            delivers, and anything unspent is refunded once the campaign ends. Because there is no
            outside ad network involved, the whole amount is yours.
        </p>

        <h3 class="doc-subheading">How a promotion is chosen</h3>
        <p>
            <strong>Prefer promotions over AdSense</strong> is on by default, and it is what makes
            a matching promotion win the slot with AdSense filling in only when nothing matches.
            Switch it off and the order reverses: AdSense takes the slot whenever it is configured,
            and promotions fill only when it is not.
        </p>
        <p>Some placements are refused no matter what the budget says:</p>
        <ul class="doc-list">
            <li>a schedule is never shown its own promotion, and neither is any other schedule belonging to the same owner;</li>
            <li>a promotion is never shown on the page for the very event it advertises;</li>
            <li>one visitor sees the same promotion at most a few times a day, set by <code class="doc-inline-code">PROMOTIONS_FREQUENCY_CAP</code>;</li>
            <li>a promotion stops the moment its event is hidden, cancelled or over, even if budget remains;</li>
            <li>a per-click campaign that nobody clicks is paused automatically once it has had enough impressions to judge, so weak creative cannot occupy your inventory for free.</li>
        </ul>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Any schedule can opt out</div>
            <p><strong>Do not show other schedules' promotions</strong>, under
            <strong>Settings &rarr; Advanced</strong> on the schedule, is free on every plan and
            appears as soon as either half of monetization is live. Despite the label it removes
            <strong>both</strong> paid promotions and AdSense from that schedule's public pages, so
            a schedule always has a way to decline. Opting out only removes that schedule's own
            inventory, and has no other effect on its plan.</p>
        </div>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">What advertisers can see</div>
            <p>Advertisers see views, clicks, click-through rate, spend and tickets sold on their
            campaign page, along with which countries their viewers were in. They are told how many
            schedules carried the promotion, but never which ones: the schedules hosting it did not
            agree to have their traffic disclosed.</p>
        </div>
    </section>

    <!-- Reviewing promotions -->
    <section id="review" class="doc-section">
        <h2 class="doc-heading">Reviewing promotions</h2>
        <p>
            Every promotion is reviewed before it runs. A paid schedule putting its event in front of
            every free schedule's audience is worth a look first, so new campaigns queue up under
            <strong>Admin &rarr; Boost</strong> and appear in the dashboard's "Needs attention" list.
            The advertiser has already been charged and is waiting, so the queue is worth clearing
            promptly.
        </p>
        <p>
            Approving one starts it immediately. Rejecting it refunds the advertiser in full and
            emails them the reason you give.
        </p>
        <p>
            A schedule stops going through the queue once it has a track record: it needs
            <code class="doc-inline-code">PROMOTIONS_AUTO_APPROVE_AFTER</code> campaigns that were
            approved, ran to completion and actually delivered impressions, and it must never have
            had a campaign rejected. A single rejection, ever, puts that schedule back in the queue
            permanently.
        </p>
    </section>

    <!-- Where ads appear -->
    <section id="where" class="doc-section">
        <h2 class="doc-heading">Where ads appear</h2>
        <p>
            Ads and promotions appear in one place: a single slot at the bottom of a free schedule's
            public schedule page and public event pages, above the footer. They never appear:
        </p>
        <ul class="doc-list">
            <li>on any paid schedule's pages;</li>
            <li>on an event page that is actively selling tickets, so an ad can never sit beside the organizer's own buy button;</li>
            <li>on checkout, ticket, appointment booking, gift card or event submission pages;</li>
            <li>inside embedded calendars, or in generated share images;</li>
            <li>on password-protected pages;</li>
            <li>on a schedule's own custom domain;</li>
            <li>to the schedule's own members and administrators;</li>
            <li>to bots and scripted requests, which are filtered out before anything is billed.</li>
        </ul>
        <p>
            The member exclusion means a schedule owner cannot see their page the way a visitor
            does. Signed in as a member or an administrator, adding
            <code class="doc-inline-code">?preview_ads=1</code> to the URL shows the slot without
            counting an impression or charging an advertiser. The parameter does nothing for anyone
            else.
        </p>
    </section>

    <!-- Accommodation affiliate -->
    <section id="accommodation" class="doc-section">
        <h2 class="doc-heading">Accommodation affiliate</h2>
        <p>
            A separate, independent way to earn from your instance: schedules can show a map of hotels
            and rentals near an event's venue, powered by <a href="https://www.stay22.com" target="_blank" rel="noopener" class="doc-link">Stay22</a>,
            and bookings made through it pay an affiliate commission.
        </p>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">This is not part of the monetization feature above</div>
            <p>It has its own switch, it is unaffected by
            <code class="doc-inline-code">ADS_ENABLED</code>, it works on a single-tenant selfhost
            and on eventschedule.com, and it applies to <strong>paid schedules as well as free
            ones</strong>. Most importantly, each schedule owner can supply their own affiliate ID
            and keep the commission themselves.</p>
        </div>

        <p>Set <code class="doc-inline-code">STAY22_ENABLED=true</code> and an
        <strong>Accommodation</strong> tab appears in every schedule's <strong>Engagement</strong>
        settings, holding a <strong>Show nearby accommodation</strong> toggle and a
        <strong>Stay22 affiliate ID</strong> field. Nothing is shown to visitors until a schedule
        owner turns it on there; it is off by default for every schedule, on every plan.</p>

        <h3 class="doc-subheading">Who gets the commission</h3>
        <p>
            A schedule that enters its own Stay22 affiliate ID keeps its own commission. A schedule that
            leaves the field blank falls back to the <strong>Fallback Stay22 affiliate ID</strong> you
            set in the <strong>Accommodation affiliate</strong> card at
            <strong>Admin &rarr; Settings</strong>, so the commission comes to you instead. The
            settings page states this plainly to the schedule owner, both on the toggle itself and as a
            warning once the map is live without their own ID.
        </p>
        <p>
            The fallback is never used on a schedule's own custom domain. A customer paying for a
            white-label domain should not have commission taken from it, so on those pages the map
            appears only when the owner keeps the commission.
        </p>

        <h3 class="doc-subheading">Consent, and when the map loads</h3>
        <p>
            Stay22 sets its own third-party cookies to attribute bookings, so the map is never loaded
            when the page opens. Visitors who have already accepted cookies get it immediately.
            Everyone else sees a short explanation and a button, and no request reaches Stay22 until
            they click. Visitors sending a
            <a href="https://globalprivacycontrol.org/" target="_blank" rel="noopener" class="doc-link">Global Privacy Control</a>
            signal are told why the map is not loading and are given no button at all, and
            withdrawing consent removes it from the page. Consent given by clicking the button lasts
            for that page view only and is never stored.
        </p>
        <p>
            <strong>You still have to disclose it.</strong> Add Stay22 to the third-party processors
            listed in your privacy policy, and describe the map in your cookie notice. Enabling this
            makes your instance an affiliate publisher, which is a disclosure obligation of yours, not
            Stay22's. An affiliate disclosure is shown to visitors in both states, whether or not
            the map has loaded.
        </p>

        <h3 class="doc-subheading">Where it appears</h3>
        <p>The map is shown on a public event page only when all of the following hold. Otherwise it is
        silently absent, which is the intended behaviour rather than an error:</p>
        <ul class="doc-list">
            <li>the operator set <code class="doc-inline-code">STAY22_ENABLED=true</code>, and either the schedule's own affiliate ID or your fallback one resolves;</li>
            <li>the schedule owner enabled it, and the event has a venue whose address has been validated, so it has coordinates;</li>
            <li>the event still has a night left to book, so a past event drops out on its own;</li>
            <li>the page is not an embed, a generated share image, or password-protected;</li>
            <li>the schedule is not the demo schedule.</li>
        </ul>
        <p>
            Check-in and check-out are derived from the occurrence the visitor is looking at, counting
            the nights the event actually spans and never fewer than one, capped by
            <code class="doc-inline-code">STAY22_MAX_NIGHTS</code>. Prices are requested in the same
            currency as the event's tickets, and a visitor arriving partway through a multi-day event
            is offered the remaining nights rather than nothing.
        </p>
    </section>

    <!-- Environment variables -->
    <section id="environment" class="doc-section">
        <h2 class="doc-heading">Environment variables</h2>
        <p>
            The two master switches, <code class="doc-inline-code">ADS_ENABLED</code> and
            <code class="doc-inline-code">STAY22_ENABLED</code>, can only be set here. Some of the
            rest are only starting values that the admin panel overrides; the others have no
            control in the admin panel at all and need a redeploy to change.
        </p>

        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Setting</th>
                        <th>Where you change it</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Master switches</td>
                        <td><code class="doc-inline-code">.env</code> only, by design</td>
                    </tr>
                    <tr>
                        <td>AdSense toggle, publisher ID, slot ID, personalized ads</td>
                        <td>Admin &rarr; Settings &rarr; Monetization, which wins over the variable</td>
                    </tr>
                    <tr>
                        <td>Promotions network toggle, priority, CPM and CPC rates</td>
                        <td>Admin &rarr; Settings &rarr; Monetization, which wins over the variable</td>
                    </tr>
                    <tr>
                        <td>Fallback Stay22 affiliate ID</td>
                        <td>Admin &rarr; Settings &rarr; Accommodation affiliate, which wins over the variable</td>
                    </tr>
                    <tr>
                        <td>Budgets, caps, currency, auto-approval threshold</td>
                        <td><code class="doc-inline-code">.env</code> only; there is no admin control</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="doc-code-block">
            <div class="doc-code-header"><span>.env</span></div>
            <pre><code><span class="code-comment"># Master switch. Cannot be overridden from the admin panel.</span>
<span class="code-variable">ADS_ENABLED</span>=<span class="code-value">true</span>

<span class="code-comment"># Google AdSense</span>
<span class="code-variable">ADSENSE_ENABLED</span>=<span class="code-value">true</span>
<span class="code-variable">ADSENSE_PUBLISHER_ID</span>=<span class="code-string">ca-pub-XXXXXXXXXXXXXXXX</span>
<span class="code-variable">ADSENSE_EVENT_SLOT_ID</span>=<span class="code-string">XXXXXXXXXX</span>
<span class="code-variable">ADSENSE_PERSONALIZED</span>=<span class="code-value">false</span>    <span class="code-comment"># leave off unless you run a certified CMP</span>

<span class="code-comment"># On-network promotions</span>
<span class="code-variable">PROMOTIONS_ENGINE_ENABLED</span>=<span class="code-value">true</span>
<span class="code-variable">NATIVE_PROMO_PRIORITY_OVER_PROGRAMMATIC</span>=<span class="code-value">true</span>
<span class="code-variable">PROMOTIONS_NETWORK_CPM</span>=<span class="code-value">2.00</span>       <span class="code-comment"># price per 1,000 views</span>
<span class="code-variable">PROMOTIONS_NETWORK_CPC</span>=<span class="code-value">0.25</span>       <span class="code-comment"># price per click</span>
<span class="code-variable">PROMOTIONS_CURRENCY</span>=<span class="code-string">USD</span>
<span class="code-variable">PROMOTIONS_MIN_BUDGET</span>=<span class="code-value">5.00</span>
<span class="code-variable">PROMOTIONS_MAX_BUDGET</span>=<span class="code-value">1000.00</span>
<span class="code-variable">PROMOTIONS_MAX_CONCURRENT</span>=<span class="code-value">2</span>         <span class="code-comment"># live campaigns per schedule</span>
<span class="code-variable">PROMOTIONS_FREQUENCY_CAP</span>=<span class="code-value">3</span>          <span class="code-comment"># views of one promotion per visitor per day</span>
<span class="code-variable">PROMOTIONS_AUTO_APPROVE_AFTER</span>=<span class="code-value">3</span>     <span class="code-comment"># clean campaigns before a schedule skips review</span>

<span class="code-comment"># Accommodation affiliate. Independent of ADS_ENABLED; also a master switch that</span>
<span class="code-comment"># cannot be overridden from the admin panel, because the Content-Security-Policy</span>
<span class="code-comment"># is built from it on every request. Run config:cache after changing it.</span>
<span class="code-variable">STAY22_ENABLED</span>=<span class="code-value">true</span>
<span class="code-variable">STAY22_AID</span>=<span class="code-string">your-stay22-affiliate-id</span>   <span class="code-comment"># fallback only; editable in the admin panel</span>
<span class="code-variable">STAY22_MAX_NIGHTS</span>=<span class="code-value">30</span>                <span class="code-comment"># upper bound on the derived stay length</span></code></pre>
        </div>
    </section>
</x-docs-page>
