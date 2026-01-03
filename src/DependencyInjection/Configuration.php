<?php

declare(strict_types=1);

namespace JostKleigrewe\CookieConsentBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('cookie_consent');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('policy_version')->defaultValue('1')->end()
                ->enumNode('storage')->values(['cookie', 'doctrine'])->defaultValue('cookie')->end()
                ->arrayNode('cookie')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('name')->defaultValue('cookie_consent')->end()
                        ->integerNode('lifetime')->defaultValue(15552000)->end()
                        ->scalarNode('path')->defaultValue('/')->end()
                        ->scalarNode('domain')->defaultNull()->end()
                        ->booleanNode('secure')->defaultNull()->end()
                        ->scalarNode('same_site')->defaultValue('lax')->end()
                        ->booleanNode('http_only')->defaultTrue()->end()
                    ->end()
                ->end()
                ->arrayNode('identifier_cookie')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('name')->defaultValue('cookie_consent_id')->end()
                        ->integerNode('lifetime')->defaultValue(31536000)->end()
                        ->scalarNode('path')->defaultValue('/')->end()
                        ->scalarNode('domain')->defaultNull()->end()
                        ->booleanNode('secure')->defaultNull()->end()
                        ->scalarNode('same_site')->defaultValue('lax')->end()
                        ->booleanNode('http_only')->defaultTrue()->end()
                    ->end()
                ->end()
                ->arrayNode('categories')
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('label')->defaultNull()->end()
                            ->scalarNode('description')->defaultNull()->end()
                            ->booleanNode('required')->defaultFalse()->end()
                            ->booleanNode('default')->defaultFalse()->end()
                        ->end()
                    ->end()
                    ->defaultValue([
                        'necessary' => [
                            'label' => 'Necessary',
                            'description' => 'Required for basic site functionality.',
                            'required' => true,
                            'default' => true,
                        ],
                        'functional' => [
                            'label' => 'Functional',
                            'description' => 'Remember choices and enhance features.',
                            'required' => false,
                            'default' => false,
                        ],
                        'analytics' => [
                            'label' => 'Analytics',
                            'description' => 'Help us improve by collecting analytics.',
                            'required' => false,
                            'default' => false,
                        ],
                        'marketing' => [
                            'label' => 'Marketing',
                            'description' => 'Personalized marketing and ads.',
                            'required' => false,
                            'default' => false,
                        ],
                    ])
                ->end()
                ->arrayNode('ui')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('layout')->defaultValue('tabler')->end()
                        ->scalarNode('template')->defaultValue('@CookieConsentBundle/modal.html.twig')->end()
                    ->end()
                ->end()
                ->arrayNode('routes')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('consent_endpoint')->defaultValue('/_cookie-consent')->end()
                    ->end()
                ->end()
                ->arrayNode('enforcement')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('require_consent_for_session')->defaultTrue()->end()
                        ->arrayNode('stateless_paths')
                            ->scalarPrototype()->end()
                            ->defaultValue([])
                        ->end()
                        ->arrayNode('stateless_routes')
                            ->scalarPrototype()->end()
                            ->defaultValue([])
                        ->end()
                        ->arrayNode('protected_paths')
                            ->scalarPrototype()->end()
                            ->defaultValue([])
                        ->end()
                        ->arrayNode('protected_routes')
                            ->scalarPrototype()->end()
                            ->defaultValue([])
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
