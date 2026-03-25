<?php

declare(strict_types=1);

namespace GeoFriendly\CLI\Command;

use GeoFriendly\Config\GeofriendlyConfig;
use GeoFriendly\Config\GeofriendlyConfigLoader;
use GeoFriendly\Generator\AiIndexGenerator;
use GeoFriendly\Generator\DocsJsonGenerator;
use GeoFriendly\Generator\LlmsFullTxtGenerator;
use GeoFriendly\Generator\LlmsTxtGenerator;
use GeoFriendly\Generator\RobotsTxtGenerator;
use GeoFriendly\Generator\SchemaGenerator;
use GeoFriendly\Generator\SitemapGenerator;
use GeoFriendly\GeoFriendly;
use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Generate Command
 *
 * This command generates all geo-friendly files based on configuration.
 */
class GenerateCommand extends Command
{
    private const NAME = 'generate';
    private const DESCRIPTION = 'Generate geo-friendly files for your website';

    private GeofriendlyConfigLoader $configLoader;

    public function __construct(?GeofriendlyConfigLoader $configLoader = null)
    {
        parent::__construct(self::NAME);
        $this->configLoader = $configLoader ?? new GeofriendlyConfigLoader();
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::DESCRIPTION)
            ->setHelp('This command generates all geo-friendly files (robots.txt, llms.txt, sitemap.xml, etc.) based on your configuration.')
            ->addOption(
                'url',
                null,
                InputOption::VALUE_REQUIRED,
                'Website URL (e.g., https://example.com)'
            )
            ->addOption(
                'title',
                null,
                InputOption::VALUE_REQUIRED,
                'Website title'
            )
            ->addOption(
                'description',
                null,
                InputOption::VALUE_REQUIRED,
                'Website description'
            )
            ->addOption(
                'out',
                null,
                InputOption::VALUE_REQUIRED,
                'Output directory (default: ./public)'
            )
            ->addOption(
                'content-dir',
                null,
                InputOption::VALUE_REQUIRED,
                'Content directory (default: ./content)'
            )
            ->addOption(
                'config',
                'c',
                InputOption::VALUE_REQUIRED,
                'Path to configuration file (YAML format)'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Load configuration
        try {
            $config = $this->loadConfig($input, $io);
        } catch (InvalidArgumentException $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        // Display configuration summary
        $this->displayConfigSummary($io, $config);

        // Create GeoFriendly instance and generate
        $geoFriendly = new GeoFriendly([
            'title' => $config->title,
            'url' => $config->url,
            'description' => $config->description,
            'outDir' => $config->outDir,
            'contentDir' => $config->contentDir,
            'generators' => $config->generators,
        ]);

        $io->section('Generating Files');

        try {
            [$generated, $errors] = $geoFriendly->generate();

            // Display results
            if (!empty($generated)) {
                $io->success('Generated files:');
                foreach ($generated as $file) {
                    $io->writeln("  ✓ $file");
                }
            } else {
                $io->note('No files were generated (all generators disabled)');
            }

            if (!empty($errors)) {
                $io->warning('Some errors occurred:');
                foreach ($errors as $error) {
                    $io->writeln("  ✗ $error");
                }
                return Command::FAILURE;
            }

            $io->success('All files generated successfully!');
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error('Generation failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Load configuration from file or CLI options
     *
     * @param InputInterface $input
     * @param SymfonyStyle $io
     * @return GeofriendlyConfig
     * @throws InvalidArgumentException
     */
    private function loadConfig(InputInterface $input, SymfonyStyle $io): GeofriendlyConfig
    {
        $configPath = $input->getOption('config');

        // Load from file if specified
        if ($configPath !== null) {
            if (!file_exists($configPath)) {
                throw new InvalidArgumentException(sprintf('Configuration file not found: %s', $configPath));
            }
            return $this->configLoader->load($configPath);
        }

        // Build config from CLI options
        $config = [];

        $url = $input->getOption('url');
        if ($url !== null) {
            $config['url'] = $url;
        }

        $title = $input->getOption('title');
        if ($title !== null) {
            $config['title'] = $title;
        }

        $description = $input->getOption('description');
        if ($description !== null) {
            $config['description'] = $description;
        }

        $outDir = $input->getOption('out');
        if ($outDir !== null) {
            $config['outDir'] = $outDir;
        }

        $contentDir = $input->getOption('content-dir');
        if ($contentDir !== null) {
            $config['contentDir'] = $contentDir;
        }

        // If no config file and no options provided, try to find default config
        if (empty($config)) {
            $defaultConfigPaths = [
                './geofriendly.yaml',
                './geofriendly.yml',
                './config/geofriendly.yaml',
                './config/geofriendly.yml',
            ];

            foreach ($defaultConfigPaths as $path) {
                if (file_exists($path)) {
                    $io->note("Using configuration file: $path");
                    return $this->configLoader->load($path);
                }
            }

            throw new InvalidArgumentException(
                'No configuration found. Please provide a --config file or specify options: --url, --title, etc.'
            );
        }

        return new GeofriendlyConfig($config);
    }

    /**
     * Display configuration summary
     *
     * @param SymfonyStyle $io
     * @param GeofriendlyConfig $config
     */
    private function displayConfigSummary(SymfonyStyle $io, GeofriendlyConfig $config): void
    {
        $io->section('Configuration');

        $io->definitionList(
            ['URL' => $config->url],
            ['Title' => $config->title],
            ['Description' => $config->description ?: '<none>'],
            ['Output Directory' => $config->outDir],
            ['Content Directory' => $config->contentDir],
        );

        // Show enabled generators
        $enabledGenerators = array_filter($config->generators, fn($v) => $v === true);
        $io->text('Enabled generators: ' . implode(', ', array_keys($enabledGenerators)));
    }
}
