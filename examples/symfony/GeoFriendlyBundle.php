<?php

declare(strict_types=1);

namespace GeoFriendly\Symfony;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * GeoFriendly Symfony Bundle
 *
 * Integrates the geo-friendly package with Symfony framework
 */
class GeoFriendlyBundle extends Bundle
{
    /**
     * Build the container
     *
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        // Add compiler passes if needed
        // $container->addCompilerPass(new SomeCompilerPass());
    }

    /**
     * Get the bundle namespace
     *
     * @return string
     */
    public function getNamespace(): string
    {
        return 'GeoFriendly\\Symfony';
    }

    /**
     * Get the bundle path
     *
     * @return string
     */
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
