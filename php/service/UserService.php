<?php
declare(strict_types=1);

require_once __DIR__ . '/../model/NutzerDAO.php';
require_once __DIR__ . '/../model/Nutzer.php';

/**
 * Business Logic Layer für Nutzer-Operationen
 * Enthält Passwort-Hashing und Validierung (getrennt von der Datenschicht)
 */
class UserService {
    private NutzerDAO $nutzerDAO;

    public function __construct() {
        $this->nutzerDAO = new NutzerDAO();
    }

    /**
     * Registriert einen neuen Nutzer mit gehashtem Passwort
     * Führt Validierungen durch und hasht das Passwort in der Business Logic
     *
     * @param string $benutzername Gewünschter Benutzername
     * @param string $email E-Mail-Adresse des Nutzers
     * @param string $passwort Klartext-Passwort
     * @return bool true bei erfolgreicher Registrierung
     * @throws InvalidArgumentException bei Validierungsfehlern
     */
    public function registrieren(string $benutzername, string $email, string $passwort): bool {
        // Passwort-Validierung nach Sicherheitsrichtlinien
        $passwordErrors = $this->validatePassword($passwort);
        if (!empty($passwordErrors)) {
            throw new InvalidArgumentException("Passwort-Validierung fehlgeschlagen: " . implode(', ', $passwordErrors));
        }

        // E-Mail-Validierung
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Ungültige E-Mail-Adresse");
        }

        // Passwort hashen (Business Logic - nicht in der Datenschicht)
        $hashedPassword = password_hash($passwort, PASSWORD_DEFAULT);

        return $this->nutzerDAO->registrieren($benutzername, $email, $hashedPassword);
    }

    /**
     * Authentifiziert einen Nutzer mit E-Mail und Passwort
     * Führt Passwort-Verifikation in der Business Logic durch
     *
     * @param string $email E-Mail-Adresse
     * @param string $passwort Klartext-Passwort
     * @return Nutzer|null Nutzer-Objekt bei erfolgreicher Authentifizierung, null sonst
     */
    public function authentifizieren(string $email, string $passwort): ?Nutzer {
        $nutzer = $this->nutzerDAO->findeNachEmail($email);

        if (!$nutzer) {
            return null;
        }

        // Passwort-Verifikation (Business Logic - nicht in der Datenschicht)
        if (password_verify($passwort, $nutzer->passwortHash)) {
            return $nutzer;
        }

        return null;
    }

    /**
     * Ändert das Passwort eines Nutzers mit Validierung
     * Prüft das alte Passwort und validiert das neue nach Sicherheitsrichtlinien
     *
     * @param int $nutzerId ID des Nutzers
     * @param string $altesPasswort Aktuelles Passwort zur Verifikation
     * @param string $neuesPasswort Neues Passwort
     * @return bool true bei erfolgreicher Änderung
     * @throws InvalidArgumentException bei Validierungsfehlern
     */
    public function passwortAendern(int $nutzerId, string $altesPasswort, string $neuesPasswort): bool {
        $nutzer = $this->nutzerDAO->findeNachID($nutzerId);
        if (!$nutzer) {
            throw new InvalidArgumentException("Nutzer nicht gefunden");
        }

        // Altes Passwort zur Sicherheit prüfen
        if (!password_verify($altesPasswort, $nutzer->passwortHash)) {
            throw new InvalidArgumentException("Altes Passwort ist falsch");
        }

        // Neues Passwort nach Sicherheitsrichtlinien validieren
        $passwordErrors = $this->validatePassword($neuesPasswort);
        if (!empty($passwordErrors)) {
            throw new InvalidArgumentException("Neues Passwort ungültig: " . implode(', ', $passwordErrors));
        }

        // Neues Passwort hashen (Business Logic)
        $hashedPassword = password_hash($neuesPasswort, PASSWORD_DEFAULT);

        return $this->nutzerDAO->passwortAktualisieren($nutzerId, $hashedPassword);
    }

    /**
     * Validiert ein Passwort nach Sicherheitsrichtlinien
     * Prüft Länge, Groß-/Kleinbuchstaben und Zahlen
     *
     * @param string $password Zu validierendes Passwort
     * @return array Array mit Fehlermeldungen (leer wenn gültig)
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
     * Findet einen Nutzer anhand der ID
     * Delegiert an die Datenschicht
     */
    public function findeNachID(int $id): ?Nutzer {
        return $this->nutzerDAO->findeNachID($id);
    }

    /**
     * Findet einen Nutzer anhand der E-Mail-Adresse
     * Delegiert an die Datenschicht
     */
    public function findeNachEmail(string $email): ?Nutzer {
        return $this->nutzerDAO->findeNachEmail($email);
    }

    /**
     * Findet alle Nutzer
     * Delegiert an die Datenschicht
     */
    public function findeAlle(): array {
        return $this->nutzerDAO->findeAlle();
    }

    /**
     * Löscht einen Nutzer (mit CASCADE DELETE für verknüpfte Daten)
     * Delegiert an die Datenschicht
     */
    public function loesche(int $id): bool {
        return $this->nutzerDAO->loesche($id);
    }
}
