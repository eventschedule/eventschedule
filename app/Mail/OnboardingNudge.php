<?php

namespace App\Mail;

use App\Models\User;
use App\Utils\UrlUtils;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

/**
 * Reaches an account that signed up meaning to run a schedule and never created one.
 *
 * Three stages with different copy rather than the same mail three times: someone who has not
 * come back after a week needs a different message from someone who left an hour ago.
 * Stage 3 says outright that it is the last one.
 *
 * The button resumes rather than restarts: when the account already picked a schedule type
 * (users.pending_schedule_type) it goes through /getting-started?type= to that type's form, and a
 * claimed name (users.pending_schedule_name) is named in the stage 1 subject.
 *
 * The personal parts - the founder sign-off, the reply invitation, the Reply-To and the example
 * schedules link - are eventschedule.com's alone, so they are gated on is_nexus. This command runs
 * on every hosted install, and an operator platform (IS_HOSTED=true, IS_NEXUS=false) has neither
 * our founder nor our inbox, and no /examples route.
 */
class OnboardingNudge extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public int $stage,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectLine(),
            replyTo: config('app.is_nexus') && config('app.support_email')
                ? [new Address((string) config('app.support_email'))]
                : [],
        );
    }

    public function content(): Content
    {
        $nexus = (bool) config('app.is_nexus');
        $type = $this->pendingType();

        return new Content(
            view: 'emails.onboarding_nudge',
            text: 'emails.onboarding_nudge_text',
            with: [
                'user' => $this->user,
                'stage' => $this->stage,
                'title' => $this->subjectLine(),
                // "Hello Sam," or just "Hello," - never firstName()'s English "there".
                'greeting' => $this->user->greetingName()
                    ? __('messages.hello').' '.$this->user->greetingName().','
                    : __('messages.hello').',',
                // Stage 1 swaps its "choose a type" sentence for one about the type they picked.
                'typeKey' => $this->stage === 1 && $type ? 'messages.onboarding_nudge_type_'.$type : null,
                'startUrl' => app_url('/getting-started'.($type ? '?type='.$type : '')),
                'examplesUrl' => $nexus && $this->stage < 3 ? marketing_url('/examples') : null,
                'replyKey' => $nexus && $this->stage > 1 ? 'messages.onboarding_nudge_reply_'.$this->stage : null,
                'signoff' => $nexus ? __('messages.onboarding_nudge_signoff', ['app' => config('app.name')]) : null,
                'unsubscribeUrl' => UrlUtils::userUnsubscribeUrl($this->user->email, app()->getLocale()),
            ],
        );
    }

    private function subjectLine(): string
    {
        if ($this->stage === 1 && $this->user->pending_schedule_name) {
            return __('messages.onboarding_nudge_subject_1_named', [
                'schedule' => Str::headline($this->user->pending_schedule_name),
            ]);
        }

        return __('messages.onboarding_nudge_subject_'.$this->stage);
    }

    private function pendingType(): ?string
    {
        $type = $this->user->pending_schedule_type;

        return in_array($type, ['talent', 'venue', 'curator'], true) ? $type : null;
    }
}
