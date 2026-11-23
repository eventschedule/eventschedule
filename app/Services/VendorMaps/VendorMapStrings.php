<?php

namespace App\Services\VendorMaps;

/**
 * The vendor map screens' own words, handed to a Vue island as one flat object. Each is a
 * top-level key `vm_{name}` in messages.php (check_translations.php walks the top level only).
 */
class VendorMapStrings
{
    public const DESIGNER = [
        'back_to_tab', 'tools', 'add_spot', 'add_row', 'number_in_order', 'add_landmark', 'landmark_entrance',
        'landmark_restroom', 'landmark_parking', 'landmark_stage', 'landmark_text', 'you_are_here', 'undo',
        'saving', 'save', 'saved', 'found_lead', 'check_names', 'looks_right_save', 'revision_clash',
        'save_mine_anyway', 'numbering_help', 'done', 'here_help', 'cancel', 'map_label', 'zoom', 'zoom_in',
        'zoom_out', 'fit', 'trace_photo', 'picture_public', 'photo_opacity', 'close', 'number', 'name_on_spot', 'name_placeholder',
        'name_locked', 'category', 'no_category', 'area', 'no_area', 'new_area', 'width', 'depth', 'turn',
        'turn_left', 'turn_right', 'move_to', 'duplicate', 'remove', 'move_help', 'label', 'check_names_lead',
        'check_this_one', 'clear_name', 'areas', 'color', 'count_line', 'tap_to_edit', 'empty_lead', 'how_many',
        'first_number', 'numbering_direction', 'from_right', 'from_left', 'add', 'spot_removed', 'name_cleared',
        'removed', 'area_default', 'save_failed', 'saved_renumbered', 'saved_toast', 'replace_with_draft',
        'found_summary', 'drag_areas', 'named_count', 'need_names', 'has_name', 'needs_name', 'draft_not_saved',
    ];

    public const ADMIN = [
        'setup_title', 'setup_lead', 'kind_question', 'event_question', 'use_event', 'new_event', 'market_name',
        'days_question', 'from_time', 'to_time', 'every_week', 'first_day', 'last_day', 'dates_question', 'continue',
        'photos_title', 'photos_lead', 'upload_map', 'tab', 'area_name', 'area_placeholder', 'add_area', 'remove_area', 'mapping_area', 'areas_lead', 'area_n', 'tip_high', 'tip_row', 'tip_signs', 'add_photo', 'take_photos', 'upload_photos',
        'make_my_map', 'draw_myself', 'or', 'photos_honest', 'stage_finding', 'stage_reading', 'stage_placing',
        'reading_wait', 'can_leave', 'map_ready', 'continue_to_map', 'try_again', 'ai_failed', 'uploading',
        'photo_too_big', 'heic', 'max_photos', 'notes_label', 'notes_placeholder', 'no_ai', 'upgrade_ai',
        'for_shoppers', 'readiness', 'see_what_shoppers_see', 'print_sign', 'copy_link', 'copied', 'embed',
        'embed_help', 'send_whatsapp', 'whatsapp_message', 'edit_message', 'vendors_can_join', 'make_new_link',
        'make_new_link_confirm', 'link_off', 'upgrade_link', 'registration_off', 'edit_map', 'redo_with_photos',
        'date', 'today', 'not_coming_count', 'waiting_count', 'here_count', 'free_count', 'cancel_day',
        'cancel_day_title', 'reopen_day', 'vendors_can_join_lead', 'remove', 'reason', 'reason_weather', 'reason_holiday', 'reason_other', 'cancel_day_confirm',
        'cancelled_banner', 'no_market_day', 'review', 'waiting_for_you', 'approve', 'decline', 'decline_note',
        'approve_all', 'approved_toast', 'declined_toast', 'undo', 'two_want', 'name_differs', 'typed_name',
        'name_on_stall', 'every_date', 'on_dates', 'tell_on_whatsapp', 'whatsapp_approved', 'move_to', 'swap',
        'this_week_only', 'rest_of_season', 'move_help', 'not_coming', 'coming_after_all', 'remove_vendor',
        'add_for_today', 'add_for_today_lead', 'name', 'category', 'add', 'cancel', 'close', 'free_spot',
        'nobody_yet', 'list_all', 'all_vendors', 'visit_page', 'phone', 'claim_of', 'n_of_m', 'saving',
        'couldnt_save', 'settings', 'kind', 'event', 'turn_off', 'turn_off_confirm', 'empty_spots', 'search',
        'no_results', 'next_market', 'event_gone', 'choose_event', 'spot_taken', 'zoom_in', 'zoom_out', 'fit', 'you_are_here',
    ];

    /** @return array<string, string> */
    public static function get(array $names): array
    {
        $out = [];
        foreach ($names as $name) {
            $out[$name] = __('messages.vm_'.$name);
        }

        return $out;
    }
}
