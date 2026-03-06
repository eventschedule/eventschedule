<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->string('ai_style_instructions', 500)->nullable()->after('custom_labels');
            $table->string('ai_content_instructions', 500)->nullable()->after('ai_style_instructions');
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn(['ai_style_instructions', 'ai_content_instructions']);
        });
    }
};
