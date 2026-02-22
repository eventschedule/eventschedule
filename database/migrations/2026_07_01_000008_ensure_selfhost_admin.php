<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (config('app.hosted')) {
            return;
        }

        $hasAdmin = DB::table('users')->where('is_admin', true)->exists();
        if (!$hasAdmin) {
            DB::table('users')->orderBy('id')->limit(1)->update(['is_admin' => true]);
        }
    }
};
