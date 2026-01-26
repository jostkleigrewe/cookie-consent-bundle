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
                    ->scalarNode('template')->defaultValue('@CookieConsent/modal.html.twig')->end()

                    // DE: Layout-Variante: plain (framework-agnostisch), bootstrap, tabler.
                    // EN: Layout variant: plain (framework-agnostic), bootstrap, tabler.
                    ->enumNode('variant')
                        ->values(['plain', 'bootstrap', 'tabler'])
                        ->defaultValue('tabler')
                    ->end()

                    // DE: Farbschema: day (hell), night (dunkel), auto (folgt prefers-color-scheme).
                    // EN: Color theme: day (light), night (dark), auto (follows prefers-color-scheme).
                    ->enumNode('theme')
                        ->values(['day', 'night', 'auto'])
                        ->defaultValue('day')
                    ->end()

                    // DE: Dichte-Modus: normal oder compact.
                    // EN: Density mode: normal or compact.
                    ->enumNode('density')
                        ->values(['normal', 'compact'])
                        ->defaultValue('normal')
                    ->end()

                    // DE: Position des Modals (z.B. center, bottom, top-right).
                    // EN: Modal position (e.g. center, bottom, top-right).
                    ->enumNode('position')
                        ->values([
                            'center',
                            'top',
                            'bottom',
                            'left',
                            'right',
                            'top-left',
                            'top-right',
                            'bottom-left',
                            'bottom-right',
                        ])
                        ->defaultValue('center')
                    ->end()

                    ->scalarNode('privacy_url')->defaultNull()->end()
                    ->scalarNode('imprint_url')->defaultNull()->end()
                    ->booleanNode('reload_on_change')->defaultFalse()->end()
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
                ->arrayNode('logging')
                ->addDefaultsIfNotSet()
                ->children()
                    ->booleanNode('enabled')->defaultFalse()->end()
                    ->scalarNode('level')->defaultValue('info')->end()
                    ->booleanNode('anonymize_ip')->defaultTrue()->end()
                ->end()
            ->end()

            // DE: Google Consent Mode v2 Integration.
            //     Wenn aktiviert, wird gtag('consent', 'update', ...) automatisch aufgerufen.
            // EN: Google Consent Mode v2 integration.
            //     When enabled, gtag('consent', 'update', ...) is called automatically.
            ->arrayNode('google_consent_mode')
                ->addDefaultsIfNotSet()
                ->children()
                    ->booleanNode('enabled')->defaultFalse()->end()
                    ->arrayNode('mapping')
                        ->addDefaultsIfNotSet()
                        ->children()
                            // DE: Mapping von Google Consent Types auf Bundle-Kategorien.
                            // EN: Mapping from Google consent types to bundle categories.
                            ->scalarNode('analytics_storage')->defaultValue('analytics')->end()
                            ->scalarNode('ad_storage')->defaultValue('marketing')->end()
                            ->scalarNode('ad_user_data')->defaultValue('marketing')->end()
                            ->scalarNode('ad_personalization')->defaultValue('marketing')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();
};
