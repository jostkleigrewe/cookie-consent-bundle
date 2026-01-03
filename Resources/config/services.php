<?php

declare(strict_types=1);

use JostKleigrewe\CookieConsentBundle\Consent\ConsentIdProvider;
use JostKleigrewe\CookieConsentBundle\Consent\ConsentManager;
use JostKleigrewe\CookieConsentBundle\Consent\ConsentPolicy;
use JostKleigrewe\CookieConsentBundle\Consent\CookieConfig;
use JostKleigrewe\CookieConsentBundle\Consent\CookieConsentStorage;
use JostKleigrewe\CookieConsentBundle\Consent\IdentifierCookieConfig;
use JostKleigrewe\CookieConsentBundle\Consent\DoctrineConsentStorage;
use JostKleigrewe\CookieConsentBundle\Controller\CookieConsentController;
use JostKleigrewe\CookieConsentBundle\EventSubscriber\ConsentSessionSubscriber;
use JostKleigrewe\CookieConsentBundle\EventSubscriber\ConsentRequirementResolver;
use JostKleigrewe\CookieConsentBundle\EventSubscriber\ControllerAttributeResolver;
use JostKleigrewe\CookieConsentBundle\Twig\ConsentTwigExtension;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;
use Symfony\Component\Security\Http\FirewallMapInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(ConsentPolicy::class)
        ->args([
            '$categories' => '%cookie_consent.categories%',
            '$policyVersion' => '%cookie_consent.policy_version%',
        ]);

    $services->set(CookieConfig::class)
        ->factory([CookieConfig::class, 'fromArray'])
        ->args(['%cookie_consent.cookie%']);

    $services->set(IdentifierCookieConfig::class)
        ->factory([IdentifierCookieConfig::class, 'fromArray'])
        ->args(['%cookie_consent.identifier_cookie%']);

    $services->set(CookieConsentStorage::class)
        ->args([
            '$config' => new ReferenceConfigurator(CookieConfig::class),
            '$policyVersion' => '%cookie_consent.policy_version%',
        ]);

    $services->set(ConsentIdProvider::class)
        ->args([
            '$config' => new ReferenceConfigurator(IdentifierCookieConfig::class),
        ]);

    if (class_exists(Doctrine\DBAL\Connection::class)) {
        $services->set(DoctrineConsentStorage::class);
    }

    $services->set(ConsentManager::class);
    $services->set(ConsentRequirementResolver::class)
        ->args([
            '$enforcement' => '%cookie_consent.enforcement%',
            '$firewallMap' => new ReferenceConfigurator(FirewallMapInterface::class),
        ]);
    $services->set(ControllerAttributeResolver::class);

    $services->set(ConsentSessionSubscriber::class)
        ->tag('kernel.event_subscriber');

    $services->set(CookieConsentController::class)
        ->tag('controller.service_arguments');

    $services->set(ConsentTwigExtension::class)
        ->tag('twig.extension')
        ->args([
            '$ui' => '%cookie_consent.ui%',
            '$routes' => '%cookie_consent.routes%',
        ]);
};
