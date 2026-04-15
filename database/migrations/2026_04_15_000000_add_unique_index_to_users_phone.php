<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Convert old stub users created by storeMember() with random bcrypt passwords.
        // In hosted mode stubs have NULL email_verified_at; real users verify during registration.
        // OAuth users have non-null google_id/facebook_id so are excluded.
        // Note: selfhosted stubs have email_verified_at set (auto-verified), so they are NOT
        // caught by this query. There is no reliable way to distinguish them from legitimate
        // selfhosted users who claimed their accounts via password reset. This is acceptable
        // since SMS features are not available in selfhosted mode.
        DB::table('users')
            ->whereNotNull('password')
            ->whereNull('email_verified_at')
            ->whereNull('google_id')
            ->whereNull('google_oauth_id')
            ->whereNull('facebook_id')
            ->update(['password' => null]);

        // Normalize empty strings to NULL before deduplication
        DB::table('users')->where('phone', '')->update(['phone' => null]);

        // Normalize all phone numbers to E.164 format before deduplication
        $phones = DB::table('users')->whereNotNull('phone')->pluck('phone', 'id');
        foreach ($phones as $id => $phone) {
            $normalized = \App\Utils\PhoneUtils::normalize($phone);
            if ($normalized !== $phone) {
                $update = ['phone' => $normalized];
                if (is_null($normalized)) {
                    $update['phone_verified_at'] = null;
                }
                DB::table('users')->where('id', $id)->update($update);
            }
        }

        // Clear phone from duplicate stub users before adding unique constraint
        $duplicates = DB::table('users')
            ->select('phone')
            ->whereNotNull('phone')
            ->groupBy('phone')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('phone');

        foreach ($duplicates as $phone) {
            // Keep the phone on the user with verified phone or verified email (real account)
            // If none are verified, keep the most recently created one
            $keepId = DB::table('users')
                ->where('phone', $phone)
                ->orderByRaw('phone_verified_at IS NOT NULL DESC')
                ->orderByRaw('email_verified_at IS NOT NULL DESC')
                ->orderBy('id', 'desc')
                ->value('id');

            DB::table('users')
                ->where('phone', $phone)
                ->where('id', '!=', $keepId)
                ->update(['phone' => null, 'phone_verified_at' => null]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->unique('phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['phone']);
        });
    }
};
