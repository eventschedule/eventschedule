<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->text('sponsor_logos')->nullable()->after('default_curator_ids');
            $table->string('sponsor_section_title')->nullable()->after('sponsor_logos');
            $table->string('sponsor_section_title_en')->nullable()->after('sponsor_section_title');
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn(['sponsor_logos', 'sponsor_section_title', 'sponsor_section_title_en']);
        });
    }
};
