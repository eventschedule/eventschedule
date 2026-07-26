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
        Schema::table('federated_events', function (Blueprint $table) {
            // Which remote URL produced the copy currently in image_path. Without it
            // the fetcher only ever looks at rows with no image at all, so a source
            // that replaces its flyer would keep showing the original here forever.
            $table->string('image_fetched_url', 1024)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('federated_events', function (Blueprint $table) {
            $table->dropColumn('image_fetched_url');
        });
    }
};
