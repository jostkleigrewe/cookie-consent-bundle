<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Tests\Controller;

use Jostkleigrewe\CookieConsentBundle\Tests\TestKernel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

final class CookieConsentControllerTest extends WebTestCase
{
    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    protected static function createKernel(array $options = []): KernelInterface
    {
        return new TestKernel($options['environment'] ?? 'test', $options['debug'] ?? false);
    }

    protected function tearDown(): void
    {
        self::ensureKernelShutdown();
        parent::tearDown();
    }

    public function testUpdateAcceptAllReturnsPreferences(): void
    {
        $client = static::createClient();

        $payload = json_encode([
            'csrf_token' => 'csrf-token',
            'action' => 'accept_all',
            'preferences' => [],
        ], JSON_THROW_ON_ERROR);

        $client->request(
            'POST',
            '/_cookie-consent',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ORIGIN' => 'http://localhost',
                'HTTP_REFERER' => 'http://localhost/',
            ],
            content: $payload
        );

        $response = $client->getResponse();

        self::assertSame(200, $response->getStatusCode());
        self::assertTrue($response->headers->contains('Content-Type', 'application/json'));

        $data = json_decode($response->getContent(), true, flags: JSON_THROW_ON_ERROR);

        self::assertSame([
            'necessary' => true,
            'analytics' => true,
        ], $data['preferences']);
    }
}
