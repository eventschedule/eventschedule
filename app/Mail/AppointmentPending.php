<?php

namespace App\Mail;

class AppointmentPending extends AppointmentLifecycleMail
{
    protected function subjectKey(): string
    {
        return 'appointment_pending_subject';
    }

    protected function headingKey(): string
    {
        return 'appointments_request_sent';
    }

    protected function introKey(): string
    {
        return 'appointment_pending_intro';
    }
}
