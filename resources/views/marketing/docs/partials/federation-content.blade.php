{{-- Shared body for the Federation docs page.

     Federation is available to every non-nexus install, single-tenant selfhost and
     selfhosted SaaS alike, so the page is mirrored into both docs trees rather than
     living under one of them. The two wrappers differ only in breadcrumb and title;
     keeping the body here means they cannot drift apart. --}}

<!-- Overview -->
<section id="overview" class="doc-section">
    <h2 class="doc-heading">
        <svg aria-hidden="true" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418" />
        </svg>
        Overview
    </h2>
    <p>
        Federation is an optional network that shares your public events with the listings on
        <a href="https://eventschedule.com/browse" target="_blank" rel="noopener">eventschedule.com</a>.
        Every listing links straight back to the event on your own site, so the discovery traffic
        comes to you rather than staying somewhere else.
    </p>
    <p>
        It is available on any Event Schedule install other than eventschedule.com itself, whether
        you run a single schedule for yourself or a multi-tenant SaaS for customers. It is free,
        off by default, and can be switched off again at any time.
    </p>

    <div class="doc-callout doc-callout-info">
        <p><strong>What gets shared.</strong> Public, upcoming events, online and in person. Private,
        draft, cancelled and password-protected events are never sent, and neither are events from
        schedules that have opted out.</p>
    </div>
</section>

<!-- Turning it on -->
<section id="enable" class="doc-section">
    <h2 class="doc-heading">
        <svg aria-hidden="true" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 011.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.56.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.893.149c-.425.07-.765.383-.93.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 01-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.397.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 01-.12-1.45l.527-.737c.25-.35.273-.806.108-1.204-.165-.397-.505-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.107-1.204l-.527-.738a1.125 1.125 0 01.12-1.45l.773-.773a1.125 1.125 0 011.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
        Turning it on
    </h2>
    <ol>
        <li>Sign in as an administrator and open <strong>Admin &rarr; Settings</strong>.</li>
        <li>Switch on <strong>Share events with the network</strong>.</li>
        <li>Add a contact email. It is used only to tell you when your install has been reviewed.</li>
        <li>Save.</li>
    </ol>
    <p>
        Before you save, the page lists exactly which events would be shared on the next run, so
        you can see what leaves your install rather than having to trust a description of it.
    </p>
    <p>
        Saving registers your install with the network and puts it in a review queue. Nothing is
        published until an administrator at eventschedule.com approves it, which is a one-time step
        for the whole install. You will get an email either way, and the connection status on the
        settings page shows where you stand: <em>Not connected</em>, <em>Pending review</em>,
        <em>Approved</em> or <em>Suspended</em>.
    </p>
</section>

<!-- Per-schedule control -->
<section id="per-schedule" class="doc-section">
    <h2 class="doc-heading">
        <svg aria-hidden="true" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
        </svg>
        Per-schedule control
    </h2>
    <p>
        Once the network is on for the install, each schedule gets its own toggle under
        <strong>Settings</strong> on the schedule edit page: <strong>List this schedule on the
        network</strong>. It is on by default and can be switched off per schedule.
    </p>
    <p>
        The toggle only appears after you have enabled the network for the whole install, so on a
        multi-tenant deployment your customers never see an option you have not opted into.
    </p>
</section>

<!-- What appears -->
<section id="listings" class="doc-section">
    <h2 class="doc-heading">
        <svg aria-hidden="true" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
        </svg>
        What a listing looks like
    </h2>
    <p>
        Listings appear in their own section on the eventschedule.com browse page, badged with your
        site's address and filterable by country and language. Clicking one goes straight to the
        event on your site: there is no copy of the event page on eventschedule.com, and the link is
        a normal followable link rather than a tracking redirect.
    </p>
    <p>
        An event needs a picture to be listed, matching the bar applied to eventschedule.com's own
        events. That can be the event flyer or the profile image of a talent or venue schedule
        attached to it.
    </p>
    <p>
        Recurring events are listed with their next few dates, and times are shown in the event's own
        timezone rather than the visitor's.
    </p>
</section>

<!-- Keeping it in sync -->
<section id="sync" class="doc-section">
    <h2 class="doc-heading">
        <svg aria-hidden="true" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
        </svg>
        Keeping it in sync
    </h2>
    <p>
        Sharing runs hourly on your existing scheduler, so it needs no extra setup beyond the cron
        entry Event Schedule already requires. Edits appear within the hour, and an event that stops
        qualifying, because it was unpublished, cancelled, made private, or its schedule opted out,
        is removed from the listings on the next run.
    </p>
    <p>
        You can also run it by hand:
    </p>
    <div class="doc-code-block">
        <pre><code>php artisan federation:push</code></pre>
    </div>
    <p>
        The settings page records the last successful sync, and shows a warning if the most recent
        attempt did not complete. Failures retry automatically.
    </p>

    <div class="doc-callout doc-callout-warning">
        <p><strong>Schedules must be verified.</strong> Only schedules with a verified email address
        or phone number are shared, matching the rule eventschedule.com applies to its own listings.
        On a multi-tenant install this is the usual reason a particular customer's events do not
        appear. The settings page shows how many schedules are being held back for this reason.</p>
    </div>
</section>

<!-- Privacy -->
<section id="privacy" class="doc-section">
    <h2 class="doc-heading">
        <svg aria-hidden="true" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
        </svg>
        What is shared, and what is not
    </h2>
    <p>
        Only information that is already public on your event pages is sent: the event name, short
        description, dates, venue and city, the schedule's name, a picture, and the link back to your
        site. No attendee data, ticket data, contact details or private fields ever leave your install.
    </p>
    <p>
        Pictures are copied and stored by eventschedule.com rather than loaded from your server, so
        visitors browsing the listings never make requests to your site until they click through.
    </p>
    <p>
        Turning the setting off stops sharing immediately, and existing listings are removed on the
        next sync.
    </p>
</section>
