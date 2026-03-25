# Content Modes

Geo-Friendly supports two modes for content generation:

1. **Local Files Mode** (default) - Uses markdown files from your `contentDir`
2. **Firecrawl Mode** - Crawls websites using the Firecrawl API

## Local Files Mode

This is the default and recommended mode for most use cases. Geo-Friendly scans your local markdown files and generates AI-friendly indexes.

### When to Use

- You have existing documentation in markdown format
- You're building a documentation site, blog, or knowledge base
- You want full control over content structure
- You need offline capability

### Setup

1. Create a `content` directory in your project root
2. Add your markdown files:
   ```
   content/
   ├── index.md
   ├── getting-started.md
   └── api/
       ├── authentication.md
       └── endpoints.md
   ```

3. Configure in `geofriendly.yaml`:
   ```yaml
   contentDir: './content'
   ```

### Examples

**Documentation Sites**
- Docusaurus
- MkDocs
- VuePress
- Jekyll blogs

**Blogs**
- Hugo
- Hexo
- Jekyll
- Any static site generator with markdown content

**Knowledge Bases**
- Company wikis
- API documentation
- User guides

## Firecrawl Mode

Firecrawl mode allows Geo-Friendly to crawl and extract content from live websites. This is useful when you don't have access to the source files or need to index dynamic content.

### When to Use

- You need to index a website you don't own
- The site uses dynamic content generation
- You want to quickly prototype without file access
- The site uses a CMS without easy export

### Setup

1. Get a Firecrawl API key from [firecrawl.dev](https://www.firecrawl.dev/)
2. Configure in `geofriendly.yaml`:
   ```yaml
   firecrawl:
     apiKey: 'your-api-key-here'
     apiUrl: 'https://api.firecrawl.dev/v1'
     enabled: true
   ```

3. Leave `contentDir` empty or set to `''`:
   ```yaml
   contentDir: ''
   ```

### How It Works

When `contentDir` is empty and Firecrawl is enabled:

1. Geo-Friendly calls Firecrawl API with your site URL
2. Firecrawl crawls and extracts content as markdown
3. The extracted content is used for generating AI-friendly files

### Examples

**E-commerce Sites**
- Product catalog sites
- Online stores (Shopify, WooCommerce, etc.)

**Corporate Websites**
- Company information pages
- Service descriptions
- About pages

**News/Media Sites**
- Article collections
- News archives
- Blog posts

**SaaS Applications**
- Feature documentation
- Pricing pages
- Help centers

## Choosing the Right Mode

### Use Local Files Mode when:

- You have access to source markdown files
- Content is relatively static
- You need precise control over what's indexed
- You're building documentation or knowledge bases

### Use Firecrawl Mode when:

- You need to index external websites
- Content is dynamically generated
- You don't have source file access
- You're prototyping or auditing

### Hybrid Approach

You can use both modes together:

```yaml
contentDir: './content'  # Local files
firecrawl:
  apiKey: 'your-key'
  enabled: true
```

In this configuration:
- Local files are indexed first
- Firecrawl supplements with additional content
- You get the best of both worlds

## Configuration Examples

### Static Documentation Site (Local Files)

```yaml
title: 'My Documentation'
url: 'https://docs.example.com'
contentDir: './docs'
generators:
  llmsTxt: true
  sitemap: true
```

### E-commerce Site (Firecrawl)

```yaml
title: 'My Store'
url: 'https://store.example.com'
contentDir: ''  # Empty to use Firecrawl
firecrawl:
  apiKey: 'fc-...'
  enabled: true
generators:
  llmsTxt: true
  sitemap: true
```

### Hybrid Configuration

```yaml
title: 'My Platform'
url: 'https://platform.example.com'
contentDir: './content'  # Local docs
firecrawl:
  apiKey: 'fc-...'
  enabled: true  # Also crawl web content
generators:
  llmsTxt: true
  llmsFullTxt: true
```

## Best Practices

### Local Files Mode

1. **Organize content logically**
   - Use clear directory structure
   - Name files descriptively
   - Use consistent formatting

2. **Add frontmatter**
   ```yaml
   ---
   title: "Page Title"
   description: "Page description"
   tags: [api, integration]
   ---
   ```

3. **Keep content updated**
   - Regular review and updates
   - Remove outdated files
   - Maintain consistency

### Firecrawl Mode

1. **Respect rate limits**
   - Don't re-crawl too frequently
   - Use appropriate timeout settings
   - Monitor API usage

2. **Handle errors gracefully**
   - Check for extraction failures
   - Log issues for review
   - Have fallback content

3. **Optimize crawl targets**
   - Crawl specific sections when possible
   - Avoid unnecessary pages
   - Focus on high-value content

## Troubleshooting

### Local Files Not Found

- Verify `contentDir` path is correct
- Check file permissions
- Ensure files have `.md` or `.mdx` extension

### Firecrawl Failures

- Verify API key is valid
- Check API rate limits
- Ensure target URL is accessible
- Review error logs for details

### Mixed Content Issues

- Clear cache between crawls
- Check for duplicate content
- Verify URL configurations

## Advanced Configuration

### Custom Firecrawl Endpoints

```yaml
firecrawl:
  apiKey: 'your-key'
  apiUrl: 'https://custom-proxy.example.com/v1'
  enabled: true
```

### Conditional Mode Selection

```php
use GeoFriendly\Utils\FileHelper;

// In your code
$config = GeofriendlyConfig::fromArray($configArray);

if (!empty($config->contentDir)) {
    // Use local files
    $files = FileHelper::collectMarkdownFiles($config->contentDir);
} elseif ($config->firecrawl['enabled']) {
    // Use Firecrawl
    $content = FileHelper::extractContent($config->url, $config);
}
```

## Performance Considerations

### Local Files Mode

- Fast - no network requests
- Scalable to thousands of files
- Low resource usage

### Firecrawl Mode

- Depends on API response time
- Subject to rate limits
- Network-dependent
- May incur API costs

## Security Considerations

### Local Files Mode

- Ensure proper file permissions
- Validate file paths
- Scan for malicious content

### Firecrawl Mode

- Secure API keys
- Use environment variables
- Monitor for unauthorized access
- Respect robots.txt
