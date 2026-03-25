<?php

declare(strict_types=1);

namespace GeoFriendly\Config;

use Symfony\Component\Yaml\Yaml;
use InvalidArgumentException;

class GeofriendlyConfigLoader
{
    /**
     * Load configuration from a YAML file
     *
     * @param string $path Path to the YAML configuration file
     * @return GeofriendlyConfig
     * @throws InvalidArgumentException If file doesn't exist or can't be parsed
     */
    public function load(string $path): GeofriendlyConfig
    {
        if (!file_exists($path)) {
            throw new InvalidArgumentException(sprintf('Configuration file not found: %s', $path));
        }

        if (!is_readable($path)) {
            throw new InvalidArgumentException(sprintf('Configuration file is not readable: %s', $path));
        }

        $content = file_get_contents($path);
        if ($content === false) {
            throw new InvalidArgumentException(sprintf('Failed to read configuration file: %s', $path));
        }

        try {
            $config = Yaml::parse($content);
        } catch (\Exception $e) {
            throw new InvalidArgumentException(
                sprintf('Failed to parse YAML configuration file: %s', $path),
                0,
                $e
            );
        }

        if (!is_array($config)) {
            throw new InvalidArgumentException(
                sprintf('Configuration file must contain a YAML object/array: %s', $path)
            );
        }

        return $this->loadFromArray($config);
    }

    /**
     * Load configuration from an array
     *
     * @param array<string, mixed> $config Configuration array
     * @return GeofriendlyConfig
     */
    public function loadFromArray(array $config): GeofriendlyConfig
    {
        return GeofriendlyConfig::fromArray($config);
    }
}
