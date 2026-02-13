<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Tests\Twig;

use Jostkleigrewe\CookieConsentBundle\Config\GoogleConsentModeConfig;
use Jostkleigrewe\CookieConsentBundle\Config\GoogleConsentModeMappingConfig;
use Jostkleigrewe\CookieConsentBundle\Config\LoggingConfig;
use Jostkleigrewe\CookieConsentBundle\Config\LogLevel;
use Jostkleigrewe\CookieConsentBundle\Config\UiConfig;
use Jostkleigrewe\CookieConsentBundle\Policy\ConsentPolicy;
use Jostkleigrewe\CookieConsentBundle\Service\ConsentLogger;
use Jostkleigrewe\CookieConsentBundle\Service\ConsentManager;
use Jostkleigrewe\CookieConsentBundle\Service\NullAuditLogPersister;
use Jostkleigrewe\CookieConsentBundle\Security\ConsentCsrfTokenManager;
use Jostkleigrewe\CookieConsentBundle\Tests\Support\InMemoryConsentStorage;
use Jostkleigrewe\CookieConsentBundle\Twig\ConsentTwigExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

/**
 * DE: Tests fuer die ConsentTwigExtension.
 * EN: Tests for the ConsentTwigExtension.
 */
final class ConsentTwigExtensionTest extends TestCase
{
    private static function createDefaultUiConfig(): UiConfig
    {
        return new UiConfig(
            template: '@CookieConsent/modal.html.twig',
            variant: 'default',
            theme: 'light',
            density: 'normal',
            position: 'center',
            privacyUrl: '/privacy',
            imprintUrl: null,
            reloadOnChange: false,
        );
    }

    private static function createDefaultGoogleConsentModeConfig(): GoogleConsentModeConfig
    {
        return new GoogleConsentModeConfig(
            enabled: false,
            mapping: new GoogleConsentModeMappingConfig(
                analyticsStorage: 'analytics',
                adStorage: 'marketing',
                adUserData: 'marketing',
                adPersonalization: 'marketing',
            ),
        );
    }

    private static function createDefaultLoggingConfig(): LoggingConfig
    {
        return new LoggingConfig(
            enabled: false,
            level: LogLevel::Info,
            anonymizeIp: true,
            retentionDays: null,
        );
    }

    public function testRenderStylesReturnsValidLinkTag(): void
    {
        $extension = $this->createExtensionWithMockedPackages('/assets/cookie_consent-abc123.css');

        $result = $extension->renderStyles();

        self::assertStringContainsString('<link rel="stylesheet"', $result);
        self::assertStringContainsString('href="/assets/cookie_consent-abc123.css"', $result);
    }

    public function testRenderStylesEscapesSpecialCharacters(): void
    {
        // DE: URL mit Sonderzeichen testen (XSS-Schutz)
        // EN: Test URL with special characters (XSS protection)
        $extension = $this->createExtensionWithMockedPackages('/assets/test.css?v=1&foo=bar"onload="alert(1)');

        $result = $extension->renderStyles();

        // DE: Anführungszeichen und Ampersand müssen escaped sein
        // EN: Quotes and ampersand must be escaped
        self::assertStringContainsString('&amp;', $result);
        self::assertStringContainsString('&quot;', $result);
        self::assertStringNotContainsString('"onload="', $result);
    }

    public function testHasConsentForReturnsFalseWithoutRequest(): void
    {
        $extension = $this->createExtensionWithEmptyRequestStack();

        self::assertFalse($extension->hasConsentFor('analytics'));
    }

    public function testHasConsentForReturnsTrueForRequiredCategory(): void
    {
        $extension = $this->createExtensionWithRequest();

        // DE: 'necessary' ist required und default=true
        // EN: 'necessary' is required and default=true
        self::assertTrue($extension->hasConsentFor('necessary'));
    }

    public function testHasConsentForReturnsFalseForOptionalCategory(): void
    {
        $extension = $this->createExtensionWithRequest();

        // DE: 'analytics' ist optional und default=false
        // EN: 'analytics' is optional and default=false
        self::assertFalse($extension->hasConsentFor('analytics'));
    }

    public function testGetCategoriesReturnsConfiguredCategories(): void
    {
        $extension = $this->createExtensionWithRequest();

        $categories = $extension->getCategories();

        self::assertArrayHasKey('necessary', $categories);
        self::assertArrayHasKey('analytics', $categories);
        self::assertTrue($categories['necessary']['required']);
        self::assertFalse($categories['analytics']['required']);
    }

    public function testGetFunctionsReturnsAllExpectedFunctions(): void
    {
        $extension = $this->createExtensionWithRequest();

        $functions = $extension->getFunctions();
        $functionNames = array_map(fn($f) => $f->getName(), $functions);

        self::assertContains('cookie_consent_styles', $functionNames);
        self::assertContains('cookie_consent_modal', $functionNames);
        self::assertContains('cookie_consent_has', $functionNames);
        self::assertContains('cookie_consent_vendor_has', $functionNames);
        self::assertContains('cookie_consent_preferences', $functionNames);
        self::assertContains('cookie_consent_required', $functionNames);
        self::assertContains('cookie_consent_categories', $functionNames);
    }

    private function createExtensionWithMockedPackages(string $assetUrl): ConsentTwigExtension
    {
        $packages = $this->createMock(Packages::class);
        $packages->method('getUrl')->willReturn($assetUrl);

        return $this->createExtension($packages, $this->createRequestStackWithRequest());
    }

    private function createExtensionWithEmptyRequestStack(): ConsentTwigExtension
    {
        $requestStack = new RequestStack();

        return $this->createExtension($this->createMock(Packages::class), $requestStack);
    }

    private function createExtensionWithRequest(): ConsentTwigExtension
    {
        return $this->createExtension(
            $this->createMock(Packages::class),
            $this->createRequestStackWithRequest()
        );
    }

    private function createRequestStackWithRequest(): RequestStack
    {
        $requestStack = new RequestStack();
        $requestStack->push(new Request());

        return $requestStack;
    }

    private function createExtension(Packages $packages, RequestStack $requestStack): ConsentTwigExtension
    {
        $policy = new ConsentPolicy([
            'necessary' => ['label' => 'Necessary', 'required' => true, 'default' => true],
            'analytics' => ['label' => 'Analytics', 'required' => false, 'default' => false],
        ], '1');

        $consentManager = new ConsentManager(
            new InMemoryConsentStorage('1'),
            $policy,
            new ConsentLogger(null, self::createDefaultLoggingConfig(), new NullAuditLogPersister())
        );

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')->willReturn('/_cookie-consent');

        // DE: Echte Klasse mit leerer RequestStack - generiert gültigen Token ohne Session
        // EN: Real class with empty RequestStack - generates valid token without session
        $csrfTokenManager = new ConsentCsrfTokenManager(new RequestStack());

        return new ConsentTwigExtension(
            $this->createMock(Environment::class),
            $consentManager,
            $policy,
            $requestStack,
            $urlGenerator,
            $csrfTokenManager,
            $packages,
            self::createDefaultUiConfig(),
            self::createDefaultGoogleConsentModeConfig(),
        );
    }
}
