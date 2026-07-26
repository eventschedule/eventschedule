<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Give the per-schedule flag a third state: "has not decided".
     *
     * It shipped as a boolean defaulting to true, which enrolled every schedule the
     * moment the operator joined a network. Making new schedules start out unlisted
     * instead cannot be done by flipping that default, because the eligibility query
     * treats an explicit false as a veto over the whole event - and it has to, or one
     * willing schedule would drag every co-listed participant onto the network with
     * it. With a two-state column every unclaimed placeholder and every venue invented
     * by calendar sync would arrive as a veto and suppress events that publish
     * perfectly well today.
     *
     * So: null means nobody has answered, true means listed, false means opted out.
     * Existing rows keep whatever they hold - a schedule that was already federating
     * carries on, which is the point of only changing what happens from here.
     */
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->boolean('federation_enabled')->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // The column is about to stop accepting null, so the undecided have to become
        // something. True matches the default they would have been created with.
        DB::table('roles')->whereNull('federation_enabled')->update(['federation_enabled' => true]);

        Schema::table('roles', function (Blueprint $table) {
            $table->boolean('federation_enabled')->default(true)->change();
        });
    }
};
