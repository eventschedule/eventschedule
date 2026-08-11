<?php

namespace App\Mail;

/**
 * The pre-event backstop, sent regardless of where the retry ladder has got to. Guarantees nobody
 * arrives at the door having never been told there is a balance outstanding.
 */
class InstallmentFinalNotice extends InstallmentMailable
{
    protected string $template = 'installment_final_notice';

    protected function subjectLine(): string
    {
        return __('messages.installment_final_notice_subject', [
            'event' => $this->plan->sale?->event?->name ?? '',
            'days' => $this->daysUntilEvent(),
        ]);
    }

    protected function daysUntilEvent(): int
    {
        $starts = $this->plan->sale?->event?->starts_at;

        return $starts ? max(0, now()->diffInDays(\Carbon\Carbon::parse($starts), false)) : 0;
    }
}
