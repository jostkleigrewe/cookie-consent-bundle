<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Tests\Consent;

use Jostkleigrewe\CookieConsentBundle\Consent\Config\IdentifierCookieConfig;
use Jostkleigrewe\CookieConsentBundle\Consent\Storage\ConsentIdProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ConsentIdProviderTest extends TestCase
{
    public function testEnsureIdReusesResponseCookieWithinSameResponse(): void
    {
        $config = IdentifierCookieConfig::fromArray([
            'name' => 'cookie_consent_id',
            'lifetime' => 3600,
            'path' => '/',
            'domain' => null,
            'secure' => null,
            'same_site' => 'lax',
            'http_only' => true,
        ]);

        $provider = new ConsentIdProvider($config);
        $request = new Request();
        $response = new Response();

        $firstId = $provider->ensureId($request, $response);
        $secondId = $provider->ensureId($request, $response);

        self::assertSame($firstId, $secondId);
        self::assertCount(1, $response->headers->getCookies());
    }
}
