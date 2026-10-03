<?php

namespace App\Logging;

use Illuminate\Log\Logger;
use Monolog\LogRecord;

/**
 * Masks email addresses in every log line before it is written (mask_emails() in app/helpers.php):
 * the message, and every string in its context. Wired as a `tap` on the file, stderr and syslog
 * channels in config/logging.php.
 *
 * Log lines are kept on the server and on hosted reach the platform's log retention, and a mail
 * transport quoting the recipient back in its error ("550 <someone@example.com> unknown") is the
 * usual way an address gets there; no call site writes one on purpose. The domain survives, which
 * is what anyone debugging a delivery failure needs.
 *
 * Sentry's log breadcrumbs are built from Laravel's MessageLogged event, which fires before
 * Monolog sees the record, so SentryScrubber masks the same pattern on its side.
 */
class MaskPersonalData
{
    public function __invoke(Logger $logger): void
    {
        $monolog = $logger->getLogger();

        if (! method_exists($monolog, 'pushProcessor')) {
            return;
        }

        $monolog->pushProcessor(static fn (LogRecord $record): LogRecord => $record->with(
            message: mask_emails($record->message),
            context: self::maskDeep($record->context),
        ));
    }

    /**
     * @param  mixed  $value
     * @return mixed
     */
    private static function maskDeep($value)
    {
        if (is_string($value)) {
            return mask_emails($value);
        }

        if (is_array($value)) {
            return array_map([self::class, 'maskDeep'], $value);
        }

        return $value;
    }
}
