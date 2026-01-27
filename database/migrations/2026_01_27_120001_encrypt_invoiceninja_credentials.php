<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration encrypts existing Invoice Ninja credentials that were stored
     * in plaintext. After running, the User model's encrypted casts will handle
     * automatic encryption/decryption.
     */
    public function up(): void
    {
        // Encrypt existing invoiceninja_api_key values
        DB::table('users')
            ->whereNotNull('invoiceninja_api_key')
            ->where('invoiceninja_api_key', '!=', '')
            ->orderBy('id')
            ->chunk(100, function ($users) {
                foreach ($users as $user) {
                    // Skip if already encrypted (starts with eyJ which is base64 for {"iv")
                    if (str_starts_with($user->invoiceninja_api_key, 'eyJ')) {
                        continue;
                    }

                    try {
                        $encrypted = Crypt::encryptString($user->invoiceninja_api_key);
                        DB::table('users')
                            ->where('id', $user->id)
                            ->update(['invoiceninja_api_key' => $encrypted]);
                    } catch (\Exception $e) {
                        // Log but continue - value might already be encrypted
                        \Log::warning('Failed to encrypt invoiceninja_api_key for user '.$user->id.': '.$e->getMessage());
                    }
                }
            });

        // Encrypt existing invoiceninja_webhook_secret values
        DB::table('users')
            ->whereNotNull('invoiceninja_webhook_secret')
            ->where('invoiceninja_webhook_secret', '!=', '')
            ->orderBy('id')
            ->chunk(100, function ($users) {
                foreach ($users as $user) {
                    // Skip if already encrypted
                    if (str_starts_with($user->invoiceninja_webhook_secret, 'eyJ')) {
                        continue;
                    }

                    try {
                        $encrypted = Crypt::encryptString($user->invoiceninja_webhook_secret);
                        DB::table('users')
                            ->where('id', $user->id)
                            ->update(['invoiceninja_webhook_secret' => $encrypted]);
                    } catch (\Exception $e) {
                        \Log::warning('Failed to encrypt invoiceninja_webhook_secret for user '.$user->id.': '.$e->getMessage());
                    }
                }
            });
    }

    /**
     * Reverse the migrations.
     *
     * WARNING: This will decrypt credentials back to plaintext, which is less secure.
     */
    public function down(): void
    {
        // Decrypt invoiceninja_api_key values
        DB::table('users')
            ->whereNotNull('invoiceninja_api_key')
            ->where('invoiceninja_api_key', '!=', '')
            ->orderBy('id')
            ->chunk(100, function ($users) {
                foreach ($users as $user) {
                    // Only decrypt if it looks encrypted
                    if (! str_starts_with($user->invoiceninja_api_key, 'eyJ')) {
                        continue;
                    }

                    try {
                        $decrypted = Crypt::decryptString($user->invoiceninja_api_key);
                        DB::table('users')
                            ->where('id', $user->id)
                            ->update(['invoiceninja_api_key' => $decrypted]);
                    } catch (\Exception $e) {
                        \Log::warning('Failed to decrypt invoiceninja_api_key for user '.$user->id.': '.$e->getMessage());
                    }
                }
            });

        // Decrypt invoiceninja_webhook_secret values
        DB::table('users')
            ->whereNotNull('invoiceninja_webhook_secret')
            ->where('invoiceninja_webhook_secret', '!=', '')
            ->orderBy('id')
            ->chunk(100, function ($users) {
                foreach ($users as $user) {
                    if (! str_starts_with($user->invoiceninja_webhook_secret, 'eyJ')) {
                        continue;
                    }

                    try {
                        $decrypted = Crypt::decryptString($user->invoiceninja_webhook_secret);
                        DB::table('users')
                            ->where('id', $user->id)
                            ->update(['invoiceninja_webhook_secret' => $decrypted]);
                    } catch (\Exception $e) {
                        \Log::warning('Failed to decrypt invoiceninja_webhook_secret for user '.$user->id.': '.$e->getMessage());
                    }
                }
            });
    }
};
