<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('newsletter_segment_users', function (Blueprint $table) {
            $table->unique(['newsletter_segment_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::table('newsletter_segment_users', function (Blueprint $table) {
            $table->dropUnique(['newsletter_segment_id', 'email']);
        });
    }
};
