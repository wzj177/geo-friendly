# Geo-Friendly

[![Latest Stable Version](https://img.shields.io/packagist/v/geo-friendly/geo-friendly)](https://packagist.org/packages/geo-friendly/geo-friendly)
[![Total Downloads](https://img.shields.io/packagist/dt/geo-friendly/geo-friendly)](https://packagist.org/packages/geo-friendly/geo-friendly)
[![License](https://img.shields.io/packagist/l/geo-friendly/geo-friendly)](https://packagist.org/packages/geo-friendly/geo-friendly)
[![PHP Version](https://img.shields.io/php/v/geo-friendly/geo-friendly)](https://packagist.org/packages/geo-friendly/geo-friendly)

Generative Engine Optimization (GEO) for PHP - Make your website discoverable by AI answer engines like ChatGPT, Claude, and Perplexity.

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

- **CLI Tool**: Easy-to-use command-line interface
- **Flexible Configuration**: YAML-based configuration for full customization
- **Framework Agnostic**: Works with any PHP project or framework

## Requirements

- PHP 8.2 or higher
- Composer

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

Create a `geo-config.yaml` file in your project root:

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

# Sitemap
Sitemap: https://example.com/sitemap.xml
```

### sitemap.xml

SEO-optimized XML sitemap with proper priorities and change frequencies.

### schema.json

Schema.org structured data for rich search results.

## CLI Commands

```bash
# Generate all GEO files
geo generate

# Generate specific file types
geo generate:llms          # Generate llms.txt and llms-full.txt
geo generate:robots        # Generate robots.txt
geo generate:sitemap       # Generate sitemap.xml
geo generate:schema        # Generate schema.json
geo generate:docs          # Generate docs.json
geo generate:ai-index      # Generate ai-index.json

# Initialize configuration
geo init                   # Create geo-config.yaml

# Validate configuration
geo validate               # Validate geo-config.yaml

# Show version
geo --version
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

## Testing

Run the test suite:

```bash
composer test
```

Run static analysis:

```bash
composer analyse
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This package is open-sourced software licensed under the [MIT license](LICENSE.md).

## Credits

- [Your Name](https://github.com/yourusername)
- [All Contributors](../../contributors)

## Support

- **Documentation**: [Full Documentation](https://github.com/geo-friendly/geo-friendly/docs)
- **Issues**: [GitHub Issues](https://github.com/geo-friendly/geo-friendly/issues)
- **Discussions**: [GitHub Discussions](https://github.com/geo-friendly/geo-friendly/discussions)

## Related Resources

- [llms-txt.org](https://llms-txt.org) - The specification for LLMs.txt files
- [Schema.org](https://schema.org) - Structured data markup
- [Google's AI Crawlers](https://developers.google.com/search/docs/crawling-indexing/overview-google-crawlers)
- [OpenAI's documentation on making content discoverable](https://platform.openai.com/docs)

---

Made with ❤️ for the open-source community
