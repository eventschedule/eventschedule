<?php

namespace App\Exceptions;

/**
 * The requested appointment slot is not bookable - taken, outside the type's hours, or in the past.
 *
 * Distinct from a plain BusinessException so the caller knows whether attaching a refreshed slot list to
 * the response makes sense. The picker's recovery handler REPLACES the whole day when it sees `slots`,
 * clearing the guest's selection and bouncing them back to step 1 - the right response to "that time
 * just went", and the wrong one for "wait a moment" or "this booking can no longer be changed", where
 * availability is not the problem and the guest simply loses their place.
 */
class SlotUnavailableException extends BusinessException
{
    //
}
