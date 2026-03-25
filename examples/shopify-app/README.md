# Shopify Geo-Friendly Integration

Generate GEO-friendly files (llms.txt, docs.json, sitemap.xml) for your Shopify store to improve AI/LLM discovery.

## Features

- **Automatic llms.txt generation**: Standard format for LLM discovery
- **Dynamic docs.json**: Structured documentation of products, collections, and pages
- **Sitemap.xml generation**: Standard XML sitemap for search engines
- **Admin API integration**: Fetches real-time data from your store
- **Flexible deployment**: Use as a standalone script or integrate into your workflow

## Requirements

- PHP 7.4 or higher
- cURL extension
- Shopify Admin API access token
- Shopify store domain

## Installation

### Option 1: Liquid Template (Recommended for simple llms.txt)

1. Copy `llms-txt.liquid` to your theme
2. Create a new page in Shopify with the handle `llms`
3. Assign the llms.txt template to the page
4. Access at `https://yourstore.com/pages/llms`

### Option 2: PHP Client (Full integration)

1. Download `shopify-client.php`
2. Get your Shopify Admin API access token:
   - Go to Shopify Admin > Apps > Manage private apps
   - Create a private app with Admin API access
   - Copy the API token
3. Run the script:
   ```bash
   php shopify-client.php --store=your-store.myshopify.com --token=your-api-token
   ```

## Usage

### Standalone Script

```bash
php shopify-client.php --store=your-store.myshopify.com --token=your-api-token
```

### With Custom Output Directory

```bash
php shopify-client.php \
  --store=your-store.myshopify.com \
  --token=your-api-token \
  --output=./geo-files
```

### With Specific API Version

```bash
php shopify-client.php \
  --store=your-store.myshopify.com \
  --token=your-api-token \
  --version=2024-04
```

## Configuration Options

| Option | Required | Default | Description |
|--------|----------|---------|-------------|
| `--store` | Yes | - | Shopify store domain (e.g., your-store.myshopify.com) |
| `--token` | Yes | - | Shopify Admin API access token |
| `--output` | No | `./output` | Directory to save generated files |
| `--version` | No | `2024-01` | Shopify Admin API version |

## Generated Files

The script generates the following files:

- **llms.txt**: Standard LLM discovery file with products, collections, and content
- **docs.json**: Structured documentation in JSON format
- **sitemap.xml**: XML sitemap for search engines

## Example Output

### llms.txt

```
# My Awesome Store
# Best products online
# https://mystore.myshopify.com

Currency: USD
Contact: store@mystore.com

# Products
Amazing Product 1: https://mystore.myshopify.com/products/amazing-product-1
Amazing Product 2: https://mystore.myshopify.com/products/amazing-product-2

# Collections
Summer Collection: https://mystore.myshopify.com/collections/summer
New Arrivals: https://mystore.myshopify.com/collections/new
```

### docs.json

```json
{
  "title": "My Awesome Store",
  "description": "Best products online",
  "homepage": "https://mystore.myshopify.com",
  "sections": [
    {
      "title": "Products",
      "items": [...]
    },
    {
      "title": "Collections",
      "items": [...]
    }
  ]
}
```

## Automation

### Cron Job (Linux/Mac)

Add to crontab for daily generation:

```bash
0 2 * * * cd /path/to/script && php shopify-client.php --store=your-store.myshopify.com --token=your-api-token
```

### GitHub Actions

```yaml
name: Generate GEO Files

on:
  schedule:
    - cron: '0 2 * * *'
  workflow_dispatch:

jobs:
  generate:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - name: Generate GEO files
        run: |
          php shopify-client.php \
            --store=${{ secrets.SHOPIFY_STORE }} \
            --token=${{ secrets.SHOPIFY_TOKEN }} \
            --output=./output
      - name: Deploy
        # Your deployment steps here
```

### Shopify Flow Integration

You can also integrate with Shopify Flow to trigger generation when:
- New product is published
- Product is updated
- Collection is modified

## API Limits

The Shopify Admin API has rate limits:
- Standard: 2 requests per second
- Leaky bucket: 40 points per second, 3000 points per minute

The script implements pagination to handle large stores efficiently.

## Troubleshooting

### API Authentication Error

- Verify your access token is valid
- Check token has necessary permissions (read products, pages, blogs)
- Ensure API version is supported

### SSL Certificate Error

- Update your CA certificates
- Or disable SSL verification (not recommended for production)

### Memory Limit Error

- Increase PHP memory limit: `php -d memory_limit=512M shopify-client.php`

## Security

- Never commit API tokens to version control
- Use environment variables for sensitive data
- Rotate access tokens regularly
- Use minimal required permissions

## Contributing

Contributions welcome! Please feel free to submit pull requests.

## License

MIT License - feel free to use in your projects.

## Support

For issues with:
- **Script functionality**: Open an issue on GitHub
- **Shopify API**: Check [Shopify Admin API docs](https://shopify.dev/api/admin)
- **Store setup**: Contact Shopify support
