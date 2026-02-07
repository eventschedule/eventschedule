<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->boolean('direct_registration')->default(false)->after('accept_requests');
        });

        // Migrate existing link_type settings from graphic_settings
        $roles = DB::table('roles')
            ->whereNotNull('graphic_settings')
            ->get();

        foreach ($roles as $role) {
            $settings = json_decode($role->graphic_settings, true);
            if (isset($settings['link_type']) && $settings['link_type'] === 'registration') {
                DB::table('roles')
                    ->where('id', $role->id)
                    ->update(['direct_registration' => true]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('direct_registration');
        });
    }
};
