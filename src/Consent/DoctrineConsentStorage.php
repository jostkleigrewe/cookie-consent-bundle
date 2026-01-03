<?php

declare(strict_types=1);

namespace JostKleigrewe\CookieConsentBundle\Consent;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class DoctrineConsentStorage implements ConsentStorageInterface
{
    private const TABLE = 'cookie_consent';

    public function __construct(
        private readonly Connection $connection,
        private readonly ConsentIdProvider $idProvider,
        private readonly ConsentPolicy $policy,
    ) {
    }

    public function load(Request $request): ConsentState
    {
        $id = $this->idProvider->getId($request);
        if ($id === null) {
            return ConsentState::empty($this->policy->getPolicyVersion());
        }

        try {
            $row = $this->connection->fetchAssociative(
                sprintf('SELECT preferences, policy_version, decided_at FROM %s WHERE id = ?', self::TABLE),
                [$id]
            );
        } catch (Exception $exception) {
            throw new \RuntimeException('Consent table is missing or unreachable. See docs for setup.', 0, $exception);
        }

        if (!$row) {
            return ConsentState::empty($this->policy->getPolicyVersion());
        }

        if (($row['policy_version'] ?? null) !== $this->policy->getPolicyVersion()) {
            return ConsentState::empty($this->policy->getPolicyVersion());
        }

        $preferences = json_decode((string) $row['preferences'], true);
        if (!is_array($preferences)) {
            $preferences = [];
        }

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

    public function save(Request $request, Response $response, ConsentState $state): void
    {
        $id = $this->idProvider->ensureId($request, $response);
        $payload = json_encode($state->getPreferences(), JSON_THROW_ON_ERROR);
        $decidedAt = $state->getDecidedAt()?->format('Y-m-d H:i:s');

        try {
            $exists = $this->connection->fetchOne(
                sprintf('SELECT 1 FROM %s WHERE id = ?', self::TABLE),
                [$id]
            );

            if ($exists) {
                $this->connection->update(self::TABLE, [
                    'preferences' => $payload,
                    'policy_version' => $state->getPolicyVersion(),
                    'decided_at' => $decidedAt,
                ], ['id' => $id]);

                return;
            }

            $this->connection->insert(self::TABLE, [
                'id' => $id,
                'preferences' => $payload,
                'policy_version' => $state->getPolicyVersion(),
                'decided_at' => $decidedAt,
            ]);
        } catch (Exception $exception) {
            throw new \RuntimeException('Failed to persist consent. See docs for database setup.', 0, $exception);
        }
    }
}
