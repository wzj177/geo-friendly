<?php

declare(strict_types=1);

namespace GeoFriendly\Generator;

use GeoFriendly\Config\GeofriendlyConfig;

/**
 * Generates schema.json file for Schema.org structured data.
 *
 * This generator creates a schema.json file that:
 * - Provides Schema.org JSON-LD structured data
 * - Includes WebSite and Organization schemas
 * - Can be disabled via config.schema.enabled setting
 * - Returns empty string if disabled
 */
class SchemaGenerator implements GeneratorInterface
{
    /**
     * Generate the schema.json content.
     *
     * @param GeofriendlyConfig $config The configuration object
     * @return string The generated schema.json content, or empty string if disabled
     */
    public function generate(GeofriendlyConfig $config): string
    {
        // Check if schema generation is enabled
        if (!($config->schema['enabled'] ?? true)) {
            return '';
        }

        $baseUrl = rtrim($config->url, '/');

        // Build the Schema.org graph
        $graph = [];

        // WebSite schema
        $websiteSchema = [
            '@type' => 'WebSite',
            '@id' => $baseUrl . '#website',
            'url' => $baseUrl,
            'name' => $config->title,
            'description' => $config->description,
        ];

        // Add alternate name if provided in config
        if (!empty($config->schema['alternateName'])) {
            $websiteSchema['alternateName'] = $config->schema['alternateName'];
        }

        // Add search action if enabled
        if ($config->schema['searchAction'] ?? true) {
            $websiteSchema['potentialAction'] = [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => $baseUrl . '/search?q={search_term_string}',
                ],
                'query-input' => 'required name=search_term_string',
            ];
        }

        $graph[] = $websiteSchema;

        // Organization schema
        $organizationSchema = [
            '@type' => 'Organization',
            '@id' => $baseUrl . '#organization',
            'url' => $baseUrl,
            'name' => $config->title,
        ];

        // Add logo if provided in config
        if (!empty($config->schema['logo'])) {
            $organizationSchema['logo'] = $config->schema['logo'];
        }

        // Add description to organization
        if (!empty($config->description)) {
            $organizationSchema['description'] = $config->description;
        }

        // Add social links if provided
        if (!empty($config->schema['sameAs']) && is_array($config->schema['sameAs'])) {
            $organizationSchema['sameAs'] = $config->schema['sameAs'];
        }

        $graph[] = $organizationSchema;

        // Build the final schema structure
        $schema = [
            '@context' => 'https://schema.org',
            '@graph' => $graph,
        ];

        return json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get the output filename.
     *
     * @return string The filename
     */
    public function getFilename(): string
    {
        return 'schema.json';
    }
}
