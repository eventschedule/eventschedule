<?php

namespace App\Enums;

enum ReleaseChannel: string
{
    case Production = 'production';
    case Beta = 'beta';

    public static function default(): self
    {
        return self::Production;
    }

    public static function fromString(?string $value): self
    {
        if ($value === null) {
            return self::default();
        }

        return self::tryFrom(strtolower($value)) ?? self::default();
    }

    public static function values(): array
    {
        return array_map(static fn (self $case) => $case->value, self::cases());
    }

    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }

    public function label(): string
    {
        return match ($this) {
            self::Production => __('messages.release_channel_production'),
            self::Beta => __('messages.release_channel_beta'),
        };
    }

    public function isBeta(): bool
    {
        return $this === self::Beta;
    }
}
