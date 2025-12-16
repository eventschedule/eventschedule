#!/usr/bin/env php
<?php

/**
 * Flyer Image Implementation Verification Script
 * 
 * This script verifies that event flyer images are properly configured
 * and accessible through the API.
 * 
 * Run with: php tests/verify_flyer_images.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Event;
use App\Models\Image;
use App\Utils\UrlUtils;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "========================================\n";
echo "Flyer Image Verification Script\n";
echo "========================================\n\n";

// Test 1: Check database schema
echo "✓ Test 1: Database Schema\n";
echo "-----------------------------------\n";

$columns = DB::select("SHOW COLUMNS FROM events LIKE '%flyer%'");

if (count($columns) === 0) {
    echo "❌ FAIL: No flyer columns found in events table\n";
    echo "   Run migration: php artisan migrate --path=/database/migrations/2026_09_01_010000_add_image_relations.php\n\n";
    exit(1);
}

foreach ($columns as $column) {
    echo "  ✓ Column found: {$column->Field} ({$column->Type})\n";
}

$hasUrl = Schema::hasColumn('events', 'flyer_image_url');
$hasId = Schema::hasColumn('events', 'flyer_image_id');

if (!$hasUrl || !$hasId) {
    echo "❌ FAIL: Missing required columns\n";
    echo "   flyer_image_url: " . ($hasUrl ? 'exists' : 'MISSING') . "\n";
    echo "   flyer_image_id: " . ($hasId ? 'exists' : 'MISSING') . "\n\n";
    exit(1);
}

echo "  ✓ All required columns exist\n\n";

// Test 2: Check Event model configuration
echo "✓ Test 2: Event Model Configuration\n";
echo "-----------------------------------\n";

$event = new Event();
$fillable = $event->getFillable();

$hasFillableUrl = in_array('flyer_image_url', $fillable);
$hasFillableId = in_array('flyer_image_id', $fillable);

echo "  Fillable fields check:\n";
echo "    flyer_image_url: " . ($hasFillableUrl ? '✓ present' : '❌ MISSING') . "\n";
echo "    flyer_image_id: " . ($hasFillableId ? '✓ present' : '❌ MISSING') . "\n";

if (!$hasFillableUrl || !$hasFillableId) {
    echo "\n❌ FAIL: Fields not in fillable array\n\n";
    exit(1);
}

// Check relationship
$hasRelationship = method_exists($event, 'flyerImage');
echo "  flyerImage() relationship: " . ($hasRelationship ? '✓ exists' : '❌ MISSING') . "\n";

if (!$hasRelationship) {
    echo "\n❌ FAIL: flyerImage() relationship not defined\n\n";
    exit(1);
}

echo "\n";

// Test 3: Check database data
echo "✓ Test 3: Database Content\n";
echo "-----------------------------------\n";

$stats = DB::selectOne("
    SELECT 
        COUNT(*) as total_events,
        COUNT(flyer_image_url) as with_url,
        COUNT(flyer_image_id) as with_id
    FROM events
");

echo "  Total events: {$stats->total_events}\n";
echo "  Events with flyer_image_url: {$stats->with_url}\n";
echo "  Events with flyer_image_id: {$stats->with_id}\n";

if ($stats->total_events > 0) {
    $percentage = round(($stats->with_url / $stats->total_events) * 100, 1);
    echo "  Coverage: {$percentage}% of events have flyer URLs\n";
}

echo "\n";

// Test 4: Check toApiData() method
echo "✓ Test 4: API Serialization\n";
echo "-----------------------------------\n";

$sampleEvent = Event::first();

if (!$sampleEvent) {
    echo "  ⚠️  No events in database to test\n\n";
} else {
    $apiData = $sampleEvent->toApiData();
    
    echo "  Sample Event: {$sampleEvent->name}\n";
    echo "  Event ID: " . UrlUtils::encodeId($sampleEvent->id) . "\n";
    
    $hasKeyInApi = array_key_exists('flyer_image_url', $apiData);
    echo "  flyer_image_url in API data: " . ($hasKeyInApi ? '✓ YES' : '❌ NO') . "\n";
    
    if ($hasKeyInApi) {
        $value = $apiData['flyer_image_url'];
        $displayValue = $value ?? 'NULL';
        echo "  API value: {$displayValue}\n";
        
        if ($value) {
            // Check if it's a valid path
            if (str_starts_with($value, '/storage/') || str_starts_with($value, 'http')) {
                echo "  ✓ Value format looks correct\n";
            } else {
                echo "  ⚠️  Unexpected value format: {$value}\n";
            }
        }
    } else {
        echo "\n❌ FAIL: flyer_image_url not in toApiData() response\n\n";
        exit(1);
    }
}

echo "\n";

// Test 5: Check events with flyer images
echo "✓ Test 5: Events with Flyer Images\n";
echo "-----------------------------------\n";

$eventsWithFlyers = Event::whereNotNull('flyer_image_url')
    ->orWhereNotNull('flyer_image_id')
    ->orderBy('updated_at', 'desc')
    ->limit(5)
    ->get();

if ($eventsWithFlyers->count() === 0) {
    echo "  ℹ️  No events with flyer images found\n";
    echo "     This is normal if no flyers have been uploaded yet\n\n";
} else {
    echo "  Found {$eventsWithFlyers->count()} event(s) with flyers:\n\n";
    
    foreach ($eventsWithFlyers as $event) {
        echo "  • {$event->name}\n";
        echo "    ID: " . UrlUtils::encodeId($event->id) . "\n";
        echo "    flyer_image_id: " . ($event->flyer_image_id ?? 'NULL') . "\n";
        echo "    flyer_image_url: " . ($event->flyer_image_url ?? 'NULL') . "\n";
        
        // Verify relationship loads
        if ($event->flyer_image_id) {
            $image = $event->flyerImage;
            if ($image) {
                echo "    ✓ Image relationship loads (Image #{$image->id})\n";
                echo "      Path: {$image->path}\n";
                
                // Check if URL matches
                if ($event->flyer_image_url !== $image->path) {
                    echo "    ⚠️  WARNING: URL mismatch!\n";
                    echo "      Event URL: {$event->flyer_image_url}\n";
                    echo "      Image path: {$image->path}\n";
                }
            } else {
                echo "    ⚠️  WARNING: Image relationship is NULL (orphaned reference)\n";
            }
        }
        
        echo "\n";
    }
}

// Test 6: Check for orphaned references
echo "✓ Test 6: Data Integrity\n";
echo "-----------------------------------\n";

$orphaned = Event::whereNotNull('flyer_image_id')
    ->whereDoesntHave('flyerImage')
    ->count();

if ($orphaned > 0) {
    echo "  ⚠️  Found {$orphaned} event(s) with flyer_image_id but no matching image\n";
    echo "     These events reference deleted images\n";
} else {
    echo "  ✓ No orphaned image references found\n";
}

$missingUrl = Event::whereNotNull('flyer_image_id')
    ->whereNull('flyer_image_url')
    ->count();

if ($missingUrl > 0) {
    echo "  ⚠️  Found {$missingUrl} event(s) with flyer_image_id but NULL flyer_image_url\n";
    echo "     Run this command to fix:\n";
    echo "     php artisan tinker\n";
    echo "     >>> Event::whereNotNull('flyer_image_id')->whereNull('flyer_image_url')->get()->each(fn(\$e) => \$e->flyer_image_url = \$e->flyerImage?->path and \$e->save());\n";
} else {
    echo "  ✓ All events with flyer_image_id have flyer_image_url\n";
}

echo "\n";

// Final summary
echo "========================================\n";
echo "Summary\n";
echo "========================================\n\n";

$allTestsPassed = $hasUrl && $hasId && $hasFillableUrl && $hasFillableId && $hasRelationship;

if ($allTestsPassed) {
    echo "✅ ALL TESTS PASSED\n\n";
    echo "The backend is correctly configured to handle flyer images.\n";
    echo "The flyer_image_url field is present in the API response.\n\n";
    
    if ($stats->with_url > 0) {
        echo "Found {$stats->with_url} event(s) with flyer images in the database.\n";
    } else {
        echo "No events with flyer images yet - upload some to test the full flow!\n";
    }
    
    echo "\nAPI Endpoint: POST /api/events/flyer/{event_id}\n";
    echo "Response includes: flyer_image_url\n\n";
    
    if ($missingUrl > 0 || $orphaned > 0) {
        echo "⚠️  Note: Some data integrity issues found (see Test 6 above)\n";
        exit(0);
    }
    
    exit(0);
} else {
    echo "❌ SOME TESTS FAILED\n\n";
    echo "Review the output above for details.\n\n";
    exit(1);
}
