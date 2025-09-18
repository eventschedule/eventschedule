<?php

namespace App\Support;

use App\Models\Setting;

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
        $values = [];

        foreach ($this->templates as $key => $config) {
            if (!is_array($config) || !array_key_exists($key, $input) || !is_array($input[$key])) {
                continue;
            }

            foreach (['subject', 'subject_curated', 'body', 'body_curated'] as $field) {
                if (array_key_exists($field, $config) && array_key_exists($field, $input[$key])) {
                    $values["{$key}_{$field}"] = $this->prepareValue(
                        $input[$key][$field],
                        in_array($field, ['body', 'body_curated'], true)
                    );
                }
            }
        }

        if (!empty($values)) {
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

        return $template;
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
}
