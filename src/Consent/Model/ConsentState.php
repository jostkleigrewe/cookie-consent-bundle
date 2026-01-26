<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Consent\Model;

/**
 * ConsentState - Immutables Wertobjekt fuer den Consent-Zustand
 *
 * Repraesentiert den aktuellen Consent-Status eines Nutzers.
 *     Enthalt die gewaehlten Praeferenzen, Policy-Version und Zeitpunkt.
 *     Immutables Objekt - Aenderungen erzeugen neue Instanzen.
 *
 * Represents the current consent state of a user.
 *     Contains chosen preferences, policy version, and timestamp.
 *     Immutable object - changes create new instances.
 *
 * @example
 * // Create empty state (no consent present)
 * $state = ConsentState::empty('1.0');
 * $state->hasDecision(); // false
 *
 * @example
 * // Create state with preferences
 * $state = $state->withPreferences([
 *     'analytics' => ['allowed' => true, 'vendors' => []],
 * ]);
 * $state->hasDecision(); // true
 * $state->isAllowed('analytics'); // true
 */
final class ConsentState
{
    /**
     * @param array<string, array{allowed: bool, vendors: array<string, bool>}> $preferences Chosen preferences
     * @param string $policyVersion Policy version at decision time
     * @param \DateTimeImmutable|null $decidedAt Decision timestamp (null = none)
     */
    public function __construct(
        private array $preferences,
        private readonly string $policyVersion,
        private ?\DateTimeImmutable $decidedAt
    ) {
    }

    /**
     * Creates an empty consent state (no decision made).
     *
     * @param string $policyVersion Current policy version
     * @return self Empty consent state
     */
    public static function empty(string $policyVersion): self
    {
        return new self([], $policyVersion, null);
    }

    /**
     * Checks if a consent decision exists.
     *
     * @return bool true if user has decided
     */
    public function hasDecision(): bool
    {
        return $this->decidedAt !== null;
    }

    /**
     * Returns the stored preferences.
     *
     * @return array<string, array{allowed: bool, vendors: array<string, bool>}> Category => allowed
     */
    public function getPreferences(): array
    {
        return $this->preferences;
    }

    /**
     * Checks if a specific category is allowed.
     *
     * @param string $category Category name
     * @return bool true if allowed
     *
     * @example
     * if ($state->isAllowed('analytics')) {
     *     // Analytics-Tracking aktivieren
     * }
     */
    public function isAllowed(string $category, ?string $vendor = null): bool
    {
        $categoryData = $this->preferences[$category] ?? null;
        if (!is_array($categoryData)) {
            return false;
        }

        $allowed = (bool) $categoryData['allowed'];
        if ($vendor === null) {
            return $allowed;
        }

        $vendors = $categoryData['vendors'];

        return $allowed && (bool) ($vendors[$vendor] ?? false);
    }

    /**
     * Returns the policy version this state applies to.
     *
     * @return string The policy version
     */
    public function getPolicyVersion(): string
    {
        return $this->policyVersion;
    }

    /**
     * Returns the timestamp of the consent decision.
     *
     * @return \DateTimeImmutable|null Timestamp or null if no decision
     */
    public function getDecidedAt(): ?\DateTimeImmutable
    {
        return $this->decidedAt;
    }

    /**
     * Creates a new state with the given preferences.
     *     Automatically sets current time as decision timestamp.
     *
     * @param array<string, array{allowed: bool, vendors: array<string, bool>}> $preferences New preferences
     * @return self New instance with preferences
     *
     * @example
     * $newState = $state->withPreferences([
 *     'necessary' => ['allowed' => true, 'vendors' => []],
 *     'analytics' => ['allowed' => true, 'vendors' => []],
 *     'marketing' => ['allowed' => false, 'vendors' => []],
     * ]);
     */
    public function withPreferences(array $preferences): self
    {
        return new self($preferences, $this->policyVersion, new \DateTimeImmutable('now'));
    }
}
