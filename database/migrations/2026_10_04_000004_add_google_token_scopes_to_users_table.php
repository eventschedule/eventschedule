<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * What a Google Calendar connection was granted. The import page asks for read access only;
     * sending events to Google needs more, and the app has to know which it holds. NULL is a
     * connection made before this column existed, when every connection asked for the full set.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('google_token_scopes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('google_token_scopes');
        });
    }
};
