<?php
// php/model/Nutzer.php

class Nutzer {
    public $benutzername;
    public $passwort;
    public $rezepte;

    public function __construct($benutzername, $passwort) {
        $this->benutzername = $benutzername;
        $this->passwort = $passwort;
        $this->rezepte = array();
    }

    public function addRezept($rezept) {
        $this->rezepte[] = $rezept;
    }

    public function getRezepte() {
        return $this->rezepte;
    }
}

// php/controller/NutzerController.php

class NutzerController {
    public function __construct() {
        // Hier können wir die Datenbank-Verbindung herstellen
        // oder andere Anfragen an die Datenbank senden
    }

    public function getNutzer($benutzername) {
        // Hier können wir die Datenbank-Abfrage ausführen
        // um den Nutzer zu finden
        $nutzer = new Nutzer($benutzername, 'geheim');
        $nutzer->addRezept(new Rezept('Nudeln mit Pesto', 'Vegetarisch', '21.04.2025', 'julia@example.com'));
        $nutzer->addRezept(new Rezept('Reis mit Curry', 'Vegan', '20.04.2025', 'max@example.com'));
        return $nutzer;
    }
}

// php/model/Rezept.php

class Rezept {
    public $name;
    public $kategorien;
    public $datum;
    public $autor;

    public function __construct($name, $kategorien, $datum, $autor) {
        $this->name = $name;
        $this->kategorien = $kategorien;
        $this->datum = $datum;
        $this->autor = $autor;
    }
}

// php/view/NutzerView.php

class NutzerView {
    public function __construct() {
        // Hier können wir die HTML-Datei einbinden
        // und die Daten darin ausgeben
    }

    public function anzeigenNutzer($nutzer) {
        echo '<h2>Mein Profil</h2>';
        echo '<div style="display: flex; align-items: center; gap: 20px; margin-bottom: 20px;">';
        echo '<img src="../images/Icon%20Nutzer%20ChatGPT.webp" alt="Profilbild" style="height: 80px; width: 80px; border-radius: 50%; padding: 10px;">';
        echo '<div>';
        echo '<p><strong>Benutzername:</strong> ' . $nutzer->benutzername . '</p>';
        echo '<p><strong>E-Mail:</strong> ' . $nutzer->passwort . '</p>';
        echo '</div>';
        echo '</div>';

        echo '<h3 style="margin-top: 30px; margin-bottom: 20px;">Eigene Rezepte</h3>';

        echo '<div class="rezept-galerie">';
        foreach ($nutzer->getRezepte() as $rezept) {
            echo '<div class="rezept-karte">';
            echo '<img src="../images/' . $rezept->name . '.jpg" alt="' . $rezept->name . '">';
            echo '<div class="inhalt">';
            echo '<h3><a href="../rezept.html">' . $rezept->name . '</a></h3>';
            echo '<p class="meta">' . $rezept->kategorien . ' · ' . $rezept->datum . ' · ' . $rezept->autor . '</p>';
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';

        echo '<h3 style="margin-top: 30px; margin-bottom: 20px;">Gespeicherte Rezepte</h3>';

        echo '<div class="rezept-galerie">';
        foreach ($nutzer->getRezepte() as $rezept) {
            echo '<div class="rezept-karte">';
            echo '<img src="../images/' . $rezept->name . '.jpg" alt="' . $rezept->name . '">';
            echo '<div class="inhalt">';
            echo '<h3><a href="../rezept.html">' . $rezept->name . '</a></h3>';
            echo '<p class="meta">' . $rezept->kategorien . ' · ' . $rezept->datum . ' · ' . $rezept->autor . '</p>';
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';

        echo '<div style="margin-top: 30px;">';
        echo '<a href="abmeldung.html" class="btn">Abmelden</a>';
        echo '</div>';
    }
}

// php/index.php

class Index {
    public function __construct() {
        // Hier können wir die Controller-Instanz erstellen
        // und die Datenbank-Verbindung herstellen
    }

    public function anzeigenNutzer() {
        $nutzerController = new NutzerController();
        $nutzer = $nutzerController->getNutzer('student123');
        $nutzerView = new NutzerView();
        $nutzerView->anzeigenNutzer($nutzer);
    }
}

$index = new Index();
$index->anzeigenNutzer();