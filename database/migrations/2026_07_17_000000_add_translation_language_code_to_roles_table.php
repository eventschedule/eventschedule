<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The schedule's auto-translation TARGET language. Defaults to 'en' so every
     * existing schedule keeps translating to English exactly as before; owners can
     * change it to offer visitors a different second language. When it equals
     * language_code (the authored language), no translation is offered.
     */
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->string('translation_language_code')->default('en')->after('language_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('translation_language_code');
        });
    }
};
