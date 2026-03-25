<?php

declare(strict_types=1);

/**
 * Geo-Friendly Configuration for Laravel
 *
 * This file contains the configuration options for the geo-friendly package.
 * You can customize the generated files by modifying these settings.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Site URL
    |--------------------------------------------------------------------------
    |
    | The base URL of your website. This will be used in the generated files.
    | Leave empty to use the Laravel app.url config.
    |
    */
    'siteUrl' => env('GEO_FRIENDLY_SITE_URL', config('app.url')),

    /*
    |--------------------------------------------------------------------------
    | Output Directory
    |--------------------------------------------------------------------------
    |
    | The directory where generated files will be saved.
    | This should be within your public directory for web access.
    |
    */
    'outDir' => env('GEO_FRIENDLY_OUTPUT_DIR', public_path()),

    /*
    |--------------------------------------------------------------------------
    | Site Information
    |--------------------------------------------------------------------------
    |
    | Basic information about your website that will be included in
    | the generated files.
    |
    */
    'siteName' => env('GEO_FRIENDLY_SITE_NAME', config('app.name')),
    'siteDescription' => env('GEO_FRIENDLY_DESCRIPTION', ''),
    'contactEmail' => env('GEO_FRIENDLY_CONTACT_EMAIL', config('mail.from.address')),

    /*
    |--------------------------------------------------------------------------
    | Generators Configuration
    |--------------------------------------------------------------------------
    |
    | Enable or disable specific file generators.
    |
    */
    'generators' => [
        'llmsTxt' => env('GEO_FRIENDLY_LLMSTXT', true),
        'robotsTxt' => env('GEO_FRIENDLY_ROBOTSTXT', true),
        'sitemap' => env('GEO_FRIENDLY_SITEMAP', true),
        'docsJson' => env('GEO_FRIENDLY_DOCSJSON', true),
        'aiIndex' => env('GEO_FRIENDLY_AIINDEX', true),
        'llmsFullTxt' => env('GEO_FRIENDLY_LLMFULLTXT', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | URLs to Index
    |--------------------------------------------------------------------------
    |
    | List of URLs to include in generated files.
    | You can provide a static array or a closure that returns an array.
    |
    */
    'urls' => env('GEO_FRIENDLY_URLS', function () {
        // Auto-discover routes from Laravel
        $urls = [config('app.url')];

        // Add all named routes
        foreach (\Route::getRoutes() as $route) {
            if ($route->getName() && !str_starts_with($route->getName(), 'generated::')) {
                try {
                    $urls[] = app('url')->to($route->uri);
                } catch (\Exception $e) {
                    // Skip routes that can't be resolved
                }
            }
        }

        return array_unique($urls);
    }),

    /*
    |--------------------------------------------------------------------------
    | Sitemap Configuration
    |--------------------------------------------------------------------------
    |
    | Specific settings for XML sitemap generation.
    |
    */
    'sitemap' => [
        'changeFreq' => env('GEO_FRIENDLY_CHANGEFREQ', 'weekly'),
        'priority' => env('GEO_FRIENDLY_PRIORITY', '0.8'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Robots.txt Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for robots.txt generation.
    |
    */
    'robots' => [
        'userAgent' => '*',
        'disallow' => [
            '/admin',
            '/login',
            '/api',
        ],
        'sitemap' => true, // Include sitemap reference
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto Generation
    |--------------------------------------------------------------------------
    |
    | Automatically generate GEO files on a schedule.
    |
    */
    'auto_generate' => env('GEO_FRIENDLY_AUTO_GENERATE', true),
    'schedule' => env('GEO_FRIENDLY_SCHEDULE', '0 2 * * *'), // Daily at 2 AM

    /*
    |--------------------------------------------------------------------------
    | Auto Generate on Content Change
    |--------------------------------------------------------------------------
    |
    | Automatically regenerate files when specified models are saved or deleted.
    | Set to true to observe all Eloquent models, or specify an array of models.
    |
    */
    'auto_generate_on_change' => env('GEO_FRIENDLY_AUTO_GENERATE_ON_CHANGE', false),
    'observe_models' => [
        // App\Models\User::class,
        // App\Models\Post::class,
        // App\Models\Page::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Additional Metadata
    |--------------------------------------------------------------------------
    |
    | Additional metadata to include in generated files.
    |
    */
    'metadata' => [
        'platform' => 'Laravel',
        'version' => app()->version(),
    ],

];
