<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->enum('custom_domain_mode', ['redirect', 'direct'])->nullable()->after('custom_domain');
            $table->string('custom_domain_host')->nullable()->unique()->after('custom_domain_mode');
            $table->enum('custom_domain_status', ['pending', 'active', 'failed'])->nullable()->after('custom_domain_host');
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn(['custom_domain_mode', 'custom_domain_host', 'custom_domain_status']);
        });
    }
};
