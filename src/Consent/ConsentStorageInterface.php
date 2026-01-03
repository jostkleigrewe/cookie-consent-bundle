<?php

declare(strict_types=1);

namespace JostKleigrewe\CookieConsentBundle\Consent;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface ConsentStorageInterface
{
    public function load(Request $request): ConsentState;

    public function save(Request $request, Response $response, ConsentState $state): void;
}
