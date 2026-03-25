<?php

declare(strict_types=1);

namespace GeoFriendly\Utils;

/**
 * File helper utility for file operations.
 *
 * Provides methods for collecting markdown files and parsing frontmatter.
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
}
