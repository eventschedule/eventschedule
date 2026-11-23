<?php

namespace App\Utils;

/**
 * The one place a schedule's TYPE is turned into words a person reads.
 *
 * `talent` stays the type in code, the database and the API, but no screen, email or page says
 * "talent": the standard schedule is an "Event schedule", with Venue and Curator as the two
 * alternatives. Before this, some fifteen views each did `__('messages.'.$type)`, which printed
 * "Talent", and the same key also titled the performers on an event page, so the type's name
 * could not change without the heading changing with it.
 *
 * An install that renamed `messages.talent` itself (the selfhost guide's own example renamed
 * "Talent" to "Artist") keeps its word: an operator's override of the old key wins over the new
 * default, here and in the lineup heading, so an update never silently undoes it.
 */
class ScheduleTypeLabel
{
    public const TYPES = ['talent', 'venue', 'curator'];

    /**
     * "Event schedule", "Venue" or "Curator"; any other value is returned as it is.
     */
    public static function for(?string $type): string
    {
        return match ($type) {
            'talent' => self::operatorWord('talent') ?? __('messages.schedule_type_talent'),
            'venue' => __('messages.venue'),
            'curator' => __('messages.curator'),
            default => (string) $type,
        };
    }

    /**
     * The type for a line that only describes a schedule (under its name in a list, a
     * breadcrumb): the standard schedule needs no word there, so it gets none.
     */
    public static function describe(?string $type): string
    {
        if ($type === 'talent' && self::operatorWord('talent') === null) {
            return '';
        }

        return self::for($type);
    }

    /**
     * The heading over the people an event names ("Lineup"), honouring an operator's own word
     * for `talent` the way the heading always did.
     */
    public static function lineup(): string
    {
        return self::operatorWord('talent') ?? __('messages.lineup');
    }

    /**
     * The plural of lineup() for places that name the people themselves (a logo wall's label).
     */
    public static function lineupPlural(): string
    {
        return self::operatorWord('talents') ?? __('messages.lineup');
    }

    /**
     * What the operator set `messages.{$key}` to for the current locale, or null.
     */
    public static function operatorWord(string $key): ?string
    {
        $value = self::overridesFor(app()->getLocale())[$key] ?? null;

        return is_string($value) && trim($value) !== '' ? $value : null;
    }

    /**
     * Read once per locale per request: once() keys on this method's arguments, so a mail
     * rendered in the schedule's language reads that language's file.
     */
    private static function overridesFor(string $locale): array
    {
        return once(fn () => operator_lang_overrides($locale, 'messages'));
    }
}
