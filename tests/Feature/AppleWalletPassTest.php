<?php

namespace Tests\Feature;

use App\Mail\TicketPurchase;
use App\Mail\TicketReminder;
use App\Models\Event;
use App\Models\Role;
use App\Models\Sale;
use App\Services\Wallet\AppleWalletService;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * "Add to Apple Wallet": a signed .pkpass built on tap.
 *
 * Off until configured is the first property, and the one the owner of the hosted install asked
 * for: no badge, no email link, a 404 at the address. The signing is checked with a certificate
 * authority made here, standing in for Apple's WWDR intermediate.
 */
class AppleWalletPassTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private string $caPem;

    private string $p12;

    private string $pemBundle;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pinAppUrl('https://eventschedule.test');
        AppleWalletService::flush();

        // A CA standing in for Apple's WWDR, and a Pass Type ID certificate it signed. A config of
        // our own: the system's openssl.cnf differs between machines and CI.
        $cnf = tempnam(sys_get_temp_dir(), 'openssl-cnf');
        file_put_contents($cnf, "[req]\ndistinguished_name = dn\n[dn]\n[v3_ca]\nbasicConstraints = critical, CA:TRUE\nkeyUsage = critical, keyCertSign, cRLSign\n[v3_leaf]\nbasicConstraints = CA:FALSE\nkeyUsage = critical, digitalSignature\nextendedKeyUsage = emailProtection\n");
        $options = fn (string $section) => ['config' => $cnf, 'x509_extensions' => $section, 'digest_alg' => 'sha256'];
        $caKey = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA, 'config' => $cnf]);
        $ca = openssl_csr_sign(openssl_csr_new(['commonName' => 'Test WWDR'], $caKey, ['config' => $cnf]), null, $caKey, 30, $options('v3_ca'));
        $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA, 'config' => $cnf]);
        $cert = openssl_csr_sign(openssl_csr_new(['commonName' => 'Pass Type ID: pass.test.tickets'], $key, ['config' => $cnf]), $ca, $caKey, 30, $options('v3_leaf'));
        unlink($cnf);

        $caPem = '';
        openssl_x509_export($ca, $caPem);
        $this->caPem = $caPem;
        $p12 = '';
        openssl_pkcs12_export($cert, $p12, $key, 'secret');
        $this->p12 = $p12;
        $certPem = $keyPem = '';
        openssl_x509_export($cert, $certPem);
        openssl_pkey_export($key, $keyPem);
        $this->pemBundle = $certPem.$keyPem;
    }

    protected function tearDown(): void
    {
        AppleWalletService::flush();
        parent::tearDown();
    }

    private function configure(array $overrides = []): void
    {
        config(array_merge([
            'services.apple.wallet_pass_type_id' => 'pass.test.tickets',
            'services.apple.wallet_team_id' => 'TEAM123456',
            'services.apple.wallet_certificate' => base64_encode($this->p12),
            'services.apple.wallet_certificate_password' => 'secret',
            'services.apple.wallet_wwdr' => base64_encode($this->caPem),
        ], $overrides));
        AppleWalletService::flush();
    }

    /** @return array{0: Role, 1: Event, 2: Sale} */
    private function paidTicket(array $eventAttrs = [], array $saleAttrs = []): array
    {
        $role = $this->createRole($this->createOwner());
        $event = $this->createEvent($role, array_merge(['tickets_enabled' => true, 'name' => 'Jazz Night', 'creator_role_id' => $role->id], $eventAttrs));
        $ticket = $this->createTicket($event, ['type' => 'General Admission', 'price' => 10]);
        $sale = $this->createSale($event, $role, array_merge(['status' => 'paid', 'name' => 'Omar Haddad'], $saleAttrs), $ticket, 2);

        return [$role, $event, $sale];
    }

    private function passUrl($event, $sale): string
    {
        return route('ticket.wallet.apple', ['event_id' => UrlUtils::encodeId($event->id), 'secret' => $sale->secret]);
    }

    private function ticketUrl($event, $sale): string
    {
        return route('ticket.view', ['event_id' => UrlUtils::encodeId($event->id), 'secret' => $sale->secret]);
    }

    /** @return array<string, string> the files inside a .pkpass */
    private function unzip(string $bytes): array
    {
        $path = tempnam(sys_get_temp_dir(), 'pkpass-test');
        file_put_contents($path, $bytes);
        $zip = new \ZipArchive;
        $this->assertTrue($zip->open($path) === true, 'the pass is a zip');
        $files = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $files[$zip->getNameIndex($i)] = $zip->getFromIndex($i);
        }
        $zip->close();
        unlink($path);

        return $files;
    }

    public function test_the_feature_is_off_until_it_is_configured(): void
    {
        [$role, $event, $sale] = $this->paidTicket();

        $this->assertFalse(AppleWalletService::isConfigured());
        $this->get($this->passUrl($event, $sale))->assertNotFound();
        $this->get($this->ticketUrl($event, $sale))->assertOk()
            ->assertDontSee(__('messages.add_to_apple_wallet'))
            ->assertDontSee('/ticket/wallet/apple/', false);

        $content = (new TicketPurchase($sale, $event, $role))->content();
        $this->assertNull($content->with['appleWalletUrl']);
        $this->assertStringNotContainsString('/ticket/wallet/apple/', (new TicketPurchase($sale, $event, $role))->render());
    }

    public function test_a_partial_or_broken_credential_does_not_switch_it_on(): void
    {
        foreach ([
            ['services.apple.wallet_team_id' => '  '],
            ['services.apple.wallet_pass_type_id' => ''],
            ['services.apple.wallet_certificate' => ''],
            ['services.apple.wallet_certificate_password' => 'wrong'],
            ['services.apple.wallet_certificate' => base64_encode('not a certificate')],
            ['services.apple.wallet_certificate' => '/nonexistent/pass.p12'],
            ['services.apple.wallet_wwdr' => base64_encode('not a certificate')],
        ] as $broken) {
            $this->configure($broken);
            $this->assertFalse(AppleWalletService::isConfigured(), 'switched on by '.json_encode($broken));
        }

        // A PEM file holding the certificate and its key works as well as a .p12.
        $this->configure(['services.apple.wallet_certificate' => base64_encode($this->pemBundle), 'services.apple.wallet_certificate_password' => '']);
        $this->assertTrue(AppleWalletService::isConfigured());
    }

    public function test_a_paid_ticket_gets_a_signed_pass_that_scans_at_the_door(): void
    {
        $this->configure();
        [$role, $event, $sale] = $this->paidTicket(['starts_at' => now()->addWeek()->setTime(20, 0)->format('Y-m-d H:i:s')]);

        // The ticket page offers it (as text: Apple's artwork is the operator's to install).
        $this->get($this->ticketUrl($event, $sale))->assertOk()
            ->assertSee(__('messages.add_to_apple_wallet'))
            ->assertSee($this->passUrl($event, $sale), false);

        $response = $this->get($this->passUrl($event, $sale))->assertOk();
        $this->assertSame('application/vnd.apple.pkpass', $response->headers->get('Content-Type'));

        $files = $this->unzip($response->getContent());
        foreach (['pass.json', 'manifest.json', 'signature', 'icon.png', 'icon@2x.png', 'logo.png'] as $name) {
            $this->assertArrayHasKey($name, $files);
        }

        // Every file is in the manifest with its SHA-1, and nothing else is.
        $manifest = json_decode($files['manifest.json'], true);
        foreach ($files as $name => $bytes) {
            if (in_array($name, ['manifest.json', 'signature'], true)) {
                continue;
            }
            $this->assertSame(sha1($bytes), $manifest[$name] ?? null, $name.' hashed');
        }
        $this->assertCount(count($files) - 2, $manifest);

        // The signature is a detached CMS over the manifest, by the configured certificate, chained
        // to the WWDR stand-in.
        $dir = sys_get_temp_dir().'/pkpass-verify-'.bin2hex(random_bytes(4));
        mkdir($dir);
        file_put_contents($dir.'/manifest.json', $files['manifest.json']);
        file_put_contents($dir.'/signature', $files['signature']);
        file_put_contents($dir.'/ca.pem', $this->caPem);
        // A detached signature: the CONTENT is the input file and the signature is sigfile (the
        // `content` argument is an output, which would overwrite the manifest).
        $verify = fn () => openssl_cms_verify($dir.'/manifest.json', OPENSSL_CMS_BINARY | OPENSSL_CMS_DETACHED, null, [$dir.'/ca.pem'], null, null, null, $dir.'/signature', OPENSSL_ENCODING_DER);
        $verified = $verify();
        file_put_contents($dir.'/manifest.json', $files['manifest.json'].' ');
        $tampered = $verify();
        array_map('unlink', glob($dir.'/*'));
        rmdir($dir);
        $this->assertTrue($verified, 'the manifest signature verifies against the WWDR chain');
        $this->assertFalse($tampered, 'and covers the manifest: a changed byte fails it');

        $pass = json_decode($files['pass.json'], true);
        $this->assertSame('pass.test.tickets', $pass['passTypeIdentifier']);
        $this->assertSame('TEAM123456', $pass['teamIdentifier']);
        $this->assertSame('Jazz Night', $pass['description']);
        $this->assertSame('PKBarcodeFormatQR', $pass['barcodes'][0]['format']);
        // Byte-identical to the on-page QR: the door scanner reads this.
        $this->assertSame(canonical_url(route('ticket.view', ['event_id' => UrlUtils::encodeId($event->id), 'secret' => $sale->secret], false)), $pass['barcodes'][0]['message']);
        $this->assertMatchesRegularExpression('#/ticket/view/([^/]+)/([^/]+)/?$#', $pass['barcodes'][0]['message']);
        $this->assertTrue($pass['eventTicket']['secondaryFields'][0]['ignoresTimeZone'], 'the event\'s own clock, wherever the phone is');
        $this->assertArrayHasKey('relevantDate', $pass);
        $this->assertArrayHasKey('expirationDate', $pass);
        $values = collect($pass['eventTicket']['auxiliaryFields'])->pluck('value', 'key');
        $this->assertSame('General Admission', $values['type']);
        $this->assertSame('Omar Haddad', $values['holder']);
        $this->assertSame('2', $values['admits']);
    }

    public function test_a_ticket_that_may_not_be_a_pass_goes_back_to_its_ticket(): void
    {
        $this->configure();

        [, $event, $sale] = $this->paidTicket([], ['status' => 'unpaid']);
        $this->get($this->passUrl($event, $sale))->assertRedirect($this->ticketUrl($event, $sale));

        [, $event, $sale] = $this->paidTicket(['is_cancelled' => true]);
        $this->get($this->passUrl($event, $sale))->assertRedirect($this->ticketUrl($event, $sale));
    }

    public function test_the_confirmation_email_carries_the_link_when_it_is_on(): void
    {
        $this->configure();
        [$role, $event, $sale] = $this->paidTicket();

        $content = (new TicketPurchase($sale, $event, $role))->content();
        $url = canonical_url(route('ticket.wallet.apple', ['event_id' => UrlUtils::encodeId($event->id), 'secret' => $sale->secret], false));
        $this->assertSame($url, $content->with['appleWalletUrl']);
        $this->assertNull($content->with['appleWalletBadge'], 'no artwork installed, so a text link');

        $this->assertStringContainsString($url, view('emails.ticket_purchase_text', $content->with)->render());
        $rendered = (new TicketPurchase($sale, $event, $role))->render();
        $this->assertStringContainsString(e($url), $rendered);
        $this->assertStringContainsString(__('messages.add_to_apple_wallet'), $rendered);
    }

    public function test_the_ticket_page_puts_the_wallets_under_the_code_and_only_for_a_valid_ticket(): void
    {
        $this->configure();
        [, $event, $sale] = $this->paidTicket();

        $html = $this->get($this->ticketUrl($event, $sale))->assertOk()->getContent();
        $code = strpos($html, 'class="gk-tk-qr"');
        $row = strpos($html, 'gk-tk-wallets gk-tk-noprint');
        $details = strpos($html, 'class="gk-ticket-b"');
        $this->assertNotFalse($row);
        $this->assertTrue($code < $row && $row < $details, 'the row sits under the QR code, above the details');

        [, $event, $sale] = $this->paidTicket([], ['status' => 'unpaid']);
        $this->get($this->ticketUrl($event, $sale))->assertOk()
            ->assertDontSee('gk-tk-wallets gk-tk-noprint', false)
            ->assertDontSee($this->passUrl($event, $sale), false);
    }

    public function test_the_order_page_offers_a_pass_per_live_leg(): void
    {
        $this->configure();
        $role = $this->createRole($this->createOwner(), 'talent');
        $primary = null;
        $legs = [];
        foreach (['First Night', 'Second Night', 'Cancelled Night'] as $name) {
            $event = $this->createEvent($role, ['tickets_enabled' => true, 'name' => $name]);
            $ticket = $this->createTicket($event, ['price' => 10]);
            $sale = $this->createSale($event, $role, ['status' => 'paid'], $ticket, 1);
            $sale->order_id = $primary ? $primary->id : $sale->id;
            $primary ??= $sale;
            $sale->saveQuietly();
            $legs[] = [$event, $sale];
        }
        [$deadEvent, $deadSale] = $legs[2];
        $deadSale->forceFill(['status' => 'cancelled'])->saveQuietly();

        $html = $this->get(route('ticket.order', ['order_id' => UrlUtils::encodeId($primary->id), 'secret' => $primary->secret]))->assertOk()->getContent();

        $this->assertStringContainsString($this->passUrl($legs[0][0], $legs[0][1]), $html);
        $this->assertStringContainsString($this->passUrl($legs[1][0], $legs[1][1]), $html);
        $this->assertStringNotContainsString($this->passUrl($deadEvent, $deadSale), $html);
    }

    public function test_the_reminder_carries_the_link_and_the_preview_does_not(): void
    {
        $this->configure();
        [$role, $event, $sale] = $this->paidTicket();

        $this->assertNotNull((new TicketReminder($sale, $event, $role))->content()->with['appleWalletUrl']);
        $this->assertNull((new TicketReminder($sale, $event, $role, preview: true))->content()->with['appleWalletUrl']);
    }

    /** A picture GD cannot read (an SVG) must not cost the pass its icon, which Wallet requires. */
    public function test_an_unreadable_schedule_picture_still_gets_an_icon(): void
    {
        $this->configure();
        [$role, $event, $sale] = $this->paidTicket();
        Storage::fake(config('filesystems.default'));
        Storage::disk(config('filesystems.default'))->put('public/profile_vector.svg', '<svg xmlns="http://www.w3.org/2000/svg"/>');
        $role->forceFill(['profile_image_url' => 'profile_vector.svg'])->saveQuietly();

        $files = $this->unzip(app(AppleWalletService::class)->pass($sale->fresh(), $event->fresh(), $role->fresh()));

        $this->assertArrayHasKey('icon.png', $files);
        $this->assertNotFalse(@imagecreatefromstring($files['icon.png']));
    }

    /** An all-day start is a day, never a midnight carried between zones, and expires on no day of its own. */
    public function test_an_all_day_event_is_dated_by_its_day(): void
    {
        $this->configure();
        [$role, $event, $sale] = $this->paidTicket();
        $event->setRawAttributes(array_merge($event->getAttributes(), ['starts_at' => '2026-12-01']));
        $sale->event_date = '2026-12-01';

        $pass = app(AppleWalletService::class)->passJson($sale, $event, $role);
        $date = collect($pass['eventTicket']['secondaryFields'])->firstWhere('key', 'date');

        $this->assertSame('2026-12-01T00:00:00Z', $date['value']);
        $this->assertSame('PKDateStyleNone', $date['timeStyle']);
        $this->assertArrayNotHasKey('expirationDate', $pass);
    }

    public function test_an_expired_certificate_is_not_configured(): void
    {
        $cnf = tempnam(sys_get_temp_dir(), 'openssl-cnf');
        file_put_contents($cnf, "[req]\ndistinguished_name = dn\n[dn]\n");
        $key = openssl_pkey_new(['private_key_bits' => 2048, 'config' => $cnf]);
        // Valid for no days at all: it ends the second it is made, so a second later it has expired.
        $cert = openssl_csr_sign(openssl_csr_new(['commonName' => 'expired'], $key, ['config' => $cnf]), null, $key, 0, ['config' => $cnf, 'digest_alg' => 'sha256']);
        unlink($cnf);
        sleep(1);
        $p12 = '';
        openssl_pkcs12_export($cert, $p12, $key, 'secret');

        $this->configure(['services.apple.wallet_certificate' => base64_encode($p12)]);

        $this->assertFalse(AppleWalletService::isConfigured());
    }
}
