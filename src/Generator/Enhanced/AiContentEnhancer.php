<?php

declare(strict_types=1);

namespace GeoFriendly\Generator\Enhanced;

use GeoFriendly\Config\GeofriendlyConfig;

/**
 * AI-powered content enhancer for improving SEO and readability.
 *
 * This class provides methods to enhance content using OpenAI's GPT models,
 * including improving titles, descriptions, and generating keywords.
 */
class AiContentEnhancer
{
    /**
     * Enhance content with AI improvements.
     *
     * @param GeofriendlyConfig $config The configuration object
     * @param array{title?: string, description?: string, content?: string} $content The content to enhance
     * @return array{title: string, description: string, keywords: array<string>} Enhanced content
     */
    public function enhance(GeofriendlyConfig $config, array $content): array
    {
        // Check if OpenAI is configured and enabled
        if ($config->openai === null || !$config->openai->enabled) {
            return [
                'title' => $content['title'] ?? '',
                'description' => $content['description'] ?? '',
                'keywords' => [],
            ];
        }

        $enhancements = $this->generateEnhancements($content, $config);

        return [
            'title' => $enhancements['title'] ?? $content['title'] ?? '',
            'description' => $enhancements['description'] ?? $content['description'] ?? '',
            'keywords' => $enhancements['keywords'] ?? [],
        ];
    }

    /**
     * Generate AI enhancements for content.
     *
     * @param array{title?: string, description?: string, content?: string} $content The content to enhance
     * @param GeofriendlyConfig $config The configuration object
     * @return array{title?: string, description?: string, keywords?: array<string>} The enhancements
     */
    private function generateEnhancements(array $content, GeofriendlyConfig $config): array
    {
        $prompt = $this->buildEnhancementPrompt($content);

        $result = $this->callOpenAI($prompt, $config);

        if (!$result) {
            return [];
        }

        return $this->parseEnhancementResponse($result);
    }

    /**
     * Build an enhancement prompt for AI.
     *
     * @param array{title?: string, description?: string, content?: string} $content The content to enhance
     * @return string The built prompt
     */
    private function buildEnhancementPrompt(array $content): string
    {
        $prompt = "You are an SEO and content optimization expert. Please analyze and enhance the following content.

";

        if (!empty($content['title'])) {
            $prompt .= "Current Title: {$content['title']}\n";
        }

        if (!empty($content['description'])) {
            $prompt .= "Current Description: {$content['description']}\n";
        }

        if (!empty($content['content'])) {
            // Truncate content if too long
            $contentPreview = strlen($content['content']) > 1000
                ? substr($content['content'], 0, 1000) . '...'
                : $content['content'];
            $prompt .= "Content Preview: {$contentPreview}\n";
        }

        $prompt .= "
Please provide:
1. An SEO-optimized title (50-60 characters ideal, max 70)
2. An engaging meta description (150-160 characters ideal, max 160)
3. 5-10 relevant keywords/tags for this content

Format your response as JSON:
{
  \"title\": \"Enhanced title here\",
  \"description\": \"Enhanced description here\",
  \"keywords\": [\"keyword1\", \"keyword2\", \"keyword3\"]
}

Return ONLY the JSON, no other text.";

        return $prompt;
    }

    /**
     * Parse the enhancement response from AI.
     *
     * @param string $response The AI response
     * @return array{title?: string, description?: string, keywords?: array<string>} Parsed enhancements
     */
    private function parseEnhancementResponse(string $response): array
    {
        // Try to extract JSON from the response
        $jsonMatch = preg_match('/\{[^}]*\}/s', $response, $matches);

        if (!$jsonMatch) {
            return [];
        }

        $json = $matches[0];
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [];
        }

        $result = [];

        if (!empty($data['title'])) {
            $result['title'] = (string) $data['title'];
        }

        if (!empty($data['description'])) {
            $result['description'] = (string) $data['description'];
        }

        if (!empty($data['keywords']) && is_array($data['keywords'])) {
            $result['keywords'] = array_map('strval', $data['keywords']);
        }

        return $result;
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
                    'content' => 'You are an SEO and content optimization expert. You always respond with valid JSON when asked for structured data.',
                ],
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
            'temperature' => 0.7,
            'max_tokens' => 500,
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
     * Generate keywords from content.
     *
     * @param GeofriendlyConfig $config The configuration object
     * @param string $content The content to analyze
     * @return array<string> Generated keywords
     */
    public function generateKeywords(GeofriendlyConfig $config, string $content): array
    {
        if ($config->openai === null || !$config->openai->enabled) {
            return [];
        }

        $prompt = "You are an SEO expert. Extract 5-10 relevant keywords from the following content.

Content: " . (strlen($content) > 1000 ? substr($content, 0, 1000) . '...' : $content) . "

Return only a JSON array of keywords, like [\"keyword1\", \"keyword2\", \"keyword3\"]";

        $result = $this->callOpenAI($prompt, $config);

        if (!$result) {
            return [];
        }

        $keywords = json_decode($result, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($keywords)) {
            return array_map('strval', $keywords);
        }

        return [];
    }

    /**
     * Improve a description for SEO.
     *
     * @param GeofriendlyConfig $config The configuration object
     * @param string $description The description to improve
     * @param string $context Additional context for the description
     * @return string Improved description
     */
    public function improveDescription(GeofriendlyConfig $config, string $description, string $context = ''): string
    {
        if ($config->openai === null || !$config->openai->enabled) {
            return $description;
        }

        $prompt = "You are an SEO expert. Improve the following meta description to be more engaging and SEO-optimized.

Current description: {$description}

" . ($context ? "Context: {$context}\n" : "") . "
Guidelines:
- Keep it between 150-160 characters
- Make it compelling and click-worthy
- Include relevant keywords naturally
- Maintain accuracy and relevance

Return only the improved description, nothing else.";

        $result = $this->callOpenAI($prompt, $config);

        return $result ?: $description;
    }

    /**
     * Generate a summary from content.
     *
     * @param GeofriendlyConfig $config The configuration object
     * @param string $content The content to summarize
     * @param int $maxLength Maximum length of the summary
     * @return string Generated summary
     */
    public function generateSummary(GeofriendlyConfig $config, string $content, int $maxLength = 300): string
    {
        if ($config->openai === null || !$config->openai->enabled) {
            return substr($content, 0, $maxLength);
        }

        $prompt = "You are a content expert. Create a concise summary of the following content.

Content: " . (strlen($content) > 2000 ? substr($content, 0, 2000) . '...' : $content) . "

Create a summary that:
- Is under {$maxLength} characters
- Captures the main points
- Is clear and concise
- Uses the same language as the original content

Return only the summary, nothing else.";

        $result = $this->callOpenAI($prompt, $config);

        return $result ?: substr($content, 0, $maxLength);
    }
}
