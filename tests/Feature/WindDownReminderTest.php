<?php

namespace Tests\Feature;

use App\Mail\SubscriptionTrialEnding;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The reminder app:wind-down-comped-plans leaves behind.
 *
 * It reuses SubscriptionTrialEnding, whose copy was written for a real Stripe trial - and both
 * of that mailable's variants are false here. There is no subscription, so nothing will be
 * charged to a card on file, and "add a payment method" is advice that achieves nothing: the
 * owner has to start a subscription, so following it lets the plan lapse anyway.
 */
class WindDownReminderTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.hosted' => true]);
        Mail::fake();
    }

    /** A comped schedule the wind-down has put on a dated trial, due for its reminder. */
    private function windingDown(array $attrs = []): Role
    {
        $role = $this->createRole($this->createOwner(), 'venue');

        $role->forceFill(array_merge([
            'plan_type' => 'pro',
            'plan_source' => 'admin',
            'trial_ends_at' => now()->addDays(14)->startOfDay()->addHours(9),
            'plan_expires' => now()->addDays(14)->format('Y-m-d'),
        ], $attrs))->save();

        return $role->fresh();
    }

    private function remind(): void
    {
        $this->artisan('app:send-subscription-reminders')->assertExitCode(0);
    }

    /**
     * The memos are process-lifetime statics, and RefreshDatabase does not reset them. Without
     * this, a failed assertion in the ZAR test below would leave every later test in the
     * process rendering prices in rand - and the amount memo would do the same with the price.
     * TestCase::setUp() flushes both as well; this keeps the guarantee local to the file that
     * needs it.
     */
    protected function tearDown(): void
    {
        \App\Utils\PlatformCurrency::flush();
        \App\Utils\PlatformPricing::flush();

        parent::tearDown();
    }

    /**
     * Queued, and in the recipient's own language. It used to be Mail::to($address)->send(),
     * which is two bugs: on hosted this command runs inside a web request
     * (AppController::translateData) and the wind-down gives the whole addressable cohort dates
     * in the same window, so one run tried to deliver all of them synchronously; and a bare
     * address renders in the CLI locale, so the he and ro subscription_winddown_* strings that
     * shipped with this feature could never be reached.
     */
    public function test_the_reminder_is_queued_in_the_recipients_language(): void
    {
        \Illuminate\Support\Facades\Queue::fake();

        $role = $this->windingDown();
        $role->user->forceFill(['language_code' => 'he'])->save();

        $this->remind();

        \Illuminate\Support\Facades\Queue::assertPushed(
            \App\Jobs\SendQueuedEmail::class,
            function ($job) use ($role) {
                $read = function (string $property) use ($job) {
                    $ref = new \ReflectionProperty($job, $property);
                    $ref->setAccessible(true);

                    return $ref->getValue($job);
                };

                return $read('recipient') === $role->user->email
                    && $read('locale') === 'he'
                    && $read('mailable') instanceof SubscriptionTrialEnding;
            }
        );
    }

    /**
     * The stamp is claimed with a conditional UPDATE before the send, so a second runner cannot
     * read the same row and mail the owner twice. The two schedulers hold different mutexes, so
     * a read-then-write genuinely can overlap.
     */
    public function test_the_window_is_claimed_before_sending(): void
    {
        $role = $this->windingDown();
        $this->assertNull($role->winddown_reminder_sent_at);

        $this->remind();

        $this->assertNotNull(
            $role->fresh()->winddown_reminder_sent_at,
            'the claim must be written, or a concurrent run re-sends'
        );

        // Re-running inside the window is refused by the claim, not by a re-read.
        Mail::fake();
        $this->remind();
        Mail::assertNothingSent();
    }

    public function test_the_amount_carries_its_currency_symbol(): void
    {
        config(['services.stripe_platform.price_monthly_amount' => '9']);

        $this->windingDown();
        $this->remind();

        Mail::assertSent(SubscriptionTrialEnding::class, function ($mail) {
            $amount = new \ReflectionProperty($mail, 'amount');
            $amount->setAccessible(true);

            // A bare int coerces to "9" and the copy renders it verbatim: "will be charged 9."
            return $amount->getValue($mail) === '$9';
        });
    }

    /**
     * And the symbol follows the installation's currency, rather than a hardcoded dollar.
     *
     * The USD case above passes either way, since plan_price(9) is still "$9" - it cannot tell
     * you whether the wiring works. This one can.
     */
    public function test_the_amount_uses_the_platform_currency(): void
    {
        config(['services.stripe_platform.price_monthly_amount' => '9']);
        \App\Models\Setting::set('platform_currency', 'ZAR');
        \App\Utils\PlatformCurrency::flush();

        $this->windingDown();
        $this->remind();

        Mail::assertSent(SubscriptionTrialEnding::class, function ($mail) {
            $amount = new \ReflectionProperty($mail, 'amount');
            $amount->setAccessible(true);

            return $amount->getValue($mail) === 'R9';
        });
    }

    /** The copy has to say "start a subscription", because that is the only thing that works. */
    public function test_the_reminder_uses_the_wind_down_copy(): void
    {
        $this->windingDown();
        $this->remind();

        Mail::assertSent(SubscriptionTrialEnding::class, function ($mail) {
            $content = $mail->content();

            return ($content->with['windDown'] ?? false) === true
                && ($content->with['hasCard'] ?? null) === false;
        });
    }

    /**
     * trial_reminder_sent_at is read by the Stripe trial path with NO time window - any value at
     * all means "already sent, forever" - so stamping it here permanently suppressed the genuine
     * "your trial ends tomorrow" email for every schedule this wound down.
     */
    public function test_it_does_not_consume_the_stripe_trial_reminder_flag(): void
    {
        $role = $this->windingDown();

        $this->remind();

        $role->refresh();
        $this->assertNotNull($role->winddown_reminder_sent_at, 'the wind-down stamps its own column');
        $this->assertNull($role->trial_reminder_sent_at, 'and must leave the Stripe trial flag alone');
    }

    public function test_a_schedule_is_not_reminded_twice_in_the_same_window(): void
    {
        $this->windingDown();

        $this->remind();
        $this->remind();

        Mail::assertSent(SubscriptionTrialEnding::class, 1);
    }
}
