<?php
require_once 'pdo.php';

$db = Database::getConnection();

try {
    $db->beginTransaction();

    // Tabellen löschen (nur für Entwicklung/testing, später entfernen!)
    $tabellen = ['RezeptKategorie', 'RezeptUtensil', 'RezeptZutat', 'Bewertung',
        'Rezept', 'Kategorie', 'Utensil', 'Zutat', 'Preisklasse', 'Portionsgröße', 'Nutzer', 'RezeptZutat'];
    foreach ($tabellen as $t) {
        $db->exec("DROP TABLE IF EXISTS $t");
    }

    // Nutzer
    $db->exec("
        CREATE TABLE Nutzer (
            NutzerID INTEGER PRIMARY KEY AUTOINCREMENT,
            Benutzername TEXT NOT NULL,
            Email TEXT NOT NULL UNIQUE,
            PasswortHash TEXT NOT NULL,
            RegistrierungsDatum TEXT NOT NULL,
            IstAdmin INTEGER NOT NULL DEFAULT 0
)
    ");

    // Preisklasse
    $db->exec("
        CREATE TABLE Preisklasse (
            PreisklasseID INTEGER PRIMARY KEY AUTOINCREMENT,
            Preisspanne TEXT NOT NULL
        )
    ");

    // Portionsgröße
    $db->exec("
        CREATE TABLE Portionsgröße (
            PortionsgrößeID INTEGER PRIMARY KEY AUTOINCREMENT,
            Angabe TEXT NOT NULL
        )
    ");

    // Rezept
    $db->exec("
        CREATE TABLE Rezept (
            RezeptID INTEGER PRIMARY KEY AUTOINCREMENT,
            Titel TEXT NOT NULL,
            Zubereitung TEXT NOT NULL,
            BildPfad TEXT,
            ErstellerID INTEGER,
            PreisklasseID INTEGER,
            PortionsgrößeID INTEGER,
            Erstellungsdatum TEXT NOT NULL,
            FOREIGN KEY (ErstellerID) REFERENCES Nutzer(NutzerID) ON DELETE CASCADE,
            FOREIGN KEY (PreisklasseID) REFERENCES Preisklasse(PreisklasseID),
            FOREIGN KEY (PortionsgrößeID) REFERENCES Portionsgröße(PortionsgrößeID)
        )
    ");

    // Bewertung
    $db->exec("
        CREATE TABLE Bewertung (
            RezeptID INTEGER,
            NutzerID INTEGER,
            Punkte INTEGER NOT NULL,
            Bewertungsdatum TEXT NOT NULL,
            PRIMARY KEY (RezeptID, NutzerID),
            FOREIGN KEY (RezeptID) REFERENCES Rezept(RezeptID) ON DELETE CASCADE,
            FOREIGN KEY (NutzerID) REFERENCES Nutzer(NutzerID) ON DELETE CASCADE
        )
    ");

    // Zutat
    $db->exec("
        CREATE TABLE Zutat (
            ZutatID INTEGER PRIMARY KEY AUTOINCREMENT,
            Name TEXT NOT NULL
        )
    ");

    // Utensil
    $db->exec("
        CREATE TABLE Utensil (
            UtensilID INTEGER PRIMARY KEY AUTOINCREMENT,
            Name TEXT NOT NULL
        )
    ");

    // Kategorie
    $db->exec("
        CREATE TABLE Kategorie (
            KategorieID INTEGER PRIMARY KEY AUTOINCREMENT,
            Bezeichnung TEXT NOT NULL
        )
    ");

    // RezeptZutat
    $db->exec("
        CREATE TABLE RezeptZutat (
            RezeptID INTEGER,
            Zutat TEXT,
            Menge TEXT,
            Einheit TEXT,
            PRIMARY KEY (RezeptID, Zutat),
            FOREIGN KEY (RezeptID) REFERENCES Rezept(RezeptID) ON DELETE CASCADE
        )
    ");

    // RezeptUtensil
    $db->exec("
        CREATE TABLE RezeptUtensil (
            RezeptID INTEGER,
            UtensilID INTEGER,
            PRIMARY KEY (RezeptID, UtensilID),
            FOREIGN KEY (RezeptID) REFERENCES Rezept(RezeptID) ON DELETE CASCADE,
            FOREIGN KEY (UtensilID) REFERENCES Utensil(UtensilID)
        )
    ");

    // RezeptKategorie
    $db->exec("
        CREATE TABLE RezeptKategorie (
            RezeptID INTEGER,
            KategorieID INTEGER,
            PRIMARY KEY (RezeptID, KategorieID),
            FOREIGN KEY (RezeptID) REFERENCES Rezept(RezeptID) ON DELETE CASCADE,
            FOREIGN KEY (KategorieID) REFERENCES Kategorie(KategorieID)
        )
    ");

    // Beispieldaten
    $nutzerStmt = $db->prepare("
    INSERT INTO Nutzer (Benutzername, Email, PasswortHash, RegistrierungsDatum, IstAdmin)
    VALUES (?, ?, ?, ?, ?)
");

// Admin 1
    $nutzerStmt->execute([
        'admin1',
        'admin1@example.com',
        password_hash('adminpass1', PASSWORD_DEFAULT),
        date('Y-m-d'),
        1 // istAdmin = true
    ]);

// Admin 2
    $nutzerStmt->execute([
        'admin2',
        'admin2@example.com',
        password_hash('adminpass2', PASSWORD_DEFAULT),
        date('Y-m-d'),
        1 // istAdmin = true
    ]);

// Normaler Nutzer
    $nutzerStmt->execute([
        'max_muster',
        'max@example.com',
        password_hash('geheim123', PASSWORD_DEFAULT),
        date('Y-m-d'),
        0 // istAdmin = false
    ]);

    $db->exec("INSERT INTO Preisklasse (Preisspanne) VALUES ('unter 5€'), ('5–10€')");
    $db->exec("INSERT INTO Portionsgröße (Angabe) VALUES ('1 Person'), ('2 Personen')");

    $db->exec("INSERT INTO Zutat (Name) VALUES ('Pasta'), ('Tomaten'), ('Basilikum')");
    $db->exec("INSERT INTO Utensil (Name) VALUES ('Topf'), ('Pfanne')");
    $db->exec("INSERT INTO Kategorie (Bezeichnung) VALUES ('Vegetarisch'), ('Schnell')");

    $rezeptStmt = $db->prepare("
        INSERT INTO Rezept (Titel, Zubereitung, BildPfad, ErstellerID, PreisklasseID, PortionsgrößeID, Erstellungsdatum)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $rezeptStmt->execute([
        'Pasta mit Tomaten', 'Pasta kochen, Tomaten schneiden, mischen.',
        '/images/pasta.jpg', 1, 1, 2, date('Y-m-d')
    ]);

    $db->exec("
    INSERT INTO RezeptZutat (RezeptID, Zutat, Menge, Einheit)
    VALUES 
        (1, 'Pasta', '200', 'g'),
        (1, 'Tomaten', '3', 'Stück')
");
    $db->exec("INSERT INTO RezeptUtensil VALUES (1, 1)");
    $db->exec("INSERT INTO RezeptKategorie VALUES (1, 1), (1, 2)");
    $db->exec("INSERT INTO Bewertung VALUES (1, 1, 5, '" . date('Y-m-d') . "')");

    $db->commit();
    echo "Datenbank erfolgreich initialisiert.";
} catch (Exception $e) {
    $db->rollBack();
    die("Fehler bei Initialisierung: " . $e->getMessage());
}