<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Stripe refused, or never answered, a request to cancel a schedule's subscription while the
 * schedule was being deleted.
 *
 * Its own type so every delete path can abort on exactly this and nothing else: carrying on with
 * the deletion leaves a subscription that Stripe keeps charging and that nothing in the app can
 * reach any more. The message is internal - callers report() it and show a generic error.
 */
class BillingCancellationException extends RuntimeException
{
    //
}
