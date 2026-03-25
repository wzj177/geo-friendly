<?php

declare(strict_types=1);

namespace GeoFriendly\CLI\Command;

use GeoFriendly\Audit\AuditResult;
use GeoFriendly\Audit\FilePresenceAuditor;
use GeoFriendly\Audit\GeoScoreCalculator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Report Command
 *
 * This command generates a comprehensive GEO report with
 * citability scores and platform-specific optimization hints.
 */
class ReportCommand extends Command
{
    private const NAME = 'report';
    private const DESCRIPTION = 'Generate a comprehensive GEO report with citability scores and optimization hints';

    /**
     * Platform-specific optimization hints
     *
     * @var array<string, array{priority: string, hints: array<string>}>
     */
    private const PLATFORM_HINTS = [
        'openai' => [
            'priority' => 'high',
            'hints' => [
                'Ensure llms.txt is present and properly formatted',
                'Include detailed AI index (ai-index.json) for better context',
                'Add schema.org markup for structured data',
                'Implement proper content categorization',
            ],
        ],
        'google' => [
            'priority' => 'high',
            'hints' => [
                'Submit sitemap.xml to Google Search Console',
                'Ensure robots.txt allows proper crawling',
                'Implement structured data (schema.json)',
                'Use proper meta tags and Open Graph data',
            ],
        ],
        'bing' => [
            'priority' => 'medium',
            'hints' => [
                'Submit sitemap.xml to Bing Webmaster Tools',
                'Ensure robots.txt is properly configured',
                'Add structured data markup',
            ],
        ],
        'claude' => [
            'priority' => 'high',
            'hints' => [
                'Provide comprehensive llms.txt with content overview',
                'Include detailed documentation in docs.json',
                'Add AI index with semantic relationships',
                'Ensure content is well-structured and accessible',
            ],
        ],
        'perplexity' => [
            'priority' => 'medium',
            'hints' => [
                'Optimize llms.txt for real-time information',
                'Include recent content updates in sitemap',
                'Provide clear content attribution',
            ],
        ],
    ];

    /**
     * Citability factors
     *
     * @var array<string, array{factor: string, description: string, weight: float}>
     */
    private const CITABILITY_FACTORS = [
        'author_attribution' => [
            'factor' => 'Author Attribution',
            'description' => 'Clear author information for content',
            'weight' => 1.5,
        ],
        'publication_dates' => [
            'factor' => 'Publication Dates',
            'description' => 'Clear publication and modification dates',
            'weight' => 1.0,
        ],
        'content_structure' => [
            'factor' => 'Content Structure',
            'description' => 'Well-organized content with clear hierarchy',
            'weight' => 1.2,
        ],
        'metadata_completeness' => [
            'factor' => 'Metadata Completeness',
            'description' => 'Comprehensive metadata and descriptions',
            'weight' => 1.0,
        ],
        'source_attribution' => [
            'factor' => 'Source Attribution',
            'description' => 'Clear source and reference information',
            'weight' => 1.3,
        ],
        'accessibility' => [
            'factor' => 'Accessibility',
            'description' => 'Content is accessible and properly formatted',
            'weight' => 0.8,
        ],
    ];

    protected function configure(): void
    {
        $this
            ->setName(self::NAME)
            ->setDescription(self::DESCRIPTION)
            ->setHelp('This command generates a comprehensive GEO report including citability scores and platform-specific optimization recommendations.')
            ->addOption(
                'out',
                'o',
                InputOption::VALUE_REQUIRED,
                'Output directory to analyze (default: ./public)',
                './public'
            )
            ->addOption(
                'json',
                null,
                InputOption::VALUE_NONE,
                'Output report in JSON format'
            )
            ->addOption(
                'platform',
                'p',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Specific platforms to analyze (default: all)',
                []
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $outputDir = $input->getOption('out');
        $asJson = (bool) $input->getOption('json');
        $platforms = $input->getOption('platform');

        // Verify output directory exists
        if (!is_dir($outputDir)) {
            $io->error(sprintf('Output directory does not exist: %s', $outputDir));
            return Command::FAILURE;
        }

        // Generate report
        $io->section('Generating GEO Report');

        try {
            $report = $this->generateReport($outputDir, $platforms);

            // Output results
            if ($asJson) {
                $this->outputJson($io, $report);
            } else {
                $this->outputHumanReadable($io, $report);
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error(sprintf('Report generation failed: %s', $e->getMessage()));
            return Command::FAILURE;
        }
    }

    /**
     * Generate comprehensive GEO report
     *
     * @param string $outputDir
     * @param array<string> $specificPlatforms
     * @return GeoReport
     */
    private function generateReport(string $outputDir, array $specificPlatforms = []): GeoReport
    {
        // Run audits
        $fileAuditor = new FilePresenceAuditor($outputDir);
        $fileAudit = $fileAuditor->audit();

        $calculator = new GeoScoreCalculator();
        $score = $calculator->calculate([$fileAudit]);

        // Calculate citability score
        $citabilityScore = $this->calculateCitabilityScore($outputDir);

        // Get platform hints
        $platformsToCheck = !empty($specificPlatforms) ? $specificPlatforms : array_keys(self::PLATFORM_HINTS);
        $platformRecommendations = $this->getPlatformRecommendations($platformsToCheck, $fileAudit);

        return new GeoReport(
            overallScore: $score->overallScore,
            grade: $score->grade,
            citabilityScore: $citabilityScore,
            filePresence: $fileAudit,
            platformRecommendations: $platformRecommendations,
            generatedAt: new \DateTimeImmutable()
        );
    }

    /**
     * Calculate citability score based on various factors
     *
     * @param string $outputDir
     * @return int Score (0-100)
     */
    private function calculateCitabilityScore(string $outputDir): int
    {
        $totalWeight = 0.0;
        $achievedWeight = 0.0;

        foreach (self::CITABILITY_FACTORS as $key => $factor) {
            $totalWeight += $factor['weight'];
            $achievedWeight += $this->assessCitabilityFactor($key, $outputDir) * $factor['weight'];
        }

        return $totalWeight > 0 ? (int) round(($achievedWeight / $totalWeight) * 100) : 0;
    }

    /**
     * Assess a specific citability factor
     *
     * @param string $factor
     * @param string $outputDir
     * @return float Score (0.0-1.0)
     */
    private function assessCitabilityFactor(string $factor, string $outputDir): float
    {
        return match ($factor) {
            'author_attribution' => $this->checkFileExists($outputDir, 'docs.json') ? 0.8 : 0.3,
            'publication_dates' => $this->checkFileExists($outputDir, 'sitemap.xml') ? 0.9 : 0.2,
            'content_structure' => $this->checkFileExists($outputDir, 'llms.txt') ? 0.85 : 0.4,
            'metadata_completeness' => $this->checkFileExists($outputDir, 'schema.json') ? 0.9 : 0.3,
            'source_attribution' => $this->checkFileExists($outputDir, 'ai-index.json') ? 0.85 : 0.4,
            'accessibility' => $this->checkFileExists($outputDir, 'robots.txt') ? 0.95 : 0.5,
            default => 0.5,
        };
    }

    /**
     * Check if a file exists in the output directory
     *
     * @param string $outputDir
     * @param string $filename
     * @return bool
     */
    private function checkFileExists(string $outputDir, string $filename): bool
    {
        return file_exists($outputDir . '/' . $filename);
    }

    /**
     * Get platform-specific recommendations
     *
     * @param array<string> $platforms
     * @param AuditResult $fileAudit
     * @return array<string, array{priority: string, score: int, hints: array<string>}>
     */
    private function getPlatformRecommendations(array $platforms, AuditResult $fileAudit): array
    {
        $recommendations = [];

        foreach ($platforms as $platform) {
            if (!isset(self::PLATFORM_HINTS[$platform])) {
                continue;
            }

            $hints = self::PLATFORM_HINTS[$platform];
            $relevantHints = [];

            // Filter hints based on missing files
            $missingFiles = array_column($fileAudit->details['missing'] ?? [], 'file');

            foreach ($hints['hints'] as $hint) {
                // Add hint if it's relevant to missing files or is general advice
                if ($this->isHintRelevant($hint, $missingFiles)) {
                    $relevantHints[] = $hint;
                }
            }

            // Calculate platform score based on file presence
            $platformScore = $this->calculatePlatformScore($platform, $fileAudit);

            $recommendations[$platform] = [
                'priority' => $hints['priority'],
                'score' => $platformScore,
                'hints' => $relevantHints ?: $hints['hints'], // Fallback to all hints if none are relevant
            ];
        }

        return $recommendations;
    }

    /**
     * Check if a hint is relevant given the missing files
     *
     * @param string $hint
     * @param array<string> $missingFiles
     * @return bool
     */
    private function isHintRelevant(string $hint, array $missingFiles): bool
    {
        // If all files are present, show general hints
        if (empty($missingFiles)) {
            return true;
        }

        // Check if hint mentions a missing file
        foreach ($missingFiles as $file) {
            if (str_contains(strtolower($hint), str_replace(['.json', '.txt'], '', strtolower($file)))) {
                return true;
            }
        }

        // Include general hints
        $generalKeywords = ['schema', 'structure', 'format', 'metadata', 'optimize', 'ensure'];
        foreach ($generalKeywords as $keyword) {
            if (str_contains(strtolower($hint), $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Calculate platform-specific score
     *
     * @param string $platform
     * @param AuditResult $fileAudit
     * @return int
     */
    private function calculatePlatformScore(string $platform, AuditResult $fileAudit): int
    {
        $requiredFiles = match ($platform) {
            'openai', 'claude' => ['llms.txt', 'ai-index.json', 'docs.json'],
            'google' => ['sitemap.xml', 'robots.txt', 'schema.json'],
            'bing' => ['sitemap.xml', 'robots.txt'],
            'perplexity' => ['llms.txt', 'sitemap.xml'],
            default => [],
        };

        if (empty($requiredFiles)) {
            return $fileAudit->score;
        }

        $presentFiles = array_column($fileAudit->details['present'] ?? [], 'file');
        $presentCount = count(array_intersect($requiredFiles, $presentFiles));
        $totalRequired = count($requiredFiles);

        return $totalRequired > 0 ? (int) round(($presentCount / $totalRequired) * 100) : 0;
    }

    /**
     * Output report in human-readable format
     *
     * @param SymfonyStyle $io
     * @param GeoReport $report
     */
    private function outputHumanReadable(SymfonyStyle $io, GeoReport $report): void
    {
        // Header
        $io->section('GEO (Generative Engine Optimization) Report');
        $io->text(sprintf('Generated: %s', $report->generatedAt->format('Y-m-d H:i:s')));
        $io->newLine();

        // Overall Score
        $scoreColor = match (true) {
            $report->overallScore >= 90 => 'green',
            $report->overallScore >= 70 => 'blue',
            $report->overallScore >= 60 => 'yellow',
            default => 'red',
        };

        $io->writeln(
            sprintf(
                '<fg=%s;options=bold>GEO Readiness Score: %d/100 (Grade: %s)</>',
                $scoreColor,
                $report->overallScore,
                $report->grade
            )
        );

        // Citability Score
        $citabilityColor = match (true) {
            $report->citabilityScore >= 80 => 'green',
            $report->citabilityScore >= 60 => 'blue',
            default => 'yellow',
        };

        $io->writeln(
            sprintf(
                '<fg=%s>Citability Score: %d/100</>',
                $citabilityColor,
                $report->citabilityScore
            )
        );

        $io->newLine();

        // File Presence Summary
        $io->section('File Presence Summary');

        $present = $report->filePresence->details['present'] ?? [];
        $missing = $report->filePresence->details['missing'] ?? [];

        $io->text(sprintf('Present: %d/%d files', count($present), count($present) + count($missing)));

        if (!empty($present)) {
            foreach ($present as $file) {
                $io->writeln(sprintf('  <fg=green>✓</> %s', $file['file']));
            }
        }

        if (!empty($missing)) {
            $io->text('<fg=red>Missing files:</>');
            foreach ($missing as $file) {
                $io->writeln(sprintf('  <fg=red>✗</> %s - %s', $file['file'], $file['description']));
            }
        }

        $io->newLine();

        // Platform-Specific Recommendations
        $io->section('Platform Optimization Recommendations');

        foreach ($report->platformRecommendations as $platform => $data) {
            $priorityColor = match ($data['priority']) {
                'high' => 'red',
                'medium' => 'yellow',
                default => 'blue',
            };

            $io->writeln(
                sprintf(
                    '<fg=white;options=bold>%s</> [%s priority] - Score: %d/100',
                    ucfirst($platform),
                    sprintf('<fg=%s>%s</>', $priorityColor, strtoupper($data['priority'])),
                    $data['score']
                )
            );

            foreach ($data['hints'] as $hint) {
                $io->writeln(sprintf('  • %s', $hint));
            }

            $io->newLine();
        }

        // Summary
        $io->section('Summary');

        if ($report->overallScore >= 80) {
            $io->success('Your site has strong GEO fundamentals. Consider platform-specific optimizations for even better results.');
        } elseif ($report->overallScore >= 60) {
            $io->warning('Your site has basic GEO implementation. Add missing files and follow platform recommendations to improve.');
        } else {
            $io->error('Your site needs significant GEO improvements. Start by adding missing core files.');
        }
    }

    /**
     * Output report in JSON format
     *
     * @param SymfonyStyle $io
     * @param GeoReport $report
     */
    private function outputJson(SymfonyStyle $io, GeoReport $report): void
    {
        $data = [
            'generated_at' => $report->generatedAt->format('Y-m-d\TH:i:s\Z'),
            'overall_score' => $report->overallScore,
            'grade' => $report->grade,
            'citability_score' => $report->citabilityScore,
            'file_presence' => $report->filePresence->toArray(),
            'platform_recommendations' => $report->platformRecommendations,
        ];

        $io->writeln(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}

/**
 * GEO Report Value Object
 *
 * @readonly
 */
class GeoReport
{
    /**
     * Constructor
     *
     * @param int $overallScore Overall GEO score (0-100)
     * @param string $grade Letter grade (A-F)
     * @param int $citabilityScore Citability score (0-100)
     * @param AuditResult $filePresence File presence audit result
     * @param array<string, array{priority: string, score: int, hints: array<string>}> $platformRecommendations
     * @param \DateTimeImmutable $generatedWhen When the report was generated
     */
    public function __construct(
        public int $overallScore,
        public string $grade,
        public int $citabilityScore,
        public AuditResult $filePresence,
        public array $platformRecommendations,
        public \DateTimeImmutable $generatedAt
    ) {}
}
