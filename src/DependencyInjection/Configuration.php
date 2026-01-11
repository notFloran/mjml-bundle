<?php

namespace NotFloran\MjmlBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Process\ExecutableFinder;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('mjml');
        if (\method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->getRootNode();
        } else {
            // BC layer for symfony/config 4.1 and older
            $rootNode = $treeBuilder->root('mjml');
        }

        $finder = new ExecutableFinder();

        $rootNode
            ->children()
                ->enumNode('renderer')
                    ->values(['binary', 'service'])
                    ->defaultValue('binary')
                ->end()
                ->arrayNode('options')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('binary')
                            ->defaultValue(function () use ($finder) {
                                return $finder->find('mjml');
                            })
                            ->info('Path to the MJML binary')
                        ->end()
                        ->scalarNode('node')
                            ->defaultNull()
                            ->info('Path to node')
                        ->end()
                        ->scalarNode('service_id')
                            ->info('Service id when renderer is defined to "service"')
                        ->end()
                        ->scalarNode('validation_level')
                            ->defaultValue('strict')
                            ->info('Validation level. See https://github.com/mjmlio/mjml/tree/master/packages/mjml-validator#validating-mjml')
                        ->end()
                        ->booleanNode('minify')
                            ->defaultFalse()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->validate()
            ->ifTrue(function ($config) {
                $emptyBinary = 'binary' === $config['renderer'] and empty($config['options']['binary']);
                $missingService = 'service' === $config['renderer'] and !isset($config['options']['service_id']);
                $invalidValidationLevel = 'binary' === $config['renderer'] and !in_array($config['options']['validation_level'], ['skip', 'soft', 'strict']);

                return $emptyBinary || $missingService || $invalidValidationLevel;
            })
            ->then(function ($config) {
                if ('service' === $config['renderer'] and !isset($config['options']['service_id'])) {
                    throw new \LogicException('"service_id" is missing for service renderer');
                }

                if ('binary' === $config['renderer'] and empty($config['options']['binary'])) {
                    throw new \LogicException('Binary is missing');
                }

                if ($config['options']['validation_level'] and !in_array($config['options']['validation_level'], ['skip', 'soft', 'strict'])) {
                    throw new \LogicException('Validation level is invalid');
                }

                return $config;
            })
            ->end()
        ;

        return $treeBuilder;
    }
}
