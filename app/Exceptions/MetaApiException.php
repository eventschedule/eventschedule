<?php

namespace App\Exceptions;

/**
 * A Graph API error with Meta's own code, so a caller can tell "our access was withdrawn" from
 * "slow down" from "try again". The message is Meta's and is for the log and the operator:
 * never print it to a Business or a guest.
 *
 * Codes: https://developers.facebook.com/docs/graph-api/guides/error-handling
 */
class MetaApiException extends \Exception
{
    public function __construct(
        string $message,
        public readonly ?int $metaCode = null,
        public readonly ?int $metaSubcode = null,
        public readonly int $httpStatus = 0,
        public readonly ?string $userMessage = null,
    ) {
        parent::__construct($message, (int) $metaCode);
    }

    /** The token is invalid or expired, or the app lost a permission on the asset. */
    public function isPermission(): bool
    {
        $code = (int) $this->metaCode;

        // 100 with subcode 33: the object exists but this token may not see it, which is how a
        // withdrawn partner share answers.
        if ($code === 100 && (int) $this->metaSubcode === 33) {
            return true;
        }

        return in_array($code, [10, 102, 190], true) || ($code >= 200 && $code <= 299);
    }

    public function isThrottle(): bool
    {
        $code = (int) $this->metaCode;

        return in_array($code, [4, 17, 32, 613], true) || ($code >= 80000 && $code <= 80014);
    }

    public function isTransient(): bool
    {
        return $this->isThrottle() || in_array((int) $this->metaCode, [1, 2], true) || $this->httpStatus >= 500 || $this->httpStatus === 0;
    }
}
