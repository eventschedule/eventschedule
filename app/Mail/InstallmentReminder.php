<?php

namespace App\Mail;

/**
 * Routine "we will charge you on Thursday" notice, sent a couple of days ahead.
 *
 * Deliberately calm: nothing is wrong, nothing is owed late, and the buyer has to do nothing.
 * A subject line that reads like a demand trains people to dread the address that will later
 * carry a genuine problem.
 */
class InstallmentReminder extends InstallmentMailable
{
    protected string $template = 'installment_reminder';

    protected function subjectLine(): string
    {
        return __('messages.installment_reminder_subject', [
            'event' => $this->plan->sale?->event?->name ?? '',
            'date' => $this->installment?->due_at?->format('j M Y') ?? '',
        ]);
    }
}
