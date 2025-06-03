<?php
declare(strict_types=1);

/**
 * Datenzugriffsobjekt (DAO) für Nutzer.
 * Achtung: Alle Daten sind nur temporär und werden NICHT persistent gespeichert!
 */
class NutzerDAO {
    // Dummy-Daten
    private static $benutzer = [
        [
            'benutzername' => 'StudentOne',
            'email' => 'student@beispiel.de',
            'passwort' => 'geheim123',
            'registriert' => '01.01.2024'
        ],
        [
            'benutzername' => 'MaxMustermann',
            'email' => 'max@uni.de',
            'passwort' => '12345678',
            'registriert' => '01.01.2025'
        ]
    ];

    /**
     * Gibt alle Benutzer zurück.
     */
    public static function getAlleBenutzer(): array {
        return self::$benutzer;
    }

    /**
     * Sucht Benutzer anhand von E-Mail und Passwort.
     */
    public static function findeBenutzer(string $email, string $passwort): ?array {
        foreach (self::$benutzer as $nutzer) {
            if ($nutzer['email'] === $email && $nutzer['passwort'] === $passwort) {
                return $nutzer;
            }
        }
        return null;
    }

    /**
     * Gibt Benutzer anhand E-Mail zurück.
     */
    public static function findeBenutzerNachEmail(string $email): ?array {
        foreach (self::$benutzer as $nutzer) {
            if ($nutzer['email'] === $email) {
                return $nutzer;
            }
        }
        return null;
    }

    /**
     * Fügt einen neuen Benutzer hinzu.
     */
    public static function addBenutzer(string $benutzername, string $email, string $passwort): void {
        self::$benutzer[] = [
            'benutzername' => $benutzername,
            'email' => $email,
            'passwort' => $passwort,
            'registriert' => date('d.m.Y')
        ];
    }

    /**
     * Löscht Benutzer anhand E-Mail.
     */
    public static function loescheBenutzer(string $email): bool {
        foreach (self::$benutzer as $key => $nutzer) {
            if ($nutzer['email'] === $email) {
                unset(self::$benutzer[$key]);
                return true;
            }
        }
        return false;
    }

    /**
     * Aktualisiert Nutzer-Daten.
     */
    public static function aktualisiereBenutzer(string $email, ?string $benutzername = null, ?string $passwort = null): bool {
        foreach (self::$benutzer as $key => $nutzer) {
            if ($nutzer['email'] === $email) {
                if ($benutzername !== null) {
                    self::$benutzer[$key]['benutzername'] = $benutzername;
                }
                if ($passwort !== null) {
                    self::$benutzer[$key]['passwort'] = $passwort;
                }
                return true;
            }
        }
        return false;
    }
}