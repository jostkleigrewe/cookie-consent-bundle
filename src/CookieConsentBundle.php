<?php

declare(strict_types=1);

namespace JostKleigrewe\CookieConsentBundle;

use Doctrine\DBAL\Connection;
use JostKleigrewe\CookieConsentBundle\Consent\ConsentStorageInterface;
use JostKleigrewe\CookieConsentBundle\Consent\CookieConsentStorage;
use JostKleigrewe\CookieConsentBundle\Consent\DoctrineConsentStorage;
use JostKleigrewe\CookieConsentBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

final class CookieConsentBundle extends AbstractBundle
{
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $configuration = new Configuration();
        $processedConfig = $this->processConfiguration($configuration, [$config]);

        $container->parameters()->set('cookie_consent', $processedConfig);
        $container->parameters()->set('cookie_consent.policy_version', $processedConfig['policy_version']);
        $container->parameters()->set('cookie_consent.storage', $processedConfig['storage']);
        $container->parameters()->set('cookie_consent.cookie', $processedConfig['cookie']);
        $container->parameters()->set('cookie_consent.identifier_cookie', $processedConfig['identifier_cookie']);
        $container->parameters()->set('cookie_consent.categories', $processedConfig['categories']);
        $container->parameters()->set('cookie_consent.ui', $processedConfig['ui']);
        $container->parameters()->set('cookie_consent.routes', $processedConfig['routes']);
        $container->parameters()->set('cookie_consent.enforcement', $processedConfig['enforcement']);

        $container->extension('twig', [
            'paths' => [
                $this->getPath() . '/templates' => 'CookieConsentBundle',
            ],
        ]);

        $loader = new PhpFileLoader($builder, new FileLocator($this->getPath() . '/Resources/config'));
        $loader->load('services.php');

        if ($processedConfig['storage'] === 'doctrine') {
            if (!class_exists(Connection::class)) {
                throw new \LogicException('Doctrine DBAL is required for doctrine consent storage.');
            }

            $builder->setAlias(ConsentStorageInterface::class, DoctrineConsentStorage::class);
        } else {
            $builder->setAlias(ConsentStorageInterface::class, CookieConsentStorage::class);
        }
    }

    public function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import($this->getPath() . '/Resources/config/routes.php');
    }
}
