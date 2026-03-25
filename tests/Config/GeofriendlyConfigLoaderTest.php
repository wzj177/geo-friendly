<?php

declare(strict_types=1);

namespace GeoFriendly\Tests\Config;

use GeoFriendly\Config\GeofriendlyConfig;
use GeoFriendly\Config\GeofriendlyConfigLoader;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

class GeofriendlyConfigLoaderTest extends TestCase
{
    private GeofriendlyConfigLoader $loader;

    protected function setUp(): void
    {
        $this->loader = new GeofriendlyConfigLoader();
    }

    public function testLoadFromArray(): void
    {
        $config = $this->loader->loadFromArray([
            'title' => 'Test Site',
            'url' => 'https://test.com',
        ]);

        $this->assertInstanceOf(GeofriendlyConfig::class, $config);
        $this->assertSame('Test Site', $config->title);
        $this->assertSame('https://test.com', $config->url);
    }

    public function testLoadFromYamlFile(): void
    {
        $yamlContent = <<<YAML
title: "YAML Test Site"
url: "https://yaml-test.com"
description: "A test site"
generators:
  robotsTxt: true
  sitemap: false
YAML;

        $tempFile = tempnam(sys_get_temp_dir(), 'yaml_');
        file_put_contents($tempFile, $yamlContent);

        $config = $this->loader->load($tempFile);

        $this->assertInstanceOf(GeofriendlyConfig::class, $config);
        $this->assertSame('YAML Test Site', $config->title);
        $this->assertSame('https://yaml-test.com', $config->url);
        $this->assertSame('A test site', $config->description);
        $this->assertTrue($config->generators['robotsTxt']);
        $this->assertFalse($config->generators['sitemap']);

        unlink($tempFile);
    }

    public function testLoadFromNonExistentFileThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Configuration file not found');

        $this->loader->load('/non/existent/file.yaml');
    }

    public function testLoadFromInvalidYamlThrowsException(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'yaml_');
        file_put_contents($tempFile, "invalid: yaml: content:\n  - broken");

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Failed to parse YAML');

        $this->loader->load($tempFile);

        unlink($tempFile);
    }

    public function testLoadFromYamlWithOpenAIConfig(): void
    {
        $yamlContent = <<<YAML
title: "AI Test Site"
openai:
  apiKey: "sk-test-key"
  model: "gpt-4o"
YAML;

        $tempFile = tempnam(sys_get_temp_dir(), 'yaml_');
        file_put_contents($tempFile, $yamlContent);

        $config = $this->loader->load($tempFile);

        $this->assertSame('AI Test Site', $config->title);
        $this->assertNotNull($config->openai);
        $this->assertSame('sk-test-key', $config->openai->apiKey);
        $this->assertSame('gpt-4o', $config->openai->model);
        $this->assertTrue($config->openai->enabled);

        unlink($tempFile);
    }
}
