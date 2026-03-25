<?php

declare(strict_types=1);

namespace GeoFriendly\Symfony\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration for GeoFriendly bundle
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Get configuration tree builder
     *
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('geofriendly');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('site_url')
                    ->defaultValue('%env(default:APP_URL)%')
                    ->info('The base URL of your website')
                ->end()
                ->scalarNode('output_dir')
                    ->defaultValue('%kernel.project_dir%/public')
                    ->info('Directory where generated files will be saved')
                ->end()
                ->scalarNode('site_name')
                    ->defaultValue('%env(default:APP_NAME:APP)%')
                    ->info('Name of your website')
                ->end()
                ->scalarNode('site_description')
                    ->defaultValue('')
                    ->info('Description of your website')
                ->end()
                ->scalarNode('contact_email')
                    ->defaultValue('no-reply@example.com')
                    ->info('Contact email for your website')
                ->end()
                ->arrayNode('generators')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('llms_txt')->defaultTrue()->end()
                        ->booleanNode('robots_txt')->defaultTrue()->end()
                        ->booleanNode('sitemap')->defaultTrue()->end()
                        ->booleanNode('docs_json')->defaultTrue()->end()
                        ->booleanNode('ai_index')->defaultTrue()->end()
                        ->booleanNode('llms_full_txt')->defaultFalse()->end()
                    ->end()
                ->end()
                ->arrayNode('urls')
                    ->defaultValue([])
                    ->info('List of URLs to include in generated files')
                    ->prototype('scalar')->end()
                ->end()
                ->booleanNode('command_enabled')
                    ->defaultTrue()
                    ->info('Enable the console command')
                ->end()
                ->booleanNode('auto_generate')
                    ->defaultFalse()
                    ->info('Automatically generate files on request (not recommended for production)')
                ->end()
                ->arrayNode('auto_discover_routes')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')
                            ->defaultFalse()
                            ->info('Automatically discover routes from Symfony routing')
                        ->end()
                        ->arrayNode('exclude_patterns')
                            ->defaultValue(['^_.*', '^api'])
                            ->info('Regex patterns to exclude from auto-discovery')
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('sitemap')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('change_freq')
                            ->defaultValue('weekly')
                            ->info('Default change frequency for sitemap')
                        ->end()
                        ->scalarNode('priority')
                            ->defaultValue('0.8')
                            ->info('Default priority for sitemap')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('robots')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('user_agent')
                            ->defaultValue('*')
                            ->info('User agent for robots.txt')
                        ->end()
                        ->arrayNode('disallow')
                            ->defaultValue(['/admin', '/login', '/api'])
                            ->info('Paths to disallow in robots.txt')
                            ->prototype('scalar')->end()
                        ->end()
                        ->booleanNode('include_sitemap')
                            ->defaultTrue()
                            ->info('Include sitemap reference in robots.txt')
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
