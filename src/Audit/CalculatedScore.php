<?php

declare(strict_types=1);

namespace GeoFriendly\Audit;

/**
 * Calculated Score
 *
 * Represents the final calculated GEO score with breakdown and recommendations.
 */
class CalculatedScore
{
    /**
     * Constructor
     *
     * @param int $overallScore Overall GEO score (0-100)
     * @param string $grade Letter grade (A-F)
     * @param array<string, array{score: int, weight: float, weighted_score: float, passed: bool}> $breakdown
     * @param array<string> $recommendations Recommendations for improvement
     * @param array<AuditResult> $rawResults Raw audit results
     */
    public function __construct(
        public int $overallScore,
        public string $grade,
        public array $breakdown,
        public array $recommendations,
        public array $rawResults
    ) {}

    /**
     * Check if the overall score is passing
     *
     * @param int $threshold Passing threshold (default: 70)
     * @return bool
     */
    public function isPassing(int $threshold = 70): bool
    {
        return $this->overallScore >= $threshold;
    }

    /**
     * Convert to array
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'overall_score' => $this->overallScore,
            'grade' => $this->grade,
            'passing' => $this->isPassing(),
            'breakdown' => $this->breakdown,
            'recommendations' => $this->recommendations,
        ];
    }

    /**
     * Get human-readable status message
     *
     * @return string
     */
    public function getStatusMessage(): string
    {
        if ($this->overallScore >= 90) {
            return 'Excellent! Your site is well optimized for AI/LLM discovery.';
        }
        if ($this->overallScore >= 80) {
            return 'Good! Your site has strong GEO fundamentals.';
        }
        if ($this->overallScore >= 70) {
            return 'Fair. Some improvements could boost your GEO score.';
        }
        if ($this->overallScore >= 60) {
            return 'Needs improvement. Consider adding missing GEO files.';
        }
        return 'Poor. Your site needs significant GEO improvements.';
    }
}
