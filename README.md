# Geo-Friendly

[![Latest Version](https://img.shields.io/packagist/v/wzj177/geo-friendly)](https://packagist.org/packages/wzj177/geo-friendly)
[![PHP Version](https://img.shields.io/php/v/wzj177/geo-friendly)](https://packagist.org/packages/wzj177/geo-friendly)

Generative Engine Optimization (GEO) for PHP - Make your website discoverable by AI answer engines like ChatGPT, Claude, and Perplexity.

[简体中文文档](README.zh-CN.md)

## What is GEO?

GEO (Generative Engine Optimization) optimizes content for AI-powered answer engines. This package generates standard files that AI engines use to understand and index your website.

## Features

- **AI-Friendly Files**: `llms.txt`, `llms-full.txt`, `robots.txt`, `sitemap.xml`, `docs.json`, `ai-index.json`, `schema.json`
- **CLI Tool**: Simple command-line interface
- **Two Modes**: Local markdown files OR Firecrawl API (for dynamic sites)
- **Content Arrays**: Direct support for database-stored content
- **Platform Ready**: WordPress, Shopify, Laravel, Symfony integrations

## Requirements

- PHP 7.4+
- Composer
- Extensions: `json`, `simplexml`, `yaml`

## Installation

```bash
composer require wzj177/geo-friendly
```

## Quick Start

### Method 1: CLI Tool (Recommended for static sites)

```bash
# Create config file
vendor/bin/geo init

# Edit geofriendly.yaml, then generate
vendor/bin/geo generate
```

**geofriendly.yaml**:
```yaml
title: 'My Site'
url: 'https://example.com'
contentDir: './content'  # Local markdown files
outDir: './public'
```

### Method 2: Content Arrays (For database content)

```php
use GeoFriendly\GeoFriendly;

$contents = [
    [
        'title' => 'Getting Started',
        'url' => '/getting-started',
        'content' => '# Getting Started\n\nThis is the content...',
        'description' => 'Learn how to get started',
        'category' => 'guide',
    ],
    [
        'title' => 'API Reference',
        'url' => '/api/reference',
        'content' => '# API Reference\n\n...',
        'description' => 'Complete API documentation',
        'category' => 'api',
    ],
];

$config = [
    'title' => 'My Documentation',
    'url' => 'https://docs.example.com',
    'outDir' => __DIR__ . '/public',
    'contents' => $contents,  // Pass content array
];

$geo = new GeoFriendly($config);
[$generated, $errors] = $geo->generate();
```

### Method 3: Firecrawl (For any website)

```yaml
title: 'My Store'
url: 'https://store.example.com'
contentDir: ''  # Empty = use Firecrawl
firecrawl:
  apiKey: 'your-api-key'
  enabled: true
```

## Generated Files

| File | Purpose |
|------|---------|
| `llms.txt` | LLM discovery (per [llms-txt.org](https://llms-txt.org)) |
| `llms-full.txt` | Complete documentation for AI training |
| `robots.txt` | AI crawler permissions |
| `sitemap.xml` | SEO sitemap |
| `docs.json` | Structured documentation index |
| `ai-index.json` | AI-optimized content index |
| `schema.json` | Schema.org structured data |

## CLI Commands

```bash
vendor/bin/geo generate          # Generate all files
vendor/bin/geo init             # Create geofriendly.yaml
vendor/bin/geo check            # Audit GEO status
vendor/bin/geo report           # Generate detailed report
```

## Content Array Format

When passing content from database:

```php
$contents = [
    [
        'title' => string,        // Required: Page title
        'url' => string,          // Required: Page URL (e.g., '/getting-started')
        'content' => string,      // Required: Markdown content
        'description' => string,  // Optional: AI-friendly description (9-10 words)
        'category' => string,     // Optional: Content category
        'tags' => array,          // Optional: Content tags
    ],
    // ... more items
];
```

## Usage Scenarios

### 1. Static Sites with Markdown (VitePress, Docusaurus, Hugo)

```yaml
title: 'My Docs'
url: 'https://docs.example.com'
contentDir: './content'
firecrawl:
  enabled: false
```

**Example**: [examples/vitepress-site](examples/vitepress-site)

### 2. Dynamic Sites (WordPress, Shopify)

```yaml
title: 'My WordPress Site'
url: 'https://mysite.com'
contentDir: ''
firecrawl:
  apiKey: '%env(FIRECRAWL_API_KEY)%'
  enabled: true
```

**Example**: [examples/wordpress-firecrawl](examples/wordpress-firecrawl)

### 3. Custom Backend (SaaS, Enterprise)

```php
// Fetch from database
$contents = Content::where('status', 'published')
    ->get()
    ->map(fn($c) => [
        'title' => $c->title,
        'url' => $c->slug,
        'content' => $c->markdown_content,
        'description' => $c->description,
    ])
    ->toArray();

$config = [
    'title' => 'My Platform',
    'url' => 'https://app.example.com',
    'outDir' => storage_path('geo'),
    'contents' => $contents,
];

$geo = new GeoFriendly($config);
[$generated, $errors] = $geo->generate();
```

**Full Guide**: [docs/generic-backend-solution.md](docs/generic-backend-solution.md)

## Documentation

- [Content Modes](docs/content-modes.md) - Local files vs Firecrawl
- [AI Integration](docs/AI-INTEGRATION.md) - OpenAI enhancement
- [Backend Solution](docs/generic-backend-solution.md) - Database integration

## License

MIT
