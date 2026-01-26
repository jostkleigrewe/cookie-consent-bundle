<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Consent\Service;

use Jostkleigrewe\CookieConsentBundle\Consent\Model\ConsentState;
use Jostkleigrewe\CookieConsentBundle\Consent\Policy\ConsentPolicy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class NullAuditLogPersister implements AuditLogPersisterInterface
{
    public function persist(
        string $action,
        ConsentState $state,
        ConsentPolicy $policy,
        Request $request,
        Response $response
    ): void {
    }
}
