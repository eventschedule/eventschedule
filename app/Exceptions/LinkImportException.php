<?php

namespace App\Exceptions;

/**
 * A link somebody pasted on the import page could not be turned into events, for a reason they
 * can do something about.
 *
 * The message is written for that person and already translated, which is what makes it the one
 * exception from the link import whose message may reach the browser: it says what happened and
 * what to try instead, never what the server saw. reason() is a stable word for the page to act
 * on (for example, pointing at the image button when the answer is "add a screenshot").
 */
class LinkImportException extends \Exception
{
    public function __construct(private string $reason, string $message)
    {
        parent::__construct($message);
    }

    public function reason(): string
    {
        return $this->reason;
    }
}
