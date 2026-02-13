<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Config;

/**
 * LoggingConfig - Konfiguration für Consent-Logging
 *
 * Immutables Wertobjekt mit Logging-Einstellungen.
 *     Steuert ob und wie Consent-Änderungen protokolliert werden.
 *
 * Immutable value object with logging settings.
 *     Controls if and how consent changes are logged.
 *
 * @example
 * // config/packages/cookie_consent.yaml
 * cookie_consent:
 *     logging:
 *         enabled: true
 *         level: info
 *         anonymize_ip: true
 *         retention_days: 365
 */
final readonly class LoggingConfig
{
    /**
     * @param bool     $enabled       Whether logging is enabled
     * @param LogLevel $level         Log level (debug, info, notice, warning, error)
     * @param bool     $anonymizeIp   Whether to anonymize IP addresses
     * @param int|null $retentionDays Days to retain logs (null = forever)
     */
    public function __construct(
        public bool $enabled,
        public LogLevel $level,
        public bool $anonymizeIp,
        public ?int $retentionDays,
    ) {
    }

    /**
     * DE: Erstellt LoggingConfig aus einem Konfigurations-Array.
     *     Das Array muss alle Keys enthalten (wird durch Symfony Config Definition garantiert).
     * EN: Creates LoggingConfig from a configuration array.
     *     The array must contain all keys (guaranteed by Symfony Config Definition).
     *
     * @param array{
     *     enabled: bool,
     *     level: string,
     *     anonymize_ip: bool,
     *     retention_days: ?int
     * } $config Configuration array
     */
    public static function fromArray(array $config): self
    {
        return new self(
            enabled: $config['enabled'],
            level: LogLevel::from($config['level']),
            anonymizeIp: $config['anonymize_ip'],
            retentionDays: $config['retention_days'],
        );
    }
}
