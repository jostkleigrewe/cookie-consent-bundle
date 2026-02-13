<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Tests\Event;

use Jostkleigrewe\CookieConsentBundle\Config\LoggingConfig;
use Jostkleigrewe\CookieConsentBundle\Config\LogLevel;
use Jostkleigrewe\CookieConsentBundle\Policy\ConsentPolicy;
use Jostkleigrewe\CookieConsentBundle\Service\ConsentLogger;
use Jostkleigrewe\CookieConsentBundle\Service\ConsentManager;
use Jostkleigrewe\CookieConsentBundle\Service\NullAuditLogPersister;
use Jostkleigrewe\CookieConsentBundle\Event\ConsentChangedEvent;
use Jostkleigrewe\CookieConsentBundle\Tests\Support\InMemoryConsentStorage;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ConsentChangedEventTest extends TestCase
{
    public function testEventIsDispatchedOnSave(): void
    {
        $policy = new ConsentPolicy([
            'necessary' => ['required' => true, 'default' => true],
            'analytics' => ['required' => false, 'default' => false],
        ], '1');

        $dispatcher = new EventDispatcher();
        $called = false;

        $dispatcher->addListener(ConsentChangedEvent::NAME, static function (ConsentChangedEvent $event) use (&$called): void {
            $called = true;
            TestCase::assertSame('custom', $event->getAction());
            TestCase::assertSame('1', $event->getPolicy()->getPolicyVersion());
        });

        $manager = new ConsentManager(
            new InMemoryConsentStorage('1'),
            $policy,
            new ConsentLogger(null, new LoggingConfig(enabled: false, level: LogLevel::Info, anonymizeIp: true, retentionDays: null), new NullAuditLogPersister()),
            $dispatcher
        );

        $manager->savePreferences(new Request(), new Response(), [
            'analytics' => ['allowed' => true, 'vendors' => []],
        ]);

        self::assertTrue($called);
    }
}
