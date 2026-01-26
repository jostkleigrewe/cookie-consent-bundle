<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Tests\Consent;

use Jostkleigrewe\CookieConsentBundle\Consent\Model\ConsentState;
use Jostkleigrewe\CookieConsentBundle\Consent\Policy\ConsentPolicy;
use Jostkleigrewe\CookieConsentBundle\Consent\Service\AuditLogPersisterInterface;
use Jostkleigrewe\CookieConsentBundle\Consent\Service\ConsentLogger;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ConsentLoggerTest extends TestCase
{
    public function testLogSkipsPersistenceWhenResponseMissing(): void
    {
        $persister = new FakeAuditLogPersister();
        $logger = new ConsentLogger(null, [
            'enabled' => true,
            'level' => 'info',
            'anonymize_ip' => true,
            'retention_days' => null,
        ], $persister);

        $policy = new ConsentPolicy([
            'necessary' => ['required' => true, 'default' => true],
        ], '1');

        $state = ConsentState::empty('1')->withPreferences([
            'necessary' => ['allowed' => true, 'vendors' => []],
        ]);

        $logger->log('accept_all', $state, $policy, new Request(), null);

        self::assertSame(0, $persister->getCalls());
    }

    public function testLogPersistsWhenResponsePresent(): void
    {
        $persister = new FakeAuditLogPersister();
        $logger = new ConsentLogger(null, [
            'enabled' => true,
            'level' => 'info',
            'anonymize_ip' => true,
            'retention_days' => null,
        ], $persister);

        $policy = new ConsentPolicy([
            'necessary' => ['required' => true, 'default' => true],
        ], '1');

        $state = ConsentState::empty('1')->withPreferences([
            'necessary' => ['allowed' => true, 'vendors' => []],
        ]);

        $logger->log('accept_all', $state, $policy, new Request(), new Response());

        self::assertSame(1, $persister->getCalls());
    }
}

final class FakeAuditLogPersister implements AuditLogPersisterInterface
{
    private int $calls = 0;

    public function persist(
        string $action,
        ConsentState $state,
        ConsentPolicy $policy,
        Request $request,
        Response $response
    ): void {
        $this->calls++;
    }

    public function getCalls(): int
    {
        return $this->calls;
    }
}
