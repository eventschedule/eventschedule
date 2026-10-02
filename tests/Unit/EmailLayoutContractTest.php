<?php

namespace Tests\Unit;

use App\Models\Event;
use App\Models\Role;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * The promises <x-email.layout> and its components make to every email built on them, and that
 * existing tests of individual mails rely on:
 *
 *  - the preheader is the first thing in <body>, its style starting "display: none;", because
 *    SignupCodeStepTest pins that shape (a code leading the preview is what "Copy code" detection
 *    keys on);
 *  - dir="rtl" appears only in an RTL locale and no CSS mentions [dir, because ActivationNudgeTest
 *    asserts the string is absent elsewhere;
 *  - the layout prints no link of its own, because FederationWelcomeTest requires every href in
 *    that mail to be one the view chose;
 *  - a guest mail never names the platform (docs/BRANDING_MATRIX.md rule 5);
 *  - a URL bound with :href is escaped exactly once.
 *
 * Built in memory: no database.
 */
class EmailLayoutContractTest extends TestCase
{
    private function render(string $template, array $data = []): string
    {
        return Blade::render($template, $data, deleteCachedView: true);
    }

    private function schedule(): Role
    {
        $role = new Role;
        $role->forceFill(['name' => 'The Blue Note', 'subdomain' => 'bluenote', 'type' => 'venue', 'timezone' => 'America/New_York', 'formatted_address' => '131 W 3rd St']);

        return $role;
    }

    public function test_the_preheader_leads_the_body(): void
    {
        $html = $this->render('<x-email.layout :theme="\App\Utils\EmailTheme::account()" title="T" preheader="123456 - your code">x</x-email.layout>');

        $this->assertMatchesRegularExpression('/<body[^>]*>\s*<div style="display: none;[^"]*">123456 /', $html);
    }

    public function test_direction_and_language_follow_the_locale(): void
    {
        $template = '<x-email.layout :theme="\App\Utils\EmailTheme::guest($role)" title="T"><x-email.heading>H</x-email.heading></x-email.layout>';

        $en = $this->render($template, ['role' => $this->schedule()]);
        $this->assertStringContainsString('lang="en"', $en);
        $this->assertStringNotContainsString('dir="rtl"', $en);
        $this->assertStringNotContainsString('[dir', $en);

        app()->setLocale('he');
        $he = $this->render($template, ['role' => $this->schedule()]);
        $this->assertStringContainsString('lang="he"', $he);
        $this->assertStringContainsString('dir="rtl"', $he);
        $this->assertStringNotContainsString('[dir', $he);
    }

    public function test_the_layout_prints_no_link_of_its_own(): void
    {
        foreach (['\App\Utils\EmailTheme::account()', '\App\Utils\EmailTheme::owner($role)', '\App\Utils\EmailTheme::guest($role)'] as $theme) {
            $html = $this->render('<x-email.layout :theme="'.$theme.'" title="T"><x-email.heading>H</x-email.heading></x-email.layout>', ['role' => $this->schedule()]);

            $this->assertStringNotContainsString('href=', $html, $theme);
            $this->assertSame(1, substr_count($html, '<h1'), $theme);
            $this->assertStringContainsString('content="light dark"', $html, $theme);
        }
    }

    public function test_only_mail_from_the_platform_names_the_platform(): void
    {
        $role = $this->schedule();
        $footer = "<x-slot:footer>\n<x-email.footer :links=\"[['Unsubscribe', 'https://x.test/u']]\">Why</x-email.footer>\n</x-slot:footer>";

        $guest = $this->render('<x-email.layout :theme="\App\Utils\EmailTheme::guest($role)" title="T">'.$footer."\nx\n</x-email.layout>", ['role' => $role]);
        $this->assertStringNotContainsString(config('app.name'), $guest);
        $this->assertStringContainsString('The Blue Note', $guest);

        $owner = $this->render('<x-email.layout :theme="\App\Utils\EmailTheme::owner($role)" title="T">'.$footer."\nx\n</x-email.layout>", ['role' => $role]);
        $this->assertSame(1, substr_count($owner, config('app.name')), 'The owner mail signs once, in the footer.');

        $none = $this->render('<x-email.layout :theme="\App\Utils\EmailTheme::guest(null)" title="T">x</x-email.layout>');
        $this->assertStringNotContainsString(config('app.name'), $none);
        $this->assertStringNotContainsString('es-tile', $none);
    }

    public function test_a_bound_url_is_escaped_once(): void
    {
        $html = $this->render('<x-email.layout :theme="\App\Utils\EmailTheme::account()" title="T"><x-email.button :href="$u">Go</x-email.button><x-email.text>See <x-email.link :href="$u">this</x-email.link>.</x-email.text></x-email.layout>', ['u' => 'https://x.test/a?b=1&c=2']);

        $this->assertSame(2, substr_count($html, 'href="https://x.test/a?b=1&amp;c=2"'));
        $this->assertStringNotContainsString('&amp;amp;', $html);
        // The inline link sits flush against the punctuation that follows it.
        $this->assertStringContainsString('>this</a>.', $html);
    }

    public function test_calendar_links_carry_the_event_page_not_its_description(): void
    {
        // The description rode URL-encoded in both the Google and the Outlook link, uncapped, and
        // could push a ticket email past Gmail's 102KB clip.
        $role = $this->schedule();
        $event = new Event;
        $event->forceFill(['name' => 'Gig', 'slug' => 'gig', 'starts_at' => '2026-10-10 00:00:00', 'duration' => 2, 'description_html' => '<p>'.str_repeat('a long description ', 1000).'</p>']);
        $event->id = 5;
        $event->setRelation('creatorRole', $role);
        $event->setRelation('roles', collect([$role]));

        $html = $this->render('<x-email.layout :theme="\App\Utils\EmailTheme::guest($role)" title="T"><x-email.event :event="$event" :role="$role" /></x-email.layout>', ['role' => $role, 'event' => $event]);

        $this->assertStringContainsString('calendar.google.com', $html);
        $this->assertStringContainsString('outlook.live.com', $html);
        $this->assertStringNotContainsString('a+long+description', $html);
        $this->assertLessThan(20 * 1024, strlen($html));

        // The helpers themselves still describe the event when no override is given.
        $this->assertStringContainsString('a+long+description', $event->getGoogleCalendarUrl());
        $this->assertStringContainsString('details=https%3A%2F%2Fx.test', $event->getGoogleCalendarUrl(null, 'https://x.test'));
    }

    public function test_no_email_component_is_handed_an_echoed_attribute(): void
    {
        // <x-email.button href="{{ $url }}"> escapes the value going in and again coming out,
        // turning every & in a signed URL into &amp;amp;. Bind instead: :href="$url".
        $offenders = [];

        foreach (File::allFiles(resource_path('views')) as $file) {
            if (preg_match_all('/<x-email\.[^>]*?\s[\w-]+="[^"]*\{[{!]/s', $file->getContents(), $m)) {
                $offenders[] = $file->getRelativePathname();
            }
        }

        $this->assertSame([], $offenders, 'Bind dynamic values with :prop="$expr", never prop="{{ $expr }}".');
    }
}
