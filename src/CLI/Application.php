<?php

declare(strict_types=1);

namespace GeoFriendly\CLI;

use GeoFriendly\CLI\Command\GenerateCommand;
use GeoFriendly\CLI\Command\InitCommand;
use GeoFriendly\CLI\Command\CheckCommand;
use GeoFriendly\CLI\Command\ReportCommand;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Symfony\Component\Console\CommandLoader\ContainerCommandLoader;

/**
 * Geo-Friendly CLI Application
 *
 * This is the main CLI application for Geo-Friendly.
 * It provides a command-line interface for generating geo-friendly files.
 */
class Application
{
    private const VERSION = '0.1.0';

    /**
     * Run the CLI application
     *
     * @param int $exitCode Optional exit code (for testing)
     * @return int
     */
    public function run(?int $exitCode = null): int
    {
        $app = new SymfonyApplication('Geo-Friendly', self::VERSION);

        // Register commands
        $commands = $this->getCommands();
        foreach ($commands as $command) {
            $app->add($command);
        }

        return $app->run();
    }

    /**
     * Get the list of commands to register
     *
     * @return array<string, object>
     */
    private function getCommands(): array
    {
        return [
            new GenerateCommand(),
            new InitCommand(),
            new CheckCommand(),
            new ReportCommand(),
        ];
    }
}
