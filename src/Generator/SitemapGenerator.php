<?php

declare(strict_types=1);

namespace GeoFriendly\Generator;

use GeoFriendly\Config\GeofriendlyConfig;
use GeoFriendly\Utils\FileHelper;

/**
 * Generates sitemap.xml file for search engines.
 *
 * This generator creates a sitemap.xml file that:
 * - Lists the homepage with priority 1.0
 * - Includes all markdown files as URLs with priority 0.8
 * - Adds GEO files (llms.txt, llms-full.txt, docs.json, ai-index.json)
 * - Properly escapes XML special characters
 */
class SitemapGenerator implements GeneratorInterface
{
    /**
     * Generate the sitemap.xml content.
     *
     * @param GeofriendlyConfig $config The configuration object
     * @return string The generated sitemap.xml content
     */
    public function generate(GeofriendlyConfig $config): string
    {
        $baseUrl = rtrim($config->url, '/');
        $lines = [];

        // XML header
        $lines[] = '<?xml version="1.0" encoding="UTF-8"?>';
        $lines[] = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        // Homepage with highest priority
        $lines[] = '  <url>';
        $lines[] = '    <loc>' . $this->escapeXml($baseUrl) . '</loc>';
        $lines[] = '    <priority>1.0</priority>';
        $lines[] = '  </url>';

        // GEO files with high priority
        $geoFiles = [
            ['path' => '/llms.txt', 'priority' => '1.0'],
            ['path' => '/llms-full.txt', 'priority' => '0.9'],
            ['path' => '/docs.json', 'priority' => '0.9'],
            ['path' => '/ai-index.json', 'priority' => '0.9'],
        ];

        foreach ($geoFiles as $file) {
            $lines[] = '  <url>';
            $lines[] = '    <loc>' . $this->escapeXml($baseUrl . $file['path']) . '</loc>';
            $lines[] = '    <priority>' . $file['priority'] . '</priority>';
            $lines[] = '  </url>';
        }

        // Collect and add markdown files
        $files = FileHelper::collectMarkdownFiles($config->contentDir);

        foreach ($files as $file) {
            $urlPath = str_replace(['.md', '.mdx'], '', $file['relativePath']);
            $urlPath = str_replace('\\', '/', $urlPath);
            $url = $baseUrl . '/' . $urlPath;

            $lines[] = '  <url>';
            $lines[] = '    <loc>' . $this->escapeXml($url) . '</loc>';
            $lines[] = '    <priority>0.8</priority>';
            $lines[] = '  </url>';
        }

        // Close urlset
        $lines[] = '</urlset>';

        return implode("\n", $lines);
    }

    /**
     * Get the output filename.
     *
     * @return string The filename
     */
    public function getFilename(): string
    {
        return 'sitemap.xml';
    }

    /**
     * Escape special XML characters.
     *
     * @param string $text The text to escape
     * @return string The escaped text
     */
    private function escapeXml(string $text): string
    {
        $replacements = [
            '&' => '&amp;',
            '<' => '&lt;',
            '>' => '&gt;',
            '"' => '&quot;',
            "'" => '&apos;',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $text);
    }
}
