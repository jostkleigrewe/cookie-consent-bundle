<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle;

use Jostkleigrewe\CookieConsentBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

final class CookieConsentBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('../config/definition.php');

//        $definition->rootNode()
//            ->children()
//            ->arrayNode('twitter')
//            ->children()
//            ->integerNode('client_id')->end()
//            ->scalarNode('client_secret')->end()
//            ->end()
//            ->end() // twitter
//            ->end()
//        ;
    }


    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {

        // Parameter setzen (für die Nutzung in services.php oder Controllern)
        foreach ($config as $key => $value) {
            $container->parameters()->set('cookie_consent.' . $key, $value);
        }

        // Services laden
        $loader = new PhpFileLoader($builder, new FileLocator($this->getPath().'/config'));
        $loader->load('services.php');

        $storage = $config['storage'] ?? 'cookie';

        $needsDoctrine = in_array($storage, ['doctrine', 'both'], true);
        if ($needsDoctrine && !class_exists(\Doctrine\DBAL\Connection::class)) {
            throw new \LogicException(sprintf(
                'cookie_consent.storage="%s" requires doctrine/dbal. Install doctrine/dbal or set storage="cookie".',
                $storage
            ));
        }

        $storageAlias = match ($storage) {
            'doctrine' => \Jostkleigrewe\CookieConsentBundle\Consent\DoctrineConsentStorage::class,
            'both' => \Jostkleigrewe\CookieConsentBundle\Consent\CombinedConsentStorage::class,
            default => \Jostkleigrewe\CookieConsentBundle\Consent\CookieConsentStorage::class,
        };

        $builder->setAlias(\Jostkleigrewe\CookieConsentBundle\Consent\ConsentStorageInterface::class, $storageAlias);
    }

    public function loadExtensionXXX(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $configuration = new Configuration();
        $processor = new Processor();
        $processedConfig = $processor->processConfiguration($configuration, [$config]);

        $container->parameters()->set('cookie_consent', $processedConfig);
        $container->parameters()->set('cookie_consent.policy_version', $processedConfig['policy_version']);
        $container->parameters()->set('cookie_consent.storage', $processedConfig['storage']);
        $container->parameters()->set('cookie_consent.cookie', $processedConfig['cookie']);
        $container->parameters()->set('cookie_consent.identifier_cookie', $processedConfig['identifier_cookie']);
        $container->parameters()->set('cookie_consent.categories', $processedConfig['categories']);
        $container->parameters()->set('cookie_consent.ui', $processedConfig['ui']);
        $container->parameters()->set('cookie_consent.routes', $processedConfig['routes']);
        $container->parameters()->set('cookie_consent.routes.consent_endpoint', $processedConfig['routes']['consent_endpoint']);
        $container->parameters()->set('cookie_consent.enforcement', $processedConfig['enforcement']);

        $loader = new PhpFileLoader($builder, new FileLocator($this->getPath().'/config'));
        $loader->load('services.php');



        // the "$config" variable is already merged and processed so you can
        // use it directly to configure the service container (when defining an
        // extension class, you also have to do this merging and processing)
//        $container->services()
//            ->get('acme_social.twitter_client')
//            ->arg(0, $config['twitter']['client_id'])
//            ->arg(1, $config['twitter']['client_secret'])
//        ;

    }


    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->extension('twig', [
            'paths' => [
                $this->getPath().'/templates' => 'CookieConsent',
            ],
        ]);

        if (!$builder->hasExtension('framework')) {
            return;
        }

        $builder->prependExtensionConfig('framework', [
            'asset_mapper' => [
                'paths' => [
                    $this->getPath().'/assets/dist' => '@jostkleigrewe/cookie-consent-bundle',
                ],
            ],
        ]);
    }


    public function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import($this->getPath().'/config/routes.php');
    }
}
