<?php

declare(strict_types=1);

namespace GeoFriendly\Audit;

use GeoFriendly\AuditorInterface;

/**
 * GEO Score Calculator
 *
 * Combines multiple auditor results into a final GEO readiness score.
 */
class GeoScoreCalculator
{
    /**
     * Default weights for different audit types
     *
     * @var array<string, float>
     */
    private const DEFAULT_WEIGHTS = [
        'file_presence' => 0.4,      // 40% weight for file presence
        'content_quality' => 0.3,    // 30% weight for content quality
        'metadata_completeness' => 0.2, // 20% weight for metadata
        'technical_compliance' => 0.1,   // 10% weight for technical compliance
    ];

    /**
     * Calculate combined GEO score from multiple audit results
     *
     * @param array<AuditResult> $results Array of audit results
     * @param array<string, float> $weights Optional custom weights
     * @return CalculatedScore
     */
    public function calculate(array $results, array $weights = []): CalculatedScore
    {
        $finalWeights = array_merge(self::DEFAULT_WEIGHTS, $weights);
        $totalWeight = 0.0;
        $weightedScore = 0.0;
        $scoreBreakdown = [];
        $recommendations = [];

        foreach ($results as $result) {
            $weight = $finalWeights[$result->name] ?? 0.1;
            $weightedScore += $result->score * $weight;
            $totalWeight += $weight;

            $scoreBreakdown[$result->name] = [
                'score' => $result->score,
                'weight' => $weight,
                'weighted_score' => $result->score * $weight,
                'passed' => $result->passed(),
            ];

            // Collect recommendations
            if (!$result->passed() && !empty($result->messages)) {
                $recommendations = array_merge($recommendations, $result->messages);
            }
        }

        // Calculate final score (normalize if weights don't sum to 1)
        $finalScore = $totalWeight > 0 ? ($weightedScore / $totalWeight) : 0;

        // Determine grade
        $grade = $this->calculateGrade((int) round($finalScore));

        return new CalculatedScore(
            overallScore: (int) round($finalScore),
            grade: $grade,
            breakdown: $scoreBreakdown,
            recommendations: $recommendations,
            rawResults: $results
        );
    }

    /**
     * Calculate letter grade from numeric score
     *
     * @param int $score Score (0-100)
     * @return string Letter grade (A-F)
     */
    private function calculateGrade(int $score): string
    {
        if ($score >= 90) {
            return 'A';
        }
        if ($score >= 80) {
            return 'B';
        }
        if ($score >= 70) {
            return 'C';
        }
        if ($score >= 60) {
            return 'D';
        }
        return 'F';
    }

    /**
     * Get default weights
     *
     * @return array<string, float>
     */
    public static function getDefaultWeights(): array
    {
        return self::DEFAULT_WEIGHTS;
    }
}
