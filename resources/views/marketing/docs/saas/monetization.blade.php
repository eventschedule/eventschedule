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
        <x-doc-nav-link href="#environment">Environment variables</x-doc-nav-link>
    </x-slot:toc>

    <!-- Overview -->
    <section id="overview" class="doc-section">
        <h2 class="doc-heading">Overview</h2>
        <p>
            Monetization lets you earn from the schedules on your instance that are not paying you.
            It has two independent halves, and you can run either one on its own:
        </p>
        <ul>
            <li><strong>Google AdSense.</strong> Ad units on free schedules' public pages, using your own AdSense account.</li>
            <li><strong>The promotions network.</strong> Paid schedules buy placement for their events on free schedules' pages. You set the price and keep all of it.</li>
        </ul>
        <p>
            Paid schedules never carry either. Removing ads becomes a concrete reason to upgrade,
            alongside removing the Event Schedule branding.
        </p>

        <div class="doc-callout doc-callout-info">
            <p><strong>Off by default, and only for multi-tenant installs.</strong> Nothing is
            enabled until you set <code>ADS_ENABLED=true</code> and configure it in the admin panel.
            A single-tenant selfhost has no free tier, so it is never monetized. eventschedule.com
            itself does not run ads.</p>
        </div>
    </section>

    <!-- Consent and privacy -->
    <section id="consent" class="doc-section">
        <h2 class="doc-heading">Consent and privacy</h2>

        <div class="doc-callout doc-callout-warning">
            <p><strong>Read this before enabling AdSense.</strong> Visitors in the EEA, the UK and
            Switzerland must be asked for consent before they are shown personalized ads, and Google
            requires that this is done through a certified Consent Management Platform.
            <strong>Event Schedule does not ship a CMP and does not detect visitors' regions for
            consent purposes.</strong></p>
        </div>

        <p>
            Google provides a free CMP. Enable the GDPR message under <strong>Privacy &amp;
            messaging</strong> in your AdSense account; it needs no changes here, and the domain it
            loads from is already permitted by this application's content security policy.
        </p>
        <p>
            Event Schedule defaults to <strong>non-personalized ads</strong>, which is the safer
            setting and the one that requires the least of you. Turning personalized ads on is your
            decision and your legal responsibility. The app also always honours a visitor's
            <code>Sec-GPC</code> (Global Privacy Control) signal by forcing non-personalized ads for
            that request, whatever the setting says.
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
            fills the slot, the page contacts Google not at all.
        </p>
    </section>

    <!-- Turning it on -->
    <section id="enable" class="doc-section">
        <h2 class="doc-heading">Turning it on</h2>
        <ol>
            <li>Set <code>ADS_ENABLED=true</code> in your <code>.env</code> and deploy. This is a
                deliberate deploy-time gate: it cannot be switched on from the admin panel, so a
                misclick can never start serving ads across your whole instance.</li>
            <li>Sign in as an administrator and open <strong>Admin &rarr; Settings</strong>. A
                <strong>Monetization</strong> card appears once the gate above is on.</li>
            <li>Configure whichever half you want, and save.</li>
        </ol>
        <div class="doc-callout doc-callout-warning">
            <div class="doc-callout-title">Selling promotions needs Stripe</div>
            <p>
                On-network promotions are prepaid, so your instance needs
                <code>STRIPE_PLATFORM_KEY</code> and <code>STRIPE_PLATFORM_SECRET</code> set - the same
                platform keys that handle ticket sales. Without them the purchase form offers only
                promotion credit you have granted by hand, and nobody can pay by card. Google AdSense
                has no such requirement.
            </p>
        </div>
    </section>

    <!-- Google AdSense -->
    <section id="adsense" class="doc-section">
        <h2 class="doc-heading">Google AdSense</h2>
        <ol>
            <li>Create an AdSense account and add your instance's domain as a site.</li>
            <li>Create a <strong>display</strong> ad unit. Note its numeric slot ID.</li>
            <li>In <strong>Admin &rarr; Settings &rarr; Monetization</strong>, switch on
                <em>Show Google AdSense on free schedules</em> and enter your publisher ID
                (<code>ca-pub-…</code>) and the slot ID.</li>
        </ol>
        <p>
            Nothing loads until both IDs are present, so a half-finished setup is inert rather than
            broken. The ad label reads "Advertisement", which is one of the two wordings AdSense
            policy permits next to a unit.
        </p>
    </section>

    <!-- The promotions network -->
    <section id="promotions" class="doc-section">
        <h2 class="doc-heading">The promotions network</h2>
        <p>
            Switch on <em>Enable the promotions network</em> and set your prices. A paid schedule can
            then promote one of its public events from <strong>Boost &rarr; On this site</strong>,
            choosing either a price per 1,000 views or a price per click, and a budget.
        </p>
        <p>
            The advertiser pays up front. Their budget is drawn down as the promotion actually
            delivers, and anything unspent is refunded when the campaign ends. Because there is no
            outside ad network involved, the whole amount is yours.
        </p>
        <p>
            When both halves are on, a matching promotion is shown in preference to an ad, and
            AdSense fills the slot only when nothing matches. You can reverse that with
            <em>Prefer promotions over AdSense</em>.
        </p>

        <div class="doc-callout doc-callout-info">
            <p><strong>Free schedules can opt out.</strong> Any schedule can decline to host other
            schedules' promotions from its own settings, at no cost. Opting out only removes that
            schedule's own inventory.</p>
        </div>
    </section>

    <!-- Reviewing promotions -->
    <section id="review" class="doc-section">
        <h2 class="doc-heading">Reviewing promotions</h2>
        <p>
            Every promotion is reviewed before it runs. A paid schedule putting its event in front of
            every free schedule's audience is worth a look first, so new campaigns queue up under
            <strong>Admin &rarr; Boost</strong> and appear in the dashboard's "Needs attention" list.
        </p>
        <p>
            Approving one starts it immediately. Rejecting it refunds the advertiser in full and
            emails them the reason you give. Once a schedule has had a few promotions approved and
            none rejected, its later campaigns skip the queue automatically.
        </p>
    </section>

    <!-- Where ads appear -->
    <section id="where" class="doc-section">
        <h2 class="doc-heading">Where ads appear</h2>
        <p>Ads and promotions appear at the bottom of a free schedule's public schedule and event pages. They never appear:</p>
        <ul>
            <li>on any paid schedule's pages;</li>
            <li>on checkout, ticket, appointment booking, gift card or event submission pages;</li>
            <li>inside embedded calendars, or in generated share images;</li>
            <li>on password-protected pages;</li>
            <li>on a schedule's own custom domain;</li>
            <li>to the schedule's own members and administrators.</li>
        </ul>
        <p>
            That last one means a schedule owner cannot see their page the way a visitor does. Adding
            <code>?preview_ads=1</code> to the URL shows them the slot without counting an impression.
        </p>
    </section>

    <!-- Environment variables -->
    <section id="environment" class="doc-section">
        <h2 class="doc-heading">Environment variables</h2>
        <p>
            Only <code>ADS_ENABLED</code> has to be set here. Everything else is configured in the
            admin panel; these variables just supply the starting values.
        </p>

        <div class="doc-code-block">
            <div class="doc-code-header"><span>.env</span></div>
            <pre><code><span class="code-comment"># Master switch. Cannot be overridden from the admin panel.</span>
<span class="code-variable">ADS_ENABLED</span>=<span class="code-value">true</span>

<span class="code-comment"># Google AdSense</span>
<span class="code-variable">ADSENSE_PUBLISHER_ID</span>=<span class="code-string">ca-pub-XXXXXXXXXXXXXXXX</span>
<span class="code-variable">ADSENSE_EVENT_SLOT_ID</span>=<span class="code-string">XXXXXXXXXX</span>

<span class="code-comment"># On-network promotions</span>
<span class="code-variable">PROMOTIONS_ENGINE_ENABLED</span>=<span class="code-value">true</span>
<span class="code-variable">NATIVE_PROMO_PRIORITY_OVER_PROGRAMMATIC</span>=<span class="code-value">true</span>
<span class="code-variable">PROMOTIONS_NETWORK_CPM</span>=<span class="code-value">2.00</span>       <span class="code-comment"># price per 1,000 views</span>
<span class="code-variable">PROMOTIONS_NETWORK_CPC</span>=<span class="code-value">0.25</span>       <span class="code-comment"># price per click</span>
<span class="code-variable">PROMOTIONS_MIN_BUDGET</span>=<span class="code-value">5.00</span>
<span class="code-variable">PROMOTIONS_MAX_BUDGET</span>=<span class="code-value">1000.00</span>
<span class="code-variable">PROMOTIONS_FREQUENCY_CAP</span>=<span class="code-value">3</span>          <span class="code-comment"># views of one promotion per visitor per day</span>
<span class="code-variable">PROMOTIONS_AUTO_APPROVE_AFTER</span>=<span class="code-value">3</span>     <span class="code-comment"># approved campaigns before a schedule skips review</span></code></pre>
        </div>

        <div class="doc-callout doc-callout-info">
            <p><strong>Reporting.</strong> Advertisers see views, clicks, click-through rate, spend
            and tickets sold on their campaign page, along with which countries their viewers were in.
            They are told how many schedules carried the promotion, but never which ones: the
            schedules hosting it did not agree to have their traffic disclosed.</p>
        </div>
    </section>
</x-docs-page>
