<?php

declare(strict_types=1);

namespace GeoFriendly\Config;

readonly class OpenAIConfig
{
    public string $apiKey;
    public string $baseUrl;
    public string $model;
    public bool $enabled;

    public function __construct(
        ?string $apiKey = null,
        string $baseUrl = 'https://api.openai.com/v1',
        string $model = 'gpt-4o-mini',
        ?bool $enabled = null
    ) {
        $this->apiKey = $apiKey ?? '';
        $this->baseUrl = $baseUrl;
        $this->model = $model;
        $this->enabled = $enabled ?? !empty($this->apiKey);
    }

    /**
     * Create from array configuration
     *
     * @param array<string, mixed> $config
     */
    public static function fromArray(array $config): self
    {
        return new self(
            apiKey: $config['apiKey'] ?? null,
            baseUrl: $config['baseUrl'] ?? 'https://api.openai.com/v1',
            model: $config['model'] ?? 'gpt-4o-mini',
            enabled: $config['enabled'] ?? null
        );
    }
}
