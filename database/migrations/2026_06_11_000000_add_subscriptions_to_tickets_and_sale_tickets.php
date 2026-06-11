<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // How a pass/subscription is consumed:
            //   per_occurrence (legacy season pass) | total | per_event | unlimited
            $table->string('pass_usage_type')->default('per_occurrence')->after('is_pass');
            // Visit cap for the `total` usage type.
            $table->unsignedInteger('pass_max_uses')->nullable()->after('pass_usage_type');
            // Validity window in days from purchase; null = no expiry.
            $table->unsignedInteger('pass_valid_days')->nullable()->after('pass_max_uses');
            // Which events the pass covers:
            //   this_event (legacy) | all_events | sub_schedule | specific_events
            $table->string('pass_scope')->default('this_event')->after('pass_valid_days');
            $table->unsignedBigInteger('pass_scope_group_id')->nullable()->after('pass_scope');
            $table->json('pass_event_ids')->nullable()->after('pass_scope_group_id');
        });

        Schema::table('sale_tickets', function (Blueprint $table) {
            // Generalized, event-aware usage log: [{event_id, date, at}].
            $table->json('pass_usages')->nullable()->after('pass_checkins');
            $table->dateTime('pass_expires_at')->nullable()->after('pass_usages');
        });

        // Migrate existing season-pass check-ins ({date: ts}) into the new
        // event-aware pass_usages list ([{event_id, date, at}]), using the
        // sale's event_id as the event each check-in happened at.
        DB::table('sale_tickets')
            ->join('sales', 'sale_tickets.sale_id', '=', 'sales.id')
            ->whereNotNull('sale_tickets.pass_checkins')
            ->select('sale_tickets.id', 'sale_tickets.pass_checkins', 'sales.event_id')
            ->orderBy('sale_tickets.id')
            ->chunk(200, function ($rows) {
                foreach ($rows as $row) {
                    $checkins = json_decode($row->pass_checkins, true);
                    if (! is_array($checkins) || empty($checkins)) {
                        continue;
                    }

                    $usages = [];
                    foreach ($checkins as $date => $at) {
                        $usages[] = [
                            'event_id' => (int) $row->event_id,
                            'date' => (string) $date,
                            'at' => (int) $at,
                        ];
                    }

                    DB::table('sale_tickets')->where('id', $row->id)->update([
                        'pass_usages' => json_encode($usages),
                    ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn([
                'pass_usage_type',
                'pass_max_uses',
                'pass_valid_days',
                'pass_scope',
                'pass_scope_group_id',
                'pass_event_ids',
            ]);
        });

        Schema::table('sale_tickets', function (Blueprint $table) {
            $table->dropColumn(['pass_usages', 'pass_expires_at']);
        });
    }
};
