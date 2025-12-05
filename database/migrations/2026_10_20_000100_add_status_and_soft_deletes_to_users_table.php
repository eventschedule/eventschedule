<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'status')) {
                $table->string('status')->default('active')->after('language_code');
            }

            if (! Schema::hasColumn('users', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $columns = Schema::getColumnListing('users');
        $deletedAtIndex = array_search('deleted_at', $columns, true);
        $createdAtIndex = array_search('created_at', $columns, true);
        $shouldDropSoftDeletes = $deletedAtIndex !== false
            && $createdAtIndex !== false
            && $deletedAtIndex > $createdAtIndex;

        Schema::table('users', function (Blueprint $table) use ($shouldDropSoftDeletes) {
            if (Schema::hasColumn('users', 'status')) {
                $table->dropColumn('status');
            }

            if ($shouldDropSoftDeletes) {
                $table->dropSoftDeletes();
            }
        });
    }
};
