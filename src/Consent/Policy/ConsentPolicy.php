<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Consent\Policy;

/**
 * ConsentPolicy - Beschreibt Cookie-Kategorien und Policy-Version
 *
 * Immutables Wertobjekt das alle konfigurierten Cookie-Kategorien
 *     und deren Eigenschaften (Pflicht, Default, Label, Beschreibung) enthaelt.
 *     Die Policy-Version ermoeglicht Re-Consent bei Aenderungen.
 *
 * Immutable value object containing all configured cookie categories
 *     and their properties (required, default, label, description).
 *     Policy version enables re-consent on changes.
 *
 * @example
 * // Configuration in cookie_consent.yaml
 * cookie_consent:
 *     policy_version: '2.0'  # On change users are asked again
 *     categories:
 *         necessary:
 *             label: 'Necessary'
 *             description: 'Required for basic site functionality'
 *             required: true   # Cannot be deselected
 *         analytics:
 *             label: 'Analytics'
 *             description: 'Helps us understand usage'
 *             default: false   # Disabled by default
 */
final class ConsentPolicy
{
    /**
     * Normalized categories with all properties.
     *
     * @var array<string, array{label: ?string, description: ?string, required: bool, default: bool}>
     */
    private array $categories;

    /**
     * @param array<string, array<string, mixed>> $categories Raw categories configuration
     * @param string $policyVersion Policy version (on change: re-consent)
     */
    public function __construct(array $categories, private readonly string $policyVersion)
    {
        // Normalize categories and fill missing values with defaults
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

    /**
     * Returns the policy version.
     *     Changing this version invalidates existing consents.
     *
     * @return string The policy version
     */
    public function getPolicyVersion(): string
    {
        return $this->policyVersion;
    }

    /**
     * Returns all configured categories.
     *
     * @return array<string, array{label: ?string, description: ?string, required: bool, default: bool}>
     * Category name => configuration
     *
     * @example
     * foreach ($policy->getCategories() as $name => $config) {
     *     echo $config['label'];
     *     if ($config['required']) {
     *         echo ' (required)';
     *     }
     * }
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    /**
     * Normalizes preferences based on the policy.
     *     - Required categories are always true
     *     - Unknown categories are ignored
     *     - Missing categories receive their default value
     *
     * @param array<string, bool> $preferences Raw preferences from user
     * @return array<string, bool> Normalized preferences
     *
     * @example
     * $normalized = $policy->normalizePreferences(['analytics' => true]);
     * // ['necessary' => true, 'analytics' => true, 'marketing' => false]
     */
    public function normalizePreferences(array $preferences): array
    {
        $normalized = [];

        foreach ($this->categories as $name => $config) {
            // Required categories always enabled
            if ($config['required']) {
                $normalized[$name] = true;
                continue;
            }

            // Use user preference if provided
            if (array_key_exists($name, $preferences)) {
                $normalized[$name] = (bool) $preferences[$name];
                continue;
            }

            // Otherwise use default value
            $normalized[$name] = $config['default'];
        }

        return $normalized;
    }

    /**
     * Returns preferences where all categories are accepted.
     *
     * @return array<string, bool> All categories set to true
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
     * Returns preferences where only required categories are accepted.
     *
     * @return array<string, bool> Only required=true categories are true
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
