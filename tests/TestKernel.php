<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Tests;

use Jostkleigrewe\CookieConsentBundle\CookieConsentBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\UX\TwigComponent\TwigComponentBundle;

final class TestKernel extends Kernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new SecurityBundle(),
            new TwigBundle(),
            new TwigComponentBundle(),
            new CookieConsentBundle(),
        ];
    }

    public function boot(): void
    {
        parent::boot();

        // FrameworkBundle registers an exception handler; remove it for stable PHPUnit globals.
        restore_exception_handler();
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->extension('framework', [
            'secret' => 'test',
            'test' => true,
            'router' => ['utf8' => true],
            'session' => ['storage_factory_id' => 'session.storage.factory.mock_file'],
        ]);

        $container->extension('security', [
            'firewalls' => [
                'main' => [
                    'lazy' => true,
                ],
            ],
        ]);

        $container->extension('twig', [
            'strict_variables' => true,
        ]);

        $container->extension('twig_component', []);

        $container->extension('cookie_consent', [
            'policy_version' => '1',
            'storage' => 'cookie',
            'categories' => [
                'necessary' => [
                    'label' => 'Necessary',
                    'required' => true,
                    'default' => true,
                ],
                'analytics' => [
                    'label' => 'Analytics',
                    'required' => false,
                    'default' => false,
                ],
            ],
        ]);
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        // DE: Lädt #[Route]-Attribute aus den Bundle-Controllern.
        // EN: Loads #[Route] attributes from the bundle's controllers.
        $routes->import(dirname(__DIR__).'/src/Controller/', 'attribute');
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir().'/cookie-consent-bundle/cache/'.$this->environment;
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir().'/cookie-consent-bundle/log/'.$this->environment;
    }
}
