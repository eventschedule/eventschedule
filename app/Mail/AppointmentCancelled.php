<?php

namespace App\Mail;

use App\Utils\IcsUtils;
use Illuminate\Mail\Mailables\Attachment;

class AppointmentCancelled extends AppointmentLifecycleMail
{
    protected function subjectKey(): string
    {
        return 'appointment_cancelled_subject';
    }

    protected function headingKey(): string
    {
        return 'appointments_cancelled';
    }

    protected function introKey(): string
    {
        return 'appointment_cancelled_intro';
    }

    protected function showRebook(): bool
    {
        return true;
    }

    protected function tone(): ?string
    {
        return 'danger';
    }

    /**
     * A cancellation (method=CANCEL) with the booking's own UID, so the entry the guest was sent
     * comes off their calendar. Only when they were sent one: see IcsUtils::guestHoldsInvite().
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        if (! IcsUtils::guestHoldsInvite($this->event, $this->sale)) {
            return [];
        }

        return [
            IcsUtils::attachment(fn () => IcsUtils::buildInvite($this->event, $this->role, $this->sale, 'CANCEL'), 'CANCEL', 'appointment.ics'),
        ];
    }
}
