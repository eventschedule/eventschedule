<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\EventAddedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MailDisableDeliveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_event_added_notification_respects_mail_disable_setting(): void
    {
        config(['mail.disable_delivery' => false]);

        $event = new Event();
        $notifiable = User::factory()->create();

        Setting::setGroup('mail', ['disable_delivery' => '0']);
        $enabledNotification = new EventAddedNotification($event);
        $this->assertSame(['mail'], $enabledNotification->via($notifiable));

        Setting::setGroup('mail', ['disable_delivery' => '1']);
        $disabledNotification = new EventAddedNotification($event);
        $this->assertSame([], $disabledNotification->via($notifiable));
    }
}
