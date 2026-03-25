<?php

declare(strict_types=1);

namespace GeoFriendly;

use GeoFriendly\Config\GeofriendlyConfig;
use GeoFriendly\Generator\AiIndexGenerator;
use GeoFriendly\Generator\DocsJsonGenerator;
use GeoFriendly\Generator\GeneratorInterface;
use GeoFriendly\Generator\LlmsFullTxtGenerator;
use GeoFriendly\Generator\LlmsTxtGenerator;
use GeoFriendly\Generator\RobotsTxtGenerator;
use GeoFriendly\Generator\SchemaGenerator;
use GeoFriendly\Generator\SitemapGenerator;
use GeoFriendly\Utils\FileHelper;
use InvalidArgumentException;

/**
 * GeoFriendly Main Class
 *
 * This is the main entry point for generating geo-friendly files.
 * It manages generators and orchestrates the generation process.
 */
class GeoFriendly
{
    private GeofriendlyConfig $config;

    /**
     * @var array<string, GeneratorInterface>
     */
    private array $generators = [];

    /**
     * Constructor
     *
     * @param array<string, mixed> $config Configuration array
     */
    public function __construct(array $config = [])
    {
        $this->config = new GeofriendlyConfig($config);
        $this->registerDefaultGenerators();
    }

    /**
     * Generate all enabled files
     *
     * @return array{0: array<string>, 1: array<string>} [generated files, errors]
     */
    public function generate(): array
    {
        $generated = [];
        $errors = [];

        // Create output directory if it doesn't exist
        if (!is_dir($this->config->outDir)) {
            if (!mkdir($this->config->outDir, 0755, true)) {
                throw new InvalidArgumentException(
                    sprintf('Failed to create output directory: %s', $this->config->outDir)
                );
            }
        }

        // Run each enabled generator
        foreach ($this->generators as $name => $generator) {
            if (!$this->isGeneratorEnabled($generator->getFilename())) {
                continue;
            }

            try {
                $content = $generator->generate($this->config);
                $filename = $generator->getFilename();
                $outputPath = $this->config->outDir . '/' . $filename;

                // Write file
                FileHelper::writeFile($outputPath, $content);
                $generated[] = $filename;

            } catch (\Exception $e) {
                $errors[] = sprintf(
                    '%s: %s',
                    $name,
                    $e->getMessage()
                );
            }
        }

        return [$generated, $errors];
    }

    /**
     * Register a custom generator
     *
     * @param string $name Generator name
     * @param GeneratorInterface $generator Generator instance
     */
    public function registerGenerator(string $name, GeneratorInterface $generator): void
    {
        $this->generators[$name] = $generator;
    }

    /**
     * Check if a generator is enabled based on its output filename
     *
     * @param string $filename Output filename from generator
     * @return bool
     */
    private function isGeneratorEnabled(string $filename): bool
    {
        // Map filenames to config keys
        $generatorMap = [
            'robots.txt' => 'robotsTxt',
            'llms.txt' => 'llmsTxt',
            'llms-full.txt' => 'llmsFullTxt',
            'sitemap.xml' => 'sitemap',
            'docs.json' => 'docsJson',
            'ai-index.json' => 'aiIndex',
            'schema.json' => 'schema',
        ];

        $configKey = $generatorMap[$filename] ?? null;

        if ($configKey === null) {
            return true; // Unknown generators are enabled by default
        }

        return $this->config->generators[$configKey] ?? true;
    }

    /**
     * Register all default generators
     */
    private function registerDefaultGenerators(): void
    {
        $this->generators = [
            'robotsTxt' => new RobotsTxtGenerator(),
            'llmsTxt' => new LlmsTxtGenerator(),
            'llmsFullTxt' => new LlmsFullTxtGenerator(),
            'sitemap' => new SitemapGenerator(),
            'docsJson' => new DocsJsonGenerator(),
            'aiIndex' => new AiIndexGenerator(),
            'schema' => new SchemaGenerator(),
        ];
    }

    /**
     * Get the configuration object
     *
     * @return GeofriendlyConfig
     */
    public function getConfig(): GeofriendlyConfig
    {
        return $this->config;
    }

    /**
     * Get registered generators
     *
     * @return array<string, GeneratorInterface>
     */
    public function getGenerators(): array
    {
        return $this->generators;
    }
}
