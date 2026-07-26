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
        // Daily rollup of clicks sent out to federated instances. Federation's whole
        // promise is "discovery traffic flows to your installation", and without this
        // an operator has no evidence any of it happened.
        //
        // Same shape as the analytics_*_daily rollups (see
        // AnalyticsSocialClicksDaily::incrementClick).
        Schema::create('federation_clicks_daily', function (Blueprint $table) {
            $table->id();
            $table->foreignId('federated_instance_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->unsignedInteger('clicks')->default(0);
            $table->timestamps();

            $table->unique(['federated_instance_id', 'date'], 'federation_clicks_instance_date_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('federation_clicks_daily');
    }
};
