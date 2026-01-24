<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Consent\Service;

use Jostkleigrewe\CookieConsentBundle\Consent\Model\ConsentState;
use Jostkleigrewe\CookieConsentBundle\Consent\Policy\ConsentPolicy;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\IpUtils;

/**
 * ConsentLogger - Audit-Logging fuer Consent-Aktionen
 *
 * DE: Protokolliert Consent-Entscheidungen fuer DSGVO-Nachweispflichten.
 *     Unterstuetzt IP-Anonymisierung und konfigurierbare Log-Levels.
 *     Kann deaktiviert werden wenn kein Audit-Trail benoetigt wird.
 *
 * EN: Logs consent decisions for GDPR accountability requirements.
 *     Supports IP anonymization and configurable log levels.
 *     Can be disabled if no audit trail is needed.
 *
 * @example
 * // config/packages/cookie_consent.yaml
 * cookie_consent:
 *     logging:
 *         enabled: true
 *         level: info
 *         anonymize_ip: true  # DSGVO: IP anonymisieren
 *
 * @example
 * // DE: Log-Eintrag Beispiel
 * // EN: Log entry example
 * [2024-01-15 10:30:00] app.INFO: Cookie consent accept_all: 3 accepted, 0 rejected (version 1.0) {
 *     "action": "accept_all",
 *     "preferences": {"necessary": true, "analytics": true, "marketing": true},
 *     "policy_version": "1.0",
 *     "decided_at": "2024-01-15T10:30:00+00:00",
 *     "accepted_categories": ["necessary", "analytics", "marketing"],
 *     "rejected_categories": [],
 *     "ip_address": "192.168.1.x",
 *     "user_agent": "Mozilla/5.0...",
 *     "request_uri": "/contact"
 * }
 */
final class ConsentLogger
{
    /**
     * @param LoggerInterface|null $logger DE: PSR-3 Logger (null = kein Logging)
     *                                      EN: PSR-3 logger (null = no logging)
     * @param array{enabled: bool, level: string, anonymize_ip: bool} $logging
     *        DE: Logging-Konfiguration | EN: Logging configuration
     */
    public function __construct(
        private readonly ?LoggerInterface $logger,
        private readonly array $logging,
    ) {
    }

    /**
     * DE: Protokolliert eine Consent-Aktion.
     *
     * EN: Logs a consent action.
     *
     * @param string $action DE: Aktion ('accept_all', 'reject_optional', 'custom')
     *                       EN: Action ('accept_all', 'reject_optional', 'custom')
     * @param ConsentState $state DE: Der gespeicherte Consent-State
     *                            EN: The saved consent state
     * @param ConsentPolicy $policy DE: Die aktuelle Policy
     *                              EN: The current policy
     * @param Request|null $request DE: HTTP-Request fuer Kontext-Daten
     *                               EN: HTTP request for context data
     */
    public function log(string $action, ConsentState $state, ConsentPolicy $policy, ?Request $request): void
    {
        // DE: Logging deaktiviert oder kein Logger? Abbrechen.
        // EN: Logging disabled or no logger? Abort.
        if (!$this->logging['enabled'] || $this->logger === null) {
            return;
        }

        // DE: Kategorien nach akzeptiert/abgelehnt gruppieren
        // EN: Group categories by accepted/rejected
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

        // DE: Kontext fuer strukturiertes Logging aufbauen
        // EN: Build context for structured logging
        $context = [
            'action' => $action,
            'preferences' => $state->getPreferences(),
            'policy_version' => $state->getPolicyVersion(),
            'decided_at' => $state->getDecidedAt()?->format(\DateTimeInterface::ATOM),
            'accepted_categories' => $accepted,
            'rejected_categories' => $rejected,
        ];

        // DE: Request-Kontext hinzufuegen (IP, User-Agent, etc.)
        // EN: Add request context (IP, user agent, etc.)
        if ($request !== null) {
            $ipAddress = $request->getClientIp();

            // DE: IP anonymisieren wenn konfiguriert (DSGVO-konform)
            // EN: Anonymize IP if configured (GDPR compliant)
            if ($this->logging['anonymize_ip'] && $ipAddress !== null) {
                $ipAddress = IpUtils::anonymize($ipAddress);
            }

            $context['ip_address'] = $ipAddress;
            $context['user_agent'] = $request->headers->get('User-Agent');
            $context['referrer'] = $request->headers->get('Referer');
            $context['request_uri'] = $request->getRequestUri();
        }

        // DE: Log-Nachricht formatieren
        // EN: Format log message
        $message = sprintf(
            'Cookie consent %s: %d accepted, %d rejected (version %s)',
            $action,
            count($accepted),
            count($rejected),
            $state->getPolicyVersion()
        );

        // DE: Mit konfiguriertem Log-Level loggen
        // EN: Log with configured log level
        match ($this->logging['level']) {
            'debug' => $this->logger->debug($message, $context),
            'notice' => $this->logger->notice($message, $context),
            'warning' => $this->logger->warning($message, $context),
            'error' => $this->logger->error($message, $context),
            default => $this->logger->info($message, $context),
        };
    }
}
