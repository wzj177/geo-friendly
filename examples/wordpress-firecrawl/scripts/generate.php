#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Generate GEO Files for WordPress Site using Firecrawl
 */

require __DIR__ . '/../vendor/autoload.php';

use GeoFriendly\GeoFriendly;
use Dotenv\Dotenv;

// Load environment variables
try {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
} catch (Exception $e) {
    // .env file not found, use default values
}

// Get configuration from environment
$wpUrl = rtrim($_ENV['WP_URL'] ?? 'https://example.com', '/');
$wpTitle = $_ENV['WP_TITLE'] ?? 'My WordPress Site';
$wpDescription = $_ENV['WP_DESCRIPTION'] ?? 'A WordPress site';
$firecrawlKey = $_ENV['FIRECRAWL_API_KEY'] ?? '';
$outputDir = $_ENV['OUTPUT_DIR'] ?? __DIR__ . '/../output';

// Validate required configuration
if (empty($firecrawlKey)) {
    fprintf(STDERR, "Error: FIRECRAWL_API_KEY is required\n");
    fprintf(STDERR, "Please set it in .env file or environment variable\n");
    exit(1);
}

// Create output directory if not exists
if (!is_dir($outputDir)) {
    if (!mkdir($outputDir, 0755, true)) {
        fprintf(STDERR, "Error: Failed to create output directory: %s\n", $outputDir);
        exit(1);
    }
}

// Configure Geo-Friendly
$config = [
    'title' => $wpTitle,
    'url' => $wpUrl,
    'description' => $wpDescription,
    'outDir' => $outputDir,
    'contentDir' => '', // Empty to use Firecrawl mode
    'firecrawl' => [
        'apiKey' => $firecrawlKey,
        'apiUrl' => 'https://api.firecrawl.dev/v1',
        'enabled' => true,
    ],
    'generators' => [
        'robotsTxt' => true,
        'llmsTxt' => true,
        'llmsFullTxt' => true,
        'sitemap' => true,
        'docsJson' => true,
        'aiIndex' => true,
        'schema' => true,
    ],
];

// Enable OpenAI if configured
$openaiKey = $_ENV['OPENAI_API_KEY'] ?? '';
if (!empty($openaiKey)) {
    $config['openai'] = [
        'apiKey' => $openaiKey,
        'baseUrl' => 'https://api.openai.com/v1',
        'model' => 'gpt-4o-mini',
        'enabled' => true,
    ];
}

echo "===========================================\n";
echo "WordPress GEO Files Generator\n";
echo "===========================================\n\n";
echo "Site URL: {$wpUrl}\n";
echo "Site Title: {$wpTitle}\n";
echo "Output Directory: {$outputDir}\n";
echo "Firecrawl: " . (!empty($firecrawlKey) ? 'Enabled' : 'Disabled') . "\n";
echo "OpenAI: " . (!empty($openaiKey) ? 'Enabled' : 'Disabled') . "\n\n";

echo "Starting generation...\n\n";

try {
    $geo = new GeoFriendly($config);
    [$generated, $errors] = $geo->generate();

    if (!empty($generated)) {
        echo "✓ Successfully generated " . count($generated) . " files:\n";
        foreach ($generated as $file) {
            $path = $outputDir . '/' . $file;
            $size = file_exists($path) ? filesize($path) : 0;
            echo sprintf("  - %-20s %10d bytes\n", $file, $size);
        }
    }

    echo "\n";

    if (!empty($errors)) {
        echo "⚠ Warnings/Errors:\n";
        foreach ($errors as $error) {
            echo "  - {$error}\n";
        }
        echo "\n";
    }

    echo "===========================================\n";
    echo "✓ Done! Files saved to: {$outputDir}\n";
    echo "===========================================\n\n";

    echo "Next steps:\n";
    echo "1. Review generated files in the output directory\n";
    echo "2. Upload them to your WordPress root directory\n";
    echo "3. Verify by visiting: {$wpUrl}/llms.txt\n\n";

    exit(0);

} catch (Exception $e) {
    fprintf(STDERR, "\n✗ Error: %s\n", $e->getMessage());
    fprintf(STDERR, "Stack trace:\n");
    fprintf(STDERR, "%s\n", $e->getTraceAsString());
    exit(1);
}
