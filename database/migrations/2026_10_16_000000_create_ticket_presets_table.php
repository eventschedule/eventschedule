<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * A schedule's ticket presets (Edit schedule > Settings > Ticket presets): named ticket setups an
 * event's Tickets tab starts from, one of them the default for new events (issue #130).
 *
 * A table of its own because `roles` is at MySQL's 65,535-byte row limit (2026_09_07_000000).
 *
 * kind: how people sign up, in the Tickets tab's own terms: `rsvp` (free registration), `tickets`
 * (sell tickets) or `none` (not needed). Null only on the preset copied from `default_tickets`
 * below, which never recorded one: a new event then keeps today's rule and takes the mode of the
 * schedule's last event (EventController::create()).
 *
 * settings: the shape `roles.default_tickets` had (currency_code, payment_method, the option
 * fields, tickets[] as Ticket::toClonePayload() wrote them, addons[], promo_codes[]) plus
 * rsvp_limit, without promo codes' ticket ids. App\Models\TicketPreset reads it.
 *
 * The data step copies every schedule's `default_tickets` into one preset with no name (shown as
 * "Usual tickets") and no kind, as the default. `default_tickets` itself is left where it is and is
 * no longer read or written. Safe to run twice: a schedule that already has a preset is skipped.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ticket_presets')) {
            Schema::create('ticket_presets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('role_id')->constrained()->cascadeOnDelete();
                $table->string('name', 100)->nullable();
                $table->string('kind', 16)->nullable();
                $table->boolean('is_default')->default(false);
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->json('settings')->nullable();
                $table->timestamps();

                $table->index(['role_id', 'sort_order']);
            });
        }

        DB::table('roles')
            ->select(['id', 'default_tickets'])
            ->whereNotNull('default_tickets')
            ->chunkById(200, function ($roles) {
                $now = now();

                foreach ($roles as $role) {
                    $settings = json_decode((string) $role->default_tickets, true);

                    if (! is_array($settings)) {
                        Log::warning('Ticket presets migration: default_tickets of schedule '.$role->id.' is not a JSON object, skipped');

                        continue;
                    }

                    if (DB::table('ticket_presets')->where('role_id', $role->id)->exists()) {
                        continue;
                    }

                    DB::table('ticket_presets')->insert([
                        'role_id' => $role->id,
                        'name' => null,
                        'kind' => null,
                        'is_default' => true,
                        'sort_order' => 0,
                        'settings' => json_encode(self::withoutEventOnlyFields($settings)),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_presets');
    }

    /**
     * Promo codes' ticket ids name the ticket types of the event the default was saved from, which
     * no new event has. The ticket types themselves are kept as they were saved, passes included:
     * a new event opens on them exactly as it did (TicketPresets::applyToModel()).
     */
    private static function withoutEventOnlyFields(array $settings): array
    {
        $settings['promo_codes'] = array_values(array_map(function ($code) {
            if (! is_array($code)) {
                return [];
            }
            unset($code['ticket_ids'], $code['expires_at']);

            return $code;
        }, is_array($settings['promo_codes'] ?? null) ? $settings['promo_codes'] : []));

        return $settings;
    }
};
