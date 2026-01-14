<?php

declare(strict_types=1);

use Jostkleigrewe\CookieConsentBundle\Consent\CombinedConsentStorage;
use Jostkleigrewe\CookieConsentBundle\Consent\ConsentIdProvider;
use Jostkleigrewe\CookieConsentBundle\Consent\ConsentManager;
use Jostkleigrewe\CookieConsentBundle\Consent\ConsentPolicy;
use Jostkleigrewe\CookieConsentBundle\Consent\ConsentStorageInterface;
use Jostkleigrewe\CookieConsentBundle\Consent\CookieConfig;
use Jostkleigrewe\CookieConsentBundle\Consent\CookieConsentStorage;
use Jostkleigrewe\CookieConsentBundle\Consent\IdentifierCookieConfig;
use Jostkleigrewe\CookieConsentBundle\Consent\DoctrineConsentStorage;
use Jostkleigrewe\CookieConsentBundle\Controller\CookieConsentController;
use Jostkleigrewe\CookieConsentBundle\EventSubscriber\ConsentSessionSubscriber;
use Jostkleigrewe\CookieConsentBundle\EventSubscriber\ConsentRequirementResolver;
use Jostkleigrewe\CookieConsentBundle\EventSubscriber\ControllerAttributeResolver;
use Jostkleigrewe\CookieConsentBundle\Twig\ConsentTwigExtension;
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
        $services->set(CombinedConsentStorage::class);
    }

//    $services->alias(
//        ConsentStorageInterface::class,
//        '%cookie_consent.storage%' === 'doctrine' ? DoctrineConsentStorage::class : CookieConsentStorage::class
//    );

    $services->set(ConsentManager::class);
    $services->set(ConsentRequirementResolver::class)
        ->args([
            '$enforcement' => '%cookie_consent.enforcement%',
            '$firewallMap' => new ReferenceConfigurator(FirewallMapInterface::class),
        ]);
    $services
        ->set(ControllerAttributeResolver::class);

    $services
        ->set(ConsentSessionSubscriber::class)
        ->tag('kernel.event_subscriber');

    $services
        ->set(CookieConsentController::class)
        ->tag('controller.service_arguments');

    $services
        ->set(ConsentTwigExtension::class)
        ->tag('twig.extension')
        ->args([
            '$ui' => '%cookie_consent.ui%',
        ]);
};
