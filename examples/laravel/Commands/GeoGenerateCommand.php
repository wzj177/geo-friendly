<?php

declare(strict_types=1);

namespace GeoFriendly\Laravel\Commands;

use GeoFriendly\GeoFriendly;
use Illuminate\Console\Command;

/**
 * Artisan command to generate GEO files
 */
class GeoGenerateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geo:generate
                            {--force : Force generation even if files exist}
                            {--verbose : Show detailed output}
                            {--output= : Custom output directory}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate GEO-friendly files (llms.txt, robots.txt, sitemap.xml, docs.json)';

    /**
     * The GeoFriendly instance.
     *
     * @var GeoFriendly
     */
    protected GeoFriendly $geo;

    /**
     * Create a new command instance.
     *
     * @param GeoFriendly $geo
     */
    public function __construct(GeoFriendly $geo)
    {
        parent::__construct();
        $this->geo = $geo;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->info('Generating GEO-friendly files...');

        if ($this->option('verbose')) {
            $this->newLine();
            $this->info('Configuration:');
            $this->table(
                ['Setting', 'Value'],
                [
                    ['Site URL', config('geofriendly.siteUrl')],
                    ['Output Directory', config('geofriendly.outDir')],
                    ['Site Name', config('geofriendly.siteName')],
                    ['LLMs.txt', config('geofriendly.generators.llmsTxt') ? 'Yes' : 'No'],
                    ['Robots.txt', config('geofriendly.generators.robotsTxt') ? 'Yes' : 'No'],
                    ['Sitemap', config('geofriendly.generators.sitemap') ? 'Yes' : 'No'],
                    ['Docs.json', config('geofriendly.generators.docsJson') ? 'Yes' : 'No'],
                    ['AI Index', config('geofriendly.generators.aiIndex') ? 'Yes' : 'No'],
                ]
            );
            $this->newLine();
        }

        try {
            // Override output directory if specified
            if ($this->option('output')) {
                $config = $this->geo->getConfig();
                $config->outDir = $this->option('output');
            }

            // Generate files
            [$generated, $errors] = $this->geo->generate();

            if ($this->option('verbose')) {
                $this->newLine();
            }

            // Show results
            if (!empty($generated)) {
                $this->info('Successfully generated:');
                foreach ($generated as $file) {
                    $this->line('  ✓ ' . $file);
                }
            }

            if (!empty($errors)) {
                $this->newLine();
                $this->error('Errors occurred:');
                foreach ($errors as $error) {
                    $this->line('  ✗ ' . $error);
                }
                return 1;
            }

            $this->newLine();
            $this->info('GEO files generated successfully!');
            $this->info('Location: ' . $this->geo->getConfig()->outDir);

            return 0;

        } catch (\Exception $e) {
            $this->error('Generation failed: ' . $e->getMessage());
            return 1;
        }
    }
}
