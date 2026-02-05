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
        if (!Schema::hasColumn('event_parts', 'description_html_en')) {
            Schema::table('event_parts', function (Blueprint $table) {
                $table->text('description_html_en')->nullable()->after('description_en');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_parts', function (Blueprint $table) {
            $table->dropColumn('description_html_en');
        });
    }
};
