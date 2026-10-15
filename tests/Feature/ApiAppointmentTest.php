<?php

namespace Tests\Feature;

use App\Jobs\SendQueuedEmail;
use App\Mail\AppointmentPaymentDue;
use App\Models\AppointmentType;
use App\Models\Role;
use App\Models\Sale;
use App\Models\User;
use App\Services\AppointmentService;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The appointment API (Api\ApiAppointmentController): the bookable types, their open times, and a
 * booking, for an agent that talks to the guest. Every time carries a sentence it can read out, a
 * taken time comes back with the nearest open ones, and a repeat with the same Idempotency-Key is
 * the same booking.
 */
class ApiAppointmentTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const TZ = 'America/New_York';

    private function apiKey(User $user): string
    {
        $raw = 'testapikey_'.Str::random(24);
        $user->api_key = substr(hash('sha256', $raw), 0, 8);
        $user->api_key_hash = Hash::make($raw);
        $user->save();

        return $raw;
    }

    private function everyDay(): array
    {
        return array_fill_keys(['0', '1', '2', '3', '4', '5', '6'], [['start' => '09:00', 'end' => '17:00']]);
    }

    /** @return array{0: User, 1: Role, 2: AppointmentType, 3: string} */
    private function schedule(array $typeAttrs = []): array
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['timezone' => self::TZ]);
        $type = $this->createAppointmentType($role, $typeAttrs + ['name' => 'Consult', 'weekly_windows' => $this->everyDay()]);

        return [$owner, $role, $type, $this->apiKey($owner)];
    }

    /**
     * Mail that can go out: an approval type is offered over the API only then, because the guest
     * hears the schedule's answer by email. Queued, never sent.
     */
    private function mailOn(): void
    {
        config(['app.hosted' => false, 'mail.default' => 'smtp']);
        Queue::fake();
    }

    private function api(string $key)
    {
        return $this->withHeaders(['X-API-Key' => $key]);
    }

    private function firstSlot(AppointmentType $type, int $offsetDays = 1, int $index = 0): string
    {
        $date = Carbon::now(self::TZ)->addDays($offsetDays)->format('Y-m-d');

        return app(AppointmentService::class)->availableSlots($type->fresh(), $date, 1)['days'][$date][$index]['utc'];
    }

    private function bookingUrl(AppointmentType $type): string
    {
        return '/api/appointment-types/'.UrlUtils::encodeId($type->id).'/bookings';
    }

    private function guest(string $slot, array $extra = []): array
    {
        return $extra + ['slot' => $slot, 'name' => 'Jane Guest', 'email' => 'jane@gmail.com', 'guest_timezone' => self::TZ];
    }

    // ---------------------------------------------------------------- types

    public function test_the_types_list_carries_what_an_agent_needs(): void
    {
        $this->mailOn();
        [, $role, $type, $key] = $this->schedule([
            'requires_approval' => true,
            'ask_phone' => true,
            'require_phone' => true,
            'custom_fields' => ['reason' => ['name' => 'Reason for the visit', 'type' => 'string', 'required' => true, 'index' => 1]],
        ]);
        $this->createAppointmentType($role, ['name' => 'Paused', 'is_active' => false, 'weekly_windows' => $this->everyDay()]);

        $response = $this->api($key)->getJson('/api/schedules/'.$role->subdomain.'/appointment-types')->assertOk();

        $response->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', UrlUtils::encodeId($type->id))
            ->assertJsonPath('data.0.name', 'Consult')
            ->assertJsonPath('data.0.duration_minutes', 30)
            ->assertJsonPath('data.0.payment', 'free')
            ->assertJsonPath('data.0.timezone', self::TZ)
            ->assertJsonPath('data.0.requires_approval', true)
            ->assertJsonPath('data.0.requires_phone', true)
            ->assertJsonPath('data.0.host_name', null)
            ->assertJsonPath('data.0.questions.0.key', 'reason')
            ->assertJsonPath('data.0.questions.0.required', true);

        $next = $response->json('data.0.next_available');
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:00Z$/', $next['starts_at']);
        $this->assertNotEmpty($next['label']);
    }

    public function test_priced_types_are_left_out_when_the_schedule_cannot_send_mail(): void
    {
        config(['app.hosted' => true]);
        [, $role, , $key] = $this->schedule();
        $this->createAppointmentType($role, ['name' => 'Paid', 'price' => 40, 'currency_code' => 'USD', 'payment_method' => 'cash', 'weekly_windows' => $this->everyDay()]);
        $this->assertFalse($role->fresh()->hasEmailSettings());

        $names = collect($this->api($key)->getJson('/api/schedules/'.$role->subdomain.'/appointment-types')->assertOk()->json('data'))->pluck('name');

        $this->assertSame(['Consult'], $names->all(), 'a guest booked by phone is told, and paid, only by email');
    }

    // ---------------------------------------------------------------- slots

    public function test_slots_come_with_a_label_in_the_schedules_zone(): void
    {
        [, , $type, $key] = $this->schedule();
        $date = Carbon::now(self::TZ)->addDay()->format('Y-m-d');

        $response = $this->api($key)
            ->getJson('/api/appointment-types/'.UrlUtils::encodeId($type->id).'/slots?from='.$date.'&days=1')
            ->assertOk()
            ->assertJsonPath('meta.timezone', self::TZ);

        $first = $response->json('data.0');
        $local = Carbon::createFromFormat('Y-m-d\TH:i:s\Z', $first['starts_at'], 'UTC')->setTimezone(self::TZ);

        $this->assertSame('9:00 AM', $local->format('g:i A'), 'the first slot opens the day, in New York');
        $this->assertSame($local->format('l, F j').' at 9:00 AM '.$local->format('T'), $first['label']);
        $this->assertSame(16, count($response->json('data')), 'nine to five, every half hour');
    }

    // ---------------------------------------------------------------- bookings

    public function test_a_free_booking_is_confirmed_and_belongs_to_the_guest_not_the_caller(): void
    {
        [$owner, , $type, $key] = $this->schedule();
        $slot = $this->firstSlot($type);

        $response = $this->api($key)->postJson($this->bookingUrl($type), $this->guest($slot))
            ->assertStatus(201)
            ->assertJsonPath('data.status', 'confirmed')
            ->assertJsonPath('data.starts_at', $slot)
            ->assertJsonPath('data.pay_by', null);

        $sale = Sale::firstOrFail();
        $this->assertNull($sale->user_id, 'the API caller never becomes the buyer');
        $this->assertNotSame($owner->id, $sale->user_id);
        $this->assertSame('api', $sale->utm_source);
        $this->assertSame('paid', $sale->status);

        $manage = $response->json('data.manage_url');
        $this->assertStringStartsWith('http', $manage, 'absolute, so it can be sent anywhere');
        $this->assertStringContainsString($sale->secret, $manage);
        $this->assertStringContainsString('Consult', $response->json('data.summary'));
    }

    public function test_an_approval_booking_waits_for_the_owner(): void
    {
        $this->mailOn();
        [, , $type, $key] = $this->schedule(['requires_approval' => true]);

        $this->api($key)->postJson($this->bookingUrl($type), $this->guest($this->firstSlot($type)))
            ->assertStatus(201)
            ->assertJsonPath('data.status', 'pending_approval');

        $sale = Sale::firstOrFail();
        $this->assertNull($sale->event->roles()->first()->pivot->is_accepted);
    }

    public function test_a_booking_paid_online_mails_the_guest_a_payment_link_at_once(): void
    {
        config(['app.hosted' => false, 'mail.default' => 'smtp']);
        Queue::fake();
        [$owner, , $type, $key] = $this->schedule(['price' => 40, 'currency_code' => 'USD', 'payment_method' => 'payment_url']);
        $owner->forceFill(['payment_url' => 'https://pay.example.org/me', 'payment_secret' => 'shh'])->save();

        $response = $this->api($key)->postJson($this->bookingUrl($type), $this->guest($this->firstSlot($type)))
            ->assertStatus(201)
            ->assertJsonPath('data.status', 'awaiting_payment')
            ->assertJsonPath('data.emails_sent', true);

        $sale = Sale::firstOrFail();
        $this->assertSame('unpaid', $sale->status);
        $this->assertSame(
            $sale->created_at->copy()->setTimezone('UTC')->addHours($type->fresh()->expireHours())->format('Y-m-d\TH:i:s\Z'),
            $response->json('data.pay_by'),
            'pay_by is the end of the hold'
        );
        $this->assertStringContainsString('j…@gmail.com', $response->json('data.summary'));

        Queue::assertPushed(SendQueuedEmail::class, function (SendQueuedEmail $job) {
            return (new \ReflectionProperty($job, 'mailable'))->getValue($job) instanceof AppointmentPaymentDue
                && (new \ReflectionProperty($job, 'recipient'))->getValue($job) === 'jane@gmail.com';
        });
    }

    public function test_a_taken_slot_is_a_conflict_with_the_three_nearest_open_times(): void
    {
        [, , $type, $key] = $this->schedule();
        $slot = $this->firstSlot($type, 1, 4);

        $this->api($key)->postJson($this->bookingUrl($type), $this->guest($slot))->assertStatus(201);

        $response = $this->api($key)
            ->postJson($this->bookingUrl($type), $this->guest($slot, ['email' => 'other@gmail.com']))
            ->assertStatus(409)
            ->assertJsonPath('error', 'slot_taken');

        $alternatives = collect($response->json('alternatives'));
        $this->assertCount(3, $alternatives);
        $this->assertNotContains($slot, $alternatives->pluck('starts_at'));

        // The nearest: the half hours either side of it, then the next one.
        $taken = Carbon::createFromFormat('Y-m-d\TH:i:s\Z', $slot, 'UTC');
        $this->assertContains($taken->copy()->subMinutes(30)->format('Y-m-d\TH:i:s\Z'), $alternatives->pluck('starts_at'));
        $this->assertContains($taken->copy()->addMinutes(30)->format('Y-m-d\TH:i:s\Z'), $alternatives->pluck('starts_at'));
        $this->assertNotEmpty($alternatives->first()['label']);
    }

    public function test_a_repeat_with_the_same_idempotency_key_is_the_same_booking(): void
    {
        [, , $type, $key] = $this->schedule();
        $body = $this->guest($this->firstSlot($type));

        $first = $this->api($key)->withHeaders(['Idempotency-Key' => 'call-42'])->postJson($this->bookingUrl($type), $body)->assertStatus(201);
        $second = $this->api($key)->withHeaders(['Idempotency-Key' => 'call-42'])->postJson($this->bookingUrl($type), $body)->assertStatus(201);

        $this->assertSame($first->json('data.id'), $second->json('data.id'));
        $second->assertHeader('Idempotent-Replayed', 'true');
        $this->assertSame(1, Sale::count());

        // The same key for a different request is a mistake, said so.
        $this->api($key)->withHeaders(['Idempotency-Key' => 'call-42'])
            ->postJson($this->bookingUrl($type), $this->guest($this->firstSlot($type, 2)))
            ->assertStatus(422)
            ->assertJsonPath('error', 'idempotency_key_reused');
        $this->assertSame(1, Sale::count());
    }

    public function test_required_answers_and_fields_are_listed_per_field(): void
    {
        [, , $type, $key] = $this->schedule([
            'custom_fields' => ['reason' => ['name' => 'Reason', 'type' => 'string', 'required' => true, 'index' => 1]],
        ]);

        $this->api($key)->postJson($this->bookingUrl($type), ['slot' => $this->firstSlot($type)])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'answers.reason']);

        $this->api($key)->postJson($this->bookingUrl($type), $this->guest($this->firstSlot($type), ['answers' => ['reason' => 'Back pain']]))
            ->assertStatus(201);
        $this->assertSame('Back pain', Sale::firstOrFail()->custom_value1);
    }

    public function test_a_nested_answer_is_a_field_error_not_a_server_error(): void
    {
        [, , $type, $key] = $this->schedule([
            'custom_fields' => ['reason' => ['name' => 'Reason', 'type' => 'string', 'required' => false, 'index' => 1]],
        ]);

        $this->api($key)->postJson($this->bookingUrl($type), $this->guest($this->firstSlot($type), ['answers' => ['reason' => [['deep']]]]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['answers.reason']);

        $this->api($key)->postJson($this->bookingUrl($type), $this->guest($this->firstSlot($type), ['answers' => ['reason' => ['Back', 'Neck']]]))
            ->assertStatus(201);
        $this->assertSame('Back, Neck', Sale::firstOrFail()->custom_value1);
    }

    public function test_a_repeat_is_answered_even_after_the_type_stopped_taking_bookings(): void
    {
        [, , $type, $key] = $this->schedule();
        $body = $this->guest($this->firstSlot($type));

        $first = $this->api($key)->withHeaders(['Idempotency-Key' => 'call-7'])->postJson($this->bookingUrl($type), $body)->assertStatus(201);
        $type->forceFill(['is_active' => false])->save();

        $this->api($key)->withHeaders(['Idempotency-Key' => 'call-7'])->postJson($this->bookingUrl($type), $body)
            ->assertStatus(201)
            ->assertHeader('Idempotent-Replayed', 'true')
            ->assertJsonPath('data.id', $first->json('data.id'));

        // Without the key, the type's state answers.
        $this->api($key)->postJson($this->bookingUrl($type), $this->guest($this->firstSlot($type, 2)))->assertStatus(422);
    }

    public function test_a_retry_without_a_key_is_told_the_guest_already_holds_the_time(): void
    {
        [, , $type, $key] = $this->schedule();
        $body = $this->guest($this->firstSlot($type));

        $first = $this->api($key)->postJson($this->bookingUrl($type), $body)->assertStatus(201);

        $this->api($key)->postJson($this->bookingUrl($type), $body)
            ->assertStatus(409)
            ->assertJsonPath('error', 'already_booked')
            ->assertJsonPath('booking.id', $first->json('data.id'))
            ->assertJsonPath('booking.status', 'confirmed');
        $this->assertSame(1, Sale::count());
    }

    public function test_emails_sent_is_false_when_the_guests_mail_is_suppressed(): void
    {
        config(['app.hosted' => false, 'mail.default' => 'smtp']);
        Queue::fake();
        [, , $type, $key] = $this->schedule();

        $this->api($key)->postJson($this->bookingUrl($type), $this->guest($this->firstSlot($type)))
            ->assertStatus(201)->assertJsonPath('data.emails_sent', true);

        $this->api($key)->postJson($this->bookingUrl($type), $this->guest($this->firstSlot($type, 2), ['email' => 'jane@example.com']))
            ->assertStatus(201)
            ->assertJsonPath('data.emails_sent', false);
    }

    public function test_a_type_that_answers_by_email_is_left_out_when_the_schedule_cannot_send_mail(): void
    {
        config(['app.hosted' => true]);
        [, $role, , $key] = $this->schedule();
        $this->createAppointmentType($role, ['name' => 'Needs approval', 'requires_approval' => true, 'weekly_windows' => $this->everyDay()]);

        $names = collect($this->api($key)->getJson('/api/schedules/'.$role->subdomain.'/appointment-types')->assertOk()->json('data'))->pluck('name');

        $this->assertSame(['Consult'], $names->all(), 'the guest would only hear of the decision by email');
    }

    // ---------------------------------------------------------------- who may call

    public function test_only_an_owner_or_admin_key_may_book(): void
    {
        [, $role, $type] = $this->schedule();

        $admin = $this->createOwner();
        $role->users()->attach($admin->id, ['level' => 'admin']);
        $viewer = $this->createOwner();
        $role->users()->attach($viewer->id, ['level' => 'viewer']);
        $stranger = $this->createOwner();

        foreach ([$viewer, $stranger] as $refused) {
            $this->api($this->apiKey($refused))->getJson('/api/schedules/'.$role->subdomain.'/appointment-types')->assertStatus(403);
            $this->api($this->apiKey($refused))->postJson($this->bookingUrl($type), $this->guest($this->firstSlot($type)))->assertStatus(403);
        }
        $this->assertSame(0, Sale::count());

        $this->api($this->apiKey($admin))->postJson($this->bookingUrl($type), $this->guest($this->firstSlot($type)))->assertStatus(201);
    }

    public function test_the_api_is_limited_to_pro_schedules(): void
    {
        $owner = $this->createOwner();
        $role = $this->createFreeRole($owner, 'talent', ['timezone' => self::TZ]);
        $this->assertFalse($role->fresh()->isPro());
        $type = $this->createAppointmentType($role, ['weekly_windows' => $this->everyDay()]);
        $key = $this->apiKey($owner);

        $this->api($key)->getJson('/api/schedules/'.$role->subdomain.'/appointment-types')
            ->assertStatus(403)
            ->assertJsonPath('error', 'API usage is limited to Pro accounts');
        $this->api($key)->getJson('/api/appointment-types/'.UrlUtils::encodeId($type->id).'/slots')->assertStatus(403);
    }

    public function test_the_bookings_list_says_a_booking_came_through_the_api(): void
    {
        [$owner, $role, $type, $key] = $this->schedule();
        $this->api($key)->postJson($this->bookingUrl($type), $this->guest($this->firstSlot($type)))->assertStatus(201);

        $this->actingAs($owner)
            ->get(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'appointments', 'view' => 'bookings']))
            ->assertOk()
            ->assertSee(__('messages.appointments_booked_via_api'));
    }
}
