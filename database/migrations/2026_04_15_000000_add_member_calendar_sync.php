<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('role_user', function (Blueprint $table) {
            $table->string('google_calendar_id')->nullable()->after('notification_settings');
        });

        // Copy owner's google_calendar_id from roles to role_user
        DB::table('role_user')
            ->join('roles', 'role_user.role_id', '=', 'roles.id')
            ->where('role_user.level', 'owner')
            ->whereNotNull('roles.google_calendar_id')
            ->update(['role_user.google_calendar_id' => DB::raw('roles.google_calendar_id')]);

        Schema::create('calendar_syncs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->string('google_event_id')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'event_id', 'role_id']);
        });

        // Copy existing owner google_event_ids from event_role to calendar_syncs
        DB::table('calendar_syncs')->insertUsing(
            ['user_id', 'event_id', 'role_id', 'google_event_id', 'created_at', 'updated_at'],
            DB::table('event_role')
                ->join('roles', 'event_role.role_id', '=', 'roles.id')
                ->whereNotNull('event_role.google_event_id')
                ->whereNotNull('roles.user_id')
                ->select([
                    'roles.user_id',
                    'event_role.event_id',
                    'event_role.role_id',
                    'event_role.google_event_id',
                    DB::raw('NOW()'),
                    DB::raw('NOW()'),
                ])
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_syncs');

        Schema::table('role_user', function (Blueprint $table) {
            $table->dropColumn('google_calendar_id');
        });
    }
};
