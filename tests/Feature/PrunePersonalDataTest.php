<?php

namespace Tests\Feature;

use App\Http\Controllers\AppController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * app:prune-personal-data deletes what is past its retention period and nothing else
 * (App\Console\Commands\PrunePersonalData).
 */
class PrunePersonalDataTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /**
     * An event with no date stores event_date = '' (EventInterestController::resolveDate()), and ''
     * sorts before every date: the first version of the prune emptied those lists every day.
     */
    public function test_lists_of_undated_events_are_kept_and_those_of_past_events_go(): void
    {
        $event = $this->createEvent($this->createRole($this->createOwner()));
        $past = now()->subDays(31)->format('Y-m-d');
        $future = now()->addDays(3)->format('Y-m-d');

        foreach (['' => 'undated@example.com', $past => 'past@example.com', $future => 'future@example.com'] as $date => $email) {
            DB::table('event_interests')->insert([
                'event_id' => $event->id, 'event_date' => (string) $date, 'email' => $email,
                'token' => Str::random(32), 'created_at' => now(), 'updated_at' => now(),
            ]);
            DB::table('ticket_waitlists')->insert([
                'event_id' => $event->id, 'event_date' => (string) $date, 'name' => 'Guest', 'email' => $email,
                'subdomain' => 'somewhere', 'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        $this->artisan('app:prune-personal-data')->assertSuccessful();

        foreach (['event_interests', 'ticket_waitlists'] as $table) {
            $this->assertEqualsCanonicalizing(
                ['undated@example.com', 'future@example.com'],
                DB::table($table)->pluck('email')->all(),
                "{$table}: only the past event's entry goes"
            );
        }
    }

    /**
     * Signing up again re-sends the confirmation on the same row (RoleSubscriberController::
     * sendConfirmation() saves a fresh confirm_token), so the age that counts is updated_at: by
     * created_at, a link sent yesterday was pruned along with the month-old row it lives on.
     */
    public function test_an_unconfirmed_sign_up_is_aged_from_its_last_confirmation_email(): void
    {
        $role = $this->createRole($this->createOwner());
        $longAgo = now()->subDays(40);

        foreach (['abandoned@example.com' => $longAgo, 'resent@example.com' => now()->subDay()] as $email => $updatedAt) {
            DB::table('role_subscribers')->insert([
                'role_id' => $role->id, 'email' => $email, 'token' => Str::random(64),
                'created_at' => $longAgo, 'updated_at' => $updatedAt,
            ]);
        }

        $this->artisan('app:prune-personal-data')->assertSuccessful();

        $this->assertSame(['resent@example.com'], DB::table('role_subscribers')->pluck('email')->all());
    }

    /** A deleted sale keeps its amounts and loses the buyer's details, per-ticket answers included. */
    public function test_a_deleted_sale_forgets_the_buyer_including_per_ticket_answers(): void
    {
        $role = $this->createRole($this->createOwner());
        $event = $this->createEvent($role);
        $ticket = $this->createTicket($event);
        $sale = $this->createSale($event, $role, ['payment_amount' => 25, 'custom_value1' => 'Vegan'], $ticket);
        DB::table('sale_tickets')->where('sale_id', $sale->id)->update(['custom_value1' => 'Size L', 'custom_value10' => 'Allergic to nuts']);
        DB::table('sales')->where('id', $sale->id)->update(['is_deleted' => true, 'updated_at' => now()->subDays(31)]);

        $this->artisan('app:prune-personal-data')->assertSuccessful();

        $row = DB::table('sales')->where('id', $sale->id)->first();
        $this->assertSame('', $row->email);
        $this->assertSame('', $row->name);
        $this->assertNull($row->custom_value1);
        $this->assertSame(25.0, (float) $row->payment_amount, 'the organizer keeps the amount');

        $answers = DB::table('sale_tickets')->where('sale_id', $sale->id)->first();
        $this->assertNull($answers->custom_value1);
        $this->assertNull($answers->custom_value10);
    }

    public function test_stale_cached_thumbnails_are_deleted_and_fresh_ones_kept(): void
    {
        $dir = storage_path('app/'.AppController::YOUTUBE_THUMB_CACHE_DIR);
        @mkdir($dir, 0755, true);
        $stale = $dir.'/Stale_00001_mq.jpg';
        $fresh = $dir.'/Fresh_00001_mq.jpg';
        file_put_contents($stale, 'x');
        file_put_contents($fresh, 'x');
        touch($stale, time() - (AppController::YOUTUBE_THUMB_TTL_DAYS + 1) * 24 * 60 * 60);

        try {
            $this->artisan('app:prune-personal-data')->assertSuccessful();

            $this->assertFileDoesNotExist($stale);
            $this->assertFileExists($fresh);
        } finally {
            @unlink($stale);
            @unlink($fresh);
        }
    }
}
