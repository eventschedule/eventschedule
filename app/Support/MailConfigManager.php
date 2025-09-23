<?php

namespace App\Support;

class MailConfigManager
{
    public static function purgeResolvedMailer(?string $mailer = null): void
    {
        if (app()->bound('mail.manager')) {
            app('mail.manager')->purge($mailer);
        }

        app()->forgetInstance('mailer');

        if (method_exists(app(), 'forgetResolvedInstance')) {
            app()->forgetResolvedInstance('mailer');
        }
    }
}
