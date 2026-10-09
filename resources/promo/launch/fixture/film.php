<?php
// The launch film's own additions to The Indigo Room, on top of the Product Hunt kit's seed.
//
//   php ~/.claude/plans/product-hunt-video/seed/run.php <copy> resources/promo/launch/fixture/film.php onsale
//   php ~/.claude/plans/product-hunt-video/seed/run.php <copy> resources/promo/launch/fixture/film.php doors
//   (and `prescan`: see below)
//
// (fixture/seed.sh does the whole chain: app:setup-demo, the kit's retheme, then this.)
// run.php refuses any schema that is not eventschedule_test_*; this refuses any that is not the
// film's own. It can be run again and again, in either state: it removes what it made first.
//
// What it does, and why the kit alone is not enough for the film:
//   - Jazz Night becomes a SEATED event: one plan, one level, a stage, four VIP tables of four and
//     134 General Admission seats, so the film can show a buyer choosing C7 and C8 and then the room
//     filling. The numbers are the gallery's: 150 sold, 142 in, VIP 16/16, General Admission 126/134.
//   - `onsale`: 96 sold, C7 and C8 free with a sold seat either side, so the picker is on the page.
//     `doors`: 150 sold, 142 through the door, one sale Avery Lane's two seats, C7 and C8.
//     `prescan`: the door a moment earlier: Avery Lane has bought and not been scanned (140 in), so
//     the scan the film shows is a real one.
//   - Every sale is a card sale with an invented buyer (the demo's are a cartoon family's).
//   - 940 followers, and a newsletter sent to them with Jazz Night and its poster at the top.
//   - Jazz Night carries the venue's own poster (fixture/poster.html) as its flyer.
//   - Realtime is switched on for the owner.

use App\Models\Event;
use App\Models\EventSeatingMap;
use App\Models\Newsletter;
use App\Models\Role;
use App\Models\SeatingDecoration;
use App\Models\SeatingLevel;
use App\Models\SeatingPlan;
use App\Models\SeatingSeat;
use App\Models\SeatingSection;
use App\Models\SeatingTable;
use App\Models\Setting;
use App\Services\SeatingMapService;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

$state = $argv[3] ?? 'doors';
if (! in_array($state, ['onsale', 'prescan', 'doors'], true)) { fwrite(STDERR, "state must be onsale, prescan or doors\n"); exit(1); }
$door = $state !== 'onsale';                               // sold out, and the door is open
if (DB::connection()->getDatabaseName() !== 'eventschedule_test_launchfilm') { fwrite(STDERR, "refusing: this is not the film's schema\n"); exit(1); }

const FOLLOWERS = 940;
const BUYER = ['name' => 'Avery Lane', 'email' => 'avery.lane@mail.example', 'row' => 'C', 'seats' => ['7', '8']];
const POSTER = 'demo_launch_jazz_night.jpg';          // public/images/demo/ in the copy (fixture/seed.sh puts it there)

$tz = 'America/New_York';
$today = now($tz)->format('Y-m-d');
$venue = Role::where('subdomain', 'indigo-room')->firstOrFail();
$jazz = Event::where('name', 'Jazz Night')->where('creator_role_id', $venue->id)->firstOrFail();
$owner = DB::table('users')->where('id', $venue->user_id)->first();

// Invented people, in a fixed order (the kit's own two lists, so the door reads like the gallery's).
$first = ['Maya', 'Theo', 'Priya', 'Jonah', 'Elena', 'Marcus', 'Ines', 'Caleb', 'Noor', 'Felix', 'Ruth', 'Owen', 'Lucia', 'Dev', 'Hana', 'Silas'];
$last = ['Okafor', 'Lindqvist', 'Castillo', 'Whitfield', 'Nakamura', 'Brennan', 'Haddad', 'Moreau', 'Ellery', 'Sandoval'];
$person = function (int $n) use ($first, $last): array {
    $name = $first[$n % count($first)].' '.$last[($n * 7 + intdiv($n, count($first)) * 3) % count($last)];

    return [$name, Str::slug($name, '.').($n >= 80 ? intdiv($n, 80) : '').'@mail.example'];
};

/* ------------------------------------------------------------------ switches */
Setting::set('realtime_enabled', '1');
Setting::set('realtime_owner_view', '1');

/* ------------------------------------------------------------------ the poster, and selling until the last set ends */
DB::table('events')->where('id', $jazz->id)->update(['flyer_image_url' => POSTER, 'image_variants' => null, 'sell_after_start' => 1]);

/* ------------------------------------------------------------------ the room */
EventSeatingMap::where('event_id', $jazz->id)->get()->each->delete();          // children go with it (cascade)
DB::table('events')->where('id', $jazz->id)->update(['seating_plan_id' => null]);
SeatingPlan::where('role_id', $venue->id)->get()->each->delete();

// The venue has its "never leave a single seat" rule off: with it on, the first of two seats a
// buyer presses is answered with an amber notice until the second is pressed, and the film's
// middle frame is one seat pressed.
$plan = SeatingPlan::create(['role_id' => $venue->id, 'name' => 'The Room', 'description' => 'Four tables at the front and the floor behind them.', 'orphan_rule_enabled' => false]);
$level = SeatingLevel::create(['seating_plan_id' => $plan->id, 'name' => 'The Room', 'position' => 0]);

// General Admission is 18 seats across, 9 either side of a centre aisle: 17 x 26 + 30 = 472 wide.
// Everything is centred on x = 236.
SeatingDecoration::create([
    'seating_plan_id' => $plan->id, 'seating_level_id' => $level->id, 'kind' => 'stage', 'label' => 'STAGE',
    'x' => 86, 'y' => -322, 'width' => 300, 'height' => 44, 'position' => 0,
]);

$vip = SeatingSection::create([
    'seating_plan_id' => $plan->id, 'seating_level_id' => $level->id, 'kind' => 'seated',
    'name' => 'VIP', 'band' => 'VIP', 'color' => '#F9A825', 'position' => 0, 'x' => 0, 'y' => -214,
]);
foreach ([[56, 62], [176, 76], [296, 76], [416, 62]] as $i => [$tx, $ty]) {     // four tables of four, in a shallow arc
    $table = SeatingTable::create([
        'seating_section_id' => $vip->id, 'label' => 'Table '.($i + 1), 'shape' => 'round',
        'seat_count' => 4, 'booking_mode' => 'seat', 'x' => $tx, 'y' => $ty, 'rotation' => 0, 'width' => 52, 'height' => 52,
    ]);
    for ($k = 0; $k < 4; $k++) {
        $a = (2 * M_PI * $k) / 4 - M_PI / 4;
        SeatingSeat::create([
            'seating_plan_id' => $plan->id, 'seating_section_id' => $vip->id, 'seating_table_id' => $table->id,
            'row_label' => null, 'row_position' => $i + 1, 'seat_label' => (string) ($k + 1), 'position' => $k + 1,
            'x' => (int) round(cos($a) * 42), 'y' => (int) round(sin($a) * 42), 'kind' => 'standard',
        ]);
    }
}

$ga = SeatingSection::create([
    'seating_plan_id' => $plan->id, 'seating_level_id' => $level->id, 'kind' => 'seated',
    'name' => 'General Admission', 'band' => 'General Admission', 'color' => '#4E81FA', 'position' => 1, 'x' => 0, 'y' => 0,
]);
// Rows A to G of eighteen (9 + 9), and a back row H of eight (4 + 4): 134. C7 and C8 sit together
// in the left block of row C.
foreach (range('A', 'H') as $r => $row) {
    $perRow = $row === 'H' ? 8 : 18;
    $half = $perRow / 2;
    for ($n = 1; $n <= $perRow; $n++) {
        // H1 to H4 stand under A6 to A9, H5 to H8 under A10 to A13: the short row is centred on the aisle.
        $col = $row === 'H' ? $n + 5 : $n;
        SeatingSeat::create([
            'seating_plan_id' => $plan->id, 'seating_section_id' => $ga->id,
            'row_label' => $row, 'row_position' => $r + 1, 'seat_label' => (string) $n, 'position' => $n,
            'x' => ($col - 1) * 26 + ($n > $half ? 30 : 0), 'y' => $r * 30,
            'aisle_after' => $n === $half,
        ]);
    }
}

// The two ticket types price the two sections.
$tGa = DB::table('tickets')->where('event_id', $jazz->id)->where('type', 'General Admission')->first();
$tVip = DB::table('tickets')->where('event_id', $jazz->id)->where('type', 'VIP')->first();
DB::table('tickets')->where('id', $tGa->id)->update(['seating_band' => 'General Admission', 'quantity' => 134]);
DB::table('tickets')->where('id', $tVip->id)->update(['seating_band' => 'VIP', 'quantity' => 16]);
DB::table('events')->where('id', $jazz->id)->update(['seating_plan_id' => $plan->id]);

$jazz = Event::findOrFail($jazz->id);
$map = app(SeatingMapService::class)->materialize($jazz, $today);
if (! $map) { throw new RuntimeException('the seat map for tonight could not be made'); }

$mVip = SeatingSection::where('event_seating_map_id', $map->id)->where('name', 'VIP')->firstOrFail();
$mGa = SeatingSection::where('event_seating_map_id', $map->id)->where('name', 'General Admission')->firstOrFail();
$seatsVip = SeatingSeat::where('seating_section_id', $mVip->id)->orderBy('row_position')->orderBy('position')->get()->all();
$seatsGa = SeatingSeat::where('seating_section_id', $mGa->id)->orderBy('row_position')->orderBy('position')->get()->all();
if (count($seatsVip) !== 16 || count($seatsGa) !== 134) { throw new RuntimeException('the room is not 16 + 134'); }

/* ------------------------------------------------------------------ who has bought */
$old = DB::table('sales')->where('event_id', $jazz->id)->pluck('id');
DB::table('sale_tickets')->whereIn('sale_id', $old)->delete();
DB::table('sales')->whereIn('id', $old)->delete();

$isBuyer = fn ($s) => $s->row_label === BUYER['row'] && in_array($s->seat_label, BUYER['seats'], true);

// The order the floor sells in: front rows first, each from the aisle outwards, with a little
// unevenness so the house does not fill like a ruler line. C6 and C9 always go, so the pair the
// film picks leaves no single empty seat beside it for the orphan rule to refuse.
$floor = array_values(array_filter($seatsGa, fn ($s) => ! $isBuyer($s)));
usort($floor, function ($a, $b) {
    $key = fn ($s) => $s->row_position * 3.4 + abs($s->position - 9.5) * 0.8 + (($s->id * 2654435761) % 97) / 97 * 2.6;

    return $key($a) <=> $key($b);
});
$pin = fn ($s) => $s->row_label === 'C' && in_array($s->seat_label, ['6', '9'], true);
$floor = array_merge(array_values(array_filter($floor, $pin)), array_values(array_filter($floor, fn ($s) => ! $pin($s))));

$sellVip = $door ? 16 : 12;                 // three tables gone, the fourth still free
$sellGa = $door ? 132 : 84;                 // plus the buyer's two at the door: 134

// Seats bought together sit together: neighbours in one row and one block, or at one table.
$group = function (array $pool, bool $tables): array {
    $rows = [];
    foreach ($pool as $s) { $rows[$s->row_position][] = $s; }
    ksort($rows);
    $orders = [];
    $k = 0;
    foreach ($rows as $row) {
        usort($row, fn ($a, $b) => $a->position <=> $b->position);
        for ($i = 0; $i < count($row);) {
            $want = $tables ? [2, 2, 4, 2][$k++ % 4] : [2, 2, 1, 3, 2, 4, 2, 1, 2, 3][$k++ % 10];
            $take = [$row[$i]];
            while (count($take) < $want && isset($row[$i + count($take)])
                && $row[$i + count($take)]->position === end($take)->position + 1
                && ($tables || ! end($take)->aisle_after)) {
                $take[] = $row[$i + count($take)];
            }
            $orders[] = $take;
            $i += count($take);
        }
    }

    return $orders;
};
$ordersVip = $group(array_slice($seatsVip, 0, $sellVip), true);
$ordersGa = $group(array_slice($floor, 0, $sellGa), false);

// Eight General Admission tickets have not arrived: whole orders, from the back rows.
$missing = [];
if ($door) {
    for ($left = 8, $i = count($ordersGa) - 1; $i >= 0 && $left > 0; $i--) {
        if (count($ordersGa[$i]) <= $left) { $missing[$i] = true; $left -= count($ordersGa[$i]); }
    }
    if ($left !== 0) { throw new RuntimeException('could not leave exactly eight at the door'); }
}

mt_srand(142);
$n = 0;
$counts = ['sold' => [$tGa->id => 0, $tVip->id => 0], 'in' => [$tGa->id => 0, $tVip->id => 0]];
$nowTs = time();
$sell = function (array $seats, object $ticket, float $price, array $who, Carbon $paid, bool $arrived, ?int $arrivedAt = null) use ($jazz, $today, &$counts, $nowTs) {
    $q = count($seats);
    $secret = substr(hash('sha256', 'launchfilm-'.$who[0].'-'.$seats[0]->id), 0, 32);
    $saleId = DB::table('sales')->insertGetId([
        'event_id' => $jazz->id, 'name' => $who[0], 'email' => $who[1], 'secret' => $secret,
        'transaction_reference' => 'pi_3R'.strtoupper(substr(hash('sha256', $secret), 0, 22)), 'status' => 'paid',
        'paid_at' => $paid, 'created_at' => $paid, 'updated_at' => $paid, 'event_date' => $today,
        'subdomain' => 'indigo-room', 'payment_method' => 'stripe', 'is_deleted' => 0, 'payment_amount' => $q * $price,
    ]);
    $slots = [];
    for ($s = 1; $s <= $q; $s++) {
        // scanned over the last hour and a half, as the gallery's door has it
        $slots[(string) $s] = $arrived ? ($arrivedAt ? $arrivedAt + $s * 4 : $nowTs - mt_rand(150, 5400)) : null;
    }
    $lineId = DB::table('sale_tickets')->insertGetId(['sale_id' => $saleId, 'ticket_id' => $ticket->id, 'quantity' => $q, 'seats' => json_encode($slots)]);
    foreach ($seats as $i => $seat) {
        DB::table('seating_seats')->where('id', $seat->id)->update([
            'status' => 'sold', 'sale_id' => $saleId, 'sale_ticket_id' => $lineId,
            'checked_in_at' => $slots[(string) ($i + 1)] ? Carbon::createFromTimestamp($slots[(string) ($i + 1)]) : null,
        ]);
    }
    $counts['sold'][$ticket->id] += $q;
    $counts['in'][$ticket->id] += $arrived ? $q : 0;

    return ['id' => $saleId, 'secret' => $secret];
};

// Most were bought over the last twelve days; the last few this afternoon, so the day's activity has sales on it.
$when = function (int $i, int $of) {
    $fromEnd = $of - 1 - $i;

    return $fromEnd < 9 ? now()->subMinutes(18 + $fromEnd * 21 + mt_rand(0, 9)) : now()->subDays(mt_rand(1, 12))->subMinutes(mt_rand(0, 900));
};
foreach ($ordersVip as $i => $seats) {
    $sell($seats, $tVip, 40, $person($n++), now()->subDays(mt_rand(2, 12))->subMinutes(mt_rand(0, 900)), $door);
}
foreach ($ordersGa as $i => $seats) {
    $sell($seats, $tGa, 25, $person($n++), $when($i, count($ordersGa)), $door && empty($missing[$i]));
}

$buyer = null;
if ($door) {
    // The buyer the film follows: bought a few minutes ago, scanned in just now (or, in `prescan`, about to be).
    $mine = array_values(array_filter($seatsGa, $isBuyer));
    usort($mine, fn ($a, $b) => $a->position <=> $b->position);
    $buyer = $sell($mine, $tGa, 25, [BUYER['name'], BUYER['email']], now()->subMinutes(7), $state === 'doors', $nowTs - 50);
}

foreach ($counts['sold'] as $ticketId => $count) {
    $was = json_decode((string) DB::table('tickets')->where('id', $ticketId)->value('sold'), true) ?: [];
    $was[$today] = $count;
    DB::table('tickets')->where('id', $ticketId)->update(['sold' => json_encode($was)]);
}
$map->bumpVersion();

/* ------------------------------------------------------------------ every sale is a card sale, by an invented person */
DB::table('sales')->update(['payment_method' => 'stripe']);
$k = 200;
foreach (DB::table('sales')->where('event_id', '!=', $jazz->id)->orderBy('id')->get(['id', 'secret']) as $sale) {
    [$name, $email] = $person($k++);
    // The demo stamps every sale with the second it was seeded, and the day's activity then lists
    // forty sales "41 min. ago". Spread over the last ten days, none newer than four hours, so
    // tonight's Jazz Night orders are the newest in the sales list.
    $at = now()->subMinutes(240 + (($sale->id * 7919) % 14000));
    DB::table('sales')->where('id', $sale->id)->update([
        'name' => $name, 'email' => $email, 'paid_at' => $at, 'created_at' => $at, 'updated_at' => $at,
        'transaction_reference' => 'pi_3R'.strtoupper(substr(hash('sha256', (string) $sale->secret), 0, 22)),
    ]);
}

/* ------------------------------------------------------------------ what is left of the demo's cartoon */
// The kit renames the venue's own events. The film also shows other schedules' pages, the sales
// list and the day's activity, where the demo's remaining in-jokes would appear.
$plain = [
    '"Beer Baron" Prohibition League Night' => 'Speakeasy Night', '"Two Dozen and One Greyhounds" Dog Show' => 'Dog Show',
    'Be Sharps Reunion Concert' => 'Barbershop Night', 'Big City Laughs Comedy Showcase' => 'Comedy Showcase',
    'Do It For Her Night' => 'Family Night', 'I Choo-Choo-Choose You Speed Dating' => 'Speed Dating',
    'Inanimate Carbon Rod Appreciation Night' => 'Volunteer Awards Night', 'Le Grille BBQ Cookoff' => 'BBQ Cookoff',
    'Lemon Tree Memorial Day' => 'Founders Day', 'Max Power Networking Night' => 'Networking Night',
    'Pin Pals Bowling Tournament' => 'Bowling Tournament', 'Purple Monkey Dishwasher' => 'Improv Night',
    'S-M-R-T Spelling Bee' => 'Spelling Bee', 'Steamed Hams Cooking Class' => 'Cooking Class', 'Tomacco Tasting Night' => 'Tasting Night',
];
foreach ($plain as $from => $to) { DB::table('events')->where('name', $from)->update(['name' => $to, 'slug' => Str::slug($to)]); }
$known = ['General Admission', 'VIP', 'Free Entry', 'Front Row', 'Audience', 'Poet Entry', 'VIP Access', 'Spectator', 'Stalls', 'Circle', 'Backstage Pass', 'VIP Front Row',
    'Orchestra Seating', 'VIP Package', 'Competitor Entry', 'Judges Table', 'Family Package', 'Audition Entry', 'Singles Entry', 'Player Entry', 'Family 4-Pack', 'Pit Pass',
    'Box Seats', 'VIP Lounge', 'Meet & Greet', 'Table Service', 'Speller Entry', 'Dog Entry', 'Challenger Entry'];
foreach (DB::table('tickets')->whereNotIn('type', $known)->orderBy('id')->get(['id', 'event_id', 'type']) as $t) {
    $taken = DB::table('tickets')->where('event_id', $t->event_id)->where('id', '!=', $t->id)->pluck('type')->all();
    $name = preg_match('/Team/i', $t->type) ? 'Team Entry' : (in_array('VIP', $taken, true) ? 'Premium' : 'VIP');
    DB::table('tickets')->where('id', $t->id)->update(['type' => in_array($name, $taken, true) ? 'Premium Plus' : $name]);
}
DB::table('seating_plans')->where('name', 'Aztec Auditorium')->update(['name' => 'The Orpheum Auditorium', 'description' => 'Stalls and a circle, 1927.']);

/* ------------------------------------------------------------------ three things to book, so the booking page has cards */
// The kit seeds one type (Private hire viewing). With one, /book goes straight to its calendar; the
// film shows the cards a visitor chooses from.
$open = [['start' => '12:00', 'end' => '18:00']];
foreach ([['Sound check', 'sound-check', 60, 'An hour on the stage with the house engineer.'], ['Booking call', 'booking-call', 15, 'Talk to the booker about a night for your band.'],
    ['Press photos', 'press-photos', 90, 'The empty room and the stage lights, for your band\'s photos.']] as [$name, $slug, $minutes, $about]) {
    \App\Models\AppointmentType::where('role_id', $venue->id)->where('slug', $slug)->delete();
    $type = new \App\Models\AppointmentType;
    $type->role_id = $venue->id;
    $type->price = 0;
    $type->payment_method = 'cash';
    $type->is_active = true;
    $type->name = $name;
    $type->slug = $slug;
    $type->description = $about;
    $type->duration_minutes = $minutes;
    $type->weekly_windows = ['0' => [], '1' => [], '2' => $open, '3' => $open, '4' => $open, '5' => $open, '6' => $open];
    $type->save();
}
\App\Models\AppointmentType::where('role_id', $venue->id)->where('slug', 'private-hire-viewing')->update(['description' => 'See the room, the stage and the bar before you hire it for a night.']);

/* ------------------------------------------------------------------ 940 followers */
$fans = DB::table('users')->where('email', 'like', '%@fans.example')->pluck('id');
DB::table('role_user')->whereIn('user_id', $fans)->delete();
DB::table('users')->whereIn('id', $fans)->delete();

$hash = Hash::make(Str::random(24));
$rows = [];
for ($i = 0; $i < FOLLOWERS; $i++) {
    $name = $first[($i * 7) % count($first)].' '.$last[($i * 3 + intdiv($i, 16)) % count($last)];
    $at = now()->subMinutes(90 + ($i * 104729) % (240 * 24 * 60));
    $rows[] = ['name' => $name, 'email' => Str::slug($name, '.').'.'.($i + 1).'@fans.example', 'password' => $hash, 'email_verified_at' => $at, 'timezone' => $tz, 'language_code' => 'en', 'created_at' => $at, 'updated_at' => $at];
}
foreach (array_chunk($rows, 235) as $chunk) { DB::table('users')->insert($chunk); }
$pivot = [];
foreach (DB::table('users')->where('email', 'like', '%@fans.example')->orderBy('id')->get(['id', 'created_at']) as $u) {
    $pivot[] = ['user_id' => $u->id, 'role_id' => $venue->id, 'level' => 'follower', 'created_at' => $u->created_at, 'updated_at' => $u->created_at];
}
foreach (array_chunk($pivot, 235) as $chunk) { DB::table('role_user')->insert($chunk); }

/* ------------------------------------------------------------------ a newsletter, sent to all of them */
Newsletter::where('role_id', $venue->id)->get()->each->delete();
$block = fn (string $type, array $data) => ['id' => (string) Str::uuid(), 'type' => $type, 'data' => $data];
// Sent this morning: its subject says "Tonight". (Before mid-morning, three quarters of an hour ago.)
$zone = $venue->timezone ?: 'America/New_York';
$morning = now($zone)->startOfDay()->addHours(10)->addMinutes(5);
$sentAt = ($morning->lt(now($zone)->subMinutes(45)) ? $morning : now($zone)->subMinutes(45))->setTimezone(config('app.timezone'));
$since = max(20, (int) $sentAt->diffInMinutes(now()) - 2);
$newsletter = Newsletter::create([
    'role_id' => $venue->id, 'user_id' => $owner->id, 'type' => 'schedule',
    'subject' => 'Tonight: Jazz Night at The Indigo Room',
    'blocks' => [
        // Jazz Night first, with its poster.
        $block('events', ['layout' => 'cards', 'useAllEvents' => false, 'eventIds' => [$jazz->id]]),
        $block('heading', ['text' => 'Two sets from The Indigo Quartet', 'level' => 'h2', 'align' => 'center']),
        $block('text', ['content' => '<p>Standards, a few new tunes, and requests after ten. Doors at six, first set at eight. Pick your own seats while there are some left.</p>']),
        $block('events', ['layout' => 'cards', 'useAllEvents' => true, 'eventIds' => []]),
    ],
    'status' => 'sent', 'sent_at' => $sentAt, 'sent_count' => FOLLOWERS, 'open_count' => 611, 'click_count' => 184,
]);
$rows = [];
foreach (DB::table('users')->where('email', 'like', '%@fans.example')->orderBy('id')->get(['id', 'email', 'name']) as $i => $f) {
    $opened = ($i * 31) % 100 < 65;
    $clicked = $opened && ($i * 13) % 100 < 30;
    $rows[] = [
        'newsletter_id' => $newsletter->id, 'user_id' => $f->id, 'email' => $f->email, 'name' => $f->name,
        'token' => substr(hash('sha256', 'nl'.$f->id), 0, 40), 'status' => 'sent', 'sent_at' => $sentAt,
        'opened_at' => $opened ? $sentAt->copy()->addMinutes(1 + ($i * 17) % $since) : null, 'open_count' => $opened ? 1 : 0,
        'clicked_at' => $clicked ? $sentAt->copy()->addMinutes(2 + ($i * 19) % ($since - 1)) : null, 'click_count' => $clicked ? 1 : 0,
    ];
}
foreach (array_chunk($rows, 235) as $chunk) { DB::table('newsletter_recipients')->insert($chunk); }
DB::table('newsletters')->where('id', $newsletter->id)->update([
    'open_count' => count(array_filter($rows, fn ($r) => $r['open_count'])), 'click_count' => count(array_filter($rows, fn ($r) => $r['click_count'])),
]);

/* ------------------------------------------------------------------ the code the film shows on a ticket */
// A real one encodes this copy's local address, and a viewer who scans a frame should land on the
// product. capture.mjs answers the ticket page's request for its code with this picture, drawn by
// the app's own generator.
@mkdir(public_path('images/demo'), 0755, true);
file_put_contents(public_path('images/demo/demo_launch_qr.png'), \App\Utils\QrCodeUtils::png('https://eventschedule.com', 1600, 80));

Artisan::call('cache:clear');

/* ------------------------------------------------------------------ what the camera needs to know */
$enc = fn ($id) => UrlUtils::encodeId($id);
$base = rtrim((string) config('app.url'), '/');
$path = fn (string $url) => str_replace($base, '', $url);
$jazzUrl = $path($jazz->getGuestUrl($venue->subdomain)).(str_ends_with($jazz->getGuestUrl($venue->subdomain), $today) ? '' : '/'.$today);
$other = fn (string $sub) => ['url' => '/'.$sub, 'encoded' => $enc(Role::where('subdomain', $sub)->value('id')), 'name' => Role::where('subdomain', $sub)->value('name')];
$fixture = [
    'state' => $state, 'today' => $today, 'timezone' => $tz, 'base' => $base,
    'made_at' => now($tz)->format('Y-m-d H:i T'),
    'venue' => ['subdomain' => 'indigo-room', 'encoded' => $enc($venue->id), 'name' => $venue->name, 'url' => '/indigo-room', 'host' => 'indigo-room.eventschedule.com'],
    'looks' => ['late-laughs' => $other('late-laughs'), 'eastside' => $other('eastside'), 'riverside' => $other('riverside'), 'orpheum' => $other('the-orpheum')],
    'jazz' => ['id' => $jazz->id, 'encoded' => $enc($jazz->id), 'url' => $jazzUrl, 'tickets_url' => $jazzUrl.'?tickets=true'],
    'room' => [
        'sold' => ['VIP' => $counts['sold'][$tVip->id], 'General Admission' => $counts['sold'][$tGa->id]],
        'checked_in' => ['VIP' => $counts['in'][$tVip->id], 'General Admission' => $counts['in'][$tGa->id]],
        'total_sold' => array_sum($counts['sold']), 'total_in' => array_sum($counts['in']),
        'checkin_url' => '/checkin?event='.$enc($jazz->id), 'scan_url' => '/scan', 'sales_url' => '/sales',
    ],
    'buyer' => $buyer ? ['name' => BUYER['name'], 'seats' => 'Row C, Seats 7 and 8', 'secret' => $buyer['secret'], 'ticket_url' => '/ticket/view/'.$enc($jazz->id).'/'.$buyer['secret'], 'qr_route' => '/ticket/qr_code/'.$enc($jazz->id).'/'.$buyer['secret']] : null,
    'followers' => FOLLOWERS,
    'newsletter' => ['encoded' => $enc($newsletter->id), 'edit_url' => '/newsletters/'.$enc($newsletter->id).'/edit?role_id='.$enc($venue->id), 'stats_url' => '/newsletters/'.$enc($newsletter->id).'/stats?role_id='.$enc($venue->id), 'list_url' => '/newsletters?role_id='.$enc($venue->id), 'create_url' => '/newsletters/create?role_id='.$enc($venue->id)],
    'booking' => ['url' => '/indigo-room/book', 'type_url' => '/indigo-room/book/private-hire-viewing'],
    'import_url' => '/indigo-room/import',
    'analytics' => ['url' => '/analytics?role_id='.$enc($venue->id), 'realtime_url' => '/analytics?tab=realtime&role_id='.$enc($venue->id)],
    'admin_url' => '/indigo-room/schedule',
    'copy' => base_path(),
    'qr_file' => public_path('images/demo/demo_launch_qr.png'),
];
$keep = getenv('LAUNCHFILM_KEEP') ?: getenv('HOME').'/.claude/plans/product-hunt-video';
file_put_contents($keep.'/fixture.json', json_encode($fixture, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");

echo json_encode(['state' => $state, 'today' => $today, 'sold' => $fixture['room']['sold'], 'in' => $fixture['room']['checked_in'], 'followers' => DB::table('role_user')->where('role_id', $venue->id)->where('level', 'follower')->count(), 'newsletter' => $newsletter->id, 'jazz_url' => $jazzUrl, 'cash_sales' => DB::table('sales')->where('payment_method', 'cash')->count()]), "\n";
