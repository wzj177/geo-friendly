# Geo-Friendly

[![Latest Stable Version](https://img.shields.io/packagist/v/geo-friendly/geo-friendly)](https://packagist.org/packages/geo-friendly/geo-friendly)
[![Total Downloads](https://img.shields.io/packagist/dt/geo-friendly/geo-friendly)](https://packagist.org/packages/geo-friendly/geo-friendly)
[![License](https://img.shields.io/packagist/l/geo-friendly/geo-friendly)](https://packagist.org/packages/geo-friendly/geo-friendly)
[![PHP Version](https://img.shields.io/php/v/geo-friendly/geo-friendly)](https://packagist.org/packages/geo-friendly/geo-friendly)

Generative Engine Optimization (GEO) for PHP - Make your website discoverable by AI answer engines like ChatGPT, Claude, and Perplexity.

[简体中文](README.zh-CN.md) | English

## What is GEO?

Generative Engine Optimization (GEO) is the next evolution of SEO, focusing on making your content discoverable and optimally formatted for AI-powered answer engines. This package helps you generate all the necessary files that AI engines use to understand and index your website.

## Features

- **AI-Friendly File Generation**: Automatically generate files that AI engines prefer
  - `llms.txt` - Structured content for LLMs (as per [llms-txt.org](https://llms-txt.org))
  - `llms-full.txt` - Comprehensive documentation for AI training
  - `robots.txt` - Enhanced with AI-crawler directives
  - `sitemap.xml` - SEO-optimized sitemap
  - `docs.json` - Structured documentation index
  - `ai-index.json` - AI-friendly content index
  - `schema.json` - Schema.org markup for rich results

- **CLI Tool**: Easy-to-use command-line interface with multiple commands
- **Flexible Configuration**: YAML-based configuration for full customization
- **Framework Agnostic**: Works with any PHP project or framework
- **Platform Integrations**: Ready-to-use integrations for WordPress, Shopify, Laravel, and Symfony
- **Audit & Reporting**: Built-in GEO score calculator and detailed reporting

## Requirements

- PHP 8.2 or higher
- Composer
- Extensions: `json`, `simplexml`, `yaml`

## Installation

Install the package via Composer:

```bash
composer require geo-friendly/geo-friendly
```

Or add it to your `composer.json`:

```json
{
    "require": {
        "geo-friendly/geo-friendly": "^1.0"
    }
}
```

After installation, you can use the CLI tool directly:

```bash
# The geo command is now available
vendor/bin/geo --version
```

## Quick Start

### Using the CLI Tool

After installation, the `geo` command will be available in your vendor bin:

```bash
# Generate all GEO files in your project root
vendor/bin/geo generate

# Generate specific files
vendor/bin/geo generate:llms
vendor/bin/geo generate:sitemap
vendor/bin/geo generate:schema

# Generate with custom output directory
vendor/bin/geo generate --output=./public

# Generate with custom configuration
vendor/bin/geo generate --config=./geo-config.yaml
```

### Using Programmatically

```php
use GeoFriendly\GeoGenerator;

$generator = new GeoGenerator([
    'site_url' => 'https://example.com',
    'site_name' => 'My Awesome Site',
    'output_dir' => __DIR__ . '/public',
]);

// Generate all files
$generator->generateAll();

// Or generate specific files
$generator->generateLlmsTxt();
$generator->generateSitemap();
$generator->generateSchema();
```

## Configuration

### Content Modes

Geo-Friendly supports two modes for content generation:

1. **Local Files Mode** (default) - Uses markdown files from your `contentDir`
2. **Firecrawl Mode** - Crawls websites using the Firecrawl API

#### Local Files Mode

Best for documentation sites, blogs, and when you have access to source markdown files:

```yaml
title: 'My Documentation'
url: 'https://docs.example.com'
contentDir: './content'
```

#### Firecrawl Mode

Best for e-commerce sites, corporate websites, or when you need to crawl external sites:

```yaml
title: 'My Store'
url: 'https://store.example.com'
contentDir: ''  # Empty to use Firecrawl
firecrawl:
  apiKey: 'your-firecrawl-api-key'
  apiUrl: 'https://api.firecrawl.dev/v1'
  enabled: true
```

**When to use each mode:**
- **Local Files**: Documentation sites (Docusaurus, MkDocs), blogs (Hugo, Jekyll), knowledge bases
- **Firecrawl**: E-commerce sites, corporate websites, SaaS applications, dynamic content

For detailed information, see [Content Modes Documentation](docs/content-modes.md).

### Basic Configuration

Create a `geofriendly.yaml` file in your project root:

```yaml
# Basic site information
site_url: https://example.com
site_name: My Awesome Site
site_description: A description of your site

# Output directory (relative to project root)
output_dir: ./public

# Files to generate
generate:
  llms_txt: true
  llms_full: true
  robots_txt: true
  sitemap_xml: true
  docs_json: true
  ai_index: true
  schema_json: true

# Content sources
content_sources:
  - ./docs/**/*.md
  - ./src/**/*.php

# Exclude patterns
exclude:
  - vendor/
  - node_modules/
  - tests/

# AI crawler permissions (for robots.txt)
ai_crawlers:
  GPTBot: allow
  Google-Extended: allow
  anthropic-ai: allow
  PerplexityBot: allow
```

## Generated Files

### llms.txt

A structured file following the [llms-txt.org](https://llms-txt.org) specification that provides AI engines with information about your content structure.

```txt
# My Awesome Site
Title: My Awesome Site
Description: A description of your site
Version: 1.0.0

## Documentation
- [Getting Started](https://example.com/docs/getting-started.md)
- [API Reference](https://example.com/docs/api.md)

## Blog
- [Latest Posts](https://example.com/blog/feed.xml)
```

### llms-full.txt

Comprehensive version containing full documentation content for AI training data. Includes complete article content, code examples, and detailed descriptions optimized for LLM consumption.

### robots.txt

Enhanced robots.txt with directives for AI crawlers:

```txt
User-agent: *
Allow: /

# AI Crawlers
User-agent: GPTBot
Allow: /

User-agent: Google-Extended
Allow: /

User-agent: anthropic-ai
Allow: /

User-agent: PerplexityBot
Allow: /

User-agent: Claude-Web
Allow: /

# Sitemap
Sitemap: https://example.com/sitemap.xml
```

### sitemap.xml

SEO-optimized XML sitemap with proper priorities and change frequencies. Includes all pages, posts, and custom post types with appropriate metadata.

### docs.json

Structured documentation index in JSON format, providing a machine-readable overview of your documentation structure with hierarchical organization and metadata.

### ai-index.json

AI-friendly content index that maps your content to optimal AI discovery formats, including summaries, keywords, and relevance scores.

### schema.json

Schema.org structured data for rich search results, including WebSite, WebPage, Article, and Organization schemas with complete metadata.

## CLI Commands

The `geo` CLI tool provides comprehensive functionality for managing your GEO files:

```bash
# Generate all GEO files
vendor/bin/geo generate

# Generate specific file types
vendor/bin/geo generate:llms          # Generate llms.txt and llms-full.txt
vendor/bin/geo generate:robots        # Generate robots.txt
vendor/bin/geo generate:sitemap       # Generate sitemap.xml
vendor/bin/geo generate:schema        # Generate schema.json
vendor/bin/geo generate:docs          # Generate docs.json
vendor/bin/geo generate:ai-index      # Generate ai-index.json

# Initialize configuration
vendor/bin/geo init                   # Create geo-config.yaml in current directory

# Validate configuration
vendor/bin/geo validate               # Validate geo-config.yaml

# Check current GEO status
vendor/bin/geo check                  # Analyze current site and provide recommendations

# Generate detailed report
vendor/bin/geo report                 # Generate comprehensive GEO report with scores

# Show help
vendor/bin/geo --help
vendor/bin/geo generate --help        # Help for specific command

# Show version
vendor/bin/geo --version
```

### Command Options

All generate commands support these options:

```bash
# Custom output directory
vendor/bin/geo generate --output=./public

# Custom configuration file
vendor/bin/geo generate --config=./custom-config.yaml

# Verbose output
vendor/bin/geo generate --verbose

# Dry run (preview changes without writing)
vendor/bin/geo generate --dry-run
```

## Advanced Usage

### Custom Content Processors

You can extend the generator with custom content processors:

```php
use GeoFriendly\Processor\ContentProcessorInterface;

class CustomProcessor implements ContentProcessorInterface
{
    public function process(string $content, array $metadata): array
    {
        // Your custom processing logic
        return [
            'title' => $metadata['title'] ?? '',
            'content' => strip_tags($content),
            'summary' => substr($content, 0, 500),
        ];
    }
}

// Register the processor
$generator->addProcessor(new CustomProcessor());
```

### Integration with Frameworks

**Laravel:**

```php
// In a command or controller
use GeoFriendly\GeoGenerator;

class GenerateGeoCommand extends Command
{
    public function handle()
    {
        $generator = new GeoGenerator([
            'site_url' => config('app.url'),
            'site_name' => config('app.name'),
            'output_dir' => public_path(),
        ]);

        $generator->generateAll();

        $this->info('GEO files generated successfully!');
    }
}
```

A complete Laravel integration package is also available:

```bash
# Install the Laravel service provider
composer require geo-friendly/geo-friendly
php artisan vendor:publish --provider="GeoFriendly\Laravel\GeoFriendlyServiceProvider"
```

See [examples/laravel](examples/laravel) for complete Laravel integration including:
- Service Provider
- Artisan Commands
- Configuration publishing
- Middleware for automatic regeneration

**Symfony:**

```php
// In a console command
use GeoFriendly\GeoGenerator;

class GenerateGeoCommand extends Command
{
    protected static $defaultName = 'geo:generate';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $generator = new GeoGenerator([
            'site_url' => $this->getParameter('router.request_context.host'),
            'site_name' => $this->getParameter('app.name'),
            'output_dir' => $this->getParameter('kernel.project_dir') . '/public',
        ]);

        $generator->generateAll();

        return Command::SUCCESS;
    }
}
```

A complete Symfony bundle is available at [examples/symfony](examples/symfony) with:
- Bundle configuration
- Console commands
- Twig integration
- Event subscribers

**WordPress:**

A WordPress plugin is available at [examples/wordpress-plugin](examples/wordpress-plugin) featuring:
- Automatic GEO file generation
- Admin interface for configuration
- Settings page for customization
- Integration with WordPress cron

**Shopify:**

A Shopify app template is available at [examples/shopify-app](examples/shopify-app) with:
- Theme app extension
- Automatic file generation
- Admin interface
- Multi-language support

## AI Enhancement

The package includes AI-powered capabilities for content optimization:

### AI-Powered Content Enhancement

```php
use GeoFriendly\GeoGenerator;
use GeoFriendly\Enhancement\AiContentEnhancer;

$generator = new GeoGenerator($config);
$enhancer = new AiContentEnhancer($openaiApiKey);

// Enhance content with AI
$enhancedContent = $enhancer->enhanceForLlm($originalContent, [
    'add_context' => true,
    'summarize' => true,
    'extract_keywords' => true,
]);

// Generate AI-optimized llms.txt
$generator->setEnhancer($enhancer);
$generator->generateLlmsTxt();
```

### Features

- **Content Summarization**: Automatically create AI-friendly summaries
- **Keyword Extraction**: Extract relevant keywords for AI discovery
- **Context Enhancement**: Add contextual information for better AI understanding
- **Schema Generation**: Generate structured data optimized for AI engines

## GEO Audit & Reporting

The package includes tools to audit your site's GEO readiness:

```bash
# Check your site's GEO status
vendor/bin/geo check --url=https://example.com

# Generate detailed report
vendor/bin/geo report --url=https://example.com --output=geo-report.html
```

### GEO Score

The audit calculates a comprehensive GEO score (0-100) based on:

- **llms.txt Presence** (20 points)
- **Robots.txt AI Crawler Rules** (15 points)
- **Schema.org Markup** (15 points)
- **Sitemap Completeness** (10 points)
- **Content Structure** (20 points)
- **Metadata Quality** (10 points)
- **AI-Friendly Formatting** (10 points)

## Testing

Run the test suite:

```bash
composer test
```

Run specific test suites:

```bash
# Unit tests only
vendor/bin/phpunit --testsuite=Unit

# Integration tests
vendor/bin/phpunit --testsuite=Integration

# Feature tests
vendor/bin/phpunit --testsuite=Feature
```

Run static analysis:

```bash
composer analyse
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

### Development Setup

```bash
# Clone the repository
git clone https://github.com/geo-friendly/geo-friendly.git
cd geo-friendly

# Install dependencies
composer install

# Run tests
composer test

# Run analysis
composer analyse
```

## License

This package is open-sourced software licensed under the [MIT license](LICENSE.md).

## Credits

- [Geo-Friendly Contributors](https://github.com/geo-friendly/geo-friendly/graphs/contributors)
- Built with inspiration from the GEO community

## Support

- **Documentation**: [Full Documentation](https://github.com/geo-friendly/geo-friendly/docs)
- **Issues**: [GitHub Issues](https://github.com/geo-friendly/geo-friendly/issues)
- **Discussions**: [GitHub Discussions](https://github.com/geo-friendly/geo-friendly/discussions)
- **Platform Examples**: [examples/](examples/) directory

## Related Resources

- [llms-txt.org](https://llms-txt.org) - The specification for LLMs.txt files
- [Schema.org](https://schema.org) - Structured data markup
- [Google's AI Crawlers](https://developers.google.com/search/docs/crawling-indexing/overview-google-crawlers)
- [OpenAI's documentation on making content discoverable](https://platform.openai.com/docs)
- [Anthropic's Claude documentation](https://docs.anthropic.com)
- [Perplexity AI documentation](https://docs.perplexity.ai)

## Changelog

Please see [CHANGELOG.md](CHANGELOG.md) for recent changes.

---

Made with ❤️ for the open-source community
