<?php

namespace App\Services\Audit;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogger
{
    public function log(?User $actor, string $action, string $resourceType, ?int $resourceId = null, array $metadata = []): AuditLog
    {
        $context = $this->resolveRequestContext();

        return AuditLog::create([
            'user_id' => $actor?->getKey(),
            'action' => $action,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'metadata' => $metadata ?: null,
            'ip' => $context['ip'],
            'user_agent' => $context['user_agent'],
        ]);
    }

    public function logFromRequest(Request $request, ?User $actor, string $action, string $resourceType, ?int $resourceId = null, array $metadata = []): AuditLog
    {
        return AuditLog::create([
            'user_id' => $actor?->getKey(),
            'action' => $action,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'metadata' => $metadata ?: null,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }

    protected function resolveRequestContext(): array
    {
        try {
            $request = request();
        } catch (\Throwable $exception) {
            $request = null;
        }

        return [
            'ip' => $request instanceof Request ? $request->ip() : null,
            'user_agent' => $request instanceof Request ? $request->userAgent() : null,
        ];
    }
}
