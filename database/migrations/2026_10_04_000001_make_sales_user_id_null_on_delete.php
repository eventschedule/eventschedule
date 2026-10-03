<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * sales.user_id cascaded, so deleting a buyer's account deleted their purchases from the
     * organiser's sales records: the organiser's revenue history, tax records and door list lost
     * rows because a customer closed an account. A sale is the organiser's record. It keeps the
     * name and email it was made under, and only loses the link to the account
     * (App\Services\AccountDeletionService).
     *
     * The constraint is replaced in place. foreign_key_checks is off for the ADD so MySQL can do
     * it INPLACE without a table copy (every existing row already satisfies it), and
     * lock_wait_timeout is short so a busy sales table makes the deploy fail and retry rather than
     * queue every checkout behind the ALTER.
     */
    public function up(): void
    {
        $this->replace('SET NULL');
    }

    public function down(): void
    {
        $this->replace('CASCADE');
    }

    private function replace(string $onDelete): void
    {
        $previous = DB::selectOne('SELECT @@SESSION.lock_wait_timeout AS value')->value;
        DB::statement('SET SESSION lock_wait_timeout = 10');

        try {
            $exists = DB::selectOne(
                "SELECT COUNT(*) AS n FROM information_schema.TABLE_CONSTRAINTS
                  WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'sales'
                    AND CONSTRAINT_NAME = 'sales_user_id_foreign' AND CONSTRAINT_TYPE = 'FOREIGN KEY'"
            )->n > 0;

            if ($exists) {
                DB::statement('ALTER TABLE `sales` DROP FOREIGN KEY `sales_user_id_foreign`');
            }

            DB::statement('SET foreign_key_checks = 0');

            try {
                DB::statement(
                    "ALTER TABLE `sales` ADD CONSTRAINT `sales_user_id_foreign` FOREIGN KEY (`user_id`)
                     REFERENCES `users` (`id`) ON DELETE {$onDelete}, ALGORITHM=INPLACE, LOCK=NONE"
                );
            } finally {
                DB::statement('SET foreign_key_checks = 1');
            }
        } finally {
            DB::statement('SET SESSION lock_wait_timeout = '.(int) $previous);
        }
    }
};
