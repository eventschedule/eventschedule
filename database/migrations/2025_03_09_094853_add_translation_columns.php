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
        Schema::table('roles', function (Blueprint $table) {
            $table->string('name_en')->nullable();
            $table->string('description_en')->nullable();
            $table->string('description_html_en')->nullable();
            $table->string('address1_en')->nullable();
            $table->string('address2_en')->nullable();
            $table->string('city_en')->nullable();
            $table->string('state_en')->nullable();            
        });

        Schema::table('events', function (Blueprint $table) {
            $table->string('name_en')->nullable();
            $table->string('description_en')->nullable();
            $table->string('description_html_en')->nullable();            
        });

        Schema::table('event_role', function (Blueprint $table) {
            $table->string('name_translated')->nullable();
            $table->string('description_translated')->nullable();
            $table->string('description_html_translated')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('name_en');
            $table->dropColumn('description_en');
            $table->dropColumn('description_html_en');
            $table->dropColumn('address1_en');
            $table->dropColumn('address2_en');
            $table->dropColumn('city_en');
            $table->dropColumn('state_en');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('name_en');
            $table->dropColumn('description_en');
            $table->dropColumn('description_html_en');
        });

        Schema::table('event_role', function (Blueprint $table) {
            $table->dropColumn('name_translated');
            $table->dropColumn('description_translated');
            $table->dropColumn('description_html_translated');
        });
    }
};
