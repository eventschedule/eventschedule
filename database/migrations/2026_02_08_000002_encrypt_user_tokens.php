<?php

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Convert columns to text and encrypt existing plaintext OAuth tokens and payment_secret values.
     */
    public function up(): void
    {
        $columns = ['google_token', 'google_refresh_token', 'facebook_token', 'payment_secret'];

        Schema::table('users', function (Blueprint $table) use ($columns) {
            foreach ($columns as $column) {
                $table->text($column)->nullable()->change();
            }
        });

        DB::table('users')
            ->whereNotNull('google_token')
            ->orWhereNotNull('google_refresh_token')
            ->orWhereNotNull('facebook_token')
            ->orWhereNotNull('payment_secret')
            ->orderBy('id')
            ->chunk(100, function ($users) use ($columns) {
                foreach ($users as $user) {
                    $updates = [];
                    foreach ($columns as $column) {
                        if ($user->$column === null || $user->$column === '') {
                            continue;
                        }
                        try {
                            Crypt::decryptString($user->$column);
                        } catch (DecryptException $e) {
                            $updates[$column] = Crypt::encryptString($user->$column);
                        }
                    }
                    if (! empty($updates)) {
                        DB::table('users')->where('id', $user->id)->update($updates);
                    }
                }
            });
    }

    /**
     * Decrypt values back to plaintext and convert columns back to string.
     */
    public function down(): void
    {
        $columns = ['google_token', 'google_refresh_token', 'facebook_token', 'payment_secret'];

        DB::table('users')
            ->whereNotNull('google_token')
            ->orWhereNotNull('google_refresh_token')
            ->orWhereNotNull('facebook_token')
            ->orWhereNotNull('payment_secret')
            ->orderBy('id')
            ->chunk(100, function ($users) use ($columns) {
                foreach ($users as $user) {
                    $updates = [];
                    foreach ($columns as $column) {
                        if ($user->$column === null || $user->$column === '') {
                            continue;
                        }
                        try {
                            $updates[$column] = Crypt::decryptString($user->$column);
                        } catch (DecryptException $e) {
                            // Already plaintext, skip
                        }
                    }
                    if (! empty($updates)) {
                        DB::table('users')->where('id', $user->id)->update($updates);
                    }
                }
            });

        Schema::table('users', function (Blueprint $table) use ($columns) {
            foreach ($columns as $column) {
                $table->string($column)->nullable()->change();
            }
        });
    }
};
