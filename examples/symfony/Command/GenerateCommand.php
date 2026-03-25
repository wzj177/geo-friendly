<?php

declare(strict_types=1);

namespace GeoFriendly\Symfony\Command;

use GeoFriendly\GeoFriendly;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Console command to generate GEO files
 */
#[AsCommand(name: 'geofriendly:generate', description: 'Generate GEO-friendly files')]
class GenerateCommand extends Command
{
    private GeoFriendly $geo;

    public function __construct(GeoFriendly $geo)
    {
        parent::__construct();
        $this->geo = $geo;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Generate GEO-friendly files (llms.txt, robots.txt, sitemap.xml, docs.json)')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force generation even if files exist')
            ->addOption('verbose', 'v', InputOption::VALUE_NONE, 'Show detailed output')
            ->addOption('output', 'o', InputOption::VALUE_OPTIONAL, 'Custom output directory');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Geo-Friendly File Generator');

        if ($input->getOption('verbose')) {
            $io->section('Configuration');

            $config = $this->geo->getConfig();
            $io->definitionList(
                ['Site URL' => $config->siteUrl ?? 'N/A'],
                ['Output Directory' => $config->outDir ?? 'N/A'],
                ['Site Name' => $config->siteName ?? 'N/A'],
                ['LLMs.txt' => $config->generators['llmsTxt'] ?? false ? 'Yes' : 'No'],
                ['Robots.txt' => $config->generators['robotsTxt'] ?? false ? 'Yes' : 'No'],
                ['Sitemap' => $config->generators['sitemap'] ?? false ? 'Yes' : 'No'],
                ['Docs.json' => $config->generators['docsJson'] ?? false ? 'Yes' : 'No'],
                ['AI Index' => $config->generators['aiIndex'] ?? false ? 'Yes' : 'No'],
            );
            $io->newLine();
        }

        $io->text('Generating GEO-friendly files...');

        try {
            // Override output directory if specified
            if ($input->getOption('output')) {
                $config = $this->geo->getConfig();
                $config->outDir = $input->getOption('output');
            }

            // Generate files
            [$generated, $errors] = $this->geo->generate();

            if ($input->getOption('verbose')) {
                $io->newLine();
            }

            // Show results
            if (!empty($generated)) {
                $io->success('Successfully generated:');
                foreach ($generated as $file) {
                    $io->text("  ✓ {$file}");
                }
            }

            if (!empty($errors)) {
                $io->newLine();
                $io->error('Errors occurred:');
                foreach ($errors as $error) {
                    $io->text("  ✗ {$error}");
                }
                return Command::FAILURE;
            }

            $io->newLine();
            $io->success('GEO files generated successfully!');
            $io->text('Location: ' . $this->geo->getConfig()->outDir);

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error('Generation failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
