# geo-friendly PHP Package Implementation Plan

**Last Updated:** 2026-03-25

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a PHP 8.2+ Composer package for Generative Engine Optimization (GEO) that generates AI-friendly files (robots.txt, llms.txt, llms-full.txt, sitemap.xml, docs.json, ai-index.json, schema.json) with CLI tool and framework integrations.

**Architecture:** Modular generator pattern with platform integrations. Core generators produce AI-friendly files from local content. Optional AI enhancement via OpenAI for descriptions, content optimization, GEO scoring, and schema generation.

**Tech Stack:** PHP 8.2+, Composer, Symfony Console, Symfony YAML, Guzzle HTTP, League CommonMark, OpenAI PHP Client (optional)

---

## File Structure Overview

```
geo-friendly/
├── composer.json
├── README.md
├── LICENSE.md
├── bin/geo
├── config/geofriendly.yaml.dist
├── src/
│   ├── GeoFriendly.php
│   ├── Config/
│   │   ├── GeofriendlyConfig.php
│   │   ├── GeofriendlyConfigLoader.php
│   │   └── OpenAIConfig.php
│   ├── Generator/
│   │   ├── GeneratorInterface.php
│   │   ├── RobotsTxtGenerator.php
│   │   ├── LlmsTxtGenerator.php
│   │   ├── LlmsFullTxtGenerator.php
│   │   ├── SitemapGenerator.php
│   │   ├── DocsJsonGenerator.php
│   │   ├── AiIndexGenerator.php
│   │   └── SchemaGenerator.php
│   ├── Generator/Enhanced/
│   │   ├── AiLlmsTxtGenerator.php
│   │   ├── AiContentEnhancer.php
│   │   └── AiSchemaGenerator.php
│   ├── Audit/
│   │   ├── AuditorInterface.php
│   │   ├── FilePresenceAuditor.php
│   │   ├── FormatValidator.php
│   │   ├── ContentQualityAuditor.php
│   │   ├── SocialMediaAuditor.php
│   │   └── GeoScoreCalculator.php
│   ├── Platform/
│   │   ├── PlatformInterface.php
│   │   ├── AbstractPlatform.php
│   │   ├── WordPress/
│   │   │   ├── WordPressPlatform.php
│   │   │   └── WordPressPlugin.php
│   │   ├── Shopify/
│   │   │   └── ShopifyPlatform.php
│   │   ├── Laravel/
│   │   │   └── LaravelServiceProvider.php
│   │   └── Symfony/
│   │       └── GeoFriendlyBundle.php
│   ├── CLI/
│   │   ├── Application.php
│   │   └── Command/
│   │       ├── GenerateCommand.php
│   │       ├── InitCommand.php
│   │       ├── CheckCommand.php
│   │       └── ReportCommand.php
│   └── Utils/
│       ├── MarkdownParser.php
│       ├── UrlHelper.php
│       ├── FileHelper.php
│       └── ContentExtractor.php
├── examples/
│   ├── cli-single-url.php
│   ├── wordpress-plugin/
│   ├── shopify-app/
│   ├── laravel/
│   └── symfony/
└── tests/
    ├── Unit/
    │   ├── Generator/
    │   ├── Audit/
    │   ├── Config/
    │   └── Utils/
    └── Feature/
        └── CLI/
```

---

## Phase 1: Project Foundation

### Task 1: Initialize Composer Package

**Files:**
- Create: `composer.json`
- Create: `README.md`
- Create: `LICENSE.md`

- [ ] **Step 1: Create composer.json**

```json
{
    "name": "geo-friendly/geo-friendly",
    "description": "Generative Engine Optimization for PHP - Make your site discoverable by AI answer engines",
    "type": "library",
    "keywords": ["geo", "seo", "llm", "ai", "chatgpt", "claude", "perplexity"],
    "license": "MIT",
    "require": {
        "php": "^8.2",
        "symfony/console": "^6.0|^7.0",
        "symfony/yaml": "^6.0|^7.0",
        "guzzlehttp/guzzle": "^7.0",
        "league/commonmark": "^2.0"
    },
    "require-dev": {
        "phpunit/phpunit": "^10.0",
        "phpstan/phpstan": "^1.0"
    },
    "suggest": {
        "openai-php/client": "Required for AI-enhanced features"
    },
    "bin": ["bin/geo"],
    "autoload": {
        "psr-4": {
            "GeoFriendly\\": "src/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "GeoFriendly\\Tests\\": "tests/"
        }
    }
}
```

- [ ] **Step 2: Create README.md**

```markdown
# geo-friendly

Generative Engine Optimization for PHP. Make your site discoverable by ChatGPT, Claude, Perplexity, and every AI answer engine.

## Installation

```bash
composer require geo-friendly/geo-friendly
```

## Quick Start

```php
use GeoFriendly\GeoFriendly;

$geo = new GeoFriendly([
    'title' => 'My Site',
    'url' => 'https://example.com',
    'description' => 'A site optimized for AI discovery',
]);

$geo->generate('./public');
```

## CLI Usage

```bash
# Generate GEO files
./bin/geo generate --url=https://example.com --title="My Site"

# Create config file
./bin/geo init

# Check GEO readiness
./bin/geo check

# Full report
./bin/geo report
```

## License

MIT
```

- [ ] **Step 3: Create LICENSE.md**

Use MIT License text from https://opensource.org/licenses/MIT

- [ ] **Step 4: Commit**

```bash
git add composer.json README.md LICENSE.md
git commit -m "feat: initialize composer package with basic metadata"
```

---

### Task 2: Create Configuration System

**Files:**
- Create: `src/Config/GeofriendlyConfig.php`
- Create: `src/Config/GeofriendlyConfigLoader.php`
- Create: `src/Config/OpenAIConfig.php`
- Create: `config/geofriendly.yaml.dist`
- Test: `tests/Unit/Config/GeofriendlyConfigTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php
declare(strict_types=1);

namespace GeoFriendly\Tests\Config;

use GeoFriendly\Config\GeofriendlyConfig;
use PHPUnit\Framework\TestCase;

class GeofriendlyConfigTest extends TestCase
{
    public function testCreateConfigFromArray(): void
    {
        $config = new GeofriendlyConfig([
            'title' => 'Test Site',
            'url' => 'https://test.com',
            'description' => 'Test description',
        ]);

        $this->assertEquals('Test Site', $config->title);
        $this->assertEquals('https://test.com', $config->url);
        $this->assertEquals('Test description', $config->description);
    }

    public function testConfigWithDefaultValues(): void
    {
        $config = new GeofriendlyConfig([
            'title' => 'Test Site',
            'url' => 'https://test.com',
        ]);

        $this->assertEquals('./public', $config->outDir);
        $this->assertTrue($config->generators['robotsTxt']);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

```bash
./vendor/bin/phpunit tests/Unit/Config/GeofriendlyConfigTest.php
```
Expected: Class not found errors

- [ ] **Step 3: Implement GeofriendlyConfig class**

```php
<?php
declare(strict_types=1);

namespace GeoFriendly\Config;

readonly class GeofriendlyConfig
{
    public string $title;
    public string $url;
    public string $outDir;
    public string $contentDir;
    public ?string $description;
    public array $generators;
    public array $robots;
    public array $widget;
    public array $schema;
    public array $og;
    public ?OpenAIConfig $openai;

    public function __construct(array $config)
    {
        $this->title = $config['title'] ?? 'My Site';
        $this->url = $config['url'] ?? 'https://example.com';
        $this->outDir = $config['outDir'] ?? './public';
        $this->contentDir = $config['contentDir'] ?? './content';
        $this->description = $config['description'] ?? null;

        $this->generators = array_merge([
            'robotsTxt' => true,
            'llmsTxt' => true,
            'llmsFullTxt' => true,
            'rawMarkdown' => true,
            'manifest' => true,
            'sitemap' => true,
            'aiIndex' => true,
            'schema' => true,
        ], $config['generators'] ?? []);

        $this->robots = $config['robots'] ?? [
            'allow' => ['/'],
            'disallow' => ['/admin'],
            'crawlDelay' => 0,
        ];

        $this->widget = $config['widget'] ?? [
            'enabled' => true,
            'position' => 'bottom-right',
        ];

        $this->schema = $config['schema'] ?? [
            'enabled' => true,
            'organization' => ['name' => '', 'url' => ''],
            'defaultType' => 'WebPage',
        ];

        $this->og = $config['og'] ?? [
            'enabled' => true,
            'image' => '',
            'twitterHandle' => '',
        ];

        $this->openai = isset($config['openai']) ? new OpenAIConfig($config['openai']) : null;
    }
}
```

- [ ] **Step 4: Implement OpenAIConfig class**

```php
<?php
declare(strict_types=1);

namespace GeoFriendly\Config;

readonly class OpenAIConfig
{
    public string $apiKey;
    public string $baseUrl;
    public string $model;
    public bool $enabled;

    public function __construct(array $config)
    {
        $this->apiKey = $config['apiKey'] ?? '';
        $this->baseUrl = $config['baseUrl'] ?? 'https://api.openai.com/v1';
        $this->model = $config['model'] ?? 'gpt-4o-mini';
        $this->enabled = !empty($this->apiKey);
    }
}
```

- [ ] **Step 5: Implement GeofriendlyConfigLoader class**

```php
<?php
declare(strict_types=1);

namespace GeoFriendly\Config;

use Symfony\Component\Yaml\Yaml;

class GeofriendlyConfigLoader
{
    public static function load(string $path): GeofriendlyConfig
    {
        if (!file_exists($path)) {
            throw new \InvalidArgumentException("Config file not found: {$path}");
        }

        $data = Yaml::parseFile($path);
        return new GeofriendlyConfig($data);
    }

    public static function loadFromArray(array $config): GeofriendlyConfig
    {
        return new GeofriendlyConfig($config);
    }
}
```

- [ ] **Step 6: Create example config file**

```yaml
# config/geofriendly.yaml.dist
title: 'My Site'
url: 'https://example.com'
description: 'A site optimized for AI discovery'

# Output directory for generated files
outDir: './public'

# Content directory with markdown files
contentDir: './content'

# Toggle individual generators
generators:
  robotsTxt: true
  llmsTxt: true
  llmsFullTxt: true
  rawMarkdown: true
  manifest: true
  sitemap: true
  aiIndex: true
  schema: true

# OpenAI configuration for enhanced features
openai:
  # API key for OpenAI or compatible service
  apiKey: '%env(OPENAI_API_KEY)%'
  # Custom base URL for OpenAI-compatible APIs
  baseUrl: 'https://api.openai.com/v1'
  # Model to use for AI features
  model: 'gpt-4o-mini'

# Customize robots.txt
robots:
  allow:
    - '/'
  disallow:
    - '/admin'
    - '/api'
  crawlDelay: 0

# Schema.org configuration
schema:
  enabled: true
  organization:
    name: 'My Company'
    url: 'https://example.com'
  defaultType: 'WebPage'

# Open Graph configuration
og:
  enabled: true
  image: 'https://example.com/og.png'
  twitterHandle: '@mycompany'

# Widget configuration
widget:
  enabled: true
  position: 'bottom-right'
```

- [ ] **Step 7: Run tests to verify they pass**

```bash
./vendor/bin/phpunit tests/Unit/Config/GeofriendlyConfigTest.php
```
Expected: PASS

- [ ] **Step 8: Commit**

```bash
git add src/Config/ config/ tests/Unit/Config/
git commit -m "feat: implement configuration system with YAML support"
```

---

### Task 3: Create Core Generator Interface

**Files:**
- Create: `src/Generator/GeneratorInterface.php`
- Test: `tests/Unit/Generator/GeneratorInterfaceTest.php`

- [ ] **Step 1: Write the interface definition**

```php
<?php
declare(strict_types=1);

namespace GeoFriendly\Generator;

use GeoFriendly\Config\GeofriendlyConfig;

interface GeneratorInterface
{
    /**
     * Generate the file content.
     *
     * @param GeofriendlyConfig $config
     * @return string The generated content
     */
    public function generate(GeofriendlyConfig $config): string;

    /**
     * Get the output filename.
     *
     * @return string
     */
    public function getFilename(): string;
}
```

- [ ] **Step 2: Commit**

```bash
git add src/Generator/GeneratorInterface.php
git commit -m "feat: define generator interface"
```

---

## Phase 2: Core Generators

### Task 4: Implement RobotsTxtGenerator

**Files:**
- Create: `src/Generator/RobotsTxtGenerator.php`
- Test: `tests/Unit/Generator/RobotsTxtGeneratorTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php
declare(strict_types=1);

namespace GeoFriendly\Tests\Generator;

use GeoFriendly\Config\GeofriendlyConfig;
use GeoFriendly\Generator\RobotsTxtGenerator;
use PHPUnit\Framework\TestCase;

class RobotsTxtGeneratorTest extends TestCase
{
    public function testGenerateBasicRobotsTxt(): void
    {
        $config = new GeofriendlyConfig([
            'title' => 'Test Site',
            'url' => 'https://test.com',
        ]);

        $generator = new RobotsTxtGenerator();
        $content = $generator->generate($config);

        $this->assertStringContainsString('User-agent: *', $content);
        $this->assertStringContainsString('Allow: /', $content);
    }

    public function testGetFilename(): void
    {
        $generator = new RobotsTxtGenerator();
        $this->assertEquals('robots.txt', $generator->getFilename());
    }

    public function testIncludeAiCrawlers(): void
    {
        $config = new GeofriendlyConfig([
            'title' => 'Test Site',
            'url' => 'https://test.com',
        ]);

        $generator = new RobotsTxtGenerator();
        $content = $generator->generate($config);

        // Check for common AI crawlers
        $this->assertStringContainsString('GPTBot', $content);
        $this->assertStringContainsString('ChatGPT-User', $content);
        $this->assertStringContainsString('CCBot', $content);
        $this->assertStringContainsString(' anthropic-ai', $content);
        $this->assertStringContainsString('Claude-Web', $content);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

```bash
./vendor/bin/phpunit tests/Unit/Generator/RobotsTxtGeneratorTest.php
```
Expected: Class not found

- [ ] **Step 3: Implement RobotsTxtGenerator**

```php
<?php
declare(strict_types=1);

namespace GeoFriendly\Generator;

use GeoFriendly\Config\GeofriendlyConfig;

class RobotsTxtGenerator implements GeneratorInterface
{
    private const AI_CRAWLERS = [
        'GPTBot' => 'OpenAI GPT Bot',
        'ChatGPT-User' => 'OpenAI ChatGPT User',
        'CCBot' => 'CommonCrawl',
        'anthropic-ai' => 'Anthropic Claude',
        'Claude-Web' => 'Anthropic Claude Web',
        'Claude-Vertex' => 'Anthropic Claude via Google Cloud',
        'Google-Extended' => 'Google AI crawlers',
        'PerplexityBot' => 'Perplexity AI',
        'YouBot' => 'You.com AI',
        'OmniBot' => 'Omni AI',
        'Amazonbot' => 'Amazon AI crawler',
        'FacebookBot' => 'Facebook AI crawler',
        'Applebot-Extended' => 'Apple AI crawler',
        'Bytespider' => 'ByteDance AI crawler',
    ];

    public function generate(GeofriendlyConfig $config): string
    {
        $lines = [];

        // Standard crawlers
        $lines[] = '# Standard crawlers';
        $lines[] = 'User-agent: *';

        foreach ($config->robots['allow'] ?? [] as $path) {
            $lines[] = "Allow: {$path}";
        }

        foreach ($config->robots['disallow'] ?? [] as $path) {
            $lines[] = "Disallow: {$path}";
        }

        if (($config->robots['crawlDelay'] ?? 0) > 0) {
            $lines[] = "Crawl-delay: {$config->robots['crawlDelay']}";
        }

        $lines[] = '';
        $lines[] = '# AI crawlers';
        $lines[] = '# Allow AI crawlers to index your content for AI answers';

        foreach (self::AI_CRAWLERS as $agent => $description) {
            $lines[] = "User-agent: {$agent}";
            $lines[] = "Allow: /";
            $lines[] = '';
        }

        // Sitemap reference
        $lines[] = '# Sitemap';
        $lines[] = "Sitemap: {$config->url}/sitemap.xml";

        return implode("\n", $lines) . "\n";
    }

    public function getFilename(): string
    {
        return 'robots.txt';
    }
}
```

- [ ] **Step 4: Run tests to verify they pass**

```bash
./vendor/bin/phpunit tests/Unit/Generator/RobotsTxtGeneratorTest.php
```
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add src/Generator/RobotsTxtGenerator.php tests/Unit/Generator/RobotsTxtGeneratorTest.php
git commit -m "feat: implement robots.txt generator with AI crawler support"
```

---

### Task 5: Implement LlmsTxtGenerator

**Files:**
- Create: `src/Generator/LlmsTxtGenerator.php`
- Create: `src/Utils/FileHelper.php`
- Test: `tests/Unit/Generator/LlmsTxtGeneratorTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php
declare(strict_types=1);

namespace GeoFriendly\Tests\Generator;

use GeoFriendly\Config\GeofriendlyConfig;
use GeoFriendly\Generator\LlmsTxtGenerator;
use PHPUnit\Framework\TestCase;

class LlmsTxtGeneratorTest extends TestCase
{
    public function testGenerateBasicLlmsTxt(): void
    {
        $config = new GeofriendlyConfig([
            'title' => 'Test Site',
            'url' => 'https://test.com',
            'description' => 'A test site',
        ]);

        $generator = new LlmsTxtGenerator();
        $content = $generator->generate($config);

        $this->assertStringContainsString('# Test Site', $content);
        $this->assertStringContainsString('https://test.com', $content);
    }

    public function testGetFilename(): void
    {
        $generator = new LlmsTxtGenerator();
        $this->assertEquals('llms.txt', $generator->getFilename());
    }

    public function testIncludeQuickLinks(): void
    {
        $config = new GeofriendlyConfig([
            'title' => 'Test Site',
            'url' => 'https://test.com',
        ]);

        $generator = new LlmsTxtGenerator();
        $content = $generator->generate($config);

        $this->assertStringContainsString('llms-full.txt', $content);
        $this->assertStringContainsString('docs.json', $content);
        $this->assertStringContainsString('ai-index.json', $content);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

```bash
./vendor/bin/phpunit tests/Unit/Generator/LlmsTxtGeneratorTest.php
```
Expected: Class not found

- [ ] **Step 3: Implement FileHelper utility**

```php
<?php
declare(strict_types=1);

namespace GeoFriendly\Utils;

use Symfony\Component\Finder\Finder;

class FileHelper
{
    private const SKIP_DIRS = ['node_modules', 'vendor', '.git', 'public', 'dist', 'build', 'coverage'];

    /**
     * Collect markdown files from a directory.
     *
     * @param string $dir
     * @return array<int, array{path: string, title: string, description: string|null, content: string}>
     */
    public static function collectMarkdownFiles(string $dir): array
    {
        if (!is_dir($dir)) {
            return [];
        }

        $files = [];
        $finder = new Finder();

        try {
            $finder->files()
                ->in($dir)
                ->name('*.md')
                ->name('*.mdx')
                ->notPath(self::SKIP_DIRS)
                ->notName('.*')
                ->sortByName();

            foreach ($finder as $file) {
                $content = $file->getContents();
                $meta = self::parseFrontmatter($content);

                $files[] = [
                    'path' => $file->getRelativePathname(),
                    'title' => $meta['title'] ?? self::extractTitle($content),
                    'description' => $meta['description'] ?? null,
                    'content' => $content,
                ];
            }
        } catch (\Exception $e) {
            // Return empty array on error
        }

        return $files;
    }

    private static function parseFrontmatter(string $content): array
    {
        if (!str_starts_with($content, '---')) {
            return [];
        }

        $end = strpos($content, '---', 3);
        if ($end === false) {
            return [];
        }

        $yaml = substr($content, 3, $end - 3);
        $lines = explode("\n", $yaml);

        $meta = [];
        foreach ($lines as $line) {
            if (str_contains($line, ':')) {
                [$key, $value] = explode(':', $line, 2);
                $meta[trim($key)] = trim($value);
            }
        }

        return $meta;
    }

    private static function extractTitle(string $content): string
    {
        // First try frontmatter
        $meta = self::parseFrontmatter($content);
        if (isset($meta['title'])) {
            return $meta['title'];
        }

        // Then try first heading
        if (preg_match('/^#\s+(.+)$/m', $content, $matches)) {
            return trim($matches[1]);
        }

        return 'Untitled';
    }
}
```

- [ ] **Step 4: Implement LlmsTxtGenerator**

```php
<?php
declare(strict_types=1);

namespace GeoFriendly\Generator;

use GeoFriendly\Config\GeofriendlyConfig;
use GeoFriendly\Utils\FileHelper;

class LlmsTxtGenerator implements GeneratorInterface
{
    public function generate(GeofriendlyConfig $config): string
    {
        $lines = [];

        $lines[] = "# {$config->title}";
        $lines[] = '';

        if ($config->description) {
            $lines[] = "> {$config->description}";
            $lines[] = '';
        }

        $lines[] = '## About';
        $lines[] = '';
        $lines[] = 'This file provides a structured overview of the documentation and content available on this site,';
        $lines[] = 'optimized for consumption by Large Language Models (LLMs) and AI assistants.';
        $lines[] = '';

        // Collect markdown files
        $markdownFiles = FileHelper::collectMarkdownFiles($config->contentDir);

        if (!empty($markdownFiles)) {
            $lines[] = '## Documentation';
            $lines[] = '';

            // Group by directory
            $grouped = [];
            foreach ($markdownFiles as $file) {
                $dir = dirname($file['path']) === '.' ? 'Main Documentation' : dirname($file['path']);
                if (!isset($grouped[$dir])) {
                    $grouped[$dir] = [];
                }
                $grouped[$dir][] = $file;
            }

            foreach ($grouped as $dir => $files) {
                $lines[] = "### {$dir}";
                $lines[] = '';

                foreach ($files as $file) {
                    $url = rtrim($config->url, '/') . '/' . str_replace(['.md', '.mdx'], '', $file['path']);
                    $lines[] = "- [{$file['title']}]({$url})";

                    if ($file['description']) {
                        $lines[] = "  {$file['description']}";
                    }
                }
                $lines[] = '';
            }
        }

        $lines[] = '## Quick Links';
        $lines[] = '';
        $lines[] = "- Full Documentation: {$config->url}/llms-full.txt";
        $lines[] = "- Documentation Manifest: {$config->url}/docs.json";
        $lines[] = "- AI-Optimized Index: {$config->url}/ai-index.json";
        $lines[] = "- Sitemap: {$config->url}/sitemap.xml";
        $lines[] = '';

        $lines[] = '## For LLMs';
        $lines[] = '';
        $lines[] = 'To get the complete documentation in a single file, request:';
        $lines[] = "{$config->url}/llms-full.txt";
        $lines[] = '';
        $lines[] = 'For structured access to individual pages with metadata:';
        $lines[] = "{$config->url}/docs.json";
        $lines[] = '';
        $lines[] = 'For RAG (Retrieval Augmented Generation) systems:';
        $lines[] = "{$config->url}/ai-index.json";
        $lines[] = '';

        $lines[] = '---';
        $lines[] = 'Generated by geo-friendly - Generative Engine Optimization for PHP';
        $lines[] = 'Learn more at https://github.com/geo-friendly/geo-friendly';

        return implode("\n", $lines) . "\n";
    }

    public function getFilename(): string
    {
        return 'llms.txt';
    }
}
```

- [ ] **Step 5: Add symfony/finder dependency**

Update composer.json:
```json
"require": {
    "php": "^8.2",
    "symfony/console": "^6.0|^7.0",
    "symfony/yaml": "^6.0|^7.0",
    "symfony/finder": "^6.0|^7.0",
    "guzzlehttp/guzzle": "^7.0",
    "league/commonmark": "^2.0"
}
```

- [ ] **Step 6: Run tests to verify they pass**

```bash
composer update
./vendor/bin/phpunit tests/Unit/Generator/LlmsTxtGeneratorTest.php
```
Expected: PASS

- [ ] **Step 7: Commit**

```bash
git add composer.json src/Generator/LlmsTxtGenerator.php src/Utils/FileHelper.php tests/Unit/Generator/LlmsTxtGeneratorTest.php
git commit -m "feat: implement llms.txt generator with markdown file collection"
```

---

### Task 6: Implement LlmsFullTxtGenerator

**Files:**
- Create: `src/Generator/LlmsFullTxtGenerator.php`
- Test: `tests/Unit/Generator/LlmsFullTxtGeneratorTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php
declare(strict_types=1);

namespace GeoFriendly\Tests\Generator;

use GeoFriendly\Config\GeofriendlyConfig;
use GeoFriendly\Generator\LlmsFullTxtGenerator;
use PHPUnit\Framework\TestCase;

class LlmsFullTxtGeneratorTest extends TestCase
{
    public function testGenerateBasicLlmsFullTxt(): void
    {
        $config = new GeofriendlyConfig([
            'title' => 'Test Site',
            'url' => 'https://test.com',
            'contentDir' => __DIR__ . '/../fixtures/content',
        ]);

        $generator = new LlmsFullTxtGenerator();
        $content = $generator->generate($config);

        $this->assertStringContainsString('# Test Site - Complete Documentation', $content);
        $this->assertStringContainsString('---', $content); // Section separator
    }

    public function testGetFilename(): void
    {
        $generator = new LlmsFullTxtGenerator();
        $this->assertEquals('llms-full.txt', $generator->getFilename());
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

```bash
./vendor/bin/phpunit tests/Unit/Generator/LlmsFullTxtGeneratorTest.php
```
Expected: Class not found

- [ ] **Step 3: Implement LlmsFullTxtGenerator**

```php
<?php
declare(strict_types=1);

namespace GeoFriendly\Generator;

use GeoFriendly\Config\GeofriendlyConfig;
use GeoFriendly\Utils\FileHelper;

class LlmsFullTxtGenerator implements GeneratorInterface
{
    public function generate(GeofriendlyConfig $config): string
    {
        $lines = [];

        $lines[] = "# {$config->title} - Complete Documentation";
        $lines[] = '';
        $lines[] = 'This file contains all documentation concatenated into a single file for easy consumption by LLMs.';
        $lines[] = '';

        if ($config->description) {
            $lines[] = "> {$config->description}";
            $lines[] = '';
        }

        $lines[] = '## Table of Contents';
        $lines[] = '';
        $lines[] = 'This document includes all content from this project.';
        $lines[] = 'Each section is separated by a horizontal rule (---) for easy parsing.';
        $lines[] = '';

        $markdownFiles = FileHelper::collectMarkdownFiles($config->contentDir);

        if (!empty($markdownFiles)) {
            foreach ($markdownFiles as $file) {
                $lines[] = '---';
                $lines[] = '';
                $lines[] = "# {$file['title']}";
                $lines[] = '';
                $lines[] = "Source: {$file['path']}";
                $lines[] = '';

                if ($file['description']) {
                    $lines[] = "> {$file['description']}";
                    $lines[] = '';
                }

                // Add content without frontmatter
                $content = $file['content'];
                $content = preg_replace('/^---\n.*?\n---\n/s', '', $content);
                $lines[] = trim($content);
                $lines[] = '';
            }
        } else {
            // Always include site overview
            $lines[] = '---';
            $lines[] = '';
            $lines[] = "# {$config->title}";
            $lines[] = '';
            $lines[] = "URL: {$config->url}";
            $lines[] = '';

            if ($config->description) {
                $lines[] = $config->description;
                $lines[] = '';
            }
        }

        $lines[] = '---';
        $lines[] = '';
        $lines[] = '## About This Document';
        $lines[] = '';
        $lines[] = 'This concatenated documentation file is generated automatically by geo-friendly';
        $lines[] = 'to make it easier for AI systems to understand the complete context of this project.';
        $lines[] = '';
        $lines[] = "For a structured index, see: {$config->url}/llms.txt";
        $lines[] = "For individual files, see: {$config->url}/docs.json";
        $lines[] = '';
        $lines[] = 'Generated by geo-friendly - https://github.com/geo-friendly/geo-friendly';

        return implode("\n", $lines) . "\n";
    }

    public function getFilename(): string
    {
        return 'llms-full.txt';
    }
}
```

- [ ] **Step 4: Run tests to verify they pass**

```bash
./vendor/bin/phpunit tests/Unit/Generator/LlmsFullTxtGeneratorTest.php
```
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add src/Generator/LlmsFullTxtGenerator.php tests/Unit/Generator/LlmsFullTxtGeneratorTest.php
git commit -m "feat: implement llms-full.txt generator with concatenated content"
```

---

### Task 7: Implement SitemapGenerator

**Files:**
- Create: `src/Generator/SitemapGenerator.php`
- Test: `tests/Unit/Generator/SitemapGeneratorTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php
declare(strict_types=1);

namespace GeoFriendly\Tests\Generator;

use GeoFriendly\Config\GeofriendlyConfig;
use GeoFriendly\Generator\SitemapGenerator;
use PHPUnit\Framework\TestCase;

class SitemapGeneratorTest extends TestCase
{
    public function testGenerateBasicSitemap(): void
    {
        $config = new GeofriendlyConfig([
            'title' => 'Test Site',
            'url' => 'https://test.com',
        ]);

        $generator = new SitemapGenerator();
        $content = $generator->generate($config);

        $this->assertStringContainsString('<?xml', $content);
        $this->assertStringContainsString('https://test.com', $content);
        $this->assertStringContainsString('<urlset', $content);
    }

    public function testGetFilename(): void
    {
        $generator = new SitemapGenerator();
        $this->assertEquals('sitemap.xml', $generator->getFilename());
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

```bash
./vendor/bin/phpunit tests/Unit/Generator/SitemapGeneratorTest.php
```
Expected: Class not found

- [ ] **Step 3: Implement SitemapGenerator**

```php
<?php
declare(strict_types=1);

namespace GeoFriendly\Generator;

use GeoFriendly\Config\GeofriendlyConfig;
use GeoFriendly\Utils\FileHelper;

class SitemapGenerator implements GeneratorInterface
{
    public function generate(GeofriendlyConfig $config): string
    {
        $urls = [];

        // Add homepage
        $urls[] = [
            'loc' => $config->url,
            'priority' => '1.0',
        ];

        // Add markdown files
        $markdownFiles = FileHelper::collectMarkdownFiles($config->contentDir);

        foreach ($markdownFiles as $file) {
            $path = str_replace(['.md', '.mdx'], '', $file['path']);
            $url = rtrim($config->url, '/') . '/' . $path;
            $urls[] = [
                'loc' => $url,
                'priority' => '0.8',
            ];
        }

        // Add GEO files
        $geoFiles = [
            'llms.txt' => '1.0',
            'llms-full.txt' => '0.8',
            'docs.json' => '0.8',
            'ai-index.json' => '0.8',
        ];

        foreach ($geoFiles as $file => $priority) {
            $urls[] = [
                'loc' => rtrim($config->url, '/') . '/' . $file,
                'priority' => $priority,
            ];
        }

        return $this->buildXml($urls);
    }

    private function buildXml(array $urls): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        $xml .= "\n";

        foreach ($urls as $url) {
            $xml .= '  <url>';
            $xml .= "\n";
            $xml .= "    <loc>{$this->escape($url['loc'])}</loc>";
            $xml .= "\n";
            if (isset($url['priority'])) {
                $xml .= "    <priority>{$url['priority']}</priority>";
                $xml .= "\n";
            }
            $xml .= '  </url>';
            $xml .= "\n";
        }

        $xml .= '</urlset>';
        $xml .= "\n";

        return $xml;
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }

    public function getFilename(): string
    {
        return 'sitemap.xml';
    }
}
```

- [ ] **Step 4: Run tests to verify they pass**

```bash
./vendor/bin/phpunit tests/Unit/Generator/SitemapGeneratorTest.php
```
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add src/Generator/SitemapGenerator.php tests/Unit/Generator/SitemapGeneratorTest.php
git commit -m "feat: implement sitemap.xml generator"
```

---

### Task 8: Implement DocsJsonGenerator

**Files:**
- Create: `src/Generator/DocsJsonGenerator.php`
- Test: `tests/Unit/Generator/DocsJsonGeneratorTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php
declare(strict_types=1);

namespace GeoFriendly\Tests\Generator;

use GeoFriendly\Config\GeofriendlyConfig;
use GeoFriendly\Generator\DocsJsonGenerator;
use PHPUnit\Framework\TestCase;

class DocsJsonGeneratorTest extends TestCase
{
    public function testGenerateBasicDocsJson(): void
    {
        $config = new GeofriendlyConfig([
            'title' => 'Test Site',
            'url' => 'https://test.com',
        ]);

        $generator = new DocsJsonGenerator();
        $content = $generator->generate($config);
        $json = json_decode($content, true);

        $this->assertIsArray($json);
        $this->assertArrayHasKey('title', $json);
        $this->assertEquals('Test Site', $json['title']);
    }

    public function testGetFilename(): void
    {
        $generator = new DocsJsonGenerator();
        $this->assertEquals('docs.json', $generator->getFilename());
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

```bash
./vendor/bin/phpunit tests/Unit/Generator/DocsJsonGeneratorTest.php
```
Expected: Class not found

- [ ] **Step 3: Implement DocsJsonGenerator**

```php
<?php
declare(strict_types=1);

namespace GeoFriendly\Generator;

use GeoFriendly\Config\GeofriendlyConfig;
use GeoFriendly\Utils\FileHelper;

class DocsJsonGenerator implements GeneratorInterface
{
    public function generate(GeofriendlyConfig $config): string
    {
        $docs = [
            'title' => $config->title,
            'description' => $config->description,
            'url' => $config->url,
            'version' => '1.0',
            'generatedAt' => date('c'),
            'files' => [],
        ];

        $markdownFiles = FileHelper::collectMarkdownFiles($config->contentDir);

        foreach ($markdownFiles as $file) {
            $path = str_replace(['.md', '.mdx'], '', $file['path']);
            $docs['files'][] = [
                'title' => $file['title'],
                'description' => $file['description'],
                'url' => rtrim($config->url, '/') . '/' . $path,
                'path' => $path,
                'type' => 'markdown',
            ];
        }

        // Add GEO files
        $docs['files'][] = [
            'title' => 'LLM Summary',
            'description' => 'Structured overview for LLMs',
            'url' => rtrim($config->url, '/') . '/llms.txt',
            'path' => 'llms.txt',
            'type' => 'llms-txt',
        ];

        $docs['files'][] = [
            'title' => 'Complete Documentation',
            'description' => 'Full content for LLMs',
            'url' => rtrim($config->url, '/') . '/llms-full.txt',
            'path' => 'llms-full.txt',
            'type' => 'llms-full-txt',
        ];

        return json_encode($docs, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
    }

    public function getFilename(): string
    {
        return 'docs.json';
    }
}
```

- [ ] **Step 4: Run tests to verify they pass**

```bash
./vendor/bin/phpunit tests/Unit/Generator/DocsJsonGeneratorTest.php
```
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add src/Generator/DocsJsonGenerator.php tests/Unit/Generator/DocsJsonGeneratorTest.php
git commit -m "feat: implement docs.json generator"
```

---

### Task 9: Implement AiIndexGenerator

**Files:**
- Create: `src/Generator/AiIndexGenerator.php`
- Test: `tests/Unit/Generator/AiIndexGeneratorTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php
declare(strict_types=1);

namespace GeoFriendly\Tests\Generator;

use GeoFriendly\Config\GeofriendlyConfig;
use GeoFriendly\Generator\AiIndexGenerator;
use PHPUnit\Framework\TestCase;

class AiIndexGeneratorTest extends TestCase
{
    public function testGenerateBasicAiIndex(): void
    {
        $config = new GeofriendlyConfig([
            'title' => 'Test Site',
            'url' => 'https://test.com',
        ]);

        $generator = new AiIndexGenerator();
        $content = $generator->generate($config);
        $json = json_decode($content, true);

        $this->assertIsArray($json);
        $this->assertArrayHasKey('index', $json);
        $this->assertIsArray($json['index']);
    }

    public function testGetFilename(): void
    {
        $generator = new AiIndexGenerator();
        $this->assertEquals('ai-index.json', $generator->getFilename());
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

```bash
./vendor/bin/phpunit tests/Unit/Generator/AiIndexGeneratorTest.php
```
Expected: Class not found

- [ ] **Step 3: Implement AiIndexGenerator**

```php
<?php
declare(strict_types=1);

namespace GeoFriendly\Generator;

use GeoFriendly\Config\GeofriendlyConfig;
use GeoFriendly\Utils\FileHelper;

class AiIndexGenerator implements GeneratorInterface
{
    public function generate(GeofriendlyConfig $config): string
    {
        $index = [
            'version' => '1.0',
            'generatedAt' => date('c'),
            'site' => [
                'title' => $config->title,
                'description' => $config->description,
                'url' => $config->url,
            ],
            'index' => [],
        ];

        $markdownFiles = FileHelper::collectMarkdownFiles($config->contentDir);

        foreach ($markdownFiles as $file) {
            $path = str_replace(['.md', '.mdx'], '', $file['path']);
            $index['index'][] = [
                'url' => rtrim($config->url, '/') . '/' . $path,
                'title' => $file['title'],
                'description' => $file['description'],
                'keywords' => $this->extractKeywords($file['content']),
                'lastModified' => date('c'),
            ];
        }

        return json_encode($index, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
    }

    private function extractKeywords(string $content): array
    {
        // Remove frontmatter
        $content = preg_replace('/^---\n.*?\n---\n/s', '', $content);

        // Extract headings
        preg_match_all('/^#+\s+(.+)$/m', $content, $headings);
        $keywords = $headings[1] ?? [];

        // Extract bold text
        preg_match_all('/\*\*(.+?)\*\*/', $content, $bold);
        $keywords = array_merge($keywords, $bold[1] ?? []);

        // Extract links
        preg_match_all('/\[([^\]]+)\]\(/', $content, $links);
        $keywords = array_merge($keywords, $links[1] ?? []);

        // Clean and deduplicate
        $keywords = array_map(fn($k) => trim($k), $keywords);
        $keywords = array_filter($keywords, fn($k) => strlen($k) > 2);
        $keywords = array_unique($keywords);

        return array_values(array_slice($keywords, 0, 10));
    }

    public function getFilename(): string
    {
        return 'ai-index.json';
    }
}
```

- [ ] **Step 4: Run tests to verify they pass**

```bash
./vendor/bin/phpunit tests/Unit/Generator/AiIndexGeneratorTest.php
```
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add src/Generator/AiIndexGenerator.php tests/Unit/Generator/AiIndexGeneratorTest.php
git commit -m "feat: implement ai-index.json generator with keyword extraction"
```

---

### Task 10: Implement SchemaGenerator

**Files:**
- Create: `src/Generator/SchemaGenerator.php`
- Test: `tests/Unit/Generator/SchemaGeneratorTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php
declare(strict_types=1);

namespace GeoFriendly\Tests\Generator;

use GeoFriendly\Config\GeofriendlyConfig;
use GeoFriendly\Generator\SchemaGenerator;
use PHPUnit\Framework\TestCase;

class SchemaGeneratorTest extends TestCase
{
    public function testGenerateBasicSchema(): void
    {
        $config = new GeofriendlyConfig([
            'title' => 'Test Site',
            'url' => 'https://test.com',
            'schema' => [
                'enabled' => true,
                'organization' => [
                    'name' => 'Test Org',
                    'url' => 'https://test.com',
                ],
            ],
        ]);

        $generator = new SchemaGenerator();
        $content = $generator->generate($config);
        $json = json_decode($content, true);

        $this->assertIsArray($json);
        $this->assertArrayHasKey('@context', $json);
        $this->assertEquals('https://schema.org', $json['@context']);
    }

    public function testGetFilename(): void
    {
        $generator = new SchemaGenerator();
        $this->assertEquals('schema.json', $generator->getFilename());
    }

    public function testDisabledWhenConfigDisabled(): void
    {
        $config = new GeofriendlyConfig([
            'title' => 'Test Site',
            'url' => 'https://test.com',
            'schema' => ['enabled' => false],
        ]);

        $generator = new SchemaGenerator();
        $content = $generator->generate($config);

        $this->assertEmpty($content);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

```bash
./vendor/bin/phpunit tests/Unit/Generator/SchemaGeneratorTest.php
```
Expected: Class not found

- [ ] **Step 3: Implement SchemaGenerator**

```php
<?php
declare(strict_types=1);

namespace GeoFriendly\Generator;

use GeoFriendly\Config\GeofriendlyConfig;

class SchemaGenerator implements GeneratorInterface
{
    public function generate(GeofriendlyConfig $config): string
    {
        if (!($config->schema['enabled'] ?? false)) {
            return '';
        }

        $schema = [
            '@context' => 'https://schema.org',
            '@graph' => [],
        ];

        // Add WebSite schema
        $schema['@graph'][] = [
            '@type' => 'WebSite',
            'name' => $config->title,
            'url' => $config->url,
            'description' => $config->description,
        ];

        // Add Organization schema if configured
        if (!empty($config->schema['organization']['name'])) {
            $schema['@graph'][] = [
                '@type' => 'Organization',
                'name' => $config->schema['organization']['name'],
                'url' => $config->schema['organization']['url'],
            ];
        }

        return json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
    }

    public function getFilename(): string
    {
        return 'schema.json';
    }
}
```

- [ ] **Step 4: Run tests to verify they pass**

```bash
./vendor/bin/phpunit tests/Unit/Generator/SchemaGeneratorTest.php
```
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add src/Generator/SchemaGenerator.php tests/Unit/Generator/SchemaGeneratorTest.php
git commit -m "feat: implement schema.json generator with Schema.org support"
```

---

## Phase 3: CLI Application

### Task 11: Create CLI Application

**Files:**
- Create: `bin/geo`
- Create: `src/CLI/Application.php`

- [ ] **Step 1: Create CLI entry point**

```bash
#!/usr/bin/env php
<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use GeoFriendly\CLI\Application;

$app = new Application();
$app->run();
```

- [ ] **Step 2: Make it executable**

```bash
chmod +x bin/geo
```

- [ ] **Step 3: Implement CLI Application class**

```php
<?php
declare(strict_types=1);

namespace GeoFriendly\CLI;

use Symfony\Component\Console\Application as SymfonyApplication;
use GeoFriendly\CLI\Command\GenerateCommand;
use GeoFriendly\CLI\Command\InitCommand;
use GeoFriendly\CLI\Command\CheckCommand;
use GeoFriendly\CLI\Command\ReportCommand;

class Application
{
    private const VERSION = '0.1.0';

    public function run(): void
    {
        $app = new SymfonyApplication('geo-friendly', self::VERSION);

        $app->add(new GenerateCommand());
        $app->add(new InitCommand());
        $app->add(new CheckCommand());
        $app->add(new ReportCommand());

        $app->run();
    }
}
```

- [ ] **Step 4: Commit**

```bash
git add bin/geo src/CLI/Application.php
git commit -m "feat: create CLI application with Symfony Console"
```

---

### Task 12: Implement GenerateCommand

**Files:**
- Create: `src/CLI/Command/GenerateCommand.php`
- Create: `src/GeoFriendly.php`
- Test: `tests/Feature/CLI/GenerateCommandTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php
declare(strict_types=1);

namespace GeoFriendly\Tests\Feature\CLI;

use GeoFriendly\CLI\Command\GenerateCommand;
use GeoFriendly\Config\GeofriendlyConfig;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class GenerateCommandTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/geo-test-' . uniqid();
        mkdir($this->tempDir, 0777, true);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->tempDir)) {
            system("rm -rf " . escapeshellarg($this->tempDir));
        }
    }

    public function testExecuteWithBasicConfig(): void
    {
        $command = new GenerateCommand();
        $tester = new CommandTester($command);

        $tester->execute([
            '--url' => 'https://test.com',
            '--title' => 'Test Site',
            '--out' => $this->tempDir,
        ]);

        $this->assertEquals(0, $tester->getStatusCode());
        $this->assertFileExists($this->tempDir . '/robots.txt');
        $this->assertFileExists($this->tempDir . '/llms.txt');
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

```bash
./vendor/bin/phpunit tests/Feature/CLI/GenerateCommandTest.php
```
Expected: Class not found

- [ ] **Step 3: Implement GeoFriendly main class**

```php
<?php
declare(strict_types=1);

namespace GeoFriendly;

use GeoFriendly\Config\GeofriendlyConfig;
use GeoFriendly\Generator\GeneratorInterface;

class GeoFriendly
{
    private GeofriendlyConfig $config;
    private array $generators = [];

    public function __construct(array $config = [])
    {
        $this->config = new GeofriendlyConfig($config);
        $this->registerDefaultGenerators();
    }

    public function addGenerator(GeneratorInterface $generator): self
    {
        $this->generators[$generator->getFilename()] = $generator;
        return $this;
    }

    public function generate(?string $outputDir = null): array
    {
        $outputDir = $outputDir ?? $this->config->outDir;

        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0777, true);
        }

        $generated = [];
        $errors = [];

        foreach ($this->generators as $filename => $generator) {
            if (!$this->isGeneratorEnabled($filename)) {
                continue;
            }

            try {
                $content = $generator->generate($this->config);
                $filepath = $outputDir . '/' . $filename;

                if (!empty($content)) {
                    file_put_contents($filepath, $content);
                    $generated[] = $filename;
                }
            } catch (\Exception $e) {
                $errors[] = "{$filename}: {$e->getMessage()}";
            }
        }

        return [
            'generated' => $generated,
            'errors' => $errors,
        ];
    }

    private function registerDefaultGenerators(): void
    {
        $this->addGenerator(new \GeoFriendly\Generator\RobotsTxtGenerator());
        $this->addGenerator(new \GeoFriendly\Generator\LlmsTxtGenerator());
        $this->addGenerator(new \GeoFriendly\Generator\LlmsFullTxtGenerator());
        $this->addGenerator(new \GeoFriendly\Generator\SitemapGenerator());
        $this->addGenerator(new \GeoFriendly\Generator\DocsJsonGenerator());
        $this->addGenerator(new \GeoFriendly\Generator\AiIndexGenerator());
        $this->addGenerator(new \GeoFriendly\Generator\SchemaGenerator());
    }

    private function isGeneratorEnabled(string $filename): bool
    {
        $map = [
            'robots.txt' => 'robotsTxt',
            'llms.txt' => 'llmsTxt',
            'llms-full.txt' => 'llmsFullTxt',
            'sitemap.xml' => 'sitemap',
            'docs.json' => 'manifest',
            'ai-index.json' => 'aiIndex',
            'schema.json' => 'schema',
        ];

        $key = $map[$filename] ?? null;
        return $key === null || $this->config->generators[$key] ?? true;
    }
}
```

- [ ] **Step 4: Implement GenerateCommand**

```php
<?php
declare(strict_types=1);

namespace GeoFriendly\CLI\Command;

use GeoFriendly\GeoFriendly;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'generate', description: 'Generate all GEO files')]
class GenerateCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addOption('url', null, InputOption::VALUE_REQUIRED, 'Site URL')
            ->addOption('title', null, InputOption::VALUE_REQUIRED, 'Site title')
            ->addOption('description', null, InputOption::VALUE_OPTIONAL, 'Site description')
            ->addOption('out', null, InputOption::VALUE_OPTIONAL, 'Output directory', './public')
            ->addOption('config', null, InputOption::VALUE_OPTIONAL, 'Config file path')
            ->addOption('content-dir', null, InputOption::VALUE_OPTIONAL, 'Content directory', './content');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $config = [];

        // Load from config file if provided
        $configFile = $input->getOption('config');
        if ($configFile && file_exists($configFile)) {
            $loader = new \GeoFriendly\Config\GeofriendlyConfigLoader();
            $config = $loader->load($configFile);
        } else {
            // Use command line options
            $url = $input->getOption('url') ?: 'https://example.com';
            $title = $input->getOption('title') ?: 'My Site';

            $config = [
                'url' => $url,
                'title' => $title,
                'description' => $input->getOption('description'),
                'outDir' => $input->getOption('out'),
                'contentDir' => $input->getOption('content-dir'),
            ];
        }

        $io->title('geo-friendly - Generative Engine Optimization');

        $io->text("Generating GEO files for: <info>{$config['title']}</info>");
        $io->text("URL: <comment>{$config['url']}</comment>");
        $io->text("Output: <comment>{$config['outDir']}</comment>");
        $io->newLine();

        $geo = new GeoFriendly($config);
        $result = $geo->generate();

        if (!empty($result['generated'])) {
            $io->success("Generated " . count($result['generated']) . " files:");
            foreach ($result['generated'] as $file) {
                $io->text("  - {$file}");
            }
        }

        if (!empty($result['errors'])) {
            $io->error("Errors occurred:");
            foreach ($result['errors'] as $error) {
                $io->text("  - {$error}");
            }
            return Command::FAILURE;
        }

        $io->newLine();
        $io->note("Run 'geo check' to verify your GEO setup.");

        return Command::SUCCESS;
    }
}
```

- [ ] **Step 5: Run tests to verify they pass**

```bash
./vendor/bin/phpunit tests/Feature/CLI/GenerateCommandTest.php
```
Expected: PASS

- [ ] **Step 6: Test CLI manually**

```bash
./bin/geo generate --url=https://example.com --title="Test Site" --out=/tmp/geo-test
```

- [ ] **Step 7: Commit**

```bash
git add src/CLI/Command/GenerateCommand.php src/GeoFriendly.php tests/Feature/CLI/GenerateCommandTest.php
git commit -m "feat: implement generate command with core functionality"
```

---

### Task 13: Implement InitCommand

**Files:**
- Create: `src/CLI/Command/InitCommand.php`

- [ ] **Step 1: Implement InitCommand**

```php
<?php
declare(strict_types=1);

namespace GeoFriendly\CLI\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'init', description: 'Create a geofriendly.yaml configuration file')]
class InitCommand extends Command
{
    protected function configure(): void
    {
        $this->addOption('force', 'f', InputOption::VALUE_NONE, 'Overwrite existing config');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $configPath = getcwd() . '/geofriendly.yaml';

        if (file_exists($configPath) && !$input->getOption('force')) {
            $io->error("geofriendly.yaml already exists. Use --force to overwrite.");
            return Command::FAILURE;
        }

        $template = $this->getConfigTemplate();
        file_put_contents($configPath, $template);

        $io->success("Created geofriendly.yaml");
        $io->text("Edit the configuration and run 'geo generate' to generate GEO files.");
        $io->newLine();
        $io->note("For AI-enhanced features, set your OPENAI_API_KEY environment variable.");

        return Command::SUCCESS;
    }

    private function getConfigTemplate(): string
    {
        return <<<'YAML'
# geo-friendly configuration
title: 'My Site'
url: 'https://example.com'
description: 'A site optimized for AI discovery'

# Output directory for generated files
outDir: './public'

# Content directory with markdown files
contentDir: './content'

# Toggle individual generators
generators:
  robotsTxt: true
  llmsTxt: true
  llmsFullTxt: true
  rawMarkdown: true
  manifest: true
  sitemap: true
  aiIndex: true
  schema: true

# OpenAI configuration for enhanced features
openai:
  # Set via environment variable: OPENAI_API_KEY=sk-...
  apiKey: '%env(OPENAI_API_KEY)%'
  # Custom base URL for OpenAI-compatible APIs
  baseUrl: 'https://api.openai.com/v1'
  # Model to use for AI features
  model: 'gpt-4o-mini'

# Customize robots.txt
robots:
  allow:
    - '/'
  disallow:
    - '/admin'
    - '/api'
  crawlDelay: 0

# Schema.org configuration
schema:
  enabled: true
  organization:
    name: 'My Company'
    url: 'https://example.com'
  defaultType: 'WebPage'

# Open Graph configuration
og:
  enabled: true
  image: 'https://example.com/og.png'
  twitterHandle: '@mycompany'

# Widget configuration (for frontend integrations)
widget:
  enabled: true
  position: 'bottom-right'

YAML;
    }
}
```

- [ ] **Step 2: Test manually**

```bash
cd /tmp && mkdir geo-init-test && cd geo-init-test
../../bin/geo init
cat geofriendly.yaml
```

- [ ] **Step 3: Commit**

```bash
git add src/CLI/Command/InitCommand.php
git commit -m "feat: implement init command to create geofriendly.yaml"
```

---

### Task 14: Implement CheckCommand

**Files:**
- Create: `src/CLI/Command/CheckCommand.php`
- Create: `src/Audit/FilePresenceAuditor.php`
- Create: `src/Audit/GeoScoreCalculator.php`
- Test: `tests/Unit/Audit/FilePresenceAuditorTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php
declare(strict_types=1);

namespace GeoFriendly\Tests\Audit;

use GeoFriendly\Audit\FilePresenceAuditor;
use PHPUnit\Framework\TestCase;

class FilePresenceAuditorTest extends TestCase
{
    public function testAuditWithAllFilesPresent(): void
    {
        $tempDir = sys_get_temp_dir() . '/geo-audit-test-' . uniqid();
        mkdir($tempDir, 0777, true);

        $expectedFiles = ['robots.txt', 'llms.txt', 'llms-full.txt', 'sitemap.xml', 'docs.json', 'ai-index.json', 'schema.json'];
        foreach ($expectedFiles as $file) {
            touch($tempDir . '/' . $file);
        }

        $auditor = new FilePresenceAuditor();
        $result = $auditor->audit($tempDir);

        $this->assertEquals(100, $result['score']);
        $this->assertCount(0, $result['missing']);

        system("rm -rf " . escapeshellarg($tempDir));
    }

    public function testAuditWithMissingFiles(): void
    {
        $tempDir = sys_get_temp_dir() . '/geo-audit-test-' . uniqid();
        mkdir($tempDir, 0777, true);

        touch($tempDir . '/robots.txt');
        touch($tempDir . '/llms.txt');

        $auditor = new FilePresenceAuditor();
        $result = $auditor->audit($tempDir);

        $this->assertLessThan(100, $result['score']);
        $this->assertGreaterThan(0, count($result['missing']));

        system("rm -rf " . escapeshellarg($tempDir));
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

```bash
./vendor/bin/phpunit tests/Unit/Audit/FilePresenceAuditorTest.php
```
Expected: Class not found

- [ ] **Step 3: Implement FilePresenceAuditor**

```php
<?php
declare(strict_types=1);

namespace GeoFriendly\Audit;

class FilePresenceAuditor implements AuditorInterface
{
    private const REQUIRED_FILES = [
        'robots.txt' => 15,
        'llms.txt' => 20,
        'llms-full.txt' => 15,
        'sitemap.xml' => 15,
        'docs.json' => 15,
        'ai-index.json' => 10,
        'schema.json' => 10,
    ];

    public function audit(string $outputDir): array
    {
        $missing = [];
        $present = [];

        foreach (self::REQUIRED_FILES as $file => $weight) {
            $filepath = $outputDir . '/' . $file;
            if (file_exists($filepath)) {
                $present[] = $file;
            } else {
                $missing[] = $file;
            }
        }

        $score = $this->calculateScore($present);

        return [
            'score' => $score,
            'present' => $present,
            'missing' => $missing,
            'totalFiles' => count(self::REQUIRED_FILES),
        ];
    }

    private function calculateScore(array $present): int
    {
        $totalWeight = array_sum(self::REQUIRED_FILES);
        $earnedWeight = 0;

        foreach ($present as $file) {
            $earnedWeight += self::REQUIRED_FILES[$file];
        }

        return (int) round(($earnedWeight / $totalWeight) * 100);
    }
}
```

- [ ] **Step 4: Implement AuditorInterface**

```php
<?php
declare(strict_types=1);

namespace GeoFriendly\Audit;

interface AuditorInterface
{
    /**
     * Audit and return results.
     *
     * @param string $outputDir
     * @return array{score: int, present: array<string>, missing: array<string>}
     */
    public function audit(string $outputDir): array;
}
```

- [ ] **Step 5: Implement GeoScoreCalculator**

```php
<?php
declare(strict_types=1);

namespace GeoFriendly\Audit;

class GeoScoreCalculator
{
    public function calculate(array $auditResults): array
    {
        $totalScore = 0;
        $maxScore = 0;
        $details = [];

        foreach ($auditResults as $auditorName => $result) {
            $weight = $this->getAuditorWeight($auditorName);
            $maxScore += $weight;
            $totalScore += ($result['score'] * $weight / 100);

            $details[$auditorName] = [
                'score' => $result['score'],
                'weight' => $weight,
                'weightedScore' => $result['score'] * $weight / 100,
            ];
        }

        $finalScore = $maxScore > 0 ? (int) round(($totalScore / $maxScore) * 100) : 0;

        return [
            'finalScore' => $finalScore,
            'details' => $details,
        ];
    }

    private function getAuditorWeight(string $name): int
    {
        $weights = [
            'filePresence' => 40,
            'formatValidation' => 25,
            'contentQuality' => 20,
            'socialMedia' => 15,
        ];

        return $weights[$name] ?? 10;
    }
}
```

- [ ] **Step 6: Run tests to verify they pass**

```bash
./vendor/bin/phpunit tests/Unit/Audit/FilePresenceAuditorTest.php
```
Expected: PASS

- [ ] **Step 7: Implement CheckCommand**

```php
<?php
declare(strict_types=1);

namespace GeoFriendly\CLI\Command;

use GeoFriendly\Audit\FilePresenceAuditor;
use GeoFriendly\Audit\GeoScoreCalculator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'check', description: 'Validate GEO setup and calculate readiness score')]
class CheckCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addOption('out', null, InputOption::VALUE_OPTIONAL, 'Output directory', './public')
            ->addOption('json', null, InputOption::VALUE_NONE, 'Output as JSON');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $outputDir = $input->getOption('out');

        $auditors = [
            'filePresence' => new FilePresenceAuditor(),
        ];

        $results = [];
        foreach ($auditors as $name => $auditor) {
            $results[$name] = $auditor->audit($outputDir);
        }

        $calculator = new GeoScoreCalculator();
        $scoreData = $calculator->calculate($results);

        if ($input->getOption('json')) {
            $io->writeln(json_encode([
                'score' => $scoreData['finalScore'],
                'results' => $results,
            ], JSON_PRETTY_PRINT));
            return Command::SUCCESS;
        }

        $this->displayReport($io, $scoreData, $results);

        return Command::SUCCESS;
    }

    private function displayReport(SymfonyStyle $io, array $scoreData, array $results): void
    {
        $io->title('GEO Readiness Check');

        // Overall score
        $score = $scoreData['finalScore'];
        $color = $score >= 80 ? 'green' : ($score >= 50 ? 'yellow' : 'red');

        $io->text("GEO Readiness Score: <{$color}>{$score}/100</{$color}>");
        $io->newLine();

        // File presence
        $result = $results['filePresence'];
        $io->section('File Presence');

        if (!empty($result['present'])) {
            $io->text("<info>✓ Present files:</info>");
            foreach ($result['present'] as $file) {
                $io->text("  - {$file}");
            }
        }

        if (!empty($result['missing'])) {
            $io->text("<error>✗ Missing files:</error>");
            foreach ($result['missing'] as $file) {
                $io->text("  - {$file}");
            }
        }

        $io->newLine();

        // Recommendations
        if ($score < 100) {
            $io->section('Recommendations');
            $io->text("Run 'geo generate' to create missing GEO files.");
            $io->newLine();
        }
    }
}
```

- [ ] **Step 8: Test manually**

```bash
./bin/geo check --out=/tmp/geo-test
./bin/geo check --json --out=/tmp/geo-test
```

- [ ] **Step 9: Commit**

```bash
git add src/CLI/Command/CheckCommand.php src/Audit/ tests/Unit/Audit/
git commit -m "feat: implement check command with GEO readiness scoring"
```

---

### Task 14.1: Implement FormatValidator

**Files:**
- Create: `src/Audit/FormatValidator.php`
- Test: `tests/Unit/Audit/FormatValidatorTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php
declare(strict_types=1);

namespace GeoFriendly\Tests\Audit;

use GeoFriendly\Audit\FormatValidator;
use PHPUnit\Framework\TestCase;

class FormatValidatorTest extends TestCase
{
    public function testValidateValidLlmsTxt(): void
    {
        $tempDir = sys_get_temp_dir() . '/geo-validate-' . uniqid();
        mkdir($tempDir, 0777, true);

        file_put_contents($tempDir . '/llms.txt', "# Test Site\n\n- [Page 1](https://example.com/page1): Description");

        $validator = new FormatValidator();
        $result = $validator->audit($tempDir);

        $this->assertGreaterThan(0, $result['score']);

        system("rm -rf " . escapeshellarg($tempDir));
    }

    public function testValidateInvalidJson(): void
    {
        $tempDir = sys_get_temp_dir() . '/geo-validate-' . uniqid();
        mkdir($tempDir, 0777, true);

        file_put_contents($tempDir . '/docs.json', "{invalid json}");

        $validator = new FormatValidator();
        $result = $validator->audit($tempDir);

        $this->assertLessThan(100, $result['score']);
        $this->assertArrayHasKey('errors', $result);

        system("rm -rf " . escapeshellarg($tempDir));
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

```bash
./vendor/bin/phpunit tests/Unit/Audit/FormatValidatorTest.php
```
Expected: Class not found

- [ ] **Step 3: Implement FormatValidator**

```php
<?php
declare(strict_types=1);

namespace GeoFriendly\Audit;

class FormatValidator implements AuditorInterface
{
    private const FILE_VALIDATORS = [
        'llms.txt' => 'validateLlmsTxt',
        'llms-full.txt' => 'validateLlmsFullTxt',
        'docs.json' => 'validateJson',
        'ai-index.json' => 'validateJson',
        'schema.json' => 'validateSchemaJson',
        'sitemap.xml' => 'validateXml',
    ];

    public function audit(string $outputDir): array
    {
        $errors = [];
        $validated = [];

        foreach (self::FILE_VALIDATORS as $file => $validator) {
            $filepath = $outputDir . '/' . $file;
            if (!file_exists($filepath)) {
                continue;
            }

            $result = $this->{$validator}($filepath);
            if ($result === true) {
                $validated[] = $file;
            } else {
                $errors[$file] = $result;
            }
        }

        $score = $this->calculateScore($validated, $errors);

        return [
            'score' => $score,
            'validated' => $validated,
            'errors' => $errors,
        ];
    }

    private function validateLlmsTxt(string $path): bool|string
    {
        $content = file_get_contents($path);
        if (!str_starts_with($content, '#')) {
            return 'Missing title header (should start with #)';
        }
        return true;
    }

    private function validateLlmsFullTxt(string $path): bool|string
    {
        $content = file_get_contents($path);
        if (!str_contains($content, '---')) {
            return 'Missing section separators (---)';
        }
        return true;
    }

    private function validateJson(string $path): bool|string
    {
        $content = file_get_contents($path);
        json_decode($content);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return 'Invalid JSON: ' . json_last_error_msg();
        }
        return true;
    }

    private function validateSchemaJson(string $path): bool|string
    {
        $content = file_get_contents($path);
        $data = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return 'Invalid JSON: ' . json_last_error_msg();
        }
        if (!isset($data['@context']) || $data['@context'] !== 'https://schema.org') {
            return 'Missing @context for Schema.org';
        }
        return true;
    }

    private function validateXml(string $path): bool|string
    {
        $content = file_get_contents($path);
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($content);
        if ($xml === false) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            return 'Invalid XML: ' . $errors[0]->message ?? 'Unknown error';
        }
        return true;
    }

    private function calculateScore(array $validated, array $errors): int
    {
        $total = count($validated) + count($errors);
        if ($total === 0) {
            return 100; // No files to validate
        }
        return (int) round((count($validated) / $total) * 100);
    }
}
```

- [ ] **Step 4: Run tests to verify they pass**

```bash
./vendor/bin/phpunit tests/Unit/Audit/FormatValidatorTest.php
```
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add src/Audit/FormatValidator.php tests/Unit/Audit/FormatValidatorTest.php
git commit -m "feat: implement format validator auditor"
```

---

### Task 14.2: Implement ContentQualityAuditor

**Files:**
- Create: `src/Audit/ContentQualityAuditor.php`
- Test: `tests/Unit/Audit/ContentQualityAuditorTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php
declare(strict_types=1);

namespace GeoFriendly\Tests\Audit;

use GeoFriendly\Audit\ContentQualityAuditor;
use PHPUnit\Framework\TestCase;

class ContentQualityAuditorTest extends TestCase
{
    public function testScoreHighQualityContent(): void
    {
        $tempDir = sys_get_temp_dir() . '/geo-quality-' . uniqid();
        mkdir($tempDir . '/content', 0777, true);

        file_put_contents($tempDir . '/content/page1.md', "# Page Title\n\nThis is a comprehensive page with detailed content.");
        file_put_contents($tempDir . '/llms.txt', "# Test Site\n\n- [Page 1](https://example.com/page1): Description");

        $auditor = new ContentQualityAuditor();
        $result = $auditor->audit($tempDir);

        $this->assertGreaterThan(50, $result['score']);

        system("rm -rf " . escapeshellarg($tempDir));
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

```bash
./vendor/bin/phpunit tests/Unit/Audit/ContentQualityAuditorTest.php
```
Expected: Class not found

- [ ] **Step 3: Implement ContentQualityAuditor**

```php
<?php
declare(strict_types=1);

namespace GeoFriendly\Audit;

use GeoFriendly\Utils\FileHelper;

class ContentQualityAuditor implements AuditorInterface
{
    public function audit(string $outputDir): array
    {
        $factors = [
            'hasContent' => $this->hasMarkdownContent($outputDir),
            'hasDescriptions' => $this->hasDescriptions($outputDir),
            'hasSchema' => file_exists($outputDir . '/schema.json'),
            'contentDepth' => $this->measureContentDepth($outputDir),
        ];

        $score = $this->calculateScore($factors);

        return [
            'score' => $score,
            'factors' => $factors,
        ];
    }

    private function hasMarkdownContent(string $outputDir): bool
    {
        $files = FileHelper::collectMarkdownFiles($outputDir . '/content');
        return count($files) > 0;
    }

    private function hasDescriptions(string $outputDir): bool
    {
        $llmsContent = file_get_contents($outputDir . '/llms.txt');
        return preg_match('/\[.*\]\(.*\): /', $llmsContent) > 0;
    }

    private function measureContentDepth(string $outputDir): int
    {
        $files = FileHelper::collectMarkdownFiles($outputDir . '/content');
        $totalLength = 0;

        foreach ($files as $file) {
            $totalLength += strlen($file['content']);
        }

        if ($totalLength > 50000) return 3; // High
        if ($totalLength > 10000) return 2; // Medium
        if ($totalLength > 1000) return 1;  // Low
        return 0;
    }

    private function calculateScore(array $factors): int
    {
        $score = 0;

        if ($factors['hasContent']) $score += 30;
        if ($factors['hasDescriptions']) $score += 25;
        if ($factors['hasSchema']) $score += 20;
        $score += $factors['contentDepth'] * 8; // Up to 24 points

        return min(100, $score);
    }
}
```

- [ ] **Step 4: Run tests to verify they pass**

```bash
./vendor/bin/phpunit tests/Unit/Audit/ContentQualityAuditorTest.php
```
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add src/Audit/ContentQualityAuditor.php tests/Unit/Audit/ContentQualityAuditorTest.php
git commit -m "feat: implement content quality auditor"
```

---

### Task 14.3: Implement SocialMediaAuditor

**Files:**
- Create: `src/Audit/SocialMediaAuditor.php`
- Test: `tests/Unit/Audit/SocialMediaAuditorTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php
declare(strict_types=1);

namespace GeoFriendly\Tests\Audit;

use GeoFriendly\Audit\SocialMediaAuditor;
use PHPUnit\Framework\TestCase;

class SocialMediaAuditorTest extends TestCase
{
    public function testScoreWithAllMeta(): void
    {
        $tempDir = sys_get_temp_dir() . '/geo-social-' . uniqid();
        mkdir($tempDir, 0777, true);

        // Create a mock index.html with Open Graph tags
        $html = '<html><head><meta property="og:title" content="Test"><meta property="og:image" content="image.png"></head></html>';
        file_put_contents($tempDir . '/index.html', $html);

        $auditor = new SocialMediaAuditor();
        $result = $auditor->audit($tempDir);

        $this->assertGreaterThan(0, $result['score']);

        system("rm -rf " . escapeshellarg($tempDir));
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

```bash
./vendor/bin/phpunit tests/Unit/Audit/SocialMediaAuditorTest.php
```
Expected: Class not found

- [ ] **Step 3: Implement SocialMediaAuditor**

```php
<?php
declare(strict_types=1);

namespace GeoFriendly\Audit;

class SocialMediaAuditor implements AuditorInterface
{
    public function audit(string $outputDir): array
    {
        $factors = [
            'hasOpenGraph' => false,
            'hasTwitterCard' => false,
            'hasFavicon' => false,
            'hasOgImage' => false,
        ];

        // Check index.html if it exists
        $indexPath = $outputDir . '/index.html';
        if (file_exists($indexPath)) {
            $html = file_get_contents($indexPath);
            $factors['hasOpenGraph'] = str_contains($html, 'og:') || str_contains($html, 'og:title');
            $factors['hasTwitterCard'] = str_contains($html, 'twitter:card');
            $factors['hasOgImage'] = str_contains($html, 'og:image');
        }

        // Check for favicon files
        $faviconFiles = ['favicon.ico', 'favicon.png', 'apple-touch-icon.png'];
        foreach ($faviconFiles as $file) {
            if (file_exists($outputDir . '/' . $file)) {
                $factors['hasFavicon'] = true;
                break;
            }
        }

        $score = $this->calculateScore($factors);

        return [
            'score' => $score,
            'factors' => $factors,
        ];
    }

    private function calculateScore(array $factors): int
    {
        $score = 0;

        if ($factors['hasOpenGraph']) $score += 40;
        if ($factors['hasTwitterCard']) $score += 30;
        if ($factors['hasFavicon']) $score += 15;
        if ($factors['hasOgImage']) $score += 15;

        return $score;
    }
}
```

- [ ] **Step 4: Run tests to verify they pass**

```bash
./vendor/bin/phpunit tests/Unit/Audit/SocialMediaAuditorTest.php
```
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add src/Audit/SocialMediaAuditor.php tests/Unit/Audit/SocialMediaAuditorTest.php
git commit -m "feat: implement social media auditor"
```

---

### Task 14.4: Update CheckCommand to use all auditors

**Files:**
- Modify: `src/CLI/Command/CheckCommand.php`

- [ ] **Step 1: Update CheckCommand to use all auditors**

Find the auditors array in CheckCommand and update it:

```php
$auditors = [
    'filePresence' => new FilePresenceAuditor(),
    'formatValidation' => new FormatValidator(),
    'contentQuality' => new ContentQualityAuditor(),
    'socialMedia' => new SocialMediaAuditor(),
];
```

- [ ] **Step 2: Commit**

```bash
git add src/CLI/Command/CheckCommand.php
git commit -m "feat: update check command to use all auditors"
```

---

### Task 15: Implement ReportCommand

**Files:**
- Create: `src/CLI/Command/ReportCommand.php`

- [ ] **Step 1: Implement ReportCommand**

```php
<?php
declare(strict_types=1);

namespace GeoFriendly\CLI\Command;

use GeoFriendly\Audit\FilePresenceAuditor;
use GeoFriendly\Audit\GeoScoreCalculator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'report', description: 'Generate full GEO report with citability scores')]
class ReportCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addOption('out', null, InputOption::VALUE_OPTIONAL, 'Output directory', './public')
            ->addOption('json', null, InputOption::VALUE_NONE, 'Output as JSON');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $outputDir = $input->getOption('out');

        $auditors = [
            'filePresence' => new FilePresenceAuditor(),
        ];

        $results = [];
        foreach ($auditors as $name => $auditor) {
            $results[$name] = $auditor->audit($outputDir);
        }

        $calculator = new GeoScoreCalculator();
        $scoreData = $calculator->calculate($results);

        if ($input->getOption('json')) {
            $io->writeln(json_encode([
                'score' => $scoreData['finalScore'],
                'results' => $results,
                'citability' => $this->calculateCitability($results),
            ], JSON_PRETTY_PRINT));
            return Command::SUCCESS;
        }

        $this->displayReport($io, $scoreData, $results);

        return Command::SUCCESS;
    }

    private function calculateCitability(array $results): array
    {
        $fileResult = $results['filePresence'];

        return [
            'score' => $fileResult['score'],
            'factors' => [
                'hasLlmsTxt' => in_array('llms.txt', $fileResult['present']),
                'hasLlmsFull' => in_array('llms-full.txt', $fileResult['present']),
                'hasDocsJson' => in_array('docs.json', $fileResult['present']),
                'hasAiIndex' => in_array('ai-index.json', $fileResult['present']),
            ],
        ];
    }

    private function displayReport(SymfonyStyle $io, array $scoreData, array $results): void
    {
        $io->title('GEO Report');

        $score = $scoreData['finalScore'];
        $io->text("GEO Readiness Score: <info>{$score}/100</info>");
        $io->newLine();

        // Citability
        $io->section('AI Citability');
        $citability = $this->calculateCitability($results);

        foreach ($citability['factors'] as $factor => $value) {
            $status = $value ? '<info>✓</info>' : '<error>✗</error>';
            $io->text("{$status} " . ucfirst(str_replace('has', '', $factor)));
        }

        $io->newLine();

        // Platform Hints
        $io->section('Platform Optimization');
        $io->text("<comment>ChatGPT:</comment> Ensure llms.txt is present and well-structured.");
        $io->text("<comment>Claude:</comment> Include detailed descriptions in docs.json.");
        $io->text("<comment>Perplexity:</comment> Use ai-index.json for better retrieval.");
        $io->text("<comment>Google AI:</comment> Schema.org markup improves visibility.");
    }
}
```

- [ ] **Step 2: Test manually**

```bash
./bin/geo report --out=/tmp/geo-test
./bin/geo report --json --out=/tmp/geo-test
```

- [ ] **Step 3: Commit**

```bash
git add src/CLI/Command/ReportCommand.php
git commit -m "feat: implement report command with citability scores"
```

---

## Phase 4: Platform Integrations

### Task 16: Create WordPress Integration

**Files:**
- Create: `examples/wordpress-plugin/geo-friendly.php`
- Create: `examples/wordpress-plugin/readme.txt`

- [ ] **Step 1: Create WordPress plugin main file**

```php
<?php
/**
 * Plugin Name: geo-friendly
 * Plugin URI: https://github.com/geo-friendly/geo-friendly
 * Description: Generative Engine Optimization - Make your WordPress site discoverable by AI answer engines
 * Version: 0.1.0
 * Author: geo-friendly
 * License: MIT
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

// Load composer autoloader
require_once __DIR__ . '/../../vendor/autoload.php';

use GeoFriendly\GeoFriendly;
use GeoFriendly\Config\GeofriendlyConfig;

class GeoFriendlyWordPress
{
    private static ?GeoFriendlyWordPress $instance = null;
    private GeoFriendly $geo;

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->geo = new GeoFriendly($this->getConfig());

        // Register hooks
        add_action('init', [$this, 'generateGEOFiles']);
        add_action('admin_menu', [$this, 'addAdminMenu']);
        add_action('admin_post_geo_friendly_save_settings', [$this, 'saveSettings']);
    }

    private function getConfig(): array
    {
        return [
            'title' => get_bloginfo('name'),
            'url' => get_site_url(),
            'description' => get_bloginfo('description'),
            'outDir' => ABSPATH,
            'contentDir' => WP_CONTENT_DIR . '/uploads/geo-friendly',
        ];
    }

    public function generateGEOFiles(): void
    {
        $this->geo->generate();
    }

    public function addAdminMenu(): void
    {
        add_options_page(
            'geo-friendly',
            'geo-friendly',
            'manage_options',
            'geo-friendly',
            [$this, 'renderAdminPage']
        );
    }

    public function renderAdminPage(): void
    {
        ?>
        <div class="wrap">
            <h1>geo-friendly Settings</h1>
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <input type="hidden" name="action" value="geo_friendly_save_settings">
                <?php wp_nonce_field('geo_friendly_save_settings', 'geo_friendly_nonce'); ?>

                <table class="form-table">
                    <tr>
                        <th scope="row">Enable GEO Files</th>
                        <td>
                            <input type="checkbox" name="geo_friendly_enabled" value="1" <?php checked(get_option('geo_friendly_enabled', '1'), '1'); ?>>
                            <label>Generate robots.txt, llms.txt, and other GEO files</label>
                        </td>
                    </tr>
                </table>

                <?php submit_button('Save Settings'); ?>
            </form>
        </div>
        <?php
    }

    public function saveSettings(): void
    {
        if (!isset($_POST['geo_friendly_nonce']) || !wp_verify_nonce($_POST['geo_friendly_nonce'], 'geo_friendly_save_settings')) {
            wp_die('Invalid nonce');
        }

        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        $enabled = isset($_POST['geo_friendly_enabled']) ? '1' : '0';
        update_option('geo_friendly_enabled', $enabled);

        wp_redirect(admin_url('options-general.php?page=geo-friendly&status=saved'));
        exit;
    }
}

// Initialize plugin
GeoFriendlyWordPress::getInstance();
```

- [ ] **Step 2: Create readme.txt**

```txt
=== geo-friendly ===
Contributors: geo-friendly
Tags: seo, ai, llm, chatgpt, claude, geo
Requires at least: 6.0
Tested up to: 6.4
Stable tag: 0.1.0
Requires PHP: 8.2
License: MIT

Generative Engine Optimization for WordPress. Make your site discoverable by AI answer engines.

== Installation ==

1. Install the plugin via composer or upload the files
2. Activate the plugin
3. Configure settings in Settings > geo-friendly

== Changelog ==

= 0.1.0 =
* Initial release
```

- [ ] **Step 3: Commit**

```bash
git add examples/wordpress-plugin/
git commit -m "feat: add WordPress plugin example"
```

---

### Task 17: Create Shopify Integration

**Files:**
- Create: `examples/shopify-app/geo-friendly-section.liquid`
- Create: `examples/shopify-app/README.md`

- [ ] **Step 1: Create Shopify Liquid template**

```liquid
{% comment %}
  geo-friendly for Shopify
  Generates AI-friendly files for your Shopify store
{% endcomment %}

{% layout none %}
{% capture output %}
# {{ shop.name }}

> {{ shop.description | default: 'Welcome to ' | append: shop.name }}

## About

This file provides a structured overview of the store content, optimized for consumption by Large Language Models (LLMs) and AI assistants.

## Products

{% for product in collections.all.products limit: 100 %}
- [{{ product.title }}]({{ shop.url }}{{ product.url }})
  {% if product.description %}
  {{ product.description | strip_html | truncate: 100 }}
  {% endif %}
{% endfor %}

## Collections

{% for collection in collections %}
{% unless collection.handle == 'all' or collection.handle == 'frontpage' %}
- [{{ collection.title }}]({{ shop.url }}{{ collection.url }})
{% endunless %}
{% endfor %}

## Quick Links

- Full Documentation: {{ shop.url }}/llms-full.txt
- Documentation Manifest: {{ shop.url }}/docs.json
- AI-Optimized Index: {{ shop.url }}/ai-index.json
- Sitemap: {{ shop.url }}/sitemap.xml

---
Generated by geo-friendly - Generative Engine Optimization for PHP
{% endcapture %}

{{ output }}
```

- [ ] **Step 2: Create Shopify README**

```markdown
# geo-friendly for Shopify

Integration of geo-friendly with Shopify stores.

## Installation

1. Create a new page template in your theme
2. Copy the `geo-friendly-section.liquid` to your theme
3. Create a new page with the slug `llms.txt` using this template

## Theme App Extension

For a proper integration, create a Shopify Theme App extension:

1. Create a new theme app extension
2. Add the liquid template files
3. Merchants can add the section to their theme

## API Integration

For dynamic content, use the Shopify API to fetch products and collections:

```php
$shopify = new ShopifyClient($shop, $accessToken);
$products = $shopify->products->getAll();
```
```

- [ ] **Step 3: Commit**

```bash
git add examples/shopify-app/
git commit -m "feat: add Shopify integration example"
```

---

### Task 18: Create Laravel Integration

**Files:**
- Create: `examples/laravel/GeoFriendlyServiceProvider.php`

- [ ] **Step 1: Create Laravel Service Provider**

```php
<?php

namespace GeoFriendly\Laravel;

use GeoFriendly\GeoFriendly;
use Illuminate\Support\ServiceProvider;

class GeoFriendlyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/geofriendly.php',
            'geofriendly'
        );

        $this->app->singleton(GeoFriendly::class, function () {
            return new GeoFriendly(config('geofriendly'));
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/config/geofriendly.php' => config_path('geofriendly.php'),
        ], 'geofriendly-config');

        $this->commands([
            \GeoFriendly\Laravel\Console\GenerateCommand::class,
            \GeoFriendly\Laravel\Console\CheckCommand::class,
        ]);
    }
}
```

- [ ] **Step 2: Create config file**

```php
<?php

return [
    'title' => env('APP_NAME', 'Laravel'),
    'url' => env('APP_URL'),
    'description' => env('GEO_DESCRIPTION', ''),
    'outDir' => public_path(),
    'contentDir' => resource_path('content'),
];
```

- [ ] **Step 3: Commit**

```bash
git add examples/laravel/
git commit -m "feat: add Laravel service provider example"
```

---

### Task 19: Create Symfony Integration

**Files:**
- Create: `examples/symfony/GeoFriendlyBundle.php`

- [ ] **Step 1: Create Symfony Bundle**

```php
<?php

namespace GeoFriendly\Symfony;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class GeoFriendlyBundle extends Bundle
{
}
```

- [ ] **Step 2: Create extension**

```php
<?php

namespace GeoFriendly\Symfony\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class GeoFriendlyExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('geo_friendly.title', $config['title']);
        $container->setParameter('geo_friendly.url', $config['url']);
        $container->setParameter('geo_friendly.out_dir', $config['out_dir']);
    }
}
```

- [ ] **Step 3: Commit**

```bash
git add examples/symfony/
git commit -m "feat: add Symfony bundle example"
```

---

## Phase 5: Examples & Documentation

### Task 20: Create CLI Usage Examples

**Files:**
- Create: `examples/cli-single-url.php`

- [ ] **Step 1: Create CLI example**

```php
#!/usr/bin/env php
<?php
/**
 * Example: Generate GEO files for a single URL
 *
 * Usage: php examples/cli-single-url.php https://example.com "My Site"
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use GeoFriendly\GeoFriendly;

$url = $argv[1] ?? 'https://example.com';
$title = $argv[2] ?? 'My Site';

echo "Generating GEO files for: {$title} ({\$url})\n";

$geo = new GeoFriendly([
    'title' => $title,
    'url' => $url,
    'description' => 'A site optimized for AI discovery',
    'outDir' => __DIR__ . '/output',
    'contentDir' => __DIR__ . '/content',
]);

$result = $geo->generate();

echo "\nGenerated " . count($result['generated']) . " files:\n";
foreach ($result['generated'] as $file) {
    echo "  - {$file}\n";
}

if (!empty($result['errors'])) {
    echo "\nErrors:\n";
    foreach ($result['errors'] as $error) {
        echo "  - {$error}\n";
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add examples/cli-single-url.php
git commit -m "feat: add CLI single URL example"
```

---

### Task 21: Update Main README

**Files:**
- Modify: `README.md`

- [ ] **Step 1: Update README with comprehensive documentation**

```markdown
# geo-friendly

[![Latest Version](https://img.shields.io/packagist/v/geo-friendly/geo-friendly.svg)](https://packagist.org/packages/geo-friendly/geo-friendly)
[![Total Downloads](https://img.shields.io/packagist/dt/geo-friendly/geo-friendly.svg)](https://packagist.org/packages/geo-friendly/geo-friendly)
[![License](https://img.shields.io/packagist/l/geo-friendly/geo-friendly.svg)](https://github.com/geo-friendly/geo-friendly)

Generative Engine Optimization for PHP. Make your site discoverable by ChatGPT, Claude, Perplexity, and every AI answer engine.

## What is GEO?

**Generative Engine Optimization (GEO)** is the practice of optimizing your website for AI answer engines. Just as SEO optimizes for search engines, GEO optimizes for AI-powered responses.

### Why GEO Matters

- **58% of searches** end without a click — AI gives the answer directly
- **40% of Gen Z** prefer AI assistants over traditional search engines
- **97% of sites** have no AI-optimized content

## Installation

```bash
composer require geo-friendly/geo-friendly
```

## Quick Start

### Library Usage

```php
use GeoFriendly\GeoFriendly;

$geo = new GeoFriendly([
    'title' => 'My Site',
    'url' => 'https://example.com',
    'description' => 'A site optimized for AI discovery',
]);

$geo->generate('./public');
```

### CLI Usage

```bash
# Generate GEO files
./vendor/bin/geo generate --url=https://example.com --title="My Site"

# Create config file
./vendor/bin/geo init

# Check GEO readiness
./vendor/bin/geo check

# Full report
./vendor/bin/geo report
```

## Configuration

Create a `geofriendly.yaml` file:

```yaml
title: 'My Site'
url: 'https://example.com'
description: 'A site optimized for AI discovery'

outDir: './public'
contentDir: './content'

generators:
  robotsTxt: true
  llmsTxt: true
  llmsFullTxt: true
  sitemap: true
  docsJson: true
  aiIndex: true
  schema: true
```

## Generated Files

After running geo-friendly, your output directory contains:

- `robots.txt` - AI-crawler directives (allows ChatGPT, Claude, Perplexity, etc.)
- `llms.txt` - Short LLM-readable summary
- `llms-full.txt` - Full content for LLMs
- `sitemap.xml` - Standard sitemap
- `docs.json` - Documentation manifest
- `ai-index.json` - AI content index
- `schema.json` - Schema.org structured data

## Platform Integrations

### WordPress

See `examples/wordpress-plugin/` for WordPress plugin integration.

### Laravel

```php
// config/app.php
GeoFriendly\Laravel\GeoFriendlyServiceProvider::class,

// Publish config
php artisan vendor:publish --provider="GeoFriendly\Laravel\GeoFriendlyServiceProvider"

// Generate GEO files
php artisan geo:generate
```

### Symfony

```bash
composer require geo-friendly/geo-friendly
# Enable in config/bundles.php
```

### Shopify

See `examples/shopify-app/` for Shopify integration.

## AI-Enhanced Features

For AI-powered content enhancement, install the OpenAI client:

```bash
composer require openai-php/client
```

Then configure in `geofriendly.yaml`:

```yaml
openai:
  apiKey: '%env(OPENAI_API_KEY)%'
  baseUrl: 'https://api.openai.com/v1'
  model: 'gpt-4o-mini'
```

## License

MIT License - see LICENSE file for details.
```

- [ ] **Step 2: Commit**

```bash
git add README.md
git commit -m "docs: update README with comprehensive documentation"
```

---

### Task 21.1: Create Chinese README (README.zh-CN.md)

**Files:**
- Create: `README.zh-CN.md`

- [ ] **Step 1: Create Chinese README**

```markdown
# geo-friendly

[![Latest Version](https://img.shields.io/packagist/v/geo-friendly/geo-friendly.svg)](https://packagist.org/packages/geo-friendly/geo-friendly)
[![Total Downloads](https://img.shields.io/packagist/dt/geo-friendly/geo-friendly.svg)](https://packagist.org/packages/geo-friendly/geo-friendly)
[![License](https://img.shields.io/packagist/l/geo-friendly/geo-friendly.svg)](https://github.com/geo-friendly/geo-friendly)
[中文文档](README.zh-CN.md)

PHP 生成式引擎优化（GEO）工具。让您的网站可被 ChatGPT、Claude、Perplexity 等 AI 答案引擎发现。

## 什么是 GEO？

**生成式引擎优化（GEO）** 是针对 AI 答案引擎优化网站的实践。就像 SEO 为搜索引擎优化一样，GEO 为 AI 驱动的响应进行优化。

### 为什么 GEO 重要？

- **58% 的搜索** 无点击结束 — AI 直接给出答案
- **40% 的 Z 世代**更喜欢 AI 助手而非传统搜索引擎
- **97% 的网站**没有 AI 优化的内容

## 安装

```bash
composer require geo-friendly/geo-friendly
```

## 快速开始

### 库用法

```php
use GeoFriendly\GeoFriendly;

$geo = new GeoFriendly([
    'title' => '我的网站',
    'url' => 'https://example.com',
    'description' => '为 AI 发现优化的网站',
]);

$geo->generate('./public');
```

### CLI 用法

```bash
# 生成 GEO 文件
./vendor/bin/geo generate --url=https://example.com --title="我的网站"

# 创建配置文件
./vendor/bin/geo init

# 检查 GEO 准备情况
./vendor/bin/geo check

# 完整报告
./vendor/bin/geo report
```

## 配置

创建 `geofriendly.yaml` 文件：

```yaml
title: '我的网站'
url: 'https://example.com'
description: '为 AI 发现优化的网站'

outDir: './public'
contentDir: './content'

generators:
  robotsTxt: true
  llmsTxt: true
  llmsFullTxt: true
  sitemap: true
  docsJson: true
  aiIndex: true
  schema: true
```

## 生成的文件

运行 geo-friendly 后，输出目录包含：

- `robots.txt` - AI 爬虫指令（允许 ChatGPT、Claude、Perplexity 等）
- `llms.txt` - 简短的 LLM 可读摘要
- `llms-full.txt` - LLM 的完整内容
- `sitemap.xml` - 标准站点地图
- `docs.json` - 文档清单
- `ai-index.json` - AI 内容索引
- `schema.json` - Schema.org 结构化数据

## 平台集成

### WordPress

参见 `examples/wordpress-plugin/` 获取 WordPress 插件集成。

### Laravel

```php
// config/app.php
GeoFriendly\Laravel\GeoFriendlyServiceProvider::class;

// 发布配置
php artisan vendor:publish --provider="GeoFriendly\Laravel\GeoFriendlyServiceProvider"

// 生成 GEO 文件
php artisan geo:generate
```

### Symfony

```bash
composer require geo-friendly/geo-friendly
# 在 config/bundles.php 中启用
```

### Shopify

参见 `examples/shopify-app/` 获取 Shopify 集成。

## AI 增强功能

如需 AI 驱动的内容增强，请安装 OpenAI 客户端：

```bash
composer require openai-php/client
```

然后在 `geofriendly.yaml` 中配置：

```yaml
openai:
  apiKey: '%env(OPENAI_API_KEY)%'
  baseUrl: 'https://api.openai.com/v1'
  model: 'gpt-4o-mini'
```

## 许可证

MIT License - 详见 LICENSE 文件
```

- [ ] **Step 2: Update English README with language switcher**

Add to the top of `README.md`:

```markdown
# geo-friendly

[![Latest Version](https://img.shields.io/packagist/v/geo-friendly/geo-friendly.svg)](https://packagist.org/packages/geo-friendly/geo-friendly)
[![Total Downloads](https://img.shields.io/packagist/dt/geo-friendly/geo-friendly.svg)](https://packagist.org/packages/geo-friendly/geo-friendly)
[![License](https://img.shields.io/packagist/l/geo-friendly/geo-friendly.svg)](https://github.com/geo-friendly/geo-friendly)
[中文文档](README.zh-CN.md)
```

- [ ] **Step 3: Commit**

```bash
git add README.zh-CN.md README.md
git commit -m "docs: add Chinese README with language switcher"
```

---

## Phase 6: Testing & Quality

### Task 22: Add PHPUnit Configuration

**Files:**
- Create: `phpunit.xml.dist`

- [ ] **Step 1: Create PHPUnit config**

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/10.0/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true"
         failOnWarning="true"
         failOnRisky="true"
         cacheDirectory=".phpunit.cache">
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory>tests/Feature</directory>
        </testsuite>
    </testsuites>
    <source>
        <include>
            <directory>src</directory>
        </include>
    </source>
</phpunit>
```

- [ ] **Step 2: Commit**

```bash
git add phpunit.xml.dist
git commit -m "test: add PHPUnit configuration"
```

---

### Task 23: Add PHPStan Configuration

**Files:**
- Create: `phpstan.neon`

- [ ] **Step 1: Create PHPStan config**

```neon
parameters:
    level: 8
    paths:
        - src
    tmpDir: .phpstan-cache
```

- [ ] **Step 2: Commit**

```bash
git add phpstan.neon
git commit -m "test: add PHPStan configuration"
```

---

## Final Steps

### Task 24: Tag and Release

- [ ] **Step 1: Update version in composer.json**

```bash
# Update version to 0.1.0
```

- [ ] **Step 2: Create git tag**

```bash
git tag -a v0.1.0 -m "Release v0.1.0"
git push origin v0.1.0
```

- [ ] **Step 3: Commit final release**

```bash
git add .
git commit -m "release: v0.1.0 - Initial release of geo-friendly"
```

---

## Phase 7: AI-Enhanced Features

### Task 25: Implement AiLlmsTxtGenerator

**Files:**
- Create: `src/Generator/Enhanced/AiLlmsTxtGenerator.php`
- Test: `tests/Unit/Generator/Enhanced/AiLlmsTxtGeneratorTest.php`

- [ ] **Step 1: Add OpenAI dependency to composer.json**

```json
"suggest": {
    "openai-php/client": "Required for AI-enhanced features"
}
```

- [ ] **Step 2: Write the failing test**

```php
<?php
declare(strict_types=1);

namespace GeoFriendly\Tests\Generator\Enhanced;

use GeoFriendly\Config\GeofriendlyConfig;
use GeoFriendly\Config\OpenAIConfig;
use GeoFriendly\Generator\Enhanced\AiLlmsTxtGenerator;
use PHPUnit\Framework\TestCase;

class AiLlmsTxtGeneratorTest extends TestCase
{
    public function testGenerateAiEnhancedDescriptions(): void
    {
        $openaiConfig = new OpenAIConfig([
            'apiKey' => 'test-key',
            'baseUrl' => 'https://api.openai.com/v1',
            'model' => 'gpt-4o-mini',
        ]);

        $config = new GeofriendlyConfig([
            'title' => 'Test Site',
            'url' => 'https://test.com',
            'openai' => $openaiConfig,
        ]);

        $generator = $this->createMockGenerator();
        $content = $generator->generate($config);

        $this->assertStringContainsString('# Test Site', $content);
    }

    private function createMockGenerator(): AiLlmsTxtGenerator
    {
        // Create a mock that doesn't actually call OpenAI
        return new class extends AiLlmsTxtGenerator {
            protected function callOpenAI(string $prompt): array
            {
                return [
                    'title' => 'AI Generated Title',
                    'description' => 'AI generated description for testing',
                ];
            }
        };
    }
}
```

- [ ] **Step 3: Implement AiLlmsTxtGenerator**

```php
<?php
declare(strict_types=1);

namespace GeoFriendly\Generator\Enhanced;

use GeoFriendly\Config\GeofriendlyConfig;
use GeoFriendly\Generator\LlmsTxtGenerator;
use GeoFriendly\Utils\FileHelper;

class AiLlmsTxtGenerator extends LlmsTxtGenerator
{
    private ?\OpenAI\Client $client = null;

    public function generate(GeofriendlyConfig $config): string
    {
        if (!$config->openai || !$config->openai->enabled) {
            // Fall back to basic generator
            return parent::generate($config);
        }

        $this->initializeClient($config);

        // Collect markdown files
        $markdownFiles = FileHelper::collectMarkdownFiles($config->contentDir);

        // Enhance descriptions with AI
        $enhancedFiles = [];
        foreach ($markdownFiles as $file) {
            $enhanced = $this->enhanceWithAI($file);
            $enhancedFiles[] = $enhanced;
        }

        // Generate llms.txt with enhanced content
        return $this->generateWithEnhancedFiles($config, $enhancedFiles);
    }

    private function initializeClient(GeofriendlyConfig $config): void
    {
        if ($this->client !== null) {
            return;
        }

        $this->client = \OpenAI::client($config->openai->apiKey, $config->openai->baseUrl);
    }

    protected function enhanceWithAI(array $file): array
    {
        $prompt = $this->buildPrompt($file);

        try {
            $response = $this->callOpenAI($prompt);

            return [
                'path' => $file['path'],
                'title' => $response['title'] ?? $file['title'],
                'description' => $response['description'] ?? $file['description'],
                'content' => $file['content'],
            ];
        } catch (\Exception $e) {
            // Fall back to original on error
            return $file;
        }
    }

    protected function buildPrompt(array $file): string
    {
        return <<<PROMPT
Generate a 9-10 word description and a 3-4 word title for the following content.

Content preview:
{$this->truncate($file['content'], 2000)}

Return the response in JSON format:
{
    "title": "3-4 word title",
    "description": "9-10 word description"
}
PROMPT;
    }

    protected function callOpenAI(string $prompt): array
    {
        $response = $this->client->chat()->create([
            'model' => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'system', 'content' => 'You are a helpful assistant that generates concise titles and descriptions for web pages.'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'response_format' => ['type' => 'json_object'],
            'temperature' => 0.3,
            'max_tokens' => 100,
        ]);

        $content = $response->choices[0]->message->content;
        return json_decode($content, true);
    }

    private function truncate(string $text, int $length): string
    {
        if (strlen($text) <= $length) {
            return $text;
        }
        return substr($text, 0, $length) . '...';
    }

    private function generateWithEnhancedFiles(GeofriendlyConfig $config, array $files): string
    {
        $lines = [];

        $lines[] = "# {$config->title}";
        $lines[] = '';

        if ($config->description) {
            $lines[] = "> {$config->description}";
            $lines[] = '';
        }

        $lines[] = '## Documentation';
        $lines[] = '';

        foreach ($files as $file) {
            $url = rtrim($config->url, '/') . '/' . str_replace(['.md', '.mdx'], '', $file['path']);
            $lines[] = "- [{$file['title']}]({$url})";
            if ($file['description']) {
                $lines[] = "  {$file['description']}";
            }
        }

        $lines[] = '';
        $lines[] = '---';
        $lines[] = 'Generated by geo-friendly with AI enhancement';

        return implode("\n", $lines) . "\n";
    }
}
```

- [ ] **Step 4: Commit**

```bash
git add src/Generator/Enhanced/AiLlmsTxtGenerator.php tests/Unit/Generator/Enhanced/AiLlmsTxtGeneratorTest.php
git commit -m "feat: implement AI-enhanced llms.txt generator"
```

---

### Task 26: Implement AiContentEnhancer

**Files:**
- Create: `src/Generator/Enhanced/AiContentEnhancer.php`

- [ ] **Step 1: Implement AiContentEnhancer**

```php
<?php
declare(strict_types=1);

namespace GeoFriendly\Generator\Enhanced;

use GeoFriendly\Config\GeofriendlyConfig;

class AiContentEnhancer
{
    private ?\OpenAI\Client $client = null;

    public function enhance(GeofriendlyConfig $config, array $markdownFiles): array
    {
        if (!$config->openai || !$config->openai->enabled) {
            return $markdownFiles;
        }

        $this->initializeClient($config);

        $enhanced = [];
        foreach ($markdownFiles as $file) {
            $enhanced[] = $this->enhanceFile($file);
        }

        return $enhanced;
    }

    private function initializeClient(GeofriendlyConfig $config): void
    {
        if ($this->client !== null) {
            return;
        }

        $this->client = \OpenAI::client($config->openai->apiKey, $config->openai->baseUrl);
    }

    private function enhanceFile(array $file): array
    {
        $enhancements = $this->generateEnhancements($file);

        return [
            'path' => $file['path'],
            'title' => $enhancements['title'] ?? $file['title'],
            'description' => $enhancements['description'] ?? $file['description'],
            'keywords' => $enhancements['keywords'] ?? [],
            'content' => $file['content'],
        ];
    }

    private function generateEnhancements(array $file): array
    {
        $prompt = $this->buildEnhancementPrompt($file);

        try {
            $response = $this->client->chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are an SEO expert optimizing content for AI answer engines.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'response_format' => ['type' => 'json_object'],
                'temperature' => 0.3,
                'max_tokens' => 200,
            ]);

            $content = $response->choices[0]->message->content;
            return json_decode($content, true);
        } catch (\Exception $e) {
            return [];
        }
    }

    private function buildEnhancementPrompt(array $file): string
    {
        $preview = $this->truncate($file['content'], 1500);

        return <<<PROMPT
Analyze the following content and provide SEO enhancements:

Content:
{$preview}

Provide:
1. An optimized title (3-4 words)
2. A compelling description (9-10 words)
3. 5-7 relevant keywords for AI discovery

Return JSON:
{
    "title": "optimized title",
    "description": "compelling description",
    "keywords": ["keyword1", "keyword2", ...]
}
PROMPT;
    }

    private function truncate(string $text, int $length): string
    {
        if (strlen($text) <= $length) {
            return $text;
        }
        return substr($text, 0, $length) . '...';
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add src/Generator/Enhanced/AiContentEnhancer.php
git commit -m "feat: implement AI content enhancer"
```

---

### Task 27: Implement AiSchemaGenerator

**Files:**
- Create: `src/Generator/Enhanced/AiSchemaGenerator.php`

- [ ] **Step 1: Implement AiSchemaGenerator**

```php
<?php
declare(strict_types=1);

namespace GeoFriendly\Generator\Enhanced;

use GeoFriendly\Config\GeofriendlyConfig;
use GeoFriendly\Generator\SchemaGenerator;

class AiSchemaGenerator extends SchemaGenerator
{
    private ?\OpenAI\Client $client = null;

    public function generate(GeofriendlyConfig $config): string
    {
        if (!$config->openai || !$config->openai->enabled) {
            return parent::generate($config);
        }

        $this->initializeClient($config);

        // Generate enhanced schema with AI
        $schema = $this->generateAiEnhancedSchema($config);

        return json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
    }

    private function initializeClient(GeofriendlyConfig $config): void
    {
        if ($this->client !== null) {
            return;
        }

        $this->client = \OpenAI::client($config->openai->apiKey, $config->openai->baseUrl);
    }

    private function generateAiEnhancedSchema(GeofriendlyConfig $config): array
    {
        $baseSchema = json_decode(parent::generate($config), true);

        // Add AI-enhanced descriptions
        foreach ($baseSchema['@graph'] as &$item) {
            if (isset($item['@type']) && $item['@type'] === 'WebPage') {
                $item['description'] = $this->generateDescription($config);
            }
        }

        return $baseSchema;
    }

    private function generateDescription(GeofriendlyConfig $config): string
    {
        if ($config->description) {
            return $config->description;
        }

        // Generate description with AI if not provided
        try {
            $response = $this->client->chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => 'Generate a concise website description.'],
                    ['role' => 'user', 'content' => "Generate a 10-15 word description for: {$config->title}"],
                ],
                'temperature' => 0.5,
                'max_tokens' => 50,
            ]);

            return trim($response->choices[0]->message->content);
        } catch (\Exception $e) {
            return $config->title;
        }
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add src/Generator/Enhanced/AiSchemaGenerator.php
git commit -m "feat: implement AI-enhanced schema generator"
```

---

## Phase 8: Testing Infrastructure

### Task 28: Create Test Fixtures

**Files:**
- Create: `tests/fixtures/content/`
- Create: `tests/fixtures/expected/`

- [ ] **Step 1: Create test content directory**

```bash
mkdir -p tests/fixtures/content
```

- [ ] **Step 2: Create sample markdown files**

```bash
# tests/fixtures/content/page1.md
---
title: "Getting Started"
description: "Introduction to geo-friendly"
---

# Getting Started

Welcome to geo-friendly! This is a comprehensive guide to making your site discoverable by AI answer engines.

## Installation

Install via composer:
```bash
composer require geo-friendly/geo-friendly
```

## Configuration

Create a geofriendly.yaml file...
```

- [ ] **Step 3: Create expected output files**

```bash
# tests/fixtures/expected/robots.txt
# AI crawlers
User-agent: GPTBot
Allow: /

User-agent: ChatGPT-User
Allow: /

User-agent: CCBot
Allow: /
```

- [ ] **Step 4: Commit**

```bash
git add tests/fixtures/
git commit -m "test: add test fixtures for unit tests"
```

---

### Task 29: Add Integration Tests

**Files:**
- Create: `tests/Integration/GenerationTest.php`

- [ ] **Step 1: Write integration test**

```php
<?php
declare(strict_types=1);

namespace GeoFriendly\Tests\Integration;

use GeoFriendly\GeoFriendly;
use PHPUnit\Framework\TestCase;

class GenerationTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/geo-integration-' . uniqid();
        mkdir($this->tempDir, 0777, true);

        // Copy fixtures
        system("cp -r " . __DIR__ . "/../fixtures/content " . escapeshellarg($this->tempDir));
    }

    protected function tearDown(): void
    {
        system("rm -rf " . escapeshellarg($this->tempDir));
    }

    public function testFullGenerationWorkflow(): void
    {
        $geo = new GeoFriendly([
            'title' => 'Test Site',
            'url' => 'https://test.com',
            'outDir' => $this->tempDir,
            'contentDir' => $this->tempDir . '/content',
        ]);

        $result = $geo->generate();

        $this->assertGreaterThan(0, count($result['generated']));
        $this->assertFileExists($this->tempDir . '/robots.txt');
        $this->assertFileExists($this->tempDir . '/llms.txt');
        $this->assertFileExists($this->tempDir . '/llms-full.txt');
        $this->assertFileExists($this->tempDir . '/sitemap.xml');
        $this->assertFileExists($this->tempDir . '/docs.json');
        $this->assertFileExists($this->tempDir . '/ai-index.json');
        $this->assertFileExists($this->tempDir . '/schema.json');

        $this->assertEmpty($result['errors']);
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add tests/Integration/
git commit -m "test: add integration tests for full generation workflow"
```

---

### Task 30: Configure Test Coverage

**Files:**
- Modify: `phpunit.xml.dist`

- [ ] **Step 1: Update PHPUnit config for coverage**

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/10.0/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true"
         failOnWarning="true"
         failOnRisky="true"
         cacheDirectory=".phpunit.cache"
         beStrictAboutOutputDuringTests="true">
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Integration">
            <directory>tests/Integration</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory>tests/Feature</directory>
        </testsuite>
    </testsuites>
    <source>
        <include>
            <directory>src</directory>
        </include>
        <exclude>
            <directory>src/CLI</directory>
        </exclude>
    </source>
    <coverage>
        <include>
            <directory suffix=".php">src</directory>
        </include>
        <exclude>
            <directory suffix=".php">src/CLI</directory>
        </exclude>
        <report>
            <html outputDirectory="coverage/html"/>
            <text outputFile="php://stdout" showUncoveredFiles="true"/>
        </report>
    </coverage>
</phpunit>
```

- [ ] **Step 2: Commit**

```bash
git add phpunit.xml.dist
git commit -m "test: configure test coverage reporting"
```

---

## Phase 9: CI/CD and Documentation

### Task 31: Create GitHub Actions Workflow

**Files:**
- Create: `.github/workflows/ci.yml`

- [ ] **Step 1: Create CI workflow**

```yaml
name: CI

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main, develop ]

jobs:
  test:
    runs-on: ubuntu-latest

    strategy:
      matrix:
        php-version: ['8.2', '8.3', '8.4']

    name: PHP ${{ matrix.php-version }} Test

    steps:
      - name: Checkout
        uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ matrix.php-version }}
          extensions: mbstring, xml, ctype, iconv, intl, json, yaml
          coverage: xdebug

      - name: Install dependencies
        run: composer update --prefer-dist --no-interaction --no-progress

      - name: Run tests
        run: vendor/bin/phpunit --coverage-text --coverage-clover=coverage.xml

      - name: Upload coverage
        uses: codecov/codecov-action@v3
        with:
          file: ./coverage.xml

  phpstan:
    runs-on: ubuntu-latest

    name: PHPStan Analysis

    steps:
      - name: Checkout
        uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'

      - name: Install dependencies
        run: composer update --prefer-dist --no-interaction --no-progress

      - name: Run PHPStan
        run: vendor/bin/phpstan analyse --error-format=github
```

- [ ] **Step 2: Commit**

```bash
git add .github/workflows/ci.yml
git commit -m "ci: add GitHub Actions workflow for CI/CD"
```

---

### Task 32: Create CHANGELOG.md

**Files:**
- Create: `CHANGELOG.md`

- [ ] **Step 1: Create CHANGELOG.md**

```markdown
# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Initial release of geo-friendly PHP package
- Core generators for all GEO files (robots.txt, llms.txt, llms-full.txt, sitemap.xml, docs.json, ai-index.json, schema.json)
- CLI tool with generate, init, check, and report commands
- YAML configuration support
- Framework integrations (Laravel, Symfony, WordPress, Shopify)
- GEO readiness scoring with multiple auditors
- AI-enhanced features with OpenAI integration

## [0.1.0] - 2026-03-25

### Added
- Initial release
```

- [ ] **Step 2: Commit**

```bash
git add CHANGELOG.md
git commit -m "docs: add CHANGELOG.md"
```

---

### Task 33: Create CONTRIBUTING.md

**Files:**
- Create: `CONTRIBUTING.md`

- [ ] **Step 1: Create CONTRIBUTING.md**

```markdown
# Contributing to geo-friendly

Thank you for considering contributing to geo-friendly!

## Development Setup

1. Clone the repository:
```bash
git clone https://github.com/geo-friendly/geo-friendly.git
cd geo-friendly
```

2. Install dependencies:
```bash
composer install
```

3. Run tests:
```bash
vendor/bin/phpunit
```

4. Run static analysis:
```bash
vendor/bin/phpstan analyse
```

## Code Style

- Follow PSR-12 coding standard
- Use PHP 8.2+ features when appropriate
- Add tests for new features
- Update documentation

## Submitting Changes

1. Fork the repository
2. Create a feature branch
3. Make your changes with tests
4. Ensure tests pass
5. Submit a pull request

## Reporting Issues

Please report issues on GitHub with:
- PHP version
- geo-friendly version
- Steps to reproduce
- Expected behavior
- Actual behavior
```

- [ ] **Step 2: Commit**

```bash
git add CONTRIBUTING.md
git commit -m "docs: add CONTRIBUTING.md"
```

---

### Task 34: Create SECURITY.md

**Files:**
- Create: `SECURITY.md`

- [ ] **Step 1: Create SECURITY.md**

```markdown
# Security Policy

## Supported Versions

| Version | Supported          |
|---------|-------------------|
| 0.1.x   | :white_check_mark: |

## Reporting a Vulnerability

Please report security vulnerabilities to security@geo-friendly.com.

Include:
- Description of the vulnerability
- Steps to reproduce
- Potential impact

We will respond within 48 hours and provide a fix within 7 days.
```

- [ ] **Step 2: Commit**

```bash
git add SECURITY.md
git commit -m "docs: add SECURITY.md"
```

---

## Summary

This implementation plan creates a fully functional PHP 8.2+ Composer package for Generative Engine Optimization with:

✅ Core generators for all GEO files (robots.txt, llms.txt, llms-full.txt, sitemap.xml, docs.json, ai-index.json, schema.json)
✅ CLI tool with generate, init, check, and report commands
✅ YAML configuration support
✅ Framework integrations (Laravel, Symfony, WordPress, Shopify)
✅ GEO readiness scoring with multiple auditors (FilePresence, FormatValidation, ContentQuality, SocialMedia)
✅ AI-enhanced features with OpenAI integration
✅ Comprehensive test coverage (unit, integration, feature tests)
✅ Platform-specific examples
✅ CI/CD with GitHub Actions
✅ Complete documentation (CHANGELOG, CONTRIBUTING, SECURITY)

**Total tasks:** 35
**Estimated time:** 3-4 days for full implementation

**Plan improvements made based on review:**
- ✅ Added missing audit implementations (FormatValidator, ContentQualityAuditor, SocialMediaAuditor)
- ✅ Added AI-enhanced feature tasks (AiLlmsTxtGenerator, AiContentEnhancer, AiSchemaGenerator)
- ✅ Added test fixtures and integration tests
- ✅ Added CI/CD configuration
- ✅ Added complete documentation (CHANGELOG, CONTRIBUTING, SECURITY)
- ✅ Added Chinese README (README.zh-CN.md) with language switcher
- ✅ Fixed date inconsistency (updated to 2026)
