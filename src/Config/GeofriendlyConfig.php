<?php

declare(strict_types=1);

namespace GeoFriendly\Config;

class GeofriendlyConfig
{
    public string $title;
    public string $url;
    public string $outDir;
    public string $contentDir;
    public string $description;
    /** @var array<string, mixed> */
    public array $generators;
    /** @var array<string> */
    public array $robots;
    /** @var array<string, mixed> */
    public array $widget;
    /** @var array<string, mixed> */
    public array $schema;
    /** @var array<string, mixed> */
    public array $og;
    public ?OpenAIConfig $openai;
    /** @var array<string, mixed> */
    public array $firecrawl;

    /**
     * @var array<string, bool>
     */
    private const DEFAULT_GENERATORS = [
        'robotsTxt' => true,
        'llmsTxt' => true,
        'llmsFullTxt' => true,
        'rawMarkdown' => true,
        'manifest' => true,
        'sitemap' => true,
        'aiIndex' => true,
        'schema' => true,
    ];

    /**
     * @param array<string, mixed> $config
     */
    public function __construct(array $config = [])
    {
        $this->title = $config['title'] ?? 'My Site';
        $this->url = $config['url'] ?? 'https://example.com';
        $this->outDir = $config['outDir'] ?? './public';
        $this->contentDir = $config['contentDir'] ?? './content';
        $this->description = $config['description'] ?? '';

        // Merge provided generators with defaults
        $providedGenerators = $config['generators'] ?? [];
        $this->generators = array_merge(self::DEFAULT_GENERATORS, $providedGenerators);

        $this->robots = $config['robots'] ?? [];
        $this->widget = $config['widget'] ?? [];
        $this->schema = $config['schema'] ?? [];
        $this->og = $config['og'] ?? [];

        // Create Firecrawl config
        $firecrawlConfig = $config['firecrawl'] ?? [];
        $this->firecrawl = [
            'apiKey' => $firecrawlConfig['apiKey'] ?? '',
            'apiUrl' => $firecrawlConfig['apiUrl'] ?? 'https://api.firecrawl.dev/v1',
            'enabled' => !empty($firecrawlConfig['apiKey']) ?? false,
        ];

        // Create OpenAI config if provided
        $this->openai = isset($config['openai']) && is_array($config['openai'])
            ? OpenAIConfig::fromArray($config['openai'])
            : null;
    }

    /**
     * Create from array configuration
     *
     * @param array<string, mixed> $config
     */
    public static function fromArray(array $config): self
    {
        return new self($config);
    }
}
