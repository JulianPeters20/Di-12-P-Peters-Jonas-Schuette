<?php
declare(strict_types=1);

/**
 * Exception für Sicherheitsverletzungen
 */
class SecurityException extends Exception {
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}
