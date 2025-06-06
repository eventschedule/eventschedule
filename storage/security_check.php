<?php

/**
 * Security Check Script
 * Run this script to check for common security issues
 */

echo "🔒 Security Check Report\n";
echo "=======================\n\n";

$issues = [];
$warnings = [];
$passed = [];

// Check if .env file exists and is not in version control
if (file_exists('.env')) {
    $passed[] = "✅ .env file exists";
    
    // Check if .env is in .gitignore
    $gitignore = file_get_contents('.gitignore');
    if (strpos($gitignore, '.env') !== false) {
        $passed[] = "✅ .env file is properly excluded from version control";
    } else {
        $issues[] = "❌ .env file should be in .gitignore";
    }
} else {
    $warnings[] = "⚠️  .env file not found (using defaults)";
}

// Check for security headers middleware
$bootstrapApp = file_get_contents('bootstrap/app.php');
if (strpos($bootstrapApp, 'SecurityHeaders') !== false) {
    $passed[] = "✅ Security headers middleware is registered";
} else {
    $issues[] = "❌ Security headers middleware is not registered";
}

// Check session encryption setting
$sessionConfig = file_get_contents('config/session.php');
if (strpos($sessionConfig, "'encrypt' => env('SESSION_ENCRYPT', true)") !== false) {
    $passed[] = "✅ Session encryption is enabled by default";
} else {
    $warnings[] = "⚠️  Session encryption should be enabled in production";
}

// Check Laravel version for known vulnerabilities
$composerLock = json_decode(file_get_contents('composer.lock'), true);
$laravelVersion = null;
foreach ($composerLock['packages'] as $package) {
    if ($package['name'] === 'laravel/framework') {
        $laravelVersion = $package['version'];
        break;
    }
}

if ($laravelVersion) {
    $passed[] = "✅ Laravel framework version: {$laravelVersion}";
} else {
    $warnings[] = "⚠️  Could not determine Laravel version";
}

// Check for common security files
$securityFiles = [
    'app/Http/Middleware/SecurityHeaders.php' => 'Security Headers middleware',
    'app/Http/Middleware/ApiAuthentication.php' => 'API Authentication middleware',
    'app/Rules/NoFakeEmail.php' => 'NoFakeEmail validation rule',
    'app/Utils/MarkdownUtils.php' => 'HTML Purifier integration'
];

foreach ($securityFiles as $file => $description) {
    if (file_exists($file)) {
        $passed[] = "✅ {$description} exists";
    } else {
        $warnings[] = "⚠️  {$description} not found at {$file}";
    }
}

// Check for debugging settings
if (file_exists('.env')) {
    $envContent = file_get_contents('.env');
    if (strpos($envContent, 'APP_DEBUG=true') !== false) {
        $warnings[] = "⚠️  APP_DEBUG is enabled (should be false in production)";
    } else {
        $passed[] = "✅ APP_DEBUG is properly configured";
    }
}

// Print results
echo "PASSED CHECKS:\n";
foreach ($passed as $pass) {
    echo "{$pass}\n";
}

if (!empty($warnings)) {
    echo "\nWARNINGS:\n";
    foreach ($warnings as $warning) {
        echo "{$warning}\n";
    }
}

if (!empty($issues)) {
    echo "\nISSUES TO FIX:\n";
    foreach ($issues as $issue) {
        echo "{$issue}\n";
    }
}

echo "\n";

// Return appropriate exit code
if (!empty($issues)) {
    echo "🚨 Security issues found! Please address the items above.\n";
    exit(1);
} elseif (!empty($warnings)) {
    echo "⚠️  Some warnings found. Review for production deployment.\n";
    exit(0);
} else {
    echo "🎉 All security checks passed!\n";
    exit(0);
} 