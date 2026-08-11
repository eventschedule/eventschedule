<?php

namespace App\Mail;

/**
 * Retries are exhausted and the ticket has stopped scanning. "On hold", never the schema's
 * "delinquent" - and it must say plainly that paying restores the ticket immediately, because
 * that is the action we want.
 */
class InstallmentOnHold extends InstallmentMailable
{
    protected string $template = 'installment_on_hold';

    protected function subjectLine(): string
    {
        return __('messages.installment_on_hold_subject', [
            'event' => $this->plan->sale?->event?->name ?? '',
        ]);
    }
}
