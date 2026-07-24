<?php

namespace App\Mail;

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
}
