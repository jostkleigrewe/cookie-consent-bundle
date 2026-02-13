<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Config;

/**
 * LogLevel - Log-Level für Consent-Logging
 *
 * Backed Enum für typsichere Log-Level-Konfiguration.
 *     Entspricht den PSR-3 Log-Levels.
 *
 * Backed enum for type-safe log level configuration.
 *     Corresponds to PSR-3 log levels.
 */
enum LogLevel: string
{
    case Debug = 'debug';
    case Info = 'info';
    case Notice = 'notice';
    case Warning = 'warning';
    case Error = 'error';
}
