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
     * Polyfill for str_ends_with() for PHP 7.4 compatibility.
     *
     * @param string $haystack The string to search in
     * @param string $needle The substring to search for
     * @return bool True if haystack ends with needle
     */
    private static function strEndsWith(string $haystack, string $needle): bool
    {
        $length = strlen($needle);
        if ($length === 0) {
            return true;
        }
        return substr($haystack, -$length) === $needle;
    }

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

        // Use RecursiveCallbackFilterIterator to filter directories
        $directoryIterator = new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS);
        $filterIterator = new \RecursiveCallbackFilterIterator($directoryIterator, function ($current, $key, $iterator) {
            // Skip specified directories
            if ($current->isDir() && in_array($current->getFilename(), self::SKIP_DIRS, true)) {
                return false;
            }
            return true;
        });

        $iterator = new \RecursiveIteratorIterator(
            $filterIterator,
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isDir()) {
                continue;
            }

            $filename = $file->getFilename();
            if (!self::strEndsWith($filename, '.md') && !self::strEndsWith($filename, '.mdx')) {
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
     * Process content array from database.
     *
     * @param array<int, array{title: string, url: string, content: string, description?: string, category?: string, tags?: array<string>}> $contents
     * @return array<string, array{title: string, content: string, url: string, relativePath: string, description: string|null, category: string|null, tags: array<string>}>
     */
    public static function processContentArray(array $contents): array
    {
        $processed = [];

        foreach ($contents as $item) {
            // Validate required fields
            if (empty($item['title']) || empty($item['url']) || empty($item['content'])) {
                continue;
            }

            // Generate relative path from URL
            $relativePath = self::urlToPath($item['url']);

            $processed[] = [
                'title' => $item['title'],
                'content' => $item['content'],
                'url' => $item['url'],
                'relativePath' => $relativePath,
                'description' => $item['description'] ?? null,
                'category' => $item['category'] ?? null,
                'tags' => $item['tags'] ?? [],
            ];
        }

        return $processed;
    }

    /**
     * Convert URL to file path.
     *
     * @param string $url The URL to convert
     * @return string The file path
     */
    private static function urlToPath(string $url): string
    {
        // Remove query string and fragment
        $url = preg_replace('/[?#].*$/', '', $url);

        // Remove leading slash
        $path = ltrim($url, '/');

        // If path is empty or ends with slash, use index
        if (empty($path) || substr($path, -1) === '/') {
            return 'index.md';
        }

        // Add .md extension if not present
        if (!self::strEndsWith($path, '.md') && !self::strEndsWith($path, '.mdx')) {
            $path .= '.md';
        }

        return $path;
    }

    /**
     * Collect content from both files and array.
     *
     * @param GeofriendlyConfig $config The configuration object
     * @return array<string, array{title: string, content: string, url: string, relativePath: string, description: string|null, category: string|null, tags: array<string>}>
     */
    public static function collectContent(GeofriendlyConfig $config): array
    {
        // If content array is provided, use it
        if ($config->hasContentArray()) {
            return self::processContentArray($config->contents);
        }

        // Otherwise, collect from markdown files
        $files = self::collectMarkdownFiles($config->contentDir);

        // Convert file info to content format
        $content = [];
        foreach ($files as $file) {
            $fileContent = file_get_contents($file['path']);
            if ($fileContent === false) {
                continue;
            }

            $frontmatter = self::parseFrontmatter($fileContent);

            $content[] = [
                'title' => $file['title'],
                'content' => $fileContent,
                'url' => '/' . str_replace(['.md', '.mdx'], '', $file['relativePath']),
                'relativePath' => $file['relativePath'],
                'description' => $frontmatter['description'] ?? null,
                'category' => $frontmatter['category'] ?? null,
                'tags' => $frontmatter['tags'] ?? [],
            ];
        }

        return $content;
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
