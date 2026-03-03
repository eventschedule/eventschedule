<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->text('custom_labels')->nullable()->after('sponsor_section_title_en');
        });

        // Migrate existing sponsor_section_title data into custom_labels JSON
        $roles = DB::table('roles')
            ->whereNotNull('sponsor_section_title')
            ->get(['id', 'sponsor_section_title', 'sponsor_section_title_en']);

        foreach ($roles as $role) {
            $label = ['value' => $role->sponsor_section_title];
            if ($role->sponsor_section_title_en) {
                $label['value_en'] = $role->sponsor_section_title_en;
            }

            DB::table('roles')
                ->where('id', $role->id)
                ->update(['custom_labels' => json_encode(['our_sponsors' => $label])]);
        }
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('custom_labels');
        });
    }
};
