<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Controller;

use Jostkleigrewe\CookieConsentBundle\Consent\Service\ConsentManager;
use Jostkleigrewe\CookieConsentBundle\Http\ConsentUpdateException;
use Jostkleigrewe\CookieConsentBundle\Http\ConsentUpdatePayload;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Jostkleigrewe\CookieConsentBundle\Security\ConsentCsrfTokenManager;
use Symfony\Component\Security\Csrf\CsrfToken;

/**
 * DE: Controller fuer den Consent-Update-Endpoint.
 * EN: Controller for the consent update endpoint.
 */
final readonly class CookieConsentController
{
    public function __construct(
        private ConsentManager $consentManager,
        private ConsentCsrfTokenManager $csrfTokenManager,
    )
    {
    }

    public function update(Request $request): JsonResponse
    {
        try {
            $payload = ConsentUpdatePayload::fromRequest($request, $this->consentManager->getPolicy());
        } catch (ConsentUpdateException $exception) {
            return new JsonResponse([
                'error' => $exception->getMessage(),
                'code' => $exception->getErrorCode(),
            ], $exception->getStatusCode());
        }

        $token = new CsrfToken(ConsentCsrfTokenManager::TOKEN_ID, $payload->getCsrfToken());
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            return new JsonResponse(['error' => 'Invalid CSRF token.'], Response::HTTP_FORBIDDEN);
        }

        $response = new JsonResponse();

        $state = match ($payload->getAction()) {
            'accept_all'        => $this->consentManager->acceptAll($request, $response),
            'reject_optional'   => $this->consentManager->rejectOptional($request, $response),
            default             => $this->consentManager->savePreferences($request, $response, $payload->getPreferences()),
        };

        $response->setData([
            'preferences'       => $state->getPreferences(),
            'policy_version'    => $state->getPolicyVersion(),
            'decided_at'        => $state->getDecidedAt()?->format(DATE_ATOM),
        ]);

        return $response;
    }
}
