<?php

declare(strict_types=1);

namespace GeoFriendly\Generator;

use GeoFriendly\Config\GeofriendlyConfig;

/**
 * Interface for all generators.
 *
 * Generators are responsible for creating specific output files
 * that help make websites more discoverable by AI answer engines.
 */
interface GeneratorInterface
{
    /**
     * Generate the file content.
     *
     * @param GeofriendlyConfig $config The configuration object
     * @return string The generated content
     */
    public function generate(GeofriendlyConfig $config): string;

    /**
     * Get the output filename.
     *
     * @return string The filename (relative to output directory)
     */
    public function getFilename(): string;
}
