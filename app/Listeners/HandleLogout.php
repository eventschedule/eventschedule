<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Authorization\AuthorizationService;
use Illuminate\Auth\Events\Logout;
use Illuminate\Http\Request;

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

        $request = request();

        if ($request instanceof Request) {
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
