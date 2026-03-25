<?php

declare(strict_types=1);

namespace GeoFriendly\CLI\Command;

use GeoFriendly\Audit\AuditResult;
use GeoFriendly\Audit\CalculatedScore;
use GeoFriendly\Audit\FilePresenceAuditor;
use GeoFriendly\Audit\GeoScoreCalculator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Check Command
 *
 * This command runs GEO (Generative Engine Optimization) audits
 * and displays the readiness score with missing files.
 */
class CheckCommand extends Command
{
    private const NAME = 'check';
    private const DESCRIPTION = 'Check GEO readiness and display score with missing files';

    protected function configure(): void
    {
        $this
            ->setName(self::NAME)
            ->setDescription(self::DESCRIPTION)
            ->setHelp('This command audits your site for GEO (Generative Engine Optimization) readiness and displays a score with recommendations.')
            ->addOption(
                'out',
                'o',
                InputOption::VALUE_REQUIRED,
                'Output directory to check (default: ./public)',
                './public'
            )
            ->addOption(
                'json',
                null,
                InputOption::VALUE_NONE,
                'Output results in JSON format'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $outputDir = $input->getOption('out');
        $asJson = (bool) $input->getOption('json');

        // Verify output directory exists
        if (!is_dir($outputDir)) {
            $io->error(sprintf('Output directory does not exist: %s', $outputDir));
            return Command::FAILURE;
        }

        // Run audits
        $io->section('Running GEO Audits');

        try {
            $results = $this->runAuditors($outputDir);
            $calculator = new GeoScoreCalculator();
            $score = $calculator->calculate($results);

            // Output results
            if ($asJson) {
                $this->outputJson($io, $score, $results);
            } else {
                $this->outputHumanReadable($io, $score, $results);
            }

            return $score->isPassing() ? Command::SUCCESS : Command::FAILURE;

        } catch (\Exception $e) {
            $io->error(sprintf('Audit failed: %s', $e->getMessage()));
            return Command::FAILURE;
        }
    }

    /**
     * Run all auditors
     *
     * @param string $outputDir
     * @return array<AuditResult>
     */
    private function runAuditors(string $outputDir): array
    {
        $results = [];

        // File Presence Auditor
        $fileAuditor = new FilePresenceAuditor($outputDir);
        $results[] = $fileAuditor->audit();

        // Future auditors can be added here
        // $contentAuditor = new ContentQualityAuditor($outputDir);
        // $results[] = $contentAuditor->audit();

        return $results;
    }

    /**
     * Output results in human-readable format
     *
     * @param SymfonyStyle $io
     * @param CalculatedScore $score
     * @param array<AuditResult> $results
     */
    private function outputHumanReadable(SymfonyStyle $io, CalculatedScore $score, array $results): void
    {
        // Overall score
        $io->section('GEO Readiness Score');

        if ($score->overallScore >= 90) {
            $scoreColor = 'green';
        } elseif ($score->overallScore >= 70) {
            $scoreColor = 'blue';
        } elseif ($score->overallScore >= 60) {
            $scoreColor = 'yellow';
        } else {
            $scoreColor = 'red';
        }

        $io->writeln(
            sprintf(
                '<fg=%s;options=bold>%s</> <fg=%s>%d/100</> (Grade: <fg=%s;options=bold>%s</>)',
                $scoreColor,
                'Overall Score:',
                $scoreColor,
                $score->overallScore,
                $scoreColor,
                $score->grade
            )
        );

        $io->text($score->getStatusMessage());
        $io->newLine();

        // Breakdown
        $io->section('Audit Breakdown');

        foreach ($results as $result) {
            $status = $result->passed() ? '<fg=green>✓ PASS</>' : '<fg=red>✗ FAIL</>';
            $io->text(sprintf('%s: %s - %d%%', $status, $result->name, $result->score));

            // Show details for file presence
            if ($result->name === 'file_presence' && isset($result->details['missing'])) {
                foreach ($result->details['missing'] as $missing) {
                    $io->writeln(sprintf('  • Missing: %s (%s)', $missing['file'], $missing['description']));
                }
            }
        }

        // Recommendations
        if (!empty($score->recommendations)) {
            $io->section('Recommendations');
            foreach ($score->recommendations as $recommendation) {
                $io->writeln('• ' . $recommendation);
            }
        }

        // Summary
        $io->newLine();
        if ($score->isPassing()) {
            $io->success('Your site meets GEO readiness standards!');
        } else {
            $io->warning('Your site needs improvements for better GEO readiness.');
        }
    }

    /**
     * Output results in JSON format
     *
     * @param SymfonyStyle $io
     * @param CalculatedScore $score
     * @param array<AuditResult> $results
     */
    private function outputJson(SymfonyStyle $io, CalculatedScore $score, array $results): void
    {
        $data = [
            'overall_score' => $score->overallScore,
            'grade' => $score->grade,
            'passing' => $score->isPassing(),
            'status_message' => $score->getStatusMessage(),
            'breakdown' => array_map(fn(AuditResult $r) => $r->toArray(), $results),
            'recommendations' => $score->recommendations,
        ];

        $io->writeln(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}
