<?php
class NutzerDAO {

    // Dummy-Daten (Benutzerliste)
    private static $benutzer = [
        [
            'email' => 'student@beispiel.de',
            'passwort' => 'geheim123',      // Fürs Projekt ggf. später mit Passwort-Hash
            'benutzername' => 'StudentOne'
        ],
        [
            'email' => 'max@uni.de',
            'passwort' => '12345678',
            'benutzername' => 'MaxMustermann'
        ]
    ];

    // Gibt die komplette Benutzerliste zurück (z. B. für die Nutzerübersicht)
    public static function getAlleBenutzer() {
        return self::$benutzer;
    }

    // Sucht Nutzer anhand von E-Mail und Passwort (z. B. für Login)
    public static function findeBenutzer($email, $passwort) {
        foreach (self::$benutzer as $nutzer) {
            if ($nutzer['email'] === $email && $nutzer['passwort'] === $passwort) {
                return $nutzer;
            }
        }
        return null; // Kein Treffer
    }

    // Sucht Nutzer anhand der E-Mail-Adresse
    public static function findeBenutzerNachEmail($email) {
        foreach (self::$benutzer as $nutzer) {
            if ($nutzer['email'] === $email) {
                return $nutzer;
            }
        }
        return null;
    }
}
?>
