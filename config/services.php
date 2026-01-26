<?php

declare(strict_types=1);

use Jostkleigrewe\CookieConsentBundle\Consent\Config\CookieConfig;
use Jostkleigrewe\CookieConsentBundle\Consent\Config\IdentifierCookieConfig;
use Jostkleigrewe\CookieConsentBundle\Consent\Policy\ConsentPolicy;
use Jostkleigrewe\CookieConsentBundle\Consent\Service\ConsentLogger;
use Jostkleigrewe\CookieConsentBundle\Consent\Service\ConsentManager;
use Jostkleigrewe\CookieConsentBundle\Consent\Service\AuditLogPersisterInterface;
use Jostkleigrewe\CookieConsentBundle\Consent\Service\DoctrineAuditLogPersister;
use Jostkleigrewe\CookieConsentBundle\Consent\Service\NullAuditLogPersister;
use Jostkleigrewe\CookieConsentBundle\Consent\Storage\ConsentIdProvider;
use Jostkleigrewe\CookieConsentBundle\Consent\Storage\CombinedConsentStorageAdapter;
use Jostkleigrewe\CookieConsentBundle\Consent\Storage\ConsentStorageFactory;
use Jostkleigrewe\CookieConsentBundle\Consent\Storage\ConsentStorageInterface;
use Jostkleigrewe\CookieConsentBundle\Consent\Storage\CookieConsentStorageAdapter;
use Jostkleigrewe\CookieConsentBundle\Consent\Storage\DoctrineConsentStorageAdapter;
use Jostkleigrewe\CookieConsentBundle\Consent\Storage\DoctrineOrmConsentStorageAdapter;
use Jostkleigrewe\CookieConsentBundle\Command\PruneConsentLogsCommand;
use Jostkleigrewe\CookieConsentBundle\Controller\CookieConsentController;
use Jostkleigrewe\CookieConsentBundle\Controller\ShowcaseController;
use Jostkleigrewe\CookieConsentBundle\EventSubscriber\ConsentSessionSubscriber;
use Jostkleigrewe\CookieConsentBundle\EventSubscriber\ConsentRequirementResolver;
use Jostkleigrewe\CookieConsentBundle\EventSubscriber\ControllerAttributeResolver;
use Jostkleigrewe\CookieConsentBundle\Security\ConsentCsrfTokenManager;
use Jostkleigrewe\CookieConsentBundle\Twig\ConsentTwigExtension;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;
use Symfony\Component\Security\Http\FirewallMapInterface;
use Psr\Log\LoggerInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('Jostkleigrewe\\CookieConsentBundle\\Component\\', __DIR__.'/../src/Component/')
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

    $services->set(CookieConsentStorageAdapter::class)
        ->args([
            '$config' => new ReferenceConfigurator(CookieConfig::class),
            '$policyVersion' => '%cookie_consent.policy_version%',
        ]);

    $services->set(ConsentIdProvider::class)
        ->args([
            '$identifierConfig' => new ReferenceConfigurator(IdentifierCookieConfig::class),
        ]);

    $doctrineStorage = null;

    $services->set(NullAuditLogPersister::class);
    $services->alias(AuditLogPersisterInterface::class, NullAuditLogPersister::class);

    $hasDoctrineBundle = class_exists(\Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class);

    if ($hasDoctrineBundle && class_exists(EntityManagerInterface::class)) {
        $services->set(DoctrineOrmConsentStorageAdapter::class);
        $doctrineStorage = new ReferenceConfigurator(DoctrineOrmConsentStorageAdapter::class);

        $services->set(DoctrineAuditLogPersister::class)
            ->args([
                '$anonymizeIp' => '%cookie_consent.logging.anonymize_ip%',
                '$tokenStorage' => (new ReferenceConfigurator('security.token_storage'))->nullOnInvalid(),
            ]);

        $services->alias(AuditLogPersisterInterface::class, DoctrineAuditLogPersister::class);

        $services->set(PruneConsentLogsCommand::class)
            ->args([
                '$logging' => '%cookie_consent.logging%',
            ])
            ->tag('console.command');
    }

    if ($hasDoctrineBundle && class_exists(Doctrine\DBAL\Connection::class)) {
        $services->set(DoctrineConsentStorageAdapter::class);
        if ($doctrineStorage === null) {
            $doctrineStorage = new ReferenceConfigurator(DoctrineConsentStorageAdapter::class);
        }
    }

    if ($doctrineStorage !== null) {
        $services->set(CombinedConsentStorageAdapter::class)
            ->args([
                '$cookieStorage' => new ReferenceConfigurator(CookieConsentStorageAdapter::class),
                '$doctrineStorage' => $doctrineStorage,
            ]);
    }


    $services->set(ConsentLogger::class)
        ->args([
            '$logger' => (new ReferenceConfigurator('logger'))->nullOnInvalid(),
            '$logging' => '%cookie_consent.logging%',
            '$auditLogPersister' => new ReferenceConfigurator(AuditLogPersisterInterface::class),
        ]);

    $services->set(ConsentCsrfTokenManager::class)
        ->args([
            '$logger' => (new ReferenceConfigurator('logger'))->nullOnInvalid(),
        ]);

    $services->set(ConsentStorageFactory::class);

    $services->set(ConsentStorageInterface::class)
        ->factory([new ReferenceConfigurator(ConsentStorageFactory::class), 'create'])
        ->args(['%cookie_consent.storage%']);

    $services->set(ConsentManager::class);
    $services->set(ConsentRequirementResolver::class)
        ->args([
            '$enforcement' => '%cookie_consent.enforcement%',
            '$firewallMap' => new ReferenceConfigurator(FirewallMapInterface::class),
            '$logger' => (new ReferenceConfigurator(LoggerInterface::class))->nullOnInvalid(),
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
        ->set(ShowcaseController::class)
        ->tag('controller.service_arguments');

    $services
        ->set(ConsentTwigExtension::class)
        ->tag('twig.extension')
        ->args([
            '$ui' => '%cookie_consent.ui%',
            '$googleConsentMode' => '%cookie_consent.google_consent_mode%',
        ]);
};
