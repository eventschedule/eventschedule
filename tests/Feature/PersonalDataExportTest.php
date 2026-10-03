<?php

namespace Tests\Feature;

use App\Mail\PersonalDataExportReady;
use App\Models\BackupJob;
use App\Models\User;
use App\Services\PersonalDataExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/** "Download my data" (GDPR Arts. 15 and 20): App\Services\PersonalDataExportService. */
class PersonalDataExportTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    public function test_the_export_holds_what_is_known_about_the_person_and_no_secrets(): void
    {
        Storage::fake('backups');
        Mail::fake();

        $user = User::factory()->create(['email' => 'person@example.com']);
        $role = $this->createRole($this->createOwner());
        $event = $this->createEvent($role, ['name' => 'Night Market']);
        // Bought as a guest, before the account existed: found by the address.
        $this->createSale($event, $role, ['email' => 'person@example.com', 'name' => 'Person', 'secret' => 'sale-secret-value']);
        DB::table('role_subscribers')->insert([
            'role_id' => $role->id, 'email' => 'person@example.com', 'name' => 'Person', 'token' => str_repeat('t', 32),
            'confirmed_at' => now(), 'ip_address' => '203.0.113.9', 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAs($user)->post(route('profile.data_export'))->assertRedirect(route('profile.edit').'#section-data');

        $job = BackupJob::where('user_id', $user->id)->where('type', 'personal')->sole();
        $this->assertSame('completed', $job->status);
        Mail::assertSent(PersonalDataExportReady::class, fn ($mail) => $mail->hasTo('person@example.com'));

        $json = Storage::disk('backups')->get($job->file_path);
        $data = json_decode($json, true);

        $this->assertSame('person@example.com', $data['account']['email']);
        $this->assertSame('Night Market', $data['purchases'][0]['event']);
        $this->assertSame('203.0.113.9', $data['email_signups'][0]['ip_address']);
        $this->assertStringNotContainsString('sale-secret-value', $json);
        $this->assertStringNotContainsString($user->getAuthPassword(), $json);
    }

    public function test_only_the_account_holder_can_download_it(): void
    {
        Storage::fake('backups');
        $owner = User::factory()->create();
        Storage::disk('backups')->put('personal-data-x.json', '{}');
        $job = BackupJob::create([
            'user_id' => $owner->id, 'type' => 'personal', 'status' => 'completed', 'role_ids' => [],
            'file_path' => 'personal-data-x.json', 'file_expires_at' => now()->addDay(),
        ]);
        $url = URL::temporarySignedRoute('profile.data_export.download', now()->addDay(), ['backupJob' => $job->id]);

        $this->actingAs(User::factory()->create())->get($url)->assertForbidden();
        $this->actingAs($owner)->get($url)->assertOk();
        $this->actingAs($owner)->get(route('profile.data_export.download', ['backupJob' => $job->id]))->assertForbidden();
    }

    /**
     * A table that stores who someone is has to be in the export, or named as left out with a
     * reason. The day a new one is added, this asks which.
     */
    public function test_every_table_holding_a_persons_rows_is_accounted_for(): void
    {
        $personColumns = ['user_id', 'email', 'guest_email', 'purchaser_email', 'recipient_email', 'reviewer_user_id',
            'reviewed_user_id', 'referrer_user_id', 'referred_user_id', 'from_user_id', 'to_user_id', 'reporter_user_id'];
        $known = array_merge(PersonalDataExportService::EXPORTED, array_keys(PersonalDataExportService::NOT_EXPORTED));
        $missing = [];

        foreach (array_column(Schema::getTables(), 'name') as $table) {
            if (in_array($table, ['migrations', 'failed_jobs', 'jobs', 'job_batches', 'cache', 'cache_locks'], true)) {
                continue;
            }
            if (array_intersect(Schema::getColumnListing($table), $personColumns) && ! in_array($table, $known, true)) {
                $missing[] = $table;
            }
        }

        $this->assertSame([], $missing, 'add these to PersonalDataExportService::build() or NOT_EXPORTED');
    }
}
