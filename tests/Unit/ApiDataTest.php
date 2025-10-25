<?php

namespace Tests\Unit;

use App\Models\Event;
use App\Models\Group;
use App\Models\Role;
use App\Models\Ticket;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ApiDataTest extends TestCase
{
    public function test_role_to_api_data_is_populated_for_non_pro_accounts(): void
    {
        $role = new Role([
            'type' => 'venue',
            'name' => 'Sample Venue',
            'email' => 'venue@example.com',
            'website' => 'https://venue.example.com',
            'description' => 'A description',
            'timezone' => 'UTC',
            'language_code' => 'en',
            'country_code' => 'US',
            'accept_requests' => true,
            'request_terms' => 'Be kind',
        ]);

        $role->id = 1;
        $role->subdomain = 'sample-venue';
        $role->plan_type = 'starter';
        $role->plan_expires = Carbon::now()->addMonth()->format('Y-m-d');
        $role->contacts = [
            ['name' => 'Support', 'email' => 'support@example.com', 'phone' => '+1234567890'],
        ];
        $role->youtube_links = json_encode([
            ['name' => 'Intro', 'url' => 'https://youtube.com/watch?v=123', 'thumbnail_url' => ''],
        ]);

        $group = new Group([
            'name' => 'Main Stage',
            'slug' => 'main-stage',
        ]);
        $group->id = 10;

        $role->setRelation('groups', new Collection([$group]));

        $data = $role->toApiData();

        $this->assertSame(UrlUtils::encodeId(1), $data->id);
        $this->assertSame('sample-venue', $data->subdomain);
        $this->assertTrue($data->accept_requests);
        $this->assertCount(1, $data->groups);
        $this->assertSame('Main Stage', $data->groups[0]['name']);
    }

    public function test_event_to_api_data_includes_members_and_tickets_for_all_plans(): void
    {
        $talent = new Role([
            'type' => 'talent',
            'name' => 'Performer',
        ]);
        $talent->id = 2;
        $talent->subdomain = 'performer';
        $talent->timezone = 'UTC';
        $talent->plan_type = 'starter';
        $talent->plan_expires = Carbon::now()->addMonth()->format('Y-m-d');
        $talent->setRelation('groups', new Collection());

        $venue = new Role([
            'type' => 'venue',
            'name' => 'The Club',
        ]);
        $venue->id = 3;
        $venue->subdomain = 'the-club';
        $venue->timezone = 'UTC';
        $venue->plan_type = 'starter';
        $venue->plan_expires = Carbon::now()->addMonth()->format('Y-m-d');
        $venue->setRelation('groups', new Collection());

        $event = new Event([
            'name' => 'API Showcase',
            'slug' => 'api-showcase',
            'description' => 'Showcase description',
            'starts_at' => '2024-01-01 20:00:00',
            'duration' => 120,
            'tickets_enabled' => true,
            'ticket_currency_code' => 'USD',
            'total_tickets_mode' => 'separate',
            'registration_url' => 'https://events.example.com/register',
            'event_url' => 'https://events.example.com/stream',
            'ticket_notes' => 'Bring your ticket',
            'payment_instructions' => 'Pay at the door',
        ]);

        $event->id = 5;
        $event->ticket_notes_html = '<p>Bring your ticket</p>';
        $event->payment_instructions_html = '<p>Pay at the door</p>';
        $event->flyer_image_url = 'flyers/api-showcase.png';

        $ticket = new Ticket([
            'type' => 'General Admission',
            'price' => 2500,
            'quantity' => 100,
            'description' => 'Floor access',
        ]);
        $ticket->id = 7;
        $ticket->event_id = 5;

        $event->setRelation('tickets', new Collection([$ticket]));
        $event->setRelation('roles', new Collection([$talent, $venue]));
        $event->setRelation('flyerImage', null);
        $event->setRelation('eventType', null);
        $event->setRelation('creatorRole', $talent);

        $data = $event->toApiData();

        $this->assertSame(UrlUtils::encodeId(5), $data->id);
        $this->assertSame('API Showcase', $data->name);
        $this->assertSame('UTC', $data->timezone);
        $this->assertTrue($data->tickets_enabled);
        $this->assertCount(1, $data->tickets);
        $this->assertArrayHasKey(UrlUtils::encodeId(2), $data->members);
        $this->assertCount(2, $data->schedules);
        $this->assertNull($data->curator_role);
    }
}
