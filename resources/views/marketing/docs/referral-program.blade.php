<x-docs-page
    key="referral-program"
    description="Learn how to earn free months of Event Schedule by referring other event organizers through the referral program."
    lede="Earn free months of Event Schedule by sharing your referral link with other event organizers."
>
    <x-slot:toc>
        <x-doc-nav-link href="#overview">Overview</x-doc-nav-link>
        <x-doc-nav-link href="#how-it-works">How It Works</x-doc-nav-link>
        <x-doc-nav-link href="#referral-link">Your Referral Link</x-doc-nav-link>
        <x-doc-nav-link href="#dashboard">Referral Dashboard</x-doc-nav-link>
        <x-doc-nav-link href="#rewards">Rewards</x-doc-nav-link>
        <x-doc-nav-link href="#applying-credits">Applying Credits</x-doc-nav-link>
        <x-doc-nav-link href="#statuses">Referral Statuses</x-doc-nav-link>
        <x-doc-nav-link href="#history">Referral History</x-doc-nav-link>
    </x-slot:toc>

    <!-- Overview -->
    <section id="overview" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
            </svg>
            Overview
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The referral program lets you earn credit towards Event Schedule by inviting other event organizers to the platform. When someone signs up through your referral link and then pays for a Pro or Enterprise plan, you earn a credit worth one month of the plan they are on. You choose which of your schedules the credit lands on.
        </p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Open it from <strong class="text-gray-900 dark:text-white">Referrals</strong> in the admin panel sidebar. The <strong class="text-gray-900 dark:text-white">Plan</strong> tab of any schedule also carries a <strong class="text-gray-900 dark:text-white">View Referral Dashboard</strong> link to the same page.
        </p>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Who can take part</div>
            <p>Every account on eventschedule.com can refer, including accounts on the Free plan, and there is no cap on how many people you refer. The program runs on eventschedule.com only: a <a href="{{ route('marketing.docs.selfhost') }}" class="doc-link">selfhosted</a> install has no subscriptions to refer anyone to, so the Referrals item does not appear in its sidebar.</p>
        </div>
    </section>

    <!-- How It Works -->
    <section id="how-it-works" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
            </svg>
            How It Works
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The Referrals page sums this up in three steps: share your link, they subscribe, you earn your credit. In full, a referral moves through five stages:
        </p>

        <ol class="doc-list doc-list-numbered mb-6">
            <li><strong class="text-gray-900 dark:text-white">You share your link.</strong> Copy it from the top of the Referrals page and send it to another organizer.</li>
            <li><strong class="text-gray-900 dark:text-white">They create an account.</strong> The referral is recorded the moment they sign up, with the status <strong class="text-gray-900 dark:text-white">Pending</strong>. Signing up with an email address and signing up with Google both count.</li>
            <li><strong class="text-gray-900 dark:text-white">They subscribe.</strong> When they start a paid Pro or Enterprise subscription on one of their schedules, the referral moves to <strong class="text-gray-900 dark:text-white">Subscribed</strong> and the 30-day clock starts.</li>
            <li><strong class="text-gray-900 dark:text-white">They stay 30 days.</strong> Thirty days after they subscribed, if the subscription is still active or in its cancellation grace period, the referral becomes <strong class="text-gray-900 dark:text-white">Qualified</strong> and the credit is yours. You get an email, and a push notification if you have those switched on.</li>
            <li><strong class="text-gray-900 dark:text-white">You apply the credit.</strong> Pick a schedule to spend it on and the referral is marked <strong class="text-gray-900 dark:text-white">Credited</strong>.</li>
        </ol>

        <h3 class="doc-subheading">What counts as a referral</h3>
        <ul class="doc-list mb-6">
            <li>The visitor has to reach Event Schedule through your link and sign up in the same browsing session. The code is held in their session, not in a long-lived cookie, so a visit today and a signup next week will not be linked.</li>
            <li>Each person can be referred once. If someone already has an Event Schedule account, or was already referred by another organizer, a new referral is not created for them.</li>
            <li>You cannot refer yourself. A signup that matches your own account is ignored.</li>
            <li>The 30-day clock starts when the subscription starts, and the 7-day free trial counts towards it.</li>
            <li>Statuses are recalculated once a day, so a referral that hits its 30th day shows as Qualified on the next daily run rather than to the minute.</li>
        </ul>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">One credit per person referred</div>
            <p>A referral produces at most one credit, no matter how many schedules the person you referred goes on to run or how long they stay. It also has to still be Pending when they subscribe: if it has already expired (see <a href="#statuses" class="doc-link">Referral Statuses</a>), a later subscription will not revive it.</p>
        </div>
    </section>

    <!-- Your Referral Link -->
    <section id="referral-link" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
            </svg>
            Your Referral Link
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The <strong class="text-gray-900 dark:text-white">Your Referral Link</strong> panel sits at the top of the Referrals page. Click <strong class="text-gray-900 dark:text-white">Copy Link</strong> and the button confirms with <strong class="text-gray-900 dark:text-white">Copied!</strong>, then paste the link into an email, a post or a message.
        </p>

        <x-doc-screenshot id="referral-link" alt="Referral link panel" loading="eager" />

        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The link is the Event Schedule home page with an eight-character code on the end, in the form <code class="doc-inline-code">/?ref=a1b2c3d4</code>. The code is created the first time you open the Referrals page and never changes after that.
        </p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            You are not limited to the home page. Adding <code class="doc-inline-code">?ref=yourcode</code> to any Event Schedule page works the same way, so you can point people straight at <a href="{{ marketing_url('/pricing') }}" class="doc-link">Pricing</a> or at a feature page and still get the credit.
        </p>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">Tip</div>
            <p>Share your referral link on social media, in event communities, or directly with organizers you know. Because attribution only lasts for the visit, links that lead somewhere worth reading straight away, such as the pricing page, convert better than a link someone bookmarks for later.</p>
        </div>
    </section>

    <!-- Referral Dashboard -->
    <section id="dashboard" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
            </svg>
            Referral Dashboard
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Four summary cards sit under the referral link, one per stage of the funnel:
        </p>

        <x-doc-screenshot id="referral-dashboard" alt="Referral dashboard statistics" />

        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Card</th>
                        <th>What it counts</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Total Referrals</span></td>
                        <td>Everyone who has signed up through your link, at any status</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Awaiting Subscription</span></td>
                        <td>Referrals still at Pending: the account exists but has not paid for a plan yet</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Awaiting Qualification</span></td>
                        <td>Referrals at Subscribed: they are paying and part way through the 30 days</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Credits Earned</span></td>
                        <td>Credits you have already applied to a schedule. Credits that are earned but not yet spent are not counted here, they are listed below in Credits Ready to Apply</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Rewards -->
    <section id="rewards" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
            </svg>
            Rewards
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            A credit is worth one month of the plan the person you referred is on, at the standard monthly price:
        </p>

        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Their plan when the referral qualifies</th>
                        <th>Credit you earn</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Pro</span></td>
                        <td>{{ plan_price($proMonthly) }}, one month of Pro</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Enterprise</span></td>
                        <td>{{ plan_price($entMonthly) }}, one month of Enterprise</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">The tier is read on day 30, not on day 1</div>
            <p>The credit follows whichever plan their subscription is on at the moment it qualifies. Someone who starts on Pro and moves up to Enterprise inside the first 30 days earns you the {{ plan_price($entMonthly) }} Enterprise credit, and the history table updates to match. The amounts are fixed at {{ plan_price($proMonthly) }} and {{ plan_price($entMonthly) }} whether they pay monthly or yearly.</p>
        </div>
    </section>

    <!-- Applying Credits -->
    <section id="applying-credits" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
            </svg>
            Applying Credits
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Once a referral qualifies, it appears in the green <strong class="text-gray-900 dark:text-white">Credits Ready to Apply</strong> panel on the Referrals page. The panel is only there while you have at least one unspent credit.
        </p>

        <x-doc-screenshot id="referral-credits" alt="Available referral credits" />

        <ol class="doc-list doc-list-numbered mb-6">
            <li>Open <strong class="text-gray-900 dark:text-white">Referrals</strong> in the admin panel sidebar</li>
            <li>Find the credit in <strong class="text-gray-900 dark:text-white">Credits Ready to Apply</strong>. Each one shows its tier and value, such as Pro {{ plan_price($proMonthly) }} credit</li>
            <li>Choose a schedule from the <strong class="text-gray-900 dark:text-white">Select schedule</strong> dropdown. Only schedules you own are listed, not ones where you were added as a team member</li>
            <li>Click <strong class="text-gray-900 dark:text-white">Apply Credit</strong></li>
        </ol>

        <h3 class="doc-subheading">What applying a credit does</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            What happens next depends on whether the schedule you picked is already paying:
        </p>

        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Schedule you apply it to</th>
                        <th>What the credit does</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Has an active subscription</span></td>
                        <td>The {{ plan_price($proMonthly) }} or {{ plan_price($entMonthly) }} goes on the schedule's billing balance and comes off its next invoice automatically. Your plan and renewal date are untouched, and a credit larger than the invoice carries over to the one after</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Has no active subscription</span></td>
                        <td>The schedule is moved onto the credit's tier for 30 days. If it already had time left on a plan, the 30 days are added to the end of it rather than replacing it</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">Tip</div>
            <p>Applying a credit is final: it is spent on the schedule you choose and cannot be split across schedules or moved afterwards. To try a paid tier on a schedule that is not paying yet, spend the credit there, since that is the case where it buys 30 days of the plan outright rather than {{ plan_price($proMonthly) }} or {{ plan_price($entMonthly) }} off a bill.</p>
        </div>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Earned plans stay unbranded</div>
            <p>A month of Pro or Enterprise you earned through a referral counts as a plan you earned, not one handed to you, so it does not add the Event Schedule credit chip to your public pages the way an admin-granted plan does.</p>
        </div>
    </section>

    <!-- Referral Statuses -->
    <section id="statuses" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4.5h14.25M3 9h9.75M3 13.5h9.75m4.5-4.5v12m0 0l-3.75-3.75M17.25 21l3.75-3.75" />
            </svg>
            Referral Statuses
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Every referral carries one of five statuses, shown as a coloured badge in the history table:
        </p>

        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Meaning</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Pending</span></td>
                        <td>They have created an account through your link but have not paid for a plan yet. No tier is shown while a referral is at this stage</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Subscribed</span></td>
                        <td>They have started a paid Pro or Enterprise subscription and the 30-day qualifying period is running</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Qualified</span></td>
                        <td>They were still subscribed 30 days later, so the credit is yours and is waiting in Credits Ready to Apply</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Credited</span></td>
                        <td>You have spent the credit, and the schedule it went to is named in the Credited To column</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Expired</span></td>
                        <td>The referral can no longer earn anything. This happens two ways: they subscribed but cancelled before the 30 days were up, or they never subscribed at all and 90 days have passed since they signed up</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Statuses move once a day</div>
            <p>A daily job promotes referrals to Qualified and expires the ones that have run out of time, so a status can be up to a day behind what happened on the account. A credit that has already reached Qualified stays yours to spend even if the person you referred cancels later.</p>
        </div>
    </section>

    <!-- Referral History -->
    <section id="history" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
            </svg>
            Referral History
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The <strong class="text-gray-900 dark:text-white">Referral History</strong> table at the bottom of the page lists every referral you have made. It appears once you have at least one.
        </p>

        <x-doc-screenshot id="referral-history" alt="Referral history table" />

        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Column</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Date</span></td>
                        <td>When they signed up through your link. Click the heading to sort, newest first by default</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Referred User</span></td>
                        <td>Their email address, partly masked, in the form <code class="doc-inline-code">li***@example.com</code>. You never see a referred organizer's full address or name</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Plan Tier</span></td>
                        <td>Pro or Enterprise, shown once they subscribe. A dash means the referral is still Pending or expired without one</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Status</span></td>
                        <td>The current status (see <a href="#statuses" class="doc-link">Referral Statuses</a> above). This heading is sortable too</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Credited To</span></td>
                        <td>The schedule you applied the credit to, or a dash if it has not been applied</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The table shows 20 referrals per page, with paging links underneath once you pass that.
        </p>
    </section>

</x-docs-page>
