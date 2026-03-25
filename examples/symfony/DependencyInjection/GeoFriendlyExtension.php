<?php

declare(strict_types=1);

namespace GeoFriendly\Symfony\DependencyInjection;

use GeoFriendly\GeoFriendly;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

/**
 * GeoFriendly Extension
 *
 * Loads and configures the geo-friendly bundle
 */
class GeoFriendlyExtension extends Extension
{
    /**
     * Load configuration
     *
     * @param array<string, mixed> $configs
     * @param ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        // Configure GeoFriendly service
        $geoFriendlyDef = $container->getDefinition(GeoFriendly::class);

        // Set configuration parameters
        $geoConfig = [
            'siteUrl' => $config['site_url'],
            'outDir' => $config['output_dir'],
            'siteName' => $config['site_name'],
            'siteDescription' => $config['site_description'],
            'contactEmail' => $config['contact_email'],
            'generators' => $config['generators'],
            'urls' => $config['urls'],
        ];

        $geoFriendlyDef->setArgument(0, $geoConfig);

        // Configure command if enabled
        if ($config['command_enabled']) {
            $container->getDefinition('geofriendly.command.generate')
                ->setArgument(0, new Reference(GeoFriendly::class));
        }

        // Configure auto-generation if enabled
        if ($config['auto_generate']) {
            $container->getDefinition('geofriendly.listener.auto_generate')
                ->setArgument(0, new Reference(GeoFriendly::class))
                ->addTag('kernel.event_listener', [
                    'event' => 'kernel.request',
                    'method' => 'onKernelRequest',
                    'priority' => -255,
                ]);
        }
    }

    /**
     * Get the namespace for configuration
     *
     * @return string
     */
    public function getNamespace(): string
    {
        return 'http://example.com/schema/dic/geofriendly';
    }

    /**
     * Get configuration alias
     *
     * @return string
     */
    public function getAlias(): string
    {
        return 'geofriendly';
    }
}
