<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->boolean('is_pass')->default(false)->after('is_deleted');
        });

        Schema::table('sale_tickets', function (Blueprint $table) {
            $table->text('pass_checkins')->nullable()->after('seats');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('is_pass');
        });

        Schema::table('sale_tickets', function (Blueprint $table) {
            $table->dropColumn('pass_checkins');
        });
    }
};
