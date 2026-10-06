<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Webhook;
use App\Services\DemoService;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The settings page in the form vocabulary the event form set: a line under each section's name
 * saying what is saved there, rows that open in place where tabs inside a section used to be, and
 * a page that comes back from a refused save with what was typed.
 *
 * What a row does when it is pressed, and which section an address opens, is script and is driven
 * in tests/Browser/ProfileTest.php. Everything the server decides is here.
 */
class SettingsPageTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.hosted' => true]);
    }

    private function page(User $user, array $session = []): string
    {
        return $this->actingAs($user)->withSession($session)->get(route('profile.edit'))->assertOk()->getContent();
    }

    /** The line under a section's name: once in the sidebar and once in the phone header. */
    private function summaries(string $html, string $section): array
    {
        preg_match_all('/data-section="'.$section.'">.*?<span class="section-nav-summary[^"]*"><bdi>(.*?)<\/bdi>/s', $html, $found);

        return array_map(fn ($text) => html_entity_decode($text, ENT_QUOTES), $found[1]);
    }

    private function summary(string $html, string $section): string
    {
        $both = $this->summaries($html, $section);
        $this->assertCount(2, $both, "{$section} has a line in the sidebar and in its phone header");
        $this->assertSame($both[0], $both[1], "{$section} says the same thing in both places");

        return $both[0];
    }

    /** The row of a list: its summary, and whether it carries the warning colour. */
    private function row(string $html, string $group, string $tab): array
    {
        $this->assertSame(1, preg_match(
            '/<button[^>]*data-row-group="'.$group.'" data-tab="'.$tab.'"[^>]*>.*?<span class="event-row-summary([^"]*)"[^>]*><bdi>(.*?)<\/bdi>/s',
            $html, $found
        ), "the {$group} list has a row for {$tab}");

        return ['text' => html_entity_decode($found[2], ENT_QUOTES), 'warn' => str_contains($found[1], 'is-warn')];
    }

    /**
     * The markup of one tab, from its id to the next phone header; or of one section inside a tab
     * (a .settings-block), which ends where the next section or the next tab begins.
     */
    private function section(string $html, string $id): string
    {
        $this->assertSame(1, preg_match('/id="'.preg_quote($id, '/').'" class="(section-content|settings-block)[^"]*"/', $html, $found, PREG_OFFSET_CAPTURE), "{$id} is on the page");
        $start = $found[0][1];
        $after = $start + strlen($found[0][0]);
        $ends = array_filter([
            strpos($html, 'class="mobile-section-header"', $after),
            $found[1][0] === 'settings-block' ? strpos($html, 'class="settings-block"', $after) : false,
        ], fn ($at) => $at !== false);

        return substr($html, $start, ($ends ? min($ends) : strlen($html)) - $start);
    }

    /** Whether the line under a tab's name is said quietly, which is how "nothing to report" reads. */
    private function quiet(string $html, string $tab): bool
    {
        $this->assertSame(1, preg_match('/data-section="'.$tab.'">.*?<span class="section-nav-summary([^"]*)">/s', $html, $found), "{$tab} has a line under its name");

        return str_contains($found[1], 'is-empty');
    }

    /** The sections stacked inside one tab, in the order they come. */
    private function blocks(string $html, string $tab): array
    {
        preg_match_all('/id="(section-[a-z-]+)" class="settings-block"/', $this->section($html, $tab), $found);

        return $found[1];
    }

    private function connected(User $user): User
    {
        $user->forceFill([
            'stripe_account_id' => 'acct_1Marge', 'stripe_completed_at' => now(), 'stripe_company_name' => 'Springfield Jazz Society',
            'invoiceninja_api_key' => 'token-123', 'invoiceninja_company_name' => 'Jazz Books',
            'payment_url' => 'https://pay.springfieldjazz.org/tickets',
            'api_key' => 'abcd1234', 'api_key_hash' => 'x',
            'google_oauth_id' => '1234567890', 'google_token' => 'g-token', 'microsoft_token' => 'ms-token',
            'two_factor_secret' => 'JBSWY3DPEHPK3PXP', 'two_factor_confirmed_at' => now(),
            'timezone' => 'America/Chicago', 'language_code' => 'en', 'use_24_hour_time' => true,
        ])->saveQuietly();

        return $user->fresh();
    }

    public function test_each_section_says_what_is_saved_under_its_name(): void
    {
        $nothing = $this->createOwner();
        $html = $this->page($nothing);

        $this->assertSame($nothing->name.' · '.$nothing->email, $this->summary($html, 'section-profile'));
        $this->assertSame(__('messages.settings_none_connected'), $this->summary($html, 'section-payment-methods'));
        // A tab of several sections says the one or two things worth knowing before it is opened.
        // Security: two-factor, and the password only when there is none.
        $this->assertSame(__('messages.settings_two_factor_short').': '.__('messages.disabled'), $this->summary($html, 'section-security'));
        $this->assertSame(__('messages.settings_none_connected'), $this->summary($html, 'section-integrations'));
        // With no state to report a tab says what is in it, quietly: none is left blank.
        $this->assertSame('API, '.__('messages.webhooks'), $this->summary($html, 'section-developers'));
        $this->assertSame(
            __('messages.backup_and_restore').', '.__('messages.data_export_title').', '.__('messages.delete_account'),
            $this->summary($html, 'section-account-data')
        );
        foreach (['section-security', 'section-integrations', 'section-developers', 'section-account-data'] as $tab) {
            $this->assertTrue($this->quiet($html, $tab), "{$tab}: said quietly");
        }

        $everything = $this->connected($this->createOwner());
        Webhook::create(['user_id' => $everything->id, 'url' => 'https://hooks.springfieldjazz.org/in', 'secret' => 's1', 'is_active' => true]);
        Webhook::create(['user_id' => $everything->id, 'url' => 'https://hooks.example.org/in', 'secret' => 's2', 'is_active' => false])
            ->forceFill(['created_at' => now()->addMinute()])->save();
        $html = $this->page($everything);

        $this->assertSame('Stripe, Invoice Ninja, '.__('messages.payment_url'), $this->summary($html, 'section-payment-methods'));
        $this->assertSame(__('messages.settings_two_factor_short').': '.__('messages.enabled'), $this->summary($html, 'section-security'));
        $this->assertSame('Google, Outlook', $this->summary($html, 'section-integrations'), 'what is connected, by name');
        $this->assertSame('API · hooks.springfieldjazz.org +1', $this->summary($html, 'section-developers'), 'the key is on; a webhook that is ON, and how many more there are: the newer one is switched off');
        foreach (['section-security', 'section-integrations', 'section-developers'] as $tab) {
            $this->assertFalse($this->quiet($html, $tab), "{$tab}: something is on, so it is not said quietly");
        }
    }

    /** A line under a name is a claim about what works, so it follows what the app will accept. */
    public function test_a_summary_does_not_say_on_for_what_is_off(): void
    {
        $user = $this->createOwner();
        Webhook::create(['user_id' => $user->id, 'url' => 'https://hooks.example.org/in', 'secret' => 's1', 'is_active' => false]);
        $user->forceFill(['api_key' => 'abcd1234', 'api_key_expires_at' => now()->subDay()])->save();

        $html = $this->page($user->fresh());

        // The only webhook is switched off, so it is not named; a key past its date is refused
        // by the API, so it is not simply "API".
        $this->assertSame('API: '.__('messages.expired'), $this->summary($html, 'section-developers'));

        // Three webhooks on one host are three webhooks.
        foreach (['a', 'b', 'c'] as $path) {
            Webhook::create(['user_id' => $user->id, 'url' => 'https://hooks.springfieldjazz.org/'.$path, 'secret' => 's', 'is_active' => true]);
        }
        $this->assertSame('API: '.__('messages.expired').' · hooks.springfieldjazz.org +3', $this->summary($this->page($user->fresh()), 'section-developers'));
    }

    public function test_a_setup_left_half_way_is_said_as_that(): void
    {
        $user = $this->createOwner();
        $user->forceFill([
            'stripe_account_id' => 'acct_1Bart', 'stripe_completed_at' => null,
            'two_factor_secret' => 'JBSWY3DPEHPK3PXP', 'two_factor_confirmed_at' => null,
            'password' => null, 'google_oauth_id' => '555',
        ])->saveQuietly();
        $html = $this->page($user->fresh());

        $this->assertSame(__('messages.settings_setup_not_finished'), $this->summary($html, 'section-payment-methods'));
        $this->assertSame(['text' => __('messages.settings_setup_not_finished'), 'warn' => true], $this->row($html, 'payment', 'stripe'));
        $this->assertSame(
            __('messages.password').': '.__('messages.settings_password_not_set').' · '.__('messages.settings_two_factor_short').': '.__('messages.settings_setup_not_finished'),
            $this->summary($html, 'section-security'),
            'no password is worth saying; two-factor left half way is said as that'
        );
        $this->assertSame('Google', $this->summary($html, 'section-integrations'));
    }

    public function test_payment_methods_are_rows_that_say_whether_each_is_connected(): void
    {
        $html = $this->page($this->connected($this->createOwner()));

        $this->assertSame(['text' => __('messages.connected').' · Springfield Jazz Society', 'warn' => false], $this->row($html, 'payment', 'stripe'));
        $this->assertSame(['text' => __('messages.connected').' · Jazz Books', 'warn' => false], $this->row($html, 'payment', 'invoiceninja'));
        $this->assertSame(['text' => __('messages.connected').' · pay.springfieldjazz.org', 'warn' => false], $this->row($html, 'payment', 'payment-url'));
        $this->assertStringNotContainsString(__('messages.connected'), $this->row($html, 'payment', 'paypal')['text']);

        // Every pane is on the page, closed, under the id the Help link and the gateway tests read.
        foreach (['stripe', 'invoiceninja', 'payment-url', 'payfast', 'paypal'] as $tab) {
            $this->assertMatchesRegularExpression('/<div id="payment-tab-'.$tab.'" class="event-subrow-body" hidden>/', $html);
        }
        $this->assertStringNotContainsString('ap-tab-container', $html, 'no strip of tabs is left inside any section');
    }

    /** Links land on these (?highlight=phone, the unsubscribe page's ?tab=general): they need no row opened. */
    public function test_the_profiles_own_fields_are_always_on_the_page(): void
    {
        $owner = $this->createOwner();
        $this->createRole($owner, 'talent');
        $profile = $this->section($this->page($owner), 'section-profile');

        // The class wherever it appears, alone or among others: nothing before it is inside a row.
        $firstPane = strpos($profile, 'event-subrow-body');
        $this->assertNotFalse($firstPane, 'sanity check: the profile has rows');
        foreach (['id="name"', 'id="email"', 'name="is_subscribed"', 'id="suggestions-field"', 'name="ask_before_following"', 'id="phone-field"', 'id="profile_image"'] as $field) {
            $at = strpos($profile, $field);
            $this->assertNotFalse($at, "{$field} is in the profile");
            $this->assertLessThan($firstPane, $at, "{$field} is outside every row");
        }

        // The three switches about what the app sends and asks sit together, after the fields that
        // say who you are: two of them used to split Email from Phone, the third was filed under
        // Localization.
        $preferences = strpos($profile, 'id="profile-preferences"');
        $this->assertGreaterThan(strpos($profile, 'id="profile_image"'), $preferences);
        foreach (['name="is_subscribed"', 'id="suggestions-field"', 'name="ask_before_following"'] as $switch) {
            $this->assertGreaterThan($preferences, strpos($profile, $switch), "{$switch} is under Preferences");
        }
        $this->assertLessThan(strpos($profile, 'name="is_subscribed"'), strpos($profile, 'id="phone-field"'), 'Phone follows Email with nothing between them');

        // And the rows are the three that were tabs, in a list of their own.
        $this->assertSame(1, preg_match('/English · America\/New_York · '.preg_quote(__('messages.settings_12_hour'), '/').'/', $this->row($profile, 'profile', 'localization')['text']));
        $this->assertMatchesRegularExpression('/<div id="profile-tab-localization" class="event-subrow-body" hidden>/', $profile);
        $this->assertMatchesRegularExpression('/<div id="profile-tab-appearance" class="event-subrow-body" hidden/', $profile);
    }

    public function test_a_refused_profile_save_gives_back_what_was_chosen(): void
    {
        $owner = $this->createOwner();
        $owner->forceFill(['language_code' => 'en'])->saveQuietly();

        $this->actingAs($owner)->from(route('profile.edit'))->patch(route('profile.update'), [
            'name' => 'A name typed before the refusal',
            'email' => 'not-an-email',
            'timezone' => 'Europe/Paris',
            'language_code' => 'fr',
        ])->assertSessionHasErrors('email');

        $profile = $this->section($this->page($owner->fresh()), 'section-profile');

        $this->assertStringContainsString('value="A name typed before the refusal"', $profile);
        $this->assertMatchesRegularExpression('/<option value="fr" selected>/', $profile, 'the language that was chosen, not the stored one');
        $this->assertDoesNotMatchRegularExpression('/<option value="en" selected>/', $profile);
        $this->assertSame('en', $owner->fresh()->language_code, 'sanity check: the save was refused');
    }

    /** With nothing marked the browser offers the first of the list, and a save about anything else keeps it. */
    public function test_a_stored_language_that_is_not_offered_falls_back_to_the_pages_own(): void
    {
        $owner = $this->createOwner();
        $owner->forceFill(['language_code' => 'xx'])->saveQuietly();
        $this->assertArrayNotHasKey('xx', config('app.supported_languages'));

        $profile = $this->section($this->page($owner->fresh()), 'section-profile');

        $this->assertMatchesRegularExpression('/<option value="'.app()->getLocale().'" selected>/', $profile);
    }

    public function test_a_refused_webhook_edit_comes_back_open_with_what_was_typed(): void
    {
        $owner = $this->createOwner();
        $hook = Webhook::create(['user_id' => $owner->id, 'url' => 'https://hooks.example.org/in', 'secret' => 's1', 'description' => 'Accounting', 'is_active' => true]);
        $id = UrlUtils::encodeId($hook->id);

        $this->actingAs($owner)->from(route('profile.edit'))->put(route('webhooks.update', $id), [
            '_form' => 'edit-'.$id,
            'url' => 'not a url',
            'description' => 'Typed before the refusal',
            'event_types' => ['sale.paid'],
        ])->assertSessionHasErrors('url');

        $html = $this->section($this->page($owner), 'section-webhooks');

        $this->assertSame(1, preg_match('/<div id="webhook-edit-'.$id.'" class="event-add-box mb-4"\s*>(.*?)<form id="webhook-add-form"/s', $html, $edit), 'its own form is open');
        $this->assertStringContainsString('value="Typed before the refusal"', $edit[1]);
        $this->assertStringContainsString('value="not a url"', $edit[1]);
        $this->assertStringContainsString('text-red-600', $edit[1], 'and the message is in it');
        $this->assertSame(1, preg_match('/value="sale\.paid"\s+checked/', $edit[1]));
        $this->assertSame(0, preg_match('/value="sale\.created"\s+checked/', $edit[1]), 'only the types that were ticked');

        $this->assertSame(1, preg_match('/<form id="webhook-add-form".*?<\/form>/s', $html, $addForm));
        $add = $addForm[0];
        $this->assertStringNotContainsString('text-red-600', $add, 'the form for a new webhook does not take the message for itself');
        $this->assertStringNotContainsString('Typed before the refusal', $add);
        $this->assertSame('Accounting', $hook->fresh()->description, 'sanity check: the save was refused');
    }

    public function test_a_refused_new_webhook_keeps_its_form_open_with_the_message(): void
    {
        $owner = $this->createOwner();
        Webhook::create(['user_id' => $owner->id, 'url' => 'https://hooks.example.org/in', 'secret' => 's1', 'is_active' => true]);

        // With a webhook already there the form for another is behind its link ...
        $this->assertMatchesRegularExpression('/<form id="webhook-add-form"[^>]*hidden/', $this->page($owner));

        $this->actingAs($owner)->from(route('profile.edit'))->post(route('webhooks.store'), [
            '_form' => 'add', 'url' => 'nope', 'description' => 'A new one',
        ])->assertSessionHasErrors('url');

        // ... and comes back open when its own save was refused.
        $this->assertSame(1, preg_match('/<form id="webhook-add-form".*?<\/form>/s', $this->page($owner), $addForm));
        $add = $addForm[0];
        $this->assertDoesNotMatchRegularExpression('/^<form id="webhook-add-form"[^>]*hidden/', $add);
        $this->assertStringContainsString('value="A new one"', $add);
        $this->assertStringContainsString('text-red-600', $add);
    }

    public function test_switching_two_factor_off_asks_for_the_password_in_the_page(): void
    {
        $owner = $this->connected($this->createOwner());
        $section = $this->section($this->page($owner), 'section-two-factor');

        $this->assertSame(1, preg_match('/<form id="two-factor-disable"[^>]*>(.*?)<\/form>/s', $section, $form));
        $this->assertStringContainsString('name="current_password"', $form[1]);
        $this->assertMatchesRegularExpression('/<form id="two-factor-disable"[^>]*hidden/', $section, 'behind its link until asked for');
        $this->assertStringNotContainsString('prompt(', $section, 'not a browser prompt');

        // A wrong password comes back with the form open and the message beside the field.
        $this->actingAs($owner)->from(route('profile.edit'))->post(route('two-factor.disable'), ['current_password' => 'wrong'])
            ->assertSessionHasErrors('current_password');
        $section = $this->section($this->page($owner), 'section-two-factor');
        $this->assertDoesNotMatchRegularExpression('/<form id="two-factor-disable"[^>]*hidden/', $section);
        $this->assertSame(1, preg_match('/<form id="two-factor-disable"[^>]*>(.*?)<\/form>/s', $section, $form));
        $this->assertStringContainsString('text-red-600', $form[1]);
        $this->assertNotNull($owner->fresh()->two_factor_confirmed_at, 'sanity check: it is still on');
    }

    public function test_an_account_without_a_password_is_asked_before_two_factor_goes_off(): void
    {
        $owner = $this->connected($this->createOwner());
        $owner->forceFill(['password' => null])->saveQuietly();
        $section = $this->section($this->page($owner->fresh()), 'section-two-factor');

        $this->assertMatchesRegularExpression('/<form id="two-factor-disable"[^>]*data-confirm="[^"]+"/', $section, 'there is no password to ask for, so it confirms');
        $this->assertMatchesRegularExpression('/<button type="submit" form="two-factor-disable"/', $section);
    }

    public function test_new_recovery_codes_are_asked_for_twice_and_a_setup_can_be_backed_out_of(): void
    {
        $owner = $this->connected($this->createOwner());
        $section = $this->section($this->page($owner), 'section-two-factor');
        $this->assertMatchesRegularExpression('/action="'.preg_quote(route('two-factor.recovery-codes'), '/').'" data-confirm="[^"]+"/', $section, 'the codes in use stop working at once');

        $owner->forceFill(['two_factor_confirmed_at' => null])->saveQuietly();
        $section = $this->section($this->page($owner->fresh()), 'section-two-factor');
        $this->assertStringContainsString(route('two-factor.confirm'), $section, 'sanity check: this is the setup in progress');
        $this->assertStringContainsString('data-reveal="two-factor-disable"', $section, 'Cancel goes through the same door as Disable');
        $this->assertStringContainsString('id="two-factor-disable"', $section);
    }

    /**
     * Each of the three saves that says "Saved" says it once, in its own section, and with no
     * script: the old fades were Alpine islands, each rendered only under its own status, so each
     * status has to be looked at under itself.
     */
    public function test_saving_says_saved_once_beside_the_button_without_a_script(): void
    {
        $owner = $this->connected($this->createOwner());

        // The class name alone is also in the page's styles: this is the element.
        $said = 'class="event-status is-on form-kit-saved"';

        foreach ([
            'profile-updated' => 'section-profile',
            'password-updated' => 'section-password',
            'payments-updated' => 'section-payment-methods',
        ] as $status => $section) {
            $html = $this->page($owner, ['status' => $status]);

            $this->assertSame(1, substr_count($html, $said), "{$status}: said once");
            $this->assertStringContainsString($said, $this->section($html, $section), "{$status}: said in {$section}");
            foreach (['x-init=', 'x-data', 'x-show=', 'x-transition'] as $alpine) {
                $this->assertStringNotContainsString($alpine, $this->section($html, $section), "{$status}: no Alpine in {$section}");
            }
        }

        // withSession() puts the value in for good, where the app flashes it for one request.
        $this->flushSession();
        $this->assertStringNotContainsString($said, $this->page($owner));
    }

    /** Every `<button>` inside the sections of a page, as its opening tag. */
    private function buttons(string $html): array
    {
        $start = strpos($html, 'id="section-profile" class="section-content');
        $end = strpos($html, 'window.FormKit = ');
        preg_match_all('/<button\b[^>]*>/', substr($html, $start, $end - $start), $found);

        return $found[0];
    }

    /** The account states that between them render every form of the page. */
    private function accountStates(): array
    {
        $connected = $this->connected($this->createOwner());
        Webhook::create(['user_id' => $connected->id, 'url' => 'https://hooks.example.org/in', 'secret' => 's1', 'is_active' => true]);

        $pending = $this->createOwner();
        $pending->forceFill(['two_factor_secret' => 'JBSWY3DPEHPK3PXP', 'stripe_account_id' => 'acct_1Pending'])->saveQuietly();

        $social = $this->createOwner();
        $social->forceFill(['password' => null, 'google_oauth_id' => '42'])->saveQuietly();

        return [
            'nothing connected' => [$this->createOwner(), []],
            'everything connected' => [$connected, []],
            'two-factor and Stripe half way' => [$pending->fresh(), []],
            'no password yet, just re-authenticated' => [$social->fresh(), ['can_set_password' => now()->timestamp]],
        ];
    }

    /**
     * The black uppercase button is x-primary-button's. No button of the page is one, whatever its
     * type attribute says, in any state of the account; and every button that submits a form and
     * is not a text action or the red one is the brand button.
     */
    public function test_every_section_saves_with_the_brand_button(): void
    {
        $forms = 0;
        foreach ($this->accountStates() as $state => [$user, $session]) {
            $this->flushSession();
            foreach ($this->buttons($this->page($user, $session)) as $button) {
                $this->assertStringNotContainsString('bg-gray-800', $button, "{$state}: a black button is left: {$button}");
                // The red button keeps its component (capitals, wide tracking) and is recased by
                // the page's own .settings-danger: any other capitals button is a stray.
                if (! str_contains($button, 'settings-danger')) {
                    $this->assertStringNotContainsString('tracking-widest', $button, "{$state}: an uppercase component button is left: {$button}");
                }

                $submits = ! str_contains($button, 'type="button"');
                $textAction = str_contains($button, 'event-link') || str_contains($button, 'event-subrow') || str_contains($button, 'mobile-section-header');
                if ($submits && ! $textAction && ! str_contains($button, 'settings-danger')) {
                    $this->assertStringContainsString('--brand-button-bg', $button, "{$state}: a save that is not the brand button: {$button}");
                    $forms++;
                }
            }
        }
        $this->assertGreaterThan(15, $forms, 'sanity check: the scan found the save buttons of every state');
    }

    public function test_demo_mode_switches_saving_off_and_says_why_in_a_panel(): void
    {
        $demo = $this->createOwner();
        $demo->forceFill(['email' => DemoService::DEMO_EMAIL])->saveQuietly();
        $demo = $this->connected($demo->fresh());
        $html = $this->page($demo);

        // Every section that can be changed says so in the panel, not the profile alone.
        foreach (['section-profile', 'section-payment-methods', 'section-password', 'section-two-factor', 'section-google-calendar', 'section-microsoft-calendar', 'section-api', 'section-webhooks', 'section-backup', 'section-delete'] as $id) {
            $section = $this->section($html, $id);
            $this->assertStringContainsString(__('messages.demo_mode_settings_disabled'), $section, "{$id} says why it cannot be changed");
            $this->assertStringContainsString('bg-amber-50', $section, "{$id}: in the bordered amber panel, not a yellow box");
        }
        $this->assertStringNotContainsString('bg-yellow-50', $html);
        // And every brand button that would save is switched off. The attribute itself, at the end
        // of the tag: the button's classes also say "disabled:".
        // (The backup tool's buttons are its Vue app's; that whole tool is inert instead.)
        // "Download my data" is left on: it changes nothing, and it is a right, not a setting.
        $changing = str_replace($this->section($html, 'section-data'), '', $html);
        $saves = array_filter($this->buttons($changing), fn ($button) => str_contains($button, '--brand-button-bg') && ! str_contains($button, 'type="button"') && ! str_contains($button, 'v-bind:disabled'));
        $this->assertGreaterThanOrEqual(4, count($saves), 'sanity check: the demo page still shows its save buttons');
        $this->assertMatchesRegularExpression('/<div id="backup-app"[^>]*\sinert\s*>/', $html, 'the backup tool cannot be reached by keyboard either');
        foreach ($saves as $button) {
            $this->assertStringEndsWith(' disabled>', $button, 'a save left switched on in demo mode: '.$button);
        }
        $this->assertStringNotContainsString('data-alert', $html, 'no button that answers with an alert');
    }

    /** An account without Pro is told so in the same panel; the list and the form are still there. */
    public function test_webhooks_without_a_pro_schedule_say_so_in_a_panel(): void
    {
        $owner = $this->createOwner();
        $this->createFreeRole($owner, 'talent');
        $section = $this->section($this->page($owner), 'section-webhooks');

        $this->assertStringContainsString(__('messages.webhooks_require_pro'), $section);
        $this->assertStringContainsString('bg-amber-50', $section);
        $this->assertStringContainsString(__('messages.settings_no_webhooks'), $section);
    }

    /**
     * Help goes to the part of the user guide for what is on screen. Three things have to hold for
     * that: every key of the map names something on the page (it had a section-appearance entry
     * for a section that never existed), every section and every row has a key (a row with none
     * left Help on its section's page), and the place each key points at exists in the guide.
     */
    public function test_every_section_and_row_has_a_help_page_that_exists(): void
    {
        config(['services.facebook.client_id' => 'fb', 'services.facebook.client_secret' => 'secret']);
        $html = $this->page($this->createOwner());

        $mappings = (new \ReflectionClass(\App\Utils\HelpUtils::class))->getStaticPropertyValue('mappings');
        $anchors = $mappings['settings']['anchors'];
        $this->assertArrayHasKey('payment-tab-stripe', $anchors, 'sanity check: this is the settings map');

        foreach (array_keys($anchors) as $id) {
            if ($id === 'section-app') {
                continue; // only where the install can update itself: SelfUpdateVisibilityTest
            }
            $this->assertStringContainsString('id="'.$id.'"', $html, "the Help map names {$id}, which is not on the page");
        }

        // Every tab of the page, and every section inside one...
        preg_match_all('/id="(section-[a-z-]+)" class="(?:section-content|settings-block)/', $html, $sections);
        $this->assertGreaterThanOrEqual(16, count($sections[1]), 'sanity check: six tabs and the ten sections that share four of them');
        foreach ($sections[1] as $section) {
            $this->assertArrayHasKey($section, $anchors, "{$section} has no Help page");
        }
        // ...and every row, by the pane it opens, which is what the Help link follows when a row
        // is pressed (layouts/navigation: any button[data-row-group], by its aria-controls).
        preg_match_all('/<button[^>]*data-row-group="[a-z]+"[^>]*aria-controls="([a-z_-]+)"/', $html, $rows);
        $this->assertGreaterThanOrEqual(8, count($rows[1]), 'sanity check: the profile and payment rows were found');
        foreach ($rows[1] as $pane) {
            $this->assertArrayHasKey($pane, $anchors, "the row that opens {$pane} has no Help page");
        }
        $this->assertStringContainsString("e.target.closest('button[data-row-group]')", file_get_contents(resource_path('views/layouts/navigation.blade.php')), 'and Help does follow a row when it is pressed');
        // The backup tool's two rows are its own (a Vue app), so the pattern above does not see
        // them: each has a place in the guide too, and an address that names one opens it.
        $backup = $this->section($html, 'section-backup');
        foreach (['export', 'import'] as $tab) {
            $this->assertStringContainsString('<div id="backup-tab-'.$tab.'"', $backup);
            $this->assertArrayHasKey('backup-tab-'.$tab, $anchors, "the {$tab} row of the backup tool has no Help page");
            $this->assertStringContainsString("hash === '#backup-tab-{$tab}'", $backup, "an address can open the {$tab} row");
        }

        // ...also when the address changes with the page already open, which no reload follows.
        $this->assertStringContainsString('openNamedRow(new URL(event.newURL).hash)', $backup);

        // The place each key points at is in the guide.
        $guide = file_get_contents(resource_path('views/marketing/docs/account-settings.blade.php'));
        foreach ($anchors as $id => $url) {
            [$path, $fragment] = array_pad(explode('#', $url, 2), 2, null);
            $this->assertSame('/docs/account-settings', $path, "{$id} points at another page");
            $this->assertNotNull($fragment, "{$id} points at the top of the page, not at its own part");
            $this->assertStringContainsString('id="'.$fragment.'"', $guide, "{$id} points at #{$fragment}, which the guide does not have");
        }
        $this->assertSame('/docs/account-settings', $mappings['settings']['doc']);
    }

    /** The guide lists its parts in the order the page lists its sections. */
    public function test_the_guide_follows_the_order_of_the_page(): void
    {
        config(['services.facebook.client_id' => 'fb', 'services.facebook.client_secret' => 'secret']);
        $html = $this->page($this->createOwner());
        $anchors = (new \ReflectionClass(\App\Utils\HelpUtils::class))->getStaticPropertyValue('mappings')['settings']['anchors'];

        // Down the page: each tab, and each section inside it. A tab of several opens the guide
        // at its first section, so the two share a place; nothing comes before what is above it.
        preg_match_all('/id="(section-[a-z-]+)" class="(?:section-content|settings-block)/', $html, $listed);
        $this->assertGreaterThanOrEqual(16, count($listed[1]));
        $guide = file_get_contents(resource_path('views/marketing/docs/account-settings.blade.php'));
        $last = 0;
        foreach ($listed[1] as $section) {
            $at = strpos($guide, '<section id="'.explode('#', $anchors[$section])[1].'"');
            $this->assertNotFalse($at, "{$section} has a part of its own in the guide");
            $this->assertGreaterThanOrEqual($last, $at, "{$section} comes in the guide where it comes on the page");
            $last = $at;
        }
    }

    /**
     * What the page's script is built on. Which section an address opens is driven in the browser
     * tests; these pin that the two old habits are gone from the page that is served.
     */
    public function test_the_page_script_opens_what_an_address_names_and_remembers_no_inner_tab(): void
    {
        $html = $this->page($this->createOwner());

        $this->assertSame(2, substr_count($html, 'FormKit.openFromHash(window.location.hash)'), 'on load, and when the address changes');
        $this->assertStringContainsString('FormKit.routeErrors()', $html);
        $this->assertStringNotContainsString('resetToFirstTab', $html, 'pressing the section you are on leaves its rows alone');
        $this->assertStringNotContainsString("getItem('profileActiveTab')", $html);
        $this->assertStringNotContainsString("getItem('paymentActiveTab')", $html);
        $this->assertStringNotContainsString("setItem('paymentActiveTab'", $html);
    }

    /**
     * The list is six entries, with no headings between them: the thirteen sections it used to
     * list under five headings are gathered into tabs, and four of those headings are the tabs.
     * A section inside a tab keeps its id, which is what every address the app builds still names.
     */
    public function test_the_tabs_are_listed_in_one_order_everywhere_with_no_headings(): void
    {
        config(['services.facebook.client_id' => 'fb', 'services.facebook.client_secret' => 'secret']);
        $html = $this->page($this->createOwner());

        $expected = ['section-profile', 'section-payment-methods', 'section-security', 'section-integrations', 'section-developers', 'section-account-data'];
        foreach (['class="section-nav-link" data-section="', 'class="mobile-section-header" data-section="', 'class="section-content[^"]*"'] as $where) {
            $pattern = str_contains($where, 'section-content') ? '/id="(section-[a-z-]+)" '.$where.'/' : '/'.$where.'(section-[a-z-]+)"/';
            preg_match_all($pattern, $html, $found);
            $this->assertSame($expected, $found[1], 'the same order in the sidebar, the phone headers and the page');
        }
        $this->assertStringNotContainsString('section-nav-group', $html, 'no headings between the entries');

        // What each tab holds, in order, each under an id of its own.
        $this->assertSame(['section-password', 'section-two-factor'], $this->blocks($html, 'section-security'));
        $this->assertSame(['section-google-calendar', 'section-microsoft-calendar', 'section-facebook'], $this->blocks($html, 'section-integrations'));
        $this->assertSame(['section-api', 'section-webhooks'], $this->blocks($html, 'section-developers'));
        $this->assertSame(['section-backup', 'section-data', 'section-delete'], $this->blocks($html, 'section-account-data'));
        $this->assertSame([], $this->blocks($html, 'section-profile'), 'a tab of one section is that section');

        // The names in the list, and each section's own title inside its tab, which stays on a
        // phone because nothing else there says it.
        foreach (['section-security' => 'settings_group_security', 'section-integrations' => 'integrations', 'section-developers' => 'settings_group_developers', 'section-account-data' => 'data'] as $tab => $key) {
            $this->assertSame(1, preg_match('/data-section="'.$tab.'">\s*<svg[^>]*>.*?<\/svg>\s*<span class="section-nav-text">\s*<span>'.preg_quote(__('messages.'.$key), '/').'<\/span>/s', $html), "{$tab} is listed under its name");
        }
        $twoFactor = $this->section($html, 'section-two-factor');
        $this->assertStringContainsString(__('messages.two_factor_authentication'), $twoFactor);
        $this->assertStringContainsString('<h2 class="form-kit-title settings-block-title">', $twoFactor);
        $this->assertStringContainsString('<h2 class="form-kit-title">', $this->section($html, 'section-profile'), 'a tab of one section has the kit\'s own title');
        $this->assertSame(0, preg_match('/class="section-nav-link"[^>]*>.*?<span>[^<]*'.preg_quote(__('messages.settings'), '/').'[^<]*<\/span>/s', substr($html, strpos($html, '<nav class="space-y-1">'), 9000)), 'no entry repeats the word Settings on the Settings page');

        // An address that names a section inside a tab is opened by showing the tab and moving to it.
        $this->assertStringContainsString('moveTo(blockOf(named));', $html);
    }

    /** Where a section is not offered, its tab goes on without it, and an empty tab is not listed. */
    public function test_a_tab_lists_only_the_sections_this_account_has(): void
    {
        config(['services.facebook.client_id' => null, 'services.facebook.client_secret' => null]);
        $html = $this->page($this->createOwner());

        $this->assertSame(['section-google-calendar', 'section-microsoft-calendar'], $this->blocks($html, 'section-integrations'));
        $this->assertStringNotContainsString('id="section-facebook"', $html);
        $this->assertStringNotContainsString('data-section="section-app"', $html, 'the update tab is for an install that can update itself');
    }

    public function test_a_payment_method_that_is_not_connected_says_what_it_is(): void
    {
        $html = $this->page($this->createOwner());
        $section = $this->section($html, 'section-payment-methods');

        $this->assertSame(__('messages.stripe_help'), $this->row($html, 'payment', 'stripe')['text']);
        $this->assertSame(__('messages.invoiceninja_help'), $this->row($html, 'payment', 'invoiceninja')['text']);
        $this->assertSame(__('messages.payment_url_help'), $this->row($html, 'payment', 'payment-url')['text']);
        // What the method is, not how to fill in its form: that is said inside the row.
        $this->assertSame(__('messages.settings_payfast_about'), $this->row($html, 'payment', 'payfast')['text']);
        $this->assertSame(__('messages.settings_paypal_about'), $this->row($html, 'payment', 'paypal')['text']);
        $this->assertStringContainsString('.event-subrow[aria-expanded="true"] .event-row-summary.is-empty', $html, 'an open row does not say it twice');
        $this->assertMatchesRegularExpression('/data-tab="stripe"[^>]*>.*?<span class="event-row-summary\s+is-empty"/s', $section, 'said quietly: it is not a state');
        $this->assertStringContainsString(__('messages.settings_payment_per_event'), $section);

        // A form that connects something says so on its button, and saves once it is connected.
        $submit = fn (string $html, string $tab) => preg_match('/<div id="payment-tab-'.$tab.'".*?<button[^>]*type="submit"[^>]*>\s*([^<]+?)\s*<\/button>/s', $html, $m) ? $m[1] : null;
        foreach (['invoiceninja', 'payment-url', 'payfast', 'paypal'] as $tab) {
            $this->assertSame(__('messages.connect'), $submit($section, $tab), "{$tab}: nothing to save over yet");
        }
        $connected = $this->section($this->page($this->connected($this->createOwner())), 'section-payment-methods');
        $this->assertSame(__('messages.disconnect'), $submit($connected, 'payment-url'), 'a connected method offers to undo it, in the word every other connection uses');
        // A token is a secret: masked, with the eye.
        $this->assertMatchesRegularExpression('/<input[^>]*type="password"[^>]*id="invoiceninja_api_key"|<input[^>]*id="invoiceninja_api_key"[^>]*type="password"/', $section);
        $this->assertStringNotContainsString('name="invoiceninja_api_key" type="text"', $section);
    }

    public function test_webhooks_say_their_state_and_keep_delete_behind_edit(): void
    {
        $owner = $this->createOwner();
        $on = Webhook::create(['user_id' => $owner->id, 'url' => 'https://hooks.example.org/in', 'secret' => 's1', 'is_active' => true]);
        Webhook::create(['user_id' => $owner->id, 'url' => 'https://hooks.example.org/out', 'secret' => 's2', 'is_active' => false, 'event_types' => ['sale.created']]);
        $section = $this->section($this->page($owner), 'section-webhooks');

        // The state in the words of the action that changes it: Disable an Enabled one, Enable a
        // Disabled one. It read Active and Inactive beside Disable and Enable.
        $this->assertStringContainsString('<span class="event-status is-on ms-2">'.__('messages.enabled').'</span>', $section);
        $this->assertStringContainsString('<span class="event-chip">'.__('messages.disabled').'</span>', $section);
        $this->assertStringNotContainsString(__('messages.inactive'), $section);

        // Delete is inside the panel Edit opens, after the form, and still asks.
        $panel = strpos($section, 'id="webhook-edit-'.UrlUtils::encodeId($on->id).'"');
        // (The address alone is also the one the edit form saves to: the form that asks is the delete.)
        $delete = strpos($section, 'action="'.route('webhooks.destroy', UrlUtils::encodeId($on->id)).'" data-confirm=');
        $this->assertNotFalse($delete);
        $this->assertGreaterThan($panel, $delete);
        $row = substr($section, strpos($section, 'class="event-list-actions"'), 1200);
        $this->assertStringNotContainsString('is-danger', substr($row, 0, strpos($row, 'webhook-edit-btn')), 'nothing red beside Edit');

        // One switch for the usual case: on (and the list put away) for a webhook that gets
        // everything, off (and the list there) for one that gets less.
        $this->assertMatchesRegularExpression('/id="webhook_all_events_'.UrlUtils::encodeId($on->id).'"[^>]*\schecked/s', $section);
        $this->assertSame(2, preg_match_all('/<div class="event-check-grid" data-webhook-events\s+hidden\s*>/', $section), 'the all-events webhook and the new one');
        $this->assertSame(1, preg_match_all('/<div class="event-check-grid" data-webhook-events\s*>/', $section), 'the one that gets one type shows its list');
        $this->assertStringContainsString(__('messages.settings_webhook_no_events_help'), $section);
    }

    /** A webhook address the app will not call comes back on its field, with what was typed. */
    public function test_a_webhook_address_that_is_not_allowed_keeps_what_was_typed(): void
    {
        $owner = $this->createOwner();

        $response = $this->actingAs($owner)->post(route('webhooks.store'), [
            '_form' => 'add', 'url' => 'http://127.0.0.1/hook', 'description' => 'Typed before the refusal', 'event_types' => ['sale.created'],
        ]);

        $response->assertSessionHasErrors('url');
        $this->assertSame('Typed before the refusal', session()->getOldInput('description'));
        $this->assertSame(0, Webhook::count(), 'sanity check: it was refused');
    }

    public function test_the_api_section_says_when_the_key_runs_out_and_asks_before_deleting_it(): void
    {
        $owner = $this->createOwner();
        $owner->forceFill(['api_key' => 'abcd1234', 'api_key_expires_at' => now()->addMonths(3)])->saveQuietly();
        $section = $this->section($this->page($owner->fresh()), 'section-api');

        $this->assertStringContainsString(__('messages.expires').': '.$owner->fresh()->api_key_expires_at->translatedFormat('M j, Y'), $section);
        $this->assertStringContainsString('data-disable-confirm="'.e(__('messages.settings_api_disable_confirm')).'"', $section, 'the save that deletes the key asks first');
        $this->assertStringNotContainsString(__('messages.settings_api_key_expired', ['date' => '']), $section);

        $owner->forceFill(['api_key_expires_at' => now()->subDay()])->saveQuietly();
        $section = $this->section($this->page($owner->fresh()), 'section-api');
        $this->assertStringContainsString(e(__('messages.settings_api_key_expired', ['date' => $owner->fresh()->api_key_expires_at->translatedFormat('M j, Y')])), $section);
        $this->assertStringNotContainsString('id="api-key-expires"', $section);

        // With no key there is nothing to delete, so nothing to ask about.
        $this->assertStringContainsString('data-disable-confirm=""', $this->section($this->page($this->createOwner()), 'section-api'));
    }

    public function test_security_and_data_sections_say_what_will_happen(): void
    {
        $owner = $this->createOwner();
        $html = $this->page($owner);

        // Password: the rule before it is broken, and a button named for what it does.
        $password = $this->section($html, 'section-password');
        $this->assertStringContainsString(__('messages.password_min_chars'), $password);
        $this->assertMatchesRegularExpression('/<button[^>]*type="submit"[^>]*>\s*'.preg_quote(__('messages.update_password'), '/').'\s*<\/button>/', $password);

        // Two-factor, off: said, with what switching it on involves, and in plain words.
        $twoFactor = $this->section($html, 'section-two-factor');
        $this->assertStringContainsString(e(__('messages.settings_two_factor_lead')), $twoFactor);
        $this->assertStringNotContainsString('TOTP', $twoFactor);
        $this->assertStringContainsString('<span class="event-status">'.__('messages.disabled').'</span>', $twoFactor);
        $this->assertStringContainsString(e(__('messages.settings_two_factor_how')), $twoFactor);

        // Your data: the button does not download on the spot, and says so before it is pressed.
        $this->assertStringContainsString(__('messages.settings_data_export_how'), $this->section($html, 'section-data'));

        // Delete account: what goes with it and that it is for good, before the dialog.
        $delete = $this->section($html, 'section-delete');
        $said = strpos($delete, __('messages.settings_delete_account_what_goes'));
        $this->assertNotFalse($said);
        $this->assertLessThan(strpos($delete, 'data-modal-open="confirm-user-deletion"'), $said);
        $this->assertStringNotContainsString(__('messages.delete_account_subscription_warning'), $delete, 'no paid plan, no word about one');
    }

    /** What used to be an alert(), and a verify that could only answer "code invalid". */
    public function test_the_profile_says_things_in_place(): void
    {
        $profile = $this->section($this->page($this->createOwner()), 'section-profile');

        $this->assertStringNotContainsString('alert(', $profile);
        $this->assertStringContainsString('function showImageError()', $profile);
        $this->assertStringContainsString(json_encode(__('messages.settings_save_phone_first'), JSON_UNESCAPED_UNICODE), $profile);
    }

    /** "Are you sure?" did not say what would stop. Each connection says it, in one word for undoing. */
    public function test_disconnecting_says_what_it_stops(): void
    {
        config(['services.facebook.client_id' => 'fb', 'services.facebook.client_secret' => 'secret']);
        $html = $this->page($this->connected($this->createOwner()));

        $google = $this->section($html, 'section-google-calendar');
        $this->assertStringContainsString('data-confirm="'.e(__('messages.settings_confirm_disconnect_google')).'"', $google, 'signing in with Google');
        $this->assertStringContainsString('data-confirm="'.e(__('messages.settings_confirm_disconnect_calendar')).'"', $google, 'the calendar connection');
        $this->assertStringContainsString('data-confirm="'.e(__('messages.settings_confirm_disconnect_calendar')).'"', $this->section($html, 'section-microsoft-calendar'));

        // One word for undoing a connection, on every section that has one.
        $payments = $this->section($html, 'section-payment-methods');
        $this->assertGreaterThanOrEqual(3, preg_match_all('/class="event-link is-danger"[^>]*>\s*'.preg_quote(__('messages.disconnect'), '/').'\s*</', $payments));
        $this->assertStringNotContainsString(__('messages.unlink_account'), $html);

        // And the codes are named for what they are.
        $this->assertStringContainsString(__('messages.settings_regenerate_recovery_codes'), $this->section($html, 'section-two-factor'));
    }

    /** The switch moved out of Localization; what it saves did not. */
    public function test_ask_before_following_saves_from_the_preferences(): void
    {
        $owner = $this->createOwner();
        $payload = ['name' => $owner->name, 'email' => $owner->email, 'timezone' => 'America/New_York', 'language_code' => 'en'];

        $this->actingAs($owner)->patch('/settings', $payload + ['ask_before_following' => '0'])->assertSessionHasNoErrors();
        $this->assertTrue((bool) $owner->fresh()->follow_consent_dismissed);

        // The page shows it the way it was saved.
        $this->assertDoesNotMatchRegularExpression('/id="ask_before_following"[^>]*\schecked/', $this->section($this->page($owner->fresh()), 'section-profile'));

        $this->actingAs($owner)->patch('/settings', $payload + ['ask_before_following' => '1'])->assertSessionHasNoErrors();
        $this->assertFalse((bool) $owner->fresh()->follow_consent_dismissed);
        $this->assertMatchesRegularExpression('/id="ask_before_following"[^>]*\schecked/', $this->section($this->page($owner->fresh()), 'section-profile'));
    }

    /** A paid plan ends with the account: said in the section, where it was only in the dialog. */
    public function test_delete_account_warns_about_a_paid_plan_before_the_dialog(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        \Illuminate\Support\Facades\DB::table('roles')->where('id', $role->id)->update(['stripe_id' => 'cus_settings_page']);
        \Illuminate\Support\Facades\DB::table('subscriptions')->insert([
            'role_id' => $role->id, 'type' => 'default', 'stripe_id' => 'sub_settings_page', 'stripe_status' => 'active',
            'stripe_price' => 'price_pro_monthly', 'quantity' => 1, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $delete = $this->section($this->page($owner->fresh()), 'section-delete');
        $opens = strpos($delete, 'data-modal-open="confirm-user-deletion"');
        $this->assertNotFalse($opens);
        $this->assertSame(2, substr_count($delete, __('messages.delete_account_subscription_warning')), 'in the section and again in the dialog');
        $this->assertLessThan($opens, strpos($delete, __('messages.delete_account_subscription_warning')));
    }

    /** The same refusal from the Edit panel: it comes back open, on its own webhook, as typed. */
    public function test_a_webhook_edit_to_an_address_that_is_not_allowed_comes_back_the_same_way(): void
    {
        $owner = $this->createOwner();
        $hook = Webhook::create(['user_id' => $owner->id, 'url' => 'https://hooks.example.org/in', 'description' => 'Before', 'secret' => 's1', 'is_active' => true]);
        $other = Webhook::create(['user_id' => $owner->id, 'url' => 'https://hooks.example.org/other', 'secret' => 's2', 'is_active' => true]);
        $id = UrlUtils::encodeId($hook->id);

        $html = $this->actingAs($owner)->followingRedirects()->put(route('webhooks.update', $id), [
            '_form' => 'edit-'.$id, 'url' => 'http://127.0.0.1/hook', 'description' => 'Typed in the edit panel', 'event_types' => ['sale.created'],
        ])->assertOk()->getContent();
        $section = $this->section($html, 'section-webhooks');

        $this->assertSame('https://hooks.example.org/in', $hook->fresh()->url, 'sanity check: it was refused');
        $this->assertMatchesRegularExpression('/<div id="webhook-edit-'.$id.'" class="event-add-box mb-4"\s*>/', $section, 'its panel is open');
        $this->assertMatchesRegularExpression('/<div id="webhook-edit-'.UrlUtils::encodeId($other->id).'" class="event-add-box mb-4"\s+hidden\s*>/', $section, 'and only its panel');
        $this->assertStringContainsString('value="Typed in the edit panel"', $section);
        $this->assertStringContainsString('value="http://127.0.0.1/hook"', $section);
        $this->assertSame(1, substr_count($section, __('messages.webhook_url_not_allowed')), 'said once, on the field that was refused');
        // It asked for one type, so the list is there to be seen, with that one ticked.
        $this->assertSame(1, preg_match_all('/<div class="event-check-grid" data-webhook-events\s*>/', $section));
    }

    /** "Connect your ..." is for somebody who has not. Connected, a section says what that gives. */
    public function test_a_connection_reads_for_the_state_it_is_in(): void
    {
        config(['services.facebook.client_id' => 'fb', 'services.facebook.client_secret' => 'secret', 'services.microsoft.client_id' => 'ms']);

        $none = $this->page($this->createOwner());
        $this->assertStringContainsString(__('messages.microsoft_settings_description'), $this->section($none, 'section-microsoft-calendar'));
        $this->assertStringNotContainsString(__('messages.connect_microsoft_calendar_description'), $none, 'the same invitation is not made twice');
        $this->assertStringContainsString(__('messages.facebook_account_description'), $this->section($none, 'section-facebook'));
        $this->assertStringContainsString(__('messages.google_account_description'), $this->section($none, 'section-google-calendar'));
        $this->assertStringNotContainsString(__('messages.settings_calendar_sync_per_schedule', ['section' => __('messages.integrations')]), $none);

        $owner = $this->connected($this->createOwner());
        $owner->forceFill(['facebook_id' => '1234567890'])->saveQuietly();
        $all = $this->page($owner->fresh());
        $perSchedule = e(__('messages.settings_calendar_sync_per_schedule', ['section' => __('messages.integrations')]));

        $outlook = $this->section($all, 'section-microsoft-calendar');
        $this->assertStringNotContainsString(__('messages.microsoft_settings_description'), $outlook);
        $this->assertStringContainsString($perSchedule, $outlook, 'connected is half of it: sync is switched on per schedule');

        $google = $this->section($all, 'section-google-calendar');
        $this->assertStringNotContainsString(__('messages.google_account_description'), $google);
        $this->assertStringNotContainsString(__('messages.connect_google_calendar_description'), $google);
        $this->assertStringContainsString($perSchedule, $google);
        $this->assertStringContainsString(__('messages.settings_social_login_connected', ['provider' => 'Google']), $google);

        $facebook = $this->section($all, 'section-facebook');
        $this->assertStringNotContainsString(__('messages.facebook_account_description'), $facebook);
        $this->assertStringContainsString(__('messages.settings_social_login_connected', ['provider' => 'Facebook']), $facebook);
    }

    /**
     * The code is asked for by the password sign-in and by nothing else: Google and Facebook let
     * the account straight in. The panel said "signing in asks for a code", which was not true of
     * either, and told an account with no password to confirm its password.
     */
    public function test_two_factor_says_which_sign_in_asks_for_the_code(): void
    {
        $owner = $this->createOwner();

        // An install with no other way in, and an account with a password.
        config(['services.google.client_id' => null, 'services.facebook.client_id' => null]);
        $twoFactor = $this->section($this->page($owner), 'section-two-factor');
        $this->assertStringContainsString('with your password', __('messages.settings_two_factor_lead'));
        $this->assertStringContainsString('with your password', __('messages.settings_two_factor_how'));
        $this->assertStringContainsString(e(__('messages.settings_two_factor_lead')), $twoFactor);
        $this->assertStringContainsString(e(__('messages.settings_two_factor_how')), $twoFactor);
        $this->assertStringNotContainsString(e(__('messages.settings_two_factor_not_social')), $twoFactor, 'nothing to say about a sign-in the install does not offer');

        // Google sign-in is offered: it is said that it does not ask.
        config(['services.google.client_id' => 'client-id']);
        $twoFactor = $this->section($this->page($owner), 'section-two-factor');
        $this->assertStringContainsString(e(__('messages.settings_two_factor_not_social')), $twoFactor);

        // An account with no password: nothing to confirm, and the code has nothing to be asked beside.
        config(['services.google.client_id' => null]);
        $owner->forceFill(['password' => null])->save();
        $this->assertFalse($owner->fresh()->hasPassword());
        $twoFactor = $this->section($this->page($owner->fresh()), 'section-two-factor');
        $this->assertStringContainsString(e(__('messages.settings_two_factor_how_no_password')), $twoFactor);
        $this->assertStringNotContainsString(e(__('messages.settings_two_factor_how')), $twoFactor);
        $this->assertStringContainsString(e(__('messages.settings_two_factor_not_social')), $twoFactor, 'the only way into this account is one that does not ask');
        $this->assertStringNotContainsString('name="current_password"', $twoFactor);
    }

    /** The four sentences exist in every language, and none is the English one left in place. */
    public function test_the_two_factor_sentences_are_translated(): void
    {
        foreach (array_keys(config('app.supported_languages')) as $language) {
            foreach (['settings_two_factor_lead', 'settings_two_factor_how', 'settings_two_factor_how_no_password', 'settings_two_factor_not_social'] as $key) {
                $line = trans('messages.'.$key, [], $language);
                $this->assertNotSame('messages.'.$key, $line, "{$key} is missing in {$language}");
                if ($language !== 'en') {
                    $this->assertNotSame(trans('messages.'.$key, [], 'en'), $line, "{$key} is still English in {$language}");
                }
            }
        }
    }
}
