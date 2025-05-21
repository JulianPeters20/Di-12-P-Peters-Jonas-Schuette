<?php
// models/RezeptDAO.php

class RezeptDAO {
    // Dummy-Daten für Rezepte
    private $rezepte = [
        [
            'id' => 1,
            'titel' => 'Nudeln mit Pesto',
            'bild' => 'images/pesto.jpg',
            'kategorie' => 'Vegetarisch',
            'datum' => '21.04.2025',
            'autor' => 'julia@example.com'
        ],
        [
            'id' => 2,
            'titel' => 'Reis mit Curry',
            'bild' => 'images/reis_mit_curry.jpg',
            'kategorie' => 'Vegan',
            'datum' => '20.04.2025',
            'autor' => 'max@example.com'
        ]
        // Hier kannst du weitere Rezepte ergänzen
    ];

    // Gibt alle Rezepte zurück
    public function findeAlle() {
        return $this->rezepte;
    }

    // Sucht und gibt ein Rezept nach ID zurück (oder null, falls nicht gefunden)
    public function findeNachId($id) {
        foreach ($this->rezepte as $rezept) {
            if ($rezept['id'] == $id) {
                return $rezept;
            }
        }
        return null;
    }

    // Fügt ein neues Rezept hinzu
    public function addRezept($titel, $kategorie, $bild, $datum, $autor) {
        $neuesRezept = [
            'id' => count($this->rezepte) + 1,
            'titel' => $titel,
            'bild' => $bild,
            'kategorie' => $kategorie,
            'datum' => $datum,
            'autor' => $autor
        ];
        $this->rezepte[] = $neuesRezept;
        return $neuesRezept;
    }

    // Aktualisiert ein bestehendes Rezept
    public function aktualisiereRezept($id, $titel = null, $kategorie = null, $bild = null, $datum = null, $autor = null) {
        foreach ($this->rezepte as &$rezept) {
            if ($rezept['id'] == $id) {
                if ($titel !== null) {
                    $rezept['titel'] = $titel;
                }
                if ($kategorie !== null) {
                    $rezept['kategorie'] = $kategorie;
                }
                if ($bild !== null) {
                    $rezept['bild'] = $bild;
                }
                if ($datum !== null) {
                    $rezept['datum'] = $datum;
                }
                if ($autor !== null) {
                    $rezept['autor'] = $autor;
                }
                return true;
            }
        }
        return false;
    }

    // Löscht ein bestehendes Rezept
    public function loescheRezept($id) {
        foreach ($this->rezepte as $key => $rezept) {
            if ($rezept['id'] == $id) {
                unset($this->rezepte[$key]);
                return true;
            }
        }
        return false;
    }
}