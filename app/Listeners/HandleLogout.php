<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Authorization\AuthorizationService;
use Illuminate\Auth\Events\Logout;

class HandleLogout
{
    public function __construct(
        private AuthorizationService $authorization,
        private AuditLogger $auditLogger
    ) {
    }

    public function handle(Logout $event): void
    {
        $user = $event->user;

        if (! $user instanceof User) {
            return;
        }

        $this->authorization->forgetUserPermissions($user);

        $request = $event->request;

        if ($request) {
            $this->auditLogger->logFromRequest($request, $user, 'logout', 'auth', $user->getKey(), [
                'guard' => $event->guard,
            ]);

            return;
        }

        $this->auditLogger->log($user, 'logout', 'auth', $user->getKey(), [
            'guard' => $event->guard,
        ]);
    }
}
