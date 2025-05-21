<?php
// models/RezeptDAO.php

class RezeptDAO {
    // Statische Dummy-Daten für alle Instanzen
    private static $rezepte = [
        [
            'id' => 1,
            'titel' => 'Nudeln mit Pesto',
            'bild' => 'images/pesto.jpg',
            'kategorie' => 'vegetarisch',
            'datum' => '21.04.2025',
            'autor' => 'student@beispiel.de',
            'zutaten' => "200g Nudeln\n2 EL Pesto\nSalz\nPfeffer",
            'zubereitung' => "Nudeln nach Packungsanweisung kochen.\nAbgießen und mit Pesto vermengen.\nMit Salz und Pfeffer abschmecken.",
            'utensilien' => "Topf\nSieb\nLöffel",
            'portionsgroesse' => 2,
            'preis' => "lt5"
        ],
        [
            'id' => 2,
            'titel' => 'Reis mit Curry',
            'bild' => 'images/reis_mit_curry.jpg',
            'kategorie' => 'vegan',
            'datum' => '20.04.2025',
            'autor' => 'max@example.com',
            'zutaten' => "150g Reis\n1 Dose Kokosmilch\n1 TL Currypulver\nGemüse nach Wahl",
            'zubereitung' => "Reis kochen.\nGemüse anbraten, mit Kokosmilch und Currypulver ablöschen.\nMit dem Reis servieren.",
            'utensilien' => "Topf\nPfanne\nSchneidebrett\nMesser",
            'portionsgroesse' => 4,
            'preis' => "5 - 10"
        ]
    ];

    private static $naechsteId = 3;

    // Gibt alle Rezepte zurück
    public static function findeAlle() {
        return self::$rezepte;
    }

    // Gibt die $anzahl neuesten Rezepte zurück
    public static function findeNeueste($anzahl = 3) {
        // Kopie der Rezepte
        $kopie = self::$rezepte;

        // Sortieren nach ID absteigend (neuste oben)
        usort($kopie, function($a, $b) {
            return $b['id'] <=> $a['id'];
        });

        return array_slice($kopie, 0, $anzahl);
    }

    // Sucht und gibt ein Rezept nach ID zurück (oder null)
    public static function findeNachId($id) {
        foreach (self::$rezepte as $rezept) {
            if ($rezept['id'] == $id) {
                return $rezept;
            }
        }
        return null;
    }

    // Fügt ein neues Rezept hinzu
    public static function addRezept(
        $titel, $kategorie, $bild, $datum, $autor,
        $zutaten = '', $zubereitung = '', $utensilien = '',
        $portionsgroesse = 1, $preis = ''
    ) {
        $neuesRezept = [
            'id' => self::$naechsteId++,
            'titel' => $titel,
            'bild' => $bild,
            'kategorie' => $kategorie,
            'datum' => $datum,
            'autor' => $autor,
            'zutaten' => $zutaten,
            'zubereitung' => $zubereitung,
            'utensilien' => $utensilien,
            'portionsgroesse' => $portionsgroesse,
            'preis' => $preis
        ];
        self::$rezepte[] = $neuesRezept;
        return $neuesRezept;
    }

    // Aktualisiert ein bestehendes Rezept
    public static function aktualisiereRezept(
        $id,
        $titel = null,
        $kategorie = null,
        $bild = null,
        $datum = null,
        $autor = null,
        $zutaten = null,
        $zubereitung = null,
        $utensilien = null,
        $portionsgroesse = null,
        $preis = null
    ) {
        foreach (self::$rezepte as &$rezept) {
            if ($rezept['id'] == $id) {
                if ($titel !== null) $rezept['titel'] = $titel;
                if ($kategorie !== null) $rezept['kategorie'] = $kategorie;
                if ($bild !== null) $rezept['bild'] = $bild;
                if ($datum !== null) $rezept['datum'] = $datum;
                if ($autor !== null) $rezept['autor'] = $autor;
                if ($zutaten !== null) $rezept['zutaten'] = $zutaten;
                if ($zubereitung !== null) $rezept['zubereitung'] = $zubereitung;
                if ($utensilien !== null) $rezept['utensilien'] = $utensilien;
                if ($portionsgroesse !== null) $rezept['portionsgroesse'] = $portionsgroesse;
                if ($preis !== null) $rezept['preis'] = $preis;
                return true;
            }
        }
        return false;
    }

    // Löscht ein Rezept
    public static function loesche($id) {
        foreach (self::$rezepte as $index => $rezept) {
            if ($rezept['id'] == $id) {
                array_splice(self::$rezepte, $index, 1);
                return true;
            }
        }
        return false;
    }
}