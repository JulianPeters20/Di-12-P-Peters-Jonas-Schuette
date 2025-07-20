<?php
declare(strict_types=1);

/**
 * Exception für Sicherheitsverletzungen
 * Wird bei CSRF-Token-Fehlern, unautorisierten Zugriffen etc. verwendet
 * Ersetzt die unsicheren die()-Aufrufe mit ordnungsgemäßer Fehlerbehandlung
 */
class SecurityException extends Exception {
    /**
     * Konstruktor für Sicherheits-Exception
     *
     * @param string $message Fehlermeldung
     * @param int $code Fehlercode
     * @param Throwable|null $previous Vorherige Exception
     */
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}
