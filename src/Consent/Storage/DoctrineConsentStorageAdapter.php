<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Consent\Storage;

use Jostkleigrewe\CookieConsentBundle\Consent\Model\ConsentState;
use Jostkleigrewe\CookieConsentBundle\Consent\Policy\ConsentPolicy;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * DoctrineConsentStorageAdapter - Speichert Consent in der Datenbank
 *
 * DE: Storage-Backend fuer persistente Consent-Speicherung via Doctrine DBAL.
 *     Ermoeglicht geraete-/session-uebergreifende Consent-Nutzung.
 *     Benoetigt die Tabelle 'cookie_consent' (siehe docs/doctrine-storage.md).
 *
 * EN: Storage backend for persistent consent storage via Doctrine DBAL.
 *     Enables cross-device/session consent reuse.
 *     Requires the 'cookie_consent' table (see docs/doctrine-storage.md).
 *
 * Tabellenstruktur / Table structure:
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
 *     storage: doctrine  # oder 'both' fuer Cookie + DB
 */
final class DoctrineConsentStorageAdapter implements ConsentStorageInterface
{
    /**
     * DE: Name der Datenbank-Tabelle.
     * EN: Database table name.
     */
    private const TABLE = 'cookie_consent';

    /**
     * @param Connection $connection DE: Doctrine DBAL Connection | EN: Doctrine DBAL connection
     * @param ConsentIdProvider $idProvider DE: ID-Provider fuer stabile Nutzer-ID
     *                                       EN: ID provider for stable user ID
     * @param ConsentPolicy $policy DE: Policy fuer Version | EN: Policy for version
     */
    public function __construct(
        private readonly Connection $connection,
        private readonly ConsentIdProvider $idProvider,
        private readonly ConsentPolicy $policy,
    ) {
    }

    /**
     * DE: Laedt Consent-State aus der Datenbank.
     *     Benoetigt eine gueltige Consent-ID im Cookie.
     *
     * EN: Loads consent state from the database.
     *     Requires a valid consent ID in the cookie.
     *
     * @param Request $request DE: HTTP-Request | EN: HTTP request
     * @return ConsentState DE: Geladener oder leerer State | EN: Loaded or empty state
     *
     * @throws RuntimeException DE: Wenn Tabelle nicht existiert | EN: If table doesn't exist
     */
    public function load(Request $request): ConsentState
    {
        // DE: Consent-ID aus Cookie holen
        // EN: Get consent ID from cookie
        $id = $this->idProvider->getId($request);
        if ($id === null) {
            return ConsentState::empty($this->policy->getPolicyVersion());
        }

        // DE: Aus Datenbank laden
        // EN: Load from database
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

        // DE: Policy-Version pruefen
        // EN: Check policy version
        if (($row['policy_version'] ?? null) !== $this->policy->getPolicyVersion()) {
            return ConsentState::empty($this->policy->getPolicyVersion());
        }

        // DE: Praeferenzen parsen
        // EN: Parse preferences
        $preferences = json_decode((string) $row['preferences'], true);
        if (!is_array($preferences)) {
            $preferences = [];
        }

        // DE: Zeitpunkt parsen
        // EN: Parse timestamp
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
     * DE: Speichert Consent-State in der Datenbank.
     *     Erstellt oder aktualisiert den Eintrag (Upsert).
     *
     * EN: Saves consent state to the database.
     *     Creates or updates the entry (upsert).
     *
     * @param Request $request DE: HTTP-Request | EN: HTTP request
     * @param Response $response DE: HTTP-Response (fuer ID-Cookie) | EN: HTTP response (for ID cookie)
     * @param ConsentState $state DE: Zu speichernder State | EN: State to save
     *
     * @throws RuntimeException DE: Bei Datenbank-Fehlern | EN: On database errors
     */
    public function save(Request $request, Response $response, ConsentState $state): void
    {
        // DE: ID sicherstellen (erstellt Cookie falls noetig)
        // EN: Ensure ID (creates cookie if needed)
        $id = $this->idProvider->ensureId($request, $response);
        $payload = json_encode($state->getPreferences(), JSON_THROW_ON_ERROR);
        $decidedAt = $state->getDecidedAt()?->format('Y-m-d H:i:s');

        try {
            // DE: Pruefen ob Eintrag existiert
            // EN: Check if entry exists
            $exists = $this->connection->fetchOne(
                sprintf('SELECT 1 FROM %s WHERE id = ?', self::TABLE),
                [$id]
            );

            if ($exists) {
                // DE: Update bestehender Eintrag
                // EN: Update existing entry
                $this->connection->update(self::TABLE, [
                    'preferences' => $payload,
                    'policy_version' => $state->getPolicyVersion(),
                    'decided_at' => $decidedAt,
                ], ['id' => $id]);

                return;
            }

            // DE: Neuer Eintrag
            // EN: New entry
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
