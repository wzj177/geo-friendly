<?php

declare(strict_types=1);

namespace GeoFriendly\Symfony\EventListener;

use GeoFriendly\GeoFriendly;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Event listener for automatic GEO file generation
 */
class AutoGenerateListener
{
    private GeoFriendly $geo;
    private bool $enabled;
    private string $cacheKey = 'geofriendly_last_generated';
    private int $cacheTtl = 3600; // 1 hour

    public function __construct(GeoFriendly $geo, bool $enabled = false)
    {
        $this->geo = $geo;
        $this->enabled = $enabled;
    }

    /**
     * Generate files on kernel terminate
     *
     * @param KernelEvent $event
     */
    public function onKernelTerminate(KernelEvent $event): void
    {
        if (!$this->enabled) {
            return;
        }

        // Only generate on master requests
        if (!$event->isMainRequest()) {
            return;
        }

        // Check cache to avoid generating too frequently
        if ($this->shouldGenerate()) {
            $this->geo->generate();
            $this->updateLastGenerated();
        }
    }

    /**
     * Check if files should be generated
     *
     * @return bool
     */
    private function shouldGenerate(): bool
    {
        // In a real implementation, you would check cache here
        // For now, we'll just return true
        return true;
    }

    /**
     * Update last generated timestamp
     */
    private function updateLastGenerated(): void
    {
        // In a real implementation, you would update cache here
    }
}
