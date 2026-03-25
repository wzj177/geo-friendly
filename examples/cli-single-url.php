#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Geo-Friendly CLI Single URL Example
 *
 * This script demonstrates how to generate GEO-friendly files
 * for a single URL using the geo-friendly package.
 *
 * Usage:
 *   php cli-single-url.php https://example.com [options]
 *
 * Options:
 *   --output=DIR        Output directory (default: ./output)
 *   --name=NAME         Site name (default: from URL)
 *   --description=DESC  Site description (optional)
 *   --email=EMAIL       Contact email (optional)
 *   --files=FILES       Comma-separated list of files to generate
 *                       (default: llms,robots,sitemap,docs,ai-index)
 *                       Available: llms, robots, sitemap, docs, ai-index, schema
 *   --help              Show this help message
 *
 * Examples:
 *   # Generate all files for a URL
 *   php cli-single-url.php https://example.com
 *
 *   # Generate only llms.txt and robots.txt
 *   php cli-single-url.php https://example.com --files=llms,robots
 *
 *   # Custom output directory and site name
 *   php cli-single-url.php https://example.com --output=/tmp/geo --name="My Site"
 *
 * Requirements:
 *   - PHP 8.2 or higher
 *   - Composer dependencies installed
 *
 * @package GeoFriendly
 * @version 1.0.0
 */

// Check if running in CLI
if (php_sapi_name() !== 'cli') {
    die('This script must be run from the command line.');
}

// Autoload dependencies
$autoloadPaths = [
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/../../../vendor/autoload.php',
    __DIR__ . '/vendor/autoload.php',
];

$autoloaded = false;
foreach ($autoloadPaths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $autoloaded = true;
        break;
    }
}

if (!$autoloaded) {
    fwrite(STDERR, "Error: Could not find autoload.php. Please run 'composer install'.\n");
    exit(1);
}

use GeoFriendly\GeoFriendly;

/**
 * Parse command line arguments
 *
 * @param array<string> $args Command line arguments
 * @return array<string, mixed> Parsed arguments
 */
function parseArguments(array $args): array
{
    $parsed = [
        'url' => null,
        'output' => './output',
        'name' => null,
        'description' => '',
        'email' => '',
        'files' => ['llms', 'robots', 'sitemap', 'docs', 'ai-index'],
        'help' => false,
    ];

    // First argument is the script name
    array_shift($args);

    // Parse remaining arguments
    foreach ($args as $arg) {
        // Check for --help
        if ($arg === '--help' || $arg === '-h') {
            $parsed['help'] = true;
            continue;
        }

        // Check for --key=value or --key value format
        if (str_starts_with($arg, '--')) {
            $parts = explode('=', substr($arg, 2), 2);
            $key = $parts[0];
            $value = $parts[1] ?? null;

            switch ($key) {
                case 'output':
                    $parsed['output'] = $value;
                    break;
                case 'name':
                    $parsed['name'] = $value;
                    break;
                case 'description':
                    $parsed['description'] = $value;
                    break;
                case 'email':
                    $parsed['email'] = $value;
                    break;
                case 'files':
                    $parsed['files'] = array_map('trim', explode(',', $value));
                    break;
            }
        } else {
            // Positional argument - assume it's the URL
            if (empty($parsed['url']) && filter_var($arg, FILTER_VALIDATE_URL)) {
                $parsed['url'] = $arg;
            }
        }
    }

    return $parsed;
}

/**
 * Display help message
 */
function showHelp(): void
{
    $script = basename(__FILE__);
    echo <<<HELP
Geo-Friendly CLI Single URL Generator
=====================================

Generate GEO-friendly files for a single URL.

Usage:
  php {$script} <URL> [options]

Arguments:
  URL                    The URL to generate files for

Options:
  --output=DIR          Output directory (default: ./output)
  --name=NAME           Site name (default: extracted from URL)
  --description=DESC    Site description (optional)
  --email=EMAIL         Contact email (optional)
  --files=FILES         Comma-separated list of files to generate
                        (default: llms,robots,sitemap,docs,ai-index)
                        Available: llms, robots, sitemap, docs, ai-index, schema
  --help, -h            Show this help message

Examples:
  # Generate all files
  php {$script} https://example.com

  # Generate specific files
  php {$script} https://example.com --files=llms,robots

  # Custom output and name
  php {$script} https://example.com --output=/tmp/geo --name="My Site"

  # Full example with all options
  php {$script} https://example.com \\
    --output=./geo-files \\
    --name="My Awesome Site" \\
    --description="Best site ever" \\
    --email=contact@example.com \\
    --files=llms,robots,sitemap,docs

Generated Files:
  The following files can be generated:
    - llms.txt          (llms)      LLM discovery file
    - robots.txt        (robots)    Search engine directives
    - sitemap.xml       (sitemap)   XML sitemap
    - docs.json         (docs)      Structured documentation
    - ai-index.json     (ai-index)  Enhanced AI index
    - schema.json       (schema)    Schema markup

For more information, visit: https://github.com/yourusername/geo-friendly

HELP;
}

/**
 * Map file names to generator config keys
 *
 * @param array<string> $files File names from command line
 * @return array<string, bool> Generator configuration
 */
function mapFileToGenerator(array $files): array
{
    $map = [
        'llms' => 'llmsTxt',
        'robots' => 'robotsTxt',
        'sitemap' => 'sitemap',
        'docs' => 'docsJson',
        'ai-index' => 'aiIndex',
        'schema' => 'schema',
        'llms-full' => 'llmsFullTxt',
    ];

    $generators = array_fill_keys(array_values($map), false);

    foreach ($files as $file) {
        if (isset($map[$file])) {
            $generators[$map[$file]] = true;
        }
    }

    return $generators;
}

/**
 * Extract domain name from URL
 *
 * @param string $url
 * @return string
 */
function extractDomain(string $url): string
{
    $domain = parse_url($url, PHP_URL_HOST);
    if ($domain === false) {
        return 'Unknown';
    }

    // Remove www. prefix
    $domain = preg_replace('/^www\./', '', $domain);

    // Capitalize first letter
    return ucfirst(str_replace(['.', '-'], ' ', $domain));
}

/**
 * Validate URL
 *
 * @param string $url
 * @return bool
 */
function isValidUrl(string $url): bool
{
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

/**
 * Main execution
 */
function main(): int
{
    // Parse arguments
    $args = parseArguments($argv);

    // Show help if requested
    if ($args['help']) {
        showHelp();
        return 0;
    }

    // Validate URL
    if (empty($args['url'])) {
        fwrite(STDERR, "Error: URL is required.\n\n");
        showHelp();
        return 1;
    }

    if (!isValidUrl($args['url'])) {
        fwrite(STDERR, "Error: Invalid URL '{$args['url']}'.\n");
        return 1;
    }

    // Extract site name from URL if not provided
    if (empty($args['name'])) {
        $args['name'] = extractDomain($args['url']);
    }

    // Display configuration
    echo "Geo-Friendly CLI Generator\n";
    echo "=========================\n\n";
    echo "URL:          {$args['url']}\n";
    echo "Site Name:    {$args['name']}\n";
    if (!empty($args['description'])) {
        echo "Description:  {$args['description']}\n";
    }
    if (!empty($args['email'])) {
        echo "Email:        {$args['email']}\n";
    }
    echo "Output:       {$args['output']}\n";
    echo "Files:        " . implode(', ', $args['files']) . "\n\n";

    // Create output directory if it doesn't exist
    if (!is_dir($args['output'])) {
        if (!mkdir($args['output'], 0755, true)) {
            fwrite(STDERR, "Error: Failed to create output directory: {$args['output']}\n");
            return 1;
        }
        echo "Created output directory: {$args['output']}\n";
    }

    // Configure GeoFriendly
    $config = [
        'siteUrl' => $args['url'],
        'outDir' => $args['output'],
        'siteName' => $args['name'],
        'siteDescription' => $args['description'],
        'contactEmail' => $args['email'],
        'urls' => [$args['url']],
        'generators' => mapFileToGenerator($args['files']),
    ];

    try {
        // Create GeoFriendly instance
        $geo = new GeoFriendly($config);

        echo "Generating files...\n\n";

        // Generate files
        [$generated, $errors] = $geo->generate();

        // Display results
        if (!empty($generated)) {
            echo "Successfully generated:\n";
            foreach ($generated as $file) {
                $fullPath = rtrim($args['output'], '/') . '/' . $file;
                $size = file_exists($fullPath) ? filesize($fullPath) : 0;
                echo "  ✓ {$file} (" . number_format($size) . " bytes)\n";
            }
        }

        if (!empty($errors)) {
            echo "\nErrors occurred:\n";
            foreach ($errors as $error) {
                echo "  ✗ {$error}\n";
            }
            return 1;
        }

        echo "\nDone! Files saved to: {$args['output']}\n";

        // Show file URLs
        $baseUrl = rtrim($args['url'], '/');
        echo "\nAccess your files at:\n";
        foreach ($generated as $file) {
            echo "  {$baseUrl}/{$file}\n";
        }

        return 0;

    } catch (\Exception $e) {
        fwrite(STDERR, "\nError: {$e->getMessage()}\n");
        return 1;
    }
}

// Run the script
exit(main());
