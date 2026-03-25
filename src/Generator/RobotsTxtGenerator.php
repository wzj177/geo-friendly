<?php

declare(strict_types=1);

namespace GeoFriendly\Generator;

use GeoFriendly\Config\GeofriendlyConfig;

/**
 * Generates robots.txt file with AI crawler support.
 *
 * This generator creates a robots.txt file that:
 * - Manages standard web crawlers with configurable rules
 * - Explicitly allows AI crawlers to access content
 * - Includes sitemap reference
 */
class RobotsTxtGenerator implements GeneratorInterface
{
    /**
     * List of known AI crawlers that should be allowed to access content.
     *
     * @var array<string>
     */
    private const AI_CRAWLERS = [
        'GPTBot',
        'ChatGPT-User',
        'CCBot',
        'anthropic-ai',
        'Claude-Web',
        'Claude-Vertex',
        'Google-Extended',
        'PerplexityBot',
        'YouBot',
        'OmniBot',
        'Amazonbot',
        'FacebookBot',
        'Applebot-Extended',
        'Bytespider',
        'FirecrawlAgent',
    ];

    /**
     * Generate the robots.txt content.
     *
     * @param GeofriendlyConfig $config The configuration object
     * @return string The generated robots.txt content
     */
    public function generate(GeofriendlyConfig $config): string
    {
        $lines = [];

        // Standard crawlers section
        $lines[] = '# Standard crawlers';
        $lines[] = 'User-agent: *';

        // Add allow/disallow rules from config
        if (!empty($config->robots)) {
            foreach ($config->robots as $rule) {
                $lines[] = $rule;
            }
        } else {
            // Default: allow all
            $lines[] = 'Allow: /';
        }

        // Add crawl-delay if specified in robots config
        if (isset($config->robots['crawl-delay']) && is_numeric($config->robots['crawl-delay'])) {
            $lines[] = 'Crawl-delay: ' . $config->robots['crawl-delay'];
        }

        $lines[] = ''; // Empty line separator

        // AI crawlers section - allow all AI crawlers
        $lines[] = '# AI crawlers - allowed to access content';
        foreach (self::AI_CRAWLERS as $crawler) {
            $lines[] = 'User-agent: ' . $crawler;
            $lines[] = 'Allow: /';
            $lines[] = '';
        }

        // Add sitemap reference
        $sitemapUrl = rtrim($config->url, '/') . '/sitemap.xml';
        $lines[] = 'Sitemap: ' . $sitemapUrl;

        return implode("\n", $lines);
    }

    /**
     * Get the output filename.
     *
     * @return string The filename
     */
    public function getFilename(): string
    {
        return 'robots.txt';
    }
}
