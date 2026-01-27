<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration adds secure API key hashing. After running:
     * - api_key: Contains first 8 chars of SHA256(raw_key) for fast DB lookup
     * - api_key_hash: Contains bcrypt hash of full key for secure verification
     *
     * Existing users with API keys will have their keys migrated.
     * The plaintext key in api_key is converted to a prefix, so existing
     * API keys will stop working until users regenerate them.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('api_key_hash')->nullable()->after('api_key');
        });

        // Migrate existing API keys to hashed format
        // Note: After this migration, users with existing API keys will need to
        // regenerate them since we're converting plaintext to prefix format
        DB::table('users')->whereNotNull('api_key')->orderBy('id')->chunk(100, function ($users) {
            foreach ($users as $user) {
                // Store the hash of the existing key
                $fullHash = Hash::make($user->api_key);
                // Convert api_key to prefix format (first 8 chars of SHA256)
                $prefix = substr(hash('sha256', $user->api_key), 0, 8);

                DB::table('users')
                    ->where('id', $user->id)
                    ->update([
                        'api_key' => $prefix,
                        'api_key_hash' => $fullHash,
                    ]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('api_key_hash');
        });

        // Note: Cannot restore original api_key values since hashes are one-way
        // Users will need to regenerate their API keys
    }
};
