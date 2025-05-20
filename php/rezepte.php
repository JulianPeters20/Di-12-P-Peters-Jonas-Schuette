<?php
// php/model/Rezept.php

class Rezept {
    public $name;
    public $kategorie;
    public $verfasser;
    public $veroeffentlichtAm;
    public $zutaten;
    public $zubereitung;
    public $kuechenutensilien;
    public $kommentare;

    public function __construct($name, $kategorie, $verfasser, $veroeffentlichtAm, $zutaten, $zubereitung, $kuechenutensilien) {
        $this->name = $name;
        $this->kategorie = $kategorie;
        $this->verfasser = $verfasser;
        $this->veroeffentlichtAm = $veroeffentlichtAm;
        $this->zutaten = $zutaten;
        $this->zubereitung = $zubereitung;
        $this->kuechenutensilien = $kuechenutensilien;
        $this->kommentare = array();
    }

    public function addKommentar($kommentar) {
        $this->kommentare[] = $kommentar;
    }
}

// php/controller/RezeptController.php

class RezeptController {
    public function __construct() {
        // Hier können wir die Datenbank-Verbindung herstellen
        // oder andere Anfragen an die Datenbank senden
    }

    public function getRezept($name) {
        // Hier können wir die Datenbank-Abfrage ausführen
        // um das Rezept zu finden
        $rezept = new Rezept($name, 'Vegetarisch', 'julia@example.com', '21.04.2025', array('200g Spaghetti', '2 EL Pesto (z.B. Basilikum)', 'Salz'), 'Die Nudeln in gesalzenem Wasser al dente kochen. Danach abgießen, zurück in den Topf geben und das Pesto unterrühren. Warm servieren.', array('Kochtopf', 'Sieb', 'Esslöffel'));
        $rezept->addKommentar(new Kommentar('max@example.com', 'Sehr einfach und lecker! Habe noch geriebenen Käse dazugegeben.', '21.04.2025'));
        $rezept->addKommentar(new Kommentar('lena@beispiel.de', 'Funktioniert auch gut mit Vollkornnudeln. Danke fürs Teilen!', '22.04.2025'));
        return $rezept;
    }
}

// php/model/Kommentar.php

class Kommentar {
    public $autor;
    public $text;
    public $datum;

    public function __construct($autor, $text, $datum) {
        $this->autor = $autor;
        $this->text = $text;
        $this->datum = $datum;
    }
}

// php/view/RezeptView.php

class RezeptView {
    public function __construct() {
        // Hier können wir die HTML-Datei einbinden
        // und die Daten darin ausgeben
    }

    public function anzeigenRezept($rezept) {
        echo '<h2>' . $rezept->name . '</h2>';

        echo '<p><strong>Kategorie:</strong> ' . $rezept->kategorie . '</p>';
        echo '<p><strong>Verfasser:</strong> ' . $rezept->verfasser . '</p>';
        echo '<p><strong>Veröffentlicht am:</strong> ' . $rezept->veroeffentlichtAm . '</p>';

        echo '<figure>';
        echo '<img src="images/' . $rezept->name . '.jpg" alt="Teller mit Nudeln und grünem Pesto" width="300">';
        echo '<figcaption>So sieht das fertige Gericht aus.</figcaption>';
        echo '</figure>';

        echo '<h3>Zutaten</h3>';
        echo '<ul class="ohne-punkte">';
        foreach ($rezept->zutaten as $zutat) {
            echo '<li>' . $zutat . '</li>';
        }
        echo '</ul>';

        echo '<h3>Zubereitung</h3>';
        echo '<p>' . $rezept->zubereitung . '</p>';

        echo '<h3>Küchenutensilien</h3>';
        echo '<ul class="ohne-punkte">';
        foreach ($rezept->kuechenutensilien as $utensil) {
            echo '<li>' . $utensil . '</li>';
        }
        echo '</ul>';

        echo '<hr>';

        echo '<section>';
        echo '<h3>Kommentare zum Rezept</h3>';

        foreach ($rezept->kommentare as $kommentar) {
            echo '<article>';
            echo '<h4>Kommentar von ' . $kommentar->autor . '</h4>';
            echo '<p><strong>' . $kommentar->autor . '</strong> schrieb am ' . $kommentar->datum . ':</p>';
            echo '<p>' . $kommentar->text . '</p>';
            echo '</article>';
        }

        echo '</section>';

        echo '<p><em>(Kommentarfunktion wird später technisch umgesetzt)</em></p>';

        echo '<hr>';

        echo '<p>';
        echo '<button> <a href="rezept-neu.html" class="btn"> Rezept bearbeiten </a></button>';
        echo '<button>Rezept löschen</button>';
        echo '</p>';
    }
}

// php/index.php

class Index {
    public function __construct() {
        // Hier können wir die Controller-Instanz erstellen
        // und die Datenbank-Verbindung herstellen
    }

    public function anzeigenRezept() {
        $rezeptController = new RezeptController();
        $rezept = $rezeptController->getRezept('Nudeln mit Pesto');
        $rezeptView = new RezeptView();
        $rezeptView->anzeigenRezept($rezept);
    }
}

$index = new Index();
$index->anzeigenRezept();