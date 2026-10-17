<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The newsletter somebody unsubscribed from, so its statistics can count who left from it.
     * Null for every row from before, and for one written by a subscriber's own manage page.
     */
    public function up(): void
    {
        Schema::table('newsletter_unsubscribes', function (Blueprint $table) {
            $table->unsignedBigInteger('newsletter_id')->nullable();
            $table->index('newsletter_id');
        });
    }

    public function down(): void
    {
        Schema::table('newsletter_unsubscribes', function (Blueprint $table) {
            $table->dropIndex(['newsletter_id']);
            $table->dropColumn('newsletter_id');
        });
    }
};
