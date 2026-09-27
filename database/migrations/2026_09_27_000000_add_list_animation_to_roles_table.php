<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Nullable with NO default: existing and new rows stay NULL, which Role::listAnimation()
     * resolves to "none", so no live schedule starts animating until its owner picks a design.
     */
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->string('list_animation', 20)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('list_animation');
        });
    }
};
