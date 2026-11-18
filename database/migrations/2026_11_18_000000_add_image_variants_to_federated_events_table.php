<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The light copies of a listing's stored picture and its size (HasImageVariants), so a poster
     * from another site keeps its shape on /browse and is not served at full size. Filled by
     * federation:maintain a few at a time, for new pictures and for those already stored.
     */
    public function up(): void
    {
        Schema::table('federated_events', function (Blueprint $table) {
            $table->json('image_variants')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('federated_events', function (Blueprint $table) {
            $table->dropColumn('image_variants');
        });
    }
};
