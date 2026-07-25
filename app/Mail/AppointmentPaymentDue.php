<?php

namespace App\Mail;

class AppointmentPaymentDue extends AppointmentLifecycleMail
{
    protected function subjectKey(): string
    {
        return 'appointment_payment_due_subject';
    }

    protected function headingKey(): string
    {
        return 'appointments_awaiting_payment'; // "Complete your payment" - already translated everywhere
    }

    protected function introKey(): string
    {
        return 'appointment_payment_due_intro';
    }
}
