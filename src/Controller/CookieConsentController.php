<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Controller;

use Jostkleigrewe\CookieConsentBundle\Consent\ConsentManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class CookieConsentController
{
    public function __construct(
        private ConsentManager $consentManager
    )
    {
    }

    public function update(Request $request): JsonResponse
    {
        $payload = $request->getPayload()->all();
        $action = $payload['action'] ?? 'custom';
        $preferences = $payload['preferences'] ?? [];

        if (!is_array($preferences)) {
            return new JsonResponse(['error' => 'Invalid preferences payload.'], Response::HTTP_BAD_REQUEST);
        }

        $response = new JsonResponse();

        $state = match ($action) {
            'accept_all'        => $this->consentManager->acceptAll($request, $response),
            'reject_optional'   => $this->consentManager->rejectOptional($request, $response),
            default             => $this->consentManager->savePreferences($request, $response, $preferences),
        };

        $response->setData([
            'preferences'       => $state->getPreferences(),
            'policy_version'    => $state->getPolicyVersion(),
            'decided_at'        => $state->getDecidedAt()?->format(DATE_ATOM),
        ]);

        return $response;
    }
}
