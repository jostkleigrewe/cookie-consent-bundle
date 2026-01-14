<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Consent\Storage;

use Jostkleigrewe\CookieConsentBundle\Consent\Model\ConsentState;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * DE: Vertrag fuer Consent-Speicher-Backends.
 * EN: Contract for consent storage backends.
 */
interface ConsentStorageInterface
{
    public function load(Request $request): ConsentState;

    public function save(Request $request, Response $response, ConsentState $state): void;
}
