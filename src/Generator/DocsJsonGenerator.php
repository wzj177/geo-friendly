<?php

declare(strict_types=1);

namespace GeoFriendly\Generator;

use GeoFriendly\Config\GeofriendlyConfig;
use GeoFriendly\Utils\FileHelper;

/**
 * Generates docs.json file for structured documentation manifest.
 *
 * This generator creates a docs.json file that:
 * - Provides site metadata (title, description, url, version, generatedAt)
 * - Lists all documentation files with their metadata
 * - Includes both markdown files and GEO files
 * - Returns JSON format for easy consumption by AI systems
 */
class DocsJsonGenerator implements GeneratorInterface
{
    /**
     * Generate the docs.json content.
     *
     * @param GeofriendlyConfig $config The configuration object
     * @return string The generated docs.json content
     */
    public function generate(GeofriendlyConfig $config): string
    {
        $baseUrl = rtrim($config->url, '/');

        // Build the manifest structure
        $manifest = [
            'title' => $config->title,
            'description' => $config->description,
            'url' => $baseUrl,
            'version' => $this->getVersion(),
            'generatedAt' => date('c'),
            'files' => [],
        ];

        // Add GEO files first
        $manifest['files'][] = [
            'title' => 'LLM Site Information',
            'description' => 'Site information optimized for Large Language Models',
            'url' => $baseUrl . '/llms.txt',
            'path' => '/llms.txt',
            'type' => 'geo',
        ];

        $manifest['files'][] = [
            'title' => 'Complete Documentation',
            'description' => 'Complete documentation in a single text file',
            'url' => $baseUrl . '/llms-full.txt',
            'path' => '/llms-full.txt',
            'type' => 'geo',
        ];

        $manifest['files'][] = [
            'title' => 'Documentation Manifest',
            'description' => 'Structured documentation manifest in JSON format',
            'url' => $baseUrl . '/docs.json',
            'path' => '/docs.json',
            'type' => 'geo',
        ];

        $manifest['files'][] = [
            'title' => 'AI Content Index',
            'description' => 'AI-optimized content index with keywords',
            'url' => $baseUrl . '/ai-index.json',
            'path' => '/ai-index.json',
            'type' => 'geo',
        ];

        $manifest['files'][] = [
            'title' => 'XML Sitemap',
            'description' => 'XML sitemap for search engines',
            'url' => $baseUrl . '/sitemap.xml',
            'path' => '/sitemap.xml',
            'type' => 'geo',
        ];

        // Collect and add markdown files
        $files = FileHelper::collectMarkdownFiles($config->contentDir);

        foreach ($files as $file) {
            $urlPath = str_replace(['.md', '.mdx'], '', $file['relativePath']);
            $urlPath = str_replace('\\', '/', $urlPath);

            // Read file content to extract description
            $content = file_get_contents($file['path']);
            $description = '';
            if ($content !== false) {
                $frontmatter = FileHelper::parseFrontmatter($content);
                $description = $frontmatter['description'] ?? '';
            }

            $manifest['files'][] = [
                'title' => $file['title'],
                'description' => $description,
                'url' => $baseUrl . '/' . $urlPath,
                'path' => '/' . $urlPath,
                'type' => 'markdown',
            ];
        }

        return json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get the output filename.
     *
     * @return string The filename
     */
    public function getFilename(): string
    {
        return 'docs.json';
    }

    /**
     * Get the package version.
     *
     * @return string The version string
     */
    private function getVersion(): string
    {
        // Try to get version from composer.json if available
        $composerJsonPath = dirname(__DIR__, 2) . '/composer.json';
        if (file_exists($composerJsonPath)) {
            $content = file_get_contents($composerJsonPath);
            if ($content !== false) {
                $data = json_decode($content, true);
                if (isset($data['version'])) {
                    return (string) $data['version'];
                }
            }
        }

        return '1.0.0';
    }
}
