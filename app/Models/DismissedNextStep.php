<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A suggestion the user turned down on the dashboard Next steps panel.
 *
 * This model owns the vocabulary shared by the two halves of the activation programme -
 * HomeController::getNextStepItems() offers the steps, SendActivationNudges emails them - so a
 * step type and the mail it silences cannot drift apart in two files.
 */
class DismissedNextStep extends Model
{
    protected $fillable = [
        'user_id',
        'role_id',
        'step_type',
    ];

    /**
     * The steps the panel can offer, and the only values a dismiss form may carry.
     *
     * Validated on the way in: without it the discriminator column accepts any string, and a
     * value that later collided with a real step type would silently suppress both a panel row
     * and an email.
     */
    public const STEP_TYPES = [
        'next_step_tickets',
        'next_step_payments',
        // Two types, not one, because they are two different asks. "You have never put a date on
        // this page" and "this page has been quiet for a month" happen at opposite ends of a
        // schedule's life, and a dismissal never expires - so folding them together lets a
        // day-one "not ready yet" silence the dormancy nudge two years later, on a schedule that
        // has since run and gone quiet. That nudge is the one this whole programme exists for.
        'next_step_first_event',
        'next_step_next_event',
    ];

    /**
     * Steps whose ask is a property of the ACCOUNT rather than the schedule, so one dismissal
     * answers it everywhere, including for schedules created later.
     *
     * payment_gateways()->connectedFor() resolves one gateway per USER: connecting it fixes every
     * schedule at once, so there is nothing schedule-specific to say no to. Asking an owner with
     * five schedules to turn the same ask down five times is not granularity, it is a loop.
     */
    public const ACCOUNT_WIDE_STEP_TYPES = ['next_step_payments'];

    /**
     * Which activation nudge emails an in-app dismissal also silences.
     *
     * The two event types line up with the two halves of the email side: no_event is the new
     * schedule that never published, the idle windows are the one that published and stopped.
     *
     * first_sale is deliberately absent. It congratulates rather than asks, so it has no dismiss
     * control on the panel and nothing can silence it.
     */
    public const NUDGE_KEYS = [
        'next_step_tickets' => ['no_ticket_type'],
        'next_step_payments' => ['no_gateway'],
        'next_step_first_event' => ['no_event'],
        'next_step_next_event' => ['idle_30', 'idle_60'],
    ];

    /**
     * The inverse of NUDGE_KEYS: which step types silence a given nudge key.
     *
     * Returns an empty array for a key nothing dismisses, which is how first_sale stays
     * unaffected without a special case in the query that calls this.
     */
    /** Whether a dismissal of this step covers the user's whole account rather than one schedule. */
    public static function isAccountWide(string $stepType): bool
    {
        return in_array($stepType, self::ACCOUNT_WIDE_STEP_TYPES, true);
    }

    public static function stepTypesForNudge(string $key): array
    {
        $types = [];

        foreach (self::NUDGE_KEYS as $stepType => $nudgeKeys) {
            if (in_array($key, $nudgeKeys, true)) {
                $types[] = $stepType;
            }
        }

        return $types;
    }
}
