<?php
// php/model/NutzerDAO.php

require_once 'nutzer.html';

class NutzerDAO {
    private static $nutzerListe = [];

    public static function addNutzer($nutzer) {
        self::$nutzerListe[] = $nutzer;
    }

    public static function findeNutzer($benutzername) {
        foreach (self::$nutzerListe as $nutzer) {
            if ($nutzer->benutzername === $benutzername) {
                return $nutzer;
            }
        }
        return null;
    }

    public static function getAlle() {
        return self::$nutzerListe;
    }
}
?>
