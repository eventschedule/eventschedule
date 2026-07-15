<?php

use App\Utils\CountryUtils;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Convert 3-letter ISO 3166-1 alpha-3 country codes stored on roles (e.g. "isr")
     * to their lowercase alpha-2 equivalent ("il"). The venue/schedule country picker
     * (intl-tel-input) only accepts alpha-2 and throws "No country data for '<code>'"
     * on an alpha-3 value. Only 3-character values are touched; alpha-2 and any
     * unmappable value are left as-is (matches CountryUtils::normalizeCountryCode).
     */
    public function up(): void
    {
        DB::table('roles')
            ->whereNotNull('country_code')
            ->whereRaw('CHAR_LENGTH(country_code) = 3')
            ->select('id', 'country_code')
            ->orderBy('id')
            ->chunkById(500, function ($roles) {
                foreach ($roles as $role) {
                    $normalized = CountryUtils::normalizeCountryCode($role->country_code);

                    // Only write when the value actually maps to a shorter alpha-2 code.
                    if ($normalized !== null && $normalized !== strtolower($role->country_code)) {
                        DB::table('roles')->where('id', $role->id)->update(['country_code' => $normalized]);
                    }
                }
            });
    }

    /**
     * Reverse the migrations.
     * No reversal needed - alpha-2 is the required format.
     */
    public function down(): void
    {
        // No reversal needed
    }
};
