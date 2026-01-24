<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Tests\Consent;

use Jostkleigrewe\CookieConsentBundle\Consent\Policy\ConsentPolicy;
use Jostkleigrewe\CookieConsentBundle\Consent\Service\ConsentLogger;
use Jostkleigrewe\CookieConsentBundle\Consent\Service\ConsentManager;
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
            new ConsentLogger(null, ['enabled' => false, 'level' => 'info', 'anonymize_ip' => true])
        );

        $preferences = $manager->getPreferences(new Request());

        self::assertSame([
            'necessary' => true,
            'analytics' => false,
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
            new ConsentLogger(null, ['enabled' => false, 'level' => 'info', 'anonymize_ip' => true])
        );

        $state = $manager->acceptAll(new Request(), new Response());

        self::assertSame([
            'necessary' => true,
            'analytics' => true,
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
            new ConsentLogger(null, ['enabled' => false, 'level' => 'info', 'anonymize_ip' => true])
        );

        $state = $manager->rejectOptional(new Request(), new Response());

        self::assertSame([
            'necessary' => true,
            'analytics' => false,
        ], $state->getPreferences());
    }
}
