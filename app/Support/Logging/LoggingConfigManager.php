<?php

namespace App\Support\Logging;

use App\Models\Setting;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;

class LoggingConfigManager
{
    /**
     * @var array<string>
     */
    private const CHANNELS_WITH_LEVEL = [
        'single',
        'daily',
        'slack',
        'papertrail',
        'stderr',
        'syslog',
        'syslog_server',
        'errorlog',
    ];

    /**
     * Apply logging configuration from the database if the settings table exists.
     */
    public static function applyFromDatabase(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        $loggingSettings = Setting::forGroup('logging');

        if (empty($loggingSettings)) {
            return;
        }

        static::apply($loggingSettings);
    }

    /**
     * Apply the provided logging configuration to the runtime config array.
     */
    public static function apply(array $settings): void
    {
        $normalized = static::normalizeSettings($settings);

        if (Arr::exists($normalized, 'syslog_host')) {
            config(['logging.channels.syslog_server.handler_with.host' => $normalized['syslog_host']]);
        }

        if (Arr::exists($normalized, 'syslog_port')) {
            config(['logging.channels.syslog_server.handler_with.port' => $normalized['syslog_port']]);
        }

        if (Arr::exists($normalized, 'level')) {
            foreach (self::CHANNELS_WITH_LEVEL as $channel) {
                config(["logging.channels.{$channel}.level" => $normalized['level']]);
            }
        }
    }

    /**
     * Retrieve the list of supported log levels keyed by their normalized value.
     *
     * @return array<string, string>
     */
    public static function availableLevels(): array
    {
        return [
            'debug' => __('messages.log_level_debug'),
            'info' => __('messages.log_level_info'),
            'notice' => __('messages.log_level_notice'),
            'warning' => __('messages.log_level_warning'),
            'error' => __('messages.log_level_error'),
            'critical' => __('messages.log_level_critical'),
            'alert' => __('messages.log_level_alert'),
            'emergency' => __('messages.log_level_emergency'),
        ];
    }

    /**
     * Normalize raw settings into a consistent format for application.
     */
    protected static function normalizeSettings(array $settings): array
    {
        $normalized = [];

        if (Arr::exists($settings, 'syslog_host')) {
            $host = is_string($settings['syslog_host']) ? trim($settings['syslog_host']) : null;
            $normalized['syslog_host'] = $host !== '' ? $host : null;
        }

        if (Arr::exists($settings, 'syslog_port')) {
            $port = $settings['syslog_port'];

            if ($port === '' || $port === null) {
                $normalized['syslog_port'] = null;
            } else {
                $normalized['syslog_port'] = (int) $port;
            }
        }

        if (Arr::exists($settings, 'level')) {
            $defaultLevel = config('logging.channels.single.level', 'debug');
            $normalized['level'] = LogLevelNormalizer::normalize($settings['level'], $defaultLevel);
        }

        return array_filter(
            $normalized,
            static fn ($value) => $value !== null
        );
    }
}
