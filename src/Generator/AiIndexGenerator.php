<?php

declare(strict_types=1);

namespace GeoFriendly\Generator;

use GeoFriendly\Config\GeofriendlyConfig;
use GeoFriendly\Utils\FileHelper;

/**
 * Generates ai-index.json file for AI-optimized content indexing.
 *
 * This generator creates an ai-index.json file that:
 * - Provides site information with version and timestamp
 * - Creates an index of all content with keywords
 * - Extracts keywords from headings, bold text, and links
 * - Optimized for AI systems to understand site content structure
 */
class AiIndexGenerator implements GeneratorInterface
{
    /**
     * Generate the ai-index.json content.
     *
     * @param GeofriendlyConfig $config The configuration object
     * @return string The generated ai-index.json content
     */
    public function generate(GeofriendlyConfig $config): string
    {
        $baseUrl = rtrim($config->url, '/');

        // Build the AI index structure
        $index = [
            'version' => '1.0',
            'generatedAt' => date('c'),
            'site' => [
                'title' => $config->title,
                'description' => $config->description,
                'url' => $baseUrl,
            ],
            'index' => [],
        ];

        // Add GEO files to index
        $index['index'][] = [
            'url' => $baseUrl . '/llms.txt',
            'title' => 'LLM Site Information',
            'description' => 'Site information optimized for Large Language Models',
            'keywords' => ['llm', 'ai', 'site information', 'documentation'],
        ];

        $index['index'][] = [
            'url' => $baseUrl . '/llms-full.txt',
            'title' => 'Complete Documentation',
            'description' => 'Complete documentation in a single text file',
            'keywords' => ['documentation', 'complete', 'reference', 'guide'],
        ];

        $index['index'][] = [
            'url' => $baseUrl . '/docs.json',
            'title' => 'Documentation Manifest',
            'description' => 'Structured documentation manifest in JSON format',
            'keywords' => ['documentation', 'manifest', 'json', 'api'],
        ];

        $index['index'][] = [
            'url' => $baseUrl . '/ai-index.json',
            'title' => 'AI Content Index',
            'description' => 'AI-optimized content index with keywords',
            'keywords' => ['ai', 'index', 'keywords', 'search', 'optimization'],
        ];

        // Collect and process markdown files
        $files = FileHelper::collectMarkdownFiles($config->contentDir);

        foreach ($files as $file) {
            $urlPath = str_replace(['.md', '.mdx'], '', $file['relativePath']);
            $urlPath = str_replace('\\', '/', $urlPath);

            // Read file content for analysis
            $content = file_get_contents($file['path']);
            $description = '';
            $keywords = [];

            if ($content !== false) {
                // Extract description from frontmatter
                $frontmatter = FileHelper::parseFrontmatter($content);
                $description = $frontmatter['description'] ?? '';

                // Strip frontmatter for keyword extraction
                $content = preg_replace('/^---\s*\n.*?\n---\s*\n/s', '', $content);

                // Extract keywords from content
                $keywords = $this->extractKeywords($content);
            }

            $index['index'][] = [
                'url' => $baseUrl . '/' . $urlPath,
                'title' => $file['title'],
                'description' => $description,
                'keywords' => array_values(array_unique($keywords)),
            ];
        }

        return json_encode($index, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get the output filename.
     *
     * @return string The filename
     */
    public function getFilename(): string
    {
        return 'ai-index.json';
    }

    /**
     * Extract keywords from markdown content.
     *
     * Keywords are extracted from:
     * - Headings (##, ###, etc.)
     * - Bold text (**text**, __text__)
     * - Link text ([text](url))
     *
     * @param string $content The markdown content
     * @return array<string> Array of extracted keywords
     */
    private function extractKeywords(string $content): array
    {
        $keywords = [];

        // Extract headings
        if (preg_match_all('/^#{2,6}\s+(.+)$/m', $content, $matches)) {
            foreach ($matches[1] as $heading) {
                $words = $this->extractWords($heading);
                $keywords = array_merge($keywords, $words);
            }
        }

        // Extract bold text
        if (preg_match_all('/\*\*(.+?)\*\*|__(.+?)__/s', $content, $matches)) {
            foreach ([1, 2] as $index) {
                if (isset($matches[$index])) {
                    foreach ($matches[$index] as $bold) {
                        $words = $this->extractWords($bold);
                        $keywords = array_merge($keywords, $words);
                    }
                }
            }
        }

        // Extract link text
        if (preg_match_all('/\[(.+?)\]\(.+?\)/', $content, $matches)) {
            foreach ($matches[1] as $linkText) {
                $words = $this->extractWords($linkText);
                $keywords = array_merge($keywords, $words);
            }
        }

        return $keywords;
    }

    /**
     * Extract individual words from text.
     *
     * @param string $text The text to extract words from
     * @return array<string> Array of words
     */
    private function extractWords(string $text): array
    {
        // Remove markdown formatting
        $text = preg_replace('/[`*#_~\[\](){}]/', ' ', $text);

        // Split into words and filter
        $words = preg_split('/\s+/', $text);
        $filtered = [];

        $stopWords = [
            'the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for',
            'of', 'with', 'by', 'from', 'as', 'is', 'was', 'are', 'were', 'been',
            'be', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would',
            'could', 'should', 'may', 'might', 'must', 'shall', 'can', 'this',
            'that', 'these', 'those', 'it', 'its', 'i', 'you', 'he', 'she', 'we',
            'they', 'what', 'which', 'who', 'when', 'where', 'why', 'how',
        ];

        foreach ($words as $word) {
            $word = trim($word);
            $word = strtolower($word);

            // Skip empty, very short, or stop words
            if (strlen($word) < 3 || in_array($word, $stopWords, true)) {
                continue;
            }

            $filtered[] = $word;
        }

        return $filtered;
    }
}
