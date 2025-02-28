<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('roles')
            ->where('accept_requests', false)
            ->update(['accept_requests' => true]);

        Schema::table('roles', function (Blueprint $table) {
            $table->boolean('accept_requests')->default(true)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
