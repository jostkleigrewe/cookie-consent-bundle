<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Tests\Support;

use Jostkleigrewe\CookieConsentBundle\Consent\Model\ConsentState;
use Jostkleigrewe\CookieConsentBundle\Consent\Storage\ConsentStorageInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class InMemoryConsentStorage implements ConsentStorageInterface
{
    private ?ConsentState $state = null;

    public function __construct(private readonly string $policyVersion)
    {
    }

    public function load(Request $request): ConsentState
    {
        return $this->state ?? ConsentState::empty($this->policyVersion);
    }

    public function save(Request $request, Response $response, ConsentState $state): void
    {
        $this->state = $state;
    }
}
