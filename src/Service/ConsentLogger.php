<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Service;

use Jostkleigrewe\CookieConsentBundle\Config\LoggingConfig;
use Jostkleigrewe\CookieConsentBundle\Config\LogLevel;
use Jostkleigrewe\CookieConsentBundle\Model\ConsentState;
use Jostkleigrewe\CookieConsentBundle\Policy\ConsentPolicy;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * ConsentLogger - Audit-Logging für Consent-Aktionen
 *
 * Protokolliert Consent-Entscheidungen für DSGVO-Nachweispflichten.
 *     Unterstützt IP-Anonymisierung und konfigurierbare Log-Levels.
 *     Kann deaktiviert werden wenn kein Audit-Trail benötigt wird.
 *
 * Logs consent decisions for GDPR accountability requirements.
 *     Supports IP anonymization and configurable log levels.
 *     Can be disabled if no audit trail is needed.
 */
final class ConsentLogger
{
    /**
     * @param LoggerInterface|null $logger PSR-3 logger (null = no logging)
     * @param LoggingConfig $logging Logging configuration DTO
     * @param AuditLogPersisterInterface|null $auditLogPersister Audit log persister
     */
    public function __construct(
        private readonly ?LoggerInterface $logger,
        private readonly LoggingConfig $logging,
        private readonly ?AuditLogPersisterInterface $auditLogPersister = null,
    ) {
    }

    /**
     * Logs a consent action.
     *
     * @param string $action Action ('accept_all', 'reject_optional', 'custom')
     * @param ConsentState $state The saved consent state
     * @param ConsentPolicy $policy The current policy
     * @param Request|null $request HTTP request for context data
     * @param Response|null $response HTTP response (needed to ensure consent ID)
     */
    public function log(string $action, ConsentState $state, ConsentPolicy $policy, ?Request $request, ?Response $response = null): void
    {
        // Logging disabled? Abort.
        if (!$this->logging->enabled) {
            return;
        }

        // Group categories by accepted/rejected
        $categories = array_keys($policy->getCategories());
        $accepted = [];
        $rejected = [];
        foreach ($categories as $category) {
            if ($state->isAllowed($category)) {
                $accepted[] = $category;
            } else {
                $rejected[] = $category;
            }
        }

        // Build context for structured logging
        $context = [
            'action' => $action,
            'preferences' => $state->getPreferences(),
            'policy_version' => $state->getPolicyVersion(),
            'decided_at' => $state->getDecidedAt()?->format(\DateTimeInterface::ATOM),
            'accepted_categories' => $accepted,
            'rejected_categories' => $rejected,
        ];

        // Add request context (IP, user agent, etc.)
        if ($request !== null) {
            $ipAddress = $request->getClientIp();
            if ($this->logging->anonymizeIp && $ipAddress !== null) {
                $ipAddress = IpUtils::anonymize($ipAddress);
            }

            $context['ip_address'] = $ipAddress;
            $context['user_agent'] = $request->headers->get('User-Agent');
            $context['referrer'] = $request->headers->get('Referer');
            $context['request_uri'] = $request->getRequestUri();
        }

        if ($request !== null && $response !== null && $this->auditLogPersister !== null) {
            $this->auditLogPersister->persist($action, $state, $policy, $request, $response);
        }

        // Format log message
        $message = sprintf(
            'Cookie consent %s: %d accepted, %d rejected (version %s)',
            $action,
            count($accepted),
            count($rejected),
            $state->getPolicyVersion()
        );

        // Log with configured log level (exhaustive match on enum)
        if ($this->logger !== null) {
            match ($this->logging->level) {
                LogLevel::Debug => $this->logger->debug($message, $context),
                LogLevel::Info => $this->logger->info($message, $context),
                LogLevel::Notice => $this->logger->notice($message, $context),
                LogLevel::Warning => $this->logger->warning($message, $context),
                LogLevel::Error => $this->logger->error($message, $context),
            };
        }
    }
}
