<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/**
 * DE: Bundle-Einstiegspunkt fuer Container, Assets und Routen.
 * EN: Bundle entry point for container wiring, assets, and routes.
 */
final class CookieConsentBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('../config/definition.php');
    }


    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {

        // DE: Parameter setzen (fuer die Nutzung in services.php oder Controllern).
        // EN: Set parameters (for usage in services.php or controllers).
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
