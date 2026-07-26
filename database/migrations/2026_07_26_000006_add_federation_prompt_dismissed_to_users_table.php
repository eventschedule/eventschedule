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
        Schema::table('users', function (Blueprint $table) {
            // Per-user "don't suggest federation again", mirroring
            // follow_consent_dismissed. No ->after() anchor: the repo rule is to avoid
            // anchoring on columns from other migrations, and appending is always safe.
            $table->boolean('federation_prompt_dismissed')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('federation_prompt_dismissed');
        });
    }
};
