<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Tests\Kernel;

use Jostkleigrewe\CookieConsentBundle\Service\ConsentManager;
use Jostkleigrewe\CookieConsentBundle\Tests\TestKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

final class BundleBootTest extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    /**
     * @param array<string, mixed> $options
     */
    protected static function createKernel(array $options = []): KernelInterface
    {
        return new TestKernel($options['environment'] ?? 'test', $options['debug'] ?? false);
    }

    protected function tearDown(): void
    {
        self::ensureKernelShutdown();
        parent::tearDown();
    }

    public function testContainerBootsWithConsentManager(): void
    {
        self::bootKernel();

        $container = static::getContainer();

        self::assertTrue($container->has(ConsentManager::class));
    }
}
