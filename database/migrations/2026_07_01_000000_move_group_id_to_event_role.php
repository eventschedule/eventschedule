<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add group_id to event_role if it doesn't exist
        if (! Schema::hasColumn('event_role', 'group_id')) {
            Schema::table('event_role', function (Blueprint $table) {
                $table->foreignId('group_id')->nullable()->constrained()->onDelete('set null');
            });
        }

        // Remove group_id from events table
        if (Schema::hasColumn('events', 'group_id')) {
            if (config('database.default') === 'sqlite') {
                // For SQLite, we need to recreate the table without the group_id column
                // First, get all columns except group_id
                $columns = DB::select("PRAGMA table_info(events)");
                $columnNames = [];
                $columnDefinitions = [];
                
                foreach ($columns as $column) {
                    if ($column->name !== 'group_id') {
                        $columnNames[] = $column->name;
                        $columnDefinitions[] = $this->getColumnDefinition($column);
                    }
                }
                
                // Create new table without group_id
                $createTableSQL = "CREATE TABLE events_new (" . implode(', ', $columnDefinitions) . ")";
                DB::statement($createTableSQL);
                
                // Copy data to new table
                $columnList = implode(', ', $columnNames);
                DB::statement("INSERT INTO events_new ($columnList) SELECT $columnList FROM events");
                
                // Drop old table and rename new one
                DB::statement("DROP TABLE events");
                DB::statement("ALTER TABLE events_new RENAME TO events");
                
                // Recreate indexes
                $this->recreateIndexes();
            } else {
                Schema::table('events', function (Blueprint $table) {
                    $table->dropForeign(['group_id']);
                    $table->dropColumn('group_id');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add group_id column back to events table if not exists
        if (!Schema::hasColumn('events', 'group_id')) {
            Schema::table('events', function (Blueprint $table) {
                $table->foreignId('group_id')->nullable()->constrained()->onDelete('set null');
            });
        }
        // Remove group_id from event_role if exists
        if (Schema::hasColumn('event_role', 'group_id')) {
            Schema::table('event_role', function (Blueprint $table) {
                $table->dropForeign(['group_id']);
                $table->dropColumn('group_id');
            });
        }
    }

    /**
     * Get column definition for SQLite table recreation
     */
    private function getColumnDefinition($column): string
    {
        $definition = $column->name . ' ' . $column->type;
        
        if ($column->notnull) {
            $definition .= ' NOT NULL';
        }
        
        if ($column->pk) {
            $definition .= ' PRIMARY KEY';
            if ($column->type === 'INTEGER') {
                $definition .= ' AUTOINCREMENT';
            }
        }
        
        if ($column->dflt_value !== null) {
            $definition .= ' DEFAULT ' . $column->dflt_value;
        }
        
        return $definition;
    }

    /**
     * Recreate indexes for the events table
     */
    private function recreateIndexes(): void
    {
        // Get existing indexes
        $indexes = DB::select("PRAGMA index_list(events)");
        
        foreach ($indexes as $index) {
            if ($index->name !== 'sqlite_autoindex_events_1') { // Skip primary key index
                $indexInfo = DB::select("PRAGMA index_info(" . $index->name . ")");
                $columns = [];
                foreach ($indexInfo as $info) {
                    $columns[] = $info->name;
                }
                
                if (!empty($columns)) {
                    $unique = $index->unique ? 'UNIQUE ' : '';
                    $columnList = implode(', ', $columns);
                    DB::statement("CREATE {$unique}INDEX {$index->name} ON events ($columnList)");
                }
            }
        }
    }
}; 