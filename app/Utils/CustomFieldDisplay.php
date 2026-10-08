<?php

namespace App\Utils;

use App\Models\Event;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Support\Str;

/**
 * An event's custom field answers, made ready to print: the field's label, and the answer as a
 * person reads it (a date as a date, a switch as yes or no, a multiselect as its choices).
 *
 * Two readers, with opposite rules:
 *  - a visitor to the event page sees only the fields their owner ticked "On event page", never a
 *    private one, and nothing below Pro (forEventPage());
 *  - the owner sees every answer to their request form, private ones too: on the Requests tab and
 *    in the email that says a request has arrived (forRequest()).
 *
 * Both read the answers against the schedule whose form WROTE them. Field keys collide across
 * schedules, every schedule's first field being new_0, so a venue's "Room" read against an act's
 * definitions is printed under the act's unrelated label. The public page asks
 * Event::customFieldValuesBelongTo(), which fails closed on a row that records nobody. The
 * owner's side is looser for rows from before answers recorded their schedule (forRequest() says
 * which, and why).
 */
class CustomFieldDisplay
{
    /**
     * Longest answer an EMAIL prints in one piece. A text field accepts 5,000 characters, and a
     * request form's answers are typed by whoever found the form. A page prints an answer whole.
     */
    public const MAIL_ANSWER_LIMIT = 600;

    /**
     * What the public event page prints, whichever schedule's page it is: the room an event is in
     * is the same fact on the venue's page, the act's and a curator's. The fields and the choice
     * to show them are those of the schedule whose form wrote the answers.
     *
     * $page is the schedule whose page is being read, and the language IT is being read in is
     * the one a label is wanted in. Not "translated: yes or no": a field's name_en is in its own
     * schedule's second language, which need not be the page's (Role::displayLanguageCode()
     * tells of the he-to-en curator showing an en-to-he venue's event).
     *
     * @return list<array{key: string, label: string, type: string, value: string, values: list<string>, public: bool}>
     */
    public static function forEventPage(Event $event, ?Role $page = null): array
    {
        $owner = self::eventPageOwner($event);

        if (! $owner) {
            return [];
        }

        $translated = ($owner->translation_language_code ?: 'en') === ($page ?? $owner)->displayLanguageCode();

        return self::rows($owner, self::eventPageFields($owner), (array) $event->custom_field_values, forGuest: true, translated: $translated);
    }

    /**
     * How many fields the event's schedule has put on its event pages, whatever this event
     * answered. The page asks so that a schedule's events all lay the row out the same way: with
     * two fields ticked, an event that answered only one must not print it in the larger form
     * the event before it did not have.
     */
    public static function eventPageFieldCount(Event $event): int
    {
        $owner = self::eventPageOwner($event);

        return $owner ? count(self::eventPageFields($owner)) : 0;
    }

    /**
     * The schedule whose form wrote the event's answers, when it may print them. Four things,
     * each of which has to hold:
     *  - the answers are recorded as that schedule's (Event::customFieldValuesBelongTo(), which
     *    fails closed on a row that records nobody);
     *  - it is on the event AND has accepted it. A request's answers are keyed to the schedule
     *    that was asked, from the moment of asking: without this an act that sends a curator a
     *    request has the curator's "Room" on its own public page at once, and still has it
     *    after the curator declines;
     *  - it has not been deleted;
     *  - it is Pro, the plan custom fields belong to.
     */
    private static function eventPageOwner(Event $event): ?Role
    {
        if (empty($event->custom_field_values)) {
            return null;
        }

        // A narrowed select that left out either ownership column reads as "nobody's": the same
        // refusal as Event::publicCustomFieldValuesFor().
        $attributes = $event->getAttributes();
        if (! array_key_exists('creator_role_id', $attributes) || ! array_key_exists('custom_field_values_role_id', $attributes)) {
            return null;
        }

        $owner = $event->roles->first(fn (Role $role) => $event->customFieldValuesBelongTo($role));

        return $owner && $owner->pivot?->is_accepted && ! $owner->is_deleted && $owner->isPro() ? $owner : null;
    }

    /** @return array<string, array<string, mixed>> */
    private static function eventPageFields(Role $owner): array
    {
        return array_filter(
            $owner->getEventCustomFields(),
            fn ($field) => is_array($field) && Role::isEventCustomFieldOnEventPage($field)
        );
    }

    /**
     * The answers to $role's request form on a request that is waiting for it. Owner-only, so
     * private fields are included. Nothing for an appointment booking (its questions are the
     * appointment type's own) or for answers recorded as another schedule's form's.
     *
     * A row that records NO schedule is from before the column existed (2026-09-29), and some
     * of those requests are still waiting. It is read as this schedule's when this schedule's
     * form can have written it: the event is its own, or has no recorded maker, or came through a
     * request form. Failing closed, as the public page does, would take answers off cards that
     * show them today; reading every such row as this schedule's would print an act's own
     * answers under this schedule's labels.
     *
     * @return list<array{key: string, label: string, type: string, value: string, values: list<string>, public: bool}>
     */
    public static function forRequest(Event $event, Role $role, ?int $limit = null): array
    {
        if ($event->appointment_type_id) {
            return [];
        }

        $keyedBy = $event->custom_field_values_role_id;
        $maker = $event->creator_role_id;
        $mine = $keyedBy !== null
            ? (int) $keyedBy === (int) $role->id
            : ($maker === null || (int) $maker === (int) $role->id || $event->is_guest_submission);

        if (! $mine) {
            return [];
        }

        return self::rows($role, $role->getRequestFormCustomFields(), $event->getCustomFieldValues(), forGuest: false, limit: $limit);
    }

    /**
     * @param  array<string, mixed>  $fields  the definitions to print, in the owner's order
     * @param  array<string, mixed>  $values  the event's answers, keyed like $fields
     * @param  int|null  $limit  cut a text answer to this many characters (an email); null prints it whole
     * @param  bool|null  $translated  a guest's page only: whether labels and options are wanted in $role's second language (null asks $role's own translate toggle)
     * @return list<array{key: string, label: string, type: string, value: string, values: list<string>, public: bool}>
     */
    public static function rows(Role $role, array $fields, array $values, bool $forGuest, ?int $limit = null, ?bool $translated = null): array
    {
        $rows = [];
        $translated = $forGuest ? ($translated ?? showing_translation($role)) : false;

        foreach ($fields as $key => $field) {
            $answer = $values[$key] ?? null;
            if (! is_array($field) || ! is_scalar($answer) || trim((string) $answer) === '') {
                continue;
            }

            $answer = trim((string) $answer);
            $type = $field['type'] ?? 'string';
            $parts = [];

            if ($type === 'switch') {
                $on = filter_var($answer, FILTER_VALIDATE_BOOLEAN);
                // The form posts 0 for a switch nobody touched, so every event saved since the
                // field was added says "no". To an owner reading a request that is an answer; on
                // a public page it would be a claim nobody made ("Step-free access: No").
                if (! $on && $forGuest) {
                    continue;
                }
                $value = $on ? __('messages.yes') : __('messages.no');
            } elseif ($type === 'date') {
                $value = self::date($answer);
            } elseif (in_array($type, ['dropdown', 'multiselect'], true)) {
                $options = $translated ? self::translatedOptions($field) : [];
                $parts = $type === 'multiselect'
                    ? array_values(array_filter(array_map('trim', explode(',', $answer)), fn ($part) => $part !== ''))
                    : [$answer];
                $parts = array_map(fn ($part) => $options[$part] ?? $part, $parts);
                $value = implode(', ', $parts);
            } else {
                $value = $limit ? Str::limit($answer, $limit) : $answer;
            }

            // A guest's label follows the page being read (see forEventPage()); an owner's follows
            // their own language, which is Role::customFieldLabel()'s rule for the admin side.
            $label = $forGuest
                ? (($translated && filled($field['name_en'] ?? null)) ? $field['name_en'] : ($field['name'] ?? (string) $key))
                : $role->customFieldLabel($field, (string) $key);

            $rows[] = [
                'key' => (string) $key,
                'label' => (string) $label,
                'type' => $type,
                'value' => $value,
                'values' => $parts,
                // Whether this answer is one the event page prints (once the schedule has
                // accepted the event): for the owner reading a stranger's answer before they do.
                'public' => Role::isEventCustomFieldOnEventPage($field) && ($type !== 'switch' || filter_var($answer, FILTER_VALIDATE_BOOLEAN)),
            ];
        }

        return $rows;
    }

    /**
     * A stored Y-m-d as a date in the language being read, in that language's own order ("12.
     * Okt 2026", not "Okt 12, 2026"); anything else as it was typed.
     */
    private static function date(string $answer): string
    {
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $answer)) {
            return $answer;
        }

        try {
            return Carbon::createFromFormat('!Y-m-d', $answer)->locale(app()->getLocale())->isoFormat('ll');
        } catch (\Throwable $e) {
            return $answer;
        }
    }

    /**
     * Option => its translation, for a visitor reading the translated page. The same pairing the
     * schedule's filter uses (role/partials/calendar): by position, and only when the two lists
     * are the same length, since a translation that came back short would name the wrong option.
     *
     * @return array<string, string>
     */
    private static function translatedOptions(array $field): array
    {
        if (empty($field['options_en'])) {
            return [];
        }

        $options = Role::customFieldOptions($field);
        $translated = array_values(array_filter(array_map('trim', explode(',', (string) $field['options_en']))));

        return count($options) === count($translated) ? array_combine($options, $translated) : [];
    }
}
