<?php

declare(strict_types=1);

namespace GeoFriendly\Utils;

use GeoFriendly\Config\GeofriendlyConfig;

/**
 * File helper utility for file operations.
 *
 * Provides methods for collecting markdown files, parsing frontmatter,
 * and extracting content from URLs using Firecrawl.
 */
class FileHelper
{
    /**
     * Directories to skip when collecting files.
     *
     * @var array<string>
     */
    public const SKIP_DIRS = [
        'node_modules',
        'vendor',
        '.git',
        'public',
        'dist',
        'build',
        'coverage',
    ];

    /**
     * Recursively collect markdown files from a directory.
     *
     * @param string $dir The directory to scan
     * @return array<string, array{title: string, path: string, relativePath: string}> Array of file info
     */
    public static function collectMarkdownFiles(string $dir): array
    {
        if (!is_dir($dir)) {
            return [];
        }

        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isDir()) {
                // Skip specified directories
                if (in_array($file->getFilename(), self::SKIP_DIRS, true)) {
                    $iterator->skipNext();
                }
                continue;
            }

            $filename = $file->getFilename();
            if (!str_ends_with($filename, '.md') && !str_ends_with($filename, '.mdx')) {
                continue;
            }

            $filepath = $file->getPathname();
            $content = file_get_contents($filepath);
            if ($content === false) {
                continue;
            }

            $relativePath = self::getRelativePath($filepath, $dir);
            $title = self::extractTitle($content);

            $files[] = [
                'title' => $title,
                'path' => $filepath,
                'relativePath' => $relativePath,
            ];
        }

        return $files;
    }

    /**
     * Parse YAML frontmatter from content.
     *
     * @param string $content The file content
     * @return array<string, mixed> The parsed frontmatter
     */
    public static function parseFrontmatter(string $content): array
    {
        if (!preg_match('/^---\s*\n(.*?)\n---\s*\n/s', $content, $matches)) {
            return [];
        }

        $frontmatter = $matches[1];
        $lines = explode("\n", $frontmatter);
        $data = [];

        foreach ($lines as $line) {
            if (preg_match('/^(\w+):\s*(.*)$/', $line, $matches)) {
                $key = $matches[1];
                $value = trim($matches[2]);

                // Handle basic YAML types
                if ($value === 'true') {
                    $value = true;
                } elseif ($value === 'false') {
                    $value = false;
                } elseif ($value === 'null') {
                    $value = null;
                } elseif (preg_match('/^\d+$/', $value)) {
                    $value = (int) $value;
                }

                $data[$key] = $value;
            }
        }

        return $data;
    }

    /**
     * Extract title from content.
     *
     * First checks frontmatter for title, then falls back to first heading.
     *
     * @param string $content The file content
     * @return string The extracted title
     */
    public static function extractTitle(string $content): string
    {
        // Try frontmatter first
        $frontmatter = self::parseFrontmatter($content);
        if (isset($frontmatter['title']) && is_string($frontmatter['title'])) {
            return $frontmatter['title'];
        }

        // Remove frontmatter for heading search
        $content = preg_replace('/^---\s*\n.*?\n---\s*\n/s', '', $content);

        // Find first heading
        if (preg_match('/^#\s+(.+)$/m', $content, $matches)) {
            return trim($matches[1]);
        }

        // Fallback to filename or empty string
        return 'Untitled';
    }

    /**
     * Get relative path from base directory.
     *
     * @param string $filepath The absolute file path
     * @param string $baseDir The base directory
     * @return string The relative path
     */
    private static function getRelativePath(string $filepath, string $baseDir): string
    {
        $filepath = realpath($filepath);
        $baseDir = realpath($baseDir);

        if ($filepath === false || $baseDir === false) {
            return basename($filepath);
        }

        $relative = str_replace($baseDir . '/', '', $filepath);
        return $relative;
    }

    /**
     * Write content to a file.
     *
     * @param string $path The file path
     * @param string $content The content to write
     * @return void
     * @throws \RuntimeException If file cannot be written
     */
    public static function writeFile(string $path, string $content): void
    {
        $dir = dirname($path);

        // Create directory if it doesn't exist
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                throw new \RuntimeException(sprintf('Failed to create directory: %s', $dir));
            }
        }

        // Write content
        $result = file_put_contents($path, $content);
        if ($result === false) {
            throw new \RuntimeException(sprintf('Failed to write file: %s', $path));
        }
    }

    /**
     * Extract content from a URL using Firecrawl if enabled.
     *
     * @param string $url The URL to extract content from
     * @param GeofriendlyConfig $config The configuration object
     * @return array<string, mixed> Array containing markdown content and metadata
     */
    public static function extractContent(string $url, GeofriendlyConfig $config): array
    {
        $firecrawlConfig = $config->firecrawl;

        if (empty($firecrawlConfig) || !($firecrawlConfig['enabled'] ?? false)) {
            return [];
        }

        $apiKey = $firecrawlConfig['apiKey'] ?? '';
        $apiUrl = $firecrawlConfig['apiUrl'] ?? 'https://api.firecrawl.dev/v1';

        if (empty($apiKey)) {
            return [];
        }

        try {
            return ContentExtractor::extractFromFirecrawl($url, $apiKey, $apiUrl);
        } catch (\RuntimeException $e) {
            // Log error but don't throw - fall back to local files
            error_log(sprintf('Firecrawl extraction failed for %s: %s', $url, $e->getMessage()));
            return [];
        }
    }
}
