<?php

namespace App\Services;

use App\Models\Event;
use Illuminate\Http\Request;

/**
 * A person's corrections to an event's automatic translation, from the event form's Translation
 * row (and the API's `translations`). Called by EventRepo::saveEvent() after the fill and before
 * the save, so the saving hook sees the correction and the original together.
 *
 * Three things a request can say, each only for the fields it names, so a request that says
 * nothing (every import, the guest forms, an API update that leaves translations out) changes
 * nothing:
 *   translations[field] = text   the correction; the field is marked as a person's
 *   translations[field] = ''     back to the automatic translation; app:translate redoes it
 *   translations_keep[] = field  "Keep mine": the correction still fits a changed original
 * translation_basis[field] carries the hash of the original a COPIED correction was made against
 * (EventController::create() from a clone), so a copy whose original was edited first says so.
 */
class EventTranslationEdits
{
    /** Widths of the columns the corrections are written to (events.name_en is varchar(255)). */
    public const MAX_LENGTH = [
        'name' => 255,
        'short_description' => 500,
        'description' => 16000,
    ];

    /** The validation rules, for the web form's requests and the API. */
    public static function rules(): array
    {
        $rules = [
            'translations' => ['nullable', 'array'],
            'translations_keep' => ['nullable', 'array'],
            'translations_keep.*' => ['string', 'in:'.implode(',', Event::HAND_TRANSLATABLE_FIELDS)],
            'translation_basis' => ['nullable', 'array'],
            'translation_basis.*' => ['nullable', 'string', 'regex:/^[0-9a-f]{40}$/'],
        ];

        foreach (self::MAX_LENGTH as $field => $max) {
            $rules['translations.'.$field] = ['nullable', 'string', 'max:'.$max];
        }

        return $rules;
    }

    public static function apply(Event $event, Request $request): void
    {
        $edits = $request->input('translations');
        $keep = $request->input('translations_keep');
        $basis = $request->input('translation_basis');

        $edits = is_array($edits) ? $edits : [];
        $keep = is_array($keep) ? $keep : [];
        $basis = is_array($basis) ? $basis : [];

        foreach (Event::HAND_TRANSLATABLE_FIELDS as $field) {
            if (array_key_exists($field, $edits)) {
                $text = is_string($edits[$field]) ? trim(str_replace(["\r\n", "\r"], "\n", $edits[$field])) : '';

                if ($text === '') {
                    // Only a translation there is to give back: an event with nothing stored for
                    // the field is already waiting for app:translate.
                    if ($event->translationIsManual($field) || $event->{$field.'_en'} !== null) {
                        $event->useAutomaticTranslation($field);
                    }

                    continue;
                }

                // Nothing to translate, so nothing to correct.
                if (trim((string) $event->{$field}) === '') {
                    continue;
                }

                $text = mb_substr($text, 0, self::MAX_LENGTH[$field]);

                // Sent back unchanged from a correction that still fits: no new date, no new mark.
                if ($event->translationIsManual($field) && $text === $event->{$field.'_en'} && ! $event->translationIsStale($field) && ! isset($basis[$field])) {
                    continue;
                }

                $event->{$field.'_en'} = $text;
                $event->markTranslationManual($field, is_string($basis[$field] ?? null) ? $basis[$field] : null);

                continue;
            }

            if (in_array($field, $keep, true)) {
                $event->keepTranslationOverride($field);
            }
        }
    }
}
