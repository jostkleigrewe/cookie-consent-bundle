<?php

declare(strict_types=1);

namespace Jostkleigrewe\CookieConsentBundle\Http;

/**
 * DE: Exception mit Fehlercode und HTTP-Status fuer Consent-Update-Validierung.
 * EN: Exception with error code and HTTP status for consent update validation.
 */
final class ConsentUpdateException extends \RuntimeException
{
    public function __construct(
        private readonly string $errorCode,
        private readonly int $statusCode,
        string $message
    ) {
        parent::__construct($message);
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
