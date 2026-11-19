<?php

namespace Tests\Browser;

use App\Models\Event;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\User;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * An embedded calendar that shows its events on the organizer's own website (the Embed dialog's
 * "Show it on your website", ?events=onsite), driven from a host page on ANOTHER origin, which is
 * the only way to see what a customer's site sees: no session in the frame, postMessage across
 * origins, focus that cannot cross into a frame on its own.
 *
 * The app is served at its APP_URL, 127.0.0.1:8000 in .env.dusk.local; the host pages are written into
 * public/ for the length of a test and opened as localhost on the same port, a different origin and
 * a different site.
 *
 *  - plain: embed.js beside the frame. A click opens the event in the overlay, Escape inside it
 *    closes it, the focus comes back to the calendar, and Get tickets opens the event's page in a
 *    new tab with the form open.
 *  - wrapped: the code inside a frame of the builder's own on ANOTHER origin, as Wix does. The
 *    script stands down, the event opens inside the calendar's frame, and Back returns to it.
 *  - reached: the code inside a srcdoc frame of the builder's own, on the site's origin. The
 *    script draws the overlay on the page above.
 *  - header: the builder's frame is on another origin and the site's header code carries the
 *    script too: the overlay opens over the whole page from the calendar it cannot see into.
 */
class EmbedEventJourneyTest extends DuskTestCase
{
    use DatabaseTruncation;

    /** The app, as .env.dusk.local serves it (http://127.0.0.1:8000). */
    private function app(): string
    {
        return rtrim(config('app.url'), '/');
    }

    /** The same server under another name: another origin, and another site. */
    private function host(): string
    {
        return str_replace('127.0.0.1', 'localhost', $this->app());
    }

    /** @var list<string> */
    private array $written = [];

    protected function tearDown(): void
    {
        foreach ($this->written as $path) {
            @unlink($path);
        }

        parent::tearDown();
    }

    private function schedule(): array
    {
        $owner = User::factory()->create(['name' => 'Marge Simpson', 'email' => 'marge@gmail.com', 'email_verified_at' => now()]);

        $role = new Role;
        $role->subdomain = 'harbourhall';
        $role->user_id = $owner->id;
        $role->type = 'venue';
        $role->name = 'Harbour Hall';
        $role->email = 'harbourhall@gmail.com';
        $role->timezone = 'America/New_York';
        $role->email_verified_at = now();
        $role->plan_type = 'enterprise';
        $role->plan_expires = now()->addYear()->format('Y-m-d');
        $role->event_layout = 'list';
        $role->save();
        $role->users()->attach($owner->id, ['level' => 'owner']);

        $event = new Event;
        $event->user_id = $owner->id;
        $event->creator_role_id = $role->id;
        $event->name = 'Lantern Night';
        $event->slug = Str::slug($event->name).'-'.strtolower(Str::random(4));
        $event->starts_at = Carbon::now('America/New_York')->addDays(3)->setTime(20, 0)->setTimezone('UTC')->format('Y-m-d H:i:s');
        $event->duration = 2;
        $event->tickets_enabled = true;
        $event->ticket_currency_code = 'USD';
        $event->save();
        $event->roles()->attach($role->id, ['is_accepted' => true]);

        $ticket = new Ticket;
        $ticket->event_id = $event->id;
        $ticket->type = 'Balcony';
        $ticket->quantity = 50;
        $ticket->price = 0;
        $ticket->save();

        return [$role->fresh(), $event->fresh()];
    }

    /** A page in public/, removed after the test; its address on the host origin. */
    private function hostPage(string $name, string $html): string
    {
        $file = 'dusk-embed-'.$name.'-'.Str::random(6).'.html';
        $path = public_path($file);
        file_put_contents($path, $html);
        $this->written[] = $path;

        return $this->host().'/'.$file;
    }

    private function calendarCode(Role $role): string
    {
        return '<iframe id="cal" src="'.$this->app().'/'.$role->subdomain.'?embed=true&events=onsite&layout=list" title="Harbour Hall events" width="100%" height="800" frameborder="0" style="border: none;" allow="clipboard-write; web-share; fullscreen"></iframe>'
            ."\n".'<script src="'.$this->app().'/embed.js" async></script>';
    }

    public function test_an_event_opens_over_the_host_page_and_closes_back_to_the_calendar(): void
    {
        [$role] = $this->schedule();

        $host = $this->hostPage('plain', '<!doctype html><html><head><meta charset="utf-8"><title>Host</title></head><body>'
            .'<h1>Our website</h1>'
            .'<script>window.opened = []; window.addEventListener("eventschedule:event-opened", function (e) { window.opened.push(e.detail.url); });'
            .'window.checkouts = []; window.addEventListener("eventschedule:checkout", function (e) { window.checkouts.push(e.detail.url); });</script>'
            .$this->calendarCode($role)
            .'</body></html>');

        $this->browse(function (Browser $browser) use ($host) {
            $browser->visit($host)->waitFor('#cal');

            // The frame was fitted to the calendar once the script greeted it.
            $browser->waitUntil('document.getElementById("cal").style.height !== ""', 15);

            $browser->withinFrame('#cal', function (Browser $frame) {
                $frame->waitForText('Lantern Night', 15)->clickLink('Lantern Night');
            });

            $browser->waitFor('dialog[data-eventschedule-overlay]', 10)
                ->assertScript('location.hash.indexOf("#es-event=") === 0')
                ->assertScript('window.opened.length === 1')
                ->assertScript('document.documentElement.style.overflow === "hidden"');

            $browser->withinFrame('dialog[data-eventschedule-overlay] iframe', function (Browser $event) {
                $event->waitFor('[data-embed-heading]', 15)
                    ->assertSee('Lantern Night')
                    ->assertSee('Balcony')
                    ->assertMissing('#gp-event-form');
                // The heading took the focus when the host greeted the overlay.
                $event->waitUntil('document.activeElement && document.activeElement.hasAttribute("data-embed-heading")', 10);
                // Escape never reaches the host page from inside a frame: the page asks to close.
                $event->keys('[data-embed-heading]', '{escape}');
            });

            $browser->waitUntilMissing('dialog[data-eventschedule-overlay]', 10)
                ->assertScript('location.hash === ""')
                ->assertScript('document.activeElement && document.activeElement.id === "cal"')
                ->assertScript('document.documentElement.style.overflow === ""');
        });
    }

    public function test_get_tickets_opens_the_events_page_in_a_new_tab_with_the_form_open(): void
    {
        [$role] = $this->schedule();

        $host = $this->hostPage('tickets', '<!doctype html><html><head><meta charset="utf-8"><title>Host</title></head><body>'
            .'<script>window.checkouts = []; window.addEventListener("eventschedule:checkout", function (e) { window.checkouts.push(e.detail.url); });</script>'
            .$this->calendarCode($role)
            .'</body></html>');

        $this->browse(function (Browser $browser) use ($host) {
            $browser->visit($host)->waitFor('#cal');
            $browser->withinFrame('#cal', function (Browser $frame) {
                $frame->waitForText('Lantern Night', 15)->clickLink('Lantern Night');
            });
            $browser->waitFor('dialog[data-eventschedule-overlay]', 10);

            $before = count($browser->driver->getWindowHandles());
            $browser->withinFrame('dialog[data-eventschedule-overlay] iframe', function (Browser $event) {
                $event->waitFor('[data-embed-checkout="tickets"]', 15)->click('[data-embed-checkout="tickets"]');
            });
            $browser->waitUntil('window.checkouts.length === 1', 10);

            $handles = $browser->driver->getWindowHandles();
            $this->assertCount($before + 1, $handles);
            $browser->driver->switchTo()->window(end($handles));
            $browser->waitFor('#gp-event-form', 15);
            $this->assertStringContainsString('tickets=true', $browser->driver->getCurrentURL());
            $this->assertStringNotContainsString('embed=true', $browser->driver->getCurrentURL());
        });
    }

    public function test_the_ticket_widget_fits_its_frame_and_buys_in_a_new_tab(): void
    {
        [$role, $event] = $this->schedule();

        $host = $this->hostPage('widget', '<!doctype html><html><head><meta charset="utf-8"><title>Host</title></head><body>'
            .'<script>window.checkouts = []; window.addEventListener("eventschedule:checkout", function (e) { window.checkouts.push(e.detail.url); });</script>'
            .'<iframe id="widget" src="'.$this->app().'/'.$role->subdomain.'/'.$event->slug.'/'.UrlUtils::encodeId($event->id).'?tickets=true&embed=true" title="Lantern Night" width="100%" height="420" frameborder="0" style="border: none;"></iframe>'
            ."\n".'<script src="'.$this->app().'/embed.js" async></script>'
            .'</body></html>');

        $this->browse(function (Browser $browser) use ($host) {
            $browser->visit($host)->waitFor('#widget');

            // The widget announced itself and the frame was fitted to it, smaller than the fallback.
            $browser->waitUntil('document.getElementById("widget").style.height !== ""', 15)
                ->assertScript('parseInt(document.getElementById("widget").style.height, 10) < 420');

            $before = count($browser->driver->getWindowHandles());
            $browser->withinFrame('#widget', function (Browser $frame) {
                $frame->waitFor('[data-embed-checkout="tickets"]', 15)
                    ->assertSee('Balcony')
                    ->click('[data-embed-checkout="tickets"]');
            });
            $browser->waitUntil('window.checkouts.length === 1', 10);

            $handles = $browser->driver->getWindowHandles();
            $this->assertCount($before + 1, $handles);
            $browser->driver->switchTo()->window(end($handles));
            $browser->waitFor('#gp-event-form', 15);
            $this->assertStringContainsString('tickets=true', $browser->driver->getCurrentURL());
        });
    }

    public function test_inside_a_builders_own_frame_the_event_opens_in_the_calendar_and_back_returns(): void
    {
        [$role] = $this->schedule();

        // What a builder that wraps pasted code does: the code, script and all, in a frame of its
        // own, on another origin than the page (here the app's name for the same server).
        $inner = str_replace($this->host(), $this->app(), $this->hostPage('wrapped-inner', '<!doctype html><html><head><meta charset="utf-8"></head><body>'.$this->calendarCode($role).'</body></html>'));
        $host = $this->hostPage('wrapped', '<!doctype html><html><head><meta charset="utf-8"><title>Host</title></head><body>'
            .'<iframe id="builder" src="'.$inner.'" width="100%" height="900" style="border:0"></iframe>'
            .'</body></html>');

        $this->browse(function (Browser $browser) use ($host) {
            $browser->visit($host)->waitFor('#builder');

            $browser->withinFrame('#builder', function (Browser $builder) {
                $builder->waitFor('#cal')
                    ->assertScript('window.EventScheduleEmbed && window.EventScheduleEmbed.wrapped === true');

                $builder->withinFrame('#cal', function (Browser $frame) {
                    $frame->waitForText('Lantern Night', 15)->clickLink('Lantern Night');
                    $frame->waitFor('[data-embed-back]', 15)
                        ->assertSee('Balcony')
                        ->assertScript('location.search.indexOf("view=event") !== -1');

                    $frame->click('[data-embed-back]');
                    $frame->waitUntil('location.search.indexOf("view=event") === -1', 15)
                        ->waitForText('Lantern Night', 15)
                        ->assertScript('location.search.indexOf("events=onsite") !== -1');
                });
            });

            $browser->assertMissing('dialog[data-eventschedule-overlay]');
        });
    }

    /** The overlay over the host page, opened from the calendar, closed with its own button. */
    private function opensOverTheWholePage(Browser $browser, callable $clickInCalendar): void
    {
        $clickInCalendar($browser);

        $browser->waitFor('dialog[data-eventschedule-overlay]', 15)
            ->assertScript('location.hash.indexOf("#es-event=") === 0')
            ->assertScript('document.documentElement.style.overflow === "hidden"');

        $browser->withinFrame('dialog[data-eventschedule-overlay] iframe', function (Browser $event) {
            $event->waitFor('[data-embed-heading]', 15)
                ->assertSee('Lantern Night')
                ->assertSee('Balcony');
            $event->keys('[data-embed-heading]', '{escape}');
        });

        $browser->waitUntilMissing('dialog[data-eventschedule-overlay]', 10)
            ->assertScript('location.hash === ""')
            ->assertScript('document.documentElement.style.overflow === ""');
    }

    public function test_inside_a_builders_frame_on_the_sites_origin_the_event_opens_over_the_page(): void
    {
        [$role] = $this->schedule();

        // A srcdoc frame, as a builder that isolates pasted code that way draws it: same origin
        // as the page, so the script reaches the page above.
        $host = $this->hostPage('reached', '<!doctype html><html><head><meta charset="utf-8"><title>Host</title></head><body>'
            .'<h1>Our website</h1>'
            .'<iframe id="builder" srcdoc="'.e('<!doctype html><html><head><meta charset="utf-8"></head><body>'.$this->calendarCode($role).'</body></html>').'" width="420" height="900" style="border:0"></iframe>'
            .'</body></html>');

        $this->browse(function (Browser $browser) use ($host) {
            $browser->visit($host)->waitFor('#builder');
            $browser->waitUntil('window.EventScheduleEmbed && window.EventScheduleEmbed.reached === true', 15);

            $this->opensOverTheWholePage($browser, function (Browser $browser) {
                $browser->withinFrame('#builder', function (Browser $builder) {
                    $builder->withinFrame('#cal', function (Browser $frame) {
                        $frame->waitForText('Lantern Night', 15)->clickLink('Lantern Night');
                    });
                });
            });

            // The calendar did not navigate: it is still the list.
            $browser->withinFrame('#builder', function (Browser $builder) {
                $builder->withinFrame('#cal', function (Browser $frame) {
                    $frame->assertScript('location.search.indexOf("view=event") === -1');
                });
            });
        });
    }

    public function test_with_the_script_in_the_sites_header_the_event_opens_over_the_page_from_a_builders_frame(): void
    {
        [$role] = $this->schedule();

        // The builder's frame is on another origin (the app's name for the server); the site's
        // header code carries the script.
        $inner = str_replace($this->host(), $this->app(), $this->hostPage('header-inner', '<!doctype html><html><head><meta charset="utf-8"></head><body>'.$this->calendarCode($role).'</body></html>'));
        $host = $this->hostPage('header', '<!doctype html><html><head><meta charset="utf-8"><title>Host</title>'
            .'<script src="'.$this->app().'/embed.js" async></script></head><body>'
            .'<h1>Our website</h1>'
            .'<iframe id="builder" src="'.$inner.'" width="420" height="900" style="border:0"></iframe>'
            .'</body></html>');

        $this->browse(function (Browser $browser) use ($host) {
            $browser->visit($host)->waitFor('#builder');

            $this->opensOverTheWholePage($browser, function (Browser $browser) {
                $browser->withinFrame('#builder', function (Browser $builder) {
                    $builder->waitFor('#cal')
                        ->assertScript('window.EventScheduleEmbed && window.EventScheduleEmbed.wrapped === true && window.EventScheduleEmbed.reached === false');
                    $builder->withinFrame('#cal', function (Browser $frame) {
                        $frame->waitUntil('window.esEmbedFrame && window.esEmbedFrame.greeted', 15)
                            ->waitForText('Lantern Night', 15)->clickLink('Lantern Night');
                    });
                });
            });
        });
    }
}
