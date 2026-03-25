<?php

declare(strict_types=1);

namespace GeoFriendly\CLI\Command;

use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Init Command
 *
 * This command creates a geofriendly.yaml configuration file
 * in the current directory with all available options documented.
 */
class InitCommand extends Command
{
    private const NAME = 'init';
    private const DESCRIPTION = 'Create a geofriendly.yaml configuration file';
    private const CONFIG_FILENAME = 'geofriendly.yaml';

    protected function configure(): void
    {
        $this
            ->setName(self::NAME)
            ->setDescription(self::DESCRIPTION)
            ->setHelp('This command creates a new geofriendly.yaml configuration file in the current directory.')
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Overwrite existing configuration file if it exists'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $force = (bool) $input->getOption('force');
        $configPath = getcwd() . '/' . self::CONFIG_FILENAME;

        // Check if file exists
        if (file_exists($configPath) && !$force) {
            $io->error(sprintf(
                'Configuration file already exists: %s. Use --force to overwrite.',
                self::CONFIG_FILENAME
            ));
            return Command::FAILURE;
        }

        // Generate the configuration template
        $template = $this->generateConfigTemplate();

        // Write the file
        try {
            file_put_contents($configPath, $template);
            $io->success(sprintf('Configuration file created: %s', self::CONFIG_FILENAME));
            $io->note('Edit this file to configure your geo-friendly settings.');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error(sprintf('Failed to create configuration file: %s', $e->getMessage()));
            return Command::FAILURE;
        }
    }

    /**
     * Generate the configuration template with comments
     *
     * @return string
     */
    private function generateConfigTemplate(): string
    {
        return <<<'YAML'
# Geo-Friendly Configuration
# This file controls the generation of AI/LLM-friendly files for your website.

# Basic site information
title: "My Site"
url: "https://example.com"
description: "A brief description of your website"

# Directory settings
outDir: "./public"          # Output directory for generated files
contentDir: "./content"      # Directory containing your content files

# Generator settings
# Control which files are generated
generators:
  robotsTxt: true      # Generate robots.txt
  llmsTxt: true        # Generate llms.txt (basic LLM information)
  llmsFullTxt: true    # Generate llms-full.txt (detailed LLM information)
  rawMarkdown: true    # Generate raw markdown files
  manifest: true       # Generate manifest.json
  sitemap: true        # Generate sitemap.xml
  aiIndex: true        # Generate ai-index.json
  schema: true         # Generate schema.json

# Robots.txt configuration
robots:
  # User agent rules
  userAgent: "*"
  # Disallow paths (add paths you want to block)
  disallow:
    - "/admin/"
    - "/private/"
  # Allow paths (overrides disallow)
  allow:
    - "/public/"
  # Sitemap location (auto-generated from URL)
  sitemap: null  # Will default to https://example.com/sitemap.xml

# Widget configuration
widget:
  enabled: false
  position: "bottom-right"  # Options: top-left, top-right, bottom-left, bottom-right
  theme: "light"            # Options: light, dark, auto

# Schema.org configuration
schema:
  type: "WebSite"           # Schema type: WebSite, Organization, etc.
  organization:             # Organization data (if applicable)
    name: ""
    url: ""
    logo: ""
    sameAs: []              # Social media links

# Open Graph configuration
og:
  type: "website"
  image: ""                 # Default OG image
  twitterCard: "summary_large_image"

# OpenAI API configuration (optional)
# Used for AI-powered content generation
openai:
  apiKey: ""                # Your OpenAI API key
  model: "gpt-4"           # Model to use for content generation
  maxTokens: 2000          # Maximum tokens for generation

# Content optimization
# These settings affect how content is processed
content:
  # Include/exclude file patterns
  include:
    - "*.md"
    - "*.html"
  exclude:
    - "draft/*"
    - "private/*"

# Citability settings
# These help improve your content's citability by AI systems
citability:
  includeAuthors: true      # Include author information
  includeDates: true        # Include publication dates
  includeTags: true         # Include tags/categories
  includeMetadata: true     # Include additional metadata

YAML;
    }
}
