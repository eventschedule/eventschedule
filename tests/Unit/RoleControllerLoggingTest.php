<?php

namespace Tests\Unit;

use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Tests\TestCase;

class RoleControllerLoggingTest extends TestCase
{
    public function test_report_role_asset_parsing_issue_handles_logging_failures(): void
    {
        $controller = app(RoleController::class);

        Log::shouldReceive('warning')
            ->once()
            ->andThrow(new RuntimeException('unable to write to log'));

        $method = new \ReflectionMethod(RoleController::class, 'reportRoleAssetParsingIssue');
        $method->setAccessible(true);

        $method->invoke($controller, 'test_context', ['sample' => 'value'], new RuntimeException('parse failure'));

        $this->assertTrue(true);
    }
}
