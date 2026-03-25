# Symfony Geo-Friendly Integration

Seamlessly integrate geo-friendly file generation into your Symfony application.

## Installation

### 1. Install via Composer

```bash
composer require yourusername/geo-friendly-symfony
```

### 2. Enable the Bundle

For Symfony Flex projects (Symfony 4+), the bundle will be auto-registered.

For manual registration, add to `config/bundles.php`:

```php
return [
    // ...
    GeoFriendly\Symfony\GeoFriendlyBundle::class => ['all' => true],
];
```

## Configuration

### Basic Configuration

Create or edit `config/packages/geofriendly.yaml`:

```yaml
geofriendly:
    site_url: '%env(APP_URL)%'
    output_dir: '%kernel.project_dir%/public'
    site_name: '%env(APP_NAME)%'
    site_description: 'My Symfony Application'
    contact_email: 'contact@example.com'

    generators:
        llms_txt: true
        robots_txt: true
        sitemap: true
        docs_json: true
        ai_index: true
```

### Environment Variables

Add to `.env`:

```env
APP_URL=https://yourapp.com
APP_NAME="My Symfony App"
```

### Advanced Configuration

```yaml
geofriendly:
    # Basic settings
    site_url: '%env(APP_URL)%'
    output_dir: '%kernel.project_dir%/public'
    site_name: 'My Symfony App'
    site_description: 'Description of your app'
    contact_email: 'contact@example.com'

    # Enable specific generators
    generators:
        llms_txt: true
        robots_txt: true
        sitemap: true
        docs_json: true
        ai_index: true
        llms_full_txt: false

    # Specify URLs manually or leave empty for auto-discovery
    urls:
        - 'https://yourapp.com/'
        - 'https://yourapp.com/about'
        - 'https://yourapp.com/contact'

    # Console command
    command_enabled: true

    # Auto-generation (not recommended for production)
    auto_generate: false

    # Auto-discover routes
    auto_discover_routes:
        enabled: true
        exclude_patterns:
            - '^_.*'
            - '^api'
            - '^_profiler'

    # Sitemap settings
    sitemap:
        change_freq: 'weekly'
        priority: '0.8'

    # Robots.txt settings
    robots:
        user_agent: '*'
        disallow:
            - '/admin'
            - '/api'
        include_sitemap: true
```

## Usage

### Console Command

Generate GEO files manually:

```bash
php bin/console geofriendly:generate
```

### With Options

```bash
# Verbose output
php bin/console geofriendly:generate -v

# Custom output directory
php bin/console geofriendly:generate --output=/custom/path

# Force regeneration
php bin/console geofriendly:generate --force
```

### Scheduled Generation

Add to `config/services.yaml` or use cron:

```yaml
# config/services.yaml
services:
    App\Scheduled\GeoGenerateScheduler:
        arguments:
            - '@GeoFriendly\GeoFriendly'
        tags:
            - { name: 'monolog.logger', channel: 'geofriendly' }
```

Create the scheduler:

```php
// src/Scheduled/GeoGenerateScheduler.php
namespace App\Scheduled;

use GeoFriendly\GeoFriendly;

class GeoGenerateScheduler
{
    private GeoFriendly $geo;

    public function __construct(GeoFriendly $geo)
    {
        $this->geo = $geo;
    }

    public function __invoke(): void
    {
        $this->geo->generate();
    }
}
```

Configure cron (recommended over Symfony scheduler for GEO files):

```bash
# crontab -e
0 2 * * * cd /path/to/project && php bin/console geofriendly:generate
```

### Programmatic Usage

Use in controllers or services:

```php
use GeoFriendly\GeoFriendly;

class MyController extends AbstractController
{
    public function someAction(GeoFriendly $geo)
    {
        // Generate files
        [$generated, $errors] = $geo->generate();

        if (!empty($errors)) {
            $this->addFlash('error', implode(', ', $errors));
        }

        return $this->redirectToRoute('homepage');
    }
}
```

### After Entity Changes

Generate after entity updates:

```php
// src/EntityListener/PostListener.php
namespace App\EntityListener;

use App\Entity\Post;
use GeoFriendly\GeoFriendly;

class PostListener
{
    private GeoFriendly $geo;

    public function __construct(GeoFriendly $geo)
    {
        $this->geo = $geo;
    }

    public function postPersist(Post $post): void
    {
        if ($post->isPublished()) {
            $this->geo->generate();
        }
    }

    public function postUpdate(Post $post): void
    {
        if ($post->isPublished()) {
            $this->geo->generate();
        }
    }
}
```

Configure in `config/services.yaml`:

```yaml
services:
    App\EntityListener\PostListener:
        arguments:
            - '@GeoFriendly\GeoFriendly'
        tags:
            - { name: 'doctrine.orm.entity_listener', event: postPersist, entity: App\Entity\Post }
            - { name: 'doctrine.orm.entity_listener', event: postUpdate, entity: App\Entity\Post }
```

### Auto-Discover Routes

Enable automatic route discovery:

```yaml
# config/packages/geofriendly.yaml
geofriendly:
    auto_discover_routes:
        enabled: true
        exclude_patterns:
            - '^_.*'
            - '^api'
            - '^admin'
```

### Custom URL Discovery

Create custom discovery service:

```php
// src/Service/GeoUrlProvider.php
namespace App\Service;

class GeoUrlProvider
{
    public function getUrls(): array
    {
        // Your custom logic
        return [
            'https://yourapp.com/',
            'https://yourapp.com/products',
            // ...
        ];
    }
}
```

Use in configuration:

```yaml
geofriendly:
    urls: '@App\Service\GeoUrlProvider::getUrls'
```

## Advanced Usage

### Custom Generator

Create custom generator:

```php
// src/GeoFriendly/CustomGenerator.php
namespace App\GeoFriendly;

use GeoFriendly\Generator\GeneratorInterface;
use GeoFriendly\Config\GeofriendlyConfig;

class CustomGenerator implements GeneratorInterface
{
    public function generate(GeofriendlyConfig $config): string
    {
        // Your generation logic
        return 'custom content';
    }

    public function getFilename(): string
    {
        return 'custom.txt';
    }
}
```

Register as service:

```yaml
# config/services.yaml
services:
    App\GeoFriendly\CustomGenerator:
        tags:
            - { name: 'geofriendly.generator', alias: 'custom' }
```

### Conditional Generation

Generate only when needed:

```php
use GeoFriendly\GeoFriendly;
use Symfony\Component\HttpFoundation\RequestStack;

class ConditionalGeoGenerator
{
    private GeoFriendly $geo;
    private RequestStack $requestStack;

    public function __construct(GeoFriendly $geo, RequestStack $requestStack)
    {
        $this->geo = $geo;
        $this->requestStack = $requestStack;
    }

    public function generateIfNeeded(): void
    {
        $request = $this->requestStack->getCurrentRequest();

        // Only generate for certain routes
        if ($request && $request->attributes->get('_route') === 'admin_content_save') {
            $this->geo->generate();
        }
    }
}
```

### Testing

Test generation locally:

```bash
php bin/console geofriendly:generate -v
```

Verify files:

```bash
ls -la public/llms.txt public/robots.txt public/sitemap.xml
```

## Troubleshooting

### Permissions

Ensure output directory is writable:

```bash
chmod 755 public/
```

### Route Discovery Issues

Check routes:

```bash
php bin/console debug:router
```

### Cache Issues

Clear cache after configuration changes:

```bash
php bin/console cache:clear
```

## Performance

### Cache URLs

Cache discovered routes:

```php
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;

class CachedRouteDiscoverer
{
    private CacheInterface $cache;

    public function getUrls(): array
    {
        return $this->cache->get('geofriendly.urls', function (ItemInterface $item) {
            $item->expiresAfter(3600); // 1 hour
            return $this->discoverRoutes();
        });
    }

    private function discoverRoutes(): array
    {
        // Discovery logic
    }
}
```

### Queue Generation

Use Messenger for async generation:

```php
// src/Message/GenerateGeoFiles.php
namespace App\Message;

class GenerateGeoFiles
{
}

// src/MessageHandler/GenerateGeoFilesHandler.php
namespace App\MessageHandler;

use App\Message\GenerateGeoFiles;
use GeoFriendly\GeoFriendly;

class GenerateGeoFilesHandler
{
    private GeoFriendly $geo;

    public function __construct(GeoFriendly $geo)
    {
        $this->geo = $geo;
    }

    public function __invoke(GenerateGeoFiles $message): void
    {
        $this->geo->generate();
    }
}
```

Dispatch:

```php
use App\Message\GenerateGeoFiles;
use Symfony\Component\Messenger\MessageBusInterface;

class MyService
{
    private MessageBusInterface $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public function someMethod(): void
    {
        $this->bus->dispatch(new GenerateGeoFiles());
    }
}
```

## Security

- Output directory should be publicly accessible
- Exclude sensitive routes from discovery
- Use environment variables for sensitive data
- Disable auto-generate in production

## Best Practices

1. Use cron for scheduled generation
2. Enable route auto-discovery for dynamic sites
3. Cache URL discovery results
4. Monitor logs for generation errors
5. Test in staging before production
6. Exclude admin and API routes from public files

## Support

- Documentation: [Link to docs]
- Issues: [Link to issues]
- Symfony: https://symfony.com/doc

## License

MIT License

## Contributing

Contributions welcome! Please feel free to submit pull requests.
