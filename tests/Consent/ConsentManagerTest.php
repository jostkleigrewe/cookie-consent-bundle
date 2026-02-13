<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Tests\Consent;

use Jostkleigrewe\CookieConsentBundle\Config\LoggingConfig;
use Jostkleigrewe\CookieConsentBundle\Config\LogLevel;
use Jostkleigrewe\CookieConsentBundle\Policy\ConsentPolicy;
use Jostkleigrewe\CookieConsentBundle\Service\ConsentLogger;
use Jostkleigrewe\CookieConsentBundle\Service\ConsentManager;
use Jostkleigrewe\CookieConsentBundle\Service\NullAuditLogPersister;
use Jostkleigrewe\CookieConsentBundle\Tests\Support\InMemoryConsentStorage;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ConsentManagerTest extends TestCase
{
    public function testPreferencesDefaultToRequiredAndDefaults(): void
    {
        $policy = new ConsentPolicy([
            'necessary' => ['required' => true, 'default' => true],
            'analytics' => ['required' => false, 'default' => false],
        ], '1');

        $manager = new ConsentManager(
            new InMemoryConsentStorage('1'),
            $policy,
            new ConsentLogger(null, new LoggingConfig(enabled: false, level: LogLevel::Info, anonymizeIp: true, retentionDays: null), new NullAuditLogPersister())
        );

        $preferences = $manager->getPreferences(new Request());

        self::assertSame([
            'necessary' => ['allowed' => true, 'vendors' => []],
            'analytics' => ['allowed' => false, 'vendors' => []],
        ], $preferences);
    }

    public function testAcceptAllEnablesAllCategories(): void
    {
        $policy = new ConsentPolicy([
            'necessary' => ['required' => true, 'default' => true],
            'analytics' => ['required' => false, 'default' => false],
        ], '1');

        $manager = new ConsentManager(
            new InMemoryConsentStorage('1'),
            $policy,
            new ConsentLogger(null, new LoggingConfig(enabled: false, level: LogLevel::Info, anonymizeIp: true, retentionDays: null), new NullAuditLogPersister())
        );

        $state = $manager->acceptAll(new Request(), new Response());

        self::assertSame([
            'necessary' => ['allowed' => true, 'vendors' => []],
            'analytics' => ['allowed' => true, 'vendors' => []],
        ], $state->getPreferences());
    }

    public function testRejectOptionalKeepsOnlyRequired(): void
    {
        $policy = new ConsentPolicy([
            'necessary' => ['required' => true, 'default' => true],
            'analytics' => ['required' => false, 'default' => false],
        ], '1');

        $manager = new ConsentManager(
            new InMemoryConsentStorage('1'),
            $policy,
            new ConsentLogger(null, new LoggingConfig(enabled: false, level: LogLevel::Info, anonymizeIp: true, retentionDays: null), new NullAuditLogPersister())
        );

        $state = $manager->rejectOptional(new Request(), new Response());

        self::assertSame([
            'necessary' => ['allowed' => true, 'vendors' => []],
            'analytics' => ['allowed' => false, 'vendors' => []],
        ], $state->getPreferences());
    }
}
