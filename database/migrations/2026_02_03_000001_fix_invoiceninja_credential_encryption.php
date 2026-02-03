<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Contracts\Encryption\DecryptException;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('invoiceninja_api_key')->nullable()->change();
            $table->text('invoiceninja_webhook_secret')->nullable()->change();
        });

        DB::table('users')
            ->whereNotNull('invoiceninja_api_key')
            ->orWhereNotNull('invoiceninja_webhook_secret')
            ->orderBy('id')
            ->chunk(100, function ($users) {
                foreach ($users as $user) {
                    $updates = [];
                    foreach (['invoiceninja_api_key', 'invoiceninja_webhook_secret'] as $field) {
                        if ($user->$field === null || $user->$field === '') {
                            continue;
                        }
                        try {
                            Crypt::decryptString($user->$field);
                        } catch (DecryptException $e) {
                            $updates[$field] = Crypt::encryptString($user->$field);
                        }
                    }
                    if (!empty($updates)) {
                        DB::table('users')->where('id', $user->id)->update($updates);
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('invoiceninja_api_key')->nullable()->change();
            $table->string('invoiceninja_webhook_secret')->nullable()->change();
        });
    }
};
