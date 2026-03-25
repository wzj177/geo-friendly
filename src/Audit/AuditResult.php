<?php

declare(strict_types=1);

namespace GeoFriendly\Audit;

/**
 * Audit Result
 *
 * Represents the result of an audit operation.
 */
readonly class AuditResult
{
    /**
     * Constructor
     *
     * @param string $name The name of the audit
     * @param int $score The audit score (0-100)
     * @param array<string, mixed> $details Detailed audit information
     * @param array<string> $messages Optional messages or recommendations
     */
    public function __construct(
        public string $name,
        public int $score,
        public array $details = [],
        public array $messages = []
    ) {}

    /**
     * Check if the audit passed (score >= threshold)
     *
     * @param int $threshold Passing threshold (default: 70)
     * @return bool
     */
    public function passed(int $threshold = 70): bool
    {
        return $this->score >= $threshold;
    }

    /**
     * Convert result to array
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'score' => $this->score,
            'passed' => $this->passed(),
            'details' => $this->details,
            'messages' => $this->messages,
        ];
    }
}
