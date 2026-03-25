# Laravel Geo-Friendly Integration

Seamlessly integrate geo-friendly file generation into your Laravel application.

## Installation

### 1. Install via Composer

```bash
composer require yourusername/geo-friendly
```

### 2. Publish the Service Provider

For Laravel 10.x and below, add to `config/app.php`:

```php
'providers' => [
    // ...
    GeoFriendly\Laravel\GeoFriendlyServiceProvider::class,
],
```

For Laravel 11.x, the package will be auto-discovered.

### 3. Publish Configuration

```bash
php artisan vendor:publish --tag=geofriendly-config
```

This creates `config/geofriendly.php` where you can customize settings.

## Configuration

### Environment Variables

Add these to your `.env` file:

```env
# Basic Settings
GEO_FRIENDLY_SITE_URL=https://yourapp.com
GEO_FRIENDLY_SITE_NAME="My Laravel App"
GEO_FRIENDLY_DESCRIPTION="My awesome application"
GEO_FRIENDLY_CONTACT_EMAIL=contact@yourapp.com

# Output Directory (default: public_path())
GEO_FRIENDLY_OUTPUT_DIR=/path/to/public

# Enable/Disable Generators
GEO_FRIENDLY_LLMSTXT=true
GEO_FRIENDLY_ROBOTSTXT=true
GEO_FRIENDLY_SITEMAP=true
GEO_FRIENDLY_DOCSJSON=true
GEO_FRIENDLY_AIINDEX=true
GEO_FRIENDLY_LLMFULLTXT=false

# Auto Generation
GEO_FRIENDLY_AUTO_GENERATE=true
GEO_FRIENDLY_SCHEDULE="0 2 * * *"

# Auto Generate on Model Changes
GEO_FRIENDLY_AUTO_GENERATE_ON_CHANGE=true
```

### Configuration File

Customize `config/geofriendly.php` for advanced settings:

```php
return [
    'siteUrl' => config('app.url'),
    'outDir' => public_path(),
    'siteName' => config('app.name'),
    'generators' => [
        'llmsTxt' => true,
        'robotsTxt' => true,
        'sitemap' => true,
        'docsJson' => true,
        'aiIndex' => true,
    ],
    'observe_models' => [
        App\Models\Post::class,
        App\Models\Page::class,
    ],
];
```

## Usage

### Artisan Command

Generate GEO files manually:

```bash
php artisan geo:generate
```

### With Options

```bash
# Force regeneration
php artisan geo:generate --force

# Verbose output
php artisan geo:generate --verbose

# Custom output directory
php artisan geo:generate --output=/custom/path
```

### Scheduled Generation

The package automatically schedules generation if `auto_generate` is enabled:

```php
// In app/Console/Kernel.php (Laravel 10.x)
protected function schedule(Schedule $schedule)
{
    // Already configured via config/geofriendly.php
    // Default: Daily at 2 AM
}
```

### Automatic Generation on Model Changes

Enable automatic regeneration when models change:

```php
// config/geofriendly.php
'observe_models' => [
    App\Models\Post::class,
    App\Models\Page::class,
    App\Models\Product::class,
],
```

### Programmatic Usage

Use in your code:

```php
use GeoFriendly\GeoFriendly;

// Get instance from container
$geo = app(GeoFriendly::class);

// Or use facade alias
$geo = app('geofriendly');

// Generate files
[$generated, $errors] = $geo->generate();

if (!empty($errors)) {
    Log::error('GEO generation errors', $errors);
}
```

### After Model Events

Trigger generation after model events:

```php
// In your model
class Post extends Model
{
    protected static function booted()
    {
        static::saved(function ($post) {
            app('geofriendly')->generate();
        });
    }
}
```

### In Controllers

Generate after content updates:

```php
class PostController extends Controller
{
    public function update(Request $request, Post $post)
    {
        $post->update($request->validated());

        // Regenerate GEO files
        app('geofriendly')->generate();

        return redirect()->route('posts.show', $post);
    }
}
```

## Advanced Usage

### Custom URL Discovery

Customize URLs to include:

```php
// config/geofriendly.php
'urls' => function () {
    $urls = [config('app.url')];

    // Add all public routes
    foreach (Route::getRoutes() as $route) {
        if ($route->getName() && !str_starts_with($route->getName(), 'admin.')) {
            try {
                $urls[] = URL::to($route->uri);
            } catch (\Exception $e) {
                // Skip invalid routes
            }
        }
    }

    return $urls;
},
```

### Custom Generators

Register custom generators in a service provider:

```php
use GeoFriendly\Generator\GeneratorInterface;

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

// Register in AppServiceProvider
public function boot()
{
    $geo = app('geofriendly');
    $geo->registerGenerator('custom', new CustomGenerator());
}
```

### Conditional Generation

Generate only when needed:

```php
class GeoObserver
{
    public function saved(Model $model)
    {
        // Check if generation is needed
        if ($this->shouldRegenerate($model)) {
            app('geofriendly')->generate();
        }
    }

    protected function shouldRegenerate(Model $model): bool
    {
        // Your logic here
        return $model->isDirty('status') && $model->status === 'published';
    }
}
```

## Generated Files

Files are generated in the configured output directory (default: `public/`):

- `llms.txt` - LLM discovery file
- `robots.txt` - Search engine directives
- `sitemap.xml` - XML sitemap
- `docs.json` - Structured documentation
- `ai-index.json` - Enhanced AI index

## Testing

Test generation locally:

```bash
php artisan geo:generate --verbose
```

Verify files are created:

```bash
ls -la public/llms.txt public/robots.txt public/sitemap.xml
```

## Troubleshooting

### Permissions

Ensure the output directory is writable:

```bash
chmod 755 public/
```

### Schedule Not Running

Verify Laravel scheduler is configured in crontab:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

### Missing Files

Check logs:

```bash
tail -f storage/logs/laravel.log
```

## Performance

### Cache URLs

Cache URL discovery for better performance:

```php
'urls' => Cache::remember('geo-urls', 3600, function () {
    // Your URL discovery logic
}),
```

### Queue Generation

Queue the generation for large sites:

```php
dispatch(function () {
    app('geofriendly')->generate();
});
```

## Security

- Output directory should be publicly accessible
- Ensure sensitive routes are excluded from generated files
- Use environment variables for sensitive configuration

## Best Practices

1. **Enable auto-generation** for always-updated files
2. **Schedule generation** during low-traffic hours
3. **Monitor logs** for generation errors
4. **Test locally** before deploying to production
5. **Exclude sensitive routes** from sitemap

## Support

- Documentation: [Link to docs]
- Issues: [Link to issues]
- Discussions: [Link to discussions]

## License

MIT License

## Contributing

Contributions welcome! Please feel free to submit pull requests.
