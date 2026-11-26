<?php

namespace App\Utils;

use App\Models\Event;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

/**
 * An event's custom field answers over the API (issue #141): `custom_field_values` on POST and PUT
 * /api/events, and the same key on every event the API returns.
 *
 * A caller names a field the way the schedule's settings show it (its name, any case), or by its
 * key. The schedule API does not list the keys (new_0, new_1 ...), so a name is what a caller has.
 *
 * Writing changes only the fields named: an API update carries over what it was not sent (see
 * ApiEventController), so a request that sets "Room" leaves "Age limit" as it was, and null or ''
 * clears one. A value is checked as the event form checks it (Role::getEventCustomFieldValidationRules():
 * an option of a dropdown, a date, a pattern the owner set), except that a field marked required is
 * not demanded: an integration that never knew about a field must not start failing when the owner
 * adds one.
 *
 * Reading returns the answers of the event's OWN schedule's fields, by name, and never a private
 * field: the API answers anybody who may edit the event, which includes a venue's team on an act's
 * event, and the event form never shows them the act's fields either.
 */
class ApiCustomFieldValues
{
    /**
     * The event's answers once $sent is laid over $stored, or the errors that refuse the request.
     *
     * @param  array<string, mixed>  $stored  the answers the event already holds for $role's fields
     * @return array{0: array<string, string>, 1: array<string, list<string>>}
     */
    public static function resolve(Role $role, mixed $sent, array $stored): array
    {
        if (! is_array($sent) || ($sent !== [] && array_is_list($sent))) {
            return [$stored, ['custom_field_values' => ['Must be an object keyed by the custom field\'s name.']]];
        }

        $fields = $role->getEventCustomFields();
        $byName = [];
        foreach ($fields as $key => $field) {
            if (is_array($field) && filled($field['name'] ?? null)) {
                $byName[mb_strtolower(trim($field['name']))] ??= (string) $key;
            }
        }

        $values = [];
        $sentAs = [];
        $errors = [];
        foreach ($sent as $name => $value) {
            $key = isset($fields[$name]) && is_array($fields[$name]) ? (string) $name : ($byName[mb_strtolower(trim((string) $name))] ?? null);

            if ($key === null) {
                $errors['custom_field_values.'.$name] = ['This schedule has no custom field with this name.'];

                continue;
            }

            $values[$key] = self::normalize($fields[$key], $value);
            $sentAs[$key] = (string) $name;
        }

        if ($errors) {
            return [$stored, $errors];
        }

        $rules = [];
        foreach ($role->getEventCustomFieldValidationRules(array_intersect_key($fields, $values)) as $attribute => $fieldRules) {
            $rules[$attribute] = array_map(fn ($rule) => $rule === 'required' ? 'nullable' : $rule, (array) $fieldRules);
        }

        $validator = Validator::make(['custom_field_values' => $values], $rules);
        if ($validator->fails()) {
            foreach ($validator->errors()->messages() as $attribute => $messages) {
                $key = explode('.', $attribute)[1] ?? '';
                $errors['custom_field_values.'.($sentAs[$key] ?? $key)] = array_values($messages);
            }

            return [$stored, $errors];
        }

        foreach ($values as $key => $value) {
            if ($value === null) {
                unset($stored[$key]);
            } elseif (is_array($value)) {
                $stored[$key] = implode(', ', $value);
            } elseif (($fields[$key]['type'] ?? '') === 'date') {
                $stored[$key] = Carbon::parse($value)->format('Y-m-d');
            } else {
                $stored[$key] = $value;
            }
        }

        return [$stored, []];
    }

    /**
     * The event's answers as the API returns them: {"Room": "Garden", "Step-free": true}.
     *
     * @return array<string, mixed>
     */
    public static function forApi(Event $event): array
    {
        $owner = $event->creatorRole;
        $values = (array) $event->custom_field_values;

        if (! $owner || ! $values || ! $event->customFieldValuesBelongTo($owner)) {
            return [];
        }

        $out = [];
        foreach ($owner->getEventCustomFields() as $key => $field) {
            $value = $values[$key] ?? null;
            if (! is_array($field) || ! empty($field['private']) || ! is_scalar($value) || trim((string) $value) === '') {
                continue;
            }

            $type = $field['type'] ?? 'string';
            $out[(string) ($field['name'] ?? $key)] = match ($type) {
                'switch' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
                'multiselect' => array_values(array_filter(array_map('trim', explode(',', (string) $value)), fn ($part) => $part !== '')),
                default => (string) $value,
            };
        }

        return $out;
    }

    /** A sent value in the shape the event form's rules read it. */
    private static function normalize(array $field, mixed $value): mixed
    {
        if ($value === null || $value === '' || $value === []) {
            return null;
        }

        return match ($field['type'] ?? 'string') {
            'switch' => match (true) {
                is_bool($value) => $value ? '1' : '0',
                in_array(mb_strtolower((string) (is_scalar($value) ? $value : '')), ['1', 'true', 'yes', 'on'], true) => '1',
                in_array(mb_strtolower((string) (is_scalar($value) ? $value : '')), ['0', 'false', 'no', 'off'], true) => '0',
                default => $value,
            },
            'multiselect' => is_array($value)
                ? array_values(array_map(fn ($part) => is_scalar($part) ? trim((string) $part) : $part, $value))
                : (is_scalar($value) ? array_values(array_filter(array_map('trim', explode(',', (string) $value)), fn ($part) => $part !== '')) : $value),
            default => is_scalar($value) && ! is_bool($value) ? (string) $value : $value,
        };
    }
}
