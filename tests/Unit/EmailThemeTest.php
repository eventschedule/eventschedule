<?php

namespace Tests\Unit;

use App\Models\Role;
use App\Utils\ColorUtils;
use App\Utils\EmailTheme;
use Tests\TestCase;

/**
 * EmailTheme is the one place an email's colours come from, and its promise is that every one of
 * them reads: WCAG AA (4.5:1) against each surface it is painted on, in light and in dark mode,
 * whatever accent colour a schedule owner picked. The accents below are the awkward ones - our own
 * blue (3.59:1 under white text), a bright yellow (white never reads on it), black (invisible on a
 * dark card), white, a mid grey, and the 3- and 8-digit forms Role::cssHexColor() lets through.
 *
 * Built in memory: no database.
 */
class EmailThemeTest extends TestCase
{
    private const ACCENTS = [null, '#4E81FA', '#007bff', '#ffd400', '#000000', '#ffffff', '#808080', '#abc', '#e11d48',
        '#22c55e', '#f97316', '#8b5cf6', '#ff9900', '#00ff00', '#ffd400cc', 'not-a-colour'];

    private function schedule(?string $accent, string $name = 'The Blue Note'): Role
    {
        $role = new Role;
        $role->forceFill(['name' => $name, 'subdomain' => 'bluenote']);
        $role->accent_color = $accent;

        return $role;
    }

    private function assertReads(string $ink, string $surface, string $what): void
    {
        $ratio = ColorUtils::getContrastRatio($ink, $surface);

        $this->assertGreaterThanOrEqual(EmailTheme::AA, $ratio, sprintf('%s: %s on %s is %.2f:1', $what, $ink, $surface, $ratio));
    }

    public function test_every_accent_yields_colours_that_read_on_every_surface(): void
    {
        foreach (self::ACCENTS as $accent) {
            $t = EmailTheme::guest($this->schedule($accent));
            $label = 'accent '.var_export($accent, true);

            $this->assertReads($t->onAccent, $t->accent, "$label, button label");

            // Links sit on the card, the raised panel, the accent tint and inside callouts.
            foreach (['#ffffff', EmailTheme::PANEL, $t->accentTint, ...array_column(EmailTheme::TONES, 'bg')] as $surface) {
                $this->assertReads($t->accentInk, $surface, "$label, link/eyebrow (light)");
            }

            foreach ([EmailTheme::CARD_DARK, EmailTheme::PANEL_DARK, $t->darkTint, ...array_column(EmailTheme::TONES, 'dark_bg')] as $surface) {
                $this->assertReads($t->darkLink, $surface, "$label, link/eyebrow (dark)");
            }
        }
    }

    public function test_our_own_blue_buttons_use_the_admin_portals_darker_button_blue(): void
    {
        // White on #4E81FA is 3.59:1. #3D6FE8 is the AP's --brand-blue-dark, already in the product.
        foreach ([EmailTheme::account(), EmailTheme::guest(null), EmailTheme::guest($this->schedule('#4E81FA'))] as $t) {
            $this->assertSame('#3d6fe8', $t->accent);
            $this->assertSame('#ffffff', $t->onAccent);
        }
    }

    public function test_an_owners_accent_keeps_its_hue_rather_than_being_swapped_for_ours(): void
    {
        // Yellow takes dark ink rather than being darkened into brown, or replaced with our blue.
        $yellow = EmailTheme::guest($this->schedule('#ffd400'));
        $this->assertSame('#ffd400', $yellow->accent);
        $this->assertSame(EmailTheme::INK, $yellow->onAccent);

        // A white button gets an edge; a black one gets a ring on the dark card.
        $this->assertNotSame('#ffffff', EmailTheme::guest($this->schedule('#ffffff'))->accentBorder);
        $this->assertTrue(EmailTheme::guest($this->schedule('#000000'))->darkRing);
    }

    public function test_every_state_colour_reads_in_both_modes(): void
    {
        foreach (EmailTheme::TONES as $name => $c) {
            $this->assertReads($c['ink'], '#ffffff', "$name ink on the card");
            $this->assertReads($c['ink'], $c['bg'], "$name ink on its callout");
            $this->assertReads('#334155', $c['bg'], "$name callout body text");
            $this->assertReads($c['dark_ink'], EmailTheme::CARD_DARK, "$name ink on the dark card");
            $this->assertReads($c['dark_ink'], $c['dark_bg'], "$name ink on its dark callout");
            $this->assertReads('#cbd5e1', $c['dark_bg'], "$name dark callout body text");
        }
    }

    public function test_an_unknown_tone_reads_as_neutral(): void
    {
        $this->assertSame('neutral', EmailTheme::toneName('error'));
        $this->assertSame('neutral', EmailTheme::toneName(null));
        $this->assertSame('danger', EmailTheme::toneName('danger'));
        $this->assertSame(EmailTheme::TONES['neutral'], EmailTheme::account()->tone('error'));
    }

    public function test_hex_normalisation(): void
    {
        $this->assertSame('#aabbcc', EmailTheme::normalizeHex('#abc'));
        $this->assertSame('#aabbcc', EmailTheme::normalizeHex('#abcf'));
        $this->assertSame('#ffd400', EmailTheme::normalizeHex('#FFD400CC'));
        $this->assertSame('#123456', EmailTheme::normalizeHex(' 123456 '));
        $this->assertNull(EmailTheme::normalizeHex('red'));
        $this->assertNull(EmailTheme::normalizeHex('#12345'));
        $this->assertNull(EmailTheme::normalizeHex(null));
    }

    public function test_guest_mail_never_carries_the_platforms_name(): void
    {
        // docs/BRANDING_MATRIX.md rule 5: our name in a schedule's outgoing mail is undecided.
        $guest = EmailTheme::guest($this->schedule('#e11d48'));
        $this->assertSame('guest', $guest->voice);
        $this->assertSame('The Blue Note', $guest->senderName);
        $this->assertNull($guest->appName);

        // A guest mail with no schedule shows no sender at all, never ours in its place.
        $this->assertNull(EmailTheme::guest(null)->senderName);
        $this->assertNull(EmailTheme::guest(null)->appName);
    }

    public function test_owner_and_account_voices(): void
    {
        $owner = EmailTheme::owner($this->schedule('#e11d48'));
        $this->assertSame('The Blue Note', $owner->senderName);
        $this->assertSame(config('app.name'), $owner->appName);
        // The schedule identifies the mail; the colours stay the platform's.
        $this->assertSame('#3d6fe8', $owner->accent);

        $account = EmailTheme::account();
        $this->assertSame(config('app.name'), $account->senderName);
        $this->assertNull($account->avatarUrl);
    }

    public function test_direction_follows_the_locale_at_build_time(): void
    {
        app()->setLocale('he');
        $t = EmailTheme::account();
        $this->assertSame(['rtl', 'right', 'left', 'he'], [$t->dir, $t->start, $t->end, $t->lang]);

        app()->setLocale('en');
        $t = EmailTheme::account();
        $this->assertSame(['ltr', 'left', 'right', 'en'], [$t->dir, $t->start, $t->end, $t->lang]);
    }

    public function test_a_schedule_named_zero_still_has_a_sender(): void
    {
        $t = EmailTheme::guest($this->schedule(null, '0'));

        $this->assertSame('0', $t->senderName);
        $this->assertSame('0', $t->initial);
    }

    public function test_a_picture_with_no_recorded_size_is_never_forced_square(): void
    {
        $role = $this->schedule(null);
        $role->forceFill(['profile_image_url' => 'demo_profile_band.jpg', 'image_variants' => null]);
        $t = EmailTheme::guest($role);
        $this->assertNotNull($t->avatarUrl);
        $this->assertSame([0, 0, false], [$t->avatarWidth, $t->avatarHeight, $t->avatarWide]);

        $role->forceFill(['image_variants' => ['src' => ['w' => 800, 'h' => 200]]]);
        $wide = EmailTheme::guest($role);
        $this->assertTrue($wide->avatarWide);
        $this->assertSame([128, 32], [$wide->avatarWidth, $wide->avatarHeight]);
    }

    public function test_the_initial_is_the_first_letter_or_digit(): void
    {
        $this->assertSame('T', EmailTheme::guest($this->schedule(null, 'The Blue Note'))->initial);
        $this->assertSame('J', EmailTheme::guest($this->schedule(null, '@jazz'))->initial);
        $this->assertSame('B', EmailTheme::guest($this->schedule(null, '"Blue" Note'))->initial);
        $this->assertSame('ק', EmailTheme::guest($this->schedule(null, 'קפה'))->initial);
    }
}
