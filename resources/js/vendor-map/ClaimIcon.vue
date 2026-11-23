<template>
    <svg :class="cls" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
         stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
        <template v-for="(d, i) in paths" :key="'p' + i"><path :d="d" /></template>
        <template v-for="(c, i) in circles" :key="'c' + i"><circle :cx="c[0]" :cy="c[1]" :r="c[2]" /></template>
    </svg>
</template>

<script>
/**
 * Outline icons for the vendor categories (VendorMapKinds::CATEGORIES and EXPO_CATEGORIES), drawn
 * here so no icon font or image is fetched. An unknown slug draws "other".
 */
const ICONS = {
    produce: { p: ['M12 21v-8', 'M12 13c0-4 3-7 8-7 0 4-3 7-8 7z', 'M12 15c0-3-2-5-6-5 0 3 2 5 6 5z'] },
    bread: { p: ['M5 11a4 4 0 0 1 4-4h6a4 4 0 0 1 3 6.6V19H6v-5.4A4 4 0 0 1 5 11z', 'M10 10l-1 3', 'M14 10l-1 3'] },
    eggs_dairy: { p: ['M12 3c3.3 0 6 5.3 6 9.3A6 6 0 0 1 6 12.3C6 8.3 8.7 3 12 3z'] },
    cheese: { p: ['M3 17l9-10 9 5v5H3z', 'M3 17l18-5'], c: [[9, 14.5, 1], [15, 15, 1]] },
    honey: { p: ['M8 4h8', 'M9 4v3', 'M15 4v3', 'M7 7h10l-1 13H8L7 7z', 'M10 11h4'] },
    hot_food: { p: ['M4 12h16a8 8 0 0 1-16 0z', 'M9 8c0-1 1-1.2 1-2.2S9 4.6 9 3.6', 'M14 8c0-1 1-1.2 1-2.2s-1-1.2-1-2.2'] },
    coffee: { p: ['M5 9h11v6a4 4 0 0 1-4 4H9a4 4 0 0 1-4-4V9z', 'M16 11h1.5a2.5 2.5 0 0 1 0 5H16', 'M9 3v3', 'M12 3v3'] },
    wine_beer: { p: ['M8 3h8l-1 6a3 3 0 0 1-6 0L8 3z', 'M12 12v8', 'M9 20h6'] },
    flowers: { p: ['M12 13v8', 'M12 18c-2 0-4-1-5-3', 'M12 17c2 0 4-1 5-3'], c: [[12, 5.5, 2], [15, 8.5, 2], [9, 8.5, 2], [12, 11, 1.2]] },
    fish_meat: { p: ['M3 12c3-4 8-6 12.5-3L21 6v12l-5.5-3C11 18 6 16 3 12z'], c: [[8, 11, 0.8]] },
    crafts: { p: ['M12 3a9 9 0 1 0 0 18c1.5 0 2-1 2-2s-1-2 0-3 3 0 4-1 3-3 3-5a9 9 0 0 0-9-7z'], c: [[7.5, 11, 1], [11, 7, 1], [16, 8.5, 1]] },
    clothing: { p: ['M8 3L3 6l2 4 2-1v12h10V9l2 1 2-4-5-3c0 2-2 3-4 3S8 5 8 3z'] },
    technology: { p: ['M5 4h14a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z', 'M8 20h8', 'M12 16v4'] },
    services: { p: ['M5 7h14a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2z', 'M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2', 'M3 13h18'] },
    food_drink: { p: ['M5 9h11v6a4 4 0 0 1-4 4H9a4 4 0 0 1-4-4V9z', 'M16 11h1.5a2.5 2.5 0 0 1 0 5H16', 'M9 3v3', 'M12 3v3'] },
    health: { p: ['M12 20s-7-4.5-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 10c0 5.5-7 10-7 10z'] },
    education: { p: ['M3 8l9-4 9 4-9 4-9-4z', 'M7 10v5c3 2 7 2 10 0v-5', 'M21 8v5'] },
    design: { p: ['M4 20l4-1 11-11-3-3L5 16l-1 4z', 'M14 7l3 3'] },
    nonprofit: { p: ['M3 20a6 6 0 0 1 12 0', 'M14 20a5 5 0 0 1 7-4.5'], c: [[9, 8, 3], [17, 9, 2.5]] },
    other: { p: [], c: [[6, 12, 1.4], [12, 12, 1.4], [18, 12, 1.4]] },
};

export default {
    name: 'ClaimIcon',
    props: {
        name: { type: String, default: 'other' },
        cls: { type: String, default: 'vmc-icon' },
    },
    computed: {
        icon() {
            return ICONS[this.name] || ICONS.other;
        },
        paths() {
            return this.icon.p || [];
        },
        circles() {
            return this.icon.c || [];
        },
    },
};
</script>
