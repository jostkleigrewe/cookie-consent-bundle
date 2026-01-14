<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Consent\Service;

use Jostkleigrewe\CookieConsentBundle\Consent\Model\ConsentState;
use Jostkleigrewe\CookieConsentBundle\Consent\Policy\ConsentPolicy;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\IpUtils;

/**
 * DE: Protokolliert Consent-Aktionen (optional, auditierbar).
 * EN: Logs consent actions (optional, auditable).
 */
final class ConsentLogger
{
    /**
     * @param array{enabled: bool, level: string, anonymize_ip: bool} $logging
     */
    public function __construct(
        private readonly ?LoggerInterface $logger,
        private readonly array $logging,
    ) {
    }

    public function log(string $action, ConsentState $state, ConsentPolicy $policy, ?Request $request): void
    {
        if (!$this->logging['enabled'] || $this->logger === null) {
            return;
        }

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

        $context = [
            'action' => $action,
            'preferences' => $state->getPreferences(),
            'policy_version' => $state->getPolicyVersion(),
            'decided_at' => $state->getDecidedAt()?->format(\DateTimeInterface::ATOM),
            'accepted_categories' => $accepted,
            'rejected_categories' => $rejected,
        ];

        if ($request !== null) {
            $ipAddress = $request->getClientIp();
            if ($this->logging['anonymize_ip'] && $ipAddress !== null) {
                $ipAddress = IpUtils::anonymize($ipAddress);
            }

            $context['ip_address'] = $ipAddress;
            $context['user_agent'] = $request->headers->get('User-Agent');
            $context['referrer'] = $request->headers->get('Referer');
            $context['request_uri'] = $request->getRequestUri();
        }

        $message = sprintf(
            'Cookie consent %s: %d accepted, %d rejected (version %s)',
            $action,
            count($accepted),
            count($rejected),
            $state->getPolicyVersion()
        );

        match ($this->logging['level']) {
            'debug'     => $this->logger->debug($message, $context),
            'notice'    => $this->logger->notice($message, $context),
            'warning'   => $this->logger->warning($message, $context),
            'error'     => $this->logger->error($message, $context),
            default     => $this->logger->info($message, $context),
        };
    }
}
