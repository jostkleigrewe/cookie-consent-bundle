<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Consent\Policy;

/**
 * ConsentPolicy - Beschreibt Cookie-Kategorien und Policy-Version
 *
 * DE: Immutables Wertobjekt das alle konfigurierten Cookie-Kategorien
 *     und deren Eigenschaften (Pflicht, Default, Label, Beschreibung) enthaelt.
 *     Die Policy-Version ermoeglicht Re-Consent bei Aenderungen.
 *
 * EN: Immutable value object containing all configured cookie categories
 *     and their properties (required, default, label, description).
 *     Policy version enables re-consent on changes.
 *
 * @example
 * // DE: Konfiguration in cookie_consent.yaml
 * // EN: Configuration in cookie_consent.yaml
 * cookie_consent:
 *     policy_version: '2.0'  # Bei Aenderung werden Nutzer erneut gefragt
 *     categories:
 *         necessary:
 *             label: 'Notwendig'
 *             description: 'Erforderlich fuer Basisfunktionen'
 *             required: true   # Kann nicht abgewaehlt werden
 *         analytics:
 *             label: 'Statistiken'
 *             description: 'Hilft uns die Nutzung zu verstehen'
 *             default: false   # Standardmaessig deaktiviert
 */
final class ConsentPolicy
{
    /**
     * DE: Normalisierte Kategorien mit allen Eigenschaften.
     * EN: Normalized categories with all properties.
     *
     * @var array<string, array{label: ?string, description: ?string, required: bool, default: bool}>
     */
    private array $categories;

    /**
     * @param array<string, array<string, mixed>> $categories DE: Rohe Kategorien-Konfiguration
     *                                                         EN: Raw categories configuration
     * @param string $policyVersion DE: Policy-Version (bei Aenderung: Re-Consent)
     *                              EN: Policy version (on change: re-consent)
     */
    public function __construct(array $categories, private readonly string $policyVersion)
    {
        // DE: Kategorien normalisieren und fehlende Werte mit Defaults auffuellen
        // EN: Normalize categories and fill missing values with defaults
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
     * DE: Gibt die Policy-Version zurueck.
     *     Bei Aenderung dieser Version werden bestehende Consents ungueltig.
     *
     * EN: Returns the policy version.
     *     Changing this version invalidates existing consents.
     *
     * @return string DE: Die Policy-Version | EN: The policy version
     */
    public function getPolicyVersion(): string
    {
        return $this->policyVersion;
    }

    /**
     * DE: Gibt alle konfigurierten Kategorien zurueck.
     *
     * EN: Returns all configured categories.
     *
     * @return array<string, array{label: ?string, description: ?string, required: bool, default: bool}>
     *         DE: Kategoriename => Konfiguration | EN: Category name => configuration
     *
     * @example
     * foreach ($policy->getCategories() as $name => $config) {
     *     echo $config['label'];
     *     if ($config['required']) {
     *         echo ' (Pflicht)';
     *     }
     * }
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    /**
     * DE: Normalisiert Praeferenzen basierend auf der Policy.
     *     - Pflicht-Kategorien sind immer true
     *     - Unbekannte Kategorien werden ignoriert
     *     - Fehlende Kategorien erhalten ihren Default-Wert
     *
     * EN: Normalizes preferences based on the policy.
     *     - Required categories are always true
     *     - Unknown categories are ignored
     *     - Missing categories receive their default value
     *
     * @param array<string, bool> $preferences DE: Rohe Praeferenzen vom Nutzer
     *                                         EN: Raw preferences from user
     * @return array<string, bool> DE: Normalisierte Praeferenzen | EN: Normalized preferences
     *
     * @example
     * $normalized = $policy->normalizePreferences(['analytics' => true]);
     * // ['necessary' => true, 'analytics' => true, 'marketing' => false]
     */
    public function normalizePreferences(array $preferences): array
    {
        $normalized = [];

        foreach ($this->categories as $name => $config) {
            // DE: Pflicht-Kategorien immer aktiviert
            // EN: Required categories always enabled
            if ($config['required']) {
                $normalized[$name] = true;
                continue;
            }

            // DE: Nutzer-Praeferenz uebernehmen wenn vorhanden
            // EN: Use user preference if provided
            if (array_key_exists($name, $preferences)) {
                $normalized[$name] = (bool) $preferences[$name];
                continue;
            }

            // DE: Sonst Default-Wert verwenden
            // EN: Otherwise use default value
            $normalized[$name] = $config['default'];
        }

        return $normalized;
    }

    /**
     * DE: Gibt Praeferenzen zurueck bei denen alle Kategorien akzeptiert sind.
     *
     * EN: Returns preferences where all categories are accepted.
     *
     * @return array<string, bool> DE: Alle Kategorien auf true | EN: All categories set to true
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
     * DE: Gibt Praeferenzen zurueck bei denen nur Pflicht-Kategorien akzeptiert sind.
     *
     * EN: Returns preferences where only required categories are accepted.
     *
     * @return array<string, bool> DE: Nur required=true Kategorien sind true
     *                             EN: Only required=true categories are true
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
