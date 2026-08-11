<?php

namespace App\Mail;

/**
 * A genuine decline. The load-bearing sentence is "your ticket is still valid" - without it the
 * buyer's first thought is that they have lost the seat they already paid most of, and that panic
 * becomes a support ticket or a chargeback rather than a retry.
 */
class InstallmentFailed extends InstallmentMailable
{
    protected string $template = 'installment_failed';

    protected function subjectLine(): string
    {
        return __('messages.installment_failed_subject', [
            'event' => $this->plan->sale?->event?->name ?? '',
        ]);
    }
}
