<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * (status, paid_at) on sales: "what was paid in the last half hour", for the whole install.
     *
     * The Realtime tab asks that every 15 seconds for every organizer with the tab open
     * (App\Services\ScheduleActivity::live(), the marks on the traffic chart), and the same
     * question over 24 hours once a minute (the Activity rail). No index on `sales` began with
     * time: `sales_allowance_index` is (event_id, status, paid_at), which answers it only once the
     * events are known. So MySQL had two ways to read, and both were wrong at size: start from
     * the viewer's events and read their whole sales history, or, for an account that covers
     * thousands of events, read every sale on the install. With this the read is a range over
     * the window, whoever is asking, and whose sale each one is gets checked on the few rows left.
     *
     * `sales_status_index` (status alone) is a prefix of this one and is left in place: nothing
     * names it, and dropping an index is a separate decision from adding one.
     *
     * An index only. It reads the whole of `sales` once, online. lock_wait_timeout is short, as in
     * the other migrations on this table, so a busy table makes the deploy fail and retry rather
     * than queue every checkout behind the ALTER. Checked first, so a second run is a no-op.
     */
    public function up(): void
    {
        if (! Schema::hasTable('sales') || $this->hasIndex('sales_status_paid_at_index')) {
            return;
        }

        $previous = DB::selectOne('SELECT @@SESSION.lock_wait_timeout AS value')->value;
        DB::statement('SET SESSION lock_wait_timeout = 10');

        try {
            Schema::table('sales', function (Blueprint $table) {
                $table->index(['status', 'paid_at'], 'sales_status_paid_at_index');
            });
        } finally {
            DB::statement('SET SESSION lock_wait_timeout = '.(int) $previous);
        }
    }

    public function down(): void
    {
        // No foreign key leans on this one (neither column is a key), so it simply goes.
        if (Schema::hasTable('sales') && $this->hasIndex('sales_status_paid_at_index')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->dropIndex('sales_status_paid_at_index');
            });
        }
    }

    private function hasIndex(string $name): bool
    {
        return collect(DB::select('SHOW INDEX FROM sales'))->contains(fn ($row) => $row->Key_name === $name);
    }
};
