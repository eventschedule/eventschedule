/**
 * Outline icons for the vendor map: one per category (VendorMapKinds::CATEGORIES and
 * EXPO_CATEGORIES) and one per landmark kind (VendorMapLandmark::KINDS).
 *
 * Each is the `d` of one or more paths on a 24x24 grid, drawn with a stroke and no fill
 * (stroke-linecap and stroke-linejoin round: a lone "h.01" segment is a dot). Shared by the
 * visitors' map (MapCanvas.vue, PublicMapApp.vue) and anything else that shows a category: the
 * claim page's chips and the designer can import the same shapes so a category looks the same
 * everywhere. Plain data, no Vue, so a Blade view can be given the same paths too.
 */

export const CATEGORY_ICONS = {
    produce: ['M12 7c-2-2-6.5-1.5-6.5 3.5 0 4.5 3 9.5 6.5 9.5s6.5-5 6.5-9.5C18.5 5.5 14 5 12 7Z', 'M12 7c0-2 1-3.5 3-4.5'],
    bread: ['M5 11.5A3.5 3.5 0 0 1 6.5 5h11a3.5 3.5 0 0 1 1.5 6.5V19a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1v-7.5Z', 'M10 9.5v3', 'M14 9.5v3'],
    eggs_dairy: ['M12 3c-3.5 0-6 5.5-6 10a6 6 0 0 0 12 0c0-4.5-2.5-10-6-10Z'],
    cheese: ['M3 18v-6.5L15.5 5 21 11.5V18H3Z', 'M3 11.5h18', 'M8 15h.01', 'M15.5 14.5h.01'],
    honey: ['M8.5 4h7', 'M7 7h10l-1 2v9a2 2 0 0 1-2 2h-4a2 2 0 0 1-2-2V9L7 7Z', 'M10 13.5h4'],
    hot_food: ['M3.5 12h17a8.5 8.5 0 0 1-17 0Z', 'M9 3.5c-1 1 1 2 0 3', 'M13 3.5c-1 1 1 2 0 3'],
    coffee: ['M5 9h11v5a5.5 5.5 0 0 1-11 0V9Z', 'M16 10.5h1.5a2.5 2.5 0 0 1 0 5H16', 'M8.5 3.5v2.5', 'M12.5 3.5v2.5'],
    wine_beer: ['M8 3h8l-.5 5.5a3.5 3.5 0 0 1-7 0L8 3Z', 'M12 12v8', 'M8.5 20.5h7'],
    flowers: ['M12 13.5v7.5', 'M12 13.5a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z', 'M12 7.5V4.5', 'M9 10.5H6', 'M18 10.5h-3', 'M12 18c-2.5 0-4-1.5-4-3 2 0 4 1 4 3Z'],
    fish_meat: ['M3 12c3-4.5 7.5-5.5 11.5-3L18.5 6v12l-4-3c-4 2.5-8.5 1.5-11.5-3Z', 'M8 11.5h.01'],
    crafts: ['M12 3a9 9 0 0 0 0 18c1 0 1.5-.8 1.5-1.6 0-1-.8-1.4-.8-2.3 0-.9.7-1.6 1.6-1.6H16a5 5 0 0 0 5-5C21 6.4 17 3 12 3Z', 'M7.5 11h.01', 'M10 7.5h.01', 'M15 7.5h.01'],
    clothing: ['M8 3.5 4 6.5l2 4 2-1V20h8V9.5l2 1 2-4-4-3c-.5 1.5-2 2.5-4 2.5S8.5 5 8 3.5Z'],
    other: ['M3.5 12.5V4.5a1 1 0 0 1 1-1h8l8.5 8.5-9 9-8.5-8.5Z', 'M8 8h.01'],
    technology: ['M4.5 5.5h15v10h-15z', 'M2.5 19h19'],
    services: ['M4 8h16v11H4z', 'M9 8V5h6v3', 'M4 13h16'],
    food_drink: ['M7 3v8a2 2 0 0 0 2 2v8', 'M5 3v5', 'M9 3v5', 'M16.5 21V3c2 1 3 4 3 7h-3'],
    health: ['M12 20s-7.5-4.5-7.5-10A4 4 0 0 1 12 7.5 4 4 0 0 1 19.5 10c0 5.5-7.5 10-7.5 10Z'],
    education: ['M4.5 5a2 2 0 0 1 2-2h13v16h-13a2 2 0 0 0-2 2V5Z', 'M4.5 19a2 2 0 0 1 2-2h13'],
    design: ['M15 4l5 5-9 9H6v-5l9-9Z', 'M13 6l5 5'],
    nonprofit: ['M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z', 'M3 12h18', 'M12 3c2.5 2.5 3.5 5.5 3.5 9s-1 6.5-3.5 9c-2.5-2.5-3.5-5.5-3.5-9s1-6.5 3.5-9Z'],
};

export const LANDMARK_ICONS = {
    entrance: ['M14 3.5h5v17h-5', 'M10 8l4 4-4 4', 'M14 12H3.5'],
    restroom: ['M7 6.5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Z', 'M17 6.5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Z', 'M5 20.5v-6H4l1-5.5h4l1 5.5H9v6', 'M15 20.5v-6h-1.5L15 9h4l1.5 5.5H19v6', 'M12 3v18'],
    parking: ['M4.5 4.5h15v15h-15z', 'M10 16.5v-9h3a2.5 2.5 0 0 1 0 5h-3'],
    stage: ['M9 18V5.5l11-2v13', 'M9 18a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z', 'M20 16a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z'],
    text: [],
};

/** The paths for a category, or the "other" tag when it is not one we know. */
export function categoryIcon(slug) {
    return CATEGORY_ICONS[slug] || CATEGORY_ICONS.other;
}

/** The paths for a landmark kind, or none (a plain text label draws no icon). */
export function landmarkIcon(kind) {
    return LANDMARK_ICONS[kind] || [];
}
