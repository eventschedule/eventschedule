<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            // null = default panel, 'transparent' = no panel, otherwise a #rrggbb colour.
            $table->string('sponsor_background_color', 20)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('sponsor_background_color');
        });
    }
};
