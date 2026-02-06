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
        Schema::table('event_role', function (Blueprint $table) {
            $table->text('short_description_translated')->nullable()->after('description_html_translated');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_role', function (Blueprint $table) {
            $table->dropColumn('short_description_translated');
        });
    }
};
