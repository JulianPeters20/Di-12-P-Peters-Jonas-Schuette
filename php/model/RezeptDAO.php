<?php
declare(strict_types=1);

/**
 * Datenzugriffsobjekt (DAO) für Rezepte.
 * Achtung: Alle Daten sind nur temporär und werden NICHT persistent gespeichert!
 */
class RezeptDAO {
    //Dummy-Daten
    private static $rezepte = [
        [
            'id' => 1,
            'titel' => 'Nudeln mit Pesto',
            'bild' => 'images/pesto.jpg',
            'kategorie' => ['vegetarisch'],
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
            'kategorie' => ['vegan'],
            'datum' => '20.04.2025',
            'autor' => 'max@example.com',
            'zutaten' => "150g Reis\n1 Dose Kokosmilch\n1 TL Currypulver\nGemüse nach Wahl",
            'zubereitung' => "Reis kochen.\nGemüse anbraten, mit Kokosmilch und Currypulver ablöschen.\nMit dem Reis servieren.",
            'utensilien' => "Topf\nPfanne\nSchneidebrett\nMesser",
            'portionsgroesse' => 4,
            'preis' => "5 - 10"
        ]
    ];

    private static int $naechsteId = 3;

    /**
     * Gibt alle Rezepte zurück.
     */
    public static function findeAlle(): array {
        return self::$rezepte;
    }

    /**
     * Gibt die $anzahl der neuesten Rezepte zurück, sortiert nach ID absteigend.
     */
    public static function findeNeueste(int $anzahl = 3): array {
        $kopie = self::$rezepte;
        usort($kopie, fn($a, $b) => $b['id'] <=> $a['id']);
        return array_slice($kopie, 0, $anzahl);
    }

    /**
     * Gibt ein Rezept anhand der ID zurück oder null, falls nicht gefunden.
     */
    public static function findeNachId(int $id): ?array {
        foreach (self::$rezepte as $rezept) {
            if ($rezept['id'] === $id) {
                return $rezept;
            }
        }
        return null;
    }

    /**
     * Fügt ein neues Rezept hinzu.
     */
    public static function addRezept(
        string $titel,
        array $kategorien,
        string $bild,
        string $datum,
        string $autor,
        string $zutaten = '',
        string $zubereitung = '',
        string $utensilien = '',
        int $portionsgroesse = 1,
        string $preis = ''
    ): array {
        $neuesRezept = [
            'id' => self::$naechsteId++,
            'titel' => $titel,
            'bild' => $bild,
            'kategorie' => $kategorien,
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

    /**
     * Aktualisiert ein bestehendes Rezept. Gibt true bei Erfolg zurück.
     */
    public static function aktualisiereRezept(
        int $id,
        ?string $titel = null,
        ?array $kategorien = null,
        ?string $bild = null,
        ?string $datum = null,
        ?string $autor = null,
        ?string $zutaten = null,
        ?string $zubereitung = null,
        ?string $utensilien = null,
        ?int $portionsgroesse = null,
        ?string $preis = null
    ): bool {
        foreach (self::$rezepte as &$rezept) {
            if ($rezept['id'] === $id) {
                if ($titel !== null) $rezept['titel'] = $titel;
                if ($kategorien !== null) $rezept['kategorie'] = $kategorien;
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

    /**
     * Löscht ein Rezept anhand der ID. Gibt true bei Erfolg zurück.
     */
    public static function loesche(int $id): bool {
        foreach (self::$rezepte as $index => $rezept) {
            if ($rezept['id'] === $id) {
                array_splice(self::$rezepte, $index, 1);
                return true;
            }
        }
        return false;
    }

    /**
     * Sucht alle Rezepte eines Autors (z. B. für das eigene Profil).
     */
    public static function findeAlleVonAutor(string $autorMail): array {
        return array_filter(
            self::$rezepte,
            fn($rezept) => $rezept['autor'] === $autorMail
        );
    }

    /**
     * Sucht alle Rezepte, die einer bestimmten Kategorie angehören.
     */
    public static function findeAlleMitKategorie(string $kategorie): array {
        return array_filter(
            self::$rezepte,
            fn($rezept) => in_array($kategorie, (array)$rezept['kategorie'], true)
        );
    }
}