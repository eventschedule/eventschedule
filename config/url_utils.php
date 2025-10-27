<?php

return [
    /*
    |--------------------------------------------------------------------------
    | URL utility SSL verification
    |--------------------------------------------------------------------------
    |
    | Controls whether the internal URL utilities verify SSL certificates when
    | making outbound HTTP requests. This should generally remain enabled, but
    | can be toggled off in local or containerized development environments
    | where certificate stores may be missing by setting URL_UTILS_VERIFY_SSL
    | to false in the environment configuration.
    |
    */
    'verify_ssl' => env('URL_UTILS_VERIFY_SSL', true),
];
