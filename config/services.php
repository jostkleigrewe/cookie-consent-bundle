<?php

declare(strict_types=1);

use Jostkleigrewe\CookieConsentBundle\Config\CookieConfig;
use Jostkleigrewe\CookieConsentBundle\Config\EnforcementConfig;
use Jostkleigrewe\CookieConsentBundle\Config\GoogleConsentModeConfig;
use Jostkleigrewe\CookieConsentBundle\Config\IdentifierCookieConfig;
use Jostkleigrewe\CookieConsentBundle\Config\LoggingConfig;
use Jostkleigrewe\CookieConsentBundle\Config\UiConfig;
use Jostkleigrewe\CookieConsentBundle\Policy\ConsentPolicy;
use Jostkleigrewe\CookieConsentBundle\Service\ConsentLogger;
use Jostkleigrewe\CookieConsentBundle\Service\ConsentManager;
use Jostkleigrewe\CookieConsentBundle\Service\AuditLogPersisterInterface;
use Jostkleigrewe\CookieConsentBundle\Service\DoctrineAuditLogPersister;
use Jostkleigrewe\CookieConsentBundle\Service\NullAuditLogPersister;
use Jostkleigrewe\CookieConsentBundle\Storage\ConsentIdProvider;
use Jostkleigrewe\CookieConsentBundle\Storage\CombinedConsentStorageAdapter;
use Jostkleigrewe\CookieConsentBundle\Storage\ConsentStorageFactory;
use Jostkleigrewe\CookieConsentBundle\Storage\ConsentStorageInterface;
use Jostkleigrewe\CookieConsentBundle\Storage\CookieConsentStorageAdapter;
use Jostkleigrewe\CookieConsentBundle\Storage\DoctrineConsentStorageAdapter;
use Jostkleigrewe\CookieConsentBundle\Storage\DoctrineOrmConsentStorageAdapter;
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

    $services->set(UiConfig::class)
        ->factory([UiConfig::class, 'fromArray'])
        ->args(['%cookie_consent.ui%']);

    $services->set(EnforcementConfig::class)
        ->factory([EnforcementConfig::class, 'fromArray'])
        ->args(['%cookie_consent.enforcement%']);

    $services->set(LoggingConfig::class)
        ->factory([LoggingConfig::class, 'fromArray'])
        ->args(['%cookie_consent.logging%']);

    $services->set(GoogleConsentModeConfig::class)
        ->factory([GoogleConsentModeConfig::class, 'fromArray'])
        ->args(['%cookie_consent.google_consent_mode%']);

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
                '$logging' => new ReferenceConfigurator(LoggingConfig::class),
                '$tokenStorage' => (new ReferenceConfigurator('security.token_storage'))->nullOnInvalid(),
            ]);

        $services->alias(AuditLogPersisterInterface::class, DoctrineAuditLogPersister::class);

        $services->set(PruneConsentLogsCommand::class)
            ->args([
                '$logging' => new ReferenceConfigurator(LoggingConfig::class),
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
            '$logging' => new ReferenceConfigurator(LoggingConfig::class),
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
            '$enforcement' => new ReferenceConfigurator(EnforcementConfig::class),
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
            '$ui' => new ReferenceConfigurator(UiConfig::class),
            '$googleConsentMode' => new ReferenceConfigurator(GoogleConsentModeConfig::class),
        ]);
};
