<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Consent\Policy;

/**
 * DE: Beschreibt Kategorien, Defaults und Policy-Version.
 * EN: Describes categories, defaults, and policy version.
 */
final class ConsentPolicy
{
    /**
     * @var array<string, array{label: ?string, description: ?string, required: bool, default: bool}>
     */
    private array $categories;

    public function __construct(array $categories, private readonly string $policyVersion)
    {
        $normalized = [];
        foreach ($categories as $name => $config) {
            $normalized[$name] = [
                'label' => $config['label'] ?? $name,
                'description' => $config['description'] ?? null,
                'required' => (bool) ($config['required'] ?? false),
                'default' => (bool) ($config['default'] ?? false),
            ];
        }

        $this->categories = $normalized;
    }

    public function getPolicyVersion(): string
    {
        return $this->policyVersion;
    }

    /**
     * @return array<string, array{label: ?string, description: ?string, required: bool, default: bool}>
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    /**
     * @param array<string, bool> $preferences
     * @return array<string, bool>
     */
    public function normalizePreferences(array $preferences): array
    {
        $normalized = [];

        foreach ($this->categories as $name => $config) {
            if ($config['required']) {
                $normalized[$name] = true;
                continue;
            }

            if (array_key_exists($name, $preferences)) {
                $normalized[$name] = (bool) $preferences[$name];
                continue;
            }

            $normalized[$name] = $config['default'];
        }

        return $normalized;
    }

    /**
     * @return array<string, bool>
     */
    public function acceptAll(): array
    {
        $preferences = [];
        foreach ($this->categories as $name => $config) {
            $preferences[$name] = true;
        }

        return $preferences;
    }

    /**
     * @return array<string, bool>
     */
    public function rejectOptional(): array
    {
        $preferences = [];
        foreach ($this->categories as $name => $config) {
            $preferences[$name] = $config['required'];
        }

        return $preferences;
    }
}
