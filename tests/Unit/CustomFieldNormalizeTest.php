<?php

namespace Tests\Unit;

use App\Models\Role;
use Tests\TestCase;

/**
 * The three request forms disagree on the shape of a multiselect answer, and AI parsing can invent
 * a value that is not on the option list. normalizeCustomFieldValues() reconciles both before the
 * validation rules run; sanitizeCustomFieldValues() is the shared whitelist applied before storage.
 */
class CustomFieldNormalizeTest extends TestCase
{
    private function role(): Role
    {
        return new Role([
            'event_custom_fields' => [
                'gear' => ['name' => 'Gear', 'type' => 'multiselect', 'options' => 'projector, dj controller, tablesoccer'],
                'size' => ['name' => 'Size', 'type' => 'dropdown', 'options' => 'S, M, L'],
                'note' => ['name' => 'Note', 'type' => 'string'],
            ],
        ]);
    }

    public function test_a_comma_joined_multiselect_string_becomes_an_array(): void
    {
        // This is what the AI import page posts.
        $values = $this->role()->normalizeCustomFieldValues(['gear' => 'projector, tablesoccer']);

        $this->assertSame(['projector', 'tablesoccer'], $values['gear']);
    }

    public function test_an_array_multiselect_passes_through(): void
    {
        // This is what the booking form and the guest submit page post.
        $values = $this->role()->normalizeCustomFieldValues(['gear' => ['projector', 'dj controller']]);

        $this->assertSame(['projector', 'dj controller'], $values['gear']);
    }

    public function test_multiselect_values_outside_the_option_list_are_dropped(): void
    {
        $values = $this->role()->normalizeCustomFieldValues(['gear' => ['projector', 'smoke machine']]);

        $this->assertSame(['projector'], $values['gear']);
    }

    public function test_an_off_list_dropdown_value_is_cleared_rather_than_left_to_fail(): void
    {
        // Vue renders such a select blank while keeping the value, so leaving it would reject a
        // field the guest sees as empty.
        $values = $this->role()->normalizeCustomFieldValues(['size' => 'XL']);

        $this->assertSame('', $values['size']);
    }

    public function test_a_valid_dropdown_value_and_free_text_are_untouched(): void
    {
        $values = $this->role()->normalizeCustomFieldValues(['size' => 'M', 'note' => 'Anything, really']);

        $this->assertSame('M', $values['size']);
        $this->assertSame('Anything, really', $values['note']);
    }

    public function test_keys_with_no_matching_field_are_left_alone(): void
    {
        // guestImport() intersects against the request-form fields before calling this, so an
        // unknown key here is not the normalizer's problem to police.
        $values = $this->role()->normalizeCustomFieldValues(['unknown' => 'x']);

        $this->assertSame(['unknown' => 'x'], $values);
    }

    public function test_sanitize_collapses_a_multiselect_to_the_stored_string(): void
    {
        $values = $this->role()->sanitizeCustomFieldValues([
            'gear' => ['projector', 'smoke machine'],
            'size' => 'XL',
            'note' => '',
        ]);

        // Invalid dropdown dropped, empty dropped, multiselect whitelisted and joined.
        $this->assertSame(['gear' => 'projector'], $values);
    }

    public function test_sanitize_tolerates_a_non_array_input(): void
    {
        // input() returns a present-but-null key as null rather than the default.
        $this->assertSame([], $this->role()->sanitizeCustomFieldValues(null));
    }

    public function test_a_subset_drops_values_for_fields_outside_it(): void
    {
        // The request forms pass the request-form subset, so a crafted post cannot set a field the
        // owner deliberately kept off the form.
        $role = $this->role();
        $subset = ['note' => $role->getEventCustomFields()['note']];

        $values = $role->sanitizeCustomFieldValues(['size' => 'M', 'note' => 'kept'], $subset);

        $this->assertSame(['note' => 'kept'], $values);
    }

    public function test_no_subset_keeps_the_historic_pass_through(): void
    {
        // The admin save path (EventRepo) passes null and must keep behaving as before.
        $values = $this->role()->sanitizeCustomFieldValues(['unknown' => 'kept']);

        $this->assertSame(['unknown' => 'kept'], $values);
    }

    /**
     * session('translate') is set on guest pages and survives for the session, so an owner who
     * viewed their own translated guest page must not then see English labels in the admin portal.
     */
    public function test_the_admin_label_ignores_the_guest_translate_flag(): void
    {
        $role = new Role(['language_code' => 'de', 'translation_language_code' => 'en']);
        $field = ['name' => 'Ausstattung', 'name_en' => 'Gear'];

        session()->put('translate', true);
        app()->setLocale('de');

        $this->assertSame('Ausstattung', $role->customFieldLabel($field));
        $this->assertSame('Gear', $role->customFieldLabel($field, null, forGuest: true));
    }

    public function test_the_admin_label_follows_the_admin_ui_locale(): void
    {
        $role = new Role(['language_code' => 'de', 'translation_language_code' => 'en']);
        $field = ['name' => 'Ausstattung', 'name_en' => 'Gear'];

        session()->forget('translate');

        app()->setLocale('en');
        $this->assertSame('Gear', $role->customFieldLabel($field));

        app()->setLocale('de');
        $this->assertSame('Ausstattung', $role->customFieldLabel($field));

        // The guest rule is the toggle, not the admin's locale.
        $this->assertSame('Ausstattung', $role->customFieldLabel($field, null, forGuest: true));
    }

    public function test_request_form_fields_default_to_shown_when_the_flag_is_absent(): void
    {
        $role = new Role([
            'event_custom_fields' => [
                'legacy' => ['name' => 'Legacy', 'type' => 'string'],
                'shown' => ['name' => 'Shown', 'type' => 'string', 'show_on_request' => true],
                'hidden' => ['name' => 'Hidden', 'type' => 'string', 'show_on_request' => false],
            ],
        ]);

        $this->assertSame(['legacy', 'shown'], array_keys($role->getRequestFormCustomFields()));
    }
}
