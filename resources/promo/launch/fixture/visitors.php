<?php
// Brings the simulated visitors to The Indigo Room. `realtime:simulate` spreads its fake visits over
// eight schedules picked at random and the marketing site, so the venue's own Realtime tab gets a
// handful of them or none. This points every simulated visit to a schedule's page at the venue, and
// half of those at Jazz Night's page. Run right after the simulator, right before the plate.
//   php run.php <copy> fixture/visitors.php
use App\Models\Event;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

if (DB::connection()->getDatabaseName() !== 'eventschedule_test_launchfilm') { fwrite(STDERR, "refusing: this is not the film's schema\n"); exit(1); }
$venue = Role::where('subdomain', 'indigo-room')->firstOrFail();
$jazz = Event::where('creator_role_id', $venue->id)->where('name', 'Jazz Night')->orderBy('id')->firstOrFail();

$moved = DB::table('realtime_hits')->where('surface', 'gp')->whereNotNull('role_id')->update(['role_id' => $venue->id, 'event_id' => null, 'is_team' => 0, 'is_admin' => 0]);
// A visitor is on one page at a time: whole visitors go to the event's page, by their key.
$onEvent = DB::table('realtime_hits')->where('surface', 'gp')->where('role_id', $venue->id)->whereRaw('MOD(CRC32(COALESCE(visitor_key, hit_key)), 2) = 0')->update(['event_id' => $jazz->id]);
echo "visits at The Indigo Room: $moved, of them on Jazz Night's page: $onEvent\n";
