<?php

namespace App\Mail;

class AppointmentDeclined extends AppointmentLifecycleMail
{
    protected function subjectKey(): string
    {
        return 'appointment_declined_subject';
    }

    protected function headingKey(): string
    {
        return 'appointment_declined_heading';
    }

    protected function introKey(): string
    {
        return 'appointment_declined_intro';
    }

    protected function showRebook(): bool
    {
        return true;
    }
}
