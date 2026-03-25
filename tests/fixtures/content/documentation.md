# Documentation

Welcome to our comprehensive documentation.

## Installation

```bash
composer require geo-friendly/geo-friendly
```

## Configuration

Create a `geofriendly.yml` file in your project root:

```yaml
site_name: "My Site"
site_url: "https://example.com"
output_dir: "./public"
```

## Usage

### Command Line Interface

```bash
# Generate all GEO files
./bin/geo generate

# Check your GEO score
./bin/geo check

# Generate a report
./bin/geo report
```

### PHP API

```php
use GeoFriendly\GeoFriendly;

$geo = new GeoFriendly([
    'site_name' => 'My Site',
    'site_url' => 'https://example.com',
]);

$geo->generate();
```

## Features

### LLMs.txt Generation

Automatically generates structured summaries optimized for large language models.

### AI Index Generation

Creates machine-readable indexes of your content.

### Schema.org Integration

Generates structured data markup for better search engine understanding.

## FAQ

**Q: What platforms are supported?**
A: We provide integrations for WordPress, Shopify, Laravel, and Symfony.

**Q: Is this free?**
A: Yes, the core package is open source and free to use.

**Q: Can I use this with static site generators?**
A: Absolutely! The package works with any PHP-based project.
