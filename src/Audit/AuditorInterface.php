<?php

declare(strict_types=1);

namespace GeoFriendly\Audit;

/**
 * Auditor Interface
 *
 * All auditors must implement this interface.
 */
interface AuditorInterface
{
    /**
     * Run the audit
     *
     * @return AuditResult
     */
    public function audit(): AuditResult;

    /**
     * Get the auditor name
     *
     * @return string
     */
    public function getName(): string;
}
