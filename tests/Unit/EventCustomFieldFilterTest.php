<?php

namespace Tests\Unit;

use App\Models\Role;
use App\Utils\CustomFieldUtils;
use Tests\TestCase;

/**
 * "Show as filter" on an event custom field. The flag was added after every public dropdown and
 * multiselect had already been a calendar filter, so an absent flag has to keep them one - and
 * keep text fields, which never were, off.
 */
class EventCustomFieldFilterTest extends TestCase
{
    public function test_an_absent_flag_keeps_option_lists_on_and_text_off(): void
    {
        $this->assertTrue(Role::isEventCustomFieldFilter(['type' => 'dropdown', 'options' => 'A,B']));
        $this->assertTrue(Role::isEventCustomFieldFilter(['type' => 'multiselect', 'options' => 'A,B']));
        $this->assertFalse(Role::isEventCustomFieldFilter(['type' => 'string']));
        // A field with no type at all is text, which is what the editor saves by default.
        $this->assertFalse(Role::isEventCustomFieldFilter([]));
    }

    public function test_the_flag_overrides_the_default_either_way(): void
    {
        $this->assertTrue(Role::isEventCustomFieldFilter(['type' => 'string', 'filter' => true]));
        $this->assertFalse(Role::isEventCustomFieldFilter(['type' => 'dropdown', 'options' => 'A,B', 'filter' => false]));
    }

    public function test_a_private_field_is_never_a_filter(): void
    {
        $this->assertFalse(Role::isEventCustomFieldFilter(['type' => 'string', 'filter' => true, 'private' => true]));
        $this->assertFalse(Role::isEventCustomFieldFilter(['type' => 'dropdown', 'options' => 'A,B', 'private' => true]));
    }

    public function test_types_that_cannot_filter_ignore_the_flag(): void
    {
        foreach (['multiline_string', 'switch', 'date'] as $type) {
            $this->assertFalse(Role::isEventCustomFieldFilter(['type' => $type, 'filter' => true]), $type);
        }
    }

    public function test_an_option_list_with_no_options_is_not_a_filter(): void
    {
        $this->assertFalse(Role::isEventCustomFieldFilter(['type' => 'dropdown', 'options' => ' , ', 'filter' => true]));
        // Text has no options by nature, so the same check must not apply to it.
        $this->assertTrue(Role::isEventCustomFieldFilter(['type' => 'string', 'options' => '', 'filter' => true]));
    }

    public function test_filter_params_keep_only_well_formed_custom_n_strings(): void
    {
        $this->assertSame(
            ['custom_1' => 'Room A', 'custom_10' => 'x'],
            CustomFieldUtils::filterParams([
                'custom_1' => '  Room A ',
                'custom_10' => 'x',
                'custom_11' => 'out of range',
                'custom_0' => 'out of range',
                'custom_2' => ['array'],
                'custom_3' => '   ',
                'category' => '4',
            ])
        );

        $long = str_repeat('a', CustomFieldUtils::FILTER_PARAM_MAX_LENGTH + 50);
        $this->assertSame(
            CustomFieldUtils::FILTER_PARAM_MAX_LENGTH,
            mb_strlen(CustomFieldUtils::filterParams(['custom_1' => $long])['custom_1'])
        );
    }
}
