<?php

namespace App\Http\Middleware;

use App\Services\Authorization\AuthorizationService;
use Closure;
use Illuminate\Http\Request;

class EnsureAbility
{
    public function __construct(private AuthorizationService $authorization)
    {
    }

    public function handle(Request $request, Closure $next, ...$abilities)
    {
        $user = $request->user();

        if (! $user) {
            abort(401, __('messages.unauthorized'));
        }

        $abilities = array_values(array_filter($abilities));

        if ($abilities === [] || $this->authorization->userHasAnyPermission($user, $abilities)) {
            return $next($request);
        }

        abort(403, __('messages.access_denied'));
    }
}
