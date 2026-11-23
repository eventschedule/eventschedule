<?php

namespace App\Mail;

use App\Mail\Concerns\SendsToNotificationEmail;
use App\Models\Role;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

/**
 * The market's editors are told who asked for a spot and who is not coming
 * (VendorClaimNotifier::tellOwners()). The lines are plain values decided once, so every
 * reader's copy names the same rows, and are worded in the view, in each reader's language.
 */
class VendorMapClaimsWaiting extends Mailable
{
    use Queueable, SendsToNotificationEmail, SerializesModels;

    /**
     * @param  list<array{name: string, spot: string, sells: ?string, categories: string[], dates: string[]}>  $claims
     * @param  list<array{name: string, spot: string, sells: ?string, categories: string[], dates: string[]}>  $absences
     * @param  int  $following  rows this mail does not name, which the next one will
     */
    public function __construct(
        public Role $role,
        public string $kind,
        public array $claims,
        public array $absences,
        public int $following = 0,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subjectLine());
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.vendor-map.claims-waiting',
            text: 'emails.vendor-map.claims-waiting_text',
            with: [
                'role' => $this->role,
                'kind' => $this->kind,
                'claims' => $this->claims,
                'absences' => $this->absences,
                'following' => $this->following,
                'mailSubject' => $this->subjectLine(),
                // app_url(), never a bare route(): from a vendor's request the host running is
                // the market's own, where the admin tab does not answer.
                'actionUrl' => app_url(route('role.view_admin', ['subdomain' => $this->role->subdomain, 'tab' => 'vendor-map'], false)),
                'unsubscribeUrl' => $this->notificationEmailUnsubscribeUrl ?: app_url(route('role.unsubscribe', ['subdomain' => $this->role->subdomain], false)),
                'notificationEmailUnsubscribeUrl' => $this->notificationEmailUnsubscribeUrl,
            ],
        );
    }

    public function headers(): Headers
    {
        return $this->listUnsubscribeHeaders($this->role);
    }

    private function subjectLine(): string
    {
        $count = count($this->claims);

        return trans_choice('messages.vm_mail_owner_subject', $count, ['name' => $this->role->name, 'count' => $count]);
    }
}
