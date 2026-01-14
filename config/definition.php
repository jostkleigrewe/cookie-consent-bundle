<?php

declare(strict_types=1);

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

return static function (DefinitionConfigurator $definition): void {
    $definition->rootNode()
        ->children()
        ->scalarNode('policy_version')
        ->defaultValue('1')
        ->end()

        // DE: Storage-Backend für Consent (Cookie, DB oder beides).
        // EN: Storage backend for consent (cookie, DB or both).
        ->enumNode('storage')
        ->values(['cookie', 'doctrine', 'both'])
        ->defaultValue('cookie')
        ->end()

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

        // DE: Identifier-Cookie wird für DB-Storage genutzt (stable pseudo id).
        // EN: Identifier cookie is used for DB storage (stable pseudo id).
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
        ->scalarNode('template')->defaultValue('@CookieConsent/modal.html.twig')->end()
        ->end()
        ->end()

        ->arrayNode('enforcement')
        ->addDefaultsIfNotSet()
        ->children()
        ->booleanNode('require_consent_for_session')->defaultTrue()->end()
        ->arrayNode('stateless_paths')->scalarPrototype()->end()->defaultValue([])->end()
        ->arrayNode('stateless_routes')->scalarPrototype()->end()->defaultValue([])->end()
        ->arrayNode('protected_paths')->scalarPrototype()->end()->defaultValue([])->end()
        ->arrayNode('protected_routes')->scalarPrototype()->end()->defaultValue([])->end()
        ->end()
        ->end()
        ->end();
};


//
//use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
//
//return static function (DefinitionConfigurator $definition): void {
//
//    $definition->rootNode()
//        ->children()
//
//
//
////        ->scalarNode('policy_version')->defaultValue('1')->end()
////        ->enumNode('storage')->values(['cookie', 'doctrine'])->defaultValue('cookie')->end()
////        ->arrayNode('cookie')
////        ->addDefaultsIfNotSet()
////        ->children()
////        ->scalarNode('name')->defaultValue('cookie_consent')->end()
////        ->integerNode('lifetime')->defaultValue(15552000)->end()
////        ->scalarNode('path')->defaultValue('/')->end()
////        ->scalarNode('domain')->defaultNull()->end()
////        ->booleanNode('secure')->defaultNull()->end()
////        ->scalarNode('same_site')->defaultValue('lax')->end()
////        ->booleanNode('http_only')->defaultTrue()->end()
////        ->end()
////        ->end()
////        ->arrayNode('identifier_cookie')
////        ->addDefaultsIfNotSet()
////        ->children()
////        ->scalarNode('name')->defaultValue('cookie_consent_id')->end()
////        ->integerNode('lifetime')->defaultValue(31536000)->end()
////        ->scalarNode('path')->defaultValue('/')->end()
////        ->scalarNode('domain')->defaultNull()->end()
////        ->booleanNode('secure')->defaultNull()->end()
////        ->scalarNode('same_site')->defaultValue('lax')->end()
////        ->booleanNode('http_only')->defaultTrue()->end()
////        ->end()
////        ->end()
////        ->arrayNode('categories')
////        ->useAttributeAsKey('name')
////        ->arrayPrototype()
////        ->children()
////        ->scalarNode('label')->defaultNull()->end()
////        ->scalarNode('description')->defaultNull()->end()
////        ->booleanNode('required')->defaultFalse()->end()
////        ->booleanNode('default')->defaultFalse()->end()
////        ->end()
////        ->end()
////        ->defaultValue([
////            'necessary' => [
////                'label' => 'Necessary',
////                'description' => 'Required for basic site functionality.',
////                'required' => true,
////                'default' => true,
////            ],
////            'functional' => [
////                'label' => 'Functional',
////                'description' => 'Remember choices and enhance features.',
////                'required' => false,
////                'default' => false,
////            ],
////            'analytics' => [
////                'label' => 'Analytics',
////                'description' => 'Help us improve by collecting analytics.',
////                'required' => false,
////                'default' => false,
////            ],
////            'marketing' => [
////                'label' => 'Marketing',
////                'description' => 'Personalized marketing and ads.',
////                'required' => false,
////                'default' => false,
////            ],
////        ])
////        ->end()
////        ->arrayNode('ui')
////        ->addDefaultsIfNotSet()
////        ->children()
////        ->scalarNode('layout')->defaultValue('tabler')->end()
////        ->scalarNode('template')->defaultValue('@CookieConsent/modal.html.twig')->end()
////        ->end()
////        ->end()
////        ->arrayNode('routes')
////        ->addDefaultsIfNotSet()
////        ->children()
////        ->scalarNode('consent_endpoint')->defaultValue('/_cookie-consent')->end()
////        ->end()
////        ->end()
////        ->arrayNode('enforcement')
////        ->addDefaultsIfNotSet()
////        ->children()
////        ->booleanNode('require_consent_for_session')->defaultTrue()->end()
////        ->arrayNode('stateless_paths')
////        ->scalarPrototype()->end()
////        ->defaultValue([])
////        ->end()
////        ->arrayNode('stateless_routes')
////        ->scalarPrototype()->end()
////        ->defaultValue([])
////        ->end()
////        ->arrayNode('protected_paths')
////        ->scalarPrototype()->end()
////        ->defaultValue([])
////        ->end()
////        ->arrayNode('protected_routes')
////        ->scalarPrototype()->end()
////        ->defaultValue([])
////        ->end()
////        ->end()
////        ->end()
////        ->end();
//
//
//
//
//
//
//            ->scalarNode('policy_version')
//                ->defaultValue('1')
//            ->end()
//
//            ->scalarNode('storage')
//                ->defaultValue('cookie')
//                ->validate()
//                    ->ifNotInArray(['cookie', 'doctrine', 'both'])
//                    ->thenInvalid('Invalid storage type %s')
//                ->end()
//            ->end()
//
//            ->arrayNode('cookie')
//                ->addDefaultsIfNotSet()
//                ->children()
//                    ->scalarNode('name')
//                        ->defaultValue('cookie_consent')
//                    ->end()
//                    ->integerNode('lifetime')
//                        ->defaultValue(15552000)
//                    ->end()
//                    ->scalarNode('same_site')
//                        ->defaultValue('lax')
//                    ->end()
//                ->end()
//            ->end()
//
//            ->integerNode('cookie_lifetime')
//                ->defaultValue(365 * 24 * 60 * 60) // 1 year in seconds
//                ->info('Cookie lifetime in seconds')
//            ->end()
//            ->scalarNode('translation_domain')
//                ->defaultValue('sp_consent')
//                ->info('Translation domain for category names and descriptions')
//            ->end()
//            ->booleanNode('use_translations')
//                ->defaultFalse()
//                ->info('Whether to translate category names and descriptions')
//            ->end()
//            ->booleanNode('enable_logging')
//                ->defaultTrue()
//                ->info('Whether to log consent actions for GDPR compliance')
//            ->end()
//            ->scalarNode('log_level')
//                ->defaultValue('info')
//                ->info('Log level for consent actions (debug, info, notice, warning, error)')
//            ->end()
//            ->scalarNode('consent_version')
//                ->defaultValue('1.0')
//                ->info('Version of the consent policy (for tracking policy changes)')
//            ->end()
//            ->arrayNode('categories')
//                ->useAttributeAsKey('key')
//                ->arrayPrototype()
//                    ->children()
//                        ->scalarNode('name')
//                            ->isRequired()
//                            ->cannotBeEmpty()
//                        ->end()
//                        ->scalarNode('description')
//                            ->isRequired()
//                            ->cannotBeEmpty()
//                        ->end()
//                        ->booleanNode('required')
//                            ->defaultFalse()
//                        ->end()
//                    ->end()
//                ->end()
//                ->defaultValue([
//                    'necessary' => [
//                        'name' => 'cookie.category.necessary.name',
//                        'description' => 'cookie.category.necessary.description',
//                        'required' => true,
//                    ],
//                    'analytics' => [
//                        'name' => 'cookie.category.analytics.name',
//                        'description' => 'cookie.category.analytics.description',
//                        'required' => false,
//                    ],
//                    'marketing' => [
//                        'name' => 'cookie.category.marketing.name',
//                        'description' => 'cookie.category.marketing.description',
//                        'required' => false,
//                    ],
//                    'functional' => [
//                        'name' => 'cookie.category.functional.name',
//                        'description' => 'cookie.category.functional.description',
//                        'required' => false,
//                    ],
//                ])
//            ->end()
//        ->end();
//};
