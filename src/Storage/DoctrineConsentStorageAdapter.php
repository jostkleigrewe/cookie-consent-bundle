<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Storage;

use Jostkleigrewe\CookieConsentBundle\Model\ConsentState;
use Jostkleigrewe\CookieConsentBundle\Policy\ConsentPolicy;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * DoctrineConsentStorageAdapter - Speichert Consent in der Datenbank
 *
 * Storage-Backend fuer persistente Consent-Speicherung via Doctrine DBAL.
 *     Ermoeglicht geraete-/session-uebergreifende Consent-Nutzung.
 *     Benoetigt die Tabelle 'cookie_consent' (siehe docs/doctrine-storage.md).
 *
 * Storage backend for persistent consent storage via Doctrine DBAL.
 *     Enables cross-device/session consent reuse.
 *     Requires the 'cookie_consent' table (see docs/doctrine-storage.md).
 *
 * Table structure:
 * CREATE TABLE cookie_consent (
 *     id VARCHAR(32) PRIMARY KEY,
 *     preferences JSON NOT NULL,
 *     policy_version VARCHAR(16) NOT NULL,
 *     decided_at DATETIME NOT NULL
 * );
 *
 * @example
 * // config/packages/cookie_consent.yaml
 * cookie_consent:
 *     storage: doctrine  # or 'both' for cookie + DB
 */
final class DoctrineConsentStorageAdapter implements ConsentStorageInterface
{
    /**
     * Database table name.
     */
    private const TABLE = 'cookie_consent';

    /**
     * @param Connection $connection Doctrine DBAL connection
     * @param ConsentIdProvider $idProvider ID provider for stable user ID
     * @param ConsentPolicy $policy Policy for version
     */
    public function __construct(
        private readonly Connection $connection,
        private readonly ConsentIdProvider $idProvider,
        private readonly ConsentPolicy $policy,
    ) {
    }

    /**
     * Loads consent state from the database.
     *     Requires a valid consent ID in the cookie.
     *
     * @param Request $request HTTP request
     * @return ConsentState Loaded or empty state
     *
     * @throws RuntimeException If table doesn't exist
     */
    public function load(Request $request): ConsentState
    {
        // Get consent ID from cookie
        $id = $this->idProvider->getId($request);
        if ($id === null) {
            return ConsentState::empty($this->policy->getPolicyVersion());
        }

        // Load from database
        try {
            $row = $this->connection->fetchAssociative(
                sprintf('SELECT preferences, policy_version, decided_at FROM %s WHERE id = ?', self::TABLE),
                [$id]
            );
        } catch (Exception $exception) {
            throw new RuntimeException('Consent table is missing or unreachable. See docs for setup.', 0, $exception);
        }

        if (!$row) {
            return ConsentState::empty($this->policy->getPolicyVersion());
        }

        // Check policy version
        if (($row['policy_version'] ?? null) !== $this->policy->getPolicyVersion()) {
            return ConsentState::empty($this->policy->getPolicyVersion());
        }

        // Parse preferences
        $preferences = json_decode((string) $row['preferences'], true);
        if (!is_array($preferences)) {
            $preferences = [];
        }

        // Parse timestamp
        $decidedAt = null;
        if (!empty($row['decided_at'])) {
            try {
                $decidedAt = new \DateTimeImmutable((string) $row['decided_at']);
            } catch (\Exception) {
                $decidedAt = null;
            }
        }

        return new ConsentState($preferences, $this->policy->getPolicyVersion(), $decidedAt);
    }

    /**
     * Saves consent state to the database.
     *     Creates or updates the entry (upsert).
     *
     * @param Request $request HTTP request
     * @param Response $response HTTP response (for ID cookie)
     * @param ConsentState $state State to save
     *
     * @throws RuntimeException On database errors
     */
    public function save(Request $request, Response $response, ConsentState $state): void
    {
        // Ensure ID (creates cookie if needed)
        $id = $this->idProvider->ensureId($request, $response);
        $payload = json_encode($state->getPreferences(), JSON_THROW_ON_ERROR);
        $decidedAt = $state->getDecidedAt()?->format('Y-m-d H:i:s');

        try {
            // Check if entry exists
            $exists = $this->connection->fetchOne(
                sprintf('SELECT 1 FROM %s WHERE id = ?', self::TABLE),
                [$id]
            );

            if ($exists) {
                // Update existing entry
                $this->connection->update(self::TABLE, [
                    'preferences' => $payload,
                    'policy_version' => $state->getPolicyVersion(),
                    'decided_at' => $decidedAt,
                ], ['id' => $id]);

                return;
            }

            // New entry
            $this->connection->insert(self::TABLE, [
                'id' => $id,
                'preferences' => $payload,
                'policy_version' => $state->getPolicyVersion(),
                'decided_at' => $decidedAt,
            ]);
        } catch (Exception $exception) {
            throw new RuntimeException('Failed to persist consent. See docs for database setup.', 0, $exception);
        }
    }
}
