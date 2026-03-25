<?php

declare(strict_types=1);

namespace GeoFriendly\Generator\Enhanced;

use GeoFriendly\Config\GeofriendlyConfig;
use GeoFriendly\Generator\SchemaGenerator;

/**
 * AI-enhanced schema.org generator.
 *
 * This generator extends the base SchemaGenerator to use OpenAI
 * for generating better, more descriptive Schema.org structured data.
 */
class AiSchemaGenerator extends SchemaGenerator
{
    /**
     * Generate the schema.json content with AI enhancement.
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

        // Check if OpenAI is configured and enabled
        if ($config->openai === null || !$config->openai->enabled) {
            // Fall back to parent implementation
            return parent::generate($config);
        }

        return $this->generateAiEnhancedSchema($config);
    }

    /**
     * Generate AI-enhanced schema markup.
     *
     * @param GeofriendlyConfig $config The configuration object
     * @return string The generated schema JSON
     */
    private function generateAiEnhancedSchema(GeofriendlyConfig $config): string
    {
        $baseUrl = rtrim($config->url, '/');

        // Build the Schema.org graph
        $graph = [];

        // WebSite schema with AI enhancement
        $websiteSchema = [
            '@type' => 'WebSite',
            '@id' => $baseUrl . '#website',
            'url' => $baseUrl,
            'name' => $config->title,
            'description' => $this->generateDescription($config, 'website'),
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

        // Organization schema with AI enhancement
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

        // Add AI-generated description
        $orgDescription = $this->generateDescription($config, 'organization');
        if (!empty($orgDescription)) {
            $organizationSchema['description'] = $orgDescription;
        }

        // Add social links if provided
        if (!empty($config->schema['sameAs']) && is_array($config->schema['sameAs'])) {
            $organizationSchema['sameAs'] = $config->schema['sameAs'];
        }

        $graph[] = $organizationSchema;

        // Add WebPage schema for the homepage if content is available
        $webPageSchema = $this->generateWebPageSchema($config);
        if ($webPageSchema !== null) {
            $graph[] = $webPageSchema;
        }

        // Build the final schema structure
        $schema = [
            '@context' => 'https://schema.org',
            '@graph' => $graph,
        ];

        return json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Generate a description using AI.
     *
     * @param GeofriendlyConfig $config The configuration object
     * @param string $context The context for the description (website, organization, etc.)
     * @return string The generated description
     */
    private function generateDescription(GeofriendlyConfig $config, string $context): string
    {
        // If description is already provided in config, use it
        if (!empty($config->description)) {
            // Enhance it with AI if possible
            return $this->enhanceDescription($config->description, $context, $config);
        }

        // Generate a new description using AI
        return $this->generateNewDescription($config, $context);
    }

    /**
     * Enhance an existing description using AI.
     *
     * @param string $description The existing description
     * @param string $context The context for enhancement
     * @param GeofriendlyConfig $config The configuration object
     * @return string The enhanced description
     */
    private function enhanceDescription(string $description, string $context, GeofriendlyConfig $config): string
    {
        $prompt = $this->buildDescriptionPrompt($description, $context, $config, true);

        $result = $this->callOpenAI($prompt, $config);

        return $result ?: $description;
    }

    /**
     * Generate a new description using AI.
     *
     * @param GeofriendlyConfig $config The configuration object
     * @param string $context The context for generation
     * @return string The generated description
     */
    private function generateNewDescription(GeofriendlyConfig $config, string $context): string
    {
        $prompt = $this->buildDescriptionPrompt('', $context, $config, false);

        $result = $this->callOpenAI($prompt, $config);

        return $result ?: "{$config->title} - {$config->description}";
    }

    /**
     * Build a prompt for description generation/enhancement.
     *
     * @param string $existingDescription The existing description (empty if generating new)
     * @param string $context The context for the description
     * @param GeofriendlyConfig $config The configuration object
     * @param bool $isEnhancement Whether this is an enhancement or new generation
     * @return string The built prompt
     */
    private function buildDescriptionPrompt(
        string $existingDescription,
        string $context,
        GeofriendlyConfig $config,
        bool $isEnhancement
    ): string {
        $siteTitle = $config->title;
        $siteUrl = $config->url;

        $prompt = "You are an SEO and structured data expert. ";

        if ($isEnhancement) {
            $prompt .= "Please enhance the following description for Schema.org structured data.
";
        } else {
            $prompt .= "Please generate a description for Schema.org structured data.
";
        }

        $prompt .= "Context: {$context}
Site Title: {$siteTitle}
Site URL: {$siteUrl}
";

        if ($isEnhancement && !empty($existingDescription)) {
            $prompt .= "Current Description: {$existingDescription}
";
        }

        $prompt .= "
Requirements:
- Keep it concise (50-300 characters depending on context)
- Make it descriptive and keyword-rich
- Write in a professional tone
- Ensure it accurately represents the site
- For website context: Focus on what the site offers
- For organization context: Focus on the organization's purpose

";

        if ($context === 'website') {
            $prompt .= "This description will be used for the WebSite schema type.
Return only the enhanced description, nothing else.";
        } elseif ($context === 'organization') {
            $prompt .= "This description will be used for the Organization schema type.
Return only the enhanced description, nothing else.";
        } else {
            $prompt .= "Return only the enhanced description, nothing else.";
        }

        return $prompt;
    }

    /**
     * Generate a WebPage schema for the homepage.
     *
     * @param GeofriendlyConfig $config The configuration object
     * @return array<string, mixed>|null The WebPage schema or null if not applicable
     */
    private function generateWebPageSchema(GeofriendlyConfig $config): ?array
    {
        $baseUrl = rtrim($config->url, '/');

        $webPageSchema = [
            '@type' => 'WebPage',
            '@id' => $baseUrl . '#webpage',
            'url' => $baseUrl,
            'name' => $config->title,
            'description' => $this->generateDescription($config, 'webpage'),
            'inLanguage' => 'en-US',
        ];

        // Add date modified
        $webPageSchema['dateModified'] = date('Y-m-d');

        return $webPageSchema;
    }

    /**
     * Call the OpenAI API.
     *
     * @param string $prompt The prompt to send
     * @param GeofriendlyConfig $config The configuration object
     * @return string|null The response text, or null on failure
     */
    private function callOpenAI(string $prompt, GeofriendlyConfig $config): ?string
    {
        if ($config->openai === null || !$config->openai->enabled) {
            return null;
        }

        $apiKey = $config->openai->apiKey;
        $baseUrl = $config->openai->baseUrl;
        $model = $config->openai->model;

        $data = [
            'model' => $model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are an SEO and structured data expert specializing in Schema.org markup. You provide clear, concise descriptions optimized for search engines.',
                ],
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
            'temperature' => 0.7,
            'max_tokens' => 300,
        ];

        $ch = curl_init($baseUrl . '/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error || $httpCode !== 200) {
            // Silently fail and return null
            return null;
        }

        $result = json_decode($response, true);

        if (isset($result['choices'][0]['message']['content'])) {
            return trim($result['choices'][0]['message']['content']);
        }

        return null;
    }

    /**
     * Generate additional schema types based on content.
     *
     * This method can be extended to add more schema types like Article,
     * BlogPosting, FAQPage, etc. based on the content analysis.
     *
     * @param GeofriendlyConfig $config The configuration object
     * @return array<string, mixed> Additional schema definitions
     */
    public function generateAdditionalSchemas(GeofriendlyConfig $config): array
    {
        $schemas = [];

        // Example: Add FAQPage schema if FAQ content is detected
        // This is a placeholder for future enhancement

        return $schemas;
    }
}
