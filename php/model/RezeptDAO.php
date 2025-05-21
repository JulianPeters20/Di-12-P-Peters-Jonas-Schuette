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
}