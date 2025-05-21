<?php
// php/model/NutzerDAO.php

class NutzerDAO {
    // Dummy-Daten (static, da keine DB)
    private static $benutzer = [
        [
            'benutzername' => 'StudentOne',
            'email' => 'student@beispiel.de',
            'passwort' => 'geheim123'
        ],
        [
            'benutzername' => 'MaxMustermann',
            'email' => 'max@uni.de',
            'passwort' => '12345678'
        ]
    ];

    // Gibt alle Benutzer zurück
    public static function getAlleBenutzer() {
        return self::$benutzer;
    }

    // Benutzer suchen (Login)
    public static function findeBenutzer($email, $passwort) {
        foreach (self::$benutzer as $nutzer) {
            if ($nutzer['email'] === $email && $nutzer['passwort'] === $passwort) {
                return $nutzer;
            }
        }
        return null;
    }

    // Nach E-Mail suchen (für Registrierung/Profil)
    public static function findeBenutzerNachEmail($email) {
        foreach (self::$benutzer as $nutzer) {
            if ($nutzer['email'] === $email) {
                return $nutzer;
            }
        }
        return null;
    }

    // Benutzer hinzufügen (nur temporär, nicht persistent!)
    public static function addBenutzer($benutzername, $email, $passwort) {
        self::$benutzer[] = [
            'benutzername' => $benutzername,
            'email' => $email,
            'passwort' => $passwort
        ];
    }
}