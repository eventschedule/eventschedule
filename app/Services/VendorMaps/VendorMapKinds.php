<?php

namespace App\Services\VendorMaps;

/**
 * The kinds of vendor map and the words each one uses.
 *
 * A kind picks a WHOLE, separately translated sentence ("Find your stall", "Find your booth"),
 * never a noun dropped into a shared sentence: Hebrew nouns carry gender, German and Russian
 * decline them, and a sentence assembled from parts is wrong in most of the twelve languages
 * (Event.php records the same trap for dates). Everything that is the same for every kind is
 * worded neutrally ("number 15") and has one key.
 *
 * Keys are flat, `vm_{kind}_{name}`, because storage/check_translations.php walks the top level
 * of messages.php and a nested array would hide a missing translation.
 */
class VendorMapKinds
{
    public const KINDS = ['market', 'craft_fair', 'flea_market', 'food_festival', 'expo'];

    /** The strings every kind words for itself. */
    public const STRINGS = [
        'kind_name',     // "Farmers market"
        'find_title',    // "Find your stall"
        'this_is_mine',  // "This is my stall"
        'spot',          // "Stall :number"
        'spots_found',   // "We found :count stalls" (plural)
        'none_today',    // "No market today"
        'name_label',    // "Your name at the market"
        'sells_label',   // "What you sell"
        'every_date',    // "Every market day"
        'when_title',    // "When is the market?"
        'people',        // "Vendors"
        'for_people',    // "For your vendors"
        'sign_headline', // "What's at the market today?"
        'share_title',   // "Find your stall at :name"
        'this_week',     // "At the market this week"
        'scan_title',    // "Scan to find any stall" (the entrance sign's QR band)
        'confirm_title', // "Is this your stall?" (the vendor link, over the map with it marked)
        'spots_count',   // ":count stalls" (plural; the designer's side panel)
    ];

    /** Product categories a vendor taps; the AI suggests one. Icons are drawn in Vue. */
    public const CATEGORIES = [
        'produce', 'bread', 'eggs_dairy', 'cheese', 'honey', 'hot_food', 'coffee', 'wine_beer',
        'flowers', 'fish_meat', 'crafts', 'clothing', 'other',
    ];

    /** An expo's exhibitors are sorted by what they show, not what they sell. */
    public const EXPO_CATEGORIES = [
        'technology', 'services', 'food_drink', 'health', 'education', 'design', 'nonprofit', 'other',
    ];

    public static function normalize(?string $kind): string
    {
        return in_array($kind, self::KINDS, true) ? $kind : 'market';
    }

    /** @return string[] */
    public static function categoriesFor(?string $kind): array
    {
        return self::normalize($kind) === 'expo' ? self::EXPO_CATEGORIES : self::CATEGORIES;
    }

    public static function isCategory(?string $kind, ?string $category): bool
    {
        return in_array($category, self::categoriesFor($kind), true);
    }

    public static function say(?string $kind, string $name, array $replace = []): string
    {
        return __('messages.vm_'.self::normalize($kind).'_'.$name, $replace);
    }

    public static function count(?string $kind, string $name, int $count, array $replace = []): string
    {
        return trans_choice('messages.vm_'.self::normalize($kind).'_'.$name, $count, $replace + ['count' => number_format($count)]);
    }

    /** Every kind-worded string for one kind, for a Vue island. */
    public static function stringsFor(?string $kind): array
    {
        $kind = self::normalize($kind);
        $out = [];
        foreach (self::STRINGS as $name) {
            $out[$name] = __('messages.vm_'.$kind.'_'.$name);
        }

        return $out;
    }

    /** @return array<string, string> slug => label */
    public static function categoryLabels(?string $kind): array
    {
        $out = [];
        foreach (self::categoriesFor($kind) as $slug) {
            $out[$slug] = __('messages.vm_cat_'.$slug);
        }

        return $out;
    }

    /** @return array<string, string> kind => its name */
    public static function kindLabels(): array
    {
        $out = [];
        foreach (self::KINDS as $kind) {
            $out[$kind] = __('messages.vm_'.$kind.'_kind_name');
        }

        return $out;
    }
}
