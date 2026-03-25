<?php

declare(strict_types=1);

namespace GeoFriendly\Audit;

/**
 * File Presence Auditor
 *
 * Checks which GEO (Generative Engine Optimization) files exist
 * and calculates a presence score.
 */
class FilePresenceAuditor implements AuditorInterface
{
    /**
     * Standard GEO files that should be present
     *
     * @var array<string, array{name: string, description: string, weight: float}>
     */
    private const GEO_FILES = [
        'robots.txt' => [
            'name' => 'robots.txt',
            'description' => 'Robots exclusion standard file',
            'weight' => 1.0,
        ],
        'llms.txt' => [
            'name' => 'llms.txt',
            'description' => 'LLM information file',
            'weight' => 1.5,
        ],
        'llms-full.txt' => [
            'name' => 'llms-full.txt',
            'description' => 'Detailed LLM information file',
            'weight' => 1.0,
        ],
        'sitemap.xml' => [
            'name' => 'sitemap.xml',
            'description' => 'XML sitemap',
            'weight' => 1.5,
        ],
        'docs.json' => [
            'name' => 'docs.json',
            'description' => 'Documentation index',
            'weight' => 1.0,
        ],
        'ai-index.json' => [
            'name' => 'ai-index.json',
            'description' => 'AI-friendly content index',
            'weight' => 1.5,
        ],
        'schema.json' => [
            'name' => 'schema.json',
            'description' => 'Schema.org structured data',
            'weight' => 1.0,
        ],
    ];

    private string $outputDirectory;

    /**
     * Constructor
     *
     * @param string $outputDirectory Directory to check for GEO files
     */
    public function __construct(string $outputDirectory = './public')
    {
        $this->outputDirectory = rtrim($outputDirectory, '/');
    }

    /**
     * Audit the presence of GEO files
     *
     * @return AuditResult
     */
    public function audit(): AuditResult
    {
        $presentFiles = [];
        $missingFiles = [];
        $totalWeight = 0.0;
        $achievedWeight = 0.0;

        foreach (self::GEO_FILES as $file => $info) {
            $totalWeight += $info['weight'];
            $filePath = $this->outputDirectory . '/' . $file;

            if (file_exists($filePath) && is_file($filePath)) {
                $presentFiles[] = [
                    'file' => $file,
                    'description' => $info['description'],
                    'weight' => $info['weight'],
                ];
                $achievedWeight += $info['weight'];
            } else {
                $missingFiles[] = [
                    'file' => $file,
                    'description' => $info['description'],
                    'weight' => $info['weight'],
                ];
            }
        }

        // Calculate score (0-100)
        $score = $totalWeight > 0 ? ($achievedWeight / $totalWeight) * 100 : 0;

        return new AuditResult(
            name: 'file_presence',
            score: (int) round($score),
            details: [
                'present' => $presentFiles,
                'missing' => $missingFiles,
                'total_files' => count(self::GEO_FILES),
                'present_count' => count($presentFiles),
                'missing_count' => count($missingFiles),
            ]
        );
    }

    /**
     * Get the list of all expected GEO files
     *
     * @return array<string>
     */
    public static function getExpectedFiles(): array
    {
        return array_keys(self::GEO_FILES);
    }

    /**
     * Get file information
     *
     * @param string $filename
     * @return array{name: string, description: string, weight: float}|null
     */
    public static function getFileInfo(string $filename): ?array
    {
        return self::GEO_FILES[$filename] ?? null;
    }

    /**
     * Get the auditor name
     *
     * @return string
     */
    public function getName(): string
    {
        return 'file_presence';
    }
}
