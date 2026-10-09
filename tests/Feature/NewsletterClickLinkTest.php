<?php

namespace Tests\Feature;

use App\Models\Newsletter;
use App\Models\NewsletterRecipient;
use App\Models\Role;
use App\Services\NewsletterService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * A newsletter's click link leads where the mail said, and nowhere else.
 *
 * The link is /nl/c/{recipient's token}/{signature}/{the address}. Without the signature it went
 * wherever its last segment said, for anybody holding any recipient's token, under this install's
 * own name.
 *
 *   - the link the mail carries is signed for its own address;
 *   - mail already sent carries no signature, and keeps working: such a link is followed to this
 *     install's own pages and to an address written in that newsletter;
 *   - anything else goes to the schedule that sent the mail, and counts no click.
 */
class NewsletterClickLinkTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** @return array{0: Role, 1: Newsletter, 2: NewsletterRecipient} */
    private function sentNewsletter(array $blocks = []): array
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $newsletter = Newsletter::create(['role_id' => $role->id, 'user_id' => $role->user_id, 'type' => 'schedule', 'subject' => 'S', 'status' => 'sent', 'template' => 'modern', 'blocks' => $blocks]);
        $recipient = NewsletterRecipient::create(['newsletter_id' => $newsletter->id, 'email' => 'reader@gmail.com', 'name' => 'Reader', 'token' => Str::random(64), 'status' => 'sent']);

        return [$role, $newsletter, $recipient];
    }

    private function encoded(string $url): string
    {
        return rtrim(strtr(base64_encode($url), '+/', '-_'), '=');
    }

    public function test_a_click_link_does_not_lead_to_an_address_the_newsletter_never_held(): void
    {
        [, , $recipient] = $this->sentNewsletter();

        $response = $this->get('/nl/c/'.$recipient->token.'/'.$this->encoded('https://somewhere-else.example.net/landing'));

        $this->assertStringNotContainsString('somewhere-else.example.net', (string) $response->headers->get('Location'));
    }

    public function test_the_link_the_mail_carries_still_leads_where_it_says(): void
    {
        [, , $recipient] = $this->sentNewsletter();
        $html = app(NewsletterService::class)->rewriteLinks('<a href="https://partner.example.org/menu?a=1&amp;b=2">menu</a>', $recipient);
        preg_match('/href="([^"]+)"/', $html, $m);

        $response = $this->get(html_entity_decode($m[1]));

        $response->assertRedirect();
        $this->assertStringStartsWith('https://partner.example.org/menu?a=1&b=2', (string) $response->headers->get('Location'));
        $this->assertNotNull($recipient->fresh()->clicked_at);
    }

    public function test_links_in_mail_already_sent_still_lead_where_they_said(): void
    {
        [$role, , $recipient] = $this->sentNewsletter([
            ['id' => 'b1', 'type' => 'button', 'data' => ['text' => 'Menu', 'url' => 'partner.example.org/menu?a=1&b=2']],
            ['id' => 't1', 'type' => 'text', 'data' => ['content' => 'See [the map](https://maps.example.org/place/1).']],
        ]);

        foreach (['https://partner.example.org/menu?a=1&b=2', 'https://maps.example.org/place/1', $role->getGuestUrl()] as $address) {
            $location = (string) $this->get('/nl/c/'.$recipient->token.'/'.$this->encoded($address))->headers->get('Location');
            $this->assertStringStartsWith(explode('?', $address)[0], $location, $address);
        }
    }

    public function test_an_address_the_mail_never_held_leads_to_the_schedule_and_counts_no_click(): void
    {
        [$role, , $recipient] = $this->sentNewsletter();
        $address = 'https://somewhere-else.example.net/landing';

        $unsigned = $this->get('/nl/c/'.$recipient->token.'/'.$this->encoded($address));
        $unsigned->assertRedirect($role->getGuestUrl());

        $wrong = $this->get('/nl/c/'.$recipient->token.'/'.str_repeat('0', 32).'/'.$this->encoded($address));
        $wrong->assertRedirect($role->getGuestUrl());

        $this->assertNull($recipient->fresh()->clicked_at);
    }

    public function test_an_unsigned_link_is_not_followed_to_the_tail_of_a_host_the_newsletter_named(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $newsletter = Newsletter::create(['role_id' => $role->id, 'user_id' => $role->user_id, 'type' => 'schedule', 'subject' => 'S', 'status' => 'sent', 'template' => 'modern',
            'blocks' => [['id' => 'b1', 'type' => 'button', 'data' => ['text' => 'Menu', 'url' => 'https://www.partner-site.example.org/menu']]]]);
        $recipient = NewsletterRecipient::create(['newsletter_id' => $newsletter->id, 'email' => 'reader@gmail.com', 'name' => 'Reader', 'token' => Str::random(64), 'status' => 'sent']);
        $encode = fn (string $url) => rtrim(strtr(base64_encode($url), '+/', '-_'), '=');

        $tail = (string) $this->get('/nl/c/'.$recipient->token.'/'.$encode('https://site.example.org/menu'))->headers->get('Location');
        $this->assertStringNotContainsString('//site.example.org', $tail);

        $whole = (string) $this->get('/nl/c/'.$recipient->token.'/'.$encode('https://www.partner-site.example.org/menu'))->headers->get('Location');
        $this->assertStringStartsWith('https://www.partner-site.example.org/menu', $whole, 'control: the address as written still works');
    }
}
