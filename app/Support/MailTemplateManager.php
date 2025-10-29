<?php

namespace App\Support;

use App\Models\Setting;
use InvalidArgumentException;

class MailTemplateManager
{
    protected const GROUP = 'mail_templates';

    /**
     * @var array<string, array<string, mixed>>
     */
    protected array $templates;

    /**
     * @var array<string, string|null>
     */
    protected array $storedValues;

    public function __construct()
    {
        $this->templates = config('mail_templates.templates', []);
        $this->storedValues = Setting::forGroup(self::GROUP);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        return collect($this->templates)
            ->map(fn ($template, $key) => $this->buildTemplate((string) $key, $template))
            ->values()
            ->all();
    }

    public function get(string $key): array
    {
        if (! array_key_exists($key, $this->templates)) {
            throw new InvalidArgumentException("Unknown mail template [{$key}].");
        }

        return $this->buildTemplate($key, $this->templates[$key]);
    }

    public function exists(string $key): bool
    {
        return array_key_exists($key, $this->templates);
    }

    public function enabled(string $key): bool
    {
        if (! $this->exists($key)) {
            return false;
        }

        $config = $this->buildTemplate($key, $this->templates[$key]);

        return (bool) ($config['enabled'] ?? true);
    }

    public function renderSubject(string $key, array $data = []): string
    {
        $template = $this->buildTemplate($key, $this->templates[$key] ?? []);

        $field = (!empty($data['is_curated']) && array_key_exists('subject_curated', $template))
            ? 'subject_curated'
            : 'subject';

        $subjectTemplate = $template[$field] ?? '';

        $subject = $this->replacePlaceholders($subjectTemplate, $data, escape: false);

        return trim(preg_replace('/\s+/', ' ', $subject) ?? '');
    }

    public function renderBody(string $key, array $data = []): string
    {
        $template = $this->buildTemplate($key, $this->templates[$key] ?? []);

        $field = (!empty($data['is_curated']) && array_key_exists('body_curated', $template))
            ? 'body_curated'
            : 'body';

        $bodyTemplate = $template[$field] ?? '';

        $subjectLine = $this->renderSubject($key, $data);

        $dataWithSubject = array_merge($data, ['subject_line' => $subjectLine]);

        return $this->replacePlaceholders($bodyTemplate, $dataWithSubject, escape: true);
    }

    public function updateFromArray(array $input): void
    {
        foreach ($input as $key => $values) {
            if (! is_array($values) || ! $this->exists((string) $key)) {
                continue;
            }

            $this->updateTemplate((string) $key, $values);
        }
    }

    public function updateTemplate(string $key, array $input): void
    {
        if (! $this->exists($key)) {
            throw new InvalidArgumentException("Unknown mail template [{$key}].");
        }

        $config = $this->templates[$key];

        $values = [];

        if (array_key_exists('enabled', $input)) {
            $values["{$key}_enabled"] = $this->boolToStored($input['enabled']) ? '1' : '0';
        }

        foreach (['subject', 'subject_curated', 'body', 'body_curated'] as $field) {
            if (array_key_exists($field, $config) && array_key_exists($field, $input)) {
                $values["{$key}_{$field}"] = $this->prepareValue(
                    $input[$field],
                    in_array($field, ['body', 'body_curated'], true)
                );
            }
        }

        if (! empty($values)) {
            Setting::setGroup(self::GROUP, $values);
            $this->storedValues = Setting::forGroup(self::GROUP);
        }
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    protected function buildTemplate(string $key, array $config): array
    {
        $template = [
            'key' => $key,
            'label' => $config['label'] ?? ucfirst(str_replace('_', ' ', $key)),
            'description' => $config['description'] ?? null,
            'placeholders' => $config['placeholders'] ?? [],
        ];

        foreach (['subject', 'subject_curated', 'body', 'body_curated'] as $field) {
            if (array_key_exists($field, $config)) {
                $template[$field] = $this->storedValues["{$key}_{$field}"] ?? $config[$field];
                $template["default_{$field}"] = $config[$field];
            }
        }

        $template['has_subject_curated'] = array_key_exists('subject_curated', $config);
        $template['has_body_curated'] = array_key_exists('body_curated', $config);
        $template['default_enabled'] = (bool) ($config['enabled'] ?? true);
        $storedEnabled = $this->storedValues["{$key}_enabled"] ?? null;
        $template['enabled'] = $this->boolFromStored($storedEnabled, $template['default_enabled']);

        return $template;
    }

    public function sampleData(string $key, bool $isCurated = false): array
    {
        $baseUrl = rtrim(config('app.url') ?? 'https://example.com', '/');
        $appName = config('app.name');

        return match ($key) {
            'claim_role' => [
                'event_name' => 'Sample Event',
                'role_name' => 'Sample Artist',
                'venue_name' => 'Sample Venue',
                'curator_name' => $isCurated ? 'Sample Curator' : '',
                'organizer_name' => 'Alex Organizer',
                'organizer_email' => 'organizer@example.com',
                'event_url' => $baseUrl . '/events/sample-event',
                'sign_up_url' => $baseUrl . '/sign-up',
                'unsubscribe_url' => $baseUrl . '/unsubscribe',
                'app_name' => $appName,
                'is_curated' => $isCurated,
            ],
            'claim_venue' => [
                'event_name' => 'Sample Event',
                'role_name' => 'Sample Performer',
                'venue_name' => 'Sample Venue',
                'curator_name' => $isCurated ? 'Sample Curator' : '',
                'organizer_name' => 'Alex Organizer',
                'organizer_email' => 'organizer@example.com',
                'event_url' => $baseUrl . '/events/sample-event',
                'sign_up_url' => $baseUrl . '/sign-up',
                'unsubscribe_url' => $baseUrl . '/unsubscribe',
                'app_name' => $appName,
                'is_curated' => $isCurated,
            ],
            'ticket_sale_purchaser' => [
                'event_name' => 'Sample Event',
                'event_date' => 'June 1, 2024 at 8:00 PM',
                'ticket_quantity' => 2,
                'event_url' => $baseUrl . '/events/sample-event',
                'ticket_view_url' => $baseUrl . '/tickets/sample-order',
                'buyer_name' => 'Jamie Attendee',
                'buyer_email' => 'jamie@example.com',
                'order_reference' => '12345',
                'app_name' => $appName,
            ],
            'ticket_sale_organizer' => [
                'event_name' => 'Sample Event',
                'event_date' => 'June 1, 2024 at 8:00 PM',
                'ticket_quantity' => 2,
                'amount_total' => '98.00 USD',
                'buyer_name' => 'Jamie Attendee',
                'buyer_email' => 'jamie@example.com',
                'event_url' => $baseUrl . '/events/sample-event',
                'order_reference' => '12345',
                'app_name' => $appName,
            ],
            'ticket_reminder_purchaser' => [
                'event_name' => 'Sample Event',
                'event_date' => 'June 1, 2024 at 8:00 PM',
                'ticket_quantity' => 2,
                'amount_total' => '98.00 USD',
                'event_url' => $baseUrl . '/events/sample-event',
                'ticket_view_url' => $baseUrl . '/tickets/sample-order',
                'order_reference' => '12345',
                'app_name' => $appName,
                'reminder_interval_hours' => 12,
                'payment_instructions_section' => "Payment Instructions:\n\nPay at the door\n",
            ],
            'ticket_timeout_purchaser' => [
                'event_name' => 'Sample Event',
                'event_date' => 'June 1, 2024 at 8:00 PM',
                'ticket_quantity' => 2,
                'amount_total' => '98.00 USD',
                'event_url' => $baseUrl . '/events/sample-event',
                'ticket_view_url' => $baseUrl . '/tickets/sample-order',
                'order_reference' => '12345',
                'app_name' => $appName,
                'expire_after_hours' => 2,
            ],
            'ticket_timeout_organizer' => [
                'event_name' => 'Sample Event',
                'event_date' => 'June 1, 2024 at 8:00 PM',
                'ticket_quantity' => 2,
                'amount_total' => '98.00 USD',
                'buyer_name' => 'Jamie Attendee',
                'buyer_email' => 'jamie@example.com',
                'event_url' => $baseUrl . '/events/sample-event',
                'ticket_view_url' => $baseUrl . '/events/sample-event?tickets=true',
                'order_reference' => '12345',
                'app_name' => $appName,
                'expire_after_hours' => 2,
            ],
            'ticket_cancelled_purchaser' => [
                'event_name' => 'Sample Event',
                'event_date' => 'June 1, 2024 at 8:00 PM',
                'ticket_quantity' => 2,
                'amount_total' => '98.00 USD',
                'event_url' => $baseUrl . '/events/sample-event',
                'ticket_view_url' => $baseUrl . '/tickets/sample-order',
                'buyer_name' => 'Jamie Attendee',
                'buyer_email' => 'jamie@example.com',
                'order_reference' => '12345',
                'app_name' => $appName,
            ],
            'ticket_cancelled_organizer' => [
                'event_name' => 'Sample Event',
                'event_date' => 'June 1, 2024 at 8:00 PM',
                'ticket_quantity' => 2,
                'amount_total' => '98.00 USD',
                'buyer_name' => 'Jamie Attendee',
                'buyer_email' => 'jamie@example.com',
                'event_url' => $baseUrl . '/events/sample-event',
                'ticket_view_url' => $baseUrl . '/events/sample-event?tickets=true',
                'order_reference' => '12345',
                'app_name' => $appName,
            ],
            'ticket_paid_purchaser' => [
                'event_name' => 'Sample Event',
                'event_date' => 'June 1, 2024 at 8:00 PM',
                'amount_total' => '98.00 USD',
                'ticket_view_url' => $baseUrl . '/tickets/sample-order',
                'order_reference' => '12345',
                'app_name' => $appName,
                'wallet_links_markdown' => '[Add to Apple Wallet](' . $baseUrl . '/wallet/apple)' . "\n\n" . '[Save to Google Wallet](' . $baseUrl . '/wallet/google)',
            ],
            'ticket_paid_organizer' => [
                'event_name' => 'Sample Event',
                'event_date' => 'June 1, 2024 at 8:00 PM',
                'amount_total' => '98.00 USD',
                'buyer_name' => 'Jamie Attendee',
                'buyer_email' => 'jamie@example.com',
                'event_url' => $baseUrl . '/events/sample-event',
                'order_reference' => '12345',
                'app_name' => $appName,
            ],
            default => [],
        };
    }

    protected function replacePlaceholders(string $template, array $data, bool $escape = true): string
    {
        if ($template === '') {
            return '';
        }

        $search = [];
        $replace = [];

        foreach ($data as $key => $value) {
            if (is_array($value) || (is_object($value) && !method_exists($value, '__toString'))) {
                continue;
            }

            $search[] = ':' . $key;
            $stringValue = (string) $value;
            $replace[] = $escape ? e($stringValue) : $stringValue;
        }

        return str_replace($search, $replace, $template);
    }

    protected function prepareValue($value, bool $multiline = false): string
    {
        $value = is_string($value) ? $value : '';

        return $multiline ? rtrim($value) : trim($value);
    }

    protected function boolFromStored($value, bool $default): bool
    {
        if ($value === null) {
            return $default;
        }

        if (is_bool($value)) {
            return $value;
        }

        $normalized = strtolower((string) $value);

        return in_array($normalized, ['1', 'true', 'yes', 'on'], true);
    }

    protected function boolToStored($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_string($value)) {
            return in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true);
        }

        return (bool) $value;
    }
}
