<?php

declare(strict_types=1);

namespace GeoFriendly\Utils;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;

/**
 * Content extractor utility for Firecrawl API integration.
 *
 * Provides methods for extracting content from websites using the Firecrawl API.
 */
class ContentExtractor
{
    /**
     * Extract content from a URL using Firecrawl API.
     *
     * @param string $url The URL to scrape
     * @param string $apiKey The Firecrawl API key
     * @param string $apiUrl The Firecrawl API URL
     * @return array<string, mixed> Array containing markdown content and metadata
     * @throws \RuntimeException If the API request fails
     */
    public static function extractFromFirecrawl(
        string $url,
        string $apiKey,
        string $apiUrl = 'https://api.firecrawl.dev/v1'
    ): array {
        if (empty($apiKey)) {
            throw new \RuntimeException('Firecrawl API key is required');
        }

        $client = new Client([
            'base_uri' => rtrim($apiUrl, '/'),
            'timeout' => 30,
            'headers' => [
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ],
        ]);

        try {
            $response = $client->post('/scrape', [
                'json' => [
                    'url' => $url,
                    'formats' => ['markdown'],
                ],
            ]);

            $body = $response->getBody()->getContents();
            $data = json_decode($body, true);

            if (!is_array($data)) {
                throw new \RuntimeException('Invalid response from Firecrawl API');
            }

            if (!isset($data['success']) || !$data['success']) {
                $error = $data['error'] ?? 'Unknown error';
                throw new \RuntimeException('Firecrawl API error: ' . $error);
            }

            $markdown = $data['data']['markdown'] ?? '';
            $metadata = $data['data']['metadata'] ?? [];

            return [
                'markdown' => $markdown,
                'metadata' => $metadata,
                'url' => $url,
            ];
        } catch (ClientException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            $body = $e->getResponse()->getBody()->getContents();
            $error = json_decode($body, true);
            $message = $error['error'] ?? $e->getMessage();

            throw new \RuntimeException(
                sprintf('Firecrawl API client error (%d): %s', $statusCode, $message),
                $statusCode,
                $e
            );
        } catch (ServerException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            throw new \RuntimeException(
                sprintf('Firecrawl API server error (%d): %s', $statusCode, $e->getMessage()),
                $statusCode,
                $e
            );
        } catch (\Exception $e) {
            throw new \RuntimeException(
                'Firecrawl API request failed: ' . $e->getMessage(),
                0,
                $e
            );
        }
    }
}
