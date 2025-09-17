<?php

namespace App\Listeners;

use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Log;

class LogSentMessage
{
    public function handle(MessageSent $event): void
    {
        $message = $this->extractMessage($event);

        $context = [];

        if ($message) {
            $context['subject'] = $this->extractSubject($message);
            $context['to'] = $this->addressesFromMessage($message, 'getTo');
            $context['cc'] = $this->addressesFromMessage($message, 'getCc');
            $context['bcc'] = $this->addressesFromMessage($message, 'getBcc');
        }

        if (isset($event->channel)) {
            $context['channel'] = $event->channel;
        }

        if (! empty($event->data['notification'] ?? null)) {
            $notification = $event->data['notification'];
            $context['notification'] = is_object($notification) ? $notification::class : (string) $notification;
        }

        if (! empty($event->data['mailable'] ?? null)) {
            $mailable = $event->data['mailable'];
            $context['mailable'] = is_object($mailable) ? $mailable::class : (string) $mailable;
        }

        if (! empty($event->data['notifiable'] ?? null) && is_object($event->data['notifiable'])) {
            $notifiable = $event->data['notifiable'];

            $context['notifiable'] = array_filter([
                'type' => $notifiable::class,
                'id' => method_exists($notifiable, 'getKey') ? $notifiable->getKey() : null,
                'email' => $notifiable->email ?? null,
            ], static fn ($value) => $value !== null && $value !== '');
        }

        $context = array_filter(
            $context,
            static fn ($value) => $value !== null && $value !== [] && $value !== ''
        );

        Log::info('Mail sent', $context);
    }

    private function extractMessage(MessageSent $event): ?object
    {
        if (isset($event->message)) {
            return $event->message;
        }

        if (isset($event->sent) && method_exists($event->sent, 'getOriginalMessage')) {
            return $event->sent->getOriginalMessage();
        }

        return null;
    }

    private function extractSubject(object $message): ?string
    {
        return method_exists($message, 'getSubject') ? $message->getSubject() : null;
    }

    private function addressesFromMessage(object $message, string $method): array
    {
        if (! method_exists($message, $method)) {
            return [];
        }

        return $this->formatAddresses($message->{$method}());
    }

    /**
     * @param mixed $addresses
     */
    private function formatAddresses($addresses): array
    {
        if (empty($addresses)) {
            return [];
        }

        $formatted = [];

        foreach ((array) $addresses as $key => $value) {
            if ($value instanceof \Symfony\Component\Mime\Address) {
                $formatted[] = array_filter([
                    'email' => $value->getAddress(),
                    'name' => $value->getName() ?: null,
                ], static fn ($part) => $part !== null && $part !== '');

                continue;
            }

            if (is_string($key) && (is_string($value) || $value === null)) {
                $formatted[] = array_filter([
                    'email' => $key,
                    'name' => $value ?: null,
                ], static fn ($part) => $part !== null && $part !== '');

                continue;
            }

            if (is_string($value)) {
                $formatted[] = ['email' => $value];

                continue;
            }

            if (is_array($value)) {
                $formatted[] = array_filter([
                    'email' => $value['email'] ?? $value['address'] ?? null,
                    'name' => $value['name'] ?? null,
                ], static fn ($part) => $part !== null && $part !== '');

                continue;
            }
        }

        return array_values(
            array_filter($formatted, static fn ($entry) => ! empty($entry['email'] ?? null))
        );
    }
}
