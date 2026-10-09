<?php
// Lets go of every seat a visitor's cart is holding. A pressed seat is held for that browser for a
// few minutes, and to the next browser it reads as taken: run before a picker plate is shot again.
//   php run.php <copy> fixture/release.php
use Illuminate\Support\Facades\DB;

if (DB::connection()->getDatabaseName() !== 'eventschedule_test_launchfilm') { fwrite(STDERR, "refusing: this is not the film's schema\n"); exit(1); }
$n = DB::table('seating_seats')->where('status', 'held')->whereNotNull('hold_token')
    ->update(['status' => 'available', 'hold_kind' => null, 'hold_note' => null, 'hold_token' => null, 'hold_expires_at' => null]);
foreach (\App\Models\EventSeatingMap::all() as $map) { $map->bumpVersion(); }
echo "released $n held seat(s)\n";
