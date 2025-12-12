<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AddEventPasswordHash extends Migration
{
    public function up()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('event_password_hash')->nullable()->after('event_password');
        });

        // Migrate existing plaintext passwords to hashes
        DB::table('events')->whereNotNull('event_password')->where('event_password', '<>', '')->orderBy('id')->chunkById(100, function ($events) {
            foreach ($events as $e) {
                try {
                    $hash = Hash::make($e->event_password);
                    DB::table('events')->where('id', $e->id)->update(['event_password_hash' => $hash, 'event_password' => null]);
                } catch (\Throwable $ex) {
                    // If Hash fails for a record, skip it.
                    continue;
                }
            }
        });
    }

    public function down()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('event_password_hash');
        });
    }
}
