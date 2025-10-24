<?php

namespace App\Support\Logging;

use App\Models\Setting;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Monolog\Handler\NullHandler;

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

    private static ?string $originalDefaultChannel = null;

    /**
     * @var array<int, string>|null
     */
    private static ?array $originalStackChannels = null;

    private static ?array $originalRoleDebugChannel = null;

    private static bool $loggingDisabled = false;

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

        static::captureOriginalConfiguration();

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

        if (Arr::exists($normalized, 'disabled')) {
            if ($normalized['disabled']) {
                static::disableLogging();
            } else {
                static::restoreLogging();
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

        if (Arr::exists($settings, 'disabled')) {
            $disabled = static::toBoolean($settings['disabled']);

            if ($disabled !== null) {
                $normalized['disabled'] = $disabled;
            }
        }

        return array_filter(
            $normalized,
            static fn ($value) => $value !== null
        );
    }

    protected static function captureOriginalConfiguration(): void
    {
        if (self::$originalDefaultChannel === null) {
            self::$originalDefaultChannel = config('logging.default');
        }

        if (self::$originalStackChannels === null) {
            $stackChannels = config('logging.channels.stack.channels');

            if (is_array($stackChannels)) {
                self::$originalStackChannels = $stackChannels;
            }
        }

        if (self::$originalRoleDebugChannel === null) {
            $roleDebug = config('logging.channels.role_debug');

            if (is_array($roleDebug)) {
                self::$originalRoleDebugChannel = $roleDebug;
            }
        }
    }

    protected static function disableLogging(): void
    {
        if (self::$loggingDisabled) {
            return;
        }

        config(['logging.default' => 'null']);
        config(['logging.channels.stack.channels' => ['null']]);

        $roleDebug = self::$originalRoleDebugChannel ?? config('logging.channels.role_debug');

        if (! is_array($roleDebug)) {
            $roleDebug = [];
        }

        $roleDebug['driver'] = 'monolog';
        $roleDebug['handler'] = NullHandler::class;
        $roleDebug['handler_with'] = [];

        unset($roleDebug['path']);

        config(['logging.channels.role_debug' => $roleDebug]);

        self::$loggingDisabled = true;
    }

    protected static function restoreLogging(): void
    {
        if (! self::$loggingDisabled) {
            return;
        }

        $defaultChannel = self::$originalDefaultChannel ?? 'stack';
        config(['logging.default' => $defaultChannel]);

        if (self::$originalStackChannels !== null) {
            config(['logging.channels.stack.channels' => self::$originalStackChannels]);
        }

        if (self::$originalRoleDebugChannel !== null) {
            config(['logging.channels.role_debug' => self::$originalRoleDebugChannel]);
        }

        self::$loggingDisabled = false;
    }

    protected static function toBoolean(mixed $value): ?bool
    {
        if ($value === null) {
            return null;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (bool) $value;
        }

        $normalized = strtolower(trim((string) $value));

        if ($normalized === '') {
            return null;
        }

        if (in_array($normalized, ['1', 'true', 'yes', 'on'], true)) {
            return true;
        }

        if (in_array($normalized, ['0', 'false', 'no', 'off'], true)) {
            return false;
        }

        return null;
    }
}
