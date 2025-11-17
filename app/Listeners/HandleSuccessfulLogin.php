<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Authorization\AuthorizationService;
use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Request;

class HandleSuccessfulLogin
{
    public function __construct(
        private AuthorizationService $authorization,
        private AuditLogger $auditLogger
    ) {
    }

    public function handle(Login $event): void
    {
        $user = $event->user;

        if (! $user instanceof User) {
            return;
        }

        $this->authorization->warmUserPermissions($user);

        $user->forceFill(['last_login_at' => now()])->saveQuietly();

        $request = null;

        try {
            $request = request();
        } catch (BindingResolutionException) {
            // Ignore missing request bindings and fall back to generic logging below.
        }

        if ($request instanceof Request) {
            $this->auditLogger->logFromRequest(
                $request,
                $user,
                'login',
                'auth',
                $user->getKey(),
                [
                    'guard' => $event->guard,
                ]
            );

            return;
        }

        $this->auditLogger->log($user, 'login', 'auth', $user->getKey(), [
            'guard' => $event->guard,
        ]);
    }
}
