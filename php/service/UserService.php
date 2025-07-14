<?php
declare(strict_types=1);

require_once __DIR__ . '/../model/NutzerDAO.php';
require_once __DIR__ . '/../model/Nutzer.php';

/**
 * Business Logic Layer für Nutzer-Operationen
 * Enthält Passwort-Hashing und Validierung
 */
class UserService {
    private NutzerDAO $nutzerDAO;

    public function __construct() {
        $this->nutzerDAO = new NutzerDAO();
    }

    /**
     * Registriert einen neuen Nutzer mit gehashtem Passwort
     */
    public function registrieren(string $benutzername, string $email, string $passwort): bool {
        // Passwort-Validierung
        $passwordErrors = $this->validatePassword($passwort);
        if (!empty($passwordErrors)) {
            throw new InvalidArgumentException("Passwort-Validierung fehlgeschlagen: " . implode(', ', $passwordErrors));
        }

        // Email-Validierung
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Ungültige E-Mail-Adresse");
        }

        // Passwort hashen (Business Logic)
        $hashedPassword = password_hash($passwort, PASSWORD_DEFAULT);
        
        return $this->nutzerDAO->registrieren($benutzername, $email, $hashedPassword);
    }

    /**
     * Authentifiziert einen Nutzer
     */
    public function authentifizieren(string $email, string $passwort): ?Nutzer {
        $nutzer = $this->nutzerDAO->findeNachEmail($email);
        
        if (!$nutzer) {
            return null;
        }

        // Passwort-Verifikation (Business Logic)
        if (password_verify($passwort, $nutzer->passwortHash)) {
            return $nutzer;
        }

        return null;
    }

    /**
     * Ändert das Passwort eines Nutzers
     */
    public function passwortAendern(int $nutzerId, string $altesPasswort, string $neuesPasswort): bool {
        $nutzer = $this->nutzerDAO->findeNachID($nutzerId);
        if (!$nutzer) {
            throw new InvalidArgumentException("Nutzer nicht gefunden");
        }

        // Altes Passwort prüfen
        if (!password_verify($altesPasswort, $nutzer->passwortHash)) {
            throw new InvalidArgumentException("Altes Passwort ist falsch");
        }

        // Neues Passwort validieren
        $passwordErrors = $this->validatePassword($neuesPasswort);
        if (!empty($passwordErrors)) {
            throw new InvalidArgumentException("Neues Passwort ungültig: " . implode(', ', $passwordErrors));
        }

        // Neues Passwort hashen
        $hashedPassword = password_hash($neuesPasswort, PASSWORD_DEFAULT);
        
        return $this->nutzerDAO->passwortAktualisieren($nutzerId, $hashedPassword);
    }

    /**
     * Validiert ein Passwort nach Sicherheitsrichtlinien
     */
    private function validatePassword(string $password): array {
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = 'Passwort muss mindestens 8 Zeichen lang sein';
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Passwort muss mindestens einen Großbuchstaben enthalten';
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Passwort muss mindestens einen Kleinbuchstaben enthalten';
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Passwort muss mindestens eine Zahl enthalten';
        }
        
        return $errors;
    }

    /**
     * Delegiert an DAO
     */
    public function findeNachID(int $id): ?Nutzer {
        return $this->nutzerDAO->findeNachID($id);
    }

    /**
     * Delegiert an DAO
     */
    public function findeNachEmail(string $email): ?Nutzer {
        return $this->nutzerDAO->findeNachEmail($email);
    }

    /**
     * Delegiert an DAO
     */
    public function findeAlle(): array {
        return $this->nutzerDAO->findeAlle();
    }

    /**
     * Delegiert an DAO
     */
    public function loesche(int $id): bool {
        return $this->nutzerDAO->loesche($id);
    }
}
