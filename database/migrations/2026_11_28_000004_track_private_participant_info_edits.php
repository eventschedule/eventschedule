<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('backstage_entries', function (Blueprint $table) {
            $table->timestamp('participant_info_updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('backstage_entries', function (Blueprint $table) {
            $table->dropColumn('participant_info_updated_at');
        });
    }
};
