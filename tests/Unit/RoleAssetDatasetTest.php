<?php

namespace Tests\Unit;

use App\Http\Controllers\RoleController;
use ReflectionMethod;
use Tests\TestCase;

class RoleAssetDatasetTest extends TestCase
{
    public function test_normalize_role_asset_dataset_backfills_name_fields(): void
    {
        $controller = app(RoleController::class);

        $rawDataset = [
            (object) ['label' => 'Header Label'],
            ['title' => 'Title Value'],
            ['value' => 'value_entry'],
            ['id' => 99],
            ['slug' => 'slug-value'],
            'SimpleName',
            42,
            ['name' => 'already_there', 'extra' => 'test'],
            ['colors' => ['#fff', '#000'], 'title' => 'Gradient'],
        ];

        $normalized = $this->callPrivateControllerMethod($controller, 'normalizeRoleAssetDataset', $rawDataset);

        $this->assertIsArray($normalized);
        $this->assertSame('Header Label', $normalized[0]['name']);
        $this->assertSame('Title Value', $normalized[1]['name']);
        $this->assertSame('value_entry', $normalized[2]['name']);
        $this->assertSame('99', $normalized[3]['name']);
        $this->assertSame('slug-value', $normalized[4]['name']);
        $this->assertSame('SimpleName', $normalized[5]['name']);
        $this->assertSame('42', $normalized[6]['name']);
        $this->assertSame('already_there', $normalized[7]['name']);
        $this->assertSame(['#fff', '#000'], $normalized[8]['colors']);
        $this->assertSame('Gradient', $normalized[8]['name']);
    }

    public function test_prepare_name_options_uses_backfilled_names(): void
    {
        $controller = app(RoleController::class);

        $rawDataset = [
            (object) ['label' => 'Header Label'],
            ['title' => 'Another Title'],
            'SimpleName',
        ];

        $normalized = $this->callPrivateControllerMethod($controller, 'normalizeRoleAssetDataset', $rawDataset);
        $options = $this->callPrivateControllerMethod($controller, 'prepareNameOptions', $normalized);

        $this->assertArrayHasKey('Header Label', $options);
        $this->assertArrayHasKey('Another Title', $options);
        $this->assertArrayHasKey('SimpleName', $options);
    }

    private function callPrivateControllerMethod(RoleController $controller, string $method, ...$arguments)
    {
        $reflection = new ReflectionMethod($controller, $method);
        $reflection->setAccessible(true);

        return $reflection->invoke($controller, ...$arguments);
    }
}
