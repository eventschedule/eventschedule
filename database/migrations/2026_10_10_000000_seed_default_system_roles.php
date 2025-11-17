<?php

use Database\Seeders\AuthorizationSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! $this->authorizationTablesExist()) {
            return;
        }

        if (DB::table('auth_roles')->exists()) {
            return;
        }

        app(AuthorizationSeeder::class)->run();
    }

    public function down(): void
    {
        // No-op: we only seed data when the tables are empty.
    }

    private function authorizationTablesExist(): bool
    {
        return Schema::hasTable('auth_roles')
            && Schema::hasTable('permissions')
            && Schema::hasTable('role_permissions')
            && Schema::hasTable('user_roles');
    }
};
