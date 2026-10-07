<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * (role_id, created_at) on role_user: "who followed these schedules since yesterday".
     *
     * The Realtime tab's Activity rail (App\Services\ScheduleActivity::followers()) asks that once
     * a minute for every organizer with the tab open. The only index that helps today is the
     * foreign key's on role_id alone, so the read walks every member and follower a schedule has
     * ever had to find the handful from the last day. With this it reads the handful.
     *
     * An index only. Checked first, so a second run is a no-op. On a first run it takes the place
     * of the foreign key's own index on role_id, which MySQL drops by itself (see down()); after
     * a rollback and a second run the key keeps the index down() gave it back, and both stay.
     */
    public function up(): void
    {
        if (! Schema::hasTable('role_user') || $this->hasIndex('role_user_role_id_created_at_index')) {
            return;
        }

        Schema::table('role_user', function (Blueprint $table) {
            $table->index(['role_id', 'created_at']);
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('role_user') || ! $this->hasIndex('role_user_role_id_created_at_index')) {
            return;
        }

        // The foreign key on role_id had an index of its own, which MySQL made for it. When the
        // wider index above arrived MySQL dropped that one silently, since the new one can
        // support the key as well. So this index is now the ONLY thing under the key, and
        // dropping it is refused (1553) until the key has its own back.
        if (! $this->hasIndex('role_user_role_id_foreign')) {
            Schema::table('role_user', function (Blueprint $table) {
                $table->index('role_id', 'role_user_role_id_foreign');
            });
        }

        Schema::table('role_user', function (Blueprint $table) {
            $table->dropIndex('role_user_role_id_created_at_index');
        });
    }

    private function hasIndex(string $name): bool
    {
        return collect(DB::select('SHOW INDEX FROM role_user'))->contains(fn ($row) => $row->Key_name === $name);
    }
};
