<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            // A shared mailbox (a team inbox, say) that receives a copy of the schedule's owner
            // notifications on top of each editor's own account email (issue #124). Nothing is
            // sent to it until someone confirms it from the address itself, which is what
            // notification_email_verified_at records; changing the address clears it.
            //
            // notification_email_settings is {"new_request": true, "new_sale": false, ...}, read
            // through Role::notificationEmailSettings(), which supplies the defaults.
            //
            // Deliberately NOT fillable (RoleController::update() writes them, so a hand-made POST
            // cannot set verified_at), hidden from toArray() (toData() feeds pickers that list
            // other people's schedules), and NOT in BackupService::ROLE_EXPORT_FIELDS: a
            // confirmation belongs to this install, never to a restored copy.
            //
            // TEXT rather than varchar(255): roles is at InnoDB's 65,535-byte row limit, and a
            // utf8mb4 varchar(255) reserves 1,020 bytes of it, which MySQL refuses (error 1118).
            // RoleUpdateRequest still caps it at 255 characters.
            $table->text('notification_email')->nullable();
            $table->timestamp('notification_email_verified_at')->nullable();
            $table->json('notification_email_settings')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn(['notification_email', 'notification_email_verified_at', 'notification_email_settings']);
        });
    }
};
