# OpenAI/Firecrawl Integration Guide

This document explains how OpenAI and Firecrawl integrations work in geo-friendly.

## Content Modes

### Mode 1: Local Markdown Files (Default)

**How it works:**
- Reads `.md` and `.mdx` files from `contentDir` directory
- Uses FileHelper to recursively collect markdown files
- Parses frontmatter for titles and descriptions
- No external API calls

**When to use:**
- ✅ Static site generators (Jekyll, Hugo, Astro, Next.js with SSG)
- ✅ Documentation sites with markdown content
- ✅ GitBook, MkDocs, Docusaurus sites
- ✅ Blogs with markdown posts
- ✅ Any site where you control the content source

**Maintenance requirements:**
1. Keep markdown files in `content/` directory
2. Use frontmatter for titles/descriptions:
   ```yaml
   ---
   title: "Page Title"
   description: "Page description for LLMs"
   ---
   ```
3. Follow naming: `page-name.md` or `page-name.mdx`

**Directory structure:**
```
project/
├── content/
│   ├── getting-started.md
│   ├── api/
│   │   └── reference.md
│   └── guides/
│       └── tutorial.md
├── public/           # Generated GEO files go here
└── geofriendly.yaml
```

### Mode 2: Firecrawl API (Enhanced)

**How it works:**
- Uses Firecrawl API to crawl and extract website content
- Converts HTML to markdown automatically
- Requires `FIRECRAWL_API_KEY` environment variable
- Falls back to local files if API fails

**When to use:**
- ✅ Dynamic websites (WordPress, Shopify, custom PHP apps)
- ✅ Sites without markdown source files
- ✅ Legacy sites you don't want to modify
- ✅ Quick testing without content migration
- ✅ Sites with complex content structures

**Setup:**
```yaml
# geofriendly.yaml
firecrawl:
  apiKey: '%env(FIRECRAWL_API_KEY)%'
  apiUrl: 'https://api.firecrawl.dev/v1'
  enabled: true
```

**Environment variable:**
```bash
export FIRECRAWL_API_KEY=fc-...
```

## OpenAI Enhancement

### AI Features (Optional)

When `openai.apiKey` is configured, geo-friendly uses AI to:

1. **Generate better descriptions**
   - AI analyzes content and creates optimized descriptions
   - 3-4 word titles and 9-10 word descriptions
   - Better than generic descriptions

2. **Content enhancement**
   - SEO optimization for AI discovery
   - Keyword extraction
   - Readability improvements

3. **Schema.org enhancement**
   - AI-generated descriptions for structured data
   - Better search engine understanding

**Setup:**
```yaml
# geofriendly.yaml
openai:
  apiKey: '%env(OPENAI_API_KEY)%'
  baseUrl: 'https://api.openai.com/v1'  # Or custom endpoint
  model: 'gpt-4o-mini'
```

**Requirements:**
```bash
composer require openai-php/client
```

## Usage Examples

### Example 1: Static Site (Local Files)

```bash
# Site structure
my-docs/
├── content/
│   ├── guide.md
│   └── api.md
├── public/
└── geofriendly.yaml

# Generate GEO files
./bin/geo generate
```

**Config:**
```yaml
title: 'My Documentation'
url: 'https://docs.example.com'
contentDir: './content'
outDir: './public'

# No Firecrawl needed - using local markdown
```

### Example 2: WordPress Site (Firecrawl)

```bash
# Set API key
export FIRECRAWL_API_KEY=fc-...

# Generate GEO files by crawling
./bin/geo generate --url=https://mysite.com --title="My Site"
```

**Config:**
```yaml
title: 'My Site'
url: 'https://mysite.com'
outDir: './public'

# Enable Firecrawl for content extraction
firecrawl:
  apiKey: '%env(FIRECRAWL_API_KEY)%'
  enabled: true
```

### Example 3: Enhanced Mode (OpenAI + Firecrawl)

```bash
# Set both API keys
export FIRECRAWL_API_KEY=fc-...
export OPENAI_API_KEY=sk-...

# Generate with AI enhancement
./bin/geo generate
```

**Config:**
```yaml
title: 'My Site'
url: 'https://mysite.com'
outDir: './public'

# Firecrawl for content extraction
firecrawl:
  apiKey: '%env(FIRECRAWL_API_KEY)%'
  enabled: true

# OpenAI for enhancement
openai:
  apiKey: '%env(OPENAI_API_KEY)%'
  model: 'gpt-4o-mini'
```

## Cost Considerations

### Local Files Mode
- **Cost:** Free
- **Performance:** Fast (local file reads)
- **Maintenance:** Requires markdown file maintenance

### Firecrawl Mode
- **Cost:** Depends on Firecrawl pricing
- **Performance:** Slower (API calls)
- **Maintenance:** No content changes needed

### OpenAI Enhancement
- **Cost:** Depends on OpenAI API usage
- **Performance:** Slower (API calls)
- **Quality:** Better AI-optimized content

## Recommendations

| Website Type | Recommended Mode | Reason |
|--------------|------------------|---------|
| Documentation sites | Local Files | You control markdown, faster |
| Blogs with markdown | Local Files | Native markdown support |
| WordPress sites | Firecrawl | No file access needed |
| Shopify stores | Firecrawl | Use Admin API or crawl |
| Custom PHP apps | Firecrawl | Or maintain markdown docs |
| Legacy sites | Firecrawl | No modifications needed |
| Testing/POC | Firecrawl | Quick setup, no migration |

## Troubleshooting

### Local Files Mode Issues

**Problem:** No files generated
- **Solution:** Check `contentDir` path exists
- **Solution:** Verify markdown files have `.md` or `.mdx` extension

**Problem:** Missing descriptions
- **Solution:** Add frontmatter to markdown files:
  ```yaml
  ---
  title: "Page Title"
  description: "Page description"
  ---
  ```

### Firecrawl Mode Issues

**Problem:** API errors
- **Solution:** Verify `FIRECRAWL_API_KEY` is set
- **Solution:** Check API key is valid
- **Solution:** Check Firecrawl credits

**Problem:** Content not extracted
- **Solution:** Verify URL is accessible
- **Solution:** Check robots.txt allows crawling
- **Solution:** Try with `onlyMainContent: true`

### OpenAI Enhancement Issues

**Problem:** AI features not working
- **Solution:** Install `openai-php/client`
- **Solution:** Verify `OPENAI_API_KEY` is set
- **Solution:** Check API key has credits

**Problem:** Poor quality descriptions
- **Solution:** Try different model (gpt-4o instead of gpt-4o-mini)
- **Solution:** Increase `max_tokens`
- **Solution:** Improve content quality
