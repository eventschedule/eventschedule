<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_experiment_stats', function (Blueprint $table) {
            $table->id();
            $table->string('experiment', 32);
            $table->string('variant', 32);
            // UTC day. Unique with the two keys above so HeroExperiment::recordEvent()'s
            // "INSERT ... ON DUPLICATE KEY UPDATE" counter never creates duplicates.
            $table->date('date');
            $table->unsignedInteger('visitors')->default(0); // first assignment of a variant in a browser session
            $table->unsignedInteger('clicks')->default(0);   // first click on a sign-up link in that session
            $table->unique(['experiment', 'variant', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_experiment_stats');
    }
};
