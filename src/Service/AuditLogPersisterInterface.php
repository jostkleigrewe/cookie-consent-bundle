<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Service;

use Jostkleigrewe\CookieConsentBundle\Model\ConsentState;
use Jostkleigrewe\CookieConsentBundle\Policy\ConsentPolicy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface AuditLogPersisterInterface
{
    public function persist(
        string $action,
        ConsentState $state,
        ConsentPolicy $policy,
        Request $request,
        Response $response
    ): void;
}
