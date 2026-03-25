<?php

declare(strict_types=1);

namespace GeoFriendly\Tests\Config;

use GeoFriendly\Config\GeofriendlyConfig;
use GeoFriendly\Config\OpenAIConfig;
use PHPUnit\Framework\TestCase;

class GeofriendlyConfigTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $config = new GeofriendlyConfig([]);

        $this->assertSame('My Site', $config->title);
        $this->assertSame('https://example.com', $config->url);
        $this->assertSame('./public', $config->outDir);
        $this->assertSame('./content', $config->contentDir);
        $this->assertSame('', $config->description);

        $expectedGenerators = [
            'robotsTxt' => true,
            'llmsTxt' => true,
            'llmsFullTxt' => true,
            'rawMarkdown' => true,
            'manifest' => true,
            'sitemap' => true,
            'aiIndex' => true,
            'schema' => true,
        ];
        $this->assertSame($expectedGenerators, $config->generators);

        $this->assertSame([], $config->robots);
        $this->assertSame([], $config->widget);
        $this->assertSame([], $config->schema);
        $this->assertSame([], $config->og);
        $this->assertNull($config->openai);
    }

    public function testCustomValues(): void
    {
        $config = new GeofriendlyConfig([
            'title' => 'Custom Site',
            'url' => 'https://custom.com',
            'outDir' => '/tmp/out',
            'contentDir' => '/tmp/content',
            'description' => 'My custom site',
            'generators' => ['robotsTxt' => false, 'sitemap' => true],
            'robots' => ['User-agent: *'],
            'widget' => ['color' => 'blue'],
            'schema' => ['@type' => 'WebSite'],
            'og' => ['type' => 'website'],
        ]);

        $this->assertSame('Custom Site', $config->title);
        $this->assertSame('https://custom.com', $config->url);
        $this->assertSame('/tmp/out', $config->outDir);
        $this->assertSame('/tmp/content', $config->contentDir);
        $this->assertSame('My custom site', $config->description);
        // Generators merge with defaults, so we expect all defaults plus our custom value
        $expectedGenerators = [
            'robotsTxt' => false, // overridden
            'llmsTxt' => true,
            'llmsFullTxt' => true,
            'rawMarkdown' => true,
            'manifest' => true,
            'sitemap' => true,
            'aiIndex' => true,
            'schema' => true,
        ];
        $this->assertSame($expectedGenerators, $config->generators);
        $this->assertSame(['User-agent: *'], $config->robots);
        $this->assertSame(['color' => 'blue'], $config->widget);
        $this->assertSame(['@type' => 'WebSite'], $config->schema);
        $this->assertSame(['type' => 'website'], $config->og);
        $this->assertNull($config->openai);
    }

    public function testOpenAIConfig(): void
    {
        $config = new GeofriendlyConfig([
            'openai' => [
                'apiKey' => 'test-key',
                'baseUrl' => 'https://custom.openai.com/v1',
                'model' => 'gpt-4',
            ],
        ]);

        $this->assertInstanceOf(OpenAIConfig::class, $config->openai);
        $this->assertSame('test-key', $config->openai->apiKey);
        $this->assertSame('https://custom.openai.com/v1', $config->openai->baseUrl);
        $this->assertSame('gpt-4', $config->openai->model);
        $this->assertTrue($config->openai->enabled);
    }

    public function testOpenAIConfigDisabledWhenNoApiKey(): void
    {
        $config = new GeofriendlyConfig([
            'openai' => [
                'baseUrl' => 'https://custom.openai.com/v1',
                'model' => 'gpt-4',
            ],
        ]);

        $this->assertInstanceOf(OpenAIConfig::class, $config->openai);
        $this->assertSame('', $config->openai->apiKey);
        $this->assertFalse($config->openai->enabled);
    }

    public function testPartialGeneratorsMergeWithDefaults(): void
    {
        $config = new GeofriendlyConfig([
            'generators' => ['robotsTxt' => false],
        ]);

        $this->assertFalse($config->generators['robotsTxt']);
        $this->assertTrue($config->generators['llmsTxt']);
        $this->assertTrue($config->generators['sitemap']);
    }
}
