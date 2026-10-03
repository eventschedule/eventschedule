<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Every click on a newsletter link stored the reader's raw IP address and browser, forever,
     * and nothing ever read either column: the stats use the URL and the time. Data kept for no
     * purpose is the kind GDPR Art. 5(1)(c) says not to keep, so both columns go, with what they
     * already hold.
     */
    public function up(): void
    {
        $columns = array_values(array_filter(
            ['ip_address', 'user_agent'],
            fn ($column) => Schema::hasColumn('newsletter_clicks', $column)
        ));

        if ($columns) {
            Schema::table('newsletter_clicks', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }

    public function down(): void
    {
        Schema::table('newsletter_clicks', function (Blueprint $table) {
            if (! Schema::hasColumn('newsletter_clicks', 'ip_address')) {
                $table->string('ip_address', 45)->nullable();
            }
            if (! Schema::hasColumn('newsletter_clicks', 'user_agent')) {
                $table->string('user_agent', 500)->nullable();
            }
        });
    }
};
