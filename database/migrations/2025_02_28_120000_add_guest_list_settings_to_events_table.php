<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->boolean('show_guest_list')->default(false)->after('tickets_enabled');
            $table->string('guest_list_visibility')->default('paid')->after('show_guest_list');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['guest_list_visibility', 'show_guest_list']);
        });
    }
};
