<?php

namespace App\Services;

use RuntimeException;

/**
 * Another schedule took the subdomain between our availability check and our write.
 *
 * Raised by ScheduleDeletionService so a caller can answer with __('messages.subdomain_taken')
 * rather than leaking a duplicate-key QueryException to the user as a 500.
 */
class SubdomainUnavailableException extends RuntimeException {}
