<?php

$baseFile = 'resources/lang/en/messages.php';
$langFiles = [
    'resources/lang/ar/messages.php',
    'resources/lang/de/messages.php',
    'resources/lang/es/messages.php',
    'resources/lang/fr/messages.php',
    'resources/lang/he/messages.php',
    'resources/lang/it/messages.php',
    'resources/lang/nl/messages.php',
    'resources/lang/pt/messages.php'
];

// Load base (English) translations
$baseTranslations = require($baseFile);
$baseKeys = array_keys($baseTranslations);

$results = [];

foreach ($langFiles as $langFile) {
    if (!file_exists($langFile)) {
        echo "Warning: File not found - $langFile\n";
        continue;
    }

    $translations = require($langFile);
    $langKeys = array_keys($translations);
    
    // Check for missing keys
    $missingKeys = array_diff($baseKeys, $langKeys);
    
    // Check for extra keys
    $extraKeys = array_diff($langKeys, $baseKeys);
    
    $results[$langFile] = [
        'missing' => $missingKeys,
        'extra' => $extraKeys,
    ];
}

// Display results
foreach ($results as $file => $result) {
    echo "\nChecking $file:\n";
    
    if (empty($result['missing']) && empty($result['extra'])) {
        echo "✓ All keys are correct\n";
        continue;
    }
    
    if (!empty($result['missing'])) {
        echo "Missing keys:\n- " . implode("\n- ", $result['missing']) . "\n";
    }
    
    if (!empty($result['extra'])) {
        echo "Extra keys:\n- " . implode("\n- ", $result['extra']) . "\n";
    }    
} 