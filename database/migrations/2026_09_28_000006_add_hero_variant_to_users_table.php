<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // The homepage headline variant (App\Utils\HeroExperiment) the account saw before
            // signing up, carried in es_attribution. Its signup count per variant.
            $table->string('hero_variant', 32)->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['hero_variant']);
            $table->dropColumn('hero_variant');
        });
    }
};
