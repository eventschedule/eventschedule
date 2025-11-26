<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('venue_scope')->default('all');
            $table->string('curator_scope')->default('all');
            $table->string('talent_scope')->default('all');
            $table->json('venue_ids')->nullable();
            $table->json('curator_ids')->nullable();
            $table->json('talent_ids')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'venue_scope',
                'curator_scope',
                'talent_scope',
                'venue_ids',
                'curator_ids',
                'talent_ids',
            ]);
        });
    }
};
