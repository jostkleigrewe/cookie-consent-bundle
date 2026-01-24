<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Consent\Model;

/**
 * ConsentState - Immutables Wertobjekt fuer den Consent-Zustand
 *
 * DE: Repraesentiert den aktuellen Consent-Status eines Nutzers.
 *     Enthalt die gewaehlten Praeferenzen, Policy-Version und Zeitpunkt.
 *     Immutables Objekt - Aenderungen erzeugen neue Instanzen.
 *
 * EN: Represents the current consent state of a user.
 *     Contains chosen preferences, policy version, and timestamp.
 *     Immutable object - changes create new instances.
 *
 * @example
 * // DE: Leeren State erstellen (kein Consent vorhanden)
 * // EN: Create empty state (no consent present)
 * $state = ConsentState::empty('1.0');
 * $state->hasDecision(); // false
 *
 * @example
 * // DE: State mit Praeferenzen erstellen
 * // EN: Create state with preferences
 * $state = $state->withPreferences(['analytics' => true]);
 * $state->hasDecision(); // true
 * $state->isAllowed('analytics'); // true
 */
final class ConsentState
{
    /**
     * @param array<string, bool> $preferences DE: Gewahlte Praeferenzen | EN: Chosen preferences
     * @param string $policyVersion DE: Policy-Version zum Zeitpunkt der Entscheidung
     *                              EN: Policy version at decision time
     * @param \DateTimeImmutable|null $decidedAt DE: Zeitpunkt der Entscheidung (null = keine)
     *                                            EN: Decision timestamp (null = none)
     */
    public function __construct(
        private array $preferences,
        private readonly string $policyVersion,
        private ?\DateTimeImmutable $decidedAt
    ) {
    }

    /**
     * DE: Erstellt einen leeren Consent-State (keine Entscheidung getroffen).
     *
     * EN: Creates an empty consent state (no decision made).
     *
     * @param string $policyVersion DE: Aktuelle Policy-Version | EN: Current policy version
     * @return self DE: Leerer Consent-State | EN: Empty consent state
     */
    public static function empty(string $policyVersion): self
    {
        return new self([], $policyVersion, null);
    }

    /**
     * DE: Prueft ob eine Consent-Entscheidung vorliegt.
     *
     * EN: Checks if a consent decision exists.
     *
     * @return bool DE: true wenn Nutzer entschieden hat | EN: true if user has decided
     */
    public function hasDecision(): bool
    {
        return $this->decidedAt !== null;
    }

    /**
     * DE: Gibt die gespeicherten Praeferenzen zurueck.
     *
     * EN: Returns the stored preferences.
     *
     * @return array<string, bool> DE: Kategorie => erlaubt | EN: Category => allowed
     */
    public function getPreferences(): array
    {
        return $this->preferences;
    }

    /**
     * DE: Prueft ob eine bestimmte Kategorie erlaubt ist.
     *
     * EN: Checks if a specific category is allowed.
     *
     * @param string $category DE: Name der Kategorie | EN: Category name
     * @return bool DE: true wenn erlaubt | EN: true if allowed
     *
     * @example
     * if ($state->isAllowed('analytics')) {
     *     // Analytics-Tracking aktivieren
     * }
     */
    public function isAllowed(string $category): bool
    {
        return $this->preferences[$category] ?? false;
    }

    /**
     * DE: Gibt die Policy-Version zurueck fuer die dieser State gilt.
     *
     * EN: Returns the policy version this state applies to.
     *
     * @return string DE: Die Policy-Version | EN: The policy version
     */
    public function getPolicyVersion(): string
    {
        return $this->policyVersion;
    }

    /**
     * DE: Gibt den Zeitpunkt der Consent-Entscheidung zurueck.
     *
     * EN: Returns the timestamp of the consent decision.
     *
     * @return \DateTimeImmutable|null DE: Zeitpunkt oder null wenn keine Entscheidung
     *                                  EN: Timestamp or null if no decision
     */
    public function getDecidedAt(): ?\DateTimeImmutable
    {
        return $this->decidedAt;
    }

    /**
     * DE: Erstellt einen neuen State mit den gegebenen Praeferenzen.
     *     Setzt automatisch den aktuellen Zeitpunkt als Entscheidungszeitpunkt.
     *
     * EN: Creates a new state with the given preferences.
     *     Automatically sets current time as decision timestamp.
     *
     * @param array<string, bool> $preferences DE: Neue Praeferenzen | EN: New preferences
     * @return self DE: Neue Instanz mit Praeferenzen | EN: New instance with preferences
     *
     * @example
     * $newState = $state->withPreferences([
     *     'necessary' => true,
     *     'analytics' => true,
     *     'marketing' => false,
     * ]);
     */
    public function withPreferences(array $preferences): self
    {
        return new self($preferences, $this->policyVersion, new \DateTimeImmutable('now'));
    }
}
