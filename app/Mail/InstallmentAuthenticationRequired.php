<?php

namespace App\Mail;

/**
 * SCA: the bank wants the cardholder to approve the payment. Its own email on purpose - nothing
 * is wrong with the card, and wording it as a decline is both false and alarming for the European
 * buyers this will reach most often.
 */
class InstallmentAuthenticationRequired extends InstallmentMailable
{
    protected string $template = 'installment_authentication';

    protected function subjectLine(): string
    {
        return __('messages.installment_authentication_subject', [
            'event' => $this->plan->sale?->event?->name ?? '',
        ]);
    }
}
