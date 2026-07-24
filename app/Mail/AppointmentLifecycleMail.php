<?php

namespace App\Mail;

use App\Models\AppointmentType;
use App\Models\Event;
use App\Models\Role;
use App\Models\Sale;
use App\Utils\UrlUtils;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

/**
 * Shared base for the guest-facing appointment lifecycle emails (pending / declined / cancelled).
 * Subclasses supply the subject, heading, and intro keys; all render the one shared template.
 */
abstract class AppointmentLifecycleMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $sale;

    protected $event;

    protected $role;

    protected $type;

    public function __construct(Sale $sale, Event $event, ?Role $role = null, ?AppointmentType $type = null)
    {
        $this->sale = $sale;
        $this->event = $event;
        $this->role = $role;
        $this->type = $type;
    }

    abstract protected function subjectKey(): string;

    abstract protected function headingKey(): string;

    abstract protected function introKey(): string;

    protected function showRebook(): bool
    {
        return false;
    }

    public function envelope(): Envelope
    {
        $fromAddress = config('mail.from.address');
        $fromName = config('mail.from.name');

        if ($this->role && $this->role->hasEmailSettings()) {
            $settings = $this->role->getEmailSettings();
            if (! empty($settings['from_address'])) {
                $fromAddress = $settings['from_address'];
            }
            if (! empty($settings['from_name'])) {
                $fromName = $settings['from_name'];
            }
        }

        return new Envelope(
            subject: __('messages.'.$this->subjectKey(), ['name' => $this->type?->name ?? $this->event->name]),
            from: new Address($fromAddress, $fromName),
        );
    }

    public function content(): Content
    {
        $manageUrl = route('appointments.manage', [
            'event_id' => UrlUtils::encodeId($this->event->id),
            'secret' => $this->sale->secret,
        ]);

        $rebookUrl = ($this->showRebook() && $this->role)
            ? route('appointments.book', ['subdomain' => $this->role->subdomain])
            : null;

        return new Content(
            view: 'emails.appointment_lifecycle',
            text: 'emails.appointment_lifecycle_text',
            with: [
                'sale' => $this->sale,
                'event' => $this->event,
                'role' => $this->role,
                'type' => $this->type,
                'heading' => __('messages.'.$this->headingKey()),
                'intro' => __('messages.'.$this->introKey(), ['schedule' => $this->role?->name ?? '']),
                'manageUrl' => $manageUrl,
                'rebookUrl' => $rebookUrl,
            ],
        );
    }

    public function headers(): Headers
    {
        if ($this->role) {
            return new Headers(
                text: [
                    'List-Unsubscribe' => '<'.route('role.unsubscribe', ['subdomain' => $this->role->subdomain]).'>',
                    'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
                ],
            );
        }

        return new Headers;
    }
}
